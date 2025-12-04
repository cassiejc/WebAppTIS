<div class="container-fluid">
    <section class="content-header mb-4">
        <div class="container-fluid">
            <div class="d-flex align-items-center p-3 bg-white shadow-sm" style="border-radius: 2rem;">
                <a href="<?= site_url('Dashboard_new/index') ?>" class="btn btn-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border-radius: 50%;">
                    <i class="fas fa-home fa-lg text-white"></i>
                </a>
                <h1 class="font-weight-bold text-dark mb-0 mx-auto" style="font-size: 2rem;">
                    Kondisi Lingkungan
                </h1>
            </div>
        </div>
    </section>

    <section class="content">
    
        <!-- Filter Tahun & Area -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-gradient-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-filter mr-2"></i>Filter Data
                </h5>
            </div>
            <div class="card-body bg-light">
                <form action="<?= site_url('Dashboard_new/visual_kondisi_lingkungan') ?>" method="post">
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tahun" class="font-weight-bold">
                                    <i class="fas fa-calendar-alt text-primary mr-1"></i>Tahun:
                                </label>
                                <select name="tahun" id="tahun" class="form-control form-control-lg shadow-sm">
                                    <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                                        <option value="<?= $i ?>" <?= ($selected_year == $i) ? 'selected' : '' ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($is_admin): ?>
                    <div class="row mt-3">
                        <div class="col-md-12"> 
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white">
                                    <h6 class="mb-0 font-weight-bold">
                                        <i class="fas fa-map-marked-alt text-success mr-2"></i>Filter Area Farm
                                    </h6>
                                    <small class="text-muted">Pilih area yang ingin ditampilkan</small>
                                </div>
                                <div class="card-body" style="max-height: 200px; overflow-y: auto; background-color: #f8f9fa;">
                                    <?php if (empty($all_areas)): ?>
                                        <div class="text-center py-4">
                                            <p class="text-muted">Tidak ada data area.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="row">
                                            <?php foreach ($all_areas as $area): ?>
                                                <?php 
                                                    $area_id = $area['master_area_id'];
                                                    $area_name = $area['nama_area'];
                                                    $is_checked = in_array($area_id, $selected_areas);
                                                ?>
                                                <div class="col-md-4 col-sm-6 mb-2">
                                                    <div class="custom-control custom-checkbox">
                                                        <input class="custom-control-input" 
                                                               type="checkbox" 
                                                               name="areas[]" 
                                                               value="<?= htmlspecialchars($area_id) ?>" 
                                                               id="area_<?= md5($area_id) ?>"
                                                               <?= $is_checked ? 'checked' : '' ?>
                                                        >
                                                        <label class="custom-control-label font-weight-normal" for="area_<?= md5($area_id) ?>">
                                                            <?= htmlspecialchars($area_name) ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary btn-lg btn-block shadow-sm">
                                <i class="fas fa-check mr-2"></i>
                                Terapkan Filter
                            </button>
                        </div>
                    </div>
                    
                </form>
            </div>
        </div>

        <!-- Chart Suhu/Kelembapan/HI (Tetap di atas) -->
        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-gradient-info text-white">
                        <h5 class="mb-0"><i class="fas fa-thermometer-half mr-2"></i>Kondisi Lingkungan (Suhu, Kelembapan, HI)</h5>
                        <small class="d-block mt-1">Rata-rata Suhu (kiri), Kelembapan (kanan), dan Heat Index (kanan)</small>
                    </div>
                    <div class="card-body bg-white">
                        <div style="height: 400px; position: relative;">
                            <canvas id="chartSuhuKelembapanHI"></canvas> 
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- LOOP: Chart per Jenis Pakan -->
        <?php if (!empty($charts_by_pakan)): ?>
            <?php foreach ($charts_by_pakan as $index => $chart_data): ?>
                <?php 
                    $pakan_name = htmlspecialchars($chart_data['pakan_name']);
                    $chart_id = 'chartPakan' . $index;
                ?>
                <div class="row">
                    <div class="col-lg-12 mb-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-gradient-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-bug mr-2"></i>
                                    <i class="fas fa-poop mr-2"></i>
                                    <?= $pakan_name ?>
                                </h5>
                                <small class="d-block mt-1">Kondisi Lalat & Kotoran untuk pakan: <?= $pakan_name ?></small>
                            </div>
                            <div class="card-body bg-white">
                                <div style="height: 500px; position: relative;">
                                    <canvas id="<?= $chart_id ?>"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Tidak ada data untuk periode yang dipilih.
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
    </section>
</div>

<style>
.bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.bg-gradient-success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.bg-gradient-info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
.card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important; }
.custom-control-input:checked ~ .custom-control-label::before { background-color: #667eea; border-color: #667eea; }
.custom-control-label { cursor: pointer; user-select: none; }
.form-control:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25); }
.btn-primary { background: linear-gradient(135deg,  #1e3c72 0%, #2a5298 100%); border: none; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@2.1.0/dist/chartjs-plugin-annotation.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        if (typeof ChartAnnotation !== 'undefined') {
            Chart.register(ChartAnnotation);
        }
        Chart.register(ChartDataLabels);

        var showNoData = function(canvasId) {
             var canvas = document.getElementById(canvasId);
             if(canvas && canvas.parentElement) {
                 canvas.parentElement.innerHTML = 
                '<div class="d-flex justify-content-center align-items-center h-100">' +
                '<div class="text-center py-5">' +
                '<i class="fas fa-chart-bar fa-4x text-muted mb-3"></i>' +
                '<p class="text-muted h5">Tidak ada data untuk periode ini.</p>' +
                '</div>' +
                '</div>';
             }
        }

        // Helper: Chart GROUPED (Lalat & Kotoran)
        function createGrouped100PercentStackedChart(canvasId, lalatDatasets, kotoranDatasets, chartLabels) {
            var ctx = document.getElementById(canvasId).getContext('2d');
            if (window[canvasId + 'Chart']) { window[canvasId + 'Chart'].destroy(); }

            var lalatColors = { 'Normal': '#28a745', 'Sedikit': '#90ee90', 'Sedang': '#ffc107', 'Banyak': '#dc3545' };
            var kotoranColors = { 'Kering': '#87ceeb', 'Lembab': '#4169e1', 'Basah': '#8b4513', 'Normal': '#6c757d' };

            var allDatasets = [];
            
            if(lalatDatasets) {
                lalatDatasets.forEach(function(dataset) {
                    var kategori = dataset.label;
                    var warna = lalatColors[kategori] || dataset.backgroundColor;
                    allDatasets.push({
                        label: 'Lalat - ' + dataset.label,
                        data: dataset.data,
                        raw_counts: dataset.raw_counts,
                        backgroundColor: warna, borderColor: warna, borderWidth: 1,
                        stack: 'lalat', barPercentage: 0.8, categoryPercentage: 0.9
                    });
                });
            }
            
            if(kotoranDatasets) {
                kotoranDatasets.forEach(function(dataset) {
                    var kategori = dataset.label;
                    var warna = kotoranColors[kategori] || dataset.backgroundColor;
                    allDatasets.push({
                        label: 'Kotoran - ' + dataset.label,
                        data: dataset.data,
                        raw_counts: dataset.raw_counts,
                        backgroundColor: warna, borderColor: warna, borderWidth: 1,
                        stack: 'kotoran', barPercentage: 0.8, categoryPercentage: 0.9
                    });
                });
            }

            window[canvasId + 'Chart'] = new Chart(ctx, {
                type: 'bar',
                data: { labels: chartLabels, datasets: allDatasets },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            display: true, position: 'bottom', 
                            labels: { 
                                padding: 15, usePointStyle: true, pointStyle: 'circle', font: { size: 11 },
                                generateLabels: function(chart) {
                                    const original = Chart.defaults.plugins.legend.labels.generateLabels;
                                    const labels = original.call(this, chart);
                                    labels.forEach(label => {
                                        if (label.text.startsWith('Lalat')) { label.strokeStyle = '#28a745'; label.lineWidth = 2; } 
                                        else if (label.text.startsWith('Kotoran')) { label.strokeStyle = '#4169e1'; label.lineWidth = 2; }
                                    });
                                    return labels;
                                }
                            } 
                        },
                        tooltip: { 
                            mode: 'index', intersect: false,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) { label += ': '; }
                                    let percentage = parseFloat(context.parsed.y);
                                    let raw_count = 0;
                                    if(context.dataset.raw_counts && context.dataset.raw_counts[context.dataIndex]){
                                        raw_count = context.dataset.raw_counts[context.dataIndex];
                                    } 
                                    if (!isNaN(percentage)) { label += `${percentage.toFixed(2)}% (${raw_count} data)`; }
                                    return label;
                                }
                            }
                        },
                        datalabels: {
                            display: true, anchor: 'center', align: 'center',
                            color: function(context) {
                                var bgColor = context.dataset.backgroundColor;
                                if (bgColor === '#90ee90' || bgColor === '#87ceeb' || bgColor === '#ffc107') { return '#000000'; }
                                return '#FFFFFF';
                            },
                            font: { weight: 'bold', size: 9 },
                            formatter: function(value) { return value < 3 ? '' : value.toFixed(1) + '%'; }
                        }
                    },
                    scales: {
                        x: { stacked: true, grid: { display: false } },
                        y: { stacked: true, min: 0, max: 100, ticks: { callback: v => v + "%" }, title: { display: true, text: 'Persentase Bulanan (Stacked 100%)' } }
                    },
                    interaction: { mode: 'index', intersect: false }
                }
            });
        }

        // Helper: Chart Multi-Sumbu (Suhu/Kelembapan/HI)
        function createMultiAxisChart(canvasId, chartData) {
            var ctx = document.getElementById(canvasId).getContext('2d');
            if (window[canvasId + 'Chart']) { window[canvasId + 'Chart'].destroy(); }
            
            if (chartData.labels && chartData.labels.length > 0) {
                const jumlahBulan = chartData.labels.length;
                const dataGarisBatas = new Array(jumlahBulan).fill(150);
                const sudahAdaGaris = chartData.datasets.some(ds => ds.label === 'BATAS STANDAR (150)');

                if (!sudahAdaGaris) {
                    chartData.datasets.push({
                        type: 'line', label: 'BATAS STANDAR (150)', data: dataGarisBatas,
                        borderColor: 'rgba(40, 167, 69, 0.35)', backgroundColor: 'rgba(40, 167, 69, 0.35)',
                        borderWidth: 2, borderDash: [5, 5], pointRadius: 0, fill: false,
                        yAxisID: 'ySuhu', order: 0, datalabels: { display: false }, tooltip: { enabled: false }
                    });
                }
            }

            const UNIFORM_MAX = 200; const UNIFORM_MIN = 0;
            window[canvasId + 'Chart'] = new Chart(ctx, {
                type: 'bar', data: chartData, 
                options: {
                    responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { 
                            display: true, position: 'bottom',
                            labels: { 
                                padding: 15, usePointStyle: true, pointStyle: 'rectRounded',
                                filter: function(item) { return !item.text.includes('BATAS STANDAR'); }
                            }
                        }, 
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    if (context.dataset.label === 'BATAS STANDAR (150)') return null;
                                    let label = context.dataset.label || '';
                                    if (label) { label += ': '; }
                                    let val = parseFloat(context.parsed.y);
                                    if (!isNaN(val)) {
                                        if (context.dataset.label.includes('Suhu')) label += val.toFixed(2) + ' ï¿½C';
                                        else if (context.dataset.label.includes('Kelembapan')) label += val.toFixed(2) + ' %';
                                        else label += val.toFixed(2);
                                    }
                                    return label;
                                }
                            },
                            filter: function(item) { return item.dataset.label !== 'BATAS STANDAR (150)'; }
                        },
                        datalabels: {
                            display: function(context) { return context.dataset.label !== 'BATAS STANDAR (150)' && context.dataset.data[context.dataIndex] > 0; },
                            anchor: 'end', align: 'end',
                            offset: function(context) { return context.dataset.label.includes('Suhu') ? 4 : 8; },
                            color: function(context) {
                                if (context.dataset.label.includes('Suhu')) return '#dc3545';
                                if (context.dataset.label.includes('Kelembapan')) return '#007bff';
                                if (context.dataset.label.includes('Heat Index')) return '#ffc107';
                                return '#444'; 
                            },
                            font: { weight: 'bold', size: 10 },
                            formatter: function(v, ctx) { 
                                let val = parseFloat(v);
                                if (ctx.dataset.label.includes('Suhu')) return val.toFixed(2) + ' ï¿½C';
                                if (ctx.dataset.label.includes('Kelembapan')) return val.toFixed(2) + ' %';
                                return val.toFixed(2);
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        ySuhu: { type: 'linear', position: 'left', title: { display: true, text: 'Suhu (ï¿½C)', color: '#dc3545' }, ticks: { color: '#dc3545' }, min: UNIFORM_MIN, max: UNIFORM_MAX },
                        yKelembapan: { type: 'linear', position: 'right', title: { display: true, text: 'Kelembapan (%)', color: '#007bff' }, ticks: { display: false }, min: UNIFORM_MIN, max: UNIFORM_MAX, grid: { drawOnChartArea: false } },
                        yHeatIndex: { type: 'linear', position: 'right', title: { display: true, text: 'Heat Index', color: '#ffc107' }, ticks: { display: false }, min: UNIFORM_MIN, max: UNIFORM_MAX, grid: { drawOnChartArea: false } }
                    }
                }
            });
        }

        // ==========================================
        // RENDER SEMUA CHART (BAGIAN INI YANG DIPERBAIKI)
        // ==========================================
        
        // 1. Render Chart Suhu - AMAN DARI NULL

        //var suhuKelembapanHIData = <?= !empty($chart_suhu_kelembapan_hi_data) ? json_encode($chart_suhu_kelembapan_hi_data) : 'null' ?>;
        var suhuKelembapanHIData = null;
        
        var hasDataAvg = false;
        // Cek dulu apakah variabelnya ada (tidak null) baru cek property-nya
        if (suhuKelembapanHIData && suhuKelembapanHIData.datasets && suhuKelembapanHIData.datasets.length > 0) {
             hasDataAvg = suhuKelembapanHIData.datasets[0].data.some(d => d !== null);
        }
        
        if (suhuKelembapanHIData && suhuKelembapanHIData.labels && suhuKelembapanHIData.labels.length > 0 && hasDataAvg) {
            createMultiAxisChart('chartSuhuKelembapanHI', suhuKelembapanHIData);
        } else {
            showNoData('chartSuhuKelembapanHI');
        }

        // 2. Render Chart Pakan - JUGA AMAN DARI NULL
        var chartsByPakan = <?= !empty($charts_by_pakan) ? json_encode($charts_by_pakan) : 'null' ?>;
        
        if (chartsByPakan && chartsByPakan.length > 0) {
            chartsByPakan.forEach(function(chartData, index) {
                var canvasId = 'chartPakan' + index;
                var lalatDatasets = (chartData.lalat_data && chartData.lalat_data.datasets) ? chartData.lalat_data.datasets : [];
                var kotoranDatasets = (chartData.kotoran_data && chartData.kotoran_data.datasets) ? chartData.kotoran_data.datasets : [];
                var chartLabels = (chartData.lalat_data && chartData.lalat_data.labels) ? chartData.lalat_data.labels : [];
                
                var hasLalatData = lalatDatasets.length > 0;
                var hasKotoranData = kotoranDatasets.length > 0;
                
                if (hasLalatData || hasKotoranData) {
                    createGrouped100PercentStackedChart(canvasId, lalatDatasets, kotoranDatasets, chartLabels);
                } else {
                    showNoData(canvasId);
                }
            });
        }
    });
</script>
