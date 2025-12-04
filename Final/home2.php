<?php 
// 1. Definisikan path gambar menggunakan base_url() dan lokasi file Anda
$image_path = base_url('assets/image/dashboard_bg.jpg'); 
?>

<div class="container-fluid" style="
    background-image: url('<?= $image_path ?>'); 
    background-size: cover; 
    background-repeat: no-repeat; 
    background-position: center center; 
    min-height: 100vh; /* Opsional: Agar background terlihat penuh bahkan jika konten sedikit */
">

<div class="container-fluid">
    <section class="content-header">
        <h1 class="font-weight-bold">Technical Information System</h1>
    </section>

    <section class="content">
        <div class="justify-content-center">

            <?php if (isset($user['group_user']) && $user['group_user'] != 'sales') : ?>
                <div class="col-8 col-sm-6 col-md-4 mb-3">
                        <div class="card-body text-center d-flex flex-column">
                            <a href="<?= site_url('Dashboard_new/visual_data_kunjungan') ?>" class="btn btn-info mt-auto">Data Kunjungan</a>
                        </div>
                </div>
            <?php endif; ?>

                <div class="col-8 col-sm-6 col-md-4 mb-3">
                    <div class="card-body text-center d-flex flex-column">
                        <a href="<?= site_url('Dashboard_new/visual_kasus_penyakit') ?>" class="btn btn-info mt-auto">Kasus Penyakit</a>
                    </div>
                </div>

                <div class="col-8 col-sm-6 col-md-4 mb-3">
                    <div class="card-body text-center d-flex flex-column">
                        <a href="<?= site_url('Dashboard_new/visual_kandang_kosong') ?>" class="btn btn-info text-white mt-auto">Kandang Kosong</a>
                    </div>
                </div>

                <div class="col-8 col-sm-6 col-md-4 mb-3">
                    <div class="card-body text-center d-flex flex-column">
                        <a href="<?= site_url('Dashboard_new/visual_kondisi_lingkungan') ?>" class="btn btn-info text-white mt-auto">Kondisi Lingkungan</a>
                    </div>
                </div>
            
                <div class="col-8 col-sm-6 col-md-4 mb-3">
                    <div class="card-body text-center d-flex flex-column">
                        <a href="<?= base_url('Dashboard_new/visual_harga/telur') ?>" class="btn btn-info mt-auto">Harga Produk</a>
                    </div>
                </div>

                <div class="col-8 col-sm-6 col-md-4 mb-3">
                    <div class="card-body text-center d-flex flex-column">
                        <a href="<?= base_url('Dashboard_new/visual_harga_compare') ?>" class="btn btn-info mt-auto">HPP</a>
                    </div>
                </div>

                <div class="col-8 col-sm-6 col-md-4 mb-3">
                    <div class="card-body text-center d-flex flex-column">
                        <a href="<?= site_url('Dashboard_new/visual_vip_farms') ?>" class="btn btn-info text-white mt-auto">Farm VIP Grower</a>
                    </div>
                </div>

            <!-- <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center d-flex flex-column">
                        <h5 class="card-title font-weight-bold mb-3">Data CRM Broiler</h5>
                        <i class="fas fa-database fa-3x text-secondary"></i>
                        <a href="<?= site_url('Dashboard_new/visual_data_crm') ?>" class="btn btn-secondary mt-auto">Buka Laporan</a>
                    </div>
                </div>
            </div> -->

        </div>
    </section>
</div>
