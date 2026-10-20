<?php

namespace App\Http\Controllers\Company;

use App\DataTables\Company\JournalsDataTable;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Services\Gl\JournalPoster;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JournalEntryController extends Controller
{
    public function __construct(private JournalPoster $poster)
    {
    }

    public function index(Request $request, string $companySlug, JournalsDataTable $dataTable)
    {
        $company = $request->attributes->get('company');

        return $dataTable->with([
            'company_id' => $company->id,
            'company_slug' => $company->slug,
        ])->render('company.accounting.journals.index', [
            'company' => $company,
        ]);
    }

    public function create(Request $request, string $companySlug)
    {
        $company = $request->attributes->get('company');

        return view('company.accounting.journals.create', $this->formData($company));
    }

    public function store(Request $request, string $companySlug)
    {
        $company = $request->attributes->get('company');
        [$header, $lines] = $this->payload($request);
        $entry = $this->poster->saveDraft($company->id, $header, $lines);

        return redirect()
            ->route('company.journals.show', ['companySlug' => $company->slug, 'id' => $entry->id])
            ->with('success', 'Draft journal saved.');
    }

    public function show(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');
        $entry = $this->find($company->id, $id);

        return view('company.accounting.journals.show', [
            'company' => $company,
            'entry' => $entry,
        ]);
    }

    public function edit(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');
        $entry = $this->find($company->id, $id);
        if ($entry->status !== 'draft') {
            throw ValidationException::withMessages([
                'journal' => 'Only a draft journal can be edited.',
            ]);
        }

        return view('company.accounting.journals.edit', $this->formData($company, $entry));
    }

    public function update(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');
        $entry = $this->find($company->id, $id);
        [$header, $lines] = $this->payload($request);
        $this->poster->saveDraft($company->id, $header, $lines, $entry);

        return redirect()
            ->route('company.journals.show', ['companySlug' => $company->slug, 'id' => $entry->id])
            ->with('success', 'Draft journal updated.');
    }

    public function post(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');
        $entry = $this->find($company->id, $id);
        $this->poster->postDraft($entry);

        return redirect()
            ->route('company.journals.show', ['companySlug' => $company->slug, 'id' => $entry->id])
            ->with('success', 'Journal posted.');
    }

    public function void(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');
        $entry = $this->find($company->id, $id);
        $reversal = $this->poster->void($entry);

        return redirect()
            ->route('company.journals.show', ['companySlug' => $company->slug, 'id' => $reversal->id])
            ->with('success', 'Journal voided with reversing entry '.$reversal->number.'.');
    }

    private function formData($company, ?JournalEntry $entry = null): array
    {
        return [
            'company' => $company,
            'entry' => $entry,
            'accounts' => Account::where('company_id', $company->id)->where('is_postable', true)->where('is_active', true)->orderBy('code')->get(),
            'branches' => $company->branches()->orderBy('name')->get(),
        ];
    }

    private function payload(Request $request): array
    {
        $data = $request->validate([
            'entry_date' => 'required|date',
            'description' => 'required|string|max:255',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'nullable|integer',
            'lines.*.branch_id' => 'nullable|integer',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'lines.*.memo' => 'nullable|string|max:255',
        ]);

        $lines = [];
        foreach ($data['lines'] as $index => $line) {
            if (empty($line['account_id'])) {
                continue;
            }
            $debit = round((float) ($line['debit'] ?? 0), 4);
            $credit = round((float) ($line['credit'] ?? 0), 4);
            if ($debit > 0 && $credit > 0) {
                throw ValidationException::withMessages([
                    "lines.$index.debit" => 'Enter a debit or a credit, not both.',
                ]);
            }
            if ($debit <= 0 && $credit <= 0) {
                continue;
            }
            $lines[] = [
                'account_id' => $line['account_id'],
                'branch_id' => $line['branch_id'] ?? null,
                'debit' => $debit,
                'credit' => $credit,
                'memo' => $line['memo'] ?? null,
            ];
        }

        return [
            [
                'entry_date' => $data['entry_date'],
                'description' => $data['description'],
            ],
            $lines,
        ];
    }

    private function find(int $companyId, string $id): JournalEntry
    {
        return JournalEntry::with(['lines.account', 'lines.branch', 'postedBy', 'voidedBy', 'reversal'])
            ->where('company_id', $companyId)
            ->findOrFail($id);
    }
}
