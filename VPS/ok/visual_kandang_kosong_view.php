<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800"><?= $title; ?></h1>
    <p class="mb-4">Gunakan filter di bawah ini untuk mengubah rentang waktu atau menampilkan tipe ternak tertentu.</p>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Laporan</h6>
        </div>
        <div class="card-body">
            <form action="<?= site_url('Dashboard_new/visual_kandang_kosong') ?>" method="post">
                <div class="row align-items-end">
                    
                    <div class="col-md-4 mb-3"> 
                        <label for="tipe_ternak" class="form-label">Tipe Ternak</label>
                        <select name="tipe_ternak" id="tipe_ternak" class="form-select">
                            <option value="">-- Tampilkan Gabungan (Semua) --</option>
                            <?php foreach ($all_tipe_ternak as $tipe): ?>
                                <option value="<?= htmlspecialchars($tipe['tipe_ternak']) ?>" <?= ($tipe['tipe_ternak'] == $selected_tipe_ternak) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tipe['tipe_ternak']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="start_month" class="form-label">Dari Bulan</label>
                        <input type="month" name="start_month" id="start_month" class="form-control" value="<?= htmlspecialchars($start_month) ?>">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="end_month" class="form-label">Sampai Bulan</label>
                        <input type="month" name="end_month" id="end_month" class="form-control" value="<?= htmlspecialchars($end_month) ?>">
                    </div>

                    <div class="col-md-2 mb-3">
                        <button type="submit" class="btn btn-primary w-100">Terapkan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Grafik Persentase Kandang Kosong</h6>
        </div>
        <div class="card-body">
            <div class="chart-area" style="height: 450px;">
                <canvas id="vacancyChart"></canvas>
            </div>
            <hr>
            <p class="text-muted">Grafik ini menampilkan persentase kapasitas kandang yang tidak terisi setiap bulan, dihitung dari data kunjungan terakhir di bulan tersebut.</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Ambil data yang sudah disiapkan oleh controller
    const labels = <?= $chart_labels; ?>;
    const datasets = <?= $chart_datasets; ?>;

    const chartData = {
        labels: labels,
        datasets: datasets
    };

    const config = {
        type: 'line',
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
                    text: 'Tren Kandang Kosong Bulanan'
                },
                tooltip: {
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
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100, // Persentase maksimal adalah 100
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    },
                    title: {
                        display: true,
                        text: 'Persentase Kosong (%)'
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
