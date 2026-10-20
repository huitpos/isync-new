<?php

namespace App\Services\Gl;

use App\Models\Account;
use App\Models\Branch;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JournalPoster
{
    public function saveDraft(int $companyId, array $header, array $lines, ?JournalEntry $existing = null): JournalEntry
    {
        return DB::transaction(function () use ($companyId, $header, $lines, $existing) {
            $normalized = $this->normalizeLines($companyId, $lines);

            if ($existing) {
                if ((int) $existing->company_id !== $companyId || $existing->status !== 'draft') {
                    throw ValidationException::withMessages([
                        'journal' => 'Only a draft journal can be edited.',
                    ]);
                }

                $existing->update([
                    'entry_date' => $header['entry_date'],
                    'description' => $header['description'],
                ]);
                $existing->lines()->delete();
                $this->storeLines($existing, $normalized);

                return $existing->fresh('lines');
            }

            $entry = JournalEntry::create([
                'company_id' => $companyId,
                'number' => $this->nextNumber($companyId, $header['entry_date']),
                'entry_date' => $header['entry_date'],
                'source_type' => 'manual',
                'source_key' => null,
                'status' => 'draft',
                'description' => $header['description'],
            ]);
            $entry->source_key = 'manual-'.$entry->id;
            $entry->save();
            $this->storeLines($entry, $normalized);

            return $entry->fresh('lines');
        });
    }

    public function post(int $companyId, array $header, array $lines): JournalEntry
    {
        return DB::transaction(function () use ($companyId, $header, $lines) {
            $this->assertPeriodOpen($companyId, $header['entry_date']);
            $this->assertSourceAvailable($companyId, $header['source_type'], $header['source_key'] ?? null);
            $normalized = $this->normalizeLines($companyId, $lines);

            $entry = JournalEntry::create([
                'company_id' => $companyId,
                'number' => $this->nextNumber($companyId, $header['entry_date']),
                'entry_date' => $header['entry_date'],
                'source_type' => $header['source_type'],
                'source_key' => $header['source_key'] ?? null,
                'status' => 'posted',
                'description' => $header['description'],
                'posted_at' => now(),
                'posted_by' => auth()->id(),
            ]);
            $this->storeLines($entry, $normalized);

            return $entry->fresh('lines');
        });
    }

    public function postDraft(JournalEntry $entry): JournalEntry
    {
        return DB::transaction(function () use ($entry) {
            $locked = JournalEntry::where('id', $entry->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'draft') {
                throw ValidationException::withMessages([
                    'journal' => 'Only a draft journal can be posted.',
                ]);
            }

            $this->assertPeriodOpen((int) $locked->company_id, $locked->entry_date->toDateString());
            $lines = $locked->lines->map(fn ($line) => [
                'account_id' => $line->account_id,
                'branch_id' => $line->branch_id,
                'debit' => $line->debit,
                'credit' => $line->credit,
                'memo' => $line->memo,
            ])->all();
            $this->normalizeLines((int) $locked->company_id, $lines);

            $locked->status = 'posted';
            $locked->posted_at = now();
            $locked->posted_by = auth()->id();
            $locked->save();

            return $locked->fresh('lines');
        });
    }

    public function void(JournalEntry $entry): JournalEntry
    {
        return DB::transaction(function () use ($entry) {
            $locked = JournalEntry::where('id', $entry->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'posted') {
                throw ValidationException::withMessages([
                    'journal' => 'Only a posted journal can be voided.',
                ]);
            }

            if ($locked->source_type === 'reversal') {
                throw ValidationException::withMessages([
                    'journal' => 'A reversal journal cannot be voided.',
                ]);
            }

            $date = $locked->entry_date->toDateString();
            $this->assertPeriodOpen((int) $locked->company_id, $date);

            $reversalLines = $locked->lines->map(fn ($line) => [
                'account_id' => $line->account_id,
                'branch_id' => $line->branch_id,
                'debit' => $line->credit,
                'credit' => $line->debit,
                'memo' => 'Reversal of '.$locked->number,
            ])->all();

            $reversal = JournalEntry::create([
                'company_id' => $locked->company_id,
                'number' => $this->nextNumber((int) $locked->company_id, $date),
                'entry_date' => $date,
                'source_type' => 'reversal',
                'source_key' => (string) $locked->id,
                'status' => 'posted',
                'description' => 'Reversal of '.$locked->number,
                'posted_at' => now(),
                'posted_by' => auth()->id(),
            ]);
            $this->storeLines($reversal, $this->normalizeLines((int) $locked->company_id, $reversalLines));

            if ($locked->source_key) {
                $locked->source_key = $locked->source_key.'#voided#'.$locked->id;
            }
            $locked->status = 'void';
            $locked->voided_at = now();
            $locked->voided_by = auth()->id();
            $locked->reversal_entry_id = $reversal->id;
            $locked->save();

            return $reversal;
        });
    }

    public function assertPeriodOpen(int $companyId, string $date): void
    {
        $closed = FiscalPeriod::where('company_id', $companyId)
            ->where('status', 'closed')
            ->whereDate('starts_on', '<=', $date)
            ->whereDate('ends_on', '>=', $date)
            ->exists();

        if ($closed) {
            throw ValidationException::withMessages([
                'entry_date' => 'The fiscal period for this date is closed.',
            ]);
        }
    }

    private function assertSourceAvailable(int $companyId, string $sourceType, ?string $sourceKey): void
    {
        if (!$sourceKey) {
            return;
        }

        $exists = JournalEntry::where('company_id', $companyId)
            ->where('source_type', $sourceType)
            ->where('source_key', $sourceKey)
            ->where('status', 'posted')
            ->lockForUpdate()
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'journal' => 'This document is already posted.',
            ]);
        }
    }

    public function normalizeLines(int $companyId, array $lines): array
    {
        $accountIds = [];
        $branchIds = [];
        $normalized = [];

        foreach ($lines as $line) {
            $debit = round((float) ($line['debit'] ?? 0), 4);
            $credit = round((float) ($line['credit'] ?? 0), 4);
            $net = round($debit - $credit, 4);
            if (abs($net) < 0.0001) {
                continue;
            }

            $normalized[] = [
                'account_id' => (int) $line['account_id'],
                'branch_id' => empty($line['branch_id']) ? null : (int) $line['branch_id'],
                'debit' => $net > 0 ? $net : 0,
                'credit' => $net < 0 ? abs($net) : 0,
                'memo' => $line['memo'] ?? null,
            ];
            $accountIds[] = (int) $line['account_id'];
            if (!empty($line['branch_id'])) {
                $branchIds[] = (int) $line['branch_id'];
            }
        }

        if (count($normalized) < 2) {
            throw ValidationException::withMessages([
                'lines' => 'A journal needs at least one debit and one credit.',
            ]);
        }

        $debits = round(collect($normalized)->sum('debit'), 4);
        $credits = round(collect($normalized)->sum('credit'), 4);
        if (abs($debits - $credits) > 0.0001) {
            throw ValidationException::withMessages([
                'lines' => 'Debits and credits must be equal.',
            ]);
        }

        $validAccounts = Account::where('company_id', $companyId)
            ->whereIn('id', array_unique($accountIds))
            ->where('is_postable', true)
            ->where('is_active', true)
            ->pluck('id')
            ->all();

        if (count(array_unique($accountIds)) !== count($validAccounts)) {
            throw ValidationException::withMessages([
                'lines' => 'Each line must use an active postable account of this company.',
            ]);
        }

        if ($branchIds) {
            $validBranches = Branch::where('company_id', $companyId)
                ->whereIn('id', array_unique($branchIds))
                ->pluck('id')
                ->count();
            if ($validBranches !== count(array_unique($branchIds))) {
                throw ValidationException::withMessages([
                    'lines' => 'A branch on a journal line does not belong to this company.',
                ]);
            }
        }

        return $normalized;
    }

    private function storeLines(JournalEntry $entry, array $lines): void
    {
        $rows = [];
        foreach (array_values($lines) as $index => $line) {
            $rows[] = array_merge($line, ['line_no' => $index + 1]);
        }
        $entry->lines()->createMany($rows);
    }

    private function nextNumber(int $companyId, string $date): string
    {
        $year = Carbon::parse($date)->format('Y');
        $prefix = 'GL-'.$year.'-';
        $last = JournalEntry::where('company_id', $companyId)
            ->where('number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('number');

        $seq = 1;
        if ($last && preg_match('/(\d+)$/', $last, $matches)) {
            $seq = ((int) $matches[1]) + 1;
        }

        return $prefix.str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
    }
}
