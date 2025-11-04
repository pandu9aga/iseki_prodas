@extends('layouts.main')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col order-0">
            <div class="card mb-3">
                <div class="d-flex align-items-end row">
                    <div class="col">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Filter</h5>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 🔘 Filter di atas tabel --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Tipe Data</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input type-switch" type="radio" name="dataType" id="unit" value="unit" checked>
                        <label class="form-check-label" for="unit">Unit</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input type-switch" type="radio" name="dataType" id="nonunit" value="nonunit">
                        <label class="form-check-label" for="nonunit">Non-Unit</label>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Filter Nomor (Min - Max)</label>
                    <div class="input-group">
                        <input type="text" id="minNumber" class="form-control form-control-sm" placeholder="Min">
                        <input type="text" id="maxNumber" class="form-control form-control-sm" placeholder="Max">
                        <button id="applyRange" class="btn btn-primary btn-sm">Apply</button>
                    </div>
                </div>
            </div>

            {{-- 📋 Tabel --}}
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
                                            <th class="text-primary">Lineoff</th>
                                            <th class="text-primary">Process</th>
                                            <th class="text-primary">Type</th>
                                            <th class="text-primary">Model Name</th>
                                            <th class="text-primary">Production Date</th>
                                            <th class="text-primary">Production No</th>
                                            <th class="text-primary">Chasis No</th>
                                            <th class="text-primary">Model Label</th>
                                            <th class="text-primary">Safety Frame Label</th>
                                            <th class="text-primary">Model Mower</th>
                                            <th class="text-primary">Mower No</th>
                                            <th class="text-primary">Model Collector</th>
                                            <th class="text-primary">Collector No</th>
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
    // 🔹 Clone header untuk filter kolom
    var headerOriginal = $('#plansTable thead tr').first();
    var headerClone = headerOriginal.clone();
    headerClone.addClass('filters');
    headerClone.find('th').each(function () {
        $(this).html('<input type="text" placeholder="Search" class="form-control form-control-sm" style="width:100%; padding:2px 4px; font-size:12px;" />');
    });
    headerClone.appendTo('#plansTable thead');

    // 🔹 DataTable inisialisasi
    var table = $('#plansTable').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        pageLength: 50,
        order: [[1, 'asc']],
        ajax: {
            url: '{{ route("report.filters.data") }}',
            type: 'GET',
            data: function (d) {
                d.type = $('input[name="dataType"]:checked').val(); // unit / nonunit
                d.min = $('#minNumber').val();
                d.max = $('#maxNumber').val();
            },
        },
        scrollX: true,
        scrollY: "500px",
        scrollCollapse: true,
        orderCellsTop: true,
        fixedColumns: { leftColumns: 2 },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'Sequence_No_Plan' },
            { data: 'Lineoff_Plan' },
            { data: 'Process' },
            { data: 'Type_Plan' },
            { data: 'Model_Name_Plan' },
            { data: 'Production_Date_Plan' },
            { data: 'Production_No_Plan' },
            { data: 'Chasis_No_Plan' },
            { data: 'Model_Label_Plan' },
            { data: 'Safety_Frame_Label_Plan' },
            { data: 'Model_Mower_Plan' },
            { data: 'Mower_No_Plan' },
            { data: 'Model_Collector_Plan' },
            { data: 'Collector_No_Plan' },
        ],
        initComplete: function () {
            var api = this.api();
            // 🔸 Aktifkan filter per header
            api.columns().every(function (colIdx) {
                $('input', $('.filters th').eq(colIdx)).on('keyup change', function () {
                    api.column(colIdx).search(this.value).draw();
                });
            });
        }
    });

    // 🔄 Jika switch tipe berubah → reload data
    $('input[name="dataType"]').on('change', function () {
        table.ajax.reload();
    });

    // 🔍 Jika filter range diubah → reload data
    $('#applyRange').on('click', function () {
        table.ajax.reload();
    });
});
</script>
@endsection
