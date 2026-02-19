@extends('layouts.main')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col order-0">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Daisha Set Report</h5>
                        <form id="filterForm" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="scan_date" class="form-label">Scan Date</label>
                                <input type="date" id="scan_date" class="form-control"
                                    value="{{ \Carbon\Carbon::today()->toDateString() }}">
                            </div>
                            <div class="col-md-3">
                                <button type="button" id="filterBtn" class="btn btn-primary">Filter</button>
                                <a id="exportBtn" href="#" class="btn btn-success ms-2">
                                    <i class='bx bx-download'></i> Export
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body p-4">
                                <h5 class="card-title text-primary mb-3"><i class='bx bx-car me-1'></i> Tipe Traktor Terscan
                                </h5>
                                <div id="typeCountsContainer" class="row">
                                    <p class="text-muted text-center">Memuat data...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8 text-center d-flex align-items-center justify-content-center">
                        <div class="card w-100 shadow-sm border-0">
                            <div class="card-body py-4">
                                <div class="row align-items-center">
                                    <div class="col-md-4 border-end">
                                        <h1 id="totalScanLarge" class="text-primary mb-0 fw-bold">0</h1>
                                        <p class="text-muted mb-0 small">TOTAL DAISHA SCANS</p>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="row g-3">
                                            <div class="col-6 text-center">
                                                <div
                                                    class="bg-label-success p-3 rounded shadow-sm border border-success border-opacity-10">
                                                    <div class="d-flex align-items-center justify-content-center mb-1">
                                                        <i class='bx bxs-check-circle me-1'></i>
                                                        <small class="fw-bold">OK</small>
                                                    </div>
                                                    <h2 id="okScanLarge" class="text-success mb-1">0</h2>
                                                </div>
                                            </div>
                                            <div class="col-6 text-center">
                                                <div
                                                    class="bg-label-danger p-3 rounded shadow-sm border border-danger border-opacity-10">
                                                    <div class="d-flex align-items-center justify-content-center mb-1">
                                                        <i class='bx bxs-x-circle me-1'></i>
                                                        <small class="fw-bold">NG</small>
                                                    </div>
                                                    <h2 id="ngScanLarge" class="text-danger mb-1">0</h2>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-muted mt-3 mb-0 small">
                                            <i class='bx bx-calendar me-1'></i>
                                            <span id="displayDate">Target Date</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="table-responsive text-nowrap">
                            <table id="daishaTable" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-primary">No</th>
                                        <th class="text-primary">Prod Date</th>
                                        <th class="text-primary">Sequence</th>
                                        <th class="text-primary">Type</th>
                                        <th class="text-primary">Scan Time</th>
                                        <th class="text-primary">Status</th>
                                        <th class="text-primary">Remark</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('style')
    <link href="{{asset('assets/css/datatables.min.css')}}" rel="stylesheet">
@endsection

@section('script')
    <script src="{{asset('assets/js/datatables.min.js')}}"></script>
    <script>
        $(document).ready(function () {
            var validTypes = ['GC', 'GNT', 'GNTDAI', 'MF', 'MFDAI', 'MFE', 'MFEDAI', 'NT', 'NTDAI', 'SF2', 'SF5', 'SUSXG2', 'SXG2', 'SXG2日本', 'SXG3', 'TLE', 'TLEDAI', 'TXGS'];
            var typeColors = {
                'GC': '#FFB3BA', 'GNT': '#BAFFC9', 'GNTDAI': '#BAE1FF', 'MF': '#E0BBE4',
                'MFDAI': '#D291BC', 'MFE': '#957DAD', 'MFEDAI': '#FEC89A', 'NT': '#F7D794',
                'NTDAI': '#A8E6CF', 'SF2': '#FFDAC1', 'SF5': '#B5EAD7', 'SUSXG2': '#C7CEEA',
                'SXG2': '#B8E0D2', 'SXG2日本': '#FFDFD3', 'SXG3': '#E2F0CB', 'TLE': '#D4F0F0',
                'TLEDAI': '#F6EAC2', 'TXGS': '#E7C6FF'
            };

            var table = $('#daishaTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 50,
                order: [[4, 'desc']],
                ajax: {
                    url: "{{ route('api.admin.daisha.reports.data') }}",
                    data: function (d) {
                        d.scan_date = $('#scan_date').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'Production_Date_Plan', name: 'Production_Date_Plan' },
                    { data: 'Sequence_No_Plan', name: 'Sequence_No_Plan' },
                    {
                        data: 'Type_Plan',
                        name: 'Type_Plan',
                        render: function (data) {
                            var bgColor = validTypes.includes(data) ? (typeColors[data] || '#D3D3D3') : '#D3D3D3';
                            return '<div class="d-flex align-items-center">' +
                                '<span class="badge" style="background-color: ' + bgColor + '; color: black;">' + data + '</span>' +
                                '</div>';
                        }
                    },
                    { data: 'Daisha_Record', name: 'Daisha_Record' },
                    {
                        data: 'status',
                        name: 'status',
                        render: function (data) {
                            var color = data === 'OK' ? 'success' : 'danger';
                            return '<span class="badge bg-' + color + '">' + data + '</span>';
                        }
                    },
                    { data: 'remark', name: 'remark' }
                ],
                initComplete: function () {
                    updateSummary();
                }
            });

            function updateSummary() {
                var counts = {};
                var total = table.ajax.json() ? table.ajax.json().recordsFiltered : 0;
                var okTotal = 0;
                var ngTotal = 0;

                $('#totalScanLarge').text(total);

                var scanDate = $('#scan_date').val();
                if (scanDate) {
                    var options = { year: 'numeric', month: 'long', day: 'numeric' };
                    $('#displayDate').text(new Date(scanDate).toLocaleDateString('id-ID', options));
                }

                table.rows().data().each(function (row) {
                    var type = row.Type_Plan;
                    counts[type] = (counts[type] || 0) + 1;

                    if (row.status === 'OK') {
                        okTotal++;
                    } else {
                        ngTotal++;
                    }
                });

                // Update Large Overview Card
                $('#okScanLarge').text(okTotal);
                $('#ngScanLarge').text(ngTotal);

                var html = '';
                Object.keys(counts).sort().forEach(function (type) {
                    var bgColor = validTypes.includes(type) ? (typeColors[type] || '#D3D3D3') : '#D3D3D3';
                    html += '<div class="col-6 mb-2">';
                    html += '<div class="card text-center border shadow-none p-2">';
                    html += '<div class="row align-items-center">';
                    html += '<div class="col-5 p-0 text-center">';
                    html += '<img src="{{ asset("assets/img/tractors") }}/' + type + '.png" alt="' + type + '" class="img-fluid" style="max-height: 40px; object-fit: contain;">';
                    html += '</div>';
                    html += '<div class="col-7 text-start">';
                    html += '<span class="badge mb-1" style="background-color: ' + bgColor + '; color: black; font-size: 0.7rem;">' + type + '</span>';
                    html += '<h5 class="mb-0">' + counts[type] + '</h5>';
                    html += '</div></div>';
                    html += '</div></div>';
                });

                if (html === '') html = '<p class="text-muted text-center">Belum ada data di halaman ini.</p>';
                $('#typeCountsContainer').html(html);
            }

            table.on('draw', function () {
                updateSummary();
            });

            $('#filterBtn').click(function () {
                table.draw();
                updateExportUrl();
            });

            function updateExportUrl() {
                var date = $('#scan_date').val();
                var url = "{{ route('area.daisha.report.export') }}?scan_date=" + date;
                $('#exportBtn').attr('href', url);
            }

            updateExportUrl();
        });
    </script>
@endsection