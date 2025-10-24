<div class="container-fluid">
    <!-- Header Section -->
    <section class="content-header mb-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="font-weight-bold text-primary">
                    <i class="fas fa-balance-scale mr-2"></i>
                    Perbandingan Laporan Harga
                </h1>

                <a href="<?= base_url('Dashboard_new/index') ?>" class="btn btn-outline-secondary btn-lg shadow-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Menu
                </a>
            </div>
        </div>
    </section>

    <section class="content">
        <!-- Filter Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 font-weight-bold text-secondary">
                            <i class="fas fa-sliders-h mr-2"></i>Filter Perbandingan
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="<?= base_url('Dashboard_new/visual_harga_compare') ?>" method="GET">
                            <div class="row">
                                <!-- Komoditas 1 -->
                                <div class="col-lg-4 mb-3 mb-lg-0">
                                    <label for="komoditas1" class="font-weight-bold text-secondary mb-2">
                                        <i class="fas fa-box mr-1"></i>Komoditas 1
                                    </label>
                                    <select id="komoditas1" name="komoditas1" class="custom-select custom-select-lg shadow-sm">
                                        <?php foreach ($all_komoditas as $value => $text): ?>
                                            <option value="<?= $value ?>" <?= ($selected_komoditas1 == $value) ? 'selected' : '' ?>>
                                                <?= $text ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Komoditas 2 -->
                                <div class="col-lg-4 mb-3 mb-lg-0">
                                    <label for="komoditas2" class="font-weight-bold text-secondary mb-2">
                                        <i class="fas fa-box mr-1"></i>Komoditas 2
                                    </label>
                                    <select id="komoditas2" name="komoditas2" class="custom-select custom-select-lg shadow-sm">
                                        <?php foreach ($all_komoditas as $value => $text): ?>
                                            <option value="<?= $value ?>" <?= ($selected_komoditas2 == $value) ? 'selected' : '' ?>>
                                                <?= $text ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Filter Tahun -->
                                <div class="col-lg-2 mb-3 mb-lg-0">
                                    <label for="tahun_filter" class="font-weight-bold text-secondary mb-2">
                                        <i class="far fa-calendar-alt mr-1"></i>Tahun
                                    </label>
                                    <select id="tahun_filter" name="tahun" class="custom-select custom-select-lg shadow-sm">
                                        <option value="semua" <?= ($selected_tahun == 'semua') ? 'selected' : '' ?>>Semua Tahun</option>
                                        <?php 
                                        $current_year = date('Y');
                                        for ($y = $current_year; $y >= $current_year - 5; $y--): 
                                        ?>
                                            <option value="<?= $y ?>" <?= ($selected_tahun == $y) ? 'selected' : '' ?>><?= $y ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <!-- Button Submit -->
                                <div class="col-lg-2">
                                    <label class="d-block mb-2 invisible">Submit</label>
                                    <button type="submit" class="btn btn-primary btn-lg btn-block shadow-sm">
                                        <i class="fas fa-search mr-2"></i> Tampilkan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Cards -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #007bff !important;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 mr-3">
                                <i class="fas fa-chart-line fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Komoditas 1</h6>
                                <h4 class="mb-0 font-weight-bold text-primary">
                                    <?= $all_komoditas[$selected_komoditas1] ?? 'Harga Telur Layer' ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #28a745 !important;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 mr-3">
                                <i class="fas fa-chart-line fa-2x text-success"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Komoditas 2</h6>
                                <h4 class="mb-0 font-weight-bold text-success">
                                    <?= $all_komoditas[$selected_komoditas2] ?? 'Harga Jagung' ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Card -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-lg">
                    <div class="card-header border-0 py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h3 class="mb-0 font-weight-bold text-white">
                            <i class="fas fa-chart-line mr-2"></i>
                            Grafik Perbandingan Harga Bulanan
                            <?php if ($selected_tahun != 'semua'): ?>
                                <span class="badge badge-light ml-2"><?= $selected_tahun ?></span>
                            <?php endif; ?>
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if (empty(json_decode($chart_labels)) || json_decode($chart_labels) == []): ?>
                            <div class="alert alert-info text-center py-5">
                                <i class="fas fa-info-circle fa-3x mb-3"></i>
                                <h5>Tidak ada data untuk tahun yang dipilih</h5>
                                <p class="mb-0">Silakan pilih tahun yang berbeda atau pilih "Semua Tahun"</p>
                            </div>
                        <?php else: ?>
                            <div style="height: 500px;">
                                <canvas id="hargaCompareChart"></canvas>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        
    </section>
</div>

<style>
.bg-opacity-10 {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

.bg-success.bg-opacity-10 {
    background-color: rgba(40, 167, 69, 0.1) !important;
}

.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.custom-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.border-right {
    border-right: 1px solid #dee2e6;
}

@media (max-width: 768px) {
    .border-right {
        border-right: none;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 1rem;
    }
    
    .border-right:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
}

.shadow-lg {
    box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    
    const formatRupiah = (value) => 'Rp ' + new Intl.NumberFormat('id-ID').format(value);

    const chartLabels = <?php echo $chart_labels; ?>;
    const chartDatasets = <?php echo $chart_datasets; ?>;
    const tooltipOptions = {
        plugins: {
            tooltip: {
                callbacks: {
                    label: context => {
                        let label = context.dataset.label || '';
                        if (label) label += ': ';
                        if (context.parsed.y !== null) label += formatRupiah(context.parsed.y);
                        return label;
                    }
                },
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                cornerRadius: 8,
                titleFont: {
                    size: 14,
                    weight: 'bold'
                },
                bodyFont: {
                    size: 13
                }
            },
            legend: {
                labels: {
                    font: {
                        size: 14,
                        weight: 'bold'
                    },
                    padding: 20,
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            }
        },
        scales: {
            y: { 
                ticks: { 
                    callback: value => formatRupiah(value),
                    font: {
                        size: 11
                    }
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            },
            x: {
                ticks: {
                    font: {
                        size: 11
                    }
                },
                grid: {
                    display: false
                }
            }
        },
        maintainAspectRatio: false,
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false
        }
    };

    if (chartLabels && chartLabels.length > 0) {
        const ctxCompare = document.getElementById('hargaCompareChart').getContext('2d');
        
        // Enhanced datasets with better styling
        const enhancedDatasets = chartDatasets.map((dataset, index) => {
            return {
                ...dataset,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointBackgroundColor: dataset.borderColor,
                fill: true
            };
        });

        new Chart(ctxCompare, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: enhancedDatasets
            },
            options: tooltipOptions
        });
    }
});
</script>
