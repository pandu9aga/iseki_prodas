@extends('layouts.main')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col order-0">
            <div class="card mb-3">
                <div class="d-flex align-items-end row">
                    <div class="col">
                        <div class="card-body">
                            <h5 class="card-title text-danger"><i class="bx bx-error-circle me-1"></i> NG Processes (Urutan Belum Selesai)</h5>
                            <p class="text-muted mb-0">Catatan riwayat error saat proses sebelumnya belum selesai saat melakukan proses di aplikasi.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="card mb-3">
                <div class="card-body">
                    <form id="filterForm" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label font-weight-bold">Aplikasi</label>
                            <select id="filterApp" class="form-select form-select-sm">
                                <option value="">Semua Aplikasi</option>
                                <option value="iseki_podium">iseki_podium</option>
                                <option value="iseki_astra">iseki_astra</option>
                                <option value="iseki_parcom">iseki_parcom</option>
                                <option value="iseki_chadet">iseki_chadet</option>
                                <option value="iseki_oiler">iseki_oiler</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-weight-bold">Tanggal Mulai</label>
                            <input type="date" id="filterStartDate" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-weight-bold">Tanggal Akhir</label>
                            <input type="date" id="filterEndDate" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="button" id="btnFilter" class="btn btn-sm btn-primary"><i class="bx bx-filter-alt"></i> Filter</button>
                            <button type="button" id="btnReset" class="btn btn-sm btn-outline-secondary"><i class="bx bx-refresh"></i> Reset</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-3">
                <div class="d-flex align-items-end row">
                    <div class="col">
                        <div class="card-body">
                            <div class="table-responsive text-nowrap">
                                <table id="ngProcessesTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th class="text-primary" style="width: 50px;">No</th>
                                            <th class="text-primary">Aplikasi</th>
                                            <th class="text-primary">Sequence No</th>
                                            <th class="text-primary">Type</th>
                                            <th class="text-primary">Proses Saat Ini</th>
                                            <th class="text-danger">Proses Sebelumnya Yang Belum Selesai (Akun/Area Salah)</th>
                                            <th class="text-primary">Pesan Error</th>
                                            <th class="text-primary">Waktu Kejadian</th>
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
        var table = $('#ngProcessesTable').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            stateSave: false,
            pageLength: 25,
            order: [[6, 'desc']], // Urutkan berdasarkan waktu kejadian
            ajax: {
                url: '{{ route("api.admin.ng_processes.data") }}',
                type: 'GET',
                data: function (d) {
                    d.app_name = $('#filterApp').val();
                    d.start_date = $('#filterStartDate').val();
                    d.end_date = $('#filterEndDate').val();
                },
                error: function (xhr, error, code) {
                    console.warn("DataTables AJAX Error:", error, code);
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { 
                    data: 'app_name', 
                    name: 'app_name',
                    render: function (data) {
                        return '<span class="badge bg-label-info">' + (data || '-') + '</span>';
                    }
                },
                { 
                    data: 'sequence_no', 
                    name: 'sequence_no',
                    render: function (data) {
                        return '<strong>' + (data || '-') + '</strong>';
                    }
                },
                { 
                    data: 'type_plan', 
                    name: 'type_plan',
                    render: function (data) {
                        return '<span class="badge bg-label-secondary">' + (data || '-') + '</span>';
                    }
                },
                { data: 'current_process', name: 'current_process' },
                { 
                    data: 'missing_process', 
                    name: 'missing_process',
                    render: function (data) {
                        return '<span class="badge bg-label-danger font-weight-bold" style="font-size: 13px;">' + (data || '-') + '</span>';
                    }
                },
                { data: 'message', name: 'message' },
                { data: 'created_at', name: 'created_at' },
            ]
        });

        $('#btnFilter').on('click', function () {
            table.draw();
        });

        $('#btnReset').on('click', function () {
            $('#filterForm')[0].reset();
            table.draw();
        });
    });
</script>
@endsection
