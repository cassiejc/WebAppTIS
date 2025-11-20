<style>
    /* Modern Card Styling */
    .modern-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }
    
    .modern-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }
    
    .content-header h1 {
        color: black;
        margin: 0;
        font-size: 1.8rem;
        font-weight: 600;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Modern Filter Area */
    .area-filter-container {
        margin-bottom: 1.5rem;
        padding: 1.5rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 1px solid #dee2e6;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .area-filter-container h5 {
        color: #495057;
        font-weight: 600;
        margin-bottom: 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .area-filter-container h5::before {
        content: "ðŸŽ¯";
        font-size: 1.2rem;
    }

    /* Checkbox Styling */
    .form-check {
        display: inline-block;
        margin-right: 1.5rem;
        margin-bottom: 0.8rem;
    }
    
    .form-check-input {
        cursor: pointer;
        width: 18px;
        height: 18px;
        border-radius: 4px;
        border: 2px solid #6c757d;
        transition: all 0.2s ease;
    }
    
    .form-check-input:checked {
        background-color: #3498db;
        border-color: #3498db;
    }
    
    .form-check-input:hover {
        border-color: #3498db;
        transform: scale(1.1);
    }
    
    .form-check-label {
        margin-left: 0.5rem;
        cursor: pointer;
        color: #495057;
        font-size: 0.95rem;
        transition: color 0.2s ease;
    }
    
    .form-check-label:hover {
        color: #2980b9;
    }
    
    .form-check-label.font-weight-bold {
        color: #212529;
        font-size: 1rem;
    }

    /* Filter Controls */
    .filter-controls {
        margin-top: 1.2rem;
        padding-top: 1.2rem;
        border-top: 2px solid #dee2e6;
        display: flex;
        gap: 0.8rem;
        flex-wrap: wrap;
    }
    
    .filter-controls .btn {
        border-radius: 8px;
        padding: 0.6rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .filter-controls .btn-primary {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        border: none;
        color: white !important;
    }
    
    .filter-controls .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
    }
    
    .filter-controls .btn-secondary {
        background-color: #6c757d;
        border: none;
    }
    
    .filter-controls .btn-secondary:hover {
        background-color: #5a6268;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
    }

    /* Table Enhancements */
    .table-wrapper {
        overflow-x: auto;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table thead th {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border: none;
        padding: 1rem;
    }
    
    .table tbody tr {
        transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .table tbody td {
        vertical-align: middle;
        padding: 1rem;
        border-color: #e9ecef;
    }

    /* Farm Name Link */
    .farm-name-link {
        color: #495057;
        text-decoration: none;
        cursor: pointer;
        font-weight: 500;
        padding: 0.5rem;
        display: inline-block;
        border-radius: 6px;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .farm-name-link:hover {
        color: #3498db;
        background-color: rgba(52, 152, 219, 0.1);
        text-decoration: none;
        padding-left: 0.8rem;
    }
    
    .farm-name-link:hover::before {
        opacity: 1;
    }

    /* Detail Row */
    .detail-row td {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 1.5rem !important;
        border-left: 4px solid #3498db;
    }
    
    .detail-row h5 {
        color: #495057;
        font-weight: 600;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .detail-row h5::before {
        content: "ðŸ“…";
        font-size: 1.2rem;
    }

    /* Visit History List */
    .visit-history-list {
        margin-bottom: 0;
        padding-left: 1.5rem;
        list-style: none;
    }
    
    .visit-history-list li {
        margin-bottom: 0.8rem;
        position: relative;
        margin-left: 1rem;
    }
    
    

    .visit-date-link {
        cursor: pointer;
        color: #495057;
        text-decoration: none;
        font-weight: 500;
        padding: 0.5rem 1rem;
        display: inline-block;
        border-radius: 8px;
        background-color: white;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .visit-date-link:hover {
        color: #3498db;
        border-color: #3498db;
        background-color: rgba(52, 152, 219, 0.05);
        text-decoration: none;
        transform: translateX(5px);
        box-shadow: 0 4px 8px rgba(52, 152, 219, 0.15);
    }

    /* Modal Enhancements */
    .visit-detail-modal {
        display: none; 
        position: fixed; 
        z-index: 1050; 
        left: 0;
        top: 0;
        width: 100%; 
        height: 100%; 
        overflow: auto; 
        background-color: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        padding-top: 60px;
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .visit-detail-modal-content {
        background-color: #ffffff;
        margin: 3% auto; 
        width: 90%; 
        max-width: 700px;
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        animation: slideDown 0.3s ease;
        overflow: hidden;
    }
    
    @keyframes slideDown {
        from { 
            transform: translateY(-50px);
            opacity: 0;
        }
        to { 
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    .visit-detail-modal-content .card-header {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: white;
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: none;
    }
    
    .visit-detail-modal-content .card-header h4 {
        margin: 0;
        font-weight: 600;
        font-size: 1.3rem;
    }
    
    .visit-detail-close {
        font-size: 1.8rem;
        font-weight: 700;
        line-height: 1;
        color: white;
        text-shadow: none;
        opacity: 0.8;
        background-color: transparent;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        padding: 0.5rem;
        border-radius: 50%;
    }
    
    .visit-detail-close:hover {
        opacity: 1;
        background-color: rgba(255, 255, 255, 0.2);
        transform: rotate(90deg);
    }
    
    .visit-detail-modal-content .card-body {
        padding: 1.5rem;
        max-height: 70vh;
        overflow-y: auto;
    }

    /* Detail List */
    .detail-list {
        list-style: none;
        padding-left: 0;
        margin-bottom: 0;
    }
    
    .detail-list li {
        padding: 1rem;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background-color 0.2s ease;
    }
    
    .detail-list li:hover {
        background-color: #f8f9fa;
    }
    
    .detail-list li:last-child {
        border-bottom: none;
    }
    
    .detail-list strong {
        color: #495057;
        min-width: 180px;
        font-weight: 600;
        font-size: 0.95rem;
    }
    
    .detail-list span {
        text-align: right;
        color: #212529;
        word-break: break-word;
        font-weight: 500;
    }

    /* Loading Spinner */
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid rgba(52, 152, 219, 0.3);
        border-radius: 50%;
        border-top-color: #3498db;
        animation: spin 0.8s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #6c757d;
    }
    
    .empty-state::before {
        content: "ðŸ“­";
        display: block;
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .content-header h1 {
            font-size: 1.4rem;
        }
        
        .form-check {
            display: block;
            margin-right: 0;
        }
        
        .detail-list li {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .detail-list strong {
            min-width: auto;
        }
        
        .detail-list span {
            text-align: left;
        }
        
        .visit-detail-modal-content {
            width: 95%;
            margin: 10% auto;
        }
    }

    /* Catatan Section Styling */
    .catatan-section {
        margin-top: 1.5rem;
        padding: 1.2rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 8px;
        border-left: 4px solid #3498db;
    }
    
    .catatan-section strong {
        color: #495057;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.8rem;
    }
    
    .catatan-section strong::before {
        content: "ðŸ“";
        font-size: 1.2rem;
    }
    
    .catatan-section p {
        margin: 0;
        color: #212529;
        line-height: 1.6;
        font-size: 0.95rem;
    }
</style>

<div class="container-fluid">
    <!-- Header Section with Gradient -->
    <section class="content-header mb-4">
        <div class="container-fluid">
            <div class="d-flex align-items-center p-3 bg-white shadow-sm" style="border-radius: 2rem;">
                <a href="<?= site_url('Dashboard_new/index') ?>" class="btn btn-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border-radius: 50%;">
                    <i class="fas fa-home fa-lg text-white"></i>
                </a>
                <h1 class="font-weight-bold text-dark mb-0 mx-auto" style="font-size: 2rem;">
                    VIP Grower
                </h1>
            </div>
        </div>
    </section>

    <section class="content">

        <?php if ($user['group_user'] === 'administrator'): ?>
            <div class="modern-card area-filter-container">
                <form method="post" action="<?= current_url(); ?>">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <input type="hidden" name="area_filter" value="1">
                    
                    <h5>Filter Berdasarkan Area</h5>

                    <div class="mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="select-all-area">
                            <label class="form-check-label font-weight-bold" for="select-all-area">
                                Pilih Semua / Hapus Semua
                            </label>
                        </div>
                    </div>

                    <div>
                        <?php foreach ($all_areas as $area): ?>
                            <div class="form-check">
                                <input class="form-check-input area-checkbox" type="checkbox" name="areas[]" 
                                       value="<?= $area['master_area_id']; ?>" 
                                       id="area_<?= $area['master_area_id']; ?>"
                                       <?= in_array($area['master_area_id'], $selected_areas) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="area_<?= $area['master_area_id']; ?>">
                                    <?= htmlspecialchars($area['nama_area']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="filter-controls">
                        <button type="submit" class="btn btn-primary btn-sm">
                            âœ“ Terapkan Filter
                        </button>
                        <a href="<?= site_url('index.php/Dashboard_new/visual_vip_farms') ?>" class="btn btn-secondary btn-sm" >
                            â†» Reset Filter
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="modern-card card shadow-sm">
                    <div class="card-header" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; border-radius: 12px 12px 0 0;">
                        <h3 class="card-title font-weight-bold" style="margin: 0;">
                            Daftar Farm VIP (Grower)
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($vip_grower_farms)): ?>
                            <div class="table-wrapper">
                                <table class="table table-bordered table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th style="width: 80px;">No.</th>
                                            <th>Nama Farm (Klik untuk detail)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="vip-farm-table-body"
                                        data-url="<?= site_url('index.php/Dashboard_new/get_visit_history_for_farm') ?>"
                                        data-detail-url="<?= site_url('index.php/Dashboard_new/get_grower_visit_details') ?>">
                                        <?php $i = 1; foreach ($vip_grower_farms as $farm): ?>
                                            <tr>
                                                <td style="text-align: center; font-weight: 600; color: #000000ff;">
                                                    <?= $i++; ?>
                                                </td>
                                                <td>
                                                    <a class="farm-name-link"
                                                       data-farm-name="<?= htmlspecialchars($farm['nama_farm'], ENT_QUOTES); ?>">
                                                        <?= htmlspecialchars($farm['nama_farm']); ?>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <p class="text-muted">
                                    Tidak ada data farm VIP Grower yang ditemukan untuk filter Anda.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal -->
<div id="visitDetailModal" class="visit-detail-modal">
    <div class="visit-detail-modal-content card">
        <div class="card-header">
            <h4 id="modalTitle">Detail Kunjungan</h4>
            <button type="button" class="visit-detail-close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="card-body" id="modalBody">
        </div>
    </div>
</div>

<script>
var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';

document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all-area');
    const areaCheckboxes = document.querySelectorAll('.area-checkbox');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            areaCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });

        areaCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                let allChecked = true;
                areaCheckboxes.forEach(cb => {
                    if (!cb.checked) {
                        allChecked = false;
                    }
                });
                selectAllCheckbox.checked = allChecked;
            });
        });

        let initialAllChecked = true;
        areaCheckboxes.forEach(cb => {
            if (!cb.checked) {
                initialAllChecked = false;
            }
        });
        selectAllCheckbox.checked = initialAllChecked;
    }
    
    var tableBody = document.getElementById('vip-farm-table-body');
    if (!tableBody) return;

    var historyAjaxUrl = tableBody.dataset.url;
    var detailAjaxUrl = tableBody.dataset.detailUrl;
    
    var modal = document.getElementById('visitDetailModal');
    var modalBody = document.getElementById('modalBody');
    var modalTitle = document.getElementById('modalTitle');
    var closeBtn = document.querySelector('.visit-detail-close');

    function closeModal() {
        modal.style.display = "none";
        modalBody.innerHTML = '';
    }

    closeBtn.onclick = closeModal;
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }

    tableBody.addEventListener('click', function(e) {
        
        if (e.target && e.target.classList.contains('farm-name-link')) {
            e.preventDefault(); 

            var clickedLink = e.target;
            var clickedRow = clickedLink.closest('tr');
            var farmName = clickedLink.dataset.farmName;

            var existingDetailRow = clickedRow.nextElementSibling;
            if (existingDetailRow && existingDetailRow.classList.contains('detail-row')) {
                existingDetailRow.remove();
                return;
            }

            document.querySelectorAll('tr.detail-row').forEach(function(row) {
                row.remove();
            });

            var loadingRow = document.createElement('tr');
            loadingRow.classList.add('detail-row');
            loadingRow.innerHTML = 
                '<td colspan="2">' +
                '<div class="text-center p-3">' +
                '<span class="loading-spinner"></span>&nbsp; Memuat riwayat kunjungan...' +
                '</div>' +
                '</td>';
            
            clickedRow.parentNode.insertBefore(loadingRow, clickedRow.nextElementSibling);

            var formData = new FormData();
            formData.append('farm_name', farmName);
            formData.append(csrfName, csrfHash);

            fetch(historyAjaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.new_csrf_hash) {
                    csrfHash = data.new_csrf_hash;
                }

                if (data.status === 'success') {
                    var contentHtml = '';
                    if (data.history && data.history.length > 0) {
                        contentHtml = '<h5>Riwayat Kunjungan</h5><ul class="visit-history-list">';
                        
                        data.history.forEach(function(visit) {
                            contentHtml += 
                                '<li class="visit-date-link" ' +
                                'data-farm-name="' + farmName + '" ' + 
                                'data-visit-id="' + visit.visit_id + '">' + 
                                visit.waktu_kunjungan_formatted + 
                                '</li>';
                        });

                        contentHtml += '</ul>';
                    } else {
                        contentHtml = '<div class="empty-state" style="padding: 2rem;"><p class="text-muted mb-0">Tidak ada riwayat kunjungan yang ditemukan.</p></div>';
                    }
                    loadingRow.querySelector('td').innerHTML = contentHtml;
                } else {
                    loadingRow.querySelector('td').innerHTML = '<p class="text-danger mb-0">Gagal mengambil data: ' + (data.message || 'Error tidak diketahui') + '</p>';
                }
            })
            .catch(function(error) {
                console.error('Fetch Error (History):', error);
                loadingRow.querySelector('td').innerHTML = '<p class="text-danger mb-0">Terjadi kesalahan saat menghubungi server.</p>';
            });
        }

        if (e.target && e.target.classList.contains('visit-date-link')) {
            e.preventDefault();
            
            var clickedDateLink = e.target;
            var farmName = clickedDateLink.dataset.farmName;
            var visitId = clickedDateLink.dataset.visitId;

            modalTitle.innerText = 'ðŸ“‹ Detail Kunjungan Farm: ' + farmName;
            modalBody.innerHTML = '<div class="text-center p-3"><span class="loading-spinner"></span>&nbsp; Memuat detail...</div>';
            modal.style.display = "block";

            var formData = new FormData();
            formData.append('farm_name', farmName);
            formData.append('visit_id', visitId);
            formData.append(csrfName, csrfHash); 

            fetch(detailAjaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.new_csrf_hash) {
                    csrfHash = data.new_csrf_hash;
                }

                if (data.status === 'success' && data.details) {
                    var details = data.details;
                    
                    function getValue(key) {
                        return (details[key] !== null && details[key] !== undefined && details[key] !== '') ? details[key] : '-';
                    }

                    var detailHtml = '<ul class="detail-list">';
                    
                    detailHtml += '<li><strong>Nama Farm</strong><span>' + getValue('nama_farm') + '</span></li>';
                    detailHtml += '<li><strong>Populasi</strong><span>' + getValue('efektif_terisi_pedaging') + '</span></li>';
                    detailHtml += '<li><strong>Strain DOC</strong><span>' + getValue('strain_pedaging') + '</span></li>';
                    detailHtml += '<li><strong>Tanggal DOC Masuk</strong><span>' + getValue('tanggal_chick_in_pedaging_formatted') + '</span></li>';
                    detailHtml += '<li><strong>Tanggal Kunjungan</strong><span>' + getValue('waktu_kunjungan_formatted') + '</span></li>';
                    detailHtml += '<li><strong>Umur</strong><span>' + getValue('umur_pedaging') + ' hari</span></li>';
                    
                    detailHtml += '<li><strong>Berat Badan</strong><span>' + 
                                    getValue('pencapaian_berat_pedaging') + ' gr ' + 
                                    '<strong style="color: #3498db; font-weight:normal;">Std (' + getValue('berat_badan_strain') + ' gr)</strong>' + 
                                  '</span></li>';

                    detailHtml += '<li><strong>Keseragaman</strong><span>' + 
                                    getValue('keseragaman_pedaging') + ' % ' + 
                                    '<strong style="color: #3498db; font-weight:normal;">Std (' + getValue('keseragaman_strain') + ' %)</strong>' + 
                                  '</span></li>';

                    detailHtml += '<li><strong>Feed Intake</strong><span>' + 
                                    getValue('intake_pedaging') + ' gr ' + 
                                    '<strong style="color: #3498db; font-weight:normal;">Std (' + getValue('konsumsi_pakan_kulmulatif_strain') + ' - ' + getValue('konsumsi_pakan_strain') + ' gr)</strong>' + 
                                  '</span></li>';

                    detailHtml += '<li><strong>Deplesi</strong><span>' + 
                                    getValue('deplesi_pedaging') + ' % ' + 
                                    '<strong style="color: #3498db; font-weight:normal;">Std (' + getValue('kematian_kulmulatif_strain') + ' %)</strong>' + 
                                  '</span></li>';

                    detailHtml += '</ul>'; 

                    let catatan = getValue('catatan_pedaging');
                    if (catatan && catatan !== '-') {
                        detailHtml += '<div class="catatan-section">'; 
                        detailHtml += '<strong>Catatan Kunjungan</strong>';
                        detailHtml += '<p>' + catatan + '</p>';
                        detailHtml += '</div>';
                    }         
                    modalBody.innerHTML = detailHtml; 
                } else {
                    modalBody.innerHTML = '<p class="text-danger p-3">Gagal memuat detail: ' + (data.message || 'Data tidak ditemukan') + '</p>';
                }
            })
            .catch(error => {
                console.error('Fetch Error (Detail):', error);
                modalBody.innerHTML = '<p class="text-danger p-3">Terjadi kesalahan saat menghubungi server.</p>';
            });
        } 

    }); 
}); 
</script>
