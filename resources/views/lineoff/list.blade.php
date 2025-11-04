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

    <link href="{{asset('assets/css/datatables.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/css/fixedColumns.dataTables.min.css')}}" rel="stylesheet">
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
      <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Lineoff /</span> List</h4>

      <div class="row">
        <button class="btn btn-secondary mb-3" onclick="location.href='{{ route('scan') }}'">
          Scan
        </button>
        <div class="col">
          <div class="card mb-3">
            <div class="d-flex align-items-end row">
                <div class="col">
                    <div class="card-body">
                        <div class="table-responsive text-nowrap">
                            <table id="plansTable" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-primary">No</th>
                                        <th class="text-primary">Sequence No</th>
                                        <th class="text-primary">Lineoff</th>
                                        <th class="text-primary">Type</th>
                                        <th class="text-primary">Production Date</th>
                                        <th class="text-primary">Model Name</th>
                                        <th class="text-primary">Production No</th>
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

    <script src="{{asset('assets/js/datatables.min.js')}}"></script>
    <script src="{{asset('assets/js/dataTables.fixedColumns.min.js')}}"></script>

    <script>
      $(document).ready(function () {
          // clone header row untuk filter
          $('#plansTable thead tr')
              .clone(true)
              .addClass('filters')
              .appendTo('#plansTable thead');

          // Tambah plugin custom sorting
          jQuery.extend(jQuery.fn.dataTable.ext.type.order, {
              "seq-pre": function (d) {
                  // Ambil angka setelah huruf (contoh: T12 -> 12)
                  let num = d.replace(/^[^\d]*/, ""); 
                  return parseInt(num, 10) || 0;
              },
              "seq-asc": function (a, b) {
                  return a - b;
              },
              "seq-desc": function (a, b) {
                  return b - a;
              }
          });

          var table = $('#plansTable').DataTable({
              processing: true,
              serverSide: true,
              deferRender: true,
              stateSave: false,
              pageLength: 50,
              order: [[1, 'asc']],
              ajax: {
                  url: '/iseki_podium/public/api/lineoffs-data',
                  type: 'GET',
                  error: function (xhr, error, code) {
                      console.warn("DataTables AJAX Error:", error, code);
                      // ⚠️ kalau mau tampilkan alert custom:
                      // toastr.error("Gagal memuat data, coba lagi.");
                  }
              },
              scrollX: true,
              scrollY: "500px",
              scrollCollapse: true,
              // fixedColumns: {
              //     leftColumns: 3,
              // },
              orderCellsTop: true,
              fixedHeader: false, // ❌ matikan supaya filter input bisa diklik
              columns: [
                  { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                  { data: 'Sequence_No_Plan', name: 'Sequence_No_Plan' },
                  { data: 'Lineoff_Plan', name: 'Lineoff_Plan' },
                  { data: 'Type_Plan', name: 'Type_Plan' },
                  { data: 'Production_Date_Plan', name: 'Production_Date_Plan' },
                  { data: 'Model_Name_Plan', name: 'Model_Name_Plan' },
                  { data: 'Production_No_Plan', name: 'Production_No_Plan' },
                  { data: 'Chasis_No_Plan', name: 'Chasis_No_Plan' },
                  { data: 'Model_Label_Plan', name: 'Model_Label_Plan' },
                  { data: 'Safety_Frame_Label_Plan', name: 'Safety_Frame_Label_Plan' },
                  { data: 'Model_Mower_Plan', name: 'Model_Mower_Plan' },
                  { data: 'Mower_No_Plan', name: 'Mower_No_Plan' },
                  { data: 'Model_Collector_Plan', name: 'Model_Collector_Plan' },
                  { data: 'Collector_No_Plan', name: 'Collector_No_Plan' },
              ],
              initComplete: function () {
                  var api = this.api();

                  // Buat input di tiap kolom
                  api.columns().eq(0).each(function (colIdx) {
                      var cell = $('.filters th').eq($(api.column(colIdx).header()).index());
                      var title = $(cell).text();

                      if (title !== "No") {
                          $(cell).html(
                              '<input type="text" placeholder="Search ' + title + '" ' +
                              'class="form-control form-control-sm" style="width:100%; padding:2px 4px; font-size:12px;"/>'
                          );
                      } else {
                          $(cell).html('');
                      }

                      // event handler untuk filter
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
  </body>
</html>
