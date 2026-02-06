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
                            <h5 class="mb-0">Total Scans:</h5>
                            <h1 class="text-primary mb-0">{{ number_format($scanCount) }}</h1>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($selectedDate)->isoFormat('D MMMM Y') }}
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
                                                    <a href="{{ route('area.daiichi.report.export', ['scan_date' => request('scan_date')]) }}"
                                                        class="btn btn-success w-100">
                                                        <i class='bx bx-file'></i> Export Excel
                                                    </a>
                                                @else
                                                    <a href="{{ route('area.daiichi.report.export', ['scan_date' => \Carbon\Carbon::today()->toDateString()]) }}"
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
                <h5 class="card-header text-primary">Area: DAIICHI</h5>
                <div class="card-body">
                    <!-- Alert untuk notifikasi kesalahan -->
                    <div id="validationAlert" class="alert alert-danger d-none" role="alert">
                        <span id="validationMessage"></span>
                    </div>

                    <form id="scanForm" action="{{ route('area.daiichi.scan.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-12 mb-2">
                                <input type="text" id="rawInput" class="form-control form-control-sm mb-2"
                                    placeholder="Scan QR here (Format: Sequence;ProductionDate)" autocomplete="off"
                                    autofocus>
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
        <!-- Tractor Types Card (Grouped) -->
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title text-primary mb-2"><i class='bx bx-car'></i> Unit</h5>
                    <div id="unitTypesContainer">
                        <p class="text-muted text-center">Memuat data...</p>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title text-success mb-2"><i class='bx bx-cut'></i> T - Mower</h5>
                    <div id="mowerTypesContainer">
                        <p class="text-muted text-center">Memuat data...</p>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-warning mb-2"><i class='bx bx-box'></i> T - Collector</h5>
                    <div id="collectorTypesContainer">
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
                order: [[6, 'desc']], // Kolom Time Scan
                ajax: {
                    url: "{{ route('api.area.daiichi.reports.data') }}",
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
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'Sequence_No_Plan', name: 'Sequence_No_Plan' },
                    { data: 'Model_Name_Plan', name: 'Model_Name_Plan' },
                    {
                        data: 'Type_Plan',
                        name: 'Type_Plan',
                        render: function (data, type, row, meta) {
                            var validTypes = [
                                'GC', 'GNT', 'GNTDAI', 'MF', 'MFDAI', 'MFE', 'MFEDAI', 'NT', 'NTDAI',
                                'SF2', 'SF5', 'SUSXG2', 'SXG2', 'SXG2日本', 'SXG3', 'TLE', 'TLEDAI', 'TXGS'
                            ];

                            var typeColors = {
                                'GC': '#FFB3BA', 'GNT': '#BAFFC9', 'GNTDAI': '#BAE1FF', 'MF': '#E0BBE4',
                                'MFDAI': '#D291BC', 'MFE': '#957DAD', 'MFEDAI': '#FEC89A', 'NT': '#F7D794',
                                'NTDAI': '#A8E6CF', 'SF2': '#FFDAC1', 'SF5': '#B5EAD7', 'SUSXG2': '#C7CEEA',
                                'SXG2': '#B8E0D2', 'SXG2日本': '#FFDFD3', 'SXG3': '#E2F0CB', 'TLE': '#D4F0F0',
                                'TLEDAI': '#F6EAC2', 'TXGS': '#E7C6FF'
                            };

                            var bgColor = validTypes.includes(data) ? (typeColors[data] || '#D3D3D3') : '#D3D3D3';
                            return '<span class="badge" style="background-color: ' + bgColor + '; color: black;">' + data + '</span>';
                        },
                    },
                    { data: 'Production_No_Plan', name: 'Production_No_Plan' },
                    { data: 'Production_Date_Plan', name: 'Production_Date_Plan' },
                    { data: 'Daiichi_Record', name: 'Daiichi_Record' },
                    { data: 'Chasis_No_Plan', name: 'Chasis_No_Plan' },
                    { data: 'Model_Label_Plan', name: 'Model_Label_Plan' },
                    { data: 'Safety_Frame_Label_Plan', name: 'Safety_Frame_Label_Plan' },
                    { data: 'Model_Mower_Plan', name: 'Model_Mower_Plan' },
                    { data: 'Mower_No_Plan', name: 'Mower_No_Plan' },
                    { data: 'Model_Collector_Plan', name: 'Model_Collector_Plan' },
                    { data: 'Collector_No_Plan', name: 'Collector_No_Plan' }
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

            function processScan(qrText, autoSubmit = false) {
                if (scanComplete) return;

                // Split by ; dan ambil index 0 dan 1
                const parts = qrText.split(';');

                if (parts.length < 2) {
                    showValidationModal('Format QR Code tidak valid. Harus: Sequence;ProductionDate');
                    return;
                }

                const sequenceNo = parts[0].trim();
                const productionDate = parts[1].trim();

                // AJAX untuk cari data di database
                $.ajax({
                    url: "{{ route('api.area.daiichi.reports.data') }}",
                    type: 'GET',
                    data: {
                        sequence_no: sequenceNo,
                        production_date: productionDate,
                        search_only: true
                    },
                    success: function (response) {
                        if (response.data && response.data.length > 0) {
                            const plan = response.data[0];

                            // Isi form dengan data dari database
                            sequenceNoInput.value = sequenceNo;
                            productionDateInput.value = productionDate;
                            sequenceNoDisplay.value = plan.Sequence_No_Plan;
                            productionDateDisplay.value = plan.Production_Date_Plan;
                            modelDisplay.value = plan.Model_Name_Plan;
                            productionNoDisplay.value = plan.Production_No_Plan;

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
                            showValidationModal('Data tidak ditemukan untuk Sequence: ' + sequenceNo + ' dan Production Date: ' + productionDate);
                        }
                    },
                    error: function () {
                        showValidationModal('Terjadi kesalahan saat mencari data');
                    }
                });
            }

            // USB Scanner Input
            rawInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const rawValue = this.value.trim();
                    if (!rawValue) return;

                    processScan(rawValue, true);
                    this.value = '';
                }
            });

            // Camera Scanner Button
            scanButton.addEventListener('click', function () {
                if (html5QrcodeScanner) {
                    scanButton.textContent = 'Scan QR (Camera)';
                    scanButton.classList.remove('btn-danger');
                    scanButton.classList.add('btn-outline-primary');

                    html5QrcodeScanner.clear().then(() => {
                        html5QrcodeScanner = null;
                        qrReaderElement.style.display = 'none';
                        rawInput.focus();
                    }).catch(console.error);
                    return;
                }

                qrReaderElement.style.display = 'block';
                scanButton.textContent = 'Stop Camera';
                scanButton.classList.remove('btn-outline-primary');
                scanButton.classList.add('btn-danger');

                html5QrcodeScanner = new Html5QrcodeScanner(
                    'qr-reader', {
                    fps: 10,
                    qrbox: { width: 300, height: 300 },
                    formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE]
                }
                );

                function onScanSuccess(decodedText, decodedResult) {
                    processScan(decodedText, true);

                    scanButton.textContent = 'Scan QR (Camera)';
                    scanButton.classList.remove('btn-danger');
                    scanButton.classList.add('btn-outline-primary');

                    html5QrcodeScanner.clear().then(() => {
                        html5QrcodeScanner = null;
                        qrReaderElement.style.display = 'none';
                    }).catch(console.error);
                }

                function onScanError(errorMessage) {
                    // Ignore error agar tidak spam console
                    // Error normal saat scan QR code yang tidak terbaca
                }

                html5QrcodeScanner.render(onScanSuccess, onScanError);
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

            // Focus on load
            setTimeout(() => {
                rawInput.focus();
            }, 300);

            function loadTractorTypes() {
                var unitCount = {};
                var mowerCount = {};
                var collectorCount = {};

                var validTypes = [
                    'GC', 'GNT', 'GNTDAI', 'MF', 'MFDAI', 'MFE', 'MFEDAI', 'NT', 'NTDAI',
                    'SF2', 'SF5', 'SUSXG2', 'SXG2', 'SXG2日本', 'SXG3', 'TLE', 'TLEDAI', 'TXGS'
                ];

                var typeColors = {
                    'GC': '#FFB3BA', 'GNT': '#BAFFC9', 'GNTDAI': '#BAE1FF', 'MF': '#E0BBE4',
                    'MFDAI': '#D291BC', 'MFE': '#957DAD', 'MFEDAI': '#FEC89A', 'NT': '#F7D794',
                    'NTDAI': '#A8E6CF', 'SF2': '#FFDAC1', 'SF5': '#B5EAD7', 'SUSXG2': '#C7CEEA',
                    'SXG2': '#B8E0D2', 'SXG2日本': '#FFDFD3', 'SXG3': '#E2F0CB', 'TLE': '#D4F0F0',
                    'TLEDAI': '#F6EAC2', 'TXGS': '#E7C6FF'
                };

                table.rows().data().each(function (row) {
                    var type = row.Type_Plan;
                    var sequenceNo = row.Sequence_No_Plan || '';
                    var modelName = row.Model_Name_Plan || '';
                    var modelMower = row.Model_Mower_Plan || '';
                    var modelCollector = row.Model_Collector_Plan || '';

                    if (type) {
                        // Logic grouping for Daiichi area
                        if (!sequenceNo.includes('T') && !sequenceNo.includes('t')) {
                            // Unit
                            unitCount[type] = (unitCount[type] || 0) + 1;
                        } else if (modelName === modelMower) {
                            // Mower
                            mowerCount[type] = (mowerCount[type] || 0) + 1;
                        } else if (modelName === modelCollector) {
                            // Collector
                            collectorCount[type] = (collectorCount[type] || 0) + 1;
                        } else {
                            // Fallback to Unit? Or ignore? 
                            // For now, let's treat others as Unit if they don't have T.
                            // Actually the logic provided for Mower is STRICT.
                            // But if Sequence HAS 'T' but doesn't match Mower/Collector models?
                            // It shouldn't happen in valid production data, but let's stick to the logic used in Admin view.
                        }
                    }
                });

                function renderCards(counts, containerId) {
                    var html = '';
                    if (Object.keys(counts).length > 0) {
                        html = '<div class="row">';
                        Object.keys(counts).sort().forEach(function (type) {
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
                            html += '<p class="card-text text-black mb-0" style="font-size: 1.2rem; font-weight: 500;">' + counts[type] + '</p>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                        });
                        html += '</div>';
                    } else {
                        html = '<p class="text-muted mb-0">Belum ada scan.</p>';
                    }
                    $(containerId).html(html);
                }

                renderCards(unitCount, '#unitTypesContainer');
                renderCards(mowerCount, '#mowerTypesContainer');
                renderCards(collectorCount, '#collectorTypesContainer');
            }
        });
    </script>
@endsection