@extends('layouts.main')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col order-0">
            <div class="card mb-3">
                <div class="d-flex align-items-end row">
                    <div class="col">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Lineoff</h5>
                        </div>
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
        // 1. Clone baris header untuk filter
        var headerOriginal = $('#plansTable thead tr').first();
        var headerClone = headerOriginal.clone();
        headerClone.addClass('filters');
        headerClone.find('th').empty(); // Kosongkan sel filter
        headerClone.appendTo('#plansTable thead');

        var table = $('#plansTable').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            stateSave: false,
            pageLength: 50,
            order: [[1, 'asc']],
            ajax: {
                url: '/iseki_podium/public/api/report/lineoffs-data',
                type: 'GET',
                error: function (xhr, error, code) {
                    console.warn("DataTables AJAX Error:", error, code);
                }
            },
            scrollX: true,
            scrollY: "500px",
            scrollCollapse: true,
            orderCellsTop: true, // Aktifkan untuk menggunakan baris filter di atas
            fixedColumns: {
                leftColumns: 2
            },
            fixedHeader: false,
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'Sequence_No_Plan', name: 'Sequence_No_Plan' },
                { data: 'Lineoff_Plan', name: 'Lineoff_Plan' },
                { data: 'Process', name: 'Process' },
                { data: 'Type_Plan', name: 'Type_Plan' },
                { data: 'Model_Name_Plan', name: 'Model_Name_Plan' },
                { data: 'Production_Date_Plan', name: 'Production_Date_Plan' },
                { data: 'Production_No_Plan', name: 'Production_No_Plan' },
                { data: 'Chasis_No_Plan', name: 'Chasis_No_Plan' },
                { data: 'Model_Label_Plan', name: 'Model_Label_Plan' },
                { data: 'Safety_Frame_Label_Plan', name: 'Safety_Frame_Label_Plan' },
                { data: 'Model_Mower_Plan', name: 'Model_Mower_Plan' },
                { data: 'Mower_No_Plan', name: 'Mower_No_Plan' },
                { data: 'Model_Collector_Plan', name: 'Model_Collector_Plan' },
                { data: 'Collector_No_Plan', name: 'Collector_No_Plan' },
            ],
            initComplete: function () {
                var api = this.api();

                // Iterasi kolom untuk membuat input filter
                api.columns().every(function (colIdx) {
                    var column = this;
                    // Ambil judul dari header asli (bukan filter)
                    var title = $('#plansTable thead tr:eq(0) th').eq(colIdx).text().trim();

                    // Cek apakah kolom ini bisa difilter
                    // Abaikan kolom "No", "Process"
                    if (['No'].includes(title)) {
                         // Biarkan sel filter kosong untuk kolom ini
                         $('.filters th').eq(colIdx).html('');
                         return; // Lanjutkan ke kolom berikutnya
                    }

                    // Ambil sel filter untuk kolom ini
                    var cell = $('.filters th').eq(colIdx);

                    // Tambahkan input ke sel filter
                    cell.html(
                        '<input type="text" placeholder="Search ' + title + '" ' +
                        'class="form-control form-control-sm" style="width:100%; padding:2px 4px; font-size:12px;"/>'
                    );

                    // Tambahkan event handler pencarian ke input
                    var input = cell.find('input');
                    input.off().on('keyup change clear', function () {
                        if (column.search() !== this.value) {
                            column.search(this.value).draw();
                        }
                    });
                });
            }
        });
    });
</script>
@endsection