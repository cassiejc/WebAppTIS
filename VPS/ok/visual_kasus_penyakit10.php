<div class="container-fluid">
    <!-- Header Section -->
    <section class="content-header mb-4">
        <div class="container-fluid">
            <div class="d-flex align-items-center p-3 bg-white shadow-sm" style="border-radius: 2rem;">
                <a href="<?= base_url('Dashboard_new/index') ?>" class="btn btn-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border-radius: 50%;">
                    <i class="fas fa-home fa-lg text-white"></i>
                </a>
                <h1 class="font-weight-bold text-dark mb-0 mx-auto" style="font-size: 2rem;">
                    Kasus Penyakit
                </h1>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="col-sm-12">
            <!-- Filter Card -->
             <form action="<?= site_url('Dashboard_new/visual_kasus_penyakit') ?>" method="post" class="row g-3 align-items-end" id="mainFilterForm">
                <input type="hidden" name="filter_area_id" id="filter_area_id" value="">
                <input type="hidden" name="filter_area_name" id="filter_area_name" value="">
                
                
            </form>
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
                                </thead>
                            <tbody>
                                <?php if (empty($pivot_table_data)): ?>
                                    <?php else: ?>
                                    <?php foreach ($pivot_table_data as $area_data): ?>
                                        <?php 
                                            $area_id = $area_data['master_area_id'] ?? 0; // Ambil ID Area
                                            $area_name = htmlspecialchars($area_data['nama_area']);
                                        ?>
                                        <tr class="clickable-area" 
                                            data-area-id="<?= $area_id; ?>" 
                                            style="transition: all 0.3s ease; cursor: pointer;"
                                            onclick="filterByArea(<?= $area_id; ?>, '<?= $area_name; ?>')">
                                            
                                            <td style="position: sticky; left: 0; background-color: white; z-index: 5;">
                                                <strong style="color: #2c3e50;">
                                                    <i class="fas fa-map-pin mr-2" style="color: #3498db;"></i>
                                                    <?= $area_name; ?>
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
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0" id="detailCaseTable" style="width:100%;">
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th style="width: 50px;" class="text-center">No</th>
                        <th style="width: 200px;">Waktu Kunjungan</th>
                        <th style="width: 150px;">Area Farm</th>
                        <th>Nama Farm</th>
                        <th style="width: 250px;">Jenis Kasus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($kasus_detail_list)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x mb-3 d-block text-muted" style="opacity: 0.3;"></i>
                                <span class="text-muted">Tidak ada rincian kasus untuk ditampilkan.</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; ?>
                        <?php foreach ($kasus_detail_list as $row): ?>
                            <tr>
                                <td class="text-center align-middle">
                                    <span class="badge badge-secondary rounded-circle" style="width: 30px; height: 30px; line-height: 30px; font-size: 12px;">
                                        <?= $no++; ?>
                                    </span>
                                </td>
                                <td class="align-middle text-nowrap">
                                    <div class="d-flex align-items-center">
                                        <i class="far fa-clock mr-2 text-primary" style="font-size: 14px;"></i>
                                        <span><?= date('d M Y', strtotime($row['waktu_kunjungan'])); ?></span>
                                        <span class="text-muted small ml-2"><?= date('H:i', strtotime($row['waktu_kunjungan'])); ?></span>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <span class="text-muted small">
                                        <?= htmlspecialchars($row['nama_area'] ?? 'N/A'); ?> </span>
                                </td>
                                <td class="align-middle">
                                    <strong style="color: #2c3e50;">
                                        <i class="fas fa-warehouse mr-2 text-info"></i>
                                        <?= htmlspecialchars($row['nama_farm']); ?>
                                    </strong>
                                </td>
                                <td class="align-middle">
                                    <?php 
                                    // Ambil kategori utama kasus (misal: 'Viral', 'Bacterial')
                                    $full_case = htmlspecialchars($row['jenis_kasus']);
                                    $main_category = explode(':', $full_case)[0];
                                    $badge_class = 'badge-secondary'; // Default
                                    switch (trim($main_category)) {
                                        case 'Viral': $badge_class = 'badge-danger'; break; // Merah
                                        case 'Bacterial': $badge_class = 'badge-success'; break; // Hijau
                                        case 'Jamur': $badge_class = 'badge-info'; break; // Biru
                                        case 'Parasit': $badge_class = 'badge-warning'; break; // Kuning/Oranye
                                        default: $badge_class = 'badge-secondary'; break;
                                    }
                                    ?>
                                    <span class="badge <?= $badge_class ?> px-3 py-2 text-wrap" style="font-size: 13px; border-radius: 5px; min-width: 100px;">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        <?= $full_case; ?>
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

    .badge-danger { /* Virus: Merah */
        background-color: #dc3545 !important;
        color: white;
    }
    .badge-success { /* Bakterial: Hijau */
        background-color: #7b23b7ff !important;
        color: white;
    }
    .badge-info { /* Jamur: Biru */
        background-color: #ffc107 !important; /* Menggunakan info untuk warna biru yang lebih baik */
        color: white;
    }
    .badge-warning { /* Parasit: Kuning */
        background-color: #28a745 !important;
        color: white; /* Teks hitam agar kontras */
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof ChartDataLabels !== 'undefined') {
        Chart.register(ChartDataLabels);
    }

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
                        },
                        // [BARU] Konfigurasi DataLabels
                        datalabels: {
                            color: '#fff', 
                            font: {
                                weight: 'bold',
                                size: 11
                            },
                            // Konteks 'y' adalah persentase dari stacked bar (0-100)
                            formatter: (value, context) => {
                                // Tampilkan persentase jika nilainya cukup besar untuk menghindari tabrakan
                                if (value >= 5) { 
                                    return value.toFixed(1) + '%';
                                } else {
                                    return ''; // Sembunyikan untuk nilai kecil
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

/**
 * Menerapkan filter berdasarkan ID Area yang diklik pada tabel pivot.
 * @param {number} areaId - ID area yang diklik.
 * @param {string} areaName - Nama area yang diklik.
 */
function filterByArea(areaId, areaName) {
    // 1. Set nilai ID Area di input tersembunyi
    document.getElementById('filter_area_id').value = areaId;
    document.getElementById('filter_area_name').value = areaName;

    // 2. Tampilkan notifikasi (Opsional)
    console.log(`Filtering by Area: ${areaName} (ID: ${areaId})`);
    
    // 3. Submit form filter utama
    document.getElementById('mainFilterForm').submit();
}
</script>
