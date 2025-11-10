<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="font-weight-bold mb-0">
                    <i class="fas fa-chart-area text-primary mr-2"></i>
                    Laporan Kondisi Lingkungan (Layer)
                </h1>
                <a href="<?= site_url('Dashboard_new') ?>" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </section>

    <section class="content">
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
                        
                        <div class="col-md-2 align-self-end">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-lg btn-block shadow">
                                    <i class="fas fa-search mr-1"></i>Filter
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white">
                                    <h6 class="mb-0 font-weight-bold">
                                        <i class="fas fa-drumstick-bite text-warning mr-2"></i>Filter Jenis Pakan
                                    </h6>
                                    <small class="text-muted">Pilih jenis pakan yang ingin ditampilkan dalam laporan</small>
                                </div>
                                <div class="card-body" style="max-height: 200px; overflow-y: auto; background-color: #f8f9fa;">
                                    <?php if (empty($all_pakan_options)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Tidak ada opsi pakan ditemukan.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="row">
                                            <?php foreach ($all_pakan_options as $pakan_name): ?>
                                                <?php
                                                    $is_checked = in_array($pakan_name, $selected_pakan);
                                                ?>
                                                <div class="col-md-3 col-sm-6 mb-2">
                                                    <div class="custom-control custom-checkbox">
                                                        <input class="custom-control-input" 
                                                               type="checkbox" 
                                                               name="pakan[]" 
                                                               value="<?= htmlspecialchars($pakan_name) ?>" 
                                                               id="pakan_<?= md5($pakan_name) ?>"
                                                               <?= $is_checked ? 'checked' : '' ?>
                                                        >
                                                        <label class="custom-control-label font-weight-normal" for="pakan_<?= md5($pakan_name) ?>">
                                                            <?= htmlspecialchars($pakan_name) ?>
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
                    
                    
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-gradient-info text-white">
                        <h5 class="mb-0"><i class="fas fa-thermometer-half mr-2"></i>Kondisi Lingkungan (Suhu, Kelembapan, HI)</h5>
                        <small class="d-block mt-1">Rata-rata Suhu (kiri), Kelembapan (kanan), dan Heat Index (kanan)</small>
                    </div>
                    <div class="card-body bg-white">
                        <div style="height: 400px; position: relative;">
                            <canvas id="chartSuhuKelembapanHI"></canvas> </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-gradient-success text-white">
                        <h5 class="mb-0"><i class="fas fa-bug mr-2"></i>Kondisi Lalat per Bulan (Distribusi)</h5>
                        <small class="d-block mt-1">Distribusi persentase kondisi lalat di kandang</small>
                    </div>
                    <div class="card-body bg-white">
                        <div style="height: 400px; position: relative;">
                            <canvas id="chartLalat"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-gradient-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-poop mr-2"></i>Kondisi Kotoran per Bulan (Distribusi)</h5>
                        <small class="d-block mt-1">Distribusi persentase kondisi kotoran di kandang</small>
                    </div>
                    <div class="card-body bg-white">
                        <div style="height: 400px; position: relative;">
                            <canvas id="chartKotoran"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </section>
</div>

<style>
/* Style Anda tidak perlu diubah */
.bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.bg-gradient-success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.bg-gradient-warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.bg-gradient-danger { background: linear-gradient(135deg, #ff0844 0%, #ffb199 100%); }
.bg-gradient-info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
.card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important; }
/* ... style lainnya ... */
.custom-control-input:checked ~ .custom-control-label::before { background-color: #667eea; border-color: #667eea; }
.custom-control-label { cursor: pointer; user-select: none; }
.form-control:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25); }
.btn-primary { background: linear-gradient(135deg,  #1e3c72 0%, #2a5298 100%); border: none; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
    // Script tidak perlu diubah, hanya urutan pemanggilannya saja
    document.addEventListener("DOMContentLoaded", function() {
        
        var showNoData = function(canvasId) {
             document.getElementById(canvasId).parentElement.innerHTML = 
                '<div class="d-flex justify-content-center align-items-center h-100">' +
                '<div class="text-center py-5">' +
                '<i class="fas fa-chart-bar fa-4x text-muted mb-3"></i>' +
                '<p class="text-muted h5">Tidak ada data untuk periode ini.</p>' +
                '</div>' +
                '</div>';
        }

        // --- Helper 1: Chart STACKED (Lalat, Kotoran) ---
        function create100PercentStackedChart(canvasId, chartLabels, chartDatasets) {
            var ctx = document.getElementById(canvasId).getContext('2d');
            if (window[canvasId + 'Chart']) { window[canvasId + 'Chart'].destroy(); }

            window[canvasId + 'Chart'] = new Chart(ctx, {
                type: 'bar',
                data: { labels: chartLabels, datasets: chartDatasets },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true, position: 'bottom', labels: { padding: 15, usePointStyle: true, pointStyle: 'circle' } },
                        tooltip: { mode: 'index', intersect: false,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) { label += ': '; }
                                    let percentage = context.parsed.y;
                                    let raw_count = context.dataset.raw_counts[context.dataIndex]; 
                                    if (percentage !== null) { label += `${percentage.toFixed(2)}% (${raw_count} data)`; }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: { stacked: true, grid: { display: false } },
                        y: { stacked: true, min: 0, max: 100,
                            ticks: { callback: function(value) { return value + "%"; } },
                            title: { display: true, text: 'Persentase Bulanan' }
                        }
                    },
                    interaction: { mode: 'index', intersect: false }
                }
            });
        }

        // --- Helper 2: Chart Multi-Sumbu (Mixed Type) ---
        function createMultiAxisChart(canvasId, chartData) {
            var ctx = document.getElementById(canvasId).getContext('2d');
            if (window[canvasId + 'Chart']) { window[canvasId + 'Chart'].destroy(); }
            
            window[canvasId + 'Chart'] = new Chart(ctx, {
                type: 'bar', 
                data: chartData, 
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index', 
                        intersect: false,
                    },
                    plugins: {
                        legend: { 
                            display: true, 
                            position: 'bottom',
                            labels: { padding: 15, usePointStyle: true, pointStyle: 'circle' }
                        }, 
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) { label += ': '; }
                                    if (context.parsed.y !== null) {
                                        if (context.dataset.label.includes('Suhu')) {
                                            label += context.parsed.y.toFixed(2) + ' °C';
                                        } else if (context.dataset.label.includes('Kelembapan')) {
                                            label += context.parsed.y.toFixed(2) + ' %';
                                        } else if (context.dataset.label.includes('Heat Index')) {
                                            label += context.parsed.y.toFixed(2);
                                        } else {
                                            label += context.parsed.y.toFixed(2);
                                        }
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: { 
                            grid: { display: false } 
                        },
                        ySuhu: { 
                            type: 'linear',
                            position: 'left',
                            title: { display: true, text: 'Suhu (°C)', color: '#dc3545' },
                            ticks: { color: '#dc3545' }
                        },
                        yKelembapan: {
                            type: 'linear',
                            position: 'right',
                            title: { display: true, text: 'Kelembapan (%)', color: '#007bff' },
                            ticks: { color: '#007bff' },
                            min: 0,
                            max: 100 
                        },
                        yHeatIndex: {
                            type: 'linear',
                            position: 'right', 
                            title: { display: true, text: 'Heat Index (F+RH)', color: '#ffc107' },
                            ticks: { 
                                color: '#ffc107',
                                display: false 
                            }, 
                            grid: { drawOnChartArea: false } 
                        }
                    }
                }
            });
        }

        // --- Render 3 Chart ---
        // Urutan pemanggilan di JavaScript tidak masalah, 
        // yang penting urutan HTML-nya
        
        // 1. [BARU] Render Chart Gabungan (Suhu, Kelembapan, HI)
        var suhuKelembapanHIData = <?= json_encode($chart_suhu_kelembapan_hi_data) ?>;
        var hasDataAvg = suhuKelembapanHIData.datasets && suhuKelembapanHIData.datasets[0].data.some(d => d !== null);
        
        if (suhuKelembapanHIData.labels && suhuKelembapanHIData.labels.length > 0 && hasDataAvg) {
            createMultiAxisChart('chartSuhuKelembapanHI', suhuKelembapanHIData);
        } else {
            showNoData('chartSuhuKelembapanHI');
        }

        // 2. Render Chart Lalat (Stacked)
        var lalatLabels = <?= json_encode($chart_lalat_data['labels']) ?>;
        var lalatDatasets = <?= json_encode($chart_lalat_data['datasets']) ?>;
        if (lalatLabels && lalatLabels.length > 0) {
            create100PercentStackedChart('chartLalat', lalatLabels, lalatDatasets);
        } else {
            showNoData('chartLalat');
        }

        // 3. Render Chart Kotoran (Stacked)
        var kotoranLabels = <?= json_encode($chart_kotoran_data['labels']) ?>;
        var kotoranDatasets = <?= json_encode($chart_kotoran_data['datasets']) ?>;
        if (kotoranLabels && kotoranLabels.length > 0) {
            create100PercentStackedChart('chartKotoran', kotoranLabels, kotoranDatasets);
        } else {
            showNoData('chartKotoran');
        }
    });
</script>
