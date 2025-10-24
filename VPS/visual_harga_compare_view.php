<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                
                <h1 class="font-weight-bold">
                    Perbandingan Laporan Harga
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
                    <div class="card-body p-2">
                        
                        <form action="<?= base_url('Dashboard_new/visual_harga_compare') ?>" method="GET" class="d-flex align-items-center flex-wrap">
                            
                            <label for="komoditas1" class="my-0 mr-2 font-weight-bold">Komoditas 1:</label>
                            <select id="komoditas1" name="komoditas1" class="form-control" style="width: 250px;">
                                <?php foreach ($all_komoditas as $value => $text): ?>
                                    <option value="<?= $value ?>" <?= ($selected_komoditas1 == $value) ? 'selected' : '' ?>>
                                        <?= $text ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <label for="komoditas2" class="my-0 ml-4 mr-2 font-weight-bold">Komoditas 2:</label>
                            <select id="komoditas2" name="komoditas2" class="form-control" style="width: 250px;">
                                <?php foreach ($all_komoditas as $value => $text): ?>
                                    <option value="<?= $value ?>" <?= ($selected_komoditas2 == $value) ? 'selected' : '' ?>>
                                        <?= $text ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <button type="submit" class="btn btn-primary ml-3">
                                <i class="fas fa-search"></i> Tampilkan
                            </button>
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
                            Grafik Perbandingan Harga Bulanan
                        </h3>
                    </div>
                    <div class="card-body">
                        <div style="height: 450px;">
                            <canvas id="hargaCompareChart"></canvas>
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
    
    // Fungsi helper untuk format Rupiah
    const formatRupiah = (value) => 'Rp ' + new Intl.NumberFormat('id-ID').format(value);

    // Opsi standar untuk tooltip dan skala Y
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

    // Inisialisasi Chart Perbandingan
    const ctxCompare = document.getElementById('hargaCompareChart').getContext('2d');
    new Chart(ctxCompare, {
        type: 'line',
        data: {
            labels: <?php echo $chart_labels; ?>,
            datasets: <?php echo $chart_datasets; ?> // Data diambil dari controller
        },
        options: tooltipOptions
    });
});
</script>
