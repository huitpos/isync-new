<x-default-layout>

    @section('title')
        {{ $paymentType ?: 'Payment' }} Details
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <h2 class="text-center fw-bold mb-1">{{ $paymentType }}</h2>
            <p class="text-center text-muted mb-6">
                {{ $branch->name }}
                ·
                {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }}
                –
                {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
            </p>

            <div class="table-responsive">
                <table id="kt_datatable_zero_configuration" class="table table-striped table-row-bordered gy-5">
                    <thead>
                        <tr class="fw-semibold fs-6 text-muted">
                            <th>Date</th>
                            <th>Machine No.</th>
                            <th>OR No.</th>
                            <th>Cashier Name</th>
                            <th>Shift No.</th>
                            <th>Transaction Details</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="6" class="text-end">Total</td>
                            <td class="text-end" id="payment_details_total">0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $("#kt_datatable_zero_configuration").DataTable({
                processing: true,
                serverSide: true,
                ajax: window.location.href,
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100],
                order: [[0, 'asc']],
                columns: [
                    { data: 'date' },
                    { data: 'machine_number' },
                    { data: 'receipt_number' },
                    { data: 'cashier_name' },
                    { data: 'shift_number' },
                    { data: 'details', orderable: false, searchable: true },
                    { data: 'amount', className: 'text-end' }
                ],
                drawCallback: function () {
                    const json = this.api().ajax.json();
                    if (json && json.totalAmount !== undefined) {
                        $('#payment_details_total').text(json.totalAmount);
                    }
                }
            });
        </script>
    @endpush
</x-default-layout>
