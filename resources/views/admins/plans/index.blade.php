@extends('layouts.main')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col order-0">
            <div class="card mb-3">
                <div class="d-flex align-items-end row">
                    <div class="col">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Plan</h5>
                            <a href="{{ route('plan.add') }}" class="btn btn-md btn-outline-primary"><span class="tf-icons bx bx-plus"></span> Add Plan</a>
                            <button type="button" class="btn btn-md btn-outline-info" data-bs-toggle="modal" data-bs-target="#importModal"><span class="tf-icons bx bx-upload"></span> Import Plan</button>
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
                        <form action="{{ route('plan.import') }}" method="POST" style="display:inline;" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-header bg-info">
                                <h5 class="modal-title text-white" id="importModalLabel">Import Plan</h5>
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
                                <table id="plansTable" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-primary">No</th>
                                            <th class="text-primary">Sequence No</th>
                                            <th class="text-primary">Type</th>
                                            <th class="text-primary">Production Date</th>
                                            <th class="text-primary">Model Name</th>
                                            <th class="text-primary">Production No</th>
                                            <th class="text-primary">Chasis No</th>
                                            <th class="text-primary">Model Label</th>
                                            <th class="text-primary">Safety Frame Label</th>
                                            <th class="text-primary">Model Mower</th>
                                            <th class="text-primary">Mower No</th>
                                            <th class="text-primary">Model Collector</th>
                                            <th class="text-primary">Collector No</th>
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
    // clone header row untuk filter
    $('#plansTable thead tr')
        .clone(true)
        .addClass('filters')
        .appendTo('#plansTable thead');

    // Tambah plugin custom sorting
    jQuery.extend(jQuery.fn.dataTable.ext.type.order, {
        "seq-pre": function (d) {
            // Ambil angka setelah huruf (contoh: T12 -> 12)
            let num = d.replace(/^[^\d]*/, ""); 
            return parseInt(num, 10) || 0;
        },
        "seq-asc": function (a, b) {
            return a - b;
        },
        "seq-desc": function (a, b) {
            return b - a;
        }
    });

    var table = $('#plansTable').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        stateSave: false,
        pageLength: 50,
        order: [[1, 'asc']],
        ajax: {
            url: '/iseki_podium/public/api/plans-data',
            type: 'GET',
            error: function (xhr, error, code) {
                console.warn("DataTables AJAX Error:", error, code);
                // ⚠️ kalau mau tampilkan alert custom:
                // toastr.error("Gagal memuat data, coba lagi.");
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
        fixedHeader: false, // ❌ matikan supaya filter input bisa diklik
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'Sequence_No_Plan', name: 'Sequence_No_Plan' },
            { data: 'Type_Plan', name: 'Type_Plan' },
            { data: 'Production_Date_Plan', name: 'Production_Date_Plan' },
            { data: 'Model_Name_Plan', name: 'Model_Name_Plan' },
            { data: 'Production_No_Plan', name: 'Production_No_Plan' },
            { data: 'Chasis_No_Plan', name: 'Chasis_No_Plan' },
            { data: 'Model_Label_Plan', name: 'Model_Label_Plan' },
            { data: 'Safety_Frame_Label_Plan', name: 'Safety_Frame_Label_Plan' },
            { data: 'Model_Mower_Plan', name: 'Model_Mower_Plan' },
            { data: 'Mower_No_Plan', name: 'Mower_No_Plan' },
            { data: 'Model_Collector_Plan', name: 'Model_Collector_Plan' },
            { data: 'Collector_No_Plan', name: 'Collector_No_Plan' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        initComplete: function () {
            var api = this.api();

            // Buat input di tiap kolom
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

                // event handler untuk filter
                $('input', cell).off().on('keyup change clear', function () {
                    if (api.column(colIdx).search() !== this.value) {
                        api.column(colIdx).search(this.value).draw();
                    }
                });
            });
        }
    });
});

// Pindahkan semua modal hasil render DataTables ke body agar tidak ketiban backdrop
$(document).on('show.bs.modal', '.modal', function () {
    $(this).appendTo('body');
});

$(document).ready(function () {
    // ... DataTables init seperti biasa ...

    // Delegated event untuk tombol delete
    $('#plansTable').on('click', '.delete-btn', function () {
        var planId = $(this).data('id');
        var planName = $(this).data('name');

        if (confirm("Do you want to delete plan " + planName + "?")) {
            $.ajax({
                url: '/iseki_podium/public/plan/delete/' + planId,
                type: 'POST',               // tetap POST
                data: {
                    _method: 'DELETE',      // Laravel akan menganggap ini DELETE
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if(res.success){
                        $('#plansTable').DataTable().ajax.reload(null, false); // reload tanpa reset page
                        alert(res.message);
                    } else {
                        alert('Failed to delete plan');
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
