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

            <!-- Area Performance -->
            <div class="card-header bg-white border-0 pt-4 pb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title font-weight-bold" style="color: #2c3e50;">
                        <i class="fas fa-map-marked-alt mr-2" style="color: #f39c12;"></i>Laporan Performa per Area
                    </h3>
                    <a id="resetAreaFilter" class="btn btn-sm btn-outline-secondary" style="display:none; border-radius: 20px; font-size: 0.8rem;">
                        <i class="fas fa-sync-alt mr-1"></i> Tampilkan Semua
                    </a>
                </div>
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
                            <tbody id="areaPerformanceBody">
                                <?php if (empty($area_performance_data)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block" style="opacity: 0.3;"></i>
                                            Tidak ada data untuk ditampilkan
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($area_performance_data as $row): ?>
                                        <tr class="area-row" 
                                            data-area-id="<?= $row['master_area_id']; ?>" 
                                            data-area-name="<?= htmlspecialchars($row['nama_area']); ?>" 
                                            style="cursor: pointer; transition: all 0.3s ease;">
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

            <!-- Performance Cards Row -->
            <div class="row mb-4">
                <div class="col-12">
                   <div class="card shadow-sm border-0" style="border-radius: 15px;">
                        <div class="card-header bg-white border-0 pt-4 pb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title font-weight-bold" style="color: #2c3e50;">
                                    <i class="fas fa-users mr-2" style="color: #e74c3c;"></i>Laporan Performa Surveyor
                                </h3>
                                <a id="resetSurveyorFilter" class="btn btn-sm btn-outline-secondary" style="display:none; border-radius: 20px; font-size: 0.8rem;">
                                    <i class="fas fa-sync-alt mr-1"></i> Tampilkan Semua Area
                                </a>
                            </div>
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
                                    <tbody id="surveyorPerformanceBody">
                                        <?php if (empty($performance_data)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-5 text-muted">
                                                    <i class="fas fa-inbox fa-3x mb-3 d-block" style="opacity: 0.3;"></i>
                                                    Tidak ada data untuk ditampilkan
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $no = 1; foreach ($performance_data as $row): ?>
                                                        <tr class="surveyor-row" 
                                                            data-user-id="<?= $row['id_user']; ?>" 
                                                            data-username="<?= htmlspecialchars($row['surveyor_name']); ?>" 
                                                            style="cursor: pointer; transition: all 0.3s ease;">

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

            

            <!-- Visit Breakdown -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-weight-bold" style="color: #2c3e50;">
                            <i class="fas fa-chart-pie mr-2" style="color: #9b59b6;"></i>Komposisi Visit
                        </h3>
                        <a id="resetCompositionFilter" class="btn btn-sm btn-outline-secondary" style="display:none; border-radius: 20px; font-size: 0.8rem;">
                            <i class="fas fa-sync-alt mr-1"></i> Tampilkan Semua
                        </a>
                    </div>
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
                            <tbody id="compositionVisitBody">
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
                            <a id="resetLogFilter" class="btn btn-sm btn-outline-secondary ml-2" style="display:none; border-radius: 20px; font-size: 0.8rem; vertical-align: middle;">
                                <i class="fas fa-sync-alt mr-1"></i> Tampilkan Semua
                            </a>
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
                                    <tr class="visit-row" 
                                        data-username="<?php echo htmlspecialchars($visit['username'], ENT_QUOTES, 'UTF-8'); ?>" 
                                        style="transition: all 0.3s ease;">

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

    .table tbody tr.row-selected {
        background-color: #e9ecef !important; /* Warna abu-abu Bootstrap */
        transform: none !important; /* Matikan efek geser saat dipilih */
        box-shadow: none !important; /* Matikan bayangan saat dipilih */
    }
    
    .table tbody tr.row-selected:hover {
        background-color: #e9ecef !important; /* Pastikan tetap abu-abu saat di-hover */
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    
    // --- FUNGSI EXPANDABLE ROW ---
    const logTableBody = document.getElementById('visitTableBody');
    if (logTableBody) {
        logTableBody.addEventListener('click', function(e) {
            if (e.target.closest('.btn-outline-primary')) {
                return;
            }
            const row = e.target.closest('.visit-row');
            if (!row) return;

            const allRows = logTableBody.querySelectorAll('.visit-row');
            
            if (row.classList.contains('expanded')) {
                row.classList.remove('expanded');
            } else {
                allRows.forEach(r => r.classList.remove('expanded'));
                row.classList.add('expanded');
            }
        });
    }
    
    // --- FUNGSI SEARCH ---
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#dataTableVisitDetails tbody tr.visit-row');
            const noDataRow = document.querySelector('#visitTableBody .no-data-filter');

            if (noDataRow) noDataRow.remove();

            let hasVisibleRows = false;
            tableRows.forEach(function(row) {
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(searchValue)) {
                    row.style.display = '';
                    hasVisibleRows = true;
                } else {
                    row.style.display = 'none';
                    row.classList.remove('expanded');
                }
            });

            if (!hasVisibleRows && tableRows.length > 0) {
                 const colspan = tableRows[0].cells.length;
                 logTableBody.insertAdjacentHTML('beforeend', `
                    <tr class="no-data-filter">
                        <td colspan="${colspan}" class="text-center py-5 text-muted">
                            <i class="fas fa-search fa-3x mb-3 d-block" style="opacity: 0.3;"></i>
                            Tidak ada data log yang cocok dengan pencarian "${escapeHTML(searchValue)}"
                        </td>
                    </tr>
                `);
            }
        });
    }

    // --- PENGATURAN UMUM UNTUK AJAX ---
    const siteUrl = "<?= site_url('Dashboard_new'); ?>";
    const currentMonth = "<?= $selected_month; ?>";
    const currentYear = "<?= $selected_year; ?>";
    const csrfTokenName = "<?= $this->security->get_csrf_token_name(); ?>";
    let csrfTokenHash = "<?= $this->security->get_csrf_hash(); ?>";

    // --- ELEMEN TABEL & TOMBOL ---
    const areaTableBody = document.getElementById('areaPerformanceBody');
    const surveyorTableBody = document.getElementById('surveyorPerformanceBody');
    const compositionTableBody = document.getElementById('compositionVisitBody');
    
    const resetAreaBtn = document.getElementById('resetAreaFilter');
    const resetSurveyorBtn = document.getElementById('resetSurveyorFilter');
    const resetCompBtn = document.getElementById('resetCompositionFilter');
    const resetLogBtn = document.getElementById('resetLogFilter');
    
    // --- EVENT: Klik baris AREA ---
    if (areaTableBody) {
        areaTableBody.addEventListener('click', function(e) {
            const row = e.target.closest('.area-row');
            if (!row) return;

            const areaId = row.dataset.areaId;
            const areaName = row.dataset.areaName;

            // --- LOGIKA HIGHLIGHT (MENGGUNAKAN CLASS) ---
            const allAreaRows = areaTableBody.querySelectorAll('.area-row');
            allAreaRows.forEach(r => r.classList.remove('row-selected')); // Hapus class dari semua
            row.classList.add('row-selected'); // Tambahkan class ke yang diklik
            // ------------------------------------------

            if(surveyorTableBody) surveyorTableBody.innerHTML = `<tr><td colspan="5" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2 text-muted">Memuat surveyor untuk ${escapeHTML(areaName)}...</p></td></tr>`;
            if(compositionTableBody) updateCompositionTable(null, true);
            if(logTableBody) filterLogTable(null, true);

            const formData = new URLSearchParams();
            formData.append('area_id', areaId);
            formData.append('bulan', currentMonth);
            formData.append('tahun', currentYear);
            formData.append(csrfTokenName, csrfTokenHash);

            fetch(`${siteUrl}/get_surveyors_for_area_ajax`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(response => {
                csrfTokenHash = response.headers.get('X-CSRF-TOKEN') || csrfTokenHash;
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    updateSurveyorTable(data.surveyor_data, areaName);
                    if(resetSurveyorBtn) resetSurveyorBtn.style.display = 'inline-block';
                    if(resetCompBtn) resetCompBtn.style.display = 'none';
                    if(resetLogBtn) resetLogBtn.style.display = 'none';
                } else {
                    if(surveyorTableBody) surveyorTableBody.innerHTML = `<tr><td colspan="5" class="text-center py-5 text-danger">Gagal memuat data surveyor.</td></tr>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if(surveyorTableBody) surveyorTableBody.innerHTML = `<tr><td colspan="5" class="text-center py-5 text-danger">Terjadi kesalahan jaringan.</td></tr>`;
            });
        });
    }

    // --- EVENT: Klik baris SURVEYOR ---
    if (surveyorTableBody) {
        surveyorTableBody.addEventListener('click', function(e) {
            const row = e.target.closest('.surveyor-row');
            if (!row) return;

            const userId = row.dataset.userId;
            const username = row.dataset.username;
            
            // --- LOGIKA HIGHLIGHT (MENGGUNAKAN CLASS) ---
            const allSurveyorRows = surveyorTableBody.querySelectorAll('.surveyor-row');
            allSurveyorRows.forEach(r => r.classList.remove('row-selected')); // Hapus class dari semua
            row.classList.add('row-selected'); // Tambahkan class ke yang diklik
            // ------------------------------------------

            if(compositionTableBody) compositionTableBody.innerHTML = `<tr><td colspan="2" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2 text-muted">Memuat komposisi...</p></td></tr>`;
            
            filterLogTable(username);
            
            const formData = new URLSearchParams();
            formData.append('user_id', userId);
            formData.append('bulan', currentMonth);
            formData.append('tahun', currentYear);
            formData.append(csrfTokenName, csrfTokenHash);

            fetch(`${siteUrl}/get_data_for_surveyor_ajax`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(response => {
                csrfTokenHash = response.headers.get('X-CSRF-TOKEN') || csrfTokenHash;
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    updateCompositionTable(data.composition_data);
                    if(resetCompBtn) resetCompBtn.style.display = 'inline-block';
                    if(resetLogBtn) resetLogBtn.style.display = 'inline-block';
                } else {
                    if(compositionTableBody) compositionTableBody.innerHTML = `<tr><td colspan="2" class="text-center py-5 text-danger">Gagal memuat data.</td></tr>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if(compositionTableBody) compositionTableBody.innerHTML = `<tr><td colspan="2" class="text-center py-5 text-danger">Terjadi kesalahan jaringan.</td></tr>`;
            });
        });
    }

    // --- FUNGSI TOMBOL RESET ---
    const resetAllFilters = (e) => {
        e.preventDefault();
        location.reload(); 
    };
    
    if(resetAreaBtn) resetAreaBtn.addEventListener('click', resetAllFilters);
    if(resetSurveyorBtn) resetSurveyorBtn.addEventListener('click', resetAllFilters);
    if(resetCompBtn) resetCompBtn.addEventListener('click', resetAllFilters);
    if(resetLogBtn) resetLogBtn.addEventListener('click', resetAllFilters);
    
    
    // --- (HELPER) Update Tabel Surveyor ---
    function updateSurveyorTable(data, areaName) {
        if (!surveyorTableBody) return;
        surveyorTableBody.innerHTML = ''; 

        if (!data || data.length === 0) {
             surveyorTableBody.innerHTML = `<tr><td colspan="5" class="text-center py-5 text-muted"><i class="fas fa-inbox fa-3x mb-3 d-block" style="opacity: 0.3;"></i>Tidak ada data surveyor untuk area ${escapeHTML(areaName)}.</td></tr>`;
             return;
        }

        let no = 1;
        data.forEach(row => {
            const percent = parseFloat(row.achievement_percent).toFixed(1);
            const percentNum = parseFloat(row.achievement_percent);
            const minPercent = Math.min(percentNum, 100);
            const progressClass = percentNum >= 100 ? 'bg-success' : 'bg-warning';
            const badgeClass = percentNum >= 100 ? 'badge-success' : 'badge-warning';
            
            const tr = `
                <tr class="surveyor-row" 
                    data-user-id="${row.id_user}" 
                    data-username="${escapeHTML(row.surveyor_name)}" 
                    style="cursor: pointer; transition: all 0.3s ease;">
                    <td class="text-center align-middle">
                        <span class="badge badge-light rounded-circle" style="width: 35px; height: 35px; line-height: 35px; font-size: 14px;">
                            ${no++}
                        </span>
                    </td>
                    <td class="align-middle">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle mr-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                ${escapeHTML(row.surveyor_name.substring(0, 1).toUpperCase())}
                            </div>
                            <strong>${escapeHTML(row.surveyor_name)}</strong>
                        </div>
                    </td>
                    <td class="text-center align-middle">
                        <span class="badge badge-light px-3 py-2" style="font-size: 14px;">
                            ${new Intl.NumberFormat('id-ID').format(row.target)}
                        </span>
                    </td>
                    <td class="text-center align-middle">
                        <span class="badge badge-info px-3 py-2" style="font-size: 14px;">
                            ${new Intl.NumberFormat('id-ID').format(row.aktual)}
                        </span>
                    </td>
                    <td class="align-middle">
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1 mr-3" style="height: 10px; border-radius: 10px;">
                                <div class="progress-bar ${progressClass}" style="width: ${minPercent}%; border-radius: 10px;"></div>
                            </div>
                            <span class="badge ${badgeClass}" style="min-width: 60px; font-size: 13px; padding: 6px 12px; border-radius: 20px;">
                                ${percent}%
                            </span>
                        </div>
                    </td>
                </tr>
            `;
            surveyorTableBody.insertAdjacentHTML('beforeend', tr);
        });
    }

    // --- (HELPER) Filter Tabel Log ---
    function filterLogTable(username, reset = false) {
        if (!logTableBody) return;
        const allLogRows = logTableBody.querySelectorAll('tr.visit-row');
        let hasData = false;
        
        const existingNoDataRow = logTableBody.querySelector('.no-data-filter');
        if (existingNoDataRow) existingNoDataRow.remove();

        const originalNoData = logTableBody.querySelector('.original-no-data');

        if (reset) {
            allLogRows.forEach(row => {
                row.style.display = '';
                row.classList.remove('expanded');
            });
            if (originalNoData) originalNoData.style.display = '';
            return;
        }

        allLogRows.forEach(row => {
            if (row.dataset.username === username) {
                row.style.display = ''; 
                hasData = true;
            } else {
                row.style.display = 'none';
                row.classList.remove('expanded');
            }
        });

        if (originalNoData) originalNoData.style.display = 'none';

        if (!hasData) {
            const colspan = allLogRows.length > 0 ? allLogRows[0].cells.length : 11;
            logTableBody.insertAdjacentHTML('beforeend', `
                <tr class="no-data-filter">
                    <td colspan="${colspan}" class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 d-block" style="opacity: 0.3;"></i>
                        Tidak ada data log untuk surveyor ${escapeHTML(username)}
                    </td>
                </tr>
            `);
        }
    }
    
    // --- (HELPER) Update Tabel Komposisi ---
    function updateCompositionTable(data, reset = false) {
        if (!compositionTableBody) return;
        compositionTableBody.innerHTML = ''; 
        
        const originalNoData = document.querySelector('.original-no-data-composition');

        if (reset) {
             compositionTableBody.innerHTML = `<tr><td colspan="2" class="text-center py-5 text-muted"><i class="fas fa-hand-pointer fa-3x mb-3 d-block" style="opacity: 0.3;"></i>Pilih surveyor untuk melihat komposisi visit.</td></tr>`;
             if (originalNoData) originalNoData.style.display = 'none';
             return;
        }

        if (!data || data.length === 0) {
             compositionTableBody.innerHTML = `<tr><td colspan="2" class="text-center py-5 text-muted"><i class="fas fa-inbox fa-3x mb-3 d-block" style="opacity: 0.3;"></i>Tidak ada data komposisi visit untuk surveyor ini.</td></tr>`;
             if (originalNoData) originalNoData.style.display = 'none';
             return;
        }

        if (originalNoData) originalNoData.style.display = 'none';

        const colors = ['#1e3c72', '#2a5298', '#3498db', '#5dade2', '#21618c', '#1a5490', '#154360'];
        let index = 0;

        data.forEach(row => {
            const color = colors[index % colors.length];
            const percent = parseFloat(row.persentase).toFixed(2);
            
            const tr = `
                <tr style="transition: all 0.3s ease;">
                    <td class="align-middle">
                        <div class="d-flex align-items-center">
                            <div style="width: 12px; height: 12px; border-radius: 50%; background-color: ${color}; margin-right: 12px;"></div>
                            ${escapeHTML(row.kategori)}
                        </div>
                    </td>
                    <td class="text-right align-middle">
                        <strong style="color: ${color}; font-size: 16px;">
                            ${percent}%
                        </strong>
                    </td>
                </tr>
            `;
            compositionTableBody.insertAdjacentHTML('beforeend', tr);
            index++;
        });
    }
    
   // --- HELPER XSS (INI BARU 100% BENAR) ---
    function escapeHTML(str) {
        if (str === null || str === undefined) return '';
        // Mengganti karakter spesial dengan entitas HTML
        return str.toString().replace(/[&<>"']/g, function(m) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[m];
        });
    }

    // --- Menandai baris "no data" asli ---
    const originalLogNoData = document.querySelector('#visitTableBody tr:not(.visit-row)');
    if (originalLogNoData) {
        originalLogNoData.classList.add('original-no-data');
    }
    
    const originalCompositionNoData = document.querySelector('#compositionVisitBody tr:not([style*="transition"])');
    if (originalCompositionNoData) {
        originalCompositionNoData.classList.add('original-no-data-composition');
    }

    // --- Reset Tampilan Awal ---
    if(compositionTableBody) updateCompositionTable(null, true);
    if(logTableBody) filterLogTable(null, true); 
    
    if(resetSurveyorBtn) resetSurveyorBtn.style.display = 'none';
    if(resetCompBtn) resetCompBtn.style.display = 'none';
    if(resetLogBtn) resetLogBtn.style.display = 'none';
    
    // Tampilkan tombol reset area HANYA jika user BUKAN surveyor/koordinator
    <?php if (!isset($user['group_user']) || ($user['group_user'] !== 'surveyor' && $user['group_user'] !== 'koordinator')): ?>
        if(resetAreaBtn) resetAreaBtn.style.display = 'inline-block';
    <?php else: ?>
        if(resetAreaBtn) resetAreaBtn.style.display = 'none';
    <?php endif; ?>

});
</script>
