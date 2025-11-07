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
<div class="col-lg col-md-4 col-6 mb-4">
    <div class="card shadow-sm h-100" style="border-left: 4px solid <?php echo $color; ?> !important;">
        <div class="card-body p-3">
            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: <?php echo $color; ?>;">
                <?php echo $title; ?>
            </div>
            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $harga_display; ?></div>
        </div>
    </div>
</div>
<?php } 
?>

<div class="container-fluid">
    <section class="content-header mb-4">
        <?php
            // Helper untuk tanggal Indonesia
            $bulan_arr = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $tanggal_hari_ini = date('j') . ' ' . $bulan_arr[(int)date('n')] . ' ' . date('Y');
        ?>
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div> 
                    <h1 class="font-weight-bold text-dark mb-0"> <i class="fas fa-chart-line mr-2"></i>
                        Dashboard Analisis Harga
                    </h1>
                    <span class="h5 font-weight-normal text-muted mt-2 d-block"> 
                        <?php echo $tanggal_hari_ini; ?>
                    </span>
                </div>
                <a href="<?= base_url('Dashboard_new/index') ?>" class="btn btn-outline-secondary btn-lg shadow-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Menu
                </a>
            </div>
        </div>
    </section>

    <section class="content">
        
        <h3 class="font-weight-bold text-secondary mb-3">Harga Terakhir</h3>
        <div class="row">
            <?php render_stat_block('Jagung', $stat_jagung, '#ffc107'); ?>
            <?php render_stat_block('Katul', $stat_katul, '#6c757d'); ?>
            <?php render_stat_block('Pakan Komplit Layer', $stat_pakan_layer, '#fd7e14'); ?>
            <?php render_stat_block('Pakan Komplit Broiler', $stat_pakan_broiler, '#17a2b8'); ?>
        </div>
        <div class="row">
            <?php render_stat_block('Pakan Campuran (Konsentrat)', $stat_konsentrat, '#007bff'); ?>
            <?php render_stat_block('HPP Telur (Konsentrat)', $stat_hpp_konsentrat, '#28a745'); ?>
            <?php render_stat_block('HPP Telur (Komplit)', $stat_hpp_komplit, '#20c997'); ?>
            <?php render_stat_block('HPP Broiler', $stat_hpp_broiler, '#dc3545'); ?>
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
