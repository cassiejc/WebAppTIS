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
        <!-- Filter Card with Modern Design -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-gradient-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-filter mr-2"></i>Filter Data
                </h5>
            </div>
            <div class="card-body bg-light">
                <form action="<?= site_url('Dashboard_new/visual_kondisi_lingkungan') ?>" method="post">
                    <div class="row">
                        <!-- Tahun Filter -->
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
                        
                        <!-- Submit Button -->
                        <div class="col-md-2 align-self-end">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-lg btn-block shadow">
                                    <i class="fas fa-search mr-1"></i>Filter
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filter Pakan Section -->
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
 <div class="card-body bg-light">
                <form action="<?= site_url('Dashboard_new/visual_kondisi_lingkungan') ?>" method="post">
                    <div class="row">
                                                                                                                        </div>                     
                                                                <div class="row mt-4">
                                                <div class="col-lg-6 mb-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0 font-weight-bold">
                                        <i class="fas fa-thermometer-half text-danger mr-2"></i>Rata-rata Suhu Kandang
                                    </h5>
                                    <small class="text-muted">Rata-rata suhu (°C) bulanan berdasarkan filter</small>
                                </div>
                                <div class="card-body">
                                    <div style="height: 300px; position: relative;">
                                        <canvas id="chartSuhu"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                                                <div class="col-lg-6 mb-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0 font-weight-bold">
                                        <i class="fas fa-tint text-primary mr-2"></i>Rata-rata Kelembapan Kandang
                                    </h5>
                                    <small class="text-muted">Rata-rata kelembapan (%) bulanan berdasarkan filter</small>
                                </div>
                                <div class="card-body">
                                    <div style="height: 300px; position: relative;">
                                        <canvas id="chartKelembapan"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                                                            <div class="row mt-3">
                                            </div>
                </form>             </div>
        </div>                   
                    
                </form>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row">
            <!-- Kondisi Lalat Chart -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-gradient-success text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-bug mr-2"></i>Kondisi Lalat per Bulan
                            </h5>
                            <span class="badge badge-light"><?= $selected_year ?></span>
                        </div>
                        <small class="d-block mt-1">Distribusi persentase kondisi lalat di kandang</small>
                    </div>
                    <div class="card-body bg-white">
                        <div style="height: 400px; position: relative;">
                            <canvas id="chartLalat"></canvas>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Data ditampilkan dalam persentase per bulan
                        </small>
                    </div>
                </div>
            </div>

            <!-- Kondisi Kotoran Chart -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-gradient-warning text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-tint mr-2"></i>Kondisi Kotoran per Bulan
                            </h5>
                            <span class="badge badge-light"><?= $selected_year ?></span>
                        </div>
                        <small class="d-block mt-1">Distribusi persentase kondisi kotoran di kandang</small>
                    </div>
                    <div class="card-body bg-white">
                        <div style="height: 400px; position: relative;">
                            <canvas id="chartKotoran"></canvas>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Data ditampilkan dalam persentase per bulan
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
/* Custom Gradient Backgrounds */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

/* Card Hover Effects */
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

/* Custom Checkbox Styling */
.custom-control-input:checked ~ .custom-control-label::before {
    background-color: #667eea;
    border-color: #667eea;
}

.custom-control-label {
    cursor: pointer;
    user-select: none;
}

.custom-control-label:hover {
    color: #667eea;
}

/* Form Control Enhancement */
.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* Button Enhancement */
.btn-primary {
    background: linear-gradient(135deg,  #1e3c72 0%, #2a5298 100%);
    border: none;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(42, 82, 152, 0.4);
}

/* ScrollBar Styling */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: #667eea;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: #764ba2;
}

/* Badge Enhancement */
.badge-light {
    background-color: rgba(255,255,255,0.3);
    color: white;
    font-weight: 600;
}

/* Icon Enhancements */
.fas, .far {
    transition: all 0.3s ease;
}

.card-header:hover .fas,
.card-header:hover .far {
    transform: scale(1.1);
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // Helper function untuk membuat chart 100% STACKED
        function create100PercentStackedChart(canvasId, chartLabels, chartDatasets) {
            var ctx = document.getElementById(canvasId).getContext('2d');
            
            if (window[canvasId + 'Chart']) {
                window[canvasId + 'Chart'].destroy();
            }

            window[canvasId + 'Chart'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: chartDatasets 
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            display: true, 
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12,
                                    weight: 'bold'
                                },
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }

                                    let percentage = context.parsed.y;
                                    let raw_count = context.dataset.raw_counts[context.dataIndex]; 

                                    if (percentage !== null) {
                                        label += `${percentage.toFixed(2)}% (${raw_count} data)`; 
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: true, 
                            grid: { 
                                display: false 
                            },
                            ticks: {
                                font: {
                                    size: 11,
                                    weight: 'bold'
                                }
                            }
                        },
                        y: {
                            stacked: true, 
                            min: 0,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + "%";
                                },
                                font: {
                                    size: 11,
                                    weight: 'bold'
                                }
                            },
                            title: {
                                display: true,
                                text: 'Persentase Bulanan',
                                font: {
                                    size: 13,
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false
                    }
                }
            });
        }

        // 1. Render Chart Lalat
        var lalatLabels = <?= json_encode($chart_lalat_data['labels']) ?>;
        var lalatDatasets = <?= json_encode($chart_lalat_data['datasets']) ?>;
        
        if (lalatLabels && lalatLabels.length > 0) {
            create100PercentStackedChart('chartLalat', lalatLabels, lalatDatasets);
        } else {
            document.getElementById('chartLalat').parentElement.innerHTML = 
                '<div class="text-center py-5">' +
                '<i class="fas fa-chart-bar fa-4x text-muted mb-3"></i>' +
                '<p class="text-muted h5">Tidak ada data untuk periode ini.</p>' +
                '</div>';
        }

        // 2. Render Chart Kotoran
        var kotoranLabels = <?= json_encode($chart_kotoran_data['labels']) ?>;
        var kotoranDatasets = <?= json_encode($chart_kotoran_data['datasets']) ?>;

        if (kotoranLabels && kotoranLabels.length > 0) {
            create100PercentStackedChart('chartKotoran', kotoranLabels, kotoranDatasets);
        } else {
            document.getElementById('chartKotoran').parentElement.innerHTML = 
                '<div class="text-center py-5">' +
                '<i class="fas fa-chart-bar fa-4x text-muted mb-3"></i>' +
                '<p class="text-muted h5">Tidak ada data untuk periode ini.</p>' +
                '</div>';
        }
    });
</script>
