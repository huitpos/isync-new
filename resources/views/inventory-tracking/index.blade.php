@extends('layout.master')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">Inventory Tracking</h1>
        </div>
        <div class="col-md-4 text-end">
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

    <div class="card mb-4">
        <div class="card-header bg-light">
            <div class="card-title">
                <h2>Filters</h2>
            </div>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-4">
                    <label for="typeFilter" class="form-label">Movement Type <span class="text-danger">*</span></label>
                    <select id="typeFilter" name="type" class="form-select form-select-sm" required onchange="loadMovements()">
                        <option value="">-- Select Movement Type --</option>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="branchFilter" class="form-label">Branch</label>
                    <select id="branchFilter" name="branch_id" class="form-select form-select-sm" onchange="loadMovements()">
                        <option value="">-- All Branches --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="resetFilters()">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h2>Unprocessed Movements</h2>
            </div>
        </div>
        <div class="card-body">
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
                    <table id="inventoryTable" class="table table-hover">
                        <thead class="table-light">
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

                <nav aria-label="Table pagination">
                    <ul class="pagination justify-content-center">
                        <li class="page-item" id="prevPage">
                            <a class="page-link" href="#" onclick="previousPage(); return false;">Previous</a>
                        </li>
                        <li class="page-item" id="pageInfo">
                            <span class="page-link">Page 1</span>
                        </li>
                        <li class="page-item" id="nextPage">
                            <a class="page-link" href="#" onclick="nextPage(); return false;">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<style>
    .table th {
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
    }

    .badge {
        font-size: 0.85rem;
        padding: 0.5em 0.75em;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let currentPage = 1;
    let totalPages = 1;
    const perPage = 15;

    function loadMovements(page = 1) {
        const type = document.getElementById('typeFilter').value;
        const branchId = document.getElementById('branchFilter').value;

        if (!type) {
            document.getElementById('noTypeAlert').style.display = 'block';
            document.getElementById('tableContainer').style.display = 'none';
            document.getElementById('loadingSpinner').style.display = 'none';
            return;
        }

        document.getElementById('noTypeAlert').style.display = 'none';
        document.getElementById('tableContainer').style.display = 'none';
        document.getElementById('loadingSpinner').style.display = 'block';

        const params = new URLSearchParams({
            type: type,
            branch_id: branchId,
            page: page,
            per_page: perPage
        });

        $.ajax({
            url: '{{ route("inventory-tracking.ajax-movements") }}',
            type: 'GET',
            data: params.toString(),
            dataType: 'json',
            success: function(response) {
                currentPage = parseInt(response.page);
                totalPages = parseInt(response.pages);

                renderTable(response.data, type, branchId);
                updatePagination();
                
                document.getElementById('loadingSpinner').style.display = 'none';
                document.getElementById('tableContainer').style.display = 'block';
            },
            error: function(xhr) {
                document.getElementById('loadingSpinner').style.display = 'none';
                alert('Error loading movements: ' + (xhr.responseJSON?.error || 'Unknown error'));
                console.error(xhr);
            }
        });
    }

    function renderTable(movements, type, branchId) {
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';

        if (movements.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No movements found</td></tr>';
            return;
        }

        movements.forEach(movement => {
            const movementId = movement.id;
            const status = movement.status ? movement.status.charAt(0).toUpperCase() + movement.status.slice(1) : (movement.is_complete ? 'Complete' : 'Pending');
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
            const row = `
                <tr>
                    <td>
                        ${movement.type}
                    </td>
                    <td>${movement.description || 'N/A'}</td>
                    <td>${movement.branch}</td>
                    <td>${createdAt}</td>
                    <td>
                        <a href="{{ url('inventory-tracking') }}/${movement.type}/${movementId}?${viewParams.toString()}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });
    }

    function updatePagination() {
        const prevBtn = document.getElementById('prevPage');
        const nextBtn = document.getElementById('nextPage');
        const pageInfo = document.getElementById('pageInfo');

        pageInfo.innerHTML = `<span class="page-link">Page ${currentPage} of ${totalPages}</span>`;

        if (currentPage <= 1) {
            prevBtn.classList.add('disabled');
        } else {
            prevBtn.classList.remove('disabled');
        }

        if (currentPage >= totalPages) {
            nextBtn.classList.add('disabled');
        } else {
            nextBtn.classList.remove('disabled');
        }
    }

    function nextPage() {
        if (currentPage < totalPages) {
            loadMovements(currentPage + 1);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        return false;
    }

    function previousPage() {
        if (currentPage > 1) {
            loadMovements(currentPage - 1);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        return false;
    }

    function resetFilters() {
        document.getElementById('typeFilter').value = '';
        document.getElementById('branchFilter').value = '';
        document.getElementById('noTypeAlert').style.display = 'block';
        document.getElementById('tableContainer').style.display = 'none';
        document.getElementById('loadingSpinner').style.display = 'none';
        currentPage = 1;
        window.history.replaceState({}, '', '{{ route('inventory-tracking.index') }}');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const params = new URLSearchParams(window.location.search);
        const type = params.get('type');
        const branchId = params.get('branch_id');
        const page = parseInt(params.get('page') || '1', 10);

        if (type) {
            document.getElementById('typeFilter').value = type;
            if (branchId) {
                document.getElementById('branchFilter').value = branchId;
            }
            loadMovements(page);
        }
    });
</script>
@endsection
