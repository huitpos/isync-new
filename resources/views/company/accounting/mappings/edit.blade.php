<x-default-layout>

    @section('title')
        Account Mapping
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.accountMappings.edit', $company) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('company.account-mappings.update', ['companySlug' => $company->slug]) }}">
                @csrf
                @method('PUT')

                <h3 class="mb-4">System accounts</h3>
                <div class="row">
                    @foreach ($keys as $key => $label)
                        <div class="col-md-6 mb-4">
                            <label class="form-label">{{ $label }}</label>
                            <select name="mappings[{{ $key }}]" class="form-select">
                                <option value="">Not mapped</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}" @selected((string) old('mappings.'.$key, $mappings[$key] ?? '') === (string) $account->id)>{{ $account->code }} — {{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>

                <h3 class="mb-4 mt-4">Payment types</h3>
                <p class="text-muted">Charge sales post to accounts receivable. Map cash, card, and e-wallet types here.</p>
                <div class="row">
                    @forelse ($paymentTypes as $paymentType)
                        <div class="col-md-6 mb-4">
                            <label class="form-label">
                                {{ $paymentType->name }}
                                @if (!$paymentType->company_id)
                                    <span class="text-muted">(all companies)</span>
                                @endif
                            </label>
                            <select name="payment_types[{{ $paymentType->id }}]" class="form-select">
                                <option value="">Not mapped</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}" @selected((string) old('payment_types.'.$paymentType->id, $paymentAccounts[$paymentType->id] ?? '') === (string) $account->id)>{{ $account->code }} — {{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @empty
                        <p>No payment types yet.</p>
                    @endforelse
                </div>

                <button type="submit" class="btn btn-primary mt-3">Save mapping</button>
            </form>
        </div>
    </div>

</x-default-layout>
