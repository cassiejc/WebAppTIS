<div class="container-fluid">
    <section class="content-header mb-4">
        <div class="container-fluid">
            <div class="d-flex align-items-center p-3 bg-white shadow-sm" style="border-radius: 2rem;">
                <a href="<?php echo site_url('Dashboard_new/index') ?>" class="btn btn-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border-radius: 50%;">
                    <i class="fas fa-home fa-lg text-white"></i>
                </a>
                <h1 class="font-weight-bold text-dark mb-0 mx-auto" style="font-size: 2rem;">
                    Kandang Kosong
                </h1>
            </div>
        </div>
    </section>

    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-gradient-primary text-white py-3">
            <div class="d-flex align-items-center">
                <i class="fas fa-filter mr-2"></i>
                <h6 class="m-0 font-weight-bold">Filter Laporan</h6>
            </div>
        </div>
        <div class="card-body p-4">
            <form action="<?php echo site_url('Dashboard_new/visual_kandang_kosong') ?>" method="post">
                <div class="row align-items-start">
                    
                    <div class="col-lg-4 col-md-6 mb-3"> 
                        <label for="tipe_ternak" class="form-label text-dark font-weight-bold">
                            <i class="fas fa-paw text-primary mr-1"></i>
                            Tipe Ternak
                        </label>
                        <select name="tipe_ternak" id="tipe_ternak" class="form-control form-control-lg border-primary">
                            <option value="">Tampilkan Semua Tipe Ternak</option>
                            
                            <?php foreach ($all_tipe_ternak as $tipe): ?>
                                <?php if ($tipe['tipe_ternak'] != 'Lainnya'): ?>
                                    <option value="<?php echo htmlspecialchars($tipe['tipe_ternak']) ?>" 
                                        <?php echo ($tipe['tipe_ternak'] == $selected_tipe_ternak) ? 'selected' : '' ?>>
                                        <?php echo htmlspecialchars($tipe['tipe_ternak']) ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i>
                            Pilih tipe ternak spesifik atau lihat gabungan
                        </small>
                    </div>

                    <div class="col-lg-6 col-md-6 mb-3">
                        <label class="form-label text-dark font-weight-bold">
                            <i class="fas fa-calendar-alt text-success mr-1"></i>
                            Filter Tahun
                        </label>
                        <div class="d-flex flex-wrap align-items-center" style="padding-top: 10px;">
                            <?php if (empty($all_years)): ?>
                                <p class="text-muted">Tidak ada data tahun.</p>
                            <?php else: ?>
                                <?php foreach ($all_years as $year): ?>
                                    <?php
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
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i>
                            Pilih tahun untuk melihat data historis kunjungan terakhir per tahun
                        </small>
                    </div>

                    <div class="col-lg-2 col-md-12 mb-3 align-self-end">
                        <button type="submit" name="submit_filter" value="1" class="btn btn-primary btn-lg w-100 shadow-sm">
                            <i class="fas fa-search mr-1"></i>
                            Terapkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Card Bar Chart Sisa/Kosong per Bulan -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex align-items-center">
                <i class="fas fa-chart-bar text-primary mr-2"></i>
                <h6 class="m-0 font-weight-bold text-primary">
                    Grafik Kandang Kosong per Bulan
                    (<?php echo empty($selected_tipe_ternak) ? 'Semua Tipe' : htmlspecialchars($selected_tipe_ternak); ?>)
                </h6>
            </div>
        </div>
        <div class="card-body p-4">
            <?php if (!empty($chart_data_monthly)): ?>
                <canvas id="chartKandangKosong" height="100"></canvas>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    Tidak ada data grafik untuk ditampilkan. Silakan pilih tahun dan tipe ternak.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Card Tabel Data Kapasitas Farm -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-database mr-2"></i>
                        Data Kapasitas Farm 
                        (<?php 
                            echo empty($selected_tipe_ternak) ? 'Semua Tipe' : htmlspecialchars($selected_tipe_ternak); 
                        ?>)
                    </h6>
                    <small class="text-muted">Berdasarkan kunjungan terakhir di tahun yang dipilih</small>
                </div>
                <div class="text-right">
                    <span class="badge badge-primary badge-pill px-3 py-2">
                        <i class="fas fa-calendar-check mr-1"></i>
                        Tahun: <?php echo empty($selected_years) ? 'Tidak dipilih' : implode(', ', $selected_years); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="dataTableKapasitas" width="100%" cellspacing="0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 5%;">No.</th>
                            <th>Nama Farm</th>
                            <th style="width: 8%;">Tahun</th>
                            <th style="width: 10%;">Kapasitas (Ekor)</th>
                            <th style="width: 10%;">Terisi CP</th>
                            <th style="width: 10%;">Terisi Non CP</th>
                            <th style="width: 10%;">Total Terisi</th>
                            <th style="width: 10%;">Sisa / Kosong</th>
                            <th style="width: 12%;">Kunjungan Terakhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($farm_capacity_list)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Tidak ada data kapasitas farm yang ditemukan untuk filter ini.
                                    <?php if (empty($selected_years)): ?>
                                        <br><small>Silakan pilih minimal satu tahun.</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($farm_capacity_list as $farm): 
                                $total_terisi = (int)($farm['total_terisi'] ?? 0);
                                $sisa_kosong = (int)($farm['sisa_kosong'] ?? $farm['kapasitas_farm']);
                                $sisa_class = ($sisa_kosong < 0) ? 'text-warning font-weight-bold' : '';
                            ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($farm['nama_farm']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-tag"></i>
                                            <?php echo htmlspecialchars($farm['tipe_ternak']); ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info">
                                            <?php echo $farm['tahun'] ?? '-'; ?>
                                        </span>
                                    </td>
                                    <td class="text-right"><?php echo number_format($farm['kapasitas_farm'], 0, ',', '.'); ?></td>
                                    
                                    <td class="text-right">
                                        <?php 
                                            if ($farm['efektif_terisi_cp'] > 0) {
                                                echo '<span class="text-success font-weight-bold">' 
                                                    . number_format($farm['efektif_terisi_cp'], 0, ',', '.') 
                                                    . '</span>';
                                            } else {
                                                echo '<span class="text-muted">-</span>'; 
                                            }
                                        ?>
                                    </td>
                                    <td class="text-right">
                                        <?php 
                                            if ($farm['efektif_terisi_noncp'] > 0) {
                                                echo '<span class="text-primary font-weight-bold">' 
                                                    . number_format($farm['efektif_terisi_noncp'], 0, ',', '.') 
                                                    . '</span>';
                                            } else {
                                                echo '<span class="text-muted">-</span>'; 
                                            }
                                        ?>
                                    </td>
                                    <td class="text-right">
                                        <strong><?php echo number_format($total_terisi, 0, ',', '.'); ?></strong>
                                    </td>
                                    <td class="text-right <?php echo $sisa_class; ?>">
                                        <?php echo number_format($sisa_kosong, 0, ',', '.'); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            if (!empty($farm['waktu_kunjungan_terakhir'])) {
                                                echo '<small>' . date('d M Y, H:i', strtotime($farm['waktu_kunjungan_terakhir'])) . '</small>';
                                            } else {
                                                echo '<span class="text-muted">-</span>';
                                            }
                                        ?>
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

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
}
.form-control-lg {
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}
.form-control:focus {
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    border-color: #4e73df;
}
.btn-primary {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    border: none;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3);
}
.card {
    border-radius: 0.75rem;
    transition: all 0.3s ease;
}
.card:hover {
    transform: translateY(-2px);
}
.badge-pill {
    font-size: 0.85rem;
    font-weight: 500;
}
.form-label {
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}
select.form-control {
    cursor: pointer;
}
.bg-light {
    background-color: #f8f9fc !important;
}

@media (max-width: 768px) {
    .btn-lg {
        font-size: 1rem;
    }
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css"/>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
$(document).ready(function() {    
    
    // Inisialisasi Chart Kandang Kosong per Bulan
    <?php if (!empty($chart_data_monthly)): ?>
    const ctx = document.getElementById('chartKandangKosong');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chart_labels_monthly); ?>,
                datasets: <?php echo $chart_data_monthly; ?>
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Total Kandang Kosong per Bulan (Tahun: <?php echo implode(", ", $selected_years); ?>)',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += new Intl.NumberFormat('id-ID').format(context.parsed.y) + ' ekor';
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Bulan',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah Kandang Kosong (Ekor)',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('id-ID').format(value);
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
