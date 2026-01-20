<!DOCTYPE html>
<html lang="en" class="light-style customizer-hide" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('assets/') }}" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Iseki Podium | Pokayoke Digital Unit Monitoring</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}" />
    <link href="{{ asset('assets/css/datatables.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/fixedColumns.dataTables.min.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
</head>

<body>
    <!-- Navbar -->
    <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
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
                    <a class="nav-link" href="{{ route('scan') }}">
                        <!-- Ganti dengan route kamu -->
                        <button class="btn btn-outline-secondary me-2">
                            Scan
                        </button>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('lineoff') }}">
                        <!-- Ganti dengan route kamu -->
                        <button class="btn btn-outline-secondary me-2">
                            Lineoff
                        </button>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('report') }}">
                        <!-- Ganti dengan route kamu -->
                        <button class="btn btn-outline-secondary me-2">
                            Report
                        </button>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}">
                        <!-- Ganti dengan route kamu -->
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
                        <li><a class="dropdown-item" href="{{ route('scan') }}"><i class="bx bx-qr me-2"></i> Scan</a>
                        </li>
                        <li><a class="dropdown-item" href="{{ route('lineoff') }}"><i
                                    class="bx bx-stop-circle me-2"></i> Lineoff</a></li>
                        <li><a class="dropdown-item" href="{{ route('report') }}"><i class="bx bx-file me-2"></i>
                                Report</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="{{ route('login') }}"><i class="bx bx-log-in me-2"></i>
                                Login</a></li>
                    </ul>
                </li>
            </ul>

        </div>
    </nav>

    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Lineoff /</span> List</h4>

        <!-- Filter Tanggal dan Export -->
		<div class="row mb-3">
			<div class="col-md-3 mb-1">
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
			<div class="col-md-6">
				<div class="card">
					<form method="GET">
						<div class="card">
							<div class="card-body">
								<div class="row g-3 align-items-end">
									<!-- Kolom untuk Filter Tanggal -->
									<div class="col-md-8">
										<label for="lineoff_date" class="form-label">Lineoff Date</label>
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
												<a href="{{ route('lineoff.export', ['lineoff_date' => request('lineoff_date')]) }}"
													class="btn btn-success w-100">
													<i class='bx bx-file'></i> Export Excel
												</a>
											@else
												<a href="{{ route('lineoff.export', ['lineoff_date' => \Carbon\Carbon::today()->toDateString()]) }}"
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

        <!-- Summary Cards -->
        <div class="row mb-3">
            <!-- Tractor Types Card (New Structure) -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">Tipe Traktor & Jumlah (Tanggal: {{ \Carbon\Carbon::parse($selectedDate)->locale('id')->isoFormat('D MMMM Y') }})</h5>
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
                                    <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
                                        <!-- Card Pipih -->
                                        <div class="card text-center border-1 shadow-none" style="border-radius: 0.25rem;">
                                            <div class="card-body p-2 d-flex flex-column">
												<div class="row">
													<div class="col-md-6">
														<!-- Gambar -->
														<img src="{{ asset('assets/img/tractors/' . $typeData->Type_Plan . '.png') }}"
															alt="{{ $typeData->Type_Plan }}"
															class="img-fluid mb-1" style="max-height: 50px; object-fit: contain;">
													</div>
													<div class="col-md-6">
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
        </div>

        <!-- Tabel -->
        <div class="row">
            <div class="col">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="table-responsive text-nowrap">
                            <table id="reportsTable" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-primary">No</th>
                                        <th class="text-primary">Sequence No</th>
                                        <th class="text-primary">Model Name</th>
                                        <th class="text-primary">Type</th>
                                        <th class="text-primary">Production No</th>
                                        <th class="text-primary">Production Date</th>
                                        <th class="text-primary">Lineoff</th>
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

    <!-- Core JS -->
    <!-- ... (script tags remain the same) ... -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/dataTables.fixedColumns.min.js') }}"></script>

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
                    url: "{{ route('api.lineoff.data') }}",
                    type: 'GET',
                    data: function (d) {
                        d.lineoff_date = $('#lineoff_date').val();
                    },
                    error: function (xhr, error, code) {
                        console.warn("DataTables AJAX Error:", error, code);
                    }
                },
                scrollX: true,
                scrollY: "500px",
                scrollCollapse: true,
                orderCellsTop: true,
                columns: [{
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
								bgColor = typeColors[data] || bgColor; // Gunakan warna dari map, jika tidak ditemukan gunakan default
							}

							// Kembalikan HTML span dengan styling badge
							return '<span class="badge" style="background-color: ' + bgColor + '; color: black;">' + data + '</span>'; // Gunakan warna teks hitam untuk kontras yang lebih baik dengan warna pastel
						},
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
                        if (title !== "No") {
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
</body>

</html>
