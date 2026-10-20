<form method="GET" class="row align-items-end mb-6">
    @if (!empty($showAccount))
        <div class="col-md-4">
            <label class="form-label">Account</label>
            <select name="account_id" class="form-select" required>
                <option value="">Select</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}" @selected((string) ($accountId ?? '') === (string) $account->id)>{{ $account->code }} — {{ $account->name }}</option>
                @endforeach
            </select>
        </div>
    @endif
    @if (!empty($showRange))
        <div class="col-md-2">
            <label class="form-label">From</label>
            <input type="date" name="from" value="{{ $from }}" class="form-control">
        </div>
        <div class="col-md-2">
            <label class="form-label">To</label>
            <input type="date" name="to" value="{{ $to }}" class="form-control">
        </div>
    @endif
    @if (!empty($showAsOf))
        <div class="col-md-2">
            <label class="form-label">As of</label>
            <input type="date" name="as_of" value="{{ $asOf }}" class="form-control">
        </div>
    @endif
    <div class="col-md-3">
        <label class="form-label">Branch</label>
        <select name="branch_id" class="form-select">
            <option value="">All branches</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected((string) ($branchId ?? '') === (string) $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <button class="btn btn-primary" type="submit">Show</button>
    </div>
</form>
@if (!empty($branchId))
    <p class="text-muted">A single branch may not balance. Journals without a branch, and stock transfers, stay on the company books.</p>
@endif
