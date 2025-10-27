<div class="container-fluid">
    <section class="content-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="font-weight-bold">Laporan Kasus Penyakit</h1>
            <a href="<?= base_url('Dashboard_new') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Menu</a>
        </div>
    </section>
    <section class="content">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Filter Laporan</h3></div>
                <div class="card-body">
                    <form action="<?= base_url('Dashboard_new/visual_kasus_penyakit') ?>" method="post" class="form-inline">
                        <div class="form-group mx-sm-3 mb-2">
                            <label for="tahun" class="mr-2">Tahun:</label>
                            <select name="tahun" id="tahun" class="form-control">
                                <option value="0">-- Semua Tahun --</option>
                                <?php for ($i = date('Y'); $i >= date('Y') - 7; $i--): ?><option value="<?= $i; ?>" <?= ($selected_year == $i) ? 'selected' : ''; ?>><?= $i; ?></option><?php endfor; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary mb-2 ml-sm-3">Tampilkan</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Grafik Kasus per Bulan</h3></div>
                <div class="card-body">
                    <?php if(isset($chart_labels) && !empty(json_decode($chart_labels))): ?>
                        <canvas id="kasusStackedChart" style="min-height: 250px; height: 350px; max-height: 400px; max-width: 100%;"></canvas>
                    <?php else: ?>
                        <div class="text-center py-5"><p class="text-muted">Tidak ada data untuk ditampilkan pada periode yang dipilih.</p></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Tabel Pivot Kasus per Area</h3></div>
                <div class="card-body p-0">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr class="text-center">
                                <th style="width: 200px;">Area</th>
                                <?php foreach ($pivot_table_categories as $kategori): ?>
                                    <th><?= htmlspecialchars($kategori); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pivot_table_data)): ?>
                                <tr><td colspan="<?= count($pivot_table_categories) + 1; ?>" class="text-center">Tidak ada data untuk ditampilkan.</td></tr>
                            <?php else: ?>
                                <?php foreach ($pivot_table_data as $area_data): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($area_data['nama_area']); ?></strong></td>
                                        <?php foreach ($pivot_table_categories as $kategori): ?>
                                            <td class="text-center"><?= $area_data[$kategori] ?? 0; ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>


            <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Rincian Laporan Kasus</h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">No.</th>
                                            <th style="width: 200px;">Waktu Kunjungan</th>
                                            <th>Nama Farm</th>
                                            <th>Jenis Kasus</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($kasus_detail_list)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center">Tidak ada rincian kasus untuk ditampilkan.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $no = 1; ?>
                                            <?php foreach ($kasus_detail_list as $row): ?>
                                                <tr>
                                                    <td><?= $no++; ?>.</td>
                                                    <td><?= date('d M Y, H:i', strtotime($row['waktu_kunjungan'])); ?></td>
                                                    <td><?= htmlspecialchars($row['nama_farm']); ?></td>
                                                    <td><?= htmlspecialchars($row['jenis_kasus']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        
    </section>
</div>

        </div>
    </section>
</div>

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
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                // --- INI UNTUK MENAMBAHKAN '%' DI TOOLTIP (HOVER) ---
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        // Menampilkan persentase dengan 1 angka desimal
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
                        x: { stacked: true },
                        y: { 
                            stacked: true,
                            max: 100, // Memastikan sumbu Y berhenti di 100
                            // --- INI UNTUK MENAMBAHKAN '%' DI SUMBU Y ---
                            ticks: {
                                callback: function(value, index, values) {
                                    return value + '%';
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
