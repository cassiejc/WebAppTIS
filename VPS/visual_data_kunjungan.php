<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="font-weight-bold">Data Kunjungan</h1>
                <a href="<?= base_url('Dashboard_new') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Menu
                </a>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Filter Laporan</h3></div>
                <div class="card-body">
                    <form action="<?= base_url('Dashboard_new/visual_data_kunjungan') ?>" method="post" class="form-inline">
                        <div class="form-group mb-2">
                            <label for="bulan" class="mr-2">Bulan:</label>
                            <select name="bulan" id="bulan" class="form-control">
                                <option value="0" <?= ($selected_month == 0) ? 'selected' : ''; ?>>-- Semua Bulan --</option>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= $i; ?>" <?= ($selected_month == $i) ? 'selected' : ''; ?>>
                                        <?= date('F', mktime(0, 0, 0, $i, 10)); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group mx-sm-3 mb-2">
                            <label for="tahun" class="mr-2">Tahun:</label>
                            <select name="tahun" id="tahun" class="form-control">
                                <option value="0" <?= ($selected_year == 0) ? 'selected' : ''; ?>>-- Semua Tahun --</option>
                                <?php for ($i = date('Y'); $i >= date('Y') - 7; $i--): ?>
                                    <option value="<?= $i; ?>" <?= ($selected_year == $i) ? 'selected' : ''; ?>><?= $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary mb-2">Tampilkan</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Laporan Performa Surveyor</h3></div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="width: 10px">No.</th>
                                <th>Surveyor</th>
                                <th class="text-center">Target</th>
                                <th class="text-center">Aktual</th>
                                <th style="width: 150px" class="text-center">Pencapaian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($performance_data)): ?>
                                <tr><td colspan="5" class="text-center">Tidak ada data untuk ditampilkan</td></tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($performance_data as $row): ?>
                                    <tr>
                                        <td><?= $no++; ?>.</td>
                                        <td><?= htmlspecialchars($row['surveyor_name']); ?></td>
                                        <td class="text-center"><?= number_format($row['target']); ?></td>
                                        <td class="text-center"><?= number_format($row['aktual']); ?></td>
                                        <td>
                                            <div class="progress progress-xs">
                                                <div class="progress-bar <?= $row['achievement_percent'] >= 100 ? 'bg-success' : 'bg-warning' ?>" style="width: <?= min($row['achievement_percent'], 100); ?>%"></div>
                                            </div>
                                            <span class="badge <?= $row['achievement_percent'] >= 100 ? 'bg-success' : 'bg-warning' ?>"><?= round($row['achievement_percent'], 1); ?>%</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header"><h3 class="card-title">Laporan Performa per Area</h3></div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Area</th>
                                <th class="text-center">Total Target</th>
                                <th class="text-center">Total Aktual</th>
                                <th style="width: 150px" class="text-center">Pencapaian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($area_performance_data)): ?>
                                <tr><td colspan="4" class="text-center">Tidak ada data untuk ditampilkan</td></tr>
                            <?php else: ?>
                                <?php foreach ($area_performance_data as $row): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($row['nama_area']); ?></strong></td>
                                        <td class="text-center"><?= number_format($row['total_target']); ?></td>
                                        <td class="text-center"><?= number_format($row['total_aktual']); ?></td>
                                        <td>
                                            <div class="progress progress-xs">
                                                <div class="progress-bar bg-info" style="width: <?= min($row['achievement_percent'], 100); ?>%"></div>
                                            </div>
                                            <span class="badge bg-info"><?= round($row['achievement_percent'], 1); ?>%</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Tabel Komposisi Visit</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Komoditas / Tujuan</th>
                                        <th style="width: 120px" class="text-right">Persentase</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($visit_breakdown_data)): ?>
                                        <tr><td colspan="2" class="text-center">Tidak ada data</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($visit_breakdown_data as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['kategori']); ?></td>
                                                <td class="text-right"><strong><?= round($row['persentase'], 2); ?>%</strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                </div>
            
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Detail Log Kunjungan</h4>

                            <div class="search-box mb-3" style="max-width: 300px;">
                                <input type="text" class="form-control" id="searchInput" placeholder="Cari data...">
                            </div>
                            <div class="table-responsive" style="max-height: 700px; overflow-y: auto;">                                       
                                <table class="table table-striped table-bordered" id="dataTableVisitDetails">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Username</th>
                                        <th>Jenis Visit</th>
                                        <th>Nama Customer</th>
                                        <th>Kapasitas</th>
                                        <th>Waktu</th>
                                        <th>Tujuan</th>
                                        <th>Kasus</th>
                                        <th>Pakan</th>
                                        <th>Alamat</th>
                                        <th>Lokasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($visit_details_table)): ?>
                                        <?php foreach ($visit_details_table as $visit): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($visit['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($visit['kategori_visit'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($visit['nama_customer'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($visit['kapasitas'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($visit['waktu_kunjungan'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($visit['tujuan_kunjungan'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($visit['jenis_kasus'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($visit['pakan'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($visit['location_address'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>
                                                    <a href="https://www.google.com/maps/search/?api=1&query=<?php echo $visit['latitude']; ?>,<?php echo $visit['longitude']; ?>" target="_blank">
                                                        Lihat Lokasi
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="10" class="text-center">Tidak ada data kunjungan untuk periode ini.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    /* ====================================================================
    SCRIPT UNTUK SEARCH BAR (Tetap Ada)
    ====================================================================
    */
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        
        searchInput.addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            
            // Mencari tabel dengan ID 'dataTableVisitDetails'
            const tableRows = document.querySelectorAll('#dataTableVisitDetails tbody tr');

            tableRows.forEach(function(row) {
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(searchValue)) {
                    row.style.display = ''; // Tampilkan baris
                } else {
                    row.style.display = 'none'; // Sembunyikan baris
                }
            });
        });
    }
    /* ==================================================================== */

});
</script>
