@extends('layouts.area')

@section('content')

    <!-- Filter Tanggal dan Export -->
    <div class="row mb-3">
        <div class="col-md-5">
            <div class="row">
                <div class="col-12 mb-1">
                    <div class="card shadow-sm border-0">
                        <div class="card-body text-center p-3">
                            <h6 class="mb-1 text-muted">TOTAL DAISHA SCANS</h6>
                            <h1 id="totalScanCount" class="text-primary fw-bold mb-3 shadow-text">
                                {{ number_format($scanCount) }}
                            </h1>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="bg-label-success p-2 rounded border border-success border-opacity-25">
                                        <div class="d-flex align-items-center justify-content-center mb-1">
                                            <i class='bx bxs-check-circle me-1'></i>
                                            <small class="fw-bold">OK</small>
                                        </div>
                                        <h3 id="okScanCount" class="mb-0 text-success">{{ number_format($okCount) }}
                                        </h3>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-label-danger p-2 rounded border border-danger border-opacity-25">
                                        <div class="d-flex align-items-center justify-content-center mb-1">
                                            <i class='bx bxs-x-circle me-1'></i>
                                            <small class="fw-bold">NG</small>
                                        </div>
                                        <h3 id="ngScanCount" class="mb-0 text-danger">{{ number_format($ngCount) }}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class='bx bx-calendar-event me-1'></i>
                                {{ \Carbon\Carbon::parse($selectedDate)->isoFormat('D MMMM Y') }}
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-12 mb-1">
                    <div class="card">
                        <form method="GET">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row m-0 p-0 align-items-end">
                                        <div class="col-md-8">
                                            <label for="scan_date" class="form-label">Scan Date</label>
                                            <div class="input-group">
                                                <input type="date" name="scan_date" id="scan_date" class="form-control"
                                                    value="{{ request('scan_date', \Carbon\Carbon::today()->toDateString()) }}">
                                                <button type="submit" class="btn btn-outline-primary">Apply</button>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">&nbsp;</label>
                                            <div>
                                                <a href="{{ route('area.daisha.report.export', ['scan_date' => request('scan_date', \Carbon\Carbon::today()->toDateString())]) }}"
                                                    class="btn btn-success w-100">
                                                    <i class='bx bx-file'></i> Export
                                                </a>
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
                <h5 class="card-header text-primary">Area: DAISHA SET</h5>
                <div class="card-body">
                    <div id="validationAlert" class="alert alert-danger d-none" role="alert">
                        <span id="validationMessage"></span>
                    </div>

                    <form id="scanForm" action="{{ route('area.daisha.scan.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-12 mb-2">
                                <input type="text" id="rawInput" class="form-control form-control-sm mb-2"
                                    placeholder="Scan QR here (Format: Sequence;ProductionDate)" autocomplete="off"
                                    autofocus>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12 mb-1">
                                <input type="text" class="form-control form-control-sm" id="sequence_no_display"
                                    placeholder="Seq No" readonly />
                            </div>
                            <div class="col-12 mb-1">
                                <input type="text" class="form-control form-control-sm" id="production_date_display"
                                    placeholder="Date" readonly />
                            </div>
                            <div class="col-12 mb-1">
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

                        <input type="hidden" id="sequence_no" name="sequence_no" />
                        <input type="hidden" id="production_date" name="production_date" />
                        <input type="hidden" id="is_stay" name="is_stay" value="0" />
                        <input type="hidden" id="remark" name="remark" />

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
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title text-primary mb-2"><i class='bx bx-car'></i> Tractor Types</h5>
                    <div id="typeCountsContainer">
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
                                    <th>No</th>
                                    <th>Prod Date</th>
                                    <th>Seq</th>
                                    <th>Type</th>
                                    <th>Scan Time</th>
                                    <th>Status</th>
                                    <th>Remark</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal NG -->
    <div class="modal fade" id="ngModal" tabindex="-1" aria-labelledby="ngModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="ngModalLabel">NOTIFIKASI NG</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <h4 id="ngMessage" class="text-danger mb-4"></h4>
                    <div id="remarkSection" style="display: none;">
                        <label for="modalRemark" class="form-label">Alasan Remark:</label>
                        <textarea id="modalRemark" class="form-control mb-3" rows="3"
                            placeholder="Masukkan alasan..."></textarea>
                        <button type="button" id="confirmNgBtn" class="btn btn-danger w-100">Submit NG</button>
                    </div>
                    <div id="ngInitialButtons">
                        <button type="button" id="stayInputBtn" class="btn btn-warning mb-2 w-100">Tetap Input</button>
                        <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Batal</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Success -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-white" id="successModalLabel">Berhasil</h5>
                </div>
                <div class="modal-body text-center">
                    <h4 id="successModalMessage"></h4>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script src="{{ asset('assets/js/html5-qrcode.min.js') }}"></script>
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

            var table = $('#reportsTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 50,
                order: [[4, 'desc']],
                ajax: {
                    url: "{{ route('api.area.daisha.reports.data') }}",
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
                    loadTypeCounts();
                }
            });

            function loadTypeCounts() {
                var counts = {};
                var okTotal = 0;
                var ngTotal = 0;

                table.rows().data().each(function (row) {
                    var type = row.Type_Plan;
                    counts[type] = (counts[type] || 0) + 1;

                    if (row.status === 'OK') {
                        okTotal++;
                    } else {
                        ngTotal++;
                    }
                });

                // Update Overview Card
                $('#totalScanCount').text(okTotal + ngTotal);
                $('#okScanCount').text(okTotal);
                $('#ngScanCount').text(ngTotal);

                var html = '<div class="row">';
                Object.keys(counts).sort().forEach(function (type) {
                    var bgColor = validTypes.includes(type) ? (typeColors[type] || '#D3D3D3') : '#D3D3D3';
                    html += '<div class="col-6 mb-2">';
                    html += '<div class="card text-center border shadow-none p-2">';
                    html += '<div class="row align-items-center">';
                    html += '<div class="col-4">';
                    html += '<img src="{{ asset("assets/img/tractors") }}/' + type + '.png" alt="' + type + '" class="img-fluid" style="max-height: 40px; object-fit: contain;">';
                    html += '</div>';
                    html += '<div class="col-8 text-start">';
                    html += '<span class="badge mb-1" style="background-color: ' + bgColor + '; color: black; font-size: 0.7rem;">' + type + '</span>';
                    html += '<h5 class="mb-0">' + counts[type] + '</h5>';
                    html += '</div></div>';
                    html += '</div></div>';
                });
                html += '</div>';
                if (html === '<div class="row"></div>') html = '<p class="text-muted text-center">Belum ada data.</p>';
                $('#typeCountsContainer').html(html);
            }

            const ngModal = new bootstrap.Modal(document.getElementById('ngModal'));
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));

            function resetScan() {
                $('#sequence_no').val('');
                $('#production_date').val('');
                $('#sequence_no_display').val('');
                $('#production_date_display').val('');
                $('#model_display').val('');
                $('#production_no_display').val('');
                $('#rawInput').val('').focus();
                $('#submitBtn').hide().prop('disabled', true);
                $('#is_stay').val('0');
                $('#remark').val('');
                $('#modalRemark').val('');
                $('#remarkSection').hide();
                $('#ngInitialButtons').show();
            }

            // Handle NG Modal close
            $('#ngModal').on('hidden.bs.modal', function () {
                if ($('#is_stay').val() === '0') {
                    resetScan();
                }
            });

            function processScan(qrText) {
                const parts = qrText.split(';');
                if (parts.length < 2) return;

                const seq = parts[0].trim();
                const date = parts[1].trim();

                $.ajax({
                    url: "{{ route('api.area.daisha.reports.data') }}",
                    data: { sequence_no: seq, production_date: date, search_only: true, scan_date: $('#scan_date').val() },
                    success: function (response) {
                        // We need a way to get plan details even if not scanned in Daisha yet.
                        // I'll reuse the scanStore's logic but for verification.
                        // Actually, I'll just post directly to scan.store and handle NG response.
                        $('#sequence_no').val(seq);
                        $('#production_date').val(date);

                        // For display, we might want to get plan info first. 
                        // Let's modify the controller to provide plan info if search_only.
                        // Wait, I can just use existing API if I modify it.
                        // For now, let's just submit and handle response.
                        submitScan();
                    }
                });
            }

            function submitScan() {
                const data = {
                    _token: "{{ csrf_token() }}",
                    sequence_no: $('#sequence_no').val(),
                    production_date: $('#production_date').val(),
                    is_stay: $('#is_stay').val(),
                    remark: $('#remark').val()
                };

                $.ajax({
                    url: "{{ route('area.daisha.scan.store') }}",
                    type: 'POST',
                    data: data,
                    success: function (response) {
                        if (response.status === 'NG') {
                            $('#ngMessage').text(response.message);
                            ngModal.show();
                        } else if (response.status === 'success') {
                            ngModal.hide();
                            $('#successModalMessage').text(response.message);
                            successModal.show();
                            setTimeout(() => {
                                successModal.hide();
                                resetScan();
                                table.ajax.reload(loadTypeCounts);
                            }, 2000);
                        }
                    },
                    error: function (xhr) {
                        alert('Error: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan'));
                        resetScan();
                    }
                });
            }

            $('#stayInputBtn').click(function () {
                $('#ngInitialButtons').hide();
                $('#remarkSection').show();
                $('#is_stay').val('1');
            });

            $('#confirmNgBtn').click(function () {
                const r = $('#modalRemark').val().trim();
                if (!r) {
                    alert('Harap isi alasan remark!');
                    return;
                }
                $('#remark').val(r);
                submitScan();
            });

            $('#rawInput').on('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    processScan($(this).val());
                    $(this).val('');
                }
            });

            // QR Camera Logic (simplified for brevity)
            let html5QrcodeScanner;
            $('#scanButton').click(function () {
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.clear();
                    html5QrcodeScanner = null;
                    $('#qr-reader').hide();
                    return;
                }
                $('#qr-reader').show();
                html5QrcodeScanner = new Html5QrcodeScanner("qr-reader", { fps: 10, qrbox: 250 });
                html5QrcodeScanner.render((text) => {
                    processScan(text);
                    html5QrcodeScanner.clear();
                    html5QrcodeScanner = null;
                    $('#qr-reader').hide();
                });
            });
        });
    </script>
@endsection