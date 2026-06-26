@extends('layouts.main')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-target-lock text-primary me-1"></i>
                            Kelola Target WA Rangkuman
                        </h5>
                        <a href="{{ route('report.wa-rangkuman') }}" class="btn btn-sm btn-outline-success">
                            <i class="bx bx-arrow-back me-1"></i> Kembali
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Alert Messages -->
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible">{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if (session('warning'))
                            <div class="alert alert-warning alert-dismissible">{{ session('warning') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if (isset($errors) && $errors->any())
                            <div class="alert alert-danger alert-dismissible">
                                <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Info Card -->
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-1"></i>
                            Target harian diisi per bulan menggunakan format Excel.
                            Kolom: <strong>Category Group</strong> | <strong>Category Item</strong> | <strong>1</strong> | <strong>2</strong> | ... | <strong>31</strong>
                            <br>Download template, isi target per hari, lalu import.
                        </div>

                        <div class="row g-4">
                            <!-- Kiri: Import -->
                            <div class="col-md-5">
                                <div class="card h-100 border-primary border-opacity-25">
                                    <div class="card-header bg-primary bg-opacity-10">
                                        <h6 class="mb-0 text-primary"><i class="bx bx-upload me-1"></i> Import Target</h6>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('report.wa-rangkuman.target.import') }}" method="POST"
                                            enctype="multipart/form-data">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label">Bulan</label>
                                                <input type="month" name="month" class="form-control"
                                                    value="{{ date('Y-m') }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">File Excel</label>
                                                <input type="file" name="file" class="form-control"
                                                    accept=".xlsx,.xls" required>
                                                <small class="text-muted">Format: .xlsx atau .xls, max 5MB</small>
                                            </div>
                                            <div class="d-grid gap-2">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bx bx-upload me-1"></i> Import
                                                </button>
                                                <a href="{{ route('report.wa-rangkuman.target.template') }}?month={{ date('Y-m') }}"
                                                    class="btn btn-outline-secondary" id="downloadTemplateBtn">
                                                    <i class="bx bx-download me-1"></i> Download Template
                                                </a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Kanan: Export + Info -->
                            <div class="col-md-7">
                                <div class="card h-100 border-success border-opacity-25">
                                    <div class="card-header bg-success bg-opacity-10">
                                        <h6 class="mb-0 text-success"><i class="bx bx-download me-1"></i> Export Target</h6>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('report.wa-rangkuman.target.export') }}" method="GET">
                                            <div class="mb-3">
                                                <label class="form-label">Pilih Bulan</label>
                                                <div class="input-group">
                                                    <input type="month" name="month" class="form-control"
                                                        value="{{ date('Y-m') }}" required>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="bx bx-download me-1"></i> Export
                                                    </button>
                                                </div>
                                                <small class="text-muted">Download data target yang sudah tersimpan</small>
                                            </div>
                                        </form>
                                        <hr>
                                        <h6><i class="bx bx-list-ul me-1"></i> Kategori yang tersedia:</h6>
                                        <div class="row">
                                            <div class="col-6">
                                                <ul class="mb-0 small">
                                                    <li><strong>TRANSMISI</strong>: SXG3 & SF, Transmisi</li>
                                                    <li><strong>SUB ENGINE</strong>: Sub Engine</li>
                                                    <li><strong>LINE A</strong>: Unit, Mocol</li>
                                                    <li><strong>LINE B</strong>: Line B</li>
                                                </ul>
                                            </div>
                                            <div class="col-6">
                                                <ul class="mb-0 small">
                                                    <li><strong>SUB ASSY</strong>: Sub Assy</li>
                                                    <li><strong>MAIN LINE</strong>: Mainline</li>
                                                    <li><strong>INSPEKSI</strong>: Inspeksi</li>
                                                    <li><strong>MOCOL</strong>: Unit, Mower, Collector</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Logs -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bx bx-history me-1"></i> Riwayat Import/Export</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Tipe</th>
                                                <th>File</th>
                                                <th>Bulan</th>
                                                <th>Total Row</th>
                                                <th>Tanggal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($waLogs as $log)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        @if ($log->Action_Type === 'IMPORT')
                                                            <span class="badge bg-success">IMPORT</span>
                                                        @elseif ($log->Action_Type === 'EXPORT')
                                                            <span class="badge bg-info text-dark">EXPORT</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ $log->Action_Type }}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $log->File_Name ?? '-' }}</td>
                                                    <td>{{ $log->Month ?? '-' }}</td>
                                                    <td class="text-center">{{ $log->Total_Rows }}</td>
                                                    <td>{{ $log->Created_At ? \Carbon\Carbon::parse($log->Created_At)->format('d-m-Y H:i') : '-' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-3">
                                                        Belum ada aktivitas import/export
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    $(document).ready(function () {
        // Update template & export link saat bulan berubah
        $('input[name="month"]').on('change', function () {
            var m = $(this).val();
            $('#downloadTemplateBtn').attr('href', '{{ route("report.wa-rangkuman.target.template") }}?month=' + m);
        });
    });
</script>
@endsection
