<div class="container-fluid">
    <!-- Header Section -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="font-weight-bold mb-2" style="color: #2c3e50;">
                        <i class="fas fa-virus mr-2" style="color: #3498db;"></i>Laporan Kasus Penyakit
                    </h1>
                    <!-- <p class="text-muted mb-0">Analisis dan monitoring kasus penyakit ternak</p> -->
                </div>
                <a href="<?= site_url('Dashboard_new') ?>" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="col-sm-12">
            <!-- Filter Card -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #3498db 100%);">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-filter mr-2"></i>Filter Laporan
                    </h3>
                </div>
                <div class="card-body p-4">
                    <form action="<?= site_url('Dashboard_new/visual_kasus_penyakit') ?>" method="post" class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label for="tahun" class="form-label fw-bold text-secondary">
                                <i class="fas fa-calendar mr-2"></i>Tahun
                            </label>
                            <select name="tahun" id="tahun" class="form-control form-select" style="border-radius: 10px; height: 45px;">
                                <option value="0">-- Semua Tahun --</option>
                                <?php for ($i = date('Y'); $i >= date('Y') - 7; $i--): ?>
                                    <option value="<?= $i; ?>" <?= ($selected_year == $i) ? 'selected' : ''; ?>><?= $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100" style="border-radius: 10px; height: 45px;">
                                <i class="fas fa-search mr-2"></i>Tampilkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Chart Card -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-weight-bold mb-0" style="color: #2c3e50;">
                            <i class="fas fa-chart-bar mr-2" style="color: #5dade2;"></i>Grafik Kasus per Bulan
                        </h3>
                    </div>
                </div>
                <div class="card-body p-4">
                    <?php if(isset($chart_labels) && !empty(json_decode($chart_labels))): ?>
                        <div style="position: relative; height: 400px;">
                            <canvas id="kasusStackedChart"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-chart-bar fa-4x mb-3 text-muted" style="opacity: 0.3;"></i>
                            <p class="text-muted h5">Tidak ada data untuk ditampilkan pada periode yang dipilih.</p>
                            <p class="text-muted small">Silakan pilih tahun yang berbeda</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pivot Table Card -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h3 class="card-title font-weight-bold mb-0" style="color: #2c3e50;">
                        <i class="fas fa-table mr-2" style="color: #3498db;"></i>Tabel Pivot Kasus per Area
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="min-width: 800px;">
                            <thead style="background-color: #f8f9fa;">
                                <tr class="text-center">
                                    <th style="width: 200px; border-top: none; position: sticky; left: 0; background-color: #f8f9fa; z-index: 10;">
                                        <i class="fas fa-map-marker-alt mr-2" style="color: #3498db;"></i>Area
                                    </th>
                                    <?php foreach ($pivot_table_categories as $kategori): ?>
                                        <th style="border-top: none; min-width: 100px;">
                                            <?= htmlspecialchars($kategori); ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pivot_table_data)): ?>
                                    <tr>
                                        <td colspan="<?= count($pivot_table_categories) + 1; ?>" class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block text-muted" style="opacity: 0.3;"></i>
                                            <span class="text-muted">Tidak ada data untuk ditampilkan.</span>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($pivot_table_data as $area_data): ?>
                                        <tr style="transition: all 0.3s ease;">
                                            <td style="position: sticky; left: 0; background-color: white; z-index: 5;">
                                                <strong style="color: #2c3e50;">
                                                    <i class="fas fa-map-pin mr-2" style="color: #3498db;"></i>
                                                    <?= htmlspecialchars($area_data['nama_area']); ?>
                                                </strong>
                                            </td>
                                            <?php foreach ($pivot_table_categories as $kategori): ?>
                                                <td class="text-center">
                                                    <?php 
                                                    $nilai = $area_data[$kategori] ?? 0;
                                                    $badge_class = $nilai > 0 ? 'badge-primary' : 'badge-light';
                                                    ?>
                                                    <span class="badge <?= $badge_class ?> px-3 py-2" style="font-size: 13px; min-width: 50px;">
                                                        <?= $nilai; ?>
                                                    </span>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Detail Cases Card -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h3 class="card-title font-weight-bold mb-0" style="color: #2c3e50;">
                        <i class="fas fa-clipboard-list mr-2" style="color: #5dade2;"></i>Rincian Laporan Kasus
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #f8f9fa; position: sticky; top: 0; z-index: 10;">
                                <tr>
                                    <th style="width: 60px; border-top: none;" class="text-center">No.</th>
                                    <th style="width: 200px; border-top: none;">Waktu Kunjungan</th>
                                    <th style="border-top: none;">Nama Farm</th>
                                    <th style="border-top: none;">Jenis Kasus</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($kasus_detail_list)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block text-muted" style="opacity: 0.3;"></i>
                                            <span class="text-muted">Tidak ada rincian kasus untuk ditampilkan.</span>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; ?>
                                    <?php foreach ($kasus_detail_list as $row): ?>
                                        <tr style="transition: all 0.3s ease;">
                                            <td class="text-center align-middle">
                                                <span class="badge badge-light rounded-circle" style="width: 35px; height: 35px; line-height: 35px; font-size: 14px;">
                                                    <?= $no++; ?>
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center">
                                                    <i class="far fa-clock mr-2 text-muted"></i>
                                                    <span><?= date('d M Y, H:i', strtotime($row['waktu_kunjungan'])); ?></span>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <strong style="color: #2c3e50;">
                                                    <i class="fas fa-warehouse mr-2" style="color: #3498db;"></i>
                                                    <?= htmlspecialchars($row['nama_farm']); ?>
                                                </strong>
                                            </td>
                                            <td class="align-middle">
                                                <span class="badge badge-primary px-3 py-2" style="font-size: 13px; border-radius: 20px;">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    <?= htmlspecialchars($row['jenis_kasus']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<style>
    /* Modern Styling - Blue Palette */
    .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
    }
    
    .form-control, .form-select {
        border: 1px solid #e0e0e0;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.15);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #3498db 100%);
        border: none;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
    }
    
    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
    }
    
    /* Scrollbar Styling - Blue */
    .table-responsive::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background: #3498db;
        border-radius: 10px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #667eea;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    <?php if(isset($chart_labels) && !empty(json_decode($chart_labels))): ?>
        const stackedCtx = document.getElementById('kasusStackedChart');
        if (stackedCtx) {
            new Chart(stackedCtx, {
                type: 'bar',
                data: {
                    labels: <?= $chart_labels; ?>,
                    datasets: <?= $chart_datasets; ?>
                },
                options: {
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12,
                                    weight: '500'
                                },
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            cornerRadius: 8,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y.toFixed(1) + '%';
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { 
                            stacked: true,
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: { 
                            stacked: true,
                            max: 100,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                callback: function(value, index, values) {
                                    return value + '%';
                                },
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });
        }
    <?php endif; ?>
});
</script>
