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
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Grafik Persentase Kandang Kosong
                    </h6>
                    <small class="text-muted">Tren bulanan kapasitas kandang yang tidak terisi</small>
                </div>
                <div class="text-right">
                    <span class="badge badge-primary badge-pill px-3 py-2">
                        <i class="fas fa-calendar-check mr-1"></i>
                        Tahun: <?php echo empty($selected_years) ? 'Tidak ada' : implode(', ', $selected_years); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="card-body p-4">
            
            <div class="chart-area" style="height: 450px; position: relative;">
                <canvas id="vacancyChart"></canvas>
            </div>
            
            <hr class="my-4">
            
            <h6 class="m-0 font-weight-bold text-primary mb-3">
                <i class="fas fa-database mr-2"></i>
                Data Kapasitas Farm Saat Ini 
                (<?php 
                    echo empty($selected_tipe_ternak) ? 'Semua Tipe' : htmlspecialchars($selected_tipe_ternak); 
                    // Pastikan tidak ada karakter aneh di luar tag PHP ini.
                ?>)
            </h6>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="dataTableKapasitas" width="100%" cellspacing="0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 5%;">No.</th>
                            <th>Nama Farm</th>
                            <th style="width: 10%;">Kapasitas (Ekor)</th>
                            <th style="width: 10%;">Terisi CP</th>
                            <th style="width: 10%;">Terisi Non CP</th>
                            <th style="width: 10%;">Sisa / Kosong</th>
                            <th style="width: 15%;">Update Kapasitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($farm_capacity_list)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data kapasitas farm yang ditemukan untuk filter ini.</td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($farm_capacity_list as $farm): 
                                $is_petelur = in_array($farm['tipe_ternak'], ['Layer', 'Arap', 'Bebek Petelur', 'Puyuh']);
                                $is_pedaging = in_array($farm['tipe_ternak'], ['Grower', 'Bebek Pedaging']);
                                $sisa_kosong = (int)($farm['sisa_kosong'] ?? $farm['kapasitas_farm']);
                                $sisa_class = ($sisa_kosong < 0) ? 'text-warning font-weight-bold' : '';
                            ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($farm['nama_farm']); ?></td>
                                    <td><?php echo number_format($farm['kapasitas_farm'], 0, ',', '.'); ?></td>
                                    
                                    <<td>
                                        <?php 
                                            // Tampilkan Efektif Terisi CP (untuk Petelur dan Pedaging)
                                            if ($farm['efektif_terisi_cp'] > 0) {
                                                echo number_format($farm['efektif_terisi_cp'], 0, ',', '.');
                                            } else {
                                                echo '-'; 
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                            // Tampilkan Efektif Terisi Non-CP (untuk Petelur dan Pedaging)
                                            if ($farm['efektif_terisi_noncp'] > 0) {
                                                echo number_format($farm['efektif_terisi_noncp'], 0, ',', '.');
                                            } else {
                                                echo '-'; 
                                            }
                                        ?>
                                    </td>
                                    <td class="<?php echo $sisa_class; ?>">
                                        <?php 
                                            echo number_format($sisa_kosong, 0, ',', '.'); 
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                            echo date('d M Y', strtotime($farm['start_date'])); 
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


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

<style>
/* ... (SEMUA STYLE CSS ANDA TETAP SAMA) ... */
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
.chart-area {
    background: linear-gradient(180deg, rgba(78, 115, 223, 0.02) 0%, rgba(255, 255, 255, 0) 100%);
    border-radius: 0.5rem;
    padding: 1rem;
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
    .chart-area {
        height: 350px !important;
    }
    
    .btn-lg {
        font-size: 1rem;
    }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // 3. [BARU] Daftarkan plugin ke Chart.js
    Chart.register(ChartDataLabels);

    // Ambil data yang sudah disiapkan oleh controller
    const labels = <?php echo $chart_labels; ?>; // Ini sekarang ['Jan', 'Feb', ..., 'Dec']
    const datasets = <?php echo $chart_datasets; ?>; // Ini sekarang [{label: '2025', data: [...]}, {label: '2024', data: [...]}]

    const chartData = {
        labels: labels,
        datasets: datasets
    };

    const config = {
        type: 'bar', // Tipe chart adalah 'bar'
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Tren Kandang Kosong Bulanan',
                    font: {
                        size: 16,
                        weight: 'bold'
                    },
                    padding: {
                        top: 10,
                        bottom: 20
                    }
                },
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
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
                                label += context.parsed.y.toFixed(2) + '%';
                            }
                            return label;
                        }
                    }
                },

                // 4. [BARU] Konfigurasi untuk plugin datalabels
                datalabels: {
                    display: true,
                    anchor: 'end',  // Tampilkan di 'ujung' (atas) bar
                    align: 'end',   // Rata 'di atas' bar
                    color: '#444', // Warna teks label
                    font: {
                        weight: 'bold',
                        size: 11
                    },
                    formatter: function(value, context) {
                        // Hanya tampilkan jika nilainya lebih dari 0
                        if (value > 0) { 
                            return value.toFixed(2) + '%';
                        }
                        return ''; // Sembunyikan jika nilainya 0
                    },
                    offset: 2 // Jarak dari atas bar
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    // [DIUBAH] max: 100 diubah jadi 105 agar label 100% tidak terpotong
                    max: 105, 
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        },
                        font: {
                            size: 11
                        }
                    },
                    title: {
                        display: true,
                        text: 'Persentase Kosong (%)',
                        font: {
                            size: 13,
                            weight: 'bold'
                        },
                        padding: {
                            top: 10,
                            bottom: 5
                        }
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                }
            }
        },
    };

    // Render chart
    const myChart = new Chart(
        document.getElementById('vacancyChart'),
        config
    );
});
</script>
