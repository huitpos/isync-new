@php
    $entry = $entry ?? null;
    $oldLines = old('lines');
    if ($oldLines === null) {
        $oldLines = $entry
            ? $entry->lines->map(fn ($line) => [
                'account_id' => $line->account_id,
                'branch_id' => $line->branch_id,
                'debit' => $line->debit > 0 ? $line->debit : '',
                'credit' => $line->credit > 0 ? $line->credit : '',
                'memo' => $line->memo,
            ])->all()
            : [];
        while (count($oldLines) < 6) {
            $oldLines[] = ['account_id' => '', 'branch_id' => '', 'debit' => '', 'credit' => '', 'memo' => ''];
        }
    }
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-md-3 mb-4">
        <label class="form-label">Date</label>
        <input type="date" name="entry_date" value="{{ old('entry_date', isset($entry) ? $entry->entry_date->format('Y-m-d') : now()->toDateString()) }}" class="form-control" required>
    </div>
    <div class="col-md-9 mb-4">
        <label class="form-label">Description</label>
        <input name="description" value="{{ old('description', $entry->description ?? '') }}" class="form-control" required>
    </div>
</div>

<div class="table-responsive">
    <table class="table align-middle" id="journal-lines">
        <thead>
            <tr>
                <th>Account</th>
                <th>Branch</th>
                <th class="text-end">Debit</th>
                <th class="text-end">Credit</th>
                <th>Memo</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($oldLines as $index => $line)
                <tr>
                    <td>
                        <select name="lines[{{ $index }}][account_id]" class="form-select">
                            <option value="">Select</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}" @selected((string) ($line['account_id'] ?? '') === (string) $account->id)>{{ $account->code }} — {{ $account->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select name="lines[{{ $index }}][branch_id]" class="form-select">
                            <option value="">Company</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((string) ($line['branch_id'] ?? '') === (string) $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input name="lines[{{ $index }}][debit]" value="{{ $line['debit'] ?? '' }}" class="form-control text-end" inputmode="decimal"></td>
                    <td><input name="lines[{{ $index }}][credit]" value="{{ $line['credit'] ?? '' }}" class="form-control text-end" inputmode="decimal"></td>
                    <td><input name="lines[{{ $index }}][memo]" value="{{ $line['memo'] ?? '' }}" class="form-control"></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<button type="button" class="btn btn-light-primary" id="add-journal-line">Add line</button>

@push('scripts')
<script>
    document.getElementById('add-journal-line')?.addEventListener('click', function () {
        const body = document.querySelector('#journal-lines tbody');
        const index = body.querySelectorAll('tr').length;
        const row = body.querySelector('tr').cloneNode(true);
        row.querySelectorAll('select, input').forEach(function (field) {
            field.name = field.name.replace(/lines\[\d+\]/, 'lines[' + index + ']');
            if (field.tagName === 'SELECT') {
                field.selectedIndex = 0;
            } else {
                field.value = '';
            }
        });
        body.appendChild(row);
    });
</script>
@endpush
