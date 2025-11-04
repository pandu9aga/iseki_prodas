<!DOCTYPE html>

<!-- =========================================================
* Sneat - Bootstrap 5 HTML Admin Template - Pro | v1.0.0
==============================================================

* Product Page: https://themeselection.com/products/sneat-bootstrap-html-admin-template/
* Created by: ThemeSelection
* License: You must have a valid license purchased in order to legally use the theme for your project.
* Copyright ThemeSelection (https://themeselection.com)

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
    {{-- <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
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
        <div class="col-md-6">
          <div class="card mb-4">
            <h5 class="card-header">Scan Unit Lineoff</h5>
            <div class="card-body">
              <form action="{{ route('scan.store') }}" method="POST"> {{-- Ganti dengan route kamu --}}
                  @csrf
                  <div>
                    <label for="sequence_no" class="form-label">Sequence Number</label>
                    <div class="row">
                      <div class="col-md-10">
                        <input
                          type="text"
                          class="form-control mt-2"
                          id="sequence_no"
                          name="sequence_no"
                          placeholder="Sequence Number"
                          aria-describedby="defaultFormControlHelp"
                          required
                          readonly {{-- Input akan diisi oleh JS --}}
                        />
                      </div>
                      <div class="col-md-2">
                        <button type="button" id="scanButton" class="btn btn-outline-secondary mt-2">
                          Scan
                        </button>
                      </div>
                    </div>
                    <div id="qr-reader" style="margin-top:15px; display: none;"></div> {{-- Area untuk QR scanner --}}
                    <div id="defaultFormControlHelp" class="form-text">
                      Gunakan tombol scan jika ingin memindai menggunakan kamera device.
                    </div>
                  </div>
                  <button type="submit" class="btn btn-primary mt-4">
                    Submit
                  </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- / Content -->

    {{-- <div class="buy-now">
      <a
        href="https://themeselection.com/products/sneat-bootstrap-html-admin-template/"
        target="_blank"
        class="btn btn-danger btn-buy-now"
        >Upgrade to Pro</a
      >
    </div> --}}

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
    {{-- <script async defer src="https://buttons.github.io/buttons.js"></script> --}}

    <script src="{{ asset('assets/js/html5-qrcode.min.js') }}"></script> {{-- Pastikan library ini di-load --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const scanButton = document.getElementById('scanButton');
            const sequenceInput = document.getElementById('sequence_no');
            const qrReaderElement = document.getElementById('qr-reader'); // Ambil elemen dari HTML

            let html5QrcodeScanner;

            scanButton.addEventListener('click', function () {
                if (html5QrcodeScanner) {
                    // Jika scanner sudah aktif, hentikan dulu
                    html5QrcodeScanner.clear().then(() => {
                        html5QrcodeScanner = null;
                        qrReaderElement.style.display = 'none'; // Sembunyikan area scanner
                    }).catch(console.error);
                    return;
                }

                // Tampilkan area scanner
                qrReaderElement.style.display = 'block';

                // Ganti qrReaderElement dengan string ID 'qr-reader'
                html5QrcodeScanner = new Html5QrcodeScanner(
                    'qr-reader', { // Gunakan string ID
                        fps: 10, // Sedikit lebih cepat
                        qrbox: {
                            width: 300, // Lebih besar
                            height: 300
                        },
                    }
                );

                function onScanSuccess(decodedText, decodedResult) {
                    // Pindai sukses, proses string QR
                    const parts = decodedText.split(';');
                    if (parts.length > 0) {
                        let sequenceNumber = parts[0].trim(); // Ambil indeks pertama
                        // Format ke 5 digit dengan leading zero
                        sequenceNumber = sequenceNumber.padStart(5, '0');
                        sequenceInput.value = sequenceNumber;
                    } else {
                        alert('Format QR Code tidak valid.');
                    }

                    // Hentikan scanner setelah sukses
                    html5QrcodeScanner.clear().then(() => {
                        html5QrcodeScanner = null;
                        qrReaderElement.style.display = 'none'; // Sembunyikan area scanner
                    }).catch(console.error);
                }

                html5QrcodeScanner.render(onScanSuccess, function(errorMessage) {
                    // Handle error jika diperlukan
                    //console.error('QR Code scan error:', errorMessage);
                });
            });
        });
    </script>
  </body>
</html>
