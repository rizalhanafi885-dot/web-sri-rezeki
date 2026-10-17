@extends('layouts.admin')

@section('title', 'Admin Dashboard - Sumber Rezeki')

@section('content')
    <h1 class="page-heading">Dashboard Operasional</h1>

    <!-- KPI ROW 1 (4 Kartu Warna) -->
    <div class="kpi-row-top">
        <!-- Saldo Kas (Biru) -->
        <div class="kpi-card kpi-blue">
            <div>
                <div class="kpi-label">Saldo Kas</div>
                <div class="kpi-value">{{ $kpi['saldo_kas'] }}</div>
                <div class="kpi-desc">{{ $kpi['saldo_kas_desc'] }}</div>
            </div>
            <div class="kpi-icon">
                <i class="fa-solid fa-wallet"></i>
            </div>
        </div>

        <!-- Pembelian Hari Ini (Hijau) -->
        <div class="kpi-card kpi-green">
            <div>
                <div class="kpi-label">Pembelian Hari Ini</div>
                <div class="kpi-value">{{ $kpi['pembelian_hari_ini_kg'] }}</div>
                <div class="kpi-desc">{{ $kpi['pembelian_hari_ini_rp'] }}</div>
            </div>
            <div class="kpi-icon">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
        </div>

        <!-- Pengiriman Hari Ini (Kuning / Oranye) -->
        <div class="kpi-card kpi-yellow">
            <div>
                <div class="kpi-label">Pengiriman Hari Ini</div>
                <div class="kpi-value">{{ $kpi['pengiriman_hari_ini_kg'] }}</div>
                <div class="kpi-desc">{{ $kpi['pengiriman_hari_ini_desc'] }}</div>
            </div>
            <div class="kpi-icon">
                <i class="fa-solid fa-truck"></i>
            </div>
        </div>

        <!-- Stok TBS Tersedia (Cyan) -->
        <div class="kpi-card kpi-cyan">
            <div>
                <div class="kpi-label">Stok TBS Tersedia</div>
                <div class="kpi-value">{{ $kpi['stok_tbs_tersedia'] }}</div>
                <div class="kpi-desc">{{ $kpi['stok_tbs_desc'] }}</div>
            </div>
            <div class="kpi-icon">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
        </div>
    </div>

    <!-- KPI ROW 2 (Total Piutang - Merah) -->
    <div class="kpi-row-bottom">
        <div class="kpi-card kpi-red">
            <div>
                <div class="kpi-label">Total Piutang</div>
                <div class="kpi-value">{{ $kpi['total_piutang'] }}</div>
                <div class="kpi-desc">{{ $kpi['total_piutang_desc'] }}</div>
            </div>
            <div class="kpi-icon">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
        </div>
    </div>

    <!-- SECTION BAWAH: GRAFIK & RINGKASAN STOK -->
    <div class="charts-section">
        <!-- Grafik Pembelian & Pengiriman -->
        <div class="panel-card">
            <h2 class="panel-title">Grafik Pembelian & Pengiriman (Bulan Ini)</h2>
            <div style="height: 320px; width: 100%;">
                <canvas id="grafikPembelianPengiriman"></canvas>
            </div>
        </div>

        <!-- Panel Ringkasan Stok -->
        <div class="panel-card">
            <h2 class="panel-title">Ringkasan Stok</h2>

            @foreach($ringkasan_stok as $item)
                <div class="stock-item">
                    <div class="stock-name-val">
                        <div class="name">{{ $item['nama'] }}</div>
                        <div class="val">Rp {{ number_format($item['nilai'], 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <span class="stock-badge">{{ $item['berat'] }}</span>
                    </div>
                </div>
            @endforeach

            <div class="stock-total-footer">
                <span class="total-label">Total Nilai Stok:</span>
                <span class="total-val">Rp {{ number_format($total_nilai_stok, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    const ctx = document.getElementById('grafikPembelianPengiriman').getContext('2d');
    const dashboardChartData = @json($chartData);
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
            datasets: [
                {
                    label: 'Pembelian (Ton)',
                    data: dashboardChartData.purchases,
                    backgroundColor: '#16a34a',
                    borderRadius: 4,
                },
                {
                    label: 'Pengiriman (Ton)',
                    data: dashboardChartData.shipments,
                    backgroundColor: '#facc15',
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    align: 'center',
                    labels: {
                        boxWidth: 14,
                        boxHeight: 10,
                        usePointStyle: false,
                        font: {
                            family: 'Inter',
                            size: 11
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    min: 0,
                    ticks: {
                        color: '#64748b',
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        color: '#f1f5f9'
                    }
                },
                x: {
                    ticks: {
                        color: '#64748b',
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
</script>
@endsection
