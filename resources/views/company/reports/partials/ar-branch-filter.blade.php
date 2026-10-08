<form method="GET" action="{{ $action }}" class="mb-5">
    <div class="row">
        <div class="col-md-4">
            <label class="form-label" for="branch_id">Branch</label>
            <select id="branch_id" name="branch_id" class="form-select" onchange="this.form.submit()">
                <option value="" {{ $branchId ? '' : 'selected' }}>All Branches</option>
                @foreach ($branches as $branchOption)
                    <option value="{{ $branchOption->id }}" {{ (int) $branchId === (int) $branchOption->id ? 'selected' : '' }}>
                        {{ $branchOption->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</form>
