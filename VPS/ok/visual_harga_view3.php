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
<div class="stat-block bg-light p-2 rounded" style="border-left: 4px solid <?php echo $color; ?>;">
    <div class="text-xs font-weight-bold" style="color: <?php echo $color; ?>;">
        <?php echo $title; ?>
    </div>
    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $harga_display; ?></div>
</div>
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
                        <div class="d-flex align-items-center mb-2">
                            <div style="width: 15%;">
                                <?php render_stat_block('Telur Layer / Kg', $latest_telur, '#007bff'); ?>
                            </div>
                            <div id="legend-chartTelurLayer" class="chart-legend-container" style="flex-grow: 1;"></div>
                        </div>
                        
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
                        <div class="d-flex align-items-center mb-2">
                            <div style="width: 15%;">
                                <?php render_stat_block('Telur Puyuh / Kg', $latest_puyuh, '#17a2b8'); ?>
                            </div>
                            <div id="legend-chartTelurPuyuh" class="chart-legend-container" style="flex-grow: 1;"></div>
                        </div>
                        
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
                        <div class="d-flex align-items-center mb-2">
                            <div style="width: 15%;">
                                <?php render_stat_block('Telur Bebek / Btr', $latest_bebek, '#28a745'); ?>
                            </div>
                            <div id="legend-chartTelurBebek" class="chart-legend-container" style="flex-grow: 1;"></div>
                        </div>
                        
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
                        <div class="d-flex align-items-center mb-2">
                            <div style="width: 15%;">
                                <?php render_stat_block('Live Bird / Kg', $latest_lb, '#ffc107'); ?>
                            </div>
                            <div id="legend-chartLiveBird" class="chart-legend-container" style="flex-grow: 1;"></div>
                        </div>
                        
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
                        <div class="d-flex align-items-center mb-2">
                            <div style="width: 15%;">
                                <?php render_stat_block('Afkir / Kg', $latest_afkir, '#dc3545'); ?>
                            </div>
                            <div id="legend-chartAfkir" class="chart-legend-container" style="flex-grow: 1;"></div>
                        </div>
                        
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
.chart-legend-container ul {
    display: flex;
    flex-wrap: wrap;
    list-style: none;
    padding: 0;
    margin: 0;
    justify-content: center; /* Ini akan menengahkan legend */
}
.chart-legend-container li {
    display: flex;
    align-items: center;
    margin: 0 10px;
    font-size: 13px;
    font-weight: bold;
    cursor: default; /* Ganti cursor jadi default (tidak bisa diklik) */
}
.chart-legend-container li span {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 8px;
}
/* Style "hidden" DIHAPUS agar tidak ada coretan */
/*
.chart-legend-container li.hidden {
    text-decoration: line-through;
    color: #aaa;
}
*/
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {

    const formatRupiah = (value) => 'Rp ' + new Intl.NumberFormat('id-ID').format(value);

    // --- PLUGIN HTML Legend ---
    const getOrCreateLegendList = (chart, id) => {
        const legendContainer = document.getElementById(id);
        if (!legendContainer) return null;
        let listContainer = legendContainer.querySelector('ul');
        if (!listContainer) {
            listContainer = document.createElement('ul');
            legendContainer.appendChild(listContainer);
        }
        return listContainer;
    };

    const htmlLegendPlugin = {
        id: 'htmlLegend',
        afterUpdate(chart, args, options) {
            const ul = getOrCreateLegendList(chart, options.containerID);
            if (!ul) return;
            
            ul.innerHTML = ''; 
            const items = chart.options.plugins.legend.labels.generateLabels(chart);
            
            items.forEach(item => {
                const li = document.createElement('li');
                li.style.color = item.fontColor;
                // Hapus toggle class 'hidden'
                
                // *** FUNGSI ONCLICK DIHAPUS/DI-KOMENTARI ***
                /*
                li.onclick = () => {
                    const {type} = chart.config;
                    if (type === 'pie' || type === 'doughnut') {
                        chart.toggleDataVisibility(item.index);
                    } else {
                        chart.setDatasetVisibility(item.datasetIndex, !chart.isDatasetVisible(item.datasetIndex));
                    }
                    chart.update();
                };
                */
                
                const boxSpan = document.createElement('span');
                boxSpan.style.background = item.fillStyle;
                boxSpan.style.borderColor = item.strokeStyle;
                boxSpan.style.borderWidth = item.lineWidth + 'px';
                
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
            console.error("Gagal parse data chart:", e, chartJsonData);
            return;
        }

        let chartOptions = JSON.parse(JSON.stringify(chartGlobalOptions));
        chartOptions.plugins.htmlLegend.containerID = legendContainerId;

        const ctx = document.getElementById(canvasId).getContext('2d');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: chartData.datasets
            },
            options: chartOptions, 
            plugins: [htmlLegendPlugin] 
        });
    }

    // Panggilan Fungsi (Tidak berubah)
    createMonthlyChart(
        'chartTelurLayer',
        'legend-chartTelurLayer',
        '<?php echo $chart_telur; ?>'
    );
    createMonthlyChart(
        'chartTelurPuyuh',
        'legend-chartTelurPuyuh',
        '<?php echo $chart_puyuh; ?>'
    );
    createMonthlyChart(
        'chartTelurBebek',
        'legend-chartTelurBebek',
        '<?php echo $chart_bebek; ?>'
    );
    createMonthlyChart(
        'chartLiveBird',
        'legend-chartLiveBird',
        '<?php echo $chart_lb; ?>'
    );
    createMonthlyChart(
        'chartAfkir',
        'legend-chartAfkir',
        '<?php echo $chart_afkir; ?>'
    );

});
</script>
