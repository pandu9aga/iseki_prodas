@extends('layouts.main')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-message-square-detail text-success me-1"></i>
                            WA RANGKUMAN
                        </h5>
                        <div>
                            <a href="{{ route('report.wa-rangkuman.target') }}" class="btn btn-sm btn-outline-primary me-2">
                                <i class="bx bx-target-lock me-1"></i> Kelola Target
                            </a>
                            <small class="text-muted" id="updateTimestamp">Loading...</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filter -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="filterDate" class="form-label">Tanggal Produksi</label>
                                <div class="input-group">
                                    <input type="date" id="filterDate" class="form-control"
                                        value="{{ \Carbon\Carbon::today()->toDateString() }}">
                                    <button type="button" id="prevDateBtn" class="btn btn-outline-secondary">
                                        <i class="bx bx-chevron-left"></i>
                                    </button>
                                    <button type="button" id="applyFilter" class="btn btn-success">
                                        <i class="bx bx-refresh me-1"></i> Tampilkan
                                    </button>
                                    <button type="button" id="nextDateBtn" class="btn btn-outline-secondary">
                                        <i class="bx bx-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-8 text-end">

                                <button type="button" id="saveHistoryBtn" class="btn btn-primary me-2">
                                    <i class="bx bx-save me-1"></i> Simpan Log
                                </button>
                                <button type="button" id="copyWaBtn" class="btn btn-outline-success">
                                    <i class="bx bx-copy me-1"></i> Copy WA
                                </button>
                                <button type="button" id="sendWaBtn" class="btn btn-success ms-2">
                                    <i class="bx bxl-whatsapp me-1"></i> Kirim WA
                                </button>
                                <button type="button" id="historyModalBtn" class="btn btn-outline-warning ms-2">
                                    <i class="bx bx-history me-1"></i> Histori
                                </button>
                            </div>
                        </div>

                        <!-- Summary Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="rangkumanTable" style="font-size: 14px;">
                                <thead class="table-success">
                                    <tr>
                                        <th style="width: 160px;">Area / Line</th>
                                        <th style="width: 70px;">Ket</th>
                                        <th style="width: 75px;" class="text-center">T</th>
                                        <th style="width: 75px;" class="text-center">A</th>
                                        <th style="width: 75px;" class="text-center">S</th>
                                        <th style="width: 75px;" class="text-center">GT</th>
                                        <th style="width: 100px;" class="text-center">Koreksi GT</th>
                                    </tr>
                                </thead>
                                <tbody id="rangkumanBody">
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <div class="spinner-border text-success" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div><br>Memuat data...
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot id="rangkumanFooter" class="table-success" style="display:none;">
                                    <tr>
                                        <th colspan="2">TOTAL</th>
                                        <th class="text-center" id="totalT">0</th>
                                        <th class="text-center" id="totalA">0</th>
                                        <th class="text-center" id="totalS">0</th>
                                        <th class="text-center" id="totalGT">0</th>
                                        <th class="text-center">-</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- WA Preview -->
                        <div class="card mt-4 border-success border-opacity-25">
                            <div class="card-header bg-success bg-opacity-10">
                                <h6 class="mb-0 text-success">
                                    <i class="bx bxl-whatsapp me-1"></i> Preview WA
                                </h6>
                            </div>
                            <div class="card-body">
                                <pre id="waPreview" class="mb-0" style="white-space: pre-wrap; font-family: monospace; font-size: 13px; background: #f5f5f5; padding: 15px; border-radius: 8px; min-height: 100px;">Loading...</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-calendar text-warning me-1"></i>
                    Produksi Bulanan
                </h5>
                <div class="d-flex align-items-center">
{{--                    <button type="button" id="exportMonthlyBtn" class="btn btn-sm btn-success me-2">--}}
{{--                        <i class="bx bx-export me-1"></i> Export Excel--}}
{{--                    </button>--}}
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
{{--                        <button type="button" id="histPrevMonth" class="btn btn-sm btn-outline-secondary" title="Bulan sebelumnya">--}}
{{--                            <i class="bx bx-chevron-left"></i>--}}
{{--                        </button>--}}
                        <span id="histMonthTitle" class="fw-bold" style="min-width:160px;text-align:center;"></span>
                        <input type="hidden" id="histMonth" value="">
{{--                        <button type="button" id="histNextMonth" class="btn btn-sm btn-outline-secondary" title="Bulan berikutnya">--}}
{{--                            <i class="bx bx-chevron-right"></i>--}}
{{--                        </button>--}}
{{--                        <button type="button" id="histLoadBtn" class="btn btn-warning btn-sm ms-2">--}}
{{--                            <i class="bx bx-refresh me-1"></i> Tampilkan--}}
{{--                        </button>--}}
                    </div>
                    <div class="small">
                        <span class="badge bg-success">A &gt; T</span>
                        <span class="badge" style="background:#fff;color:#333;border:1px solid #ddd;">A = T</span>
                        <span class="badge bg-danger ms-1">A &lt; T</span>
                        <span class="badge bg-secondary ms-1">Kosong</span>
                        <span class="badge ms-1" style="background:#fff3e0;color:#e65100;"><span style="color:#ff9800;">●</span> History</span>
                    </div>
                </div>
                <div style="overflow:auto; max-height:70vh;">
                    <table class="table table-sm table-bordered mb-0" style="font-size:10px;">
                        <thead class="table-warning" id="histMonthHead" style="position:sticky; top:0; z-index:2;">
                            <tr><th style="width:90px;">Category Group</th><th style="width:75px;">Category Item</th><th style="width:22px;">T/A</th></tr>
                        </thead>
                        <tbody id="histMonthBody">
                            <tr><td colspan="3" class="text-center text-muted py-4">Pilih bulan & klik Tampilkan</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    // ── Simple Toast (no CDN) ──
    function showToast(msg, type) {
        type = type || 'success';
        var bg = type === 'success' ? '#28a745' : (type === 'error' ? '#dc3545' : '#ffc107');
        var toast = $('<div>')
            .css({ position: 'fixed', top: '20px', right: '20px', zIndex: 99999,
                background: bg, color: '#fff', padding: '12px 20px', borderRadius: '6px',
                fontSize: '14px', boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                maxWidth: '400px', wordWrap: 'break-word', opacity: 0 })
            .text(msg)
            .appendTo('body')
            .animate({ opacity: 1 }, 200);
        setTimeout(function () {
            toast.animate({ opacity: 0, top: '-50px' }, 300, function () { toast.remove(); });
        }, 3000);
    }

    $(document).ready(function () {
        var currentData = null;

        function toggleKoreksiInput() {
            const selectedDate = $('#filterDate').val();

            if (!selectedDate) return;

            const day = new Date(selectedDate + 'T00:00:00').getDate();

            const isFirstDayOfMonth = day === 1;

            $('.koreksi-input').prop('disabled', !isFirstDayOfMonth);

            if (!isFirstDayOfMonth) {
                $('.koreksi-input').addClass('bg-light');
            } else {
                $('.koreksi-input').removeClass('bg-light');
            }
        }

        function loadData(date) {
            $('#rangkumanBody').html(`
                <tr><td colspan="7" class="text-center text-muted py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div><br>Memuat data...
                </td></tr>
            `);
            $('#rangkumanFooter').hide();
            $('#waPreview').text('Loading...');

            $.getJSON('{{ route("api.admin.wa-rangkuman.data") }}', { date: date })
                .done(function (res) {
                    currentData = res;
                    renderTable(res);
                    renderWaPreview(res, date);
                    loadKoreksiFromHistory(date);
                    $('#updateTimestamp').text('Update: ' + new Date().toLocaleString('id-ID'));
                })
                .fail(function () {
                    $('#rangkumanBody').html(`
                        <tr><td colspan="7" class="text-center text-danger py-4">
                            <i class="bx bx-error-circle"></i> Gagal memuat data.
                        </td></tr>
                    `);
                    $('#waPreview').text('Gagal memuat data.');
                });
        }

        function renderTable(res) {
            var html = '';
            $.each(res.rows, function (gi, group) {
                var groupFirst = true;
                $.each(group.items, function (ii, item) {
                    var groupLabel = groupFirst ? '<strong>' + group.group + '</strong>' : '';
                    if (groupFirst) groupFirst = false;

                    var sClass = 'text-center';
                    if (item.S < 0) sClass += ' text-danger fw-bold';
                    else if (item.S > 0) sClass += ' text-success';
                    else sClass += ' text-muted';

                    var sText = item.S < 0 ? item.S : (item.S > 0 ? item.S : '0');
                    var aClass = item.A >= item.T ? 'text-success' : 'text-danger';

                    html += '<tr data-group="' + group.group + '" data-item="' + item.label + '">';
                    html += '<td>' + groupLabel + '</td>';
                    html += '<td>' + item.label + '</td>';
                    html += '<td class="text-center fw-bold">' + item.T + '</td>';
                    html += '<td class="text-center fw-bold ' + aClass + '">' + item.A + '</td>';
                    html += '<td class="' + sClass + '">' + sText + '</td>';
                    html += '<td class="text-center fw-bold gt-cell">' + item.GT + '</td>';
                    html += '<td class="text-center"><input type="number" class="form-control form-control-sm koreksi-input" style="width:90px;display:inline-block;text-align:center;" value="' + (item.koreksi || 0) + '"></td>';
                    html += '</tr>';
                });
            });
            $('#rangkumanBody').html(html);

            toggleKoreksiInput()

            // Koreksi input change → recalculate GT
            $('.koreksi-input').on('change keyup', function () {
                recalcGT();
            });

            if (res.totals) {
                $('#totalT').text(res.totals.T);
                $('#totalA').text(res.totals.A);
                var sTot = res.totals.S < 0 ? res.totals.S : (res.totals.S > 0 ? res.totals.S : '0');
                $('#totalS').text(sTot);
                $('#totalGT').text(res.totals.GT);
                // $('#rangkumanFooter').show(); //i disable it since doenst need footer
            }
        }

        function recalcGT() {
            var data = currentData;
            if (!data) return;
            var totalGT = 0;
            var rows = [];
            $.each(data.rows, function (gi, group) {
                $.each(group.items, function (ii, item) {
                    var tr = $('tr[data-group="' + group.group + '"][data-item="' + item.label + '"]');
                    var koreksiVal = parseInt(tr.find('.koreksi-input').val()) || 0;
                    var monthlyGT = item.GT;
                    var newGT = monthlyGT + koreksiVal;
                    tr.find('.gt-cell').text(newGT);
                    totalGT += newGT;
                    rows.push({ group: group.group, item: item.label, koreksi: koreksiVal });
                });
            });
            $('#totalGT').text(totalGT);
            window._koreksiData = rows;
        }

        function renderWaPreview(res, date) {
            var d = new Date(date + 'T00:00:00');
            var formatted = d.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });

            var lines = [];
            lines.push('═══════════════════════════════');
            lines.push('  *WA RANGKUMAN PRODUKSI*');
            lines.push('  ' + formatted.toUpperCase());
            lines.push('═══════════════════════════════');
            lines.push('');

            $.each(res.rows, function (gi, group) {
                lines.push('*' + group.group + '*');
                $.each(group.items, function (ii, item) {
                    var tr = $('tr[data-group="' + group.group + '"][data-item="' + item.label + '"]');
                    var koreksiVal = parseInt(tr.find('.koreksi-input').val()) || 0;
                    var displayGT = item.GT + koreksiVal;
                    var sStr = item.S < 0 ? item.S : (item.S > 0 ? item.S : '0');
                    lines.push('  ' + item.label + ':');
                    lines.push('    T = ' + item.T + '   A = ' + item.A + '   S = ' + sStr + '   GT = ' + displayGT);
                });
                lines.push('');
            });

            lines.push('_Update: ' + new Date().toLocaleString('id-ID') + '_');
            lines.push('═══════════════════════════════');

            $('#waPreview').text(lines.join('\n'));
        }

        // ── History ──
        // ── Muat koreksi dari history ──
        function loadKoreksiFromHistory(date) {
            $.getJSON('{{ route("api.admin.wa-rangkuman.history") }}', { date: date, limit: 50 })
                .done(function (res) {
                    if (res.success && Object.keys(res.history).length > 0) {
                        var items = Object.values(res.history)[0];
                        $.each(items, function (i, h) {
                            if (h.Koreksi && h.Koreksi.match(/^-?\d+$/)) {
                                var tr = $('tr[data-group="' + h.Category_Group + '"][data-item="' + h.Category_Item + '"]');
                                tr.find('.koreksi-input').val(parseInt(h.Koreksi));
                            }
                        });
                        recalcGT();
                        renderWaPreview(currentData, date);
                    }
                });
        }

        // ── Save History + Koreksi ──
        $('#saveHistoryBtn').on('click', function () {
            if (!currentData || !currentData.rows) return;
            var date = $('#filterDate').val();

            // Collect koreksi values
            recalcGT();
            var koreksiRows = window._koreksiData || [];

            // First, save history with current data
            $.ajax({
                url: '{{ route("api.admin.wa-rangkuman.save-history") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    date: date,
                    rows: currentData.rows
                },
                success: function () {
                    // Then save koreksi values
                    $.ajax({
                        url: '{{ route("api.admin.wa-rangkuman.koreksi") }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            date: date,
                            rows: koreksiRows
                        },
                        success: function (res) {
                            showToast('Log & koreksi tersimpan', 'success');
                        },
                        error: function () {
                            showToast('Gagal simpan koreksi', 'error');
                        }
                    });
                },
                error: function () {
                    showToast('Gagal simpan log', 'error');
                }
            });
        });

        // ── Copy WA ──
        $('#copyWaBtn').on('click', function () {
            var text = $('#waPreview').text();
            if (!text || text === 'Loading...') return;
            function doCopy(t) {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(t).then(function () {
                        showToast('Teks berhasil disalin!', 'success');
                    }).catch(function () { fallbackCopy(t); });
                } else { fallbackCopy(t); }
            }
            function fallbackCopy(t) {
                var ta = document.createElement('textarea');
                ta.value = t; ta.style.position = 'fixed'; ta.style.left = '-9999px';
                document.body.appendChild(ta); ta.select();
                try { document.execCommand('copy'); showToast('Teks berhasil disalin!', 'success'); }
                catch (e) { showToast('Gagal menyalin teks.', 'error'); }
                document.body.removeChild(ta);
            }
            doCopy(text);
        });

        // ── Kirim WA Langsung via Proxy ──
        $('#sendWaBtn').on('click', function () {
            var text = $('#waPreview').text();
            if (!text || text === 'Loading...') {
                showToast('Tidak ada data untuk dikirim', 'warning');
                return;
            }

            var btn = $(this);
            btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Mengirim...');

            var proxyUrl = 'https://wablas-proxy.isekipandu.workers.dev';
            var waToken = 'NOFl7qr6DjYqG4jiy3MOmecZrzPfqkCeLQh76lpawgIRAi6ZSKfPXOB.c1eWrwl7';
            var groupId = '{{ config("app.wa_rangkuman_group_id", "") }}';

            if (!groupId) {
                showToast('Group ID WA belum diisi di config', 'error');
                btn.prop('disabled', false).html('<i class="bx bxl-whatsapp me-1"></i> Kirim WA');
                return;
            }

            fetch(proxyUrl + '/api/v2/send-message', {
                method: 'POST',
                headers: {
                    'Authorization': waToken,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    data: [{
                        phone: groupId,
                        message: text,
                        isGroup: 'true'
                    }]
                })
            })
            .then(function (resp) { return resp.json(); })
            .then(function (result) {
                if (result.status === true || result.status === 'success') {
                    showToast('WA berhasil dikirim!', 'success');
                } else {
                    showToast('Gagal: ' + JSON.stringify(result), 'error');
                }
            })
            .catch(function (err) {
                showToast('Network error: ' + err.message, 'error');
            })
            .finally(function () {
                btn.prop('disabled', false).html('<i class="bx bxl-whatsapp me-1"></i> Kirim WA');
            });
        });



        $('#applyFilter').on('click', function () { loadData($('#filterDate').val()); });
        loadData($('#filterDate').val());

        // ── Auto refresh setiap 5 menit ──
        setInterval(function () {
            var date = $('#filterDate').val();
            $.getJSON('{{ route("api.admin.wa-rangkuman.data") }}', { date: date })
                .done(function (res) {
                    currentData = res;
                    renderTable(res);
                    renderWaPreview(res, date);
                    loadKoreksiFromHistory(date);
                    $('#updateTimestamp').text('Update: ' + new Date().toLocaleString('id-ID'));
                });
        }, 300000); // 5 menit = 300.000 ms

        // ── Prev/Next Day Navigation ──
        $('#prevDateBtn').on('click', function () {
            var d = new Date($('#filterDate').val() + 'T00:00:00');
            d.setDate(d.getDate() - 1);
            var ys = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
            $('#filterDate').val(ys);
            $('#applyFilter').click();
        });

        $('#nextDateBtn').on('click', function () {
            var d = new Date($('#filterDate').val() + 'T00:00:00');
            d.setDate(d.getDate() + 1);
            var ys = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
            $('#filterDate').val(ys);
            $('#applyFilter').click();
        });

        // ── History Modal — Monthly View ──
        function loadMonthlyData() {
            var month = $('#histMonth').val();
            if (!month) return;

            $('#histMonthHead').html('<tr><th style="min-width:130px;">Category Group</th><th style="min-width:120px;">Category Item</th><th style="width:35px;">T/A</th></tr>');
            $('#histMonthBody').html('<tr><td colspan="3" class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm text-warning"></div> Memuat data...</td></tr>');

            // Use the first day of month as date param
            var date = month + '-01';

            $.getJSON('{{ route("api.admin.wa-rangkuman.harian-data") }}', { date: date })
                .done(function (res) {
                    if (!res.success || !res.rows || res.rows.length === 0) {
                        $('#histMonthHead').html('<tr><th style="min-width:130px;">Category Group</th><th style="min-width:120px;">Category Item</th><th style="width:35px;">T/A</th></tr>');
                        $('#histMonthBody').html('<tr><td colspan="3" class="text-center text-muted py-4">Belum ada data untuk bulan ini</td></tr>');
                        return;
                    }

                    var days = res.daysInMonth || 30;
                    // Build header
                    var headHtml = '<tr><th style="width:90px;">Category Group</th><th style="width:75px;">Category Item</th><th style="width:5px;">T/A</th>';
                    for (var d = 1; d <= days; d++) {
                        headHtml += '<th class="text-center" style="width:24px;padding:2px;">' + d + '</th>';
                    }
                    headHtml += '</tr>';
                    $('#histMonthHead').html(headHtml);

                    // Build body
                    var bodyHtml = '';
                    $.each(res.rows, function (gi, group) {
                        console.log(gi,group)
                        var groupFirst = true;
                        $.each(group.items, function (ii, item) {
                            var grpLabel = groupFirst ? '<strong>' + group.group + '</strong>' : '';

                            // T row
                            bodyHtml += '<tr>';
                            if(groupFirst) bodyHtml += '<td style="padding:2px 4px;" rowspan="'+group.items.length *2+'">' + grpLabel + '</td>';
                            if (groupFirst) groupFirst = false;
                            bodyHtml += '<td style="padding:2px 4px;" rowspan="2">' + item.label + '</td>';
                            bodyHtml += '<td class="text-center fw-bold" style="padding:2px">T</td>';
                            for (var d = 1; d <= days; d++) {
                                var val = item.targets[d] || 0;
                                bodyHtml += '<td class="text-center" style="padding:2px;">' + (val > 0 ? val : '-') + '</td>';
                            }
                            bodyHtml += '</tr>';

                            // A row
                            bodyHtml += '<tr>';
                            // bodyHtml += '<td style="padding:2px 4px;"></td>';
                            // bodyHtml += '<td style="padding:2px 4px;"></td>';
                            bodyHtml += '<td class="text-center fw-bold" style="padding:2px;">A</td>';
                            for (var d = 1; d <= days; d++) {
                                var val = item.actuals[d] || 0;
                                var tVal = item.targets[d] || 0;
                                var isHistory = item.history && item.history[d];
                                var color = '';
                                var bg = '';

                                if (val === 0) {
                                    color = 'text-muted';
                                } else if (tVal === 0) {
                                    color = 'text-muted';
                                } else if (val > tVal) {
                                    color = 'text-success fw-bold';   // 🟢 hijau = A > T
                                    bg = 'style="background:#e4fce8;padding:2px;"';
                                } else if (val == tVal) {
                                    color = 'fw-bold';                // ⚪ putih = A == T
                                    bg = 'style="padding:2px;"';
                                } else {
                                    color = 'text-danger fw-bold';    // 🔴 merah = A < T
                                    bg = 'style="background:#fce4e4;padding:2px;"';
                                }

                                var dot = isHistory ? '<span style="color:#ff9800;font-size:10px;margin-right:1px;">●</span>' : '';
                                bodyHtml += '<td class="text-center ' + color + '" ' + (bg || 'style="padding:2px;"') + '>' + dot + (val > 0 ? val : '-') + '</td>';
                            }
                            bodyHtml += '</tr>';
                        });
                    });



                    $('#histMonthBody').html(bodyHtml);
                })
                .fail(function () {
                    $('#histMonthBody').html('<tr><td colspan="3" class="text-center text-danger py-4">Gagal memuat data</td></tr>');
                });
        }

        // Open modal — default to filter date's month
        function setMonthLabel(m) {
            var names = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            var parts = m.split('-');
            $('#histMonthTitle').text(names[parseInt(parts[1])-1] + ' ' + parts[0]);
        }

        $('#historyModalBtn').on('click', function () {
            var filterDate = $('#filterDate').val();
            var m = filterDate ? filterDate.substring(0, 7) : new Date().toISOString().substring(0, 7);
            $('#histMonth').val(m);
            setMonthLabel(m);
            loadMonthlyData();
            $('#historyModal').modal('show');
        });

        $('#histLoadBtn').on('click', function () {
            loadMonthlyData();
        });

        $('#histPrevMonth').on('click', function () {
            var m = $('#histMonth').val();
            if (!m) return;
            var d = new Date(m + '-01');
            d.setMonth(d.getMonth() - 1);
            var ys = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0');
            $('#histMonth').val(ys);
            setMonthLabel(ys);
            loadMonthlyData();
        });

        $('#histNextMonth').on('click', function () {
            var m = $('#histMonth').val();
            if (!m) return;
            var d = new Date(m + '-01');
            d.setMonth(d.getMonth() + 1);
            var ys = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0');
            $('#histMonth').val(ys);
            setMonthLabel(ys);
            loadMonthlyData();
        });

        // Export Excel
        $('#exportMonthlyBtn').on('click', function () {
            var m = $('#histMonth').val();
            if (!m) return;
            var url = '{{ route("api.admin.wa-rangkuman.export-monthly") }}?date=' + m + '-01';
            window.open(url, '_blank');
        });
    });
</script>
@endsection
