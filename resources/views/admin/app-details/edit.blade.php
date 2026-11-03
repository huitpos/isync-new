<x-default-layout>

    @section('title')
        POS App Details
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.app-details.edit') }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            @if ($appDetail->exists)
                <p class="text-muted">
                    Last updated {{ $appDetail->updated_at }}
                </p>
            @endif

            <form class="mt-3" action="{{ route('admin.app-details.update') }}" method="POST" novalidate>
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="form-label">Version Code</label>
                    <input value="{{ old('version_code', $appDetail->version_code) }}" name="version_code" type="number" min="1" class="form-control @error('version_code') is-invalid @enderror" placeholder="Version Code" required/>
                    <div class="form-text">Must match the <code>versionCode</code> of the APK. The POS app updates when this is higher than its installed version.</div>

                    @error('version_code')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Version Name</label>
                    <input value="{{ old('version_name', $appDetail->version_name) }}" name="version_name" type="text" class="form-control @error('version_name') is-invalid @enderror" placeholder="e.g. 1.3.0" required/>

                    @error('version_name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">APK URL</label>
                    <input value="{{ old('apk_url', $appDetail->apk_url) }}" name="apk_url" type="url" class="form-control @error('apk_url') is-invalid @enderror" placeholder="https://isync-bucket.s3.ap-southeast-1.amazonaws.com/pos/isyncpos-1.3.0.apk" required/>

                    @error('apk_url')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">SHA-256</label>
                    <input value="{{ old('sha256', $appDetail->sha256) }}" name="sha256" type="text" maxlength="64" class="form-control font-monospace @error('sha256') is-invalid @enderror" placeholder="SHA-256 checksum of the APK" required/>
                    <div class="form-text">Get it with <code>Get-FileHash file.apk</code> (Windows) or <code>sha256sum file.apk</code> (Linux).</div>

                    @error('sha256')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input type="hidden" name="mandatory" value="0">
                        <input class="form-check-input" type="checkbox" name="mandatory" value="1" id="mandatory" {{ old('mandatory', $appDetail->mandatory) ? 'checked' : '' }}>
                        <label class="form-check-label" for="mandatory">Mandatory update</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary disable-on-click">Save</button>
            </form>
        </div>
    </div>
</x-default-layout>
