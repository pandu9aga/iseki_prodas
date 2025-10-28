@extends('layouts.main')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col order-0">
            <div class="card mb-3">
                <div class="d-flex align-items-end row">
                    <div class="col">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Rule</h5>
                            <a href="{{ route('rule.add') }}" class="btn btn-md btn-outline-primary"><span class="tf-icons bx bx-plus"></span> Add Rule</a>
                            <button type="button" class="btn btn-md btn-outline-info" data-bs-toggle="modal" data-bs-target="#importModal"><span class="tf-icons bx bx-upload"></span> Import Rule</button>
                            @if(session('success'))
                                <p class="text-success mt-2">{{ session('success') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <form action="{{ route('rule.import') }}" method="POST" style="display:inline;" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-header bg-info">
                                <h5 class="modal-title text-white" id="importModalLabel">Import Rule</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col mb-3">
                                        <label for="excel" class="form-label">Upload Excel</label>
                                        <input type="file" id="excel" name="excel" class="form-control" />
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Cancel
                                </button>
                                <button type="submit" class="btn btn-info">Import</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="d-flex align-items-end row">
                    <div class="col">
                        <div class="card-body">
                            <div class="table-responsive text-nowrap">
                                <table id="rulesTable" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-primary">No</th>
                                            <th class="text-primary">Type</th>
                                            <th class="text-primary">Rule</th>
                                            <th class="text-primary">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('style')
<link href="{{asset('assets/css/datatables.min.css')}}" rel="stylesheet">
<link href="{{asset('assets/css/fixedColumns.dataTables.min.css')}}" rel="stylesheet">

@endsection

@section('script')
<script src="{{asset('assets/js/datatables.min.js')}}"></script>
<script src="{{asset('assets/js/dataTables.fixedColumns.min.js')}}"></script>

<script>
$(document).ready(function () {
    // Clone header untuk filter
    $('#rulesTable thead tr')
        .clone(true)
        .addClass('filters')
        .appendTo('#rulesTable thead');

    // Custom sorting (opsional, bisa dipertahankan)
    jQuery.extend(jQuery.fn.dataTable.ext.type.order, {
        "seq-pre": function (d) {
            let num = d.replace(/^[^\d]*/, "");
            return parseInt(num, 10) || 0;
        }
    });

    // Fungsi untuk merender Rule_Rule sebagai daftar bernomor
    function renderRuleList(data, type, row) {
        if (!data || data === '{}' || data === '[]' || data === 'null') {
            return '<span class="text-muted">Tidak ada rule</span>';
        }

        try {
            const rules = JSON.parse(data); // ✅ sekarang ini valid JSON string
            if (typeof rules !== 'object' || rules === null || Array.isArray(rules)) {
                return '<span class="text-muted">Invalid</span>';
            }

            const sortedEntries = Object.entries(rules).sort((a, b) => {
                return parseInt(a[0]) - parseInt(b[0]);
            });

            if (sortedEntries.length === 0) {
                return '<span class="text-muted">Tidak ada rule</span>';
            }

            let html = '<ol class="mb-0 ps-3" style="font-size:0.9rem;">';
            sortedEntries.forEach(([key, value]) => {
                html += `<li class="text-secondary">${value}</li>`;
            });
            html += '</ol>';
            return html;
        } catch (e) {
            console.error("Error parsing rule:", data, e);
            return '<span class="text-danger">Error parsing rule</span>';
        }
    }

    var table = $('#rulesTable').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        stateSave: false,
        pageLength: 200, // default 200
        lengthMenu: [
            [50, 100, 200, -1],
            [50, 100, 200, "All"]
        ],
        order: [[1, 'asc']],
        ajax: {
            url: '/iseki_podium/public/api/rules-data',
            type: 'GET',
            error: function (xhr, error, code) {
                console.warn("DataTables AJAX Error:", error, code);
            }
        },
        scrollX: true,
        scrollY: "500px",
        scrollCollapse: true,
        fixedColumns: {
            leftColumns: 2,
            rightColumns: 1
        },
        orderCellsTop: true,
        fixedHeader: false,
        columns: [
            { 
                data: 'DT_RowIndex', 
                name: 'DT_RowIndex', 
                orderable: false, 
                searchable: false 
            },
            { 
                data: 'Type_Rule', 
                name: 'Type_Rule' 
            },
            { 
                data: 'Rule_Rule', 
                name: 'Rule_Rule',
                render: renderRuleList // ✅ render sebagai daftar bernomor
            },
            { 
                data: 'action', 
                name: 'action', 
                orderable: false, 
                searchable: false 
            },
        ],
        initComplete: function () {
            var api = this.api();
            api.columns().eq(0).each(function (colIdx) {
                var cell = $('.filters th').eq($(api.column(colIdx).header()).index());
                var title = $(cell).text();

                if (title !== "No" && title !== "Action") {
                    $(cell).html(
                        '<input type="text" placeholder="Search ' + title + '" ' +
                        'class="form-control form-control-sm" style="width:100%; padding:2px 4px; font-size:12px;"/>'
                    );
                } else {
                    $(cell).html('');
                }

                $('input', cell).off().on('keyup change clear', function () {
                    if (api.column(colIdx).search() !== this.value) {
                        api.column(colIdx).search(this.value).draw();
                    }
                });
            });
        }
    });
});

// Modal handling
$(document).on('show.bs.modal', '.modal', function () {
    $(this).appendTo('body');
});

// Delete handler
$(document).ready(function () {
    $('#rulesTable').on('click', '.delete-btn', function () {
        var ruleId = $(this).data('id');
        var ruleName = $(this).data('name');

        if (confirm("Do you want to delete rule " + ruleName + "?")) {
            $.ajax({
                url: '/iseki_podium/public/rule/delete/' + ruleId,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if(res.success){
                        $('#rulesTable').DataTable().ajax.reload(null, false);
                        alert(res.message);
                    } else {
                        alert('Failed to delete rule');
                    }
                },
                error: function(xhr) {
                    alert('Error: ' + xhr.statusText);
                }
            });
        }
    });
});
</script>
@endsection