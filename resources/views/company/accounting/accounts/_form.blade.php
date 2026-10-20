@php
    $account = $account ?? null;
@endphp

<div class="row">
    <div class="col-md-3 mb-4">
        <label class="form-label">Code</label>
        <input name="code" value="{{ old('code', $account->code ?? '') }}" class="form-control @error('code') is-invalid @enderror" required>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-5 mb-4">
        <label class="form-label">Name</label>
        <input name="name" value="{{ old('name', $account->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 mb-4">
        <label class="form-label">Type</label>
        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
            @foreach (['asset', 'liability', 'equity', 'income', 'expense'] as $type)
                <option value="{{ $type }}" @selected(old('type', $account->type ?? '') === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-4">
        <label class="form-label">Parent</label>
        <select name="parent_id" class="form-select">
            <option value="">None</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" @selected((string) old('parent_id', $account->parent_id ?? '') === (string) $parent->id)>{{ $parent->code }} — {{ $parent->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-4 d-flex align-items-end gap-6">
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="is_postable" value="1" @checked(old('is_postable', $account->is_postable ?? true))>
            <span class="form-check-label">Postable</span>
        </label>
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $account->is_active ?? true))>
            <span class="form-check-label">Active</span>
        </label>
    </div>
</div>
