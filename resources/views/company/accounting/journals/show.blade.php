<x-default-layout>

    @section('title')
        {{ $entry->number }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.journals.show', $company, $entry) }}
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

            <div class="d-flex justify-content-between mb-6">
                <div>
                    <div class="fs-4 fw-bold">{{ $entry->number }}</div>
                    <div class="text-muted">{{ $entry->entry_date->format('Y-m-d') }} · {{ ucfirst($entry->status) }} · {{ str_replace('_', ' ', $entry->source_type) }}</div>
                    <div class="mt-2">{{ $entry->description }}</div>
                    @if ($entry->reversal)
                        <div class="mt-2">Reversed by <a href="{{ route('company.journals.show', ['companySlug' => $company->slug, 'id' => $entry->reversal_entry_id]) }}">{{ $entry->reversal->number }}</a></div>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    @if ($entry->status === 'draft')
                        <a href="{{ route('company.journals.edit', ['companySlug' => $company->slug, 'id' => $entry->id]) }}" class="btn btn-light">Edit</a>
                        <form method="POST" action="{{ route('company.journals.post', ['companySlug' => $company->slug, 'id' => $entry->id]) }}">
                            @csrf
                            <button class="btn btn-primary" type="submit">Post</button>
                        </form>
                    @elseif ($entry->status === 'posted' && $entry->source_type !== 'reversal')
                        <form method="POST" action="{{ route('company.journals.void', ['companySlug' => $company->slug, 'id' => $entry->id]) }}">
                            @csrf
                            <button class="btn btn-danger" type="submit" onclick="return confirm('Void this journal and post a reversing entry?')">Void</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th>Branch</th>
                            <th>Memo</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($entry->lines as $line)
                            <tr>
                                <td>{{ $line->account->code }} — {{ $line->account->name }}</td>
                                <td>{{ $line->branch->name ?? 'Company' }}</td>
                                <td>{{ $line->memo }}</td>
                                <td class="text-end">{{ $line->debit > 0 ? number_format($line->debit, 2) : '' }}</td>
                                <td class="text-end">{{ $line->credit > 0 ? number_format($line->credit, 2) : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="3">Total</td>
                            <td class="text-end">{{ number_format($entry->lines->sum('debit'), 2) }}</td>
                            <td class="text-end">{{ number_format($entry->lines->sum('credit'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</x-default-layout>
