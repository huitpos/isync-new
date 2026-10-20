<div class="card mb-5">
    <div class="card-header border-0 pt-6">
        <h3 class="card-title fw-bold">Needs attention</h3>
    </div>
    <div class="card-body pt-0">
        <div class="row g-3">
            @foreach ($items as $item)
                <div class="col-md-4 col-xl-2">
                    <a href="{{ $item['url'] }}" class="border rounded p-4 d-block text-gray-800 text-hover-primary">
                        <div class="fs-2 fw-bold">{{ $item['count'] }}</div>
                        <div class="fw-semibold">{{ $item['label'] }}</div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
