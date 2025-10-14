<!DOCTYPE html>
<html>
<head>
    <title><?= isset($page_title) ? $page_title : 'Daftar Data' ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .table-container { margin: 20px; }
        .page-title { margin: 20px; }
        .btn-edit { background-color: #ffc107; border-color: #ffc107; color: #000; }
        .btn-edit:hover { background-color: #e0a800; border-color: #d39e00; }
        .btn-delete { background-color: #dc3545; border-color: #dc3545; color: #fff; }
        .btn-delete:hover { background-color: #c82333; border-color: #bd2130; }
        .btn-calculate { background-color: #0dcaf0; border-color: #0dcaf0; color: #000; }
        .btn-calculate:hover { background-color: #0ba9c9; border-color: #0a9cb8; }
        .action-buttons { white-space: nowrap; }
        .table th { background-color: #f8f9fa; font-weight: 600; }
        .search-box { max-width: 300px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h2 class="page-title mb-4"><?= isset($page_title) ? $page_title : 'Daftar Data' ?></h2>

        <div class="table-container">
            <div class="row mb-3">
                <div class="col-md-6">
                    <?php
                        // Logika untuk menampilkan tombol "Tambah"
                        $special_add_routes = ['Target', 'Harga'];
                        $no_add_button = ['User', 'Kontributor Harga'];
                        $add_url = '';

                        if (!in_array($kategori_selected, $no_add_button)) {
                            if (in_array($kategori_selected, $special_add_routes)) {
                                $url_segment = strtolower(str_replace(' ', '_', $kategori_selected));
                                $add_url = site_url('Admin_Controller/add_' . $url_segment);
                            } else {
                                $url_segment = strtolower(str_replace(' ', '_', $kategori_selected));
                                $add_url = site_url('Tambah_Data_Baru_Master_Controller/' . $url_segment);
                            }
                        }
                        if (!empty($add_url)):
                    ?>
                    <a href="<?= $add_url ?>" class="btn btn-success mb-3">
                        <i class="fas fa-plus"></i> Tambah <?= htmlspecialchars($kategori_selected) ?>
                    </a>
                    <?php endif; ?>

                    <div class="search-box">
                        <input type="text" class="form-control" id="searchInput" placeholder="Cari data...">
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <a href="<?= site_url('Dashboard_new/index') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

            <?php
                // Mapping Kategori ke URL
                $kategori_to_method = [
                    'Sub Agen' => 'subagen', 'Agen' => 'agen', 'Peternak' => 'peternak',
                    'Kemitraan' => 'kemitraan', 'Farm' => 'farm', 'Lokasi Baru' => 'lokasibaru',
                    'Pakan' => 'pakan', 'Strain' => 'strain', 'Target' => 'target', 'Harga' => 'harga',
                    'User' => 'user', 'Kontributor Harga' => 'kontributorharga'
                ];
                $method_name = $kategori_to_method[$kategori_selected] ?? strtolower(str_replace(' ', '', $kategori_selected));
            ?>

            <?php if (!empty($data_list)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="dataTable">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <?php foreach ($table_headers as $header): ?>
                                    <th><?= $header ?></th>
                                <?php endforeach; ?>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php foreach ($data_list as $data): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <?php foreach ($table_fields as $field): ?>
                                        <td><?= htmlspecialchars($data[$field] ?? '-') ?></td>
                                    <?php endforeach; ?>
                                    <td class="action-buttons">
                                        <a href="<?= site_url('Admin_Controller/' . $method_name . '/' . $data[$primary_key]) ?>" 
                                           class="btn btn-edit btn-sm me-1" 
                                           title="Edit Data">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>

                                        <?php if ($kategori_selected == 'Harga' && isset($data['nama_harga']) && $data['nama_harga'] == 'Average Harga Telur Layer'): ?>
                                            <button type="button" 
                                                    class="btn btn-calculate btn-sm me-1" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#calculateModal"
                                                    data-id-harga="<?= $data[$primary_key] ?>"
                                                    title="Hitung Ulang Harga Rata-Rata Telur">
                                                <i class="fas fa-calculator"></i> Hitung Ulang
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button type="button" 
                                                class="btn btn-delete btn-sm" 
                                                onclick="confirmDelete('<?= $data[$primary_key] ?>', '<?= htmlspecialchars($data[$display_field] ?? 'data ini') ?>')"
                                                title="Hapus Data">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i>
                    <p class="mb-0">Tidak ada data yang tersedia untuk kategori <?= $kategori_selected ?>.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal fade" id="calculateModal" tabindex="-1" aria-labelledby="calculateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="calculateModalLabel">Hitung Ulang Harga Rata-Rata</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="calculateForm" method="post">
                    <div class="modal-body">
                        <p>Pilih tanggal untuk menghitung harga rata-rata telur layer.</p>
                        <div class="mb-3">
                            <label for="calculation_date" class="form-label">Tanggal Kalkulasi</label>
                            <input type="date" class="form-control" id="calculation_date" name="calculation_date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Proses</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus data <strong id="itemName"></strong>?</p>
                    <p class="text-muted small">Data yang sudah dihapus tidak dapat dikembalikan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Search functionality
            document.getElementById('searchInput').addEventListener('keyup', function() {
                const searchValue = this.value.toLowerCase();
                const tableRows = document.querySelectorAll('#dataTable tbody tr');

                tableRows.forEach(function(row) {
                    const rowText = row.textContent.toLowerCase();
                    row.style.display = rowText.includes(searchValue) ? '' : 'none';
                });
            });

            // Delete confirmation
            let deleteId = null;
            const deleteModalEl = document.getElementById('deleteModal');
            const deleteModal = new bootstrap.Modal(deleteModalEl);

            // Kita harus membuat fungsi confirmDelete global agar bisa diakses dari atribut onclick
            window.confirmDelete = function(id, name) {
                deleteId = id;
                document.getElementById('itemName').textContent = name;
                deleteModal.show();
            }

            document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
                if (deleteId) {
                    window.location.href = `<?= site_url('Admin_Controller/delete_data/' . $method_name . '/') ?>${deleteId}`;
                }
            });

            // KODE YANG DIPERBAIKI ADA DI SINI
            const calculateModalEl = document.getElementById('calculateModal');
            if (calculateModalEl) {
                calculateModalEl.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const idHarga = button.getAttribute('data-id-harga');
                    
                    const form = document.getElementById('calculateForm');
                    form.action = `<?= site_url('Admin_Controller/hitung_ulang_harga_telur_layer/') ?>${idHarga}`;
                    
                    const dateInput = document.getElementById('calculation_date');
                    // Menggunakan format YYYY-MM-DD untuk kompatibilitas
                    const today = new Date();
                    const year = today.getFullYear();
                    const month = String(today.getMonth() + 1).padStart(2, '0');
                    const day = String(today.getDate()).padStart(2, '0');
                    dateInput.value = `${year}-${month}-${day}`;
                });
            }

            // Auto hide alerts
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert-success, .alert-info');
                alerts.forEach(function(alert) {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                });
            }, 5000);
        });
    </script>
</body>
</html>
