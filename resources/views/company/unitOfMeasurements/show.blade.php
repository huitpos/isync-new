<x-default-layout>
    @section('title')
        {{ $uom->name }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.uom.show', $company, $uom) }}
    @endsection

    <div class="card">
        <div class="card-body">
            <div class="pb-4 fs-6">
                <div class="fw-bold">Name</div>
                <div class="text-gray-600">{{ $uom->name }}</div>
            </div>

            <div class="pb-4 fs-6">
                <div class="fw-bold">Description</div>
                <div class="text-gray-600">{{ $uom->description }}</div>
            </div>

            <form class="mt-3" action="{{ route('company.unit-of-measurements.save-conversion', ['companySlug' => $company->slug]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="uom_id" value="{{ $uom->id }}" hidden/>

                <div class="mt-7">
                    <label class="form-label fw-bold">Conversions</label>

                    @if ($toConversions->count() > 0)
                        @foreach ($toConversions as $toConversion)
                            <div class="form-group row">
                                <div class="col-md-12 mt-3">
                                    1 {{ $toConversion->fromUnit->name }} = {{ $toConversion->value }} {{ $toConversion->toUnit->name }} <a href="{{ route('company.unit-of-measurements.show', [
                                        'companySlug' => $company->slug,
                                        'unit_of_measurement' => $toConversion->fromUnit->id
                                    ]) }}">Edit</a>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    <!--begin::Repeater-->
                    <div class="repeater mt-10" data-init-empty="false">
                        <!--begin::Form group-->
                        <div class="form-group">
                            <div data-repeater-list="unit_conversions">
                                @if (empty(old('unit_conversions')) && count($uom->conversions) == 0)
                                    <div data-repeater-item>
                                        <div class="form-group row mb-5">
                                            <div class="col-md-1 mt-3">
                                                1 {{ $uom->name }} =
                                            </div>

                                            <div class="col-md-3">
                                                <input type="number" name="value" class="form-control mb-2 mb-md-0" placeholder="Conversion Ratio"/>
                                            </div>

                                            <div class="col-md-3">
                                                <select name="to_unit_id" class="form-control">
                                                    <option value="">Select UOM</option>
                                                    @foreach ($otherUoms as $otherUom)
                                                        <option value="{{ $otherUom->id }}">{{ $otherUom->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <a href="javascript:;" data-repeater-delete class="btn btn-sm btn-flex btn-light-danger mt-1">
                                                    <i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                    Delete Conversion
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    @php
                                        $fromOld = !empty(old('unit_conversions'));
                                        $conversions = old('unit_conversions') ?? [];

                                        if (!$fromOld) {
                                            $conversions = $uom->conversions->toArray();
                                        }
                                    @endphp

                                    @foreach ($conversions as $key => $conversion)
                                        <div data-repeater-item>
                                            <div class="form-group row mb-5">
                                                <div class="col-md-1 mt-3">
                                                    1 {{ $uom->name }} =
                                                </div>

                                                <div class="col-md-3">
                                                    <input type="number" value="{{ $conversion['value'] }}" name="value" class="form-control mb-2 mb-md-0 @error('unit_conversions.' . $key . '.value') is-invalid @enderror" placeholder="Conversion Ratio"/>

                                                    @error('unit_conversions.' . $key . '.value')
                                                        <div class="invalid-feedback"> {{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-md-3">
                                                    <select name="to_unit_id" class="form-control @error('unit_conversions.' . $key . '.to_unit_id') is-invalid @enderror">
                                                        <option value="">Select UOM</option>
                                                        @foreach ($otherUoms as $otherUom)
                                                            <option value="{{ $otherUom->id }}" {{ $otherUom->id == $conversion['to_unit_id'] ? 'selected' : '' }}>{{ $otherUom->name }}</option>
                                                        @endforeach
                                                    </select>

                                                    @error('unit_conversions.' . $key . '.to_unit_id')
                                                        <div class="invalid-feedback"> {{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-md-3">
                                                    <a href="javascript:;" data-repeater-delete class="btn btn-sm btn-flex btn-light-danger mt-1">
                                                        <i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                        Delete Conversion
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <!--end::Form group-->

                        <!--begin::Form group-->
                        <div class="form-group">
                            <a href="javascript:;" data-repeater-create class="btn btn-flex btn-light-primary">
                                <i class="ki-duotone ki-plus fs-3"></i>
                                Add Conversion
                            </a>
                        </div>
                        <!--end::Form group-->
                    </div>
                    <!--end::Repeater-->
                </div>

                <button type="submit" class="btn btn-primary mt-10 disable-on-click">Submit</button>
                <a href="{{ url()->previous() }}" class="btn btn-label-secondary waves-effect mt-10">Cancel</a>
            </form>
        </div>
    </div>
</x-default-layout>
