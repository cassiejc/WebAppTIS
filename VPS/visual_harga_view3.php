<div class="container-fluid">
    <!-- Header Section -->
    <section class="content-header mb-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="font-weight-bold text-primary">
                    <i class="fas fa-chart-line mr-2"></i>
                    Laporan Harga 
                    <?php 
                        $titles = [
                            'jagung' => 'Jagung',
                            'katul' => 'Katul',
                            'afkir' => 'Afkir',
                            'telur_puyuh' => 'Telur Puyuh',
                            'telur_bebek' => 'Telur Bebek',
                            'bebek_pedaging' => 'Bebek Pedaging',
                            'live_bird' => 'Live Bird',
                            'pakan_broiler' => 'Pakan Komplit Broiler',
                            'doc' => 'DOC',
                            'konsentrat_layer' => 'Avg Harga Konsentrat Layer',
                            'hpp_konsentrat_layer' => 'Avg HPP Konsentrat Layer',
                            'hpp_komplit_layer' => 'Avg HPP Komplit Layer',
                            'cost_komplit_broiler' => 'Avg Cost Komplit Broiler',
                            'hpp_broiler' => 'Avg HPP Broiler',
                            'telur' => 'Telur Layer'
                        ];
                        echo $titles[$jenis_terpilih] ?? 'Telur Layer';
                    ?>
                </h1>

                <a href="<?= base_url('Dashboard_new/index') ?>" class="btn btn-outline-secondary btn-lg shadow-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Menu
                </a>
            </div>
        </div>
    </section>

    <section class="content">
        <!-- Commodity Selector Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <label for="pilihKomoditas" class="mb-0 mr-3 font-weight-bold text-secondary">
                                <i class="fas fa-filter mr-2"></i>Pilih Laporan:
                            </label>
                            
                            <select id="pilihKomoditas" class="custom-select shadow-sm" style="width: 300px;">
                                <option value="telur" <?= ($jenis_terpilih == 'telur') ? 'selected' : '' ?>>Harga Telur Layer</option>
                                <option value="jagung" <?= ($jenis_terpilih == 'jagung') ? 'selected' : '' ?>>Harga Jagung</option>
                                <option value="katul" <?= ($jenis_terpilih == 'katul') ? 'selected' : '' ?>>Harga Katul</option>
                                <option value="afkir" <?= ($jenis_terpilih == 'afkir') ? 'selected' : '' ?>>Harga Afkir</option>
                                <option value="telur_puyuh" <?= ($jenis_terpilih == 'telur_puyuh') ? 'selected' : '' ?>>Harga Telur Puyuh</option>
                                <option value="telur_bebek" <?= ($jenis_terpilih == 'telur_bebek') ? 'selected' : '' ?>>Harga Telur Bebek</option>
                                <option value="bebek_pedaging" <?= ($jenis_terpilih == 'bebek_pedaging') ? 'selected' : '' ?>>Harga Bebek Pedaging</option>
                                <option value="live_bird" <?= ($jenis_terpilih == 'live_bird') ? 'selected' : '' ?>>Harga Live Bird</option>
                                <option value="pakan_broiler" <?= ($jenis_terpilih == 'pakan_broiler') ? 'selected' : '' ?>>Pakan Komplit Broiler</option>
                                <option value="doc" <?= ($jenis_terpilih == 'doc') ? 'selected' : '' ?>>DOC</option>
                                <option value="konsentrat_layer" <?= ($jenis_terpilih == 'konsentrat_layer') ? 'selected' : '' ?>>Avg Harga Konsentrat Layer</option>
                                <option value="hpp_konsentrat_layer" <?= ($jenis_terpilih == 'hpp_konsentrat_layer') ? 'selected' : '' ?>>Avg HPP Konsentrat Layer</option>
                                <option value="hpp_komplit_layer" <?= ($jenis_terpilih == 'hpp_komplit_layer') ? 'selected' : '' ?>>Avg HPP Komplit Layer</option>
                                <option value="cost_komplit_broiler" <?= ($jenis_terpilih == 'cost_komplit_broiler') ? 'selected' : '' ?>>Avg Cost Komplit Broiler</option>
                                <option value="hpp_broiler" <?= ($jenis_terpilih == 'hpp_broiler') ? 'selected' : '' ?>>Avg HPP Broiler</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Price Display Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body p-4">
                        <div class="row align-items-center text-white">
                            <div class="col-auto">
                                <div class="bg-white bg-opacity-25 rounded-circle p-4">
                                    <i class="fas fa-tag fa-3x text-white"></i>
                                </div>
                            </div>
                            <div class="col">
                                <?php
                                $judul_harga = "Harga Rata-Rata";
                                $tanggal_display = date('d M Y'); 
                                $harga_display = "Belum Tersedia";
                                $sumber_data_text = "Data harga untuk hari ini belum diproses";
                                $is_data_today = false; 
                                if (isset($harga_hari_ini) && is_array($harga_hari_ini) &&
                                    isset($harga_hari_ini['nilai_rata_rata']) && $harga_hari_ini['nilai_rata_rata'] > 0 &&
                                    isset($harga_hari_ini['tanggal'])) {

                                    $data_tanggal = $harga_hari_ini['tanggal'];
                                    $tanggal_display = date('d M Y', strtotime($data_tanggal)); 
                                    $harga_display = "Rp " . number_format($harga_hari_ini['nilai_rata_rata'], 0, ',', '.');

                                    if ($data_tanggal == date('Y-m-d')) {
                                        $is_data_today = true;
                                        $judul_harga = "Harga Rata-Rata Hari Ini";
                                        if (isset($harga_hari_ini['jumlah_sumber_data'])) {
                                             $sumber_data_text = "Berdasarkan " . $harga_hari_ini['jumlah_sumber_data'] . " sumber data";
                                        } else {
                                             $sumber_data_text = "Berdasarkan 1 sumber data"; 
                                        }
                                    } else {
                                        $judul_harga = "Harga Rata-Rata Terakhir";
                                        $sumber_data_text = "Data per " . $tanggal_display . " (belum ada update hari ini)";
                                    }
                                }
                                ?>

                                <div class="text-uppercase font-weight-bold mb-2" style="letter-spacing: 1px;">
                                    <?= $judul_harga ?>
                                    <?php if ($is_data_today): ?>
                                        <span class="badge badge-light ml-2"><?= $tanggal_display ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="h1 font-weight-bold mb-2" style="font-size: 2.5rem;">
                                    <?= $harga_display ?>
                                </div>

                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <small><?= $sumber_data_text ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 font-weight-bold text-secondary">
                            <i class="fas fa-sliders-h mr-2"></i>Filter Grafik
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="get" action="<?= current_url(); ?>" class="form-row align-items-end">
                            <input type="hidden" name="komoditas" value="<?= $jenis_terpilih; ?>">
                            
                            <div class="col-md-6 col-lg-3 mb-3 mb-lg-0">
                                <label for="filter_bulan_harian" class="font-weight-bold text-secondary">
                                    <i class="far fa-calendar-alt mr-1"></i>Grafik Harian
                                </label>
                                <div class="input-group">
                                    <select name="bulan_harian" id="filter_bulan_harian" class="custom-select">
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <?php $bulan_val = str_pad($m, 2, '0', STR_PAD_LEFT); ?>
                                            <option value="<?= $bulan_val; ?>" <?= ($selected_bulan_harian == $bulan_val) ? 'selected' : ''; ?>>
                                                <?= date('F', mktime(0, 0, 0, $m, 10)); ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                    <select name="tahun_harian" class="custom-select ml-2">
                                        <?php $tahun_sekarang = date('Y'); ?>
                                        <?php for ($y = $tahun_sekarang; $y >= $tahun_sekarang - 5; $y--): ?>
                                            <option value="<?= $y; ?>" <?= ($selected_tahun_harian == $y) ? 'selected' : ''; ?>><?= $y; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-3 mb-3 mb-lg-0">
                                <label for="filter_tahun_bulanan" class="font-weight-bold text-secondary">
                                    <i class="far fa-calendar-alt mr-1"></i>Grafik Bulanan
                                </label>
                                <select name="tahun_bulanan" id="filter_tahun_bulanan" class="custom-select">
                                    <?php for ($y = $tahun_sekarang; $y >= $tahun_sekarang - 5; $y--): ?>
                                        <option value="<?= $y; ?>" <?= ($selected_tahun_bulanan == $y) ? 'selected' : ''; ?>><?= $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-lg-3">
                                <button type="submit" class="btn btn-primary btn-block shadow-sm">
                                    <i class="fas fa-sync-alt mr-2"></i>Terapkan Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Daily Chart -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-gradient-success text-white border-0">
                        <h5 class="mb-0 font-weight-bold">
                            <i class="fas fa-chart-line mr-2"></i>
                            Grafik Harga Harian
                            <?php 
                                echo $titles[$jenis_terpilih] ?? 'Telur Layer';
                            ?>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div style="height: 400px;">
                            <canvas id="hargaHarianChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly and Yearly Charts -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-gradient-primary text-white border-0">
                        <h5 class="mb-0 font-weight-bold">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            Grafik Bulanan
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div style="height: 350px;">
                            <canvas id="hargaBulananChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-gradient-purple text-white border-0">
                        <h5 class="mb-0 font-weight-bold">
                            <i class="fas fa-flag mr-2"></i>
                            Grafik Tahunan
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div style="height: 350px;">
                            <canvas id="hargaTahunanChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.bg-gradient-success {
    background: linear-gradient(87deg, #2dce89 0, #2dcecc 100%) !important;
}

.bg-gradient-primary {
    background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%) !important;
}

.bg-gradient-purple {
    background: linear-gradient(87deg, #8965e0 0, #bc65e0 100%) !important;
}

.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08) !important;
}

.custom-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.bg-opacity-25 {
    opacity: 0.25;
}

.shadow-lg {
    box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const selectKomoditas = document.getElementById('pilihKomoditas');
    selectKomoditas.addEventListener('change', function() {
        const selectedValue = this.value;
        window.location.href = `<?= base_url('Dashboard_new/visual_harga/') ?>${selectedValue}`;
    });

    const formatRupiah = (value) => 'Rp ' + new Intl.NumberFormat('id-ID').format(value);

    const tooltipOptions = {
        plugins: {
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
                titleFont: {
                    size: 14,
                    weight: 'bold'
                },
                bodyFont: {
                    size: 13
                }
            },
            legend: {
                labels: {
                    font: {
                        size: 13
                    },
                    padding: 15
                }
            }
        },
        scales: {
            y: { 
                ticks: { 
                    callback: value => formatRupiah(value),
                    font: {
                        size: 11
                    }
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            },
            x: {
                ticks: {
                    font: {
                        size: 11
                    }
                },
                grid: {
                    display: false
                }
            }
        },
        maintainAspectRatio: false,
        responsive: true,
    };

    const ctxHarian = document.getElementById('hargaHarianChart').getContext('2d');
    new Chart(ctxHarian, {
        type: 'line',
        data: {
            labels: <?php echo $chart_harian_labels; ?>,
            datasets: [{
                label: 'Harga Rata-Rata (Rp)',
                data: <?php echo $chart_harian_data; ?>,
                borderColor: '#2dce89',
                backgroundColor: 'rgba(45, 206, 137, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#2dce89',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: tooltipOptions
    });

    const ctxBulanan = document.getElementById('hargaBulananChart').getContext('2d');
    new Chart(ctxBulanan, {
        type: 'line',
        data: {
            labels: <?php echo $chart_bulanan_labels; ?>,
            datasets: [{
                label: 'Harga Rata-Rata (Rp)',
                data: <?php echo $chart_bulanan_data; ?>,
                borderColor: '#5e72e4',
                backgroundColor: 'rgba(94, 114, 228, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#5e72e4',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: tooltipOptions
    });

    const ctxTahunan = document.getElementById('hargaTahunanChart').getContext('2d');
    new Chart(ctxTahunan, {
        type: 'line',
        data: {
            labels: <?php echo $chart_tahunan_labels; ?>,
            datasets: [{
                label: 'Harga Rata-Rata (Rp)',
                data: <?php echo $chart_tahunan_data; ?>,
                borderColor: '#8965e0',
                backgroundColor: 'rgba(137, 101, 224, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#8965e0',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: tooltipOptions
    });
});
</script>
