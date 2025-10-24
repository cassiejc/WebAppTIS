<style>
    .farm-name-link {
    color: inherit; /* Ini membuat warnanya jadi hitam (mengikuti teks tabel) */
    text-decoration: none;
    cursor: pointer; /* Kursor 'tangan' tetap ada agar tahu bisa diklik */
    font-weight: normal; /* Ini menghilangkan efek bold */
    }
    .farm-name-link:hover {
        text-decoration: none; /* Menghilangkan garis bawah saat disentuh mouse */
    }
    .detail-row td {
        background-color: #f8f9fa;
        padding: 15px !important;
    }
    .visit-history-list {
        margin-bottom: 0;
        padding-left: 20px;
    }
    /* ... (CSS .farm-name-link Anda yang sudah ada) ... */

    /* BARU: CSS untuk link tanggal kunjungan */
    .visit-history-list .visit-date-link {
        cursor: pointer;
        color: inherit; /* Biru agar terlihat bisa diklik */
        text-decoration: none;
        font-weight: normal;
    }
    .visit-history-list .visit-date-link:hover {
        text-decoration: none;
    }

    /* BARU: CSS untuk Modal Pop-up */
    .visit-detail-modal {
        display: none; 
        position: fixed; 
        z-index: 1050; 
        left: 0;
        top: 0;
        width: 100%; 
        height: 100%; 
        overflow: auto; 
        background-color: rgba(0,0,0,0.4); 
        padding-top: 60px;
    }
    .visit-detail-modal-content {
        background-color: #fefefe;
        margin: 5% auto; 
        width: 90%; 
        max-width: 650px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .visit-detail-modal-content .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .visit-detail-close {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
        color: #000;
        text-shadow: 0 1px 0 #fff;
        opacity: .5;
        background-color: transparent;
        border: 0;
    }
    .visit-detail-close:hover {
        opacity: .75;
    }

    /* BARU: CSS untuk daftar detail di dalam modal */
    .detail-list {
        list-style: none;
        padding-left: 0;
        margin-bottom: 0;
    }
    .detail-list li {
        padding: 0.6rem 0;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
    }
    .detail-list li:last-child {
        border-bottom: none;
    }
    .detail-list strong {
        color: #333;
        min-width: 160px; /* Lebar label */
    }
    .detail-list span {
        text-align: right;
        color: #555;
        word-break: break-word;
    }
</style>

<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <h1 class="font-weight-bold"><?= isset($title) ? $title : 'Laporan Farm VIP' ?></h1>
        </div>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Daftar Farm VIP (Grower)</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($vip_grower_farms)): ?>
                            <div style="overflow-x: auto;">
                                <table class="table table-bordered table-striped table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 50px;">No.</th>
                                            <th>Nama Farm (Klik untuk detail)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="vip-farm-table-body" 
                                        data-url="<?= base_url('index.php/Dashboard_new/get_visit_history_for_farm') ?>"
                                        data-detail-url="<?= base_url('index.php/Dashboard_new/get_grower_visit_details') ?>">
                                        <?php $i = 1; foreach ($vip_grower_farms as $farm): ?>
                                            <tr>
                                                <td><?= $i++; ?></td>
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
                            <p class="text-muted">
                                Tidak ada data farm VIP Grower yang ditemukan untuk filter Anda.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<div id="visitDetailModal" class="visit-detail-modal">
    <div class="visit-detail-modal-content card">
        <div class="card-header">
            <h4 class="card-title mb-0" id="modalTitle">Detail Kunjungan</h4>
            <button type="button" class="close visit-detail-close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="card-body" id="modalBody">
            </div>
    </div>
</div>


<script>
// Variabel CSRF tetap sama
var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';

// Ini adalah pengganti $(document).ready()
document.addEventListener('DOMContentLoaded', function() {
    
    var tableBody = document.getElementById('vip-farm-table-body');
    if (!tableBody) return; // Keluar jika tabel tidak ditemukan

    var historyAjaxUrl = tableBody.dataset.url;
    var detailAjaxUrl = tableBody.dataset.detailUrl; // BARU: Ambil URL detail
    
    // --- Referensi ke Modal ---
    var modal = document.getElementById('visitDetailModal');
    var modalBody = document.getElementById('modalBody');
    var modalTitle = document.getElementById('modalTitle');
    var closeBtn = document.querySelector('.visit-detail-close');

    // --- Fungsi untuk menutup modal ---
    function closeModal() {
        modal.style.display = "none";
        modalBody.innerHTML = ''; // Kosongkan isi
    }

    // --- Event listener untuk tombol close & klik di luar modal ---
    closeBtn.onclick = closeModal;
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }

    // --- Event listener utama untuk semua klik di tabel ---
    tableBody.addEventListener('click', function(e) {
        
        // =======================================================
        // HANDLER 1: Klik NAMA FARM (untuk memuat daftar kunjungan)
        // =======================================================
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
                '<i class="fas fa-spinner fa-spin"></i>&nbsp; Memuat riwayat kunjungan...' +
                '</div>' +
                '</td>';
            
            clickedRow.parentNode.insertBefore(loadingRow, clickedRow.nextElementSibling);

            var formData = new FormData();
            formData.append('farm_name', farmName);
            formData.append(csrfName, csrfHash);

            // AJAX 1: Ambil daftar kunjungan
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
                        contentHtml = '<h5>Riwayat Kunjungan (klik tanggal untuk detail):</h5><ul class="visit-history-list">';
                        
                        // --- PERUBAHAN DI SINI ---
                        // Kita buat <li> dengan data-atribute untuk AJAX 2
                        data.history.forEach(function(visit) {
                            contentHtml += 
                                '<li class="visit-date-link" ' +
                                'data-farm-name="' + farmName + '" ' + 
                                'data-visit-id="' + visit.visit_id + '">' + 
                                visit.waktu_kunjungan_formatted + 
                                '</li>';
                        });
                        // --- AKHIR PERUBAHAN ---

                        contentHtml += '</ul>';
                    } else {
                        contentHtml = '<p class="text-muted mb-0">Tidak ada riwayat kunjungan yang ditemukan.</p>';
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
        } // Akhir dari handler klik .farm-name-link

        
        // =======================================================
        // HANDLER 2 (BARU): Klik TANGGAL KUNJUNGAN (untuk memuat detail)
        // =======================================================
        if (e.target && e.target.classList.contains('visit-date-link')) {
            e.preventDefault();
            
            var clickedDateLink = e.target;
            var farmName = clickedDateLink.dataset.farmName;
            var visitId = clickedDateLink.dataset.visitId;

            // Tampilkan modal dengan status loading
            modalTitle.innerText = 'Detail Kunjungan Farm: ' + farmName;
            modalBody.innerHTML = '<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i>&nbsp; Memuat detail...</div>';
            modal.style.display = "block";

            // Siapkan data untuk AJAX 2
            var formData = new FormData();
            formData.append('farm_name', farmName);
            formData.append('visit_id', visitId);
            formData.append(csrfName, csrfHash); 

            // AJAX 2: Ambil detail kunjungan
            fetch(detailAjaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.new_csrf_hash) {
                    csrfHash = data.new_csrf_hash;
                }

                // ... (di dalam .then(data => { ... )

                    if (data.status === 'success' && data.details) {
                        var details = data.details;
                        
                        // --- MODIFIKASI DI SINI ---
                        // Daftar label sesuai permintaan Anda
                        var labels = {
                            nama_farm: 'Nama Farm',
                            efektif_terisi_pedaging: 'Populasi',
                            strain_pedaging: 'Strain DOC',
                            tanggal_chick_in_pedaging_formatted: 'Tanggal DOC Masuk',
                            waktu_kunjungan_formatted: 'Tanggal Kunjungan',
                            umur_pedaging: 'Umur',
                            
                            /* --- Data Aktual --- */
                            /* Saya tambahkan (Aktual) agar lebih jelas perbedaannya */
                            pencapaian_berat_pedaging: 'Berat Badan (Aktual)',
                            keseragaman_pedaging: 'Keseragaman (Aktual)',
                            intake_pedaging: 'Feed Intake (Aktual)',
                            deplesi_pedaging: 'Deplesi (Aktual)',

                            /* --- BARU: Data Standard Strain --- */
                            /* Pastikan key-nya (sisi kiri) sama dengan alias di query Model */
                            berat_badan_strain: 'Std. Berat Badan',
                            keseragaman_strain: 'Std. Keseragaman',
                            konsumsi_pakan_strain: 'Std. Feed Intake',
                            konsumsi_pakan_kulmulatif_strain: 'Std. FI Kumulatif',
                            kematian_kulmulatif_strain: 'Std. Deplesi Kumulatif'
                        };
                        // --- AKHIR MODIFIKASI ---

                        // Buat HTML untuk ditampilkan di modal
                        var detailHtml = '<ul class="detail-list">';
                        for (var key in labels) {
                            detailHtml += '<li>' +
                                        '<strong>' + labels[key] + '</strong>' +
                                        '<span>' + (details[key] !== null ? details[key] : '-') + '</span>' +
                                        '</li>';
                        }
                        detailHtml += '</ul>';
                        modalBody.innerHTML = detailHtml;

                    } else {
                    // ... (sisa kode)
                    modalBody.innerHTML = '<p class="text-danger p-3">Gagal memuat detail: ' + (data.message || 'Data tidak ditemukan') + '</p>';
                }
            })
            .catch(error => {
                console.error('Fetch Error (Detail):', error);
                modalBody.innerHTML = '<p class="text-danger p-3">Terjadi kesalahan saat menghubungi server.</p>';
            });
        } // Akhir dari handler klik .visit-date-link

    }); // Akhir dari event listener tableBody
});
</script>
