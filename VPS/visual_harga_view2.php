<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                
                <h1 class="font-weight-bold">
                    Laporan Harga 
                    <?php 
                        if ($jenis_terpilih == 'jagung') echo 'Jagung';
                        elseif ($jenis_terpilih == 'katul') echo 'Katul';
                        elseif ($jenis_terpilih == 'afkir') echo 'Afkir'; // Tambahkan ini
                        else echo 'Telur Layer';
                    ?>
                </h1>

                <a href="<?= base_url('Dashboard_new/index') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Menu
                </a>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-12 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body p-2 d-flex align-items-center">
                        <label for="pilihKomoditas" class="my-0 mr-2 font-weight-bold">Pilih Laporan:</label>
                        
                        <select id="pilihKomoditas" class="form-control" style="width: 250px;">
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

            <div class="col-12 mb-3">
                <div class="card shadow-sm" style="background-color: #28a745; color: white;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <i class="fas fa-tag fa-3x"></i>
                            </div>
                            <div>
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

                                } else {
                                }
                                ?>

                                <div class="text-uppercase font-weight-bold">
                                    <?= $judul_harga ?>
                                    <?php if ($is_data_today): ?>
                                        (<?= $tanggal_display ?>)
                                    <?php endif; ?>
                                </div>

                                <div class="h2 font-weight-bold mb-0">
                                    <?= $harga_display ?>
                                </div>

                                <small>
                                    <?= $sumber_data_text ?>
                                </small>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="row mb-3">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body p-2">
                        <form method="get" action="<?= current_url(); ?>" class="form-inline">
                            <input type="hidden" name="komoditas" value="<?= $jenis_terpilih; ?>"> <label for="filter_bulan_harian" class="my-1 mr-2 font-weight-bold">Grafik Harian:</label>
                            <select name="bulan_harian" id="filter_bulan_harian" class="form-control my-1 mr-sm-2">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <?php $bulan_val = str_pad($m, 2, '0', STR_PAD_LEFT); ?>
                                    <option value="<?= $bulan_val; ?>" <?= ($selected_bulan_harian == $bulan_val) ? 'selected' : ''; ?>>
                                        <?= date('F', mktime(0, 0, 0, $m, 10)); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <select name="tahun_harian" class="form-control my-1 mr-sm-2">
                                <?php $tahun_sekarang = date('Y'); ?>
                                <?php for ($y = $tahun_sekarang; $y >= $tahun_sekarang - 5; $y--): // Ambil 5 tahun ke belakang ?>
                                    <option value="<?= $y; ?>" <?= ($selected_tahun_harian == $y) ? 'selected' : ''; ?>><?= $y; ?></option>
                                <?php endfor; ?>
                            </select>

                            <label for="filter_tahun_bulanan" class="my-1 ml-3 mr-2 font-weight-bold">Grafik Bulanan:</label>
                            <select name="tahun_bulanan" id="filter_tahun_bulanan" class="form-control my-1 mr-sm-2">
                                <?php for ($y = $tahun_sekarang; $y >= $tahun_sekarang - 5; $y--): ?>
                                    <option value="<?= $y; ?>" <?= ($selected_tahun_bulanan == $y) ? 'selected' : ''; ?>><?= $y; ?></option>
                                <?php endfor; ?>
                            </select>

                            <button type="submit" class="btn btn-primary my-1 ml-2">Terapkan Filter</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-chart-line mr-1"></i>
                            Grafik Harga 
                            <?php 
                                $titles = [
                                    'telur' => 'Telur Layer',
                                    'jagung' => 'Jagung', 'katul' => 'Katul', 'afkir' => 'Afkir',
                                    'telur_puyuh' => 'Telur Puyuh', 'telur_bebek' => 'Telur Bebek',
                                    'bebek_pedaging' => 'Bebek Pedaging', 'live_bird' => 'Live Bird',
                                    'pakan_broiler' => 'Pakan Komplit Broiler', 'doc' => 'DOC',
                                    'konsentrat_layer' => 'Avg Harga Konsentrat Layer',
                                    'hpp_konsentrat_layer' => 'Avg HPP Konsentrat Layer',
                                    'hpp_komplit_layer' => 'Avg HPP Komplit Layer',
                                    'cost_komplit_broiler' => 'Avg Cost Komplit Broiler',
                                    'hpp_broiler' => 'Avg HPP Broiler'
                                ];
                                echo $titles[$jenis_terpilih] ?? 'Telur Layer';
                            ?>
                            (30 Hari Terakhir)
                        </h3>
                    </div>
                    <div class="card-body">
                        <div style="height: 400px;">
                            <canvas id="hargaHarianChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            Grafik Bulanan (12 Bulan Terakhir)
                        </h3>
                    </div>
                    <div class="card-body">
                        <div style="height: 350px;">
                            <canvas id="hargaBulananChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-flag mr-1"></i>
                            Grafik Tahunan
                        </h3>
                    </div>
                    <div class="card-body">
                        <div style="height: 350px;">
                            <canvas id="hargaTahunanChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

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
                }
            }
        },
        scales: {
            y: { ticks: { callback: value => formatRupiah(value) } }
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
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                fill: true,
                tension: 0.1
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
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true,
                tension: 0.1
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
                borderColor: '#6f42c1',
                backgroundColor: 'rgba(111, 66, 193, 0.1)',
                fill: true,
                tension: 0.1
            }]
        },
        options: tooltipOptions
    });
});
</script>
