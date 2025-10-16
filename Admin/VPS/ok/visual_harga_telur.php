<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="font-weight-bold">Laporan Harga Telur</h1>
                <a href="<?= base_url('Dashboard_new/index') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Menu
                </a>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-12 mb-3">
                <div class="card shadow-sm" style="background-color: #28a745; color: white;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <i class="fas fa-tag fa-3x"></i>
                            </div>
                            <div>
                                <div class="text-uppercase font-weight-bold">Harga Rata-Rata Hari Ini (<?= date('d M Y') ?>)</div>
                                <?php if (!empty($harga_hari_ini) && $harga_hari_ini['nilai_rata_rata'] > 0): ?>
                                    <div class="h2 font-weight-bold mb-0">
                                        Rp <?= number_format($harga_hari_ini['nilai_rata_rata'], 0, ',', '.') ?>
                                    </div>
                                    <small>
                                        Berdasarkan <?= $harga_hari_ini['jumlah_sumber_data'] ?> sumber data
                                    </small>
                                <?php else: ?>
                                    <div class="h2 font-weight-bold mb-0">
                                        Belum Tersedia
                                    </div>
                                    <small>
                                        Data harga untuk hari ini belum diproses
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
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
                            Grafik Harga Jual Telur Layer (30 Hari Terakhir)
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

    // Fungsi bantuan untuk format Rupiah agar tidak duplikat kode
    const formatRupiah = (value) => 'Rp ' + new Intl.NumberFormat('id-ID').format(value);

    // Opsi default untuk tooltip agar menampilkan format Rupiah
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

    // 1. Inisialisasi Chart HARIAN
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

    // 2. Inisialisasi Chart BULANAN
    const ctxBulanan = document.getElementById('hargaBulananChart').getContext('2d');
    new Chart(ctxBulanan, {
        type: 'line',
        data: {
            labels: <?php echo $chart_bulanan_labels; ?>,
            datasets: [{
                label: 'Harga Rata-Rata (Rp)',
                data: <?php echo $chart_bulanan_data; ?>,
                borderColor: '#007bff', // Biru
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true,
                tension: 0.1
            }]
        },
        options: tooltipOptions
    });

    // 3. Inisialisasi Chart TAHUNAN
    const ctxTahunan = document.getElementById('hargaTahunanChart').getContext('2d');
    new Chart(ctxTahunan, {
        type: 'line',
        data: {
            labels: <?php echo $chart_tahunan_labels; ?>,
            datasets: [{
                label: 'Harga Rata-Rata (Rp)',
                data: <?php echo $chart_tahunan_data; ?>,
                borderColor: '#6f42c1', // Ungu
                backgroundColor: 'rgba(111, 66, 193, 0.1)',
                fill: true,
                tension: 0.1
            }]
        },
        options: tooltipOptions
    });
});
</script>
