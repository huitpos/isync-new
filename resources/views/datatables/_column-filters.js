window.initColumnFilters = function (api) {
    var table = api.table().node();
    if (!table || table.getAttribute('data-column-filters') === '1') {
        return;
    }

    table.setAttribute('data-column-filters', '1');
    var head = table.querySelector('thead');
    if (!head) {
        return;
    }

    var row = document.createElement('tr');
    row.className = 'dt-column-filters';

    api.columns().every(function () {
        var column = this;
        var cell = document.createElement('th');
        row.appendChild(cell);

        if (!column.visible()) {
            cell.classList.add('d-none');
            return;
        }

        var header = column.header();
        var title = (header ? header.textContent : '').trim();
        var searchable = column.settings()[0].aoColumns[column.index()].bSearchable;
        if (!searchable || title === '' || title.toLowerCase() === 'actions') {
            return;
        }

        var input = document.createElement('input');
        input.type = 'search';
        input.className = 'form-control form-control-sm';
        input.placeholder = title;
        var timer = null;
        var apply = function () {
            if (column.search() !== input.value) {
                column.search(input.value).draw();
            }
        };
        input.addEventListener('keyup', function () {
            clearTimeout(timer);
            timer = setTimeout(apply, 300);
        });
        input.addEventListener('change', apply);
        input.addEventListener('click', function (event) {
            event.stopPropagation();
        });
        cell.appendChild(input);
    });

    head.appendChild(row);
};

window.bindRowLinks = function (api) {
    var table = api.table().node();
    if (!table || table.getAttribute('data-row-links') === '1') {
        return;
    }

    table.setAttribute('data-row-links', '1');
    table.addEventListener('click', function (event) {
        if (event.target.closest('a, button, input, select, textarea, label')) {
            return;
        }

        var row = event.target.closest('tbody tr');
        if (!row || !table.contains(row)) {
            return;
        }

        var data = api.row(row).data();
        if (data && data.view_url) {
            window.location.href = data.view_url;
        }
    });
};

window.reloadUniformTable = function (source) {
    var card = source && source.closest ? source.closest('[data-dt-card]') : null;
    var tables = card ? card.querySelectorAll('table') : document.querySelectorAll('[data-dt-card] table');
    tables.forEach(function (table) {
        if (window.LaravelDataTables && window.LaravelDataTables[table.id]) {
            window.LaravelDataTables[table.id].ajax.reload();
        } else if (window.jQuery && window.jQuery.fn.dataTable.isDataTable(table)) {
            window.jQuery(table).DataTable().draw();
        }
    });
};

if (window.jQuery) {
    window.jQuery(document).on('change apply.daterangepicker', 'select[data-dt-filter], input[data-dt-filter][data-kt-daterangepicker]', function () {
        window.reloadUniformTable(this);
    });

    window.jQuery(document).on('keyup', 'input[data-dt-filter]:not([data-kt-daterangepicker])', function () {
        var input = this;
        clearTimeout(window.__dtToolbarTimer);
        window.__dtToolbarTimer = setTimeout(function () {
            window.reloadUniformTable(input);
        }, 300);
    });
}
