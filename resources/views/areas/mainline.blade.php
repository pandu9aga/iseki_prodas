@extends('layouts.area')

@section('content')
{{-- <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Lineoff /</span> Report</h4> --}}

<!-- Filter Tanggal dan Export -->
<div class="row mb-3">
    <div class="col-md-5">
        <div class="row">
            <div class="col-12 mb-2">
                <!-- Kolom untuk Total Tractors Card -->
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="mb-0">Total:</h5>
                        <h1 class="text-primary mb-0">{{ number_format($totalTractors) }}</h1>
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
                                        <label for="lineoff_date" class="form-label">Scan Date</label>
                                        <div class="input-group">
                                            <input type="date" name="lineoff_date" id="lineoff_date" class="form-control"
                                                value="{{ request('lineoff_date', \Carbon\Carbon::today()->toDateString()) }}">
                                            <button type="submit" class="btn btn-outline-primary">Apply</button>
                                        </div>
                                    </div>

                                    <!-- Kolom untuk Tombol Export -->
                                    <div class="col-md-4">
                                        <label class="form-label">&nbsp;</label> <!-- Label kosong untuk alignment -->
                                        <div>
                                            @if(request('lineoff_date'))
                                                <a href="{{ route('area.mainline.report.export', ['lineoff_date' => request('lineoff_date')]) }}"
                                                    class="btn btn-success w-100">
                                                    <i class='bx bx-file'></i> Export Excel
                                                </a>
                                            @else
                                                <a href="{{ route('area.mainline.report.export', ['lineoff_date' => \Carbon\Carbon::today()->toDateString()]) }}"
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
            <h5 class="card-header text-primary">Area: MAIN LINE</h5>
            <div class="card-body">
                <!-- Alert untuk notifikasi kesalahan -->
                <div id="validationAlert" class="alert alert-danger d-none" role="alert">
                    <span id="validationMessage"></span>
                </div>

                <form id="scanForm" action="{{ route('area.mainline.scan.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <!-- Kolom Kiri (Scan 1) -->
                        <div class="col-md-6 mb-2">
                            <!-- Input tersembunyi untuk Scanner USB Scan 1 -->
                            <input type="text" id="rawInput1" class="form-control form-control-sm mb-2" placeholder="Scan QR 1 here" style="display: block;" autocomplete="off" autofocus>
                            <div class="row mb-1">
                                <div class="col-12">
                                    <input type="text" class="form-control form-control-sm" id="sequence_no_input_1" name="sequence_no" placeholder="Seq No" readonly required/>
                                </div>
                                <div class="col-12">
                                    <input type="text" class="form-control form-control-sm" id="production_date_input_1" name="production_date" placeholder="Date" readonly required/>
                                </div>
                                <div class="col-12">
                                    <input type="text" class="form-control form-control-sm" id="model_name_input_1" placeholder="Model" readonly required/>
                                </div>
                                <div class="col-12">
                                    <input type="text" class="form-control form-control-sm" id="production_no_input_1" placeholder="Prod No" readonly required/>
                                </div>
                            </div>
                            <button type="button" id="scanButton1" class="btn btn-outline-primary mt-2">
                                Scan 1 (Camera)
                            </button>
                            <div id="qr-reader-1" style="margin-top:15px; display: none;"></div>
                        </div>

                        <!-- Kolom Kanan (Scan 2) -->
                        <div class="col-md-6 mb-2">
                            <!-- Input tersembunyi untuk Scanner USB Scan 2 -->
                            <input type="text" id="rawInput2" class="form-control form-control-sm mb-2" placeholder="Scan QR 2 here" style="display: block;">
                            <div class="row mb-1">
                                <div class="col-12">
                                    <input type="text" class="form-control form-control-sm" id="sequence_no_input_2" placeholder="Seq No" readonly required/>
                                </div>
                                <div class="col-12">
                                    <input type="text" class="form-control form-control-sm" id="production_date_input_2" placeholder="Date" readonly required/>
                                </div>
                                <div class="col-12">
                                    <input type="text" class="form-control form-control-sm" id="model_name_input_2" placeholder="Model" readonly required/>
                                </div>
                                <div class="col-12">
                                    <input type="text" class="form-control form-control-sm" id="production_no_input_2" placeholder="Prod No" readonly required/>
                                </div>
                            </div>
                            <button type="button" id="scanButton2" class="btn btn-outline-primary mt-2">
                                Scan 2 (Camera)
                            </button>
                            <div id="qr-reader-2" style="margin-top:15px; display: none;"></div>
                        </div>
                    </div>

                    <button type="submit" id="submitBtn" class="btn btn-primary mt-4" disabled style="display:none;">
                        Submit
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-3">
    <!-- Tractor Types Card (New Structure) -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title text-primary mb-3">Tipe Traktor Terscan ({{ \Carbon\Carbon::parse($selectedDate)->locale('id')->isoFormat('D MMMM Y') }})</h5>
                @if ($typesWithCount->isNotEmpty())
                    <?php
                    // Definisikan tipe valid di sini
                    $validTypes = [
                        'GC', 'GNT', 'GNTDAI', 'MF', 'MFDAI', 'MFE', 'MFEDAI', 'NT', 'NTDAI',
                        'SF2', 'SF5', 'SUSXG2', 'SXG2', 'SXG2日本', 'SXG3', 'TLE', 'TLEDAI', 'TXGS'
                    ];
                    ?>
                    <div class="row">
                        @foreach ($typesWithCount as $typeData)
                            <div class="col-lg-6 mb-2">
                                <!-- Card Pipih -->
                                <div class="card text-center border-1 shadow-none" style="border-radius: 0.25rem;">
                                    <div class="card-body p-2 d-flex flex-column">
                                        <div class="row">
                                            <div class="col-lg-5">
                                                <!-- Gambar -->
                                                <img src="{{ asset('assets/img/tractors/' . $typeData->Type_Plan . '.png') }}"
                                                    alt="{{ $typeData->Type_Plan }}"
                                                    class="img-fluid mb-1" style="width: 100%; max-height: 50px; object-fit: contain;">
                                            </div>
                                            <div class="col-lg-7">
                                                <!-- Badge Type Plan -->
                                                <span class="badge mb-1" style="
                                                    @if(in_array($typeData->Type_Plan, $validTypes))
                                                        @switch($typeData->Type_Plan)
                                                            @case('GC') background-color: #FFB3BA; @break {{-- Pastel Red --}}
                                                            @case('GNT') background-color: #BAFFC9; @break {{-- Pastel Green --}}
                                                            @case('GNTDAI') background-color: #BAE1FF; @break {{-- Pastel Light Blue --}}
                                                            @case('MF') background-color: #E0BBE4; @break {{-- Pastel Lavender --}}
                                                            @case('MFDAI') background-color: #D291BC; @break {{-- Pastel Orchid --}}
                                                            @case('MFE') background-color: #957DAD; @break {{-- Pastel Purple --}}
                                                            @case('MFEDAI') background-color: #FEC89A; @break {{-- Pastel Orange --}}
                                                            @case('NT') background-color: #F7D794; @break {{-- Pastel Yellow --}}
                                                            @case('NTDAI') background-color: #A8E6CF; @break {{-- Pastel Mint --}}
                                                            @case('SF2') background-color: #FFDAC1; @break {{-- Pastel Peach --}}
                                                            @case('SF5') background-color: #B5EAD7; @break {{-- Pastel Sage --}}
                                                            @case('SUSXG2') background-color: #C7CEEA; @break {{-- Pastel Periwinkle --}}
                                                            @case('SXG2') background-color: #B8E0D2; @break {{-- Pastel Thistle --}}
                                                            @case('SXG2日本') background-color: #FFDFD3; @break {{-- Pastel Apricot --}}
                                                            @case('SXG3') background-color: #E2F0CB; @break {{-- Pastel Pear --}}
                                                            @case('TLE') background-color: #D4F0F0; @break {{-- Pastel Ice --}}
                                                            @case('TLEDAI') background-color: #F6EAC2; @break {{-- Pastel Sand --}}
                                                            @case('TXGS') background-color: #E7C6FF; @break {{-- Pastel Lilac --}}
                                                            @default background-color: #D3D3D3; @break {{-- Abu-abu Muda --}} 
                                                        @endswitch
                                                    @else
                                                        background-color: #6c757d; /* Abu-abu untuk tipe tidak valid */
                                                    @endif
                                                    color: black; font-size: 0.8rem; padding: 0.25rem 0.5rem;">
                                                    {{ $typeData->Type_Plan }}
                                                </span>
                                                <!-- Jumlah -->
                                                <p class="card-text text-black mb-0" style="font-size: 1.2rem; font-weight: 500;">{{ $typeData->count }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">Tidak ada data traktor yang ditemukan untuk tanggal ini.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Tabel -->
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
                    [6, 'desc']
                ],
                ajax: {
                    url: "{{ route('api.area.mainline.reports.data') }}",
                    type: 'GET',
                    data: function (d) {
                        d.lineoff_date = $('#lineoff_date').val();
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
                            // Definisikan tipe valid di sini
                            var validTypes = [
                                'GC', 'GNT', 'GNTDAI', 'MF', 'MFDAI', 'MFE', 'MFEDAI', 'NT', 'NTDAI',
                                'SF2', 'SF5', 'SUSXG2', 'SXG2', 'SXG2日本', 'SXG3', 'TLE', 'TLEDAI', 'TXGS'
                            ];

                            // Definisikan warna pastel unik untuk setiap tipe
                            var typeColors = {
                                'GC': '#FFB3BA',     // Pastel Red
                                'GNT': '#BAFFC9',    // Pastel Green
                                'GNTDAI': '#BAE1FF', // Pastel Light Blue
                                'MF': '#E0BBE4',     // Pastel Lavender
                                'MFDAI': '#D291BC',  // Pastel Orchid
                                'MFE': '#957DAD',    // Pastel Purple
                                'MFEDAI': '#FEC89A', // Pastel Orange
                                'NT': '#F7D794',     // Pastel Yellow
                                'NTDAI': '#A8E6CF',  // Pastel Mint
                                'SF2': '#FFDAC1',    // Pastel Peach
                                'SF5': '#B5EAD7',    // Pastel Sage
                                'SUSXG2': '#C7CEEA', // Pastel Periwinkle
                                'SXG2': '#B8E0D2',   // Pastel Thistle
                                'SXG2日本': '#FFDFD3', // Pastel Apricot
                                'SXG3': '#E2F0CB',   // Pastel Pear
                                'TLE': '#D4F0F0',    // Pastel Ice
                                'TLEDAI': '#F6EAC2', // Pastel Sand
                                'TXGS': '#E7C6FF'    // Pastel Lilac
                            };

                            // Tentukan warna berdasarkan tipe
                            var bgColor = '#D3D3D3'; // Default abu-abu muda
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
                        data: 'Lineoff_Plan',
                        name: 'Lineoff_Plan'
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
                        var cell = $('.filters th').eq($(api.column(colIdx).header())
                        .index());
                        var title = $(cell).text();
                        
                        // Daftar kolom yang ingin discroll (tanpa filter input)
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
                }
            });
        });

    </script>

    <script src="{{ asset('assets/js/html5-qrcode.min.js') }}"></script> {{-- Pastikan library ini di-load --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Ambil elemen-elemen input
            const sequenceInput1 = document.getElementById('sequence_no_input_1');
            const dateInput1 = document.getElementById('production_date_input_1');
            const modelNameInput1 = document.getElementById('model_name_input_1');
            const prodNoInput1 = document.getElementById('production_no_input_1');
            const rawInput1 = document.getElementById('rawInput1'); // Input raw untuk scanner USB Scan 1

            const sequenceInput2 = document.getElementById('sequence_no_input_2');
            const dateInput2 = document.getElementById('production_date_input_2');
            const modelNameInput2 = document.getElementById('model_name_input_2');
            const prodNoInput2 = document.getElementById('production_no_input_2');
            const rawInput2 = document.getElementById('rawInput2'); // Input raw untuk scanner USB Scan 2

            // Ambil tombol submit dan form
            const submitBtn = document.getElementById('submitBtn');
            const scanForm = document.getElementById('scanForm');
            const validationAlert = document.getElementById('validationAlert');
            const validationMessage = document.getElementById('validationMessage');

            // Scanner 1
            let html5QrcodeScanner1;
            const scanButton1 = document.getElementById('scanButton1');
            const qrReaderElement1 = document.getElementById('qr-reader-1');

            // Scanner 2
            let html5QrcodeScanner2;
            const scanButton2 = document.getElementById('scanButton2');
            const qrReaderElement2 = document.getElementById('qr-reader-2');

            const validationModalElement = document.getElementById('validationModal');
            const validationModalMessage = document.getElementById('validationModalMessage');
            const validationModalInstance = new bootstrap.Modal(validationModalElement);

            // Status scan
            let scan1Complete = false;
            let scan2Complete = false;

			let modalTimeout = null;

			function resetAllScans() {
				// Kosongkan semua input Scan 1
				sequenceInput1.value = '';
				dateInput1.value = '';
				modelNameInput1.value = '';
				prodNoInput1.value = '';
				rawInput1.value = '';

				// Kosongkan semua input Scan 2
				sequenceInput2.value = '';
				dateInput2.value = '';
				modelNameInput2.value = '';
				prodNoInput2.value = '';
				rawInput2.value = '';

				// Reset status
				scan1Complete = false;
				scan2Complete = false;

				// Disable submit
				submitBtn.disabled = true;
			}

			function showValidationModal(message, focusTarget = null) {
				resetAllScans();

				validationModalMessage.textContent = message;
				validationModalInstance.show();

				if (modalTimeout) clearTimeout(modalTimeout);

				modalTimeout = setTimeout(() => {
					validationModalInstance.hide();
					rawInput1.focus();
				}, 3000);
			}

			// --- CEK PESAN SESI UNTUK MENAMPILKAN MODAL STATUS ---
            @if(session('success'))
                // Ambil pesan dari Blade
                const statusMessage = `{{ session('success') }}`;

                // Isi pesan ke dalam modal
                document.getElementById('statusModalMessage').textContent = statusMessage;

                // Tampilkan modal
                const statusModalElement = document.getElementById('statusNotificationModal');
                const statusModalInstance = new bootstrap.Modal(statusModalElement);

                // Tambahkan event listener untuk menutup modal secara otomatis setelah 3 detik
                statusModalElement.addEventListener('shown.bs.modal', function () {
                    setTimeout(function() {
                        statusModalInstance.hide(); // Tutup modal
                    }, 3000); // 3000 milidetik = 3 detik
                });

                statusModalInstance.show();
            @endif
			@if(session('error'))
                // Ambil pesan dari Blade
                const errorMessage = `{{ session('error') }}`;
                console.log('[WITH ERROR MESSAGE]:', errorMessage);

                // Isi pesan ke dalam modal
                document.getElementById('errorModalMessage').textContent = errorMessage;

                // Tampilkan modal
                const errorModalElement = document.getElementById('errorNotificationModal');
                const errorModalInstance = new bootstrap.Modal(errorModalElement);

                // Tambahkan event listener untuk menutup modal secara otomatis setelah 3 detik
                errorModalElement.addEventListener('shown.bs.modal', function () {
                    setTimeout(function() {
                        errorModalInstance.hide(); // Tutup modal
                    }, 3000); // 3000 milidetik = 3 detik
                });

                errorModalInstance.show();
            @endif

            // --- Fungsi Validasi dan Update Status Submit ---
            function validateAndToggleSubmit() {
              if (scan1Complete && scan2Complete) {
                  // Ambil nilai dari kedua inputan
                  const val1 = {
                      seq: sequenceInput1.value.trim(),
                      date: dateInput1.value.trim(),
                      model: modelNameInput1.value.trim(),
                      prod: prodNoInput1.value.trim()
                  };
                  const val2 = {
                      seq: sequenceInput2.value.trim(),
                      date: dateInput2.value.trim(),
                      model: modelNameInput2.value.trim(),
                      prod: prodNoInput2.value.trim()
                  };

                  // Bandingkan
                  if (val1.seq === val2.seq && val1.date === val2.date && val1.model === val2.model && val1.prod === val2.prod) {
                      // Jika cocok, sembunyikan alert (jika ada), aktifkan tombol submit
                      validationAlert.classList.add('d-none'); // Pastikan alert HTML juga disembunyikan jika muncul
                      submitBtn.disabled = false;
                      // Jika validasi sukses, langsung submit form
                      return true; // Validasi sukses
                  } else {
                      // Jika tidak cocok, tampilkan modal, nonaktifkan tombol submit
                      validationModalMessage.textContent = 'Data antara Scan 1 dan Scan 2 tidak cocok. Silakan scan ulang.';
                      submitBtn.disabled = true; // Nonaktifkan tombol submit
                      return false; // Validasi gagal
                  }
              } else {
                  // Jika salah satu scan belum selesai, nonaktifkan tombol submit
                  submitBtn.disabled = true;
                  // Jika alert sedang muncul, sembunyikan karena belum selesai (opsional)
                  // Jika modal sedang terbuka, jangan tampilkan lagi
                  if (validationModalInstance._isShown) {
                      // Jangan lakukan apa-apa jika modal sedang tampil
                  } else {
                      // Jika belum selesai, sembunyikan alert dan nonaktifkan tombol
                      validationAlert.classList.add('d-none'); // Sembunyikan alert HTML jika muncul
                  }
                  return false; // Belum bisa submit
              }
            }

            // --- Fungsi Proses Scan 1 ---
            function processScan1(parts) {
				if (scan1Complete) return;

				if (parts.length >= 4) {
					sequenceInput1.value = parts[0].trim();
					dateInput1.value = parts[1].trim();
					modelNameInput1.value = parts[2].trim();
					prodNoInput1.value = parts[3].trim();

					scan1Complete = true;

					// Fokus otomatis ke Scan 2
					setTimeout(() => {
						rawInput2.focus();
					}, 500);

				} else {
					showValidationModal(
						'Format QR Code untuk Scan 1 tidak valid',
						rawInput1
					);
					scan1Complete = false;
				}
			}

            // --- Fungsi Proses Scan 2 ---
            function processScan2(parts) {
				if (scan2Complete) return;

                if (parts.length >= 4) { // Minimal 4 bagian
                    // --- KOREKSI LOGIKA COCOKAN ---
                    const values2 = parts.map(part => part.trim()); // Ambil SEMUA nilai dari scan 2
                    // Ambil nilai dari scan 1 sebagai referensi urutan dan kecocokan
                    const expectedValues = [
                        sequenceInput1.value.trim(),
                        dateInput1.value.trim(),
                        modelNameInput1.value.trim(),
                        prodNoInput1.value.trim()
                    ];

                    // Buat salinan array values2 untuk diproses (agar tidak mengubah aslinya)
                    let remainingValues2 = [...values2];
                    let matchedValues = { seq: '', date: '', model: '', prod: '' };

                    // Loop melalui setiap nilai yang diharapkan dari scan 1
                    for (let i = 0; i < expectedValues.length; i++) {
                        const expectedVal = expectedValues[i];
                        // Cari indeks nilai dari scan 1 di sisa nilai scan 2
                        const indexInRemaining = remainingValues2.indexOf(expectedVal);

                        if (indexInRemaining !== -1) {
                            // Jika ditemukan, tandai field yang cocok berdasarkan urutan di scan 1
                            if (i === 0) matchedValues.seq = expectedVal;
                            else if (i === 1) matchedValues.date = expectedVal;
                            else if (i === 2) matchedValues.model = expectedVal;
                            else if (i === 3) matchedValues.prod = expectedVal;

                            // Hapus nilai yang sudah dicocokkan dari sisa nilai scan 2
                            remainingValues2.splice(indexInRemaining, 1);
                        } else {
                            // Jika nilai dari scan 1 TIDAK DITEMUKAN di sisa nilai scan 2, berhenti
                            // console.log(`Nilai '${expectedVal}' dari Scan 1 tidak ditemukan di sisa nilai Scan 2.`);
                            break; // Keluar dari loop karena sudah tidak mungkin cocok semua
                        }
                    }

                    // Isi input field 2 berdasarkan hasil matching
                    sequenceInput2.value = matchedValues.seq;
                    dateInput2.value = matchedValues.date;
                    modelNameInput2.value = matchedValues.model;
                    prodNoInput2.value = matchedValues.prod;

                    if (matchedValues.seq && matchedValues.date && matchedValues.model && matchedValues.prod) {
						scan2Complete = true;
						submitBtn.disabled = false;

						// optional: auto submit setelah terlihat
						setTimeout(() => {
							submitBtn.click();
						}, 500);
					} else {
						showValidationModal(
							'Data Scan 2 tidak cocok dengan Scan 1',
							rawInput2
						);

						sequenceInput2.value = '';
						dateInput2.value = '';
						modelNameInput2.value = '';
						prodNoInput2.value = '';

						scan2Complete = false;
					}
                } else {
					showValidationModal(
						'Format QR Code untuk Scan 2 tidak valid',
						rawInput2
					);
				}
            }

            // --- Event Listener untuk Input Scanner USB Scan 1 ---
            rawInput1.addEventListener('keydown', function (e) {
				if (e.key === 'Enter') {
					e.preventDefault();

					const rawValue = this.value.trim();
					if (!rawValue) return;

					const parts = rawValue.split(';');
					processScan1(parts);

					this.value = '';
				}
			});

            // --- Event Listener untuk Input Scanner USB Scan 2 ---
            rawInput2.addEventListener('keydown', function (e) {
				if (e.key === 'Enter') {
					e.preventDefault();

					const rawValue = this.value.trim();
					if (!rawValue) return;

					const parts = rawValue.split(';');
					processScan2(parts);

					this.value = '';
				}
			});

            // --- Scanner 1 (Kamera) ---
            scanButton1.addEventListener('click', function () {
                if (html5QrcodeScanner1) {
                    html5QrcodeScanner1.clear().then(() => {
                        html5QrcodeScanner1 = null;
                        qrReaderElement1.style.display = 'none';
                        rawInput1.focus(); // Fokus ke input raw agar bisa menerima scan dari USB jika perlu
                    }).catch(console.error);
                    return;
                }

                qrReaderElement1.style.display = 'block';

                html5QrcodeScanner1 = new Html5QrcodeScanner(
                    'qr-reader-1', {
                        fps: 10,
                        qrbox: { width: 300, height: 300 },
                    }
                );

                function onScanSuccess1(decodedText, decodedResult) {
                    const parts = decodedText.split(';');
                    processScan1(parts); // Gunakan fungsi proses

                    html5QrcodeScanner1.clear().then(() => {
                        html5QrcodeScanner1 = null;
                        qrReaderElement1.style.display = 'none';
                        // Fokus ke input raw agar bisa menerima scan dari USB jika perlu
                        // Jika scan dilakukan via kamera, fokus ke scan 2 tetap terjadi di akhir processScan1
                    }).catch(console.error);
                }

                html5QrcodeScanner1.render(onScanSuccess1, function(errorMessage) {
                    console.warn("QR Code scan error (Scanner 1):", errorMessage);
                });
            });

            // --- Scanner 2 (Kamera) ---
            scanButton2.addEventListener('click', function () {
                if (html5QrcodeScanner2) {
                    html5QrcodeScanner2.clear().then(() => {
                        html5QrcodeScanner2 = null;
                        qrReaderElement2.style.display = 'none';
                        rawInput2.focus(); // Fokus ke input raw agar bisa menerima scan dari USB jika perlu
                    }).catch(console.error);
                    return;
                }

                qrReaderElement2.style.display = 'block';

                html5QrcodeScanner2 = new Html5QrcodeScanner(
                    'qr-reader-2', {
                        fps: 10,
                        qrbox: { width: 300, height: 300 },
                    }
                );

                function onScanSuccess2(decodedText, decodedResult) {
                  const parts = decodedText.split(';');
                  processScan2(parts); // Gunakan fungsi proses

                  html5QrcodeScanner2.clear().then(() => {
                      html5QrcodeScanner2 = null;
                      qrReaderElement2.style.display = 'none';
                      rawInput2.focus(); // Fokus ke input raw agar bisa menerima scan dari USB jika perlu
                  }).catch(console.error);
                }

                html5QrcodeScanner2.render(onScanSuccess2, function(errorMessage) {
                    console.warn("QR Code scan error (Scanner 2):", errorMessage);
                });
            });

            // --- FOKUS KE SCAN 1 SAAT HALAMAN DIMUAT ---
            setTimeout(() => {
				rawInput1.focus();
			}, 300);
            // --- AKHIR FOKUS ---

        });
    </script>

    <!-- Modal Notifikasi Kesalahan -->
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

	<!-- Modal Notifikasi Status -->
    <div class="modal fade" id="statusNotificationModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-white" id="statusModalLabel">Status Lineoff</h5>
                </div>
                <div class="modal-body">
                    <h4 id="statusModalMessage"></h4>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

	<div class="modal fade" id="errorNotificationModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="errorModalLabel">Status Lineoff</h5>
                </div>
                <div class="modal-body">
                    <h4 id="errorModalMessage"></h4>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
@endsection