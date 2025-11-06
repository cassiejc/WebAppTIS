<?php
/**
 * Helper Function
 * Merender blok stat harga terakhir di dalam card-body
 */
function render_stat_block($title, $data, $color) {
    $harga_display = "Belum Tersedia";
    
    if ($data && isset($data['nilai_rata_rata']) && $data['nilai_rata_rata'] > 0) {
        $harga_display = "Rp " . number_format($data['nilai_rata_rata'], 0, ',', '.');
    }
?>
<div class="stat-block mb-3 bg-light p-2 rounded" style="border-left: 4px solid <?php echo $color; ?>;">
    <div class="text-xs font-weight-bold mb-1" style="color: <?php echo $color; ?>;">
        <?php echo $title; ?> </div>
    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $harga_display; ?></div>
</div>
<hr class="mt-2 mb-3">
<?php } 
?>

<div class="container-fluid">
    <section class="content-header mb-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="font-weight-bold text-primary">
                    <i class="fas fa-chart-line mr-2"></i>
                    Laporan Harga Komoditas Utama
                </h1>
                <a href="<?= base_url('Dashboard_new/index') ?>" class="btn btn-outline-secondary btn-lg shadow-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Menu
                </a>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="row">

            <div class="col-lg-12 mb-4"> 
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-chart-line mr-2"></i>Grafik Bulanan: Telur Layer</h5>
                    </div>
                    <div class="card-body"> 
                        <?php render_stat_block('Telur Layer', $latest_telur, '#007bff'); ?>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="chartTelurLayer"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 mb-4"> 
                <div class="card shadow-sm h-100">
                    <div class="card-header text-white" style="background-color: #17a2b8;">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-chart-line mr-2"></i>Grafik Bulanan: Telur Puyuh</h5>
                    </div>
                    <div class="card-body">
                        <?php render_stat_block('Telur Puyuh', $latest_puyuh, '#17a2b8'); ?>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="chartTelurPuyuh"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        
            <div class="col-lg-12 mb-4"> 
                <div class="card shadow-sm h-100">
                    <div class="card-header text-white" style="background-color: #28a745;">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-chart-line mr-2"></i>Grafik Bulanan: Telur Bebek</h5>
                    </div>
                    <div class="card-body">
                        <?php render_stat_block('Telur Bebek', $latest_bebek, '#28a745'); ?>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="chartTelurBebek"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 mb-4"> 
                <div class="card shadow-sm h-100">
                    <div class="card-header text-white" style="background-color: #ffc107;">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-chart-line mr-2"></i>Grafik Bulanan: Live Bird</h5>
                    </div>
                    <div class="card-body">
                        <?php render_stat_block('Live Bird', $latest_lb, '#ffc107'); ?>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="chartLiveBird"></canvas>
                        </div>
                    </div>
                </div>
            </div>
       
            <div class="col-lg-12 mb-4"> 
                <div class="card shadow-sm h-100">
                    <div class="card-header text-white" style="background-color: #dc3545;">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-chart-line mr-2"></i>Grafik Bulanan: Afkir</h5>
                    </div>
                    <div class="card-body">
                        <?php render_stat_block('Afkir', $latest_afkir, '#dc3545'); ?>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="chartAfkir"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
        </div> </section>
</div>

<style>
.card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08) !important;
}
.text-xs {
    font-size: 0.75rem;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {

    const formatRupiah = (value) => 'Rp ' + new Intl.NumberFormat('id-ID').format(value);

    // Opsi Global untuk Tooltip dan Legend
    const chartGlobalOptions = {
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
                titleFont: { size: 14, weight: 'bold' },
                bodyFont: { size: 13 }
            },
            legend: {
                position: 'top', 
                align: 'end',
                labels: {
                    font: { size: 13, weight: 'bold' },
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
                    font: { size: 11 }
                },
                grid: { 
                    color: 'rgba(0, 0, 0, 0.05)',
                    drawOnChartArea: false
                }
            },
            x: {
                ticks: { font: { size: 11, weight: 'bold' } },
                grid: { display: false }
            }
        },
        maintainAspectRatio: false,
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
    };

    /**
     * Helper Function BARU untuk membuat Grafik
     */
    function createMonthlyChart(canvasId, chartJsonData) {
        if (!document.getElementById(canvasId)) return;
        
        let chartData;
        try {
            chartData = JSON.parse(chartJsonData);
        } catch (e) {
            console.error("Gagal parse data chart:", e, chartJsonData);
            return;
        }

        const ctx = document.getElementById(canvasId).getContext('2d');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: chartData.datasets
            },
            options: chartGlobalOptions
        });
    }

    // 1. Buat Grafik Telur Layer
    createMonthlyChart(
        'chartTelurLayer',
        '<?php echo $chart_telur; ?>'
    );

    // 2. Buat Grafik Telur Puyuh
    createMonthlyChart(
        'chartTelurPuyuh',
        '<?php echo $chart_puyuh; ?>'
    );

    // 3. Buat Grafik Telur Bebek
    createMonthlyChart(
        'chartTelurBebek',
        '<?php echo $chart_bebek; ?>'
    );

    // 4. Buat Grafik Live Bird
    createMonthlyChart(
        'chartLiveBird',
        '<?php echo $chart_lb; ?>'
    );

    // 5. Buat Grafik Afkir
    createMonthlyChart(
        'chartAfkir',
        '<?php echo $chart_afkir; ?>'
    );

});
</script>
