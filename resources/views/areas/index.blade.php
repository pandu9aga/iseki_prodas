@extends('layouts.area')

@section('content')

<!-- Filter Tanggal dan Export -->
<div class="row mb-3">
    <div class="col-md-5">
        <div class="row">
            <div class="col-12 mb-2">
                <!-- Kolom untuk Total Scans Card -->
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
                <div class="alert alert-info" role="alert">
                    <i class='bx bx-info-circle'></i>
                    Menampilkan scan untuk area <strong>{{ $areaName }}</strong> pada tanggal yang dipilih.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-3">
    <!-- Tractor Types Card -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title text-primary mb-3">Tipe Traktor & Jumlah (Tanggal: {{ \Carbon\Carbon::parse($selectedDate)->locale('id')->isoFormat('D MMMM Y') }})</h5>
                <div id="tractorTypesContainer">
                    <p class="text-muted text-center">Memuat data...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Scans -->
    <div class="col-md-8">
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
                                <th class="text-primary">Assigned Hour</th>
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

@endsection

@section('script')
<script>
    $(document).ready(function () {
        // Clone header untuk filter kolom
        $('#reportsTable thead tr')
            .clone(true)
            .addClass('filters')
            .appendTo('#reportsTable thead');

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
                    render: function(data, type, row, meta) {
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

                // Load tractor types setelah data selesai
                loadTractorTypes();
            }
        });

        // Fungsi untuk load tractor types dari data table
        function loadTractorTypes() {
            var typeCount = {};
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

            // Hitung tipe dari semua data
            table.rows().data().each(function(row) {
                var type = row.Type_Plan;
                if (type) {
                    typeCount[type] = (typeCount[type] || 0) + 1;
                }
            });

            // Render tractor types
            var html = '';
            if (Object.keys(typeCount).length > 0) {
                html = '<div class="row">';
                Object.keys(typeCount).sort().forEach(function(type) {
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

            $('#tractorTypesContainer').html(html);
        }
    });
</script>
@endsection