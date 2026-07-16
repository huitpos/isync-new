@extends('layout.master')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3">Inventory Tracking</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('inventory-tracking.history') }}" class="btn btn-outline-secondary">
                <i class="fas fa-history"></i> View History
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-5">
                    <label class="form-label" for="typeFilter">Movement Type <span class="text-danger">*</span></label>
                    <select id="typeFilter" name="type" class="form-select" required>
                        <option value="">-- Select Movement Type --</option>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label" for="branchFilter">Branch</label>
                    <select id="branchFilter" name="branch_id" class="form-select">
                        @if ($branches->count() > 1)
                            <option value="">All Branches</option>
                        @endif
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" id="clearFilters" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-redo"></i> Clear Filters
                    </button>
                </div>
            </div>

            <div id="noTypeAlert" class="alert alert-info" role="alert">
                <i class="fas fa-info-circle"></i> Please select a movement type to view items.
            </div>

            <div id="loadingSpinner" style="display: none; text-align: center; padding: 40px;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Loading movements...</p>
            </div>

            <div id="tableContainer" style="display: none;">
                <div class="table-responsive">
                    <table id="inventoryTable" class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Branch</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                        </tbody>
                    </table>
                </div>

                <div class="row mt-3">
                    <div class="col-sm-12 col-md-5">
                        <div id="tableInfo" class="dataTables_info"></div>
                    </div>
                    <div class="col-sm-12 col-md-7">
                        <div class="dataTables_paginate paging_simple_numbers float-md-end">
                            <ul class="pagination" id="pagination"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .table-responsive {
        border-radius: 0.25rem;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function () {
        let currentPage = 1;
        let totalPages = 1;
        let totalRecords = 0;
        const perPage = 15;

        function loadMovements(page = 1) {
            const type = $('#typeFilter').val();
            const branchId = $('#branchFilter').val();

            if (!type) {
                $('#noTypeAlert').show();
                $('#tableContainer').hide();
                $('#loadingSpinner').hide();
                return;
            }

            $('#noTypeAlert').hide();
            $('#tableContainer').hide();
            $('#loadingSpinner').show();

            $.ajax({
                url: '{{ route("inventory-tracking.ajax-movements") }}',
                type: 'GET',
                data: {
                    type: type,
                    branch_id: branchId,
                    page: page,
                    per_page: perPage
                },
                dataType: 'json',
                success: function (response) {
                    currentPage = parseInt(response.page);
                    totalPages = parseInt(response.pages) || 1;
                    totalRecords = parseInt(response.total) || 0;

                    renderTable(response.data, type, branchId);
                    updatePagination();

                    $('#loadingSpinner').hide();
                    $('#tableContainer').show();
                },
                error: function (xhr) {
                    $('#loadingSpinner').hide();
                    alert('Error loading movements: ' + (xhr.responseJSON?.error || 'Unknown error'));
                    console.error(xhr);
                }
            });
        }

        function renderTable(movements, type, branchId) {
            const $tbody = $('#tableBody');
            $tbody.empty();

            if (movements.length === 0) {
                $tbody.append('<tr><td colspan="5" class="text-center text-muted py-4">No movements found</td></tr>');
                return;
            }

            movements.forEach(function (movement) {
                const movementId = movement.id;
                const createdAt = new Date(movement.created_at).toLocaleDateString('en-PH', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                const viewParams = new URLSearchParams({
                    type: type,
                    branch_id: branchId,
                    page: currentPage
                });

                $tbody.append(`
                    <tr>
                        <td>${movement.type}</td>
                        <td>${movement.description || 'N/A'}</td>
                        <td>${movement.branch}</td>
                        <td><small>${createdAt}</small></td>
                        <td>
                            <a href="{{ url('inventory-tracking') }}/${movement.type}/${movementId}?${viewParams.toString()}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                `);
            });
        }

        function getPaginationPages(current, total) {
            const pages = [];

            if (total <= 7) {
                for (let i = 1; i <= total; i++) {
                    pages.push(i);
                }
                return pages;
            }

            pages.push(1);

            if (current <= 4) {
                for (let i = 2; i <= 5; i++) {
                    pages.push(i);
                }
                pages.push('ellipsis');
                pages.push(total);
            } else if (current >= total - 3) {
                pages.push('ellipsis');
                for (let i = total - 4; i <= total; i++) {
                    pages.push(i);
                }
            } else {
                pages.push('ellipsis');
                for (let i = current - 1; i <= current + 1; i++) {
                    pages.push(i);
                }
                pages.push('ellipsis');
                pages.push(total);
            }

            return pages;
        }

        function updatePagination() {
            const start = totalRecords === 0 ? 0 : ((currentPage - 1) * perPage) + 1;
            const end = Math.min(currentPage * perPage, totalRecords);

            $('#tableInfo').text(`Showing ${start} to ${end} of ${totalRecords} entries`);

            const $pagination = $('#pagination');
            $pagination.empty();

            $pagination.append(`
                <li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
                </li>
            `);

            getPaginationPages(currentPage, totalPages).forEach(function (page) {
                if (page === 'ellipsis') {
                    $pagination.append(`
                        <li class="page-item disabled">
                            <span class="page-link">…</span>
                        </li>
                    `);
                    return;
                }

                $pagination.append(`
                    <li class="page-item ${page === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${page}">${page}</a>
                    </li>
                `);
            });

            $pagination.append(`
                <li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
                </li>
            `);
        }

        $('#typeFilter, #branchFilter').on('change', function () {
            currentPage = 1;
            loadMovements(1);
        });

        $('#pagination').on('click', 'a.page-link', function (e) {
            e.preventDefault();

            const $item = $(this).parent();
            if ($item.hasClass('disabled') || $item.hasClass('active')) {
                return;
            }

            const page = parseInt($(this).data('page'), 10);
            if (page >= 1 && page <= totalPages) {
                loadMovements(page);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });

        $('#clearFilters').on('click', function () {
            $('#typeFilter').val('');
            $('#branchFilter').val('');
            $('#noTypeAlert').show();
            $('#tableContainer').hide();
            $('#loadingSpinner').hide();
            currentPage = 1;
            window.history.replaceState({}, '', '{{ route('inventory-tracking.index') }}');
        });

        const params = new URLSearchParams(window.location.search);
        const type = params.get('type');
        const branchId = params.get('branch_id');
        const page = parseInt(params.get('page') || '1', 10);

        if (type) {
            $('#typeFilter').val(type);
            if (branchId) {
                $('#branchFilter').val(branchId);
            }
            loadMovements(page);
        }
    });
</script>
@endpush
@endsection
