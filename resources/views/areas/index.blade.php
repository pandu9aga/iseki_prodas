@extends('layouts.area')

@section('content')

    <!-- Filter Tanggal dan Export -->
    <div class="row mb-3">
        <div class="col-md-5">
            <div class="row">
                <div class="col-12 mb-2">
                    <!-- Kolom untuk Total Scans Card -->
                    @if(session('Name_Area') === 'LINE A')
                        <div class="row">
                            <div class="col-6">
                                <div class="card bg-label-primary">
                                    <div class="card-body text-center p-2">
                                        <h6 class="mb-0">Unit</h6>
                                        <h2 class="text-primary mb-0" id="unitTotalCount">
                                            {{ number_format($unitScanCount ?? 0) }}
                                        </h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-label-success">
                                    <div class="card-body text-center p-2">
                                        <h6 class="mb-0">Mocol</h6>
                                        <h2 class="text-success mb-0" id="mocolTotalCount">
                                            {{ number_format($mocolScanCount ?? 0) }}
                                        </h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card">
                            <div class="card-body text-center">
                                <h5 class="mb-0">Total:</h5>
                                <h1 class="text-primary mb-0">{{ number_format($scanCount) }}</h1>
                                <small class="text-muted">
                                    @if ($selectedDate)
                                        {{ \Carbon\Carbon::parse($selectedDate)->isoFormat('D MMMM Y') }}
                                    @else
                                        All Dates
                                    @endif
                                </small>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="col-12">
                    <div class="card">
                        <form method="GET">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row g-3 align-items-end">
                                        <!-- Kolom untuk Filter Tanggal -->
                                        <div class="col-md-8">
                                            <label for="scan_date" class="form-label">Scan Date</label>
                                            <div class="input-group">
                                                <input type="date" name="scan_date" id="scan_date" class="form-control"
                                                    value="{{ request('scan_date', \Carbon\Carbon::today()->toDateString()) }}">
                                                <button type="submit" class="btn btn-outline-primary">Apply</button>
                                            </div>
                                        </div>

                                        <!-- Kolom untuk Tombol Export -->
                                        <div class="col-md-4">
                                            <label class="form-label">&nbsp;</label>
                                            <div>
                                                @if(request('scan_date'))
                                                    <a href="{{ route('area.report.export', ['scan_date' => request('scan_date')]) }}"
                                                        class="btn btn-success w-100">
                                                        <i class='bx bx-file'></i> Export Excel
                                                    </a>
                                                @else
                                                    <a href="{{ route('area.report.export', ['scan_date' => \Carbon\Carbon::today()->toDateString()]) }}"
                                                        class="btn btn-success w-100">
                                                        <i class='bx bx-file'></i> Export Excel
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card">
                <h5 class="card-header text-primary">Area: {{ $areaName }}</h5>
                <div class="card-body">
                    <!-- Alert untuk notifikasi kesalahan -->
                    <div id="validationAlert" class="alert alert-danger d-none" role="alert">
                        <span id="validationMessage"></span>
                    </div>

                    <form id="scanForm" action="{{ route('area.scan.store') }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-2">
                                        <label class="form-label">Scan Type:</label>
                                    </div>

                                    {{-- Kondisi 1: Area adalah MOWER -> Hanya tampilkan MOCOL dan checked --}}
                                    @if(session('Name_Area') === 'MOWER')
                                        <div class="col-2 form-check">
                                            <input class="form-check-input" type="radio" name="scan_type" id="scanMocol"
                                                value="mocol" checked>
                                            <label class="form-check-label" for="scanMocol">
                                                MOCOL
                                            </label>
                                        </div>
                                    @elseif(session('Name_Area') === 'LINE A')
                                        {{-- Kondisi 2: Area adalah LINE A -> Tampilkan UNIT (default checked) dan MOCOL --}}
                                        <div class="col-2 form-check">
                                            <input class="form-check-input" type="radio" name="scan_type" id="scanUnit"
                                                value="unit" checked>
                                            <label class="form-check-label" for="scanUnit">
                                                UNIT
                                            </label>
                                        </div>
                                        <div class="col-2 form-check">
                                            <input class="form-check-input" type="radio" name="scan_type" id="scanMocol"
                                                value="mocol">
                                            <label class="form-check-label" for="scanMocol">
                                                MOCOL
                                            </label>
                                        </div>
                                    @else
                                        {{-- Kondisi 3: Area selain MOWER, LINE A -> Hanya tampilkan UNIT dan checked --}}
                                        <div class="col-2 form-check">
                                            <input class="form-check-input" type="radio" name="scan_type" id="scanUnit"
                                                value="unit" checked>
                                            <label class="form-check-label" for="scanUnit">
                                                UNIT
                                            </label>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-2">
                                <input type="text" id="rawInput" class="form-control form-control-sm mb-2"
                                    placeholder="Scan QR here" autocomplete="off" autofocus>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <input type="text" class="form-control form-control-sm" id="sequence_no_display"
                                    placeholder="Seq No" readonly />
                            </div>
                            <div class="col-12">
                                <input type="text" class="form-control form-control-sm" id="production_date_display"
                                    placeholder="Date" readonly />
                            </div>
                            <div class="col-12">
                                <input type="text" class="form-control form-control-sm" id="model_display"
                                    placeholder="Model" readonly />
                            </div>
                            <div class="col-12">
                                <input type="text" class="form-control form-control-sm" id="production_no_display"
                                    placeholder="Prod No" readonly />
                            </div>
                        </div>

                        <button type="button" id="scanButton" class="btn btn-outline-primary w-100 mb-2">
                            Scan QR (Camera)
                        </button>
                        <div id="qr-reader" style="margin-top:15px; display: none;"></div>

                        <!-- Hidden inputs untuk form submission -->
                        <input type="hidden" id="sequence_no" name="sequence_no" />
                        <input type="hidden" id="production_date" name="production_date" />

                        <button type="submit" id="submitBtn" class="btn btn-primary w-100 mt-4" disabled
                            style="display:none;">
                            Submit Scan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-3">
        <!-- Tractor Types Card -->
        <div class="col-md-4">
            @if(session('Name_Area') === 'LINE A')
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3"><i class='bx bx-car'></i> Tipe Unit Terscan</h5>
                        <div id="unitTractorTypesContainer">
                            <p class="text-muted text-center">Memuat data...</p>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-success mb-3"><i class='bx bx-wrench'></i> Tipe Mocol Terscan</h5>
                        <div id="mocolTractorTypesContainer">
                            <p class="text-muted text-center">Memuat data...</p>
                        </div>
                    </div>
                </div>
            @elseif(session('Name_Area') === 'MOWER')
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3"><i class='bx bx-car'></i> Unit (<span
                                id="headerDateUnit"></span>)</h5>
                        <div id="unitTypesContainer">
                            <p class="text-muted text-center">Memuat data...</p>
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title text-success mb-3"><i class='bx bx-cut'></i> T - Mower (<span
                                id="headerDateMower"></span>)</h5>
                        <div id="mowerTypesContainer">
                            <p class="text-muted text-center">Memuat data...</p>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-warning mb-3"><i class='bx bx-box'></i> T - Collector (<span
                                id="headerDateCollector"></span>)</h5>
                        <div id="collectorTypesContainer">
                            <p class="text-muted text-center">Memuat data...</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">Tipe Traktor Terscan
                            ({{ \Carbon\Carbon::parse($selectedDate)->locale('id')->isoFormat('D MMMM Y') }})</h5>
                        <div id="tractorTypesContainer">
                            <p class="text-muted text-center">Memuat data...</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Tabel Scans -->
        <div class="col-md-8">
            @if(session('Name_Area') === 'LINE A')
                <!-- LINE A: Dual Tables with Tabs -->
                <ul class="nav nav-tabs mb-3" id="lineATabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="unit-tab" data-bs-toggle="tab" data-bs-target="#unit-table-pane"
                            type="button" role="tab" aria-controls="unit-table-pane" aria-selected="true">
                            <i class='bx bx-car'></i> UNIT SCANS <span class="badge bg-primary" id="unitCount">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="mocol-tab" data-bs-toggle="tab" data-bs-target="#mocol-table-pane"
                            type="button" role="tab" aria-controls="mocol-table-pane" aria-selected="false">
                            <i class='bx bx-wrench'></i> MOCOL SCANS <span class="badge bg-success" id="mocolCount">0</span>
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="lineATabContent">
                    <!-- Unit Table -->
                    <div class="tab-pane fade show active" id="unit-table-pane" role="tabpanel" aria-labelledby="unit-tab">
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="table-responsive text-nowrap">
                                    <table id="unitTable" class="table table-bordered table-sm" style="font-size: 12px;">
                                        <thead>
                                            <tr>
                                                <th class="text-primary">No</th>
                                                <th class="text-primary">Sequence</th>
                                                <th class="text-primary">Model Name</th>
                                                <th class="text-primary">Type</th>
                                                <th class="text-primary">Hour</th>
                                                <th class="text-primary">Production</th>
                                                <th class="text-primary">Date</th>
                                                <th class="text-primary">Time Scan</th>
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
                    <!-- Mocol Table -->
                    <div class="tab-pane fade" id="mocol-table-pane" role="tabpanel" aria-labelledby="mocol-tab">
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="table-responsive text-nowrap">
                                    <table id="mocolTable" class="table table-bordered table-sm" style="font-size: 12px;">
                                        <thead>
                                            <tr>
                                                <th class="text-success">No</th>
                                                <th class="text-success">Sequence</th>
                                                <th class="text-success">Model Name</th>
                                                <th class="text-success">Type</th>
                                                <th class="text-success">Hour</th>
                                                <th class="text-success">Production</th>
                                                <th class="text-success">Date</th>
                                                <th class="text-success">Time Scan</th>
                                                <th class="text-success">Chasis No</th>
                                                <th class="text-success">Model Label</th>
                                                <th class="text-success">Safety Frame Label</th>
                                                <th class="text-success">Model Mower</th>
                                                <th class="text-success">Mower No</th>
                                                <th class="text-success">Model Collector</th>
                                                <th class="text-success">Collector No</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Other Areas: Single Table -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="table-responsive text-nowrap">
                            <table id="reportsTable" class="table table-bordered table-sm" style="font-size: 12px;">
                                <thead>
                                    <tr>
                                        <th class="text-primary">No</th>
                                        <th class="text-primary">Sequence</th>
                                        <th class="text-primary">Model Name</th>
                                        <th class="text-primary">Type</th>
                                        <th class="text-primary">Hour</th>
                                        <th class="text-primary">Production</th>
                                        <th class="text-primary">Date</th>
                                        <th class="text-primary">Time Scan</th>
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
            @endif
        </div>
    </div>

    <!-- Modal Validasi -->
    <div class="modal fade" id="validationModal" tabindex="-1" aria-labelledby="validationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="validationModalLabel">Validasi Gagal</h5>
                </div>
                <div class="modal-body">
                    <h4 id="validationModalMessage">Terjadi kesalahan.</h4>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Success -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-white" id="successModalLabel">Scan Berhasil</h5>
                </div>
                <div class="modal-body">
                    <h4 id="successModalMessage"></h4>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script src="{{ asset('assets/js/html5-qrcode.min.js') }}"></script>
    <script>
            $(document).ready(function () {
                var isLineA = {{ session('Name_Area') === 'LINE A' ? 'true' : 'false' }};
                var table, unitTable, mocolTable;

                // Common variables
                var validTypes = ['GC', 'GNT', 'GNTDAI', 'MF', 'MFDAI', 'MFE', 'MFEDAI', 'NT', 'NTDAI', 'SF2', 'SF5', 'SUSXG2', 'SXG2', 'SXG2日本', 'SXG3', 'TLE', 'TLEDAI', 'TXGS'];
                var typeColors = { 'GC': '#FFB3BA', 'GNT': '#BAFFC9', 'GNTDAI': '#BAE1FF', 'MF': '#E0BBE4', 'MFDAI': '#D291BC', 'MFE': '#957DAD', 'MFEDAI': '#FEC89A', 'NT': '#F7D794', 'NTDAI': '#A8E6CF', 'SF2': '#FFDAC1', 'SF5': '#B5EAD7', 'SUSXG2': '#C7CEEA', 'SXG2': '#B8E0D2', 'SXG2日本': '#FFDFD3', 'SXG3': '#E2F0CB', 'TLE': '#D4F0F0', 'TLEDAI': '#F6EAC2', 'TXGS': '#E7C6FF' };
                var columns = [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'Sequence_No_Plan', name: 'scans.Sequence_No_Plan' },
                    { data: 'Model_Name_Plan', name: 'plans.Model_Name_Plan' },
                    { data: 'Type_Plan', name: 'plans.Type_Plan', render: function (data) { var bgColor = validTypes.includes(data) ? (typeColors[data] || '#D3D3D3') : '#D3D3D3'; return '<span class="badge" style="background-color: ' + bgColor + '; color: black;">' + data + '</span>'; } },
                    { data: 'Assigned_Hour_Scan', name: 'scans.Assigned_Hour_Scan', searchable: false }, // Aggregate
                    { data: 'Production_No_Plan', name: 'plans.Production_No_Plan' },
                    { data: 'Production_Date_Plan', name: 'scans.Production_Date_Plan' },
                    { data: 'Time_Scan', name: 'scans.Time_Scan', searchable: false }, // Aggregate
                    { data: 'Chasis_No_Plan', name: 'plans.Chasis_No_Plan' },
                    { data: 'Model_Label_Plan', name: 'plans.Model_Label_Plan' },
                    { data: 'Safety_Frame_Label_Plan', name: 'plans.Safety_Frame_Label_Plan' },
                    { data: 'Model_Mower_Plan', name: 'plans.Model_Mower_Plan' },
                    { data: 'Mower_No_Plan', name: 'plans.Mower_No_Plan' },
                    { data: 'Model_Collector_Plan', name: 'plans.Model_Collector_Plan' },
                    { data: 'Collector_No_Plan', name: 'plans.Collector_No_Plan' }
                ];


                if (isLineA) {
                    // LINE A: Dual tables
                    unitTable = $('#unitTable').DataTable({
                        processing: true, serverSide: true, deferRender: true, pageLength: 50, order: [[7, 'desc']],
                        ajax: { 
                            url: "{{ route('api.area.reports.data') }}", 
                            type: 'GET', 
                            data: function (d) { d.scan_date = $('#scan_date').val(); d.scan_type = 'unit'; }, 
                            dataSrc: function (json) { 
                                $('#unitCount').text(json.recordsFiltered); 
                                $('#unitTotalCount').text(json.recordsFiltered.toLocaleString());
                                return json.data; 
                            } 
                        },
                        scrollX: true, scrollY: "400px", scrollCollapse: true, columns: columns, 
                        initComplete: function () { loadTractorTypesLineA(); }
                    });
                    mocolTable = $('#mocolTable').DataTable({
                        processing: true, serverSide: true, deferRender: true, pageLength: 50, order: [[7, 'desc']],
                        ajax: { 
                            url: "{{ route('api.area.reports.data') }}", 
                            type: 'GET', 
                            data: function (d) { d.scan_date = $('#scan_date').val(); d.scan_type = 'mocol'; }, 
                            dataSrc: function (json) { 
                                $('#mocolCount').text(json.recordsFiltered); 
                                $('#mocolTotalCount').text(json.recordsFiltered.toLocaleString());
                                return json.data; 
                            } 
                        },
                        scrollX: true, scrollY: "400px", scrollCollapse: true, columns: columns
                    });
                    $('#lineATabs button').on('shown.bs.tab', function () { unitTable.columns.adjust(); mocolTable.columns.adjust(); });
                    unitTable.on('draw', loadTractorTypesLineA); mocolTable.on('draw', loadTractorTypesLineA);
                    function loadTractorTypesLineA() { 
                        var unitTc = {}; 
                        unitTable.rows().data().each(function (r) { if (r.Type_Plan) unitTc[r.Type_Plan] = (unitTc[r.Type_Plan] || 0) + 1; }); 
                        renderTractorTypes(unitTc, '#unitTractorTypesContainer');

                        var mocolTc = {}; 
                        mocolTable.rows().data().each(function (r) { if (r.Type_Plan) mocolTc[r.Type_Plan] = (mocolTc[r.Type_Plan] || 0) + 1; }); 
                        renderTractorTypes(mocolTc, '#mocolTractorTypesContainer');
                    }
                } else {
                    // Other areas: Single table
                    $('#reportsTable thead tr').clone(true).addClass('filters').appendTo('#reportsTable thead');

                    var table = $('#reportsTable').DataTable({
                        processing: true,
                        serverSide: true,
                        deferRender: true,
                        pageLength: 50,
                        order: [
                            [7, 'desc']
                        ],
                        ajax: {
                            url: "{{ route('api.area.reports.data') }}",
                            type: 'GET',
                            data: function (d) {
                                d.scan_date = $('#scan_date').val();
                            },
                            error: function (xhr, error, code) {
                                console.warn("DataTables AJAX Error:", error, code);
                            }
                        },
                        scrollX: true,
                        scrollY: "400px",
                        scrollCollapse: true,
                        orderCellsTop: true,
                        columns: [
                            {
                                data: 'DT_RowIndex',
                                name: 'DT_RowIndex',
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: 'Sequence_No_Plan',
                                name: 'Sequence_No_Plan'
                            },
                            {
                                data: 'Model_Name_Plan',
                                name: 'Model_Name_Plan'
                            },
                            {
                                data: 'Type_Plan',
                                name: 'Type_Plan',
                                render: function (data, type, row, meta) {
                                    var validTypes = [
                                        'GC', 'GNT', 'GNTDAI', 'MF', 'MFDAI', 'MFE', 'MFEDAI', 'NT', 'NTDAI',
                                        'SF2', 'SF5', 'SUSXG2', 'SXG2', 'SXG2日本', 'SXG3', 'TLE', 'TLEDAI', 'TXGS'
                                    ];

                                    var typeColors = {
                                        'GC': '#FFB3BA',
                                        'GNT': '#BAFFC9',
                                        'GNTDAI': '#BAE1FF',
                                        'MF': '#E0BBE4',
                                        'MFDAI': '#D291BC',
                                        'MFE': '#957DAD',
                                        'MFEDAI': '#FEC89A',
                                        'NT': '#F7D794',
                                        'NTDAI': '#A8E6CF',
                                        'SF2': '#FFDAC1',
                                        'SF5': '#B5EAD7',
                                        'SUSXG2': '#C7CEEA',
                                        'SXG2': '#B8E0D2',
                                        'SXG2日本': '#FFDFD3',
                                        'SXG3': '#E2F0CB',
                                        'TLE': '#D4F0F0',
                                        'TLEDAI': '#F6EAC2',
                                        'TXGS': '#E7C6FF'
                                    };

                                    var bgColor = '#D3D3D3';
                                    if (validTypes.includes(data)) {
                                        bgColor = typeColors[data] || bgColor;
                                    }

                                    return '<span class="badge" style="background-color: ' + bgColor + '; color: black;">' + data + '</span>';
                                },
                            },
                            {
                                data: 'Assigned_Hour_Scan',
                                name: 'Assigned_Hour_Scan'
                            },
                            {
                                data: 'Production_No_Plan',
                                name: 'Production_No_Plan'
                            },
                            {
                                data: 'Production_Date_Plan',
                                name: 'Production_Date_Plan'
                            },
                            {
                                data: 'Time_Scan',
                                name: 'Time_Scan'
                            },
                            {
                                data: 'Chasis_No_Plan',
                                name: 'Chasis_No_Plan'
                            },
                            {
                                data: 'Model_Label_Plan',
                                name: 'Model_Label_Plan'
                            },
                            {
                                data: 'Safety_Frame_Label_Plan',
                                name: 'Safety_Frame_Label_Plan'
                            },
                            {
                                data: 'Model_Mower_Plan',
                                name: 'Model_Mower_Plan'
                            },
                            {
                                data: 'Mower_No_Plan',
                                name: 'Mower_No_Plan'
                            },
                            {
                                data: 'Model_Collector_Plan',
                                name: 'Model_Collector_Plan'
                            },
                            {
                                data: 'Collector_No_Plan',
                                name: 'Collector_No_Plan'
                            }
                        ],
                        initComplete: function () {
                            var api = this.api();
                            api.columns().eq(0).each(function (colIdx) {
                                var cell = $('.filters th').eq($(api.column(colIdx).header()).index());
                                var title = $(cell).text();

                                const noFilterColumns = ["No"];

                                if (!noFilterColumns.includes(title)) {
                                    $(cell).html('<input type="text" placeholder="Search ' + title +
                                        '" class="form-control form-control-sm" style="width:100%; padding:2px 4px; font-size:12px;"/>'
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

                            loadTractorTypes();
                        }
                    });
                } // End else block for non-LINE A areas

                // QR Code Scanner
                let html5QrcodeScanner;
                const scanButton = document.getElementById('scanButton');
                const qrReaderElement = document.getElementById('qr-reader');
                const rawInput = document.getElementById('rawInput');
                const sequenceNoInput = document.getElementById('sequence_no');
                const productionDateInput = document.getElementById('production_date');
                const sequenceNoDisplay = document.getElementById('sequence_no_display');
                const productionDateDisplay = document.getElementById('production_date_display');
                const modelDisplay = document.getElementById('model_display');
                const productionNoDisplay = document.getElementById('production_no_display');
                const submitBtn = document.getElementById('submitBtn');
                const scanForm = document.getElementById('scanForm');
                const validationModal = new bootstrap.Modal(document.getElementById('validationModal'));
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));

                let scanComplete = false;

                function resetScan() {
                    sequenceNoInput.value = '';
                    productionDateInput.value = '';
                    sequenceNoDisplay.value = '';
                    productionDateDisplay.value = '';
                    modelDisplay.value = '';
                    productionNoDisplay.value = '';
                    rawInput.value = '';
                    scanComplete = false;
                    submitBtn.disabled = true;
                    submitBtn.style.display = 'none';
                }

                function showValidationModal(message) {
                    resetScan();
                    document.getElementById('validationModalMessage').textContent = message;
                    validationModal.show();
                    setTimeout(() => {
                        validationModal.hide();
                        rawInput.focus();
                    }, 3000);
                }

                function processScan(parts, autoSubmit = false) {
                    if (scanComplete) return;

                    if (parts.length >= 4) {
                        sequenceNoInput.value = parts[0].trim();
                        productionDateInput.value = parts[1].trim();
                        sequenceNoDisplay.value = parts[0].trim();
                        productionDateDisplay.value = parts[1].trim();
                        modelDisplay.value = parts[2].trim();
                        productionNoDisplay.value = parts[3].trim();

                        scanComplete = true;
                        submitBtn.disabled = false;
                        submitBtn.style.display = 'block';

                        // Auto submit jika dari camera scanner
                        if (autoSubmit) {
                            setTimeout(() => {
                                scanForm.submit();
                            }, 500);
                        }
                    } else {
                        showValidationModal('Format QR Code tidak valid');
                    }
                }

                // USB Scanner Input
                rawInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const rawValue = this.value.trim();
                        if (!rawValue) return;

                        const parts = rawValue.split(';');
                        processScan(parts, true); // Manual submit
                        this.value = '';
                    }
                });

                // Camera Scanner Button
                scanButton.addEventListener('click', function () {
                    if (html5QrcodeScanner) {
                        html5QrcodeScanner.clear().then(() => {
                            html5QrcodeScanner = null;
                            qrReaderElement.style.display = 'none';
                            rawInput.focus();
                        }).catch(console.error);
                        return;
                    }

                    qrReaderElement.style.display = 'block';

                    html5QrcodeScanner = new Html5QrcodeScanner(
                        'qr-reader', {
                        fps: 10,
                        qrbox: { width: 300, height: 300 },
                    }
                    );

                    function onScanSuccess(decodedText, decodedResult) {
                        const parts = decodedText.split(';');
                        processScan(parts, true); // Auto submit dari camera

                        html5QrcodeScanner.clear().then(() => {
                            html5QrcodeScanner = null;
                            qrReaderElement.style.display = 'none';
                        }).catch(console.error);
                    }

                    html5QrcodeScanner.render(onScanSuccess, function (errorMessage) {
                        console.warn("QR Code scan error:", errorMessage);
                    });
                });

                // Session messages
                @if(session('success'))
                    document.getElementById('successModalMessage').textContent = '{{ session("success") }}';
                    successModal.show();
                    setTimeout(() => {
                        successModal.hide();
                        resetScan();
                        table.draw();
                        rawInput.focus();
                    }, 3000);
                @endif

                @if(session('error'))
                    showValidationModal('{{ session("error") }}');
                @endif

                // --- FOKUS KE RAW INPUT SAAT RADIO BUTTON DIKLIK ---
                document.querySelectorAll('input[name="scan_type"]').forEach(radio => {
                    radio.addEventListener('change', function () {
                        // Setelah radio dipilih, fokuskan ke rawInput
                        rawInput.focus();
                    });
                });
                // --- AKHIR FOKUS ---

                // Focus on load
                setTimeout(() => {
                    rawInput.focus();
                }, 300);

                function loadTractorTypes() {
                    var typeCount = {};
                    var unitCount = {};
                    var mowerCount = {};
                    var collectorCount = {};

                    // Cek nama area dari session (passed from backend or inline PHP)
                    var areaName = "{{ session('Name_Area') }}";

                    // Update headers if MOWER
                    if (areaName === 'MOWER') {
                        // Assuming date is already available in JS from previous header code or input
                         // But for now, just reusing logic if needed or updating specific spans
                        var dateStr = $('#scan_date').val(); // Assuming input id
                         if(dateStr){
                            var dateObj = new Date(dateStr);
                            var options = { year: 'numeric', month: 'long', day: 'numeric' };
                            var formattedKey = dateObj.toLocaleDateString('id-ID', options);
                             $('#headerDateUnit').text(formattedKey);
                             $('#headerDateMower').text(formattedKey);
                             $('#headerDateCollector').text(formattedKey);
                         }
                    }

                    table.rows().data().each(function (row) {
                        var type = row.Type_Plan;
                        var sequenceNo = row.Sequence_No_Plan || '';
                        var modelName = row.Model_Name_Plan || '';
                        var modelMower = row.Model_Mower_Plan || '';
                        var modelCollector = row.Model_Collector_Plan || '';

                        if (type) {
                            if (areaName === 'MOWER') {
                                if (!sequenceNo.includes('T') && !sequenceNo.includes('t')) {
                                    unitCount[type] = (unitCount[type] || 0) + 1;
                                } else if (modelName === modelMower) {
                                    mowerCount[type] = (mowerCount[type] || 0) + 1;
                                } else if (modelName === modelCollector) {
                                    collectorCount[type] = (collectorCount[type] || 0) + 1;
                                }
                            } else {
                                typeCount[type] = (typeCount[type] || 0) + 1;
                            }
                        }
                    });

                    if (areaName === 'MOWER') {
                        renderTractorTypes(unitCount, '#unitTypesContainer');
                        renderTractorTypes(mowerCount, '#mowerTypesContainer');
                        renderTractorTypes(collectorCount, '#collectorTypesContainer');
                    } else {
                        renderTractorTypes(typeCount);
                    }
                }

                // Shared function for rendering tractor types
                function renderTractorTypes(typeCount, containerId) {
                    var targetContainer = containerId || '#tractorTypesContainer';
                    var html = '';
                    if (Object.keys(typeCount).length > 0) {
                        html = '<div class="row">';
                        Object.keys(typeCount).sort().forEach(function (type) {
                            var bgColor = validTypes.includes(type) ? (typeColors[type] || '#D3D3D3') : '#D3D3D3';
                            html += '<div class="col-lg-6 mb-2">';
                            html += '<div class="card text-center border-1 shadow-none" style="border-radius: 0.25rem;">';
                            html += '<div class="card-body p-2 d-flex flex-column">';
                            html += '<div class="row">';
                            html += '<div class="col-lg-5">';
                            html += '<img src="{{ asset("assets/img/tractors") }}' + '/' + type + '.png" alt="' + type + '" class="img-fluid mb-1" style="width: 100%; max-height: 50px; object-fit: contain;">';
                            html += '</div>';
                            html += '<div class="col-lg-7">';
                            html += '<span class="badge mb-1" style="background-color: ' + bgColor + '; color: black; font-size: 0.8rem; padding: 0.25rem 0.5rem;">' + type + '</span>';
                            html += '<p class="card-text text-black mb-0" style="font-size: 1.2rem; font-weight: 500;">' + typeCount[type] + '</p>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                        });
                        html += '</div>';
                    } else {
                        html = '<p class="text-muted mb-0">Belum ada scan untuk tanggal ini.</p>';
                    }
                    $(targetContainer).html(html);
                }
            });
        </script>
@endsection