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
                    <input id="version-name" value="{{ old('version_name', $appDetail->version_name) }}" name="version_name" type="text" class="form-control @error('version_name') is-invalid @enderror" placeholder="e.g. 1.3.0" required/>

                    @error('version_name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label" for="apk-file">Upload APK directly to S3</label>
                    <div class="input-group">
                        <input id="apk-file" type="file" class="form-control" accept=".apk,application/vnd.android.package-archive">
                        <button id="upload-apk" type="button" class="btn btn-secondary">Upload APK</button>
                    </div>
                    <div class="form-text">
                        The file will be uploaded as
                        <code id="apk-filename">{{ $apkEnvironment }}-isyncpos-{{ old('version_name', $appDetail->version_name) }}.apk</code>.
                        It uploads directly from your browser to S3.
                    </div>
                    <div id="upload-progress-container" class="progress mt-3 d-none" style="height: 20px;">
                        <div id="upload-progress" class="progress-bar" role="progressbar" style="width: 0%">0%</div>
                    </div>
                    <div id="upload-error" class="text-danger mt-2 d-none"></div>
                </div>

                <div class="mb-4">
                    <label class="form-label">APK URL</label>
                    <input id="apk-url" value="{{ old('apk_url', $appDetail->apk_url) }}" name="apk_url" type="url" class="form-control @error('apk_url') is-invalid @enderror" placeholder="https://isync-bucket.s3.ap-southeast-1.amazonaws.com/pos/uat-isyncpos-1.3.0.apk" required/>

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

    @push('scripts')
        <script>
            (() => {
                const environment = @json($apkEnvironment);
                const versionName = document.getElementById('version-name');
                const apkFilename = document.getElementById('apk-filename');
                const apkFile = document.getElementById('apk-file');
                const apkUrl = document.getElementById('apk-url');
                const uploadButton = document.getElementById('upload-apk');
                const progressContainer = document.getElementById('upload-progress-container');
                const progress = document.getElementById('upload-progress');
                const uploadError = document.getElementById('upload-error');

                const updateFilename = () => {
                    apkFilename.textContent = `${environment}-isyncpos-${versionName.value || 'version'}.apk`;
                };

                const showError = (message) => {
                    uploadError.textContent = message;
                    uploadError.classList.remove('d-none');
                };

                const setUploading = (uploading) => {
                    uploadButton.disabled = uploading;
                    apkFile.disabled = uploading;
                    versionName.readOnly = uploading;
                    uploadButton.textContent = uploading ? 'Uploading...' : 'Upload APK';
                };

                versionName.addEventListener('input', updateFilename);

                uploadButton.addEventListener('click', async () => {
                    const file = apkFile.files[0];
                    const version = versionName.value.trim();

                    uploadError.classList.add('d-none');

                    if (!version) {
                        showError('Enter the version name before uploading.');
                        return;
                    }

                    if (!file || !file.name.toLowerCase().endsWith('.apk')) {
                        showError('Select an APK file to upload.');
                        return;
                    }

                    setUploading(true);
                    progressContainer.classList.remove('d-none');
                    progress.style.width = '0%';
                    progress.textContent = '0%';

                    try {
                        const response = await fetch(@json(route('admin.app-details.apk-upload-url')), {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ version_name: version }),
                        });
                        const data = await response.json();

                        if (!response.ok) {
                            const validationError = data.errors
                                ? Object.values(data.errors).flat()[0]
                                : data.message;
                            throw new Error(validationError || 'Unable to prepare the upload.');
                        }

                        await new Promise((resolve, reject) => {
                            const xhr = new XMLHttpRequest();
                            xhr.open('PUT', data.upload_url);
                            xhr.setRequestHeader('Content-Type', 'application/vnd.android.package-archive');

                            Object.entries(data.headers || {}).forEach(([name, value]) => {
                                if (name.toLowerCase() !== 'host') {
                                    xhr.setRequestHeader(name, Array.isArray(value) ? value.join(', ') : value);
                                }
                            });

                            xhr.upload.addEventListener('progress', (event) => {
                                if (event.lengthComputable) {
                                    const percent = Math.round((event.loaded / event.total) * 100);
                                    progress.style.width = `${percent}%`;
                                    progress.textContent = `${percent}%`;
                                }
                            });
                            xhr.addEventListener('load', () => {
                                xhr.status >= 200 && xhr.status < 300
                                    ? resolve()
                                    : reject(new Error(`S3 rejected the upload (HTTP ${xhr.status}).`));
                            });
                            xhr.addEventListener('error', () => reject(new Error('The direct S3 upload failed.')));
                            xhr.send(file);
                        });

                        apkUrl.value = data.apk_url;
                        progress.style.width = '100%';
                        progress.textContent = '100%';
                        toastr.success(`${data.filename} was uploaded successfully.`);
                    } catch (error) {
                        showError(error.message || 'The APK upload failed.');
                    } finally {
                        setUploading(false);
                    }
                });
            })();
        </script>
    @endpush
</x-default-layout>
