<!DOCTYPE html>

<!-- =========================================================
* Sneat - Bootstrap 5 HTML Admin Template - Pro | v1.0.0
==============================================================

* Product Page: https://themeselection.com/products/sneat-bootstrap-html-admin-template/  
* Created by: ThemeSelection
* License: You must have a valid license purchased in order to legally use the theme for your project.
* Copyright ThemeSelection (https://themeselection.com    )

=========================================================
 -->
<!-- beautify ignore:start -->
<html
  lang="en"
  class="light-style customizer-hide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="{{ asset('assets/') }}"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>Iseki Podium | Pokayoke Digital Unit Monitoring</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    {{-- <link rel="preconnect" href="https://fonts.googleapis.com    " />
    <link rel="preconnect" href="https://fonts.gstatic.com    " crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans    :ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    /> --}}

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}" />
    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/js/config.js') }}"></script>
  </head>

  <body>
    <!-- Content -->

    <nav
      class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
      id="layout-navbar">

      <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <!-- Search -->
        <div class="navbar-nav align-items-center">
          <div class="nav-item d-flex align-items-center">
              <h5 class="text-primary mb-0">Digital Pokayoke</h5>
          </div>
        </div>

        <!-- Tombol-tombol di pojok kanan hanya muncul di layar XL dan besar -->
        <ul class="navbar-nav flex-row align-items-center ms-auto d-none d-xl-flex">
          <li class="nav-item">
            <a class="nav-link" href="{{ route('scan') }}"> <!-- Ganti dengan route kamu -->
              <button class="btn btn-outline-secondary me-2">
                Scan
              </button>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('lineoff') }}"> <!-- Ganti dengan route kamu -->
              <button class="btn btn-outline-secondary me-2">
                Lineoff
              </button>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('report') }}"> <!-- Ganti dengan route kamu -->
              <button class="btn btn-outline-secondary me-2">
                Report
              </button>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('login') }}"> <!-- Ganti dengan route kamu -->
              <button class="btn btn-outline-primary">
                Login
              </button>
            </a>
          </li>
        </ul>

        <!-- Dropdown menu di pojok kanan hanya muncul di layar kecil -->
        <ul class="navbar-nav flex-row align-items-center ms-auto d-xl-none">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
              <i class="bx bx-dots-vertical-rounded bx-sm"></i> <!-- Ikon titik tiga -->
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="{{ route('scan') }}"><i class="bx bx-qr me-2"></i> Scan</a></li>
              <li><a class="dropdown-item" href="{{ route('lineoff') }}"><i class="bx bx-stop-circle me-2"></i> Lineoff</a></li>
              <li><a class="dropdown-item" href="{{ route('report') }}"><i class="bx bx-file me-2"></i> Report</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="{{ route('login') }}"><i class="bx bx-log-in me-2"></i> Login</a></li>
            </ul>
          </li>
        </ul>

      </div>
    </nav>

    <div class="container-xxl flex-grow-1 container-p-y">
      <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Lineoff /</span> Scan</h4>

      <div class="row">
        <div class="col-md-12"> <!-- Ganti ke 12 agar form lebar penuh -->
          <div class="card mb-4">
            <h5 class="card-header">Scan Unit Lineoff</h5>
            <div class="card-body">
              <!-- Alert untuk notifikasi kesalahan -->
              <div id="validationAlert" class="alert alert-danger d-none" role="alert">
                <span id="validationMessage"></span>
              </div>

              <form id="scanForm" action="{{ route('scan.store') }}" method="POST">
                  @csrf
                  <div class="row">
                    <!-- Kolom Kiri (Scan 1) -->
                    <div class="col-md-6 mb-2">
                      <h6>Scan 1</h6>
					  <!-- Input tersembunyi untuk Scanner USB Scan 1 -->
					  <input type="text" id="rawInput1" class="form-control mb-2" placeholder="Scan QR 1 here" style="display: block;" autocomplete="off" autofocus>
                      <div class="row mb-1">
                        <div class="col-12">
                          <label for="sequence_no_input_1" class="form-label">Sequence Number *</label>
                          <input
                            type="text"
                            class="form-control"
                            id="sequence_no_input_1"
                            name="sequence_no"
                            placeholder="Seq No"
                            readonly
                            required
                          />
                        </div>
                        <div class="col-12">
                          <label for="production_date_input_1" class="form-label">Production Date *</label>
                          <input
                            type="text"
                            class="form-control"
                            id="production_date_input_1"
                            name="production_date"
                            placeholder="Date"
                            readonly
                            required
                          />
                        </div>
                        <div class="col-12">
                          <label for="model_name_input_1" class="form-label">Model Name *</label>
                          <input
                            type="text"
                            class="form-control"
                            id="model_name_input_1"
                            placeholder="Model"
                            readonly
                            required
                          />
                        </div>
                        <div class="col-12">
                          <label for="production_no_input_1" class="form-label">Production No *</label>
                          <input
                            type="text"
                            class="form-control"
                            id="production_no_input_1"
                            placeholder="Prod No"
                            readonly
                            required
                          />
                        </div>
                      </div>
                      <button type="button" id="scanButton1" class="btn btn-outline-primary mt-2">
                        Scan 1 (Camera)
                      </button>
                      <div id="qr-reader-1" style="margin-top:15px; display: none;"></div>
                    </div>

                    <!-- Kolom Kanan (Scan 2) -->
                    <div class="col-md-6 mb-2">
                      <h6>Scan 2</h6>
					  <!-- Input tersembunyi untuk Scanner USB Scan 2 -->
                      <input type="text" id="rawInput2" class="form-control mb-2" placeholder="Scan QR 2 here" style="display: block;">
                      <div class="row mb-1">
                        <div class="col-12">
                          <label for="sequence_no_input_2" class="form-label">Sequence Number *</label>
                          <input
                            type="text"
                            class="form-control"
                            id="sequence_no_input_2"
                            placeholder="Seq No"
                            readonly
                            required
                          />
                        </div>
                        <div class="col-12">
                          <label for="production_date_input_2" class="form-label">Production Date *</label>
                          <input
                            type="text"
                            class="form-control"
                            id="production_date_input_2"
                            placeholder="Date"
                            readonly
                            required
                          />
                        </div>
                        <div class="col-12">
                          <label for="model_name_input_2" class="form-label">Model Name *</label>
                          <input
                            type="text"
                            class="form-control"
                            id="model_name_input_2"
                            placeholder="Model"
                            readonly
                            required
                          />
                        </div>
                        <div class="col-12">
                          <label for="production_no_input_2" class="form-label">Production No *</label>
                          <input
                            type="text"
                            class="form-control"
                            id="production_no_input_2"
                            placeholder="Prod No"
                            readonly
                            required
                          />
                        </div>
                      </div>
                      <button type="button" id="scanButton2" class="btn btn-outline-primary mt-2">
                        Scan 2 (Camera)
                      </button>
                      <div id="qr-reader-2" style="margin-top:15px; display: none;"></div>
                    </div>
                  </div>

                  <button type="submit" id="submitBtn" class="btn btn-primary mt-4" disabled>
                    Submit
                  </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- / Content -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>

    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->

    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- Page JS -->

    <!-- Place this tag in your head or just before your close body tag. -->
    {{-- <script async defer src="https://buttons.github.io/buttons.js    "></script> --}}

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
					}, 2000);

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
                    <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
                    <!-- Tombol close disembunyikan karena hanya bisa ditutup dengan tombol OK -->
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
  </body>
</html>