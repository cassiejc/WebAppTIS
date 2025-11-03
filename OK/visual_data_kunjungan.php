<div class="container-fluid">
    <!-- Header Section with Gradient -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="font-weight-bold mb-2" style="color: #2c3e50;">
                        <i class="fas fa-chart-line mr-2" style="color: #3498db;"></i>Data Kunjungan
                    </h1>
                    <p class="text-muted mb-0">Laporan performa dan analisis kunjungan surveyor</p>
                </div>
                <a href="<?= site_url('Dashboard_new') ?>" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="col-sm-12">
            <!-- Filter Card with Modern Design -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-gradient-primary text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-filter mr-2"></i>Filter Laporan
                    </h3>
                </div>
                <div class="card-body p-4">
                    <form action="<?= site_url('Dashboard_new/visual_data_kunjungan') ?>" method="post" class="row g-3">
                        <div class="col-md-5">
                            <label for="bulan" class="form-label fw-bold text-secondary">
                                <i class="fas fa-calendar-alt mr-2"></i>Bulan
                            </label>
                            <select name="bulan" id="bulan" class="form-control form-select" style="border-radius: 10px;">
                                <option value="0" <?= ($selected_month == 0) ? 'selected' : ''; ?>>-- Semua Bulan --</option>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= $i; ?>" <?= ($selected_month == $i) ? 'selected' : ''; ?>>
                                        <?= date('F', mktime(0, 0, 0, $i, 10)); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="tahun" class="form-label fw-bold text-secondary">
                                <i class="fas fa-calendar mr-2"></i>Tahun
                            </label>
                            <select name="tahun" id="tahun" class="form-control form-select" style="border-radius: 10px;">
                                <option value="0" <?= ($selected_year == 0) ? 'selected' : ''; ?>>-- Semua Tahun --</option>
                                <?php for ($i = date('Y'); $i >= date('Y') - 7; $i--): ?>
                                    <option value="<?= $i; ?>" <?= ($selected_year == $i) ? 'selected' : ''; ?>><?= $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100" style="border-radius: 10px; height: 45px;">
                                <i class="fas fa-search mr-2"></i>Tampilkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Performance Cards Row -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0" style="border-radius: 15px;">
                        <div class="card-header bg-white border-0 pt-4 pb-3">
                            <h3 class="card-title font-weight-bold" style="color: #2c3e50;">
                                <i class="fas fa-users mr-2" style="color: #e74c3c;"></i>Laporan Performa Surveyor
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 500px;">
                                <table class="table table-hover mb-0">
                                    <thead style="background-color: #f8f9fa; position: sticky; top: 0; z-index: 10;">
                                        <tr>
                                            <th style="width: 60px; border-top: none;" class="text-center">No.</th>
                                            <th style="border-top: none;">Surveyor</th>
                                            <th style="border-top: none;" class="text-center">Target</th>
                                            <th style="border-top: none;" class="text-center">Aktual</th>
                                            <th style="width: 200px; border-top: none;" class="text-center">Pencapaian</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($performance_data)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-5 text-muted">
                                                    <i class="fas fa-inbox fa-3x mb-3 d-block" style="opacity: 0.3;"></i>
                                                    Tidak ada data untuk ditampilkan
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $no = 1; foreach ($performance_data as $row): ?>
                                                <tr style="transition: all 0.3s ease;">
                                                    <td class="text-center align-middle">
                                                        <span class="badge badge-light rounded-circle" style="width: 35px; height: 35px; line-height: 35px; font-size: 14px;">
                                                            <?= $no++; ?>
                                                        </span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-circle mr-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                                                <?= strtoupper(substr($row['surveyor_name'], 0, 1)); ?>
                                                            </div>
                                                            <strong><?= htmlspecialchars($row['surveyor_name']); ?></strong>
                                                        </div>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <span class="badge badge-light px-3 py-2" style="font-size: 14px;">
                                                            <?= number_format($row['target']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <span class="badge badge-info px-3 py-2" style="font-size: 14px;">
                                                            <?= number_format($row['aktual']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="d-flex align-items-center">
                                                            <div class="progress flex-grow-1 mr-3" style="height: 10px; border-radius: 10px;">
                                                                <div class="progress-bar <?= $row['achievement_percent'] >= 100 ? 'bg-success' : 'bg-warning' ?>" 
                                                                     style="width: <?= min($row['achievement_percent'], 100); ?>%; border-radius: 10px;"></div>
                                                            </div>
                                                            <span class="badge <?= $row['achievement_percent'] >= 100 ? 'badge-success' : 'badge-warning' ?>" 
                                                                  style="min-width: 60px; font-size: 13px; padding: 6px 12px; border-radius: 20px;">
                                                                <?= round($row['achievement_percent'], 1); ?>%
                                                            </span>
                                                        </div>
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
            </div>

            <!-- Area Performance -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h3 class="card-title font-weight-bold" style="color: #2c3e50;">
                        <i class="fas fa-map-marked-alt mr-2" style="color: #f39c12;"></i>Laporan Performa per Area
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive table-no-horizontal-scroll" style="max-height: 500px;">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #f8f9fa;">
                                <tr>
                                    <th style="border-top: none;">Area</th>
                                    <th style="border-top: none;" class="text-center">Total Target</th>
                                    <th style="border-top: none;" class="text-center">Total Aktual</th>
                                    <th style="width: 200px; border-top: none;" class="text-center">Pencapaian</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($area_performance_data)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block" style="opacity: 0.3;"></i>
                                            Tidak ada data untuk ditampilkan
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($area_performance_data as $row): ?>
                                        <tr style="transition: all 0.3s ease;">
                                            <td class="align-middle">
                                                <strong style="color: #2c3e50;">
                                                    <i class="fas fa-map-marker-alt mr-2" style="color: #3498db;"></i>
                                                    <?= htmlspecialchars($row['nama_area']); ?>
                                                </strong>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge badge-light px-3 py-2" style="font-size: 14px;">
                                                    <?= number_format($row['total_target']); ?>
                                                </span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge badge-info px-3 py-2" style="font-size: 14px;">
                                                    <?= number_format($row['total_aktual']); ?>
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 mr-3" style="height: 10px; border-radius: 10px;">
                                                        <div class="progress-bar bg-info" style="width: <?= min($row['achievement_percent'], 100); ?>%; border-radius: 10px;"></div>
                                                    </div>
                                                    <span class="badge badge-info" style="min-width: 60px; font-size: 13px; padding: 6px 12px; border-radius: 20px;">
                                                        <?= round($row['achievement_percent'], 1); ?>%
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Visit Breakdown -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h3 class="card-title font-weight-bold" style="color: #2c3e50;">
                        <i class="fas fa-chart-pie mr-2" style="color: #9b59b6;"></i>Komposisi Visit
                    </h3>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive table-no-horizontal-scroll" style="max-height: 500px;">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #f8f9fa;">
                                <tr>
                                    <th style="border-top: none;">Komoditas / Tujuan</th>
                                    <th style="width: 100px; border-top: none;" class="text-right">Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($visit_breakdown_data)): ?>
                                    <tr>
                                        <td colspan="2" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block" style="opacity: 0.3;"></i>
                                            Tidak ada data
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    $colors = ['#1e3c72', '#2a5298', '#3498db', '#5dade2', '#21618c', '#1a5490', '#154360'];
                                    $index = 0;
                                    foreach ($visit_breakdown_data as $row): 
                                        $color = $colors[$index % count($colors)];
                                    ?>
                                        <tr style="transition: all 0.3s ease;">
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center">
                                                    <div style="width: 12px; height: 12px; border-radius: 50%; background-color: <?= $color; ?>; margin-right: 12px;"></div>
                                                    <?= htmlspecialchars($row['kategori']); ?>
                                                </div>
                                            </td>
                                            <td class="text-right align-middle">
                                                <strong style="color: <?= $color; ?>; font-size: 16px;">
                                                    <?= round($row['persentase'], 2); ?>%
                                                </strong>
                                            </td>
                                        </tr>
                                    <?php 
                                        $index++;
                                    endforeach; 
                                    ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Detail Log Kunjungan dengan Expandable Rows -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 pt-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="card-title font-weight-bold mb-0" style="color: #2c3e50;">
                                <i class="fas fa-clipboard-list mr-2" style="color: #16a085;"></i>Detail Log Kunjungan
                            </h4>
                            <small class="text-muted">Klik pada baris untuk melihat detail lengkap</small>
                        </div>
                        <div class="col-md-6">
                            <div class="search-box">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 10px 0 0 10px;">
                                            <i class="fas fa-search text-muted"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control border-left-0" id="searchInput" 
                                           placeholder="Cari data..." 
                                           style="border-radius: 0 10px 10px 0; border-left: none;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 700px; overflow-y: auto;">                                       
                        <table class="table mb-0" id="dataTableVisitDetails">
                            <thead style="background-color: #f8f9fa; position: sticky; top: 0; z-index: 10;">
                                <tr>
                                    <th style="border-top: none; width: 30px;"></th>
                                    <th style="border-top: none; width: 120px;">Username</th>
                                    <th style="border-top: none; width: 130px;">Jenis Visit</th>
                                    <th style="border-top: none; width: 100px;">Nama Customer</th>
                                    <th style="border-top: none; width: 100px;">Kapasitas</th>
                                    <th style="border-top: none; width: 100px;">Waktu</th>
                                    <th style="border-top: none; width: 100px;">Tujuan</th>
                                    <th style="border-top: none; width: 120px;">Kasus</th>
                                    <th style="border-top: none; width: 120px;">Pakan</th>
                                    <th style="border-top: none; width: 100px;">Alamat</th>
                                    <th style="border-top: none; width: 100px;" class="text-center">Lokasi</th>
                                </tr>
                            </thead>
                            <tbody id="visitTableBody">
                                <?php if (!empty($visit_details_table)): ?>
                                    <?php foreach ($visit_details_table as $visit): ?>
                                        <tr class="visit-row" style="transition: all 0.3s ease;">
                                            <td class="text-center visit-cell-icon">
                                                <i class="fas fa-chevron-right expand-icon"></i>
                                            </td>
                                            <td class="visit-cell">
                                                <span class="badge badge-primary" style="border-radius: 20px; padding: 6px 12px;">
                                                    <?php echo htmlspecialchars($visit['username'], ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td class="visit-cell"><?php echo htmlspecialchars($visit['kategori_visit'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="visit-cell wide"><strong><?php echo htmlspecialchars($visit['nama_customer'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                            <td class="visit-cell"><?php echo htmlspecialchars($visit['kapasitas'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="visit-cell">
                                                <small class="text-muted">
                                                    <i class="far fa-clock mr-1"></i>
                                                    <?php echo htmlspecialchars(date('d M Y, H:i', strtotime($visit['waktu_kunjungan'])), ENT_QUOTES, 'UTF-8'); ?>
                                                </small>
                                            </td>
                                            <td class="visit-cell wide"><?php echo htmlspecialchars($visit['tujuan_kunjungan'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="visit-cell"><?php echo htmlspecialchars($visit['jenis_kasus'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="visit-cell"><?php echo htmlspecialchars($visit['pakan'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="visit-cell extra-wide"><small><?php echo htmlspecialchars($visit['location_address'], ENT_QUOTES, 'UTF-8'); ?></small></td>
                                            <td class="text-center visit-cell">
                                                <a href="https://www.google.com/maps/search/?api=1&query=<?php echo $visit['latitude']; ?>,<?php echo $visit['longitude']; ?>" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary rounded-pill"
                                                   onclick="event.stopPropagation();">
                                                    <i class="fas fa-map-marker-alt mr-1"></i>Lihat
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="11" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block" style="opacity: 0.3;"></i>
                                            Tidak ada data kunjungan untuk periode ini.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<style>
    /* Modern Enhancements */
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
        border-color: #2a5298;
        box-shadow: 0 0 0 0.2rem rgba(42, 82, 152, 0.15);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        border: none;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(42, 82, 152, 0.4);
    }
    
    .progress-bar {
        transition: width 0.6s ease;
    }
    
    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
    }
    
    /* Scrollbar Styling */
    .table-responsive::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background: #2a5298;
        border-radius: 10px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #1e3c72;
    }
    
    /* Hide horizontal scrollbar only for specific tables */
    .table-no-horizontal-scroll {
        overflow-x: hidden !important;
        overflow-y: auto !important;
    }
    
    .table-no-horizontal-scroll::-webkit-scrollbar {
        width: 8px;
        height: 0px;
    }
    
    .table-no-horizontal-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .table-no-horizontal-scroll::-webkit-scrollbar-thumb {
        background: #2a5298;
        border-radius: 10px;
    }
    
    .table-no-horizontal-scroll::-webkit-scrollbar-thumb:hover {
        background: #1e3c72;
    }
    
    /* Expandable Row Styles - MODIFIED FOR VERTICAL EXPANSION */
    .visit-row {
        cursor: pointer;
        transition: all 0.3s ease;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .visit-row:hover {
        background-color: #f8f9fa;
    }
    
    .visit-row.expanded {
        background-color: #e3f2fd;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    /* Cell styling untuk expand vertikal */
    .visit-cell {
        padding: 12px;
        vertical-align: top;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: all 0.3s ease;
        line-height: 1.5;
    }
    
    /* Lebar kolom yang lebih besar */
    .visit-cell.wide {
        max-width: 100px;
    }
    
    .visit-cell.extra-wide {
        max-width: 100px;
    }
    
    /* Cell icon tidak berubah */
    .visit-cell-icon {
        padding: 12px;
        vertical-align: middle;
        width: 30px;
    }
    
    /* Saat expanded - text wrap dengan lebar tetap */
    .visit-row.expanded .visit-cell {
        white-space: normal;
        word-wrap: break-word;
        overflow-wrap: break-word;
        padding: 16px 12px;
        vertical-align: top;
    }
    
    /* Icon expand */
    .expand-icon {
        transition: transform 0.3s ease;
        color: #2a5298;
        font-size: 12px;
    }
    
    .visit-row.expanded .expand-icon {
        transform: rotate(90deg);
    }
    
    .badge-primary {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        border: none;
    }
    
    /* Pastikan table layout fixed untuk konsistensi lebar kolom */
    #dataTableVisitDetails {
        table-layout: fixed;
        width: 100%;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Expandable Row Functionality
    const visitRows = document.querySelectorAll('.visit-row');
    
    visitRows.forEach(function(row) {
        row.addEventListener('click', function(e) {
            // Jangan expand jika yang diklik adalah tombol Lihat Lokasi
            if (e.target.closest('.btn-outline-primary')) {
                return;
            }
            
            const allRows = document.querySelectorAll('.visit-row');
            
            // Jika row sudah expanded, collapse
            if (this.classList.contains('expanded')) {
                this.classList.remove('expanded');
            } else {
                // Collapse semua row lain
                allRows.forEach(r => r.classList.remove('expanded'));
                // Expand row yang diklik
                this.classList.add('expanded');
            }
        });
    });
    
    // Search Functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#dataTableVisitDetails tbody tr.visit-row');

            tableRows.forEach(function(row) {
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                    row.classList.remove('expanded'); // Remove expanded state when hiding
                }
            });
        });
    }
});
</script>
