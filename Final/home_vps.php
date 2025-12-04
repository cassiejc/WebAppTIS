<div class="container-fluid">
    <section class="content-header">
        <h1 class="font-weight-bold">Dashboard</h1>
    </section>

    <section class="content">
        <div class="row">

            <?php if (isset($user['group_user']) && $user['group_user'] != 'sales') : ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center d-flex flex-column">
                            <h5 class="card-title font-weight-bold mb-3">Data Kunjungan</h5>
                            <i class="fas fa-chart-line fa-3x text-purple"></i>
                            <a href="<?= site_url('Dashboard_new/visual_data_kunjungan') ?>" class="btn mt-auto" style="background-color: #6f42c1; color: white;">Buka Laporan</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center d-flex flex-column">
                        <h5 class="card-title font-weight-bold mb-3">Kasus Penyakit</h5>
                        <i class="fas fa-first-aid fa-3x text-danger"></i>
                        <a href="<?= site_url('Dashboard_new/visual_kasus_penyakit') ?>" class="btn btn-danger mt-auto">Buka Laporan</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center d-flex flex-column">
                        <h5 class="card-title font-weight-bold mb-3">Kandang Kosong</h5>
                        <i class="fas fa-door-open fa-3x text-info"></i>
                        <a href="<?= site_url('Dashboard_new/visual_kandang_kosong') ?>" class="btn btn-info text-white mt-auto">Buka Laporan</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center d-flex flex-column">
                        <h5 class="card-title font-weight-bold mb-3">Kondisi Lingkungan</h5>
                        <i class="fas fa-bug fa-3x text-warning"></i>
                        <a href="<?= site_url('Dashboard_new/visual_kondisi_lingkungan') ?>" class="btn btn-warning text-white mt-auto">Buka Laporan</a>
                    </div>
                </div>
            </div>

            <!-- <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center d-flex flex-column">
                        <h5 class="card-title font-weight-bold mb-3">Laporan Harga</h5>
                        <i class="fas fa-chart-area fa-3x text-success"></i>
                        <a href="<?= site_url('Dashboard_new/visual_harga_gabungan') ?>" class="btn btn-success mt-auto">Buka Laporan</a>
                    </div>
                </div>
            </div> -->

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center d-flex flex-column">
                        <h5 class="card-title font-weight-bold mb-3">Harga Produk</h5>
                        <i class="fas fa-dollar-sign fa-3x text-success"></i>
                        <a href="<?= site_url('Dashboard_new/visual_harga/telur') ?>" class="btn btn-success mt-auto">Buka Laporan</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center d-flex flex-column">
                        <h5 class="card-title font-weight-bold mb-3">HPP</h5>
                        <i class="fas fa-exchange-alt fa-3x text-primary"></i>
                        <a href="<?= site_url('Dashboard_new/visual_harga_compare') ?>" class="btn btn-primary mt-auto">Buka Laporan</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center d-flex flex-column">
                        <h5 class="card-title font-weight-bold mb-3">Farm VIP Grower</h5>
                        <i class="fas fa-star fa-3x text-warning"></i>
                        <a href="<?= site_url('Dashboard_new/visual_vip_farms') ?>" class="btn btn-warning text-white mt-auto">Buka Laporan</a>
                    </div>
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
