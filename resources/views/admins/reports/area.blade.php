@extends('layouts.main')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Area Reports</h5>
                </div>
                <div class="card-body">
                    <!-- Nav Tabs untuk Area -->
                    <ul class="nav nav-tabs" id="areaTabs" role="tablist">
                        @foreach ($areas as $area)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $loop->first ? 'active' : '' }}" 
                                    id="area-{{ $area->Id_Area }}-tab" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#area-{{ $area->Id_Area }}" 
                                    type="button" 
                                    role="tab" 
                                    aria-controls="area-{{ $area->Id_Area }}" 
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                    {{ $area->Name_Area }}
                                </button>
                            </li>
                        @endforeach
                        <!-- Tab DAIICHI (Hardcoded) -->
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" 
                                id="area-999-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#area-999" 
                                type="button" 
                                role="tab" 
                                aria-controls="area-999" 
                                aria-selected="false">
                                DAIICHI
                            </button>
                        </li>
                    </ul>

                    <h5 class="text-primary mt-4 mb-0 ms-4">Area: <span id="areaName"></span></h5>
                    <!-- Tab Content -->
                    <div class="tab-content" id="areaTabContent">
                        @foreach ($areas as $area)
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                                id="area-{{ $area->Id_Area }}" 
                                role="tabpanel" 
                                aria-labelledby="area-{{ $area->Id_Area }}-tab">
                                
                                <!-- Filter, Export, dan Tractor Types Card -->
                                <div class="row mb-3">
                                    <!-- Kolom Kiri: Filter dan Export -->
                                    <div class="col-md-8">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-8">
                                                <label for="scan_date_{{ $area->Id_Area }}" class="form-label">Scan Date</label>
                                                <div class="input-group">
                                                    <input type="date" 
                                                        name="scan_date" 
                                                        id="scan_date_{{ $area->Id_Area }}" 
                                                        class="form-control scan_date_input"
                                                        data-area-id="{{ $area->Id_Area }}"
                                                        value="{{ Carbon\Carbon::today()->toDateString() }}">
                                                    <button type="button" 
                                                        class="btn btn-outline-primary apply-date-btn" 
                                                        data-area-id="{{ $area->Id_Area }}">
                                                        Apply
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <a href="#" 
                                                    class="btn btn-success w-100 export-btn" 
                                                    data-area-id="{{ $area->Id_Area }}"
                                                    data-area-name="{{ $area->Name_Area }}">
                                                    <i class='bx bx-file'></i> Export Excel
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Kolom Kanan: Total Scan Card -->
                                    <div class="col-md-4">
                                        <div class="card text-center">
                                            <div class="card-body">
                                                <h5 class="mb-1">Total Scan</h5>
                                                <h3 class="text-primary total-scan mb-2" id="total_{{ $area->Id_Area }}">0</h3>
                                                <small class="text-muted d-block" id="totalScanDate_{{ $area->Id_Area }}">
                                                    Tanggal: -
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tractor Types Card dan Tabel Data -->
                                <div class="row mb-3">
                                    <!-- Kolom Kiri: Tractor Types Card -->
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title text-primary mb-2">Tipe Traktor Terscan (<span id="tractorTypeDate_{{ $area->Id_Area }}"></span>)</h5>
                                                <div id="tractorTypesContainer_{{ $area->Id_Area }}" class="tractor-types-container row">
                                                    <p class="text-muted text-center">Memuat data...</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Kolom Kanan: Tabel Data -->
                                    <div class="col-md-8">
                                        <div class="card mb-3">
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table id="reportsTable_{{ $area->Id_Area }}" 
                                                        class="table table-bordered table-sm area-reports-table" 
                                                        style="font-size: 12px;"
                                                        data-area-id="{{ $area->Id_Area }}">
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
                                </div>
                            </div>
                        @endforeach

                        <!-- Tab Content untuk DAIICHI (Hardcoded) -->
                        <div class="tab-pane fade" 
                            id="area-999" 
                            role="tabpanel" 
                            aria-labelledby="area-999-tab">
                            
                            <!-- Filter, Export, dan Tractor Types Card -->
                            <div class="row mb-3">
                                <!-- Kolom Kiri: Filter dan Export -->
                                <div class="col-md-8">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-8">
                                            <label for="scan_date_999" class="form-label">Scan Date</label>
                                            <div class="input-group">
                                                <input type="date" 
                                                    name="scan_date" 
                                                    id="scan_date_999" 
                                                    class="form-control scan_date_input"
                                                    data-area-id="999"
                                                    value="{{ Carbon\Carbon::today()->toDateString() }}">
                                                <button type="button" 
                                                    class="btn btn-outline-primary apply-date-btn" 
                                                    data-area-id="999">
                                                    Apply
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <a href="#" 
                                                class="btn btn-success w-100 export-btn" 
                                                data-area-id="999"
                                                data-area-name="DAIICHI">
                                                <i class='bx bx-file'></i> Export Excel
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kolom Kanan: Total Scan Card -->
                                <div class="col-md-4">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h5 class="mb-1">Total Scan</h5>
                                            <h3 class="text-primary total-scan mb-2" id="total_999">0</h3>
                                            <small class="text-muted d-block" id="totalScanDate_999">
                                                Tanggal: -
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tractor Types Card dan Tabel Data -->
                            <div class="row mb-3">
                                <!-- Kolom Kiri: Tractor Types Card -->
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title text-primary mb-2">Tipe Traktor Terscan (<span id="tractorTypeDate_999"></span>)</h5>
                                            <div id="tractorTypesContainer_999" class="tractor-types-container row">
                                                <p class="text-muted text-center">Memuat data...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kolom Kanan: Tabel Data -->
                                <div class="col-md-8">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table id="reportsTable_999" 
                                                    class="table table-bordered table-sm area-reports-table" 
                                                    style="font-size: 12px;"
                                                    data-area-id="999">
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
<link href="{{ asset('assets/css/datatables.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/fixedColumns.dataTables.min.css') }}" rel="stylesheet">
<style>
    .tractor-types-container {
        max-height: 500px;
        overflow-y: auto;
    }
    
    .tractor-type-card {
        text-align: center;
        border-radius: 0.25rem;
        border: 1px solid #dee2e6;
        margin-bottom: 10px;
        padding: 8px;
    }
    
    .tractor-type-card img {
        max-height: 60px;
        object-fit: contain;
        margin-bottom: 5px;
    }
    
    .tractor-type-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        display: inline-block;
        margin-bottom: 5px;
    }
    
    .tractor-type-count {
        font-size: 1.2rem;
        font-weight: 500;
        color: #000;
    }
</style>
@endsection

@section('script')
<script src="{{ asset('assets/js/datatables.min.js') }}"></script>
<script src="{{ asset('assets/js/dataTables.fixedColumns.min.js') }}"></script>
<script>
$(document).ready(function() {
    var tables = {};

    // Definisi tipe traktor dan warnanya
    var validTypes = ['GC', 'GNT', 'GNTDAI', 'MF', 'MFDAI', 'MFE', 'MFEDAI', 'NT', 'NTDAI', 'SF2', 'SF5', 'SUSXG2', 'SXG2', 'SXG2日本', 'SXG3', 'TLE', 'TLEDAI', 'TXGS'];
    var typeColors = {
        'GC': '#FFB3BA', 'GNT': '#BAFFC9', 'GNTDAI': '#BAE1FF', 'MF': '#E0BBE4',
        'MFDAI': '#D291BC', 'MFE': '#957DAD', 'MFEDAI': '#FEC89A', 'NT': '#F7D794',
        'NTDAI': '#A8E6CF', 'SF2': '#FFDAC1', 'SF5': '#B5EAD7', 'SUSXG2': '#C7CEEA',
        'SXG2': '#B8E0D2', 'SXG2日本': '#FFDFD3', 'SXG3': '#E2F0CB', 'TLE': '#D4F0F0',
        'TLEDAI': '#F6EAC2', 'TXGS': '#E7C6FF'
    };

    // Inisialisasi table untuk setiap area (termasuk DAIICHI dengan ID 999)
    @foreach ($areas as $area)
        tables[{{ $area->Id_Area }}] = initTable({{ $area->Id_Area }});
    @endforeach
    tables[999] = initTable(999); // DAIICHI

    // --- UPDATE NAMA AREA DI HEADER ---
    $('#areaTabs').on('shown.bs.tab', 'button[data-bs-toggle="tab"]', function (e) {
        const activeTabButton = $(e.target);
        const areaName = activeTabButton.text().trim();
        document.getElementById('areaName').textContent = areaName;
    });

    // Set nama area awal
    const initialActiveTab = $('#areaTabs button.nav-link.active');
    if (initialActiveTab.length) {
        const initialAreaName = initialActiveTab.text().trim();
        document.getElementById('areaName').textContent = initialAreaName;
    }

    function initTable(areaId) {
        var tableId = 'reportsTable_' + areaId;
        
        return $('#' + tableId).DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            pageLength: 50,
            order: [[7, 'desc']],
            ajax: {
                url: "{{ route('api.admin.area.reports.data') }}",
                type: 'GET',
                data: function(d) {
                    d.area_id = areaId;
                    d.scan_date = $('#scan_date_' + areaId).val();
                },
                dataSrc: function(json) {
                    $('#total_' + areaId).text(json.recordsFiltered);
                    updateTractorTypes(areaId, json.data);
                    return json.data;
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
                { data: 'Sequence_No_Plan', name: 'Sequence_No_Plan' },
                { data: 'Model_Name_Plan', name: 'Model_Name_Plan' },
                {
                    data: 'Type_Plan',
                    name: 'Type_Plan',
                    render: function(data) {
                        var bgColor = validTypes.includes(data) ? (typeColors[data] || '#D3D3D3') : '#D3D3D3';
                        return '<span class="badge" style="background-color: ' + bgColor + '; color: black;">' + data + '</span>';
                    }
                },
                { data: 'Assigned_Hour_Scan', name: 'Assigned_Hour_Scan' },
                { data: 'Production_No_Plan', name: 'Production_No_Plan' },
                { data: 'Production_Date_Plan', name: 'Production_Date_Plan' },
                { data: 'Time_Scan', name: 'Time_Scan' },
                { data: 'Chasis_No_Plan', name: 'Chasis_No_Plan' },
                { data: 'Model_Label_Plan', name: 'Model_Label_Plan' },
                { data: 'Safety_Frame_Label_Plan', name: 'Safety_Frame_Label_Plan' },
                { data: 'Model_Mower_Plan', name: 'Model_Mower_Plan' },
                { data: 'Mower_No_Plan', name: 'Mower_No_Plan' },
                { data: 'Model_Collector_Plan', name: 'Model_Collector_Plan' },
                { data: 'Collector_No_Plan', name: 'Collector_No_Plan' }
            ]
        });
    }

    function updateTractorTypes(areaId, data) {
        var typeCount = {};
        var scanDate = $('#scan_date_' + areaId).val();

        var dateObj = new Date(scanDate + 'T00:00:00');
        var options = { year: 'numeric', month: 'long', day: 'numeric' };
        var formattedDate = dateObj.toLocaleDateString('en-GB', options);
        var formattedDateId = dateObj.toLocaleDateString('id-ID', options);

        $('#tractorTypeDate_' + areaId).html(formattedDateId);
        $('#totalScanDate_' + areaId).html(formattedDate);
        
        data.forEach(function(row) {
            var type = row.Type_Plan;
            if (type) {
                typeCount[type] = (typeCount[type] || 0) + 1;
            }
        });

        var html = '';
        if (Object.keys(typeCount).length > 0) {
            Object.keys(typeCount).sort().forEach(function(type) {
                var bgColor = validTypes.includes(type) ? (typeColors[type] || '#D3D3D3') : '#D3D3D3';
                html += '<div class="tractor-type-card col-lg-6 mb-2">';
                html += '<div class="row">';
                html += '<div class="col-lg-5">';
                html += '<img src="{{ asset("assets/img/tractors") }}/' + type + '.png" alt="' + type + '" class="img-fluid">';
                html += '</div>';
                html += '<div class="col-lg-7">';
                html += '<span class="badge tractor-type-badge" style="background-color: ' + bgColor + '; color: black;">' + type + '</span>';
                html += '<div class="tractor-type-count">' + typeCount[type] + '</div>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
            });
        } else {
            html = '<p class="text-muted text-center">Belum ada scan untuk tanggal ini.</p>';
        }

        $('#tractorTypesContainer_' + areaId).html(html);
    }

    // Apply date filter
    $('.apply-date-btn').on('click', function() {
        var areaId = $(this).data('area-id');
        tables[areaId].draw();
    });

    // Export Excel
    $('.export-btn').on('click', function(e) {
        e.preventDefault();
        var areaId = $(this).data('area-id');
        var scanDate = $('#scan_date_' + areaId).val();
        window.location.href = "{{ route('admin.area.report.export') }}" + 
            '?area_id=' + areaId + 
            '&scan_date=' + scanDate;
    });
});
</script>
@endsection