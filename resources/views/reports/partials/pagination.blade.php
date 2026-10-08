@if ($paginator->total() > 0)
    <div class="d-flex flex-stack flex-wrap pt-5">
        <div class="fs-6 fw-semibold text-gray-700">
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }}
        </div>
        @if ($paginator->hasPages())
            <div>
                {{ $paginator->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endif
