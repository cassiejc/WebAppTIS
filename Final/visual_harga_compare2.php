<?php
/**
 * Helper Function BARU
 * Merender blok stat harga terakhir (Style seperti di gambar)
 */
function render_stat_block($title, $data) {
    $harga_display = "0"; // Default jika tidak ada data
    
    if ($data && isset($data['nilai_rata_rata']) && $data['nilai_rata_rata'] > 0) {
        // Format angka seperti di gambar (e.g., "6,000")
        $harga_display = number_format($data['nilai_rata_rata'], 0, ',', ',');
    }
?>
<div class="card shadow-sm h-100" style="border-radius: 8px; border: none; background-color: #eeeeee;">
    <div class="card-body p-3 d-flex justify-content-between align-items-center">
        <span class="text-muted" style="font-size: 1.0rem; font-weight: 500;"><?php echo $title; ?></span>
        <span class="h5 mb-0 font-weight-bold text-dark" style="font-size: 2rem;"><?php echo $harga_display; ?></span>
    </div>
</div>
<?php } 
?>

<div class="container-fluid">
    
    <section class="content-header mb-4">
        <?php
            // Format tanggal seperti di gambar "Nov 7, 2025"
            $tanggal_hari_ini = date('M j, Y');
        ?>
        <div class="container-fluid">
            <div class="d-flex align-items-center p-3 bg-white shadow-sm" style="border-radius: 2rem;">
                
                <a href="<?= site_url('Dashboard_new/index') ?>" class="btn btn-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border-radius: 50%;">
                    <i class="fas fa-home fa-lg text-white"></i>
                </a>

                <h1 class="font-weight-bold text-dark mb-0 mx-auto" style="font-size: 2rem;">
                    Informasi Harga Produk
                </h1>
                
                <span class="h5 font-weight-bold text-dark mb-0" style="font-size: 1.25rem;"> 
                    <?php echo $tanggal_hari_ini; ?>
                </span>

            </div>
        </div>
    </section>

    <section class="content mb-4">
        <div class="card shadow-sm" style="border-radius: 8px; border: none;">
            <div class="card-body">
                <form action="<?= base_url('Dashboard_new/visual_harga_compare') ?>" method="POST" id="filterTahunForm">
                    <h5 class="font-weight-bold">Filter Tahun</h5>
                    <div class="d-flex flex-wrap align-items-center">
                        <?php if (empty($all_years)): ?>
                            <p class="text-muted">Tidak ada data tahun untuk difilter.</p>
                        <?php else: ?>
                            <?php foreach ($all_years as $year): ?>
                                <?php
                                    // Cek apakah tahun ini ada di array tahun yang terpilih
                                    $is_checked = in_array((string)$year, $selected_years, true);
                                ?>
                                <div class="form-check form-check-inline mr-3 mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="tahun[]" 
                                           value="<?php echo $year; ?>" 
                                           id="tahun-<?php echo $year; ?>"
                                           <?php echo $is_checked ? 'checked' : ''; ?>>
                                    <label class="form-check-label font-weight-bold" for="tahun-<?php echo $year; ?>">
                                        <?php echo $year; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <button type="submit" name="submit_filter" value="1" class="btn btn-primary btn-sm mb-2 ml-auto">
                            <i class="fas fa-filter mr-1"></i> Terapkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="content">
        
        <div class="p-4 rounded" style="background-color: #ffffffff;">
            
            <div class="row d-flex justify-content-center" style="position: relative;">
                <div class="col-md-3 d-flex flex-column justify-content-between">
                    <div class="mb-4">
                        <?php render_stat_block('Jagung', $stat_jagung); ?>
                    </div>
                    <div class="mb-4">
                        <?php render_stat_block('Bekatul', $stat_katul); ?>
                    </div>
                    <div class="mb-4">
                        <?php render_stat_block('Pakan Komplit Layer', $stat_pakan_layer); ?>
                    </div>
                    <div class="mb-4">
                        <?php render_stat_block('Pakan Komplit Broiler', $stat_pakan_broiler); ?>
                    </div>
                </div>

                <div class="col-md-6 d-flex flex-column align-items-center justify-content-center" style="min-height: 400px;">
                    <img src="<?= base_url('assets/image/jagung.jpg') ?>" alt="Jagung" class="img-fluid mb-3" 
                        style="max-height: 100px; position: relative; left: -100px;">
                    <img src="<?= base_url('assets/image/ayam2.jpg') ?>" alt="Ayam" class="img-fluid mb-3" 
                        style="max-height: 150px; position: relative; left: 170px;">  
                    <img src="<?= base_url('assets/image/ayam.jpg') ?>" alt="Ayam2" class="img-fluid" 
                        style="max-height: 250px; position: relative; left: 150px;">
                </div>

                <div class="col-md-3 d-flex flex-column justify-content-between">
                    <div class="mb-4">
                        <?php render_stat_block('Pakan Campuran', $stat_konsentrat); ?>
                    </div>
                    <div class="mb-4">
                        <?php render_stat_block('HPP Telur (Konsentrat)', $stat_hpp_konsentrat); ?>
                    </div>
                    <div class="mb-4">
                        <?php render_stat_block('HPP Telur (komplit)', $stat_hpp_komplit); ?>
                    </div>
                    <div class="mb-4">
                        <?php render_stat_block('HPP Broiler', $stat_hpp_broiler); ?>
                    </div>
                </div>

            </div>
        </div>
        <hr class="my-4">

        <div class="row">

            <div class="col-lg-12 mb-4"> 
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-chart-line mr-2"></i>Trend HPP (Konsentrat) vs Harga Telur</h5>
                    </div>
                    <div class="card-body"> 
                        <div id="legend-chart1" class="chart-legend-container" style="flex-grow: 1;"></div>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="chart1"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 mb-4"> 
                <div class="card shadow-sm h-100">
                    <div class="card-header text-white" style="background-color: #28a745;">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-chart-line mr-2"></i>Trend HPP (Komplit) vs Harga Telur</h5>
                    </div>
                    <div class="card-body">
                        <div id="legend-chart2" class="chart-legend-container" style="flex-grow: 1;"></div>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="chart2"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-12 mb-4"> 
                <div class="card shadow-sm h-100">
                    <div class="card-header text-white" style="background-color: #dc3545;">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-chart-line mr-2"></i>Trend HPP (Komplit Broiler) vs Harga Live Bird</h5>
                    </div>
                    <div class="card-body">
                        <div id="legend-chart3" class="chart-legend-container" style="flex-grow: 1;"></div>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="chart3"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 mb-4"> 
                <div class="card shadow-sm h-100">
                    <div class="card-header text-white" style="background-color: #ffc107;">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-chart-line mr-2"></i>Trend Harga Jagung</h5>
                    </div>
                    <div class="card-body">
                        <div id="legend-chart4" class="chart-legend-container" style="flex-grow: 1;"></div>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="chart4"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-12 mb-4"> 
                <div class="card shadow-sm h-100">
                    <div class="card-header text-white" style="background-color: #6c757d;">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-chart-line mr-2"></i>Trend Harga Katul</h5>
                    </div>
                    <div class="card-body">
                        <div id="legend-chart5" class="chart-legend-container" style="flex-grow: 1;"></div>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="chart5"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
        </div> 
    </section>
</div>

<style>
/* Style Tambahan */
body, .content-wrapper {
    background-color: #fff !important; /* Paksa background halaman jadi putih */
}

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
/* Style untuk Legend HTML Kustom */
.chart-legend-container ul {
    display: flex;
    flex-wrap: wrap;
    list-style: none;
    padding: 0;
    margin: 0 0 10px 0; /* Beri jarak bawah */
    justify-content: center; /* Menengahkan legend */
}
.chart-legend-container li {
    display: flex;
    align-items: center;
    margin: 0 10px;
    font-size: 13px;
    font-weight: bold;
    cursor: pointer; 
    transition: all 0.2s; 
}
.chart-legend-container li:hover {
    opacity: 0.7; 
}
.chart-legend-container li span.line {
    display: inline-block;
    width: 25px; /* Buat lebih panjang */
    height: 4px; /* Buat lebih tebal */
    border-radius: 2px;
    margin-right: 8px;
}
/* Style untuk garis putus-putus */
.chart-legend-container li span.dashed {
    background-image: linear-gradient(to right, var(--color, #000) 60%, transparent 40%);
    background-size: 8px 4px;
    background-color: transparent !important; /* Hapus background solid */
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sisa JavaScript Anda untuk 5 chart di bawah tidak perlu diubah
// ... (salin tempel semua kode <script> Anda yang ada sebelumnya) ...
document.addEventListener("DOMContentLoaded", function() {

    const charts = {}; 
    const formatRupiah = (value) => 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
    const defaultBorderWidth = 3; 
    const highlightBorderWidth = 6; 

    // --- PLUGIN HTML Legend ---
    const getOrCreateLegendList = (chart, id) => {
        const legendContainer = document.getElementById(id); // Ini adalah DIV
        if (!legendContainer) return null;
        let listContainer = legendContainer.querySelector('ul');
        if (!listContainer) {
            listContainer = document.createElement('ul');
            legendContainer.appendChild(listContainer);
        }
        return listContainer; // Ini adalah UL
    };

    const htmlLegendPlugin = {
        id: 'htmlLegend',
        afterUpdate(chart, args, options) {
            const ul = getOrCreateLegendList(chart, options.containerID);
            if (!ul) return;
            
            const legendContainer = document.getElementById(options.containerID);
            if (!legendContainer) return;

            // Pasang event 'mouseleave' di DIV container
            legendContainer.onmouseleave = () => {
                const chartInstance = charts[chart.canvas.id]; 
                if (!chartInstance) return;

                chartInstance.data.datasets.forEach(dataset => {
                    dataset.borderWidth = defaultBorderWidth;
                });
                chartInstance.update('none'); 
            };

            ul.innerHTML = ''; 
            const items = chart.options.plugins.legend.labels.generateLabels(chart);
            
            items.forEach(item => {
                const li = document.createElement('li');
                li.style.color = item.fontColor;
                
                li.onmouseenter = () => {
                    const chartInstance = charts[chart.canvas.id]; 
                    if (!chartInstance) return;
                    
                    chartInstance.data.datasets.forEach((dataset, index) => {
                        if (index === item.datasetIndex) {
                            dataset.borderWidth = highlightBorderWidth;
                        } else {
                            dataset.borderWidth = defaultBorderWidth; 
                        }
                    });
                    chartInstance.update('none'); 
                };

                const boxSpan = document.createElement('span');
                boxSpan.className = 'line'; // Class baru untuk styling
                
                // Set warna garis (solid)
                boxSpan.style.background = item.strokeStyle; 
                boxSpan.style.borderColor = item.strokeStyle;
                
                // Cek apakah dataset ini putus-putus
                if (item.borderDash && item.borderDash.length > 0) {
                    boxSpan.classList.add('dashed');
                    // Set variabel CSS untuk warna
                    boxSpan.style.setProperty('--color', item.strokeStyle); 
                }
                
                const text = document.createTextNode(item.text);
                
                li.appendChild(boxSpan);
                li.appendChild(text);
                ul.appendChild(li);
            });
        }
    };
    // --- AKHIR PLUGIN ---


    // Opsi Global (Tidak berubah)
    const chartGlobalOptions = {
        plugins: {
            htmlLegend: {
                // containerID akan diset di createMonthlyChart
            },
            legend: {
                display: false
            },
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
     * Helper Function (Tidak berubah)
     */
    function createMonthlyChart(canvasId, legendContainerId, chartJsonData) {
        if (!document.getElementById(canvasId)) return;
        
        let chartData;
        try {
            chartData = JSON.parse(chartJsonData);
        } catch (e) {
            console.error("Gagal parse data JSON untuk chart:", e);
            console.error("Data bermasalah:", chartJsonData);
            return;
        }

        let chartOptions = JSON.parse(JSON.stringify(chartGlobalOptions));
        chartOptions.plugins.htmlLegend.containerID = legendContainerId;

        const ctx = document.getElementById(canvasId).getContext('2d');
        
        const chartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: chartData.datasets
            },
            options: chartOptions, 
            plugins: [htmlLegendPlugin] 
        });
        charts[canvasId] = chartInstance; 
    }

    // --- Panggil 5 Chart ---
    createMonthlyChart(
        'chart1',
        'legend-chart1',
        '<?php echo $chart_hpp_konsentrat_vs_telur; ?>'
    );
    createMonthlyChart(
        'chart2',
        'legend-chart2',
        '<?php echo $chart_hpp_komplit_vs_telur; ?>'
    );
    createMonthlyChart(
        'chart3',
        'legend-chart3',
        '<?php echo $chart_hpp_broiler_vs_lb; ?>'
    );
    createMonthlyChart(
        'chart4',
        'legend-chart4',
        '<?php echo $chart_jagung; ?>'
    );
    createMonthlyChart(
        'chart5',
        'legend-chart5',
        '<?php echo $chart_katul; ?>'
    );

});
</script>
