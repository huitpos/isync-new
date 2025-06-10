<div class="card card-flush {{ $class ?? '' }} mb-5 mb-xl-10" style="{{ $style ?? '' }}">
	<div class="card-header pt-5">
		<div class="card-title d-flex flex-column">
			<span class="fs-1 fw-bold text-gray-900 me-2 lh-1 ls-n2">
				{{ $text }}
			</span>

			@if ($subText)
				<span class="text-gray-500 pt-1 fw-semibold fs-6 mb-5 mt-2">{{ $subText }}</span>
			@endif
		</div>
	</div>
</div>
