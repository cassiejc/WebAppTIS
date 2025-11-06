<?php
// Helper Function untuk membuat Stat Card
function display_price_card($title, $data, $icon, $borderColor) {
    $harga_display = "Belum Tersedia";
    $tanggal_display = "N/A";
    
    if ($data && isset($data['nilai_rata_rata']) && $data['nilai_rata_rata'] > 0) {
        $harga_display = "Rp " . number_format($data['nilai_rata_rata'], 0, ',', '.');
        $tanggal_display = "Per " . date('d M Y', strtotime($data['tanggal']));
    }
?>
<div class="col-lg col-md-6 mb-4">
    <div class="card shadow-sm h-100" style="border-left: 4px solid <?php echo $borderColor; ?> !important;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mr-2">
                    <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: <?php echo $borderColor; ?>;">
                        <?php echo $title; ?>
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $harga_display; ?></div>
                    <small class="text-muted"><?php echo $tanggal_display; ?></small>
                </div>
                <div class="col-auto">
                    <i class="fas <?php echo $icon; ?> fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>

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
        <div class="row mb-3">
            <?php 
                display_price_card('Telur Layer', $latest_telur, 'fa-egg', '#007bff');
                display_price_card('Telur Puyuh', $latest_puyuh, 'fa-egg', '#17a2b8');
                display_price_card('Telur Bebek', $latest_bebek, 'fa-egg', '#28a745');
                display_price_card('Live Bird', $latest_lb, 'fa-dove', '#ffc107');
                display_price_card('Afkir', $latest_afkir, 'fa-drumstick-bite', '#dc3545');
            ?>
        </div>

        <div class="row">

            <div class="col-lg-12 mb-4"> <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-chart-line mr-2"></i>Grafik Bulanan: Telur Layer (<?php echo $display_year; ?>)</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="chartTelurLayer"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 mb-4"> <div class="card shadow-sm h-100">
                    <div class="card-header text-white" style="background-color: #17a2b8;">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-chart-line mr-2"></i>Grafik Bulanan: Telur Puyuh (<?php echo $display_year; ?>)</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="chartTelurPuyuh"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        
            <div class="col-lg-12 mb-4"> <div class="card shadow-sm h-100">
                    <div class="card-header text-white" style="background-color: #28a745;">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-chart-line mr-2"></i>Grafik Bulanan: Telur Bebek (<?php echo $display_year; ?>)</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="chartTelurBebek"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 mb-4"> <div class="card shadow-sm h-100">
                    <div class="card-header text-white" style="background-color: #ffc107;">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-chart-line mr-2"></i>Grafik Bulanan: Live Bird (<?php echo $display_year; ?>)</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="chartLiveBird"></canvas>
                        </div>
                    </div>
                </div>
            </div>
       
            <div class="col-lg-12 mb-4"> <div class="card shadow-sm h-100">
                    <div class="card-header text-white" style="background-color: #dc3545;">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-chart-line mr-2"></i>Grafik Bulanan: Afkir (<?php echo $display_year; ?>)</h5>
                    </div>
                    <div class="card-body">
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

    // Opsi Global untuk Tooltip
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
                titleFont: { size: 14, weight: 'bold' },
                bodyFont: { size: 13 }
            },
            legend: {
                labels: {
                    font: { size: 13 },
                    padding: 15
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
                    drawOnChartArea: false // Sembunyikan grid Y
                }
            },
            x: {
                ticks: { font: { size: 11 } },
                grid: { display: false }
            }
        },
        maintainAspectRatio: false,
        responsive: true,
    };

    // Helper Function untuk membuat Grafik
    function createMonthlyChart(canvasId, chartLabels, chartData, label, borderColor, bgColor) {
        if (!document.getElementById(canvasId)) return;
        
        const ctx = document.getElementById(canvasId).getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: label,
                    data: chartData,
                    borderColor: borderColor,
                    backgroundColor: bgColor,
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 4,          // Tampilkan titik
                    pointHoverRadius: 6,     // Tampilkan titik saat hover
                    pointBackgroundColor: borderColor,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2      // Beri border pada titik
                }]
            },
            options: tooltipOptions
        });
    }

    // 1. Buat Grafik Telur Layer
    createMonthlyChart(
        'chartTelurLayer',
        <?php echo $chart_telur['labels']; ?>,
        <?php echo $chart_telur['data']; ?>,
        'Harga Rata-Rata (Rp)',
        '#007bff', 'rgba(0,123,255,0.1)'
    );

    // 2. Buat Grafik Telur Puyuh
    createMonthlyChart(
        'chartTelurPuyuh',
        <?php echo $chart_puyuh['labels']; ?>,
        <?php echo $chart_puyuh['data']; ?>,
        'Harga Rata-Rata (Rp)',
        '#17a2b8', 'rgba(23,162,184,0.1)'
    );

    // 3. Buat Grafik Telur Bebek
    createMonthlyChart(
        'chartTelurBebek',
        <?php echo $chart_bebek['labels']; ?>,
        <?php echo $chart_bebek['data']; ?>,
        'Harga Rata-Rata (Rp)',
        '#28a745', 'rgba(40,167,69,0.1)'
    );

    // 4. Buat Grafik Live Bird
    createMonthlyChart(
        'chartLiveBird',
        <?php echo $chart_lb['labels']; ?>,
        <?php echo $chart_lb['data']; ?>,
        'Harga Rata-Rata (Rp)',
        '#ffc107', 'rgba(255,193,7,0.1)'
    );

    // 5. Buat Grafik Afkir
    createMonthlyChart(
        'chartAfkir',
        <?php echo $chart_afkir['labels']; ?>,
        <?php echo $chart_afkir['data']; ?>,
        'Harga Rata-Rata (Rp)',
        '#dc3545', 'rgba(220,53,69,0.1)'
    );

});
</script>
