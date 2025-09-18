@extends('layouts.main')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-8 mb-4 order-0">
            <div class="row mb-4">
                <div class="card">
                    <div class="d-flex align-items-end row">
                        <div class="col-sm-7">
                            <div class="card-body">
                                <h5 class="card-title">Welcome <span class="text-primary">{{ $user->Name_User }}</span>!</h5>
                                <span class="fw-semibold d-block mb-1" id="hari-ini"></span>
                                <h3 class="card-title mb-2" id="tanggal-hari-ini"></h3>
                                <span class="text-success fw-semibold">
                                    <i class="bx bx-time"></i> 
                                    <span id="jam-menit-detik"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="card">
                    <div class="row row-bordered g-0">
                        <div class="col-md-12">
                            <h5 class="card-header m-0 me-2 pb-3">Plan <span id="year" class="text-primary">0</span></h5>
                            <div id="totalRevenueChart" class="px-2"></div>
                        </div>
                        {{-- <div class="col-md-4">
                            <div class="card-body">
                                <div class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                                            id="growthReportId" data-bs-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                            2022
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="growthReportId">
                                            <a class="dropdown-item" href="javascript:void(0);">2021</a>
                                            <a class="dropdown-item" href="javascript:void(0);">2020</a>
                                            <a class="dropdown-item" href="javascript:void(0);">2019</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="growthChart"></div>
                            <div class="text-center fw-semibold pt-3 mb-2">62% Company Growth</div>

                            <div class="d-flex px-xxl-4 px-lg-2 p-4 gap-xxl-3 gap-lg-1 gap-3 justify-content-between">
                                <div class="d-flex">
                                    <div class="me-2">
                                        <span class="badge bg-label-primary p-2"><i
                                                class="bx bx-dollar text-primary"></i></span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <small>2022</small>
                                        <h6 class="mb-0">$32.5k</h6>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <div class="me-2">
                                        <span class="badge bg-label-info p-2"><i class="bx bx-wallet text-info"></i></span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <small>2021</small>
                                        <h6 class="mb-0">$41.2k</h6>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 order-1">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between pb-0">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2">
                            Plan <span id="year1" class="text-primary">0</span>
                        </h5>
                        <small class="text-muted">
                            <span id="annual-count" class="text-primary">0</span> Products
                        </small>
                    </div>
                    {{-- <div class="dropdown">
                        <button class="btn p-0" type="button" id="orederStatistics" data-bs-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="orederStatistics">
                            <a class="dropdown-item" href="javascript:void(0);">Select All</a>
                            <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                            <a class="dropdown-item" href="javascript:void(0);">Share</a>
                        </div>
                    </div> --}}
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex flex-column align-items-center gap-1">
                            <h2 id="monthly-count" class="mb-2 text-primary">0</h2>
                            <span>This Month Plans</span>
                        </div>
                        <div id="orderStatisticsChart"></div>
                    </div>

                    <ul class="p-0 m-0" id="type-list"></ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
{{-- <script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script> --}}
<script>
    function updateDateTime() {
        let now = new Date();

        // Nama hari
        let hariList = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
        let hari = hariList[now.getDay()];
        document.getElementById('hari-ini').textContent = hari;

        // Tanggal (contoh: 16 September 2025)
        let options = { year: 'numeric', month: 'long', day: 'numeric' };
        let tanggal = now.toLocaleDateString('id-ID', options);
        document.getElementById('tanggal-hari-ini').textContent = tanggal;

        // Jam real-time (contoh: 14:35:07)
        let waktu = now.toLocaleTimeString('id-ID', { hour12: false });
        document.getElementById('jam-menit-detik').textContent = waktu;
    }

    // Update pertama kali
    updateDateTime();

    // Update tiap detik
    setInterval(updateDateTime, 1000);

    document.addEventListener("DOMContentLoaded", function () {
        fetch("/iseki_prodas/public/api/api_dashboard")
            .then(res => res.json())
            .then(data => {
                // update year & annualCount
                document.getElementById("year").textContent = data.year;
                document.getElementById("year1").textContent = data.year;
                document.getElementById("annual-count").textContent = data.annualCount;

                // update monthly count
                document.getElementById("monthly-count").textContent = data.monthlyCount;

                // build list type
                let list = document.getElementById("type-list");
                list.innerHTML = "";
                data.types.forEach(stat => {
                    list.innerHTML += `
                        <li class="d-flex mb-4 pb-1">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-${stat.color}">
                                    <i class="bx bx-star"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">${stat.type}</h6>
                                    <small class="text-muted">Monthly count</small>
                                </div>
                                <div class="user-progress">
                                    <small class="fw-semibold">${stat.total}</small>
                                </div>
                            </div>
                        </li>
                    `;
                });

                // build chart (tetap sama seperti sebelumnya)
                const chartOrderStatistics = document.querySelector('#orderStatisticsChart');
                const orderChartConfig = {
                    chart: { height: 165, width: 130, type: 'donut' },
                    labels: data.types.map(stat => stat.type),
                    series: data.types.map(stat => stat.total),
                    colors: data.types.map(stat => config.colors[stat.color]),
                    stroke: { width: 5, colors: window.cardColor },
                    dataLabels: { enabled: false },
                    legend: { show: false },
                    grid: { padding: { top: 0, bottom: 0, right: 15 } },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '75%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        fontSize: '0.8125rem',
                                        color: window.axisColor,
                                        label: 'Monthly',
                                        formatter: function () {
                                            return data.monthlyCount;
                                        }
                                    }
                                }
                            }
                        }
                    }
                };
                if (chartOrderStatistics) {
                    const statisticsChart = new ApexCharts(chartOrderStatistics, orderChartConfig);
                    statisticsChart.render();
                }
            })
            .catch(err => console.error("Error loading dashboard data:", err));
    });

    let cardColor, headingColor, axisColor, shadeColor, borderColor;

    cardColor = config.colors.white;
    headingColor = config.colors.headingColor;
    axisColor = config.colors.axisColor;
    borderColor = config.colors.borderColor;
    
    document.addEventListener("DOMContentLoaded", function () {
        fetch("/iseki_prodas/public/api/api_dashboard2")
            .then(res => res.json())
            .then(data => {
                const totalRevenueChartEl = document.querySelector('#totalRevenueChart');
                const totalRevenueChartOptions = {
                    series: data.series, // array of {name, data}
                    chart: {
                        height: 500,
                        stacked: true,
                        type: 'bar',
                        toolbar: { show: true }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '20%',
                            borderRadius: 12,
                            // borderRadiusApplication: 'end', // 'around', 'end'
                            // borderRadiusWhenStacked: 'last', // 'all', 'last'
                            // startingShape: 'rounded',
                            // endingShape: 'rounded',
                        }
                    },
                    colors: [
                        config.colors.primary,
                        config.colors.info,
                        config.colors.success,
                        config.colors.warning,
                        config.colors.danger,
                        config.colors.secondary
                    ],
                    dataLabels: { enabled: true },
                    stroke: {
                        curve: 'smooth',
                        width: 0,
                        lineCap: 'round',
                        colors: [cardColor]
                    },
                    legend: {
                        show: true,
                        horizontalAlign: 'left',
                        position: 'top',
                        labels: { colors: axisColor },
                        itemMargin: { horizontal: 10 }
                    },
                    grid: {
                        borderColor: borderColor,
                        padding: { top: 0, bottom: -8, left: 20, right: 20 }
                    },
                    xaxis: {
                        categories: data.months, // hasilnya ['Jan','Feb',...]
                        labels: { style: { fontSize: '13px', colors: axisColor } },
                        axisTicks: { show: false },
                        axisBorder: { show: false }
                    },
                    yaxis: {
                        labels: { style: { fontSize: '13px', colors: axisColor } }
                    }
                };
                if (totalRevenueChartEl) {
                    const totalRevenueChart = new ApexCharts(totalRevenueChartEl, totalRevenueChartOptions);
                    totalRevenueChart.render();
                }
            })
            .catch(err => console.error("Error loading plans data:", err));
    });
</script>
@endsection