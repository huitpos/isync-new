<x-default-layout>

    @section('title')
        Import POS SQLite
    @endsection

    <div class="card mb-5">
        <div class="card-body py-4">
            <p class="text-muted">
                Copy the POS SQLite file to <code>{{ $directory }}</code> on the server, then select it below.
            </p>

            <form id="importForm" class="mt-3" onsubmit="return false;">
                <div class="mb-4">
                    <label class="form-label">SQLite File</label>
                    <select name="file" class="form-control form-select" required>
                        <option value="">-- Select file --</option>
                        @foreach ($files as $file)
                            <option value="{{ $file['name'] }}">
                                {{ $file['name'] }} ({{ number_format($file['size'] / 1048576, 2) }} MB, {{ $file['modified'] }})
                            </option>
                        @endforeach
                    </select>

                    @if (empty($files))
                        <div class="form-text text-warning">No SQLite files found in the import folder.</div>
                    @endif
                </div>

                <div class="mb-4">
                    <label class="form-label">Tables</label>
                    <div class="mb-2">
                        <a href="#" id="selectAllTables">Select all</a>
                        &middot;
                        <a href="#" id="clearTables">Clear</a>
                    </div>
                    <div class="row">
                        @foreach ($tables as $table)
                            <div class="col-md-3 col-sm-6">
                                <div class="form-check mb-2">
                                    <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="{{ $table }}" id="table_{{ $table }}" {{ $table === 'endOfDay' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="table_{{ $table }}">{{ $table }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Branch ID override (optional)</label>
                    <input name="branch_id" type="number" class="form-control" placeholder="Leave blank to use branchId from the SQLite"/>
                </div>

                <div class="mb-4">
                    <label class="form-label">POS Machine ID override (optional)</label>
                    <input name="pos_machine_id" type="number" class="form-control" placeholder="Leave blank to use machineNumber / machineId from the SQLite"/>
                </div>

                <div id="formError" class="alert alert-danger d-none"></div>

                <button type="button" id="verifyButton" class="btn btn-primary">Verify</button>
            </form>
        </div>
    </div>

    <div id="resultCard" class="card mb-5 d-none">
        <div class="card-body py-4">
            <h4 class="mb-4">
                <span id="resultTitle"></span>
                <span id="resultBadge" class="badge ms-2"></span>
            </h4>

            <div class="table-responsive mb-4">
                <table class="table table-row-bordered table-sm align-middle">
                    <thead>
                        <tr class="fw-bold">
                            <th>Table</th>
                            <th>Status</th>
                            <th class="text-end">Rows read</th>
                            <th class="text-end" data-label-verify="To insert" data-label-import="Inserted">To insert</th>
                            <th class="text-end" data-label-verify="To update" data-label-import="Updated">To update</th>
                            <th class="text-end">Unchanged</th>
                            <th class="text-end">Failed</th>
                            <th data-label-verify="Fields to update" data-label-import="Fields updated">Fields to update</th>
                            <th>Ignored columns</th>
                        </tr>
                    </thead>
                    <tbody id="resultRows"></tbody>
                </table>
            </div>

            <div id="importNotice" class="alert d-none"></div>

            <div id="confirmWrapper" class="d-none">
                <button type="button" id="confirmButton" class="btn btn-success">Confirm Import</button>
                <span id="confirmNote" class="text-danger ms-3"></span>
                <div class="form-text">Each table is saved separately. If a table fails, the tables before it stay saved; importing the same file again is safe.</div>
            </div>
        </div>
    </div>

    <div id="samplesCard" class="card mb-5 d-none">
        <div class="card-body py-4">
            <h5 class="mb-4">Sample changes (first 20 inserts and 20 updates per table)</h5>
            <div class="table-responsive">
                <table class="table table-row-bordered table-sm align-middle">
                    <thead>
                        <tr class="fw-bold">
                            <th>Table</th>
                            <th>SQLite ID</th>
                            <th>Keys</th>
                            <th>treg</th>
                            <th>Action</th>
                            <th>Changes</th>
                        </tr>
                    </thead>
                    <tbody id="sampleRows"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="failuresCard" class="card d-none">
        <div class="card-body py-4">
            <h5 class="mb-4">Failed rows</h5>
            <div class="table-responsive">
                <table class="table table-row-bordered table-sm align-middle">
                    <thead>
                        <tr class="fw-bold">
                            <th>Table</th>
                            <th>SQLite ID</th>
                            <th>Error</th>
                        </tr>
                    </thead>
                    <tbody id="failureRows"></tbody>
                </table>
            </div>
            <div id="failuresMore" class="text-muted"></div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(function () {
            const verifyUrl = '{{ route('admin.pos-sqlite-import.verify-table') }}';
            const importUrl = '{{ route('admin.pos-sqlite-import.import-table') }}';
            const $form = $('#importForm');
            const MAX_SAMPLES = 20;
            const MAX_FAILURES = 200;
            let state = null;
            let busy = false;

            const formatNumber = (value) => Number(value || 0).toLocaleString();

            function formValues() {
                return {
                    file: $form.find('[name=file]').val(),
                    branch_id: $form.find('[name=branch_id]').val(),
                    pos_machine_id: $form.find('[name=pos_machine_id]').val(),
                    tables: $form.find('.table-checkbox:checked').map((i, el) => el.value).get(),
                };
            }

            function errorMessage(xhr) {
                const json = xhr.responseJSON || {};
                if (json.errors) {
                    return Object.values(json.errors).flat().join(' ');
                }
                return json.message || ('Request failed (' + xhr.status + (xhr.status === 504 ? ', server timeout' : '') + ')');
            }

            function setBusy(value) {
                busy = value;
                $form.find('input, select, button').prop('disabled', value);
                $('#confirmButton').prop('disabled', value);
            }

            function setMode(mode) {
                $('#resultCard thead th[data-label-' + mode + ']').each(function () {
                    $(this).text($(this).data('label-' + mode));
                });
            }

            function setStatus(table, text, cssClass) {
                $('#row_' + table + ' .status').html($('<span class="badge">').addClass(cssClass).text(text));
            }

            function renderRow(table, summary) {
                const $row = $('#row_' + table);

                if (!summary.source) {
                    $row.find('.counts').remove();
                    $row.append($('<td colspan="7" class="counts text-muted">').text('Not found in SQLite'));
                    return;
                }

                $row.find('.counts').remove();
                $row.append(
                    $('<td class="counts text-end">').text(formatNumber(summary.total)),
                    $('<td class="counts text-end text-success">').text(formatNumber(summary.inserted)),
                    $('<td class="counts text-end text-primary">').text(formatNumber(summary.updated)),
                    $('<td class="counts text-end text-muted">').text(formatNumber(summary.unchanged)),
                    $('<td class="counts text-end text-danger">').text(formatNumber(summary.failed)),
                    $('<td class="counts small">').text(
                        Object.entries(summary.changed_columns || {})
                            .sort((a, b) => b[1] - a[1])
                            .map(([column, count]) => column + ' (' + formatNumber(count) + ')')
                            .join(', ')
                    ),
                    $('<td class="counts text-muted small">').text((summary.ignored_columns || []).join(', ')),
                );
            }

            function displayValue(value) {
                return value === null || value === undefined ? 'NULL' : String(value);
            }

            function renderChanges(changes) {
                const $list = $('<div class="small">');

                Object.entries(changes || {}).forEach(function ([column, change]) {
                    $list.append($('<div class="text-break">').append(
                        $('<span class="fw-bold">').text(column + ': '),
                        $('<span class="text-danger text-decoration-line-through">').text(displayValue(change.old)),
                        $('<span class="text-muted">').text(' → '),
                        $('<span class="text-success">').text(displayValue(change.new)),
                    ));
                });

                return $list;
            }

            function emptySummary() {
                return { source: null, source_total: 0, total: 0, inserted: 0, updated: 0, unchanged: 0, failed: 0, changed_columns: {}, ignored_columns: [] };
            }

            function mergeSummary(summary, batch) {
                summary.source = batch.source;
                summary.source_total = batch.source_total;
                ['total', 'inserted', 'updated', 'unchanged', 'failed'].forEach((key) => summary[key] += batch[key]);
                Object.entries(batch.changed_columns || {}).forEach(function ([column, count]) {
                    summary.changed_columns[column] = (summary.changed_columns[column] || 0) + count;
                });
                summary.ignored_columns = [...new Set(summary.ignored_columns.concat(batch.ignored_columns || []))];
            }

            function appendDetails(table, response) {
                if (response.dry_run) {
                    (response.summary.samples || []).forEach(function (sample) {
                        if ($('#sampleRows tr[data-table="' + table + '"][data-action="' + sample.action + '"]').length >= MAX_SAMPLES) {
                            return;
                        }

                        const keys = Object.entries(sample.keys).map(([key, value]) => key + '=' + value).join(', ');
                        const isInsert = sample.action === 'insert';
                        $('#sampleRows').append($('<tr>').attr({ 'data-table': table, 'data-action': sample.action }).append(
                            $('<td>').text(table),
                            $('<td>').text(sample.source_id ?? ''),
                            $('<td class="small">').text(keys),
                            $('<td>').text(sample.treg ?? ''),
                            $('<td>').append($('<span class="badge">').addClass(isInsert ? 'badge-light-success' : 'badge-light-primary').text(isInsert ? 'Insert' : 'Update')),
                            $('<td>').append(isInsert ? '' : renderChanges(sample.changes)),
                        ));
                    });
                }

                state.hiddenFailures += response.failure_count - response.failures.length;

                response.failures.forEach(function (failure) {
                    if ($('#failureRows tr').length >= MAX_FAILURES) {
                        state.hiddenFailures++;
                        return;
                    }

                    $('#failureRows').append($('<tr>').attr('data-table', table).append(
                        $('<td>').text(table),
                        $('<td>').text(failure.source_id ?? ''),
                        $('<td class="text-break text-danger small">').text(failure.error),
                    ));
                });

                $('#failuresMore').text(state.hiddenFailures > 0 ? formatNumber(state.hiddenFailures) + ' more failed rows not shown.' : '');
                $('#samplesCard').toggleClass('d-none', $('#sampleRows tr').length === 0);
                $('#failuresCard').toggleClass('d-none', $('#failureRows tr').length === 0);
            }

            function resetResults(values) {
                $('#resultRows, #sampleRows, #failureRows').empty();
                $('#samplesCard, #failuresCard, #confirmWrapper, #importNotice').addClass('d-none');
                $('#failuresMore').text('');

                values.tables.forEach(function (table) {
                    $('#resultRows').append($('<tr>').attr('id', 'row_' + table).append(
                        $('<td>').text(table),
                        $('<td class="status">'),
                    ));
                    setStatus(table, 'Waiting', 'badge-light');
                });

                $('#resultCard').removeClass('d-none');
            }

            function showNotice(message, cssClass) {
                $('#importNotice').removeClass('d-none alert-success alert-warning alert-danger').addClass(cssClass).text(message);
            }

            async function runTables(url, tables, mode) {
                const label = mode === 'verify' ? 'Verifying...' : 'Importing...';

                for (const table of tables) {
                    const summary = emptySummary();
                    let afterRowId = 0;

                    state.summaries[table] = summary;
                    $('#sampleRows tr[data-table="' + table + '"], #failureRows tr[data-table="' + table + '"]').remove();
                    setStatus(table, label, 'badge-light-info');

                    try {
                        do {
                            const response = await $.ajax({
                                url: url,
                                method: 'POST',
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content'),
                                    file: state.file,
                                    table: table,
                                    after_row_id: afterRowId,
                                    branch_id: state.branch_id,
                                    pos_machine_id: state.pos_machine_id,
                                    fingerprint: state.fingerprint,
                                },
                            });

                            if (mode === 'verify') {
                                state.fingerprints.add(response.fingerprint);
                                state.fingerprint = response.fingerprint;
                            }

                            mergeSummary(summary, response.summary);
                            renderRow(table, summary);
                            appendDetails(table, response);

                            afterRowId = response.summary.next_row_id;

                            if (afterRowId) {
                                setStatus(table, label + ' ' + formatNumber(summary.total) + ' / ' + formatNumber(summary.source_total), 'badge-light-info');
                            }
                        } while (afterRowId);

                        if (!summary.source) {
                            setStatus(table, 'Skipped', 'badge-light');
                        } else {
                            setStatus(table, mode === 'verify' ? 'Verified' : 'Imported', mode === 'verify' ? 'badge-light-primary' : 'badge-light-success');
                        }
                    } catch (xhr) {
                        state.errors[table] = errorMessage(xhr);

                        if (summary.total > 0) {
                            state.errors[table] += ' (stopped after ' + formatNumber(summary.total) + ' of ' + formatNumber(summary.source_total) + ' rows)';
                        }

                        setStatus(table, 'Error', 'badge-light-danger');
                        $('#row_' + table + ' .status').append($('<div class="small text-danger mt-1">').text(state.errors[table]));

                        if (xhr.status === 409) {
                            return false;
                        }
                    }
                }

                return true;
            }

            $('#selectAllTables').on('click', function (e) {
                e.preventDefault();
                $('.table-checkbox').prop('checked', true).trigger('change');
            });

            $('#clearTables').on('click', function (e) {
                e.preventDefault();
                $('.table-checkbox').prop('checked', false).trigger('change');
            });

            $form.on('change input', 'input, select', function () {
                if (!busy && state) {
                    $('#confirmWrapper').addClass('d-none');
                    showNotice('The form changed. Verify again before importing.', 'alert-warning');
                    state = null;
                }
            });

            $('#verifyButton').on('click', async function () {
                const values = formValues();
                $('#formError').addClass('d-none');

                if (!values.file || values.tables.length === 0) {
                    $('#formError').removeClass('d-none').text('Select a file and at least one table.');
                    return;
                }

                state = Object.assign({}, values, {
                    fingerprint: null,
                    fingerprints: new Set(),
                    summaries: {},
                    errors: {},
                    hiddenFailures: 0,
                });

                setMode('verify');
                resetResults(values);
                $('#resultTitle').text('Verification of ' + values.file);
                $('#resultBadge').attr('class', 'badge badge-light-warning ms-2').text('Nothing has been saved yet');

                setBusy(true);
                await runTables(verifyUrl, values.tables, 'verify');
                setBusy(false);

                if (state.fingerprints.size > 1) {
                    showNotice('The file changed while it was being verified. Verify again.', 'alert-danger');
                    return;
                }

                const summaries = Object.values(state.summaries);
                const changes = summaries.reduce((sum, s) => sum + s.inserted + s.updated, 0);
                const failed = summaries.reduce((sum, s) => sum + s.failed, 0);
                const errorTables = Object.keys(state.errors);

                if (changes === 0) {
                    showNotice(errorTables.length ? 'Some tables could not be verified.' : 'Nothing to insert or update.', errorTables.length ? 'alert-danger' : 'alert-success');
                    return;
                }

                const notes = [];
                if (failed > 0) notes.push(formatNumber(failed) + ' failed rows will be skipped.');
                if (errorTables.length) notes.push('Tables with errors will not be imported: ' + errorTables.join(', ') + '.');

                $('#confirmNote').text(notes.join(' '));
                $('#confirmWrapper').removeClass('d-none');
            });

            $('#confirmButton').on('click', async function () {
                if (!state) {
                    return;
                }

                const tables = state.tables.filter((table) => {
                    const summary = state.summaries[table];
                    return summary && !state.errors[table] && (summary.inserted + summary.updated) > 0;
                });
                const changes = tables.reduce((sum, table) => sum + state.summaries[table].inserted + state.summaries[table].updated, 0);

                if (!confirm('Save ' + formatNumber(changes) + ' inserted/updated records across ' + tables.length + ' table(s)?')) {
                    return;
                }

                state.tables.filter((table) => !tables.includes(table)).forEach(function (table) {
                    if (!state.errors[table]) {
                        setStatus(table, 'No changes', 'badge-light');
                    }
                });

                setMode('import');
                $('#confirmWrapper').addClass('d-none');
                $('#samplesCard').addClass('d-none');
                $('#sampleRows').empty();
                $('#failuresMore').text('');
                state.hiddenFailures = 0;
                $('#resultTitle').text('Importing ' + state.file);
                $('#resultBadge').attr('class', 'badge badge-light-info ms-2').text('In progress');
                state.errors = {};

                setBusy(true);
                const completed = await runTables(importUrl, tables, 'import');
                setBusy(false);

                const errorTables = Object.keys(state.errors);
                $('#resultTitle').text('Import of ' + state.file);

                if (!completed) {
                    $('#resultBadge').attr('class', 'badge badge-light-danger ms-2').text('Stopped');
                    showNotice('Import stopped because the file changed after verification. Tables marked Imported, and the rows counted before the error, were saved. Verify again to continue.', 'alert-danger');
                } else if (errorTables.length) {
                    $('#resultBadge').attr('class', 'badge badge-light-warning ms-2').text('Finished with errors');
                    showNotice('These tables had errors: ' + errorTables.join(', ') + '. Rows counted before the error were saved; verify and import the file again to finish them.', 'alert-warning');
                } else {
                    $('#resultBadge').attr('class', 'badge badge-light-success ms-2').text('Saved');
                    showNotice('Import complete.', 'alert-success');
                }

                state = null;
            });
        });
    </script>
    @endpush

</x-default-layout>
