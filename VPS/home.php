<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <h1 class="font-weight-bold">Dashboard</h1>
        </div>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-lg-4 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line fa-3x mb-3 text-purple"></i>
                        <h5 class="card-title font-weight-bold">Data Kunjungan</h5>
                        <p class="card-text">Menampilkan semua laporan performa visiting surveyor.</p>
                        <a href="<?= base_url('Dashboard_new/visual_data_kunjungan') ?>" class="btn" style="background-color: #6f42c1; color: white;">Buka Laporan</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-first-aid fa-3x mb-3 text-danger"></i>
                        <h5 class="card-title font-weight-bold">Kasus Penyakit</h5>
                        <p class="card-text">Menampilkan laporan kasus penyakit di lapangan.</p>
                        <a href="<?= base_url('Dashboard_new/visual_kasus_penyakit') ?>" class="btn btn-danger">Buka Laporan</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-dollar-sign fa-3x mb-3 text-success"></i>
                        <h5 class="card-title font-weight-bold">Laporan Harga Komoditas</h5>
                        <p class="card-text">Menampilkan grafik harga.</p>
                        <a href="<?= base_url('Dashboard_new/visual_harga/telur') ?>" class="btn btn-success">Buka Laporan</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-exchange-alt fa-3x mb-3 text-primary"></i> 
                        <h5 class="card-title font-weight-bold">Perbandingan Harga</h5>
                        <p class="card-text">Membandingkan grafik bulanan antara dua komoditas.</p>
                        
                        <a href="<?= base_url('Dashboard_new/visual_harga_compare') ?>" class="btn btn-primary">Buka Laporan</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-door-open fa-3x mb-3 text-info"></i>
                        <h5 class="card-title font-weight-bold">Kandang Kosong</h5>
                        <p class="card-text">Menampilkan laporan persentase kandang kosong per bulan.</p>
                        <a href="<?= base_url('Dashboard_new/visual_kandang_kosong') ?>" class="btn btn-info text-white">Buka Laporan</a>
                    </div>
                </div>
            </div>   
            
            <div class="col-lg-4 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-bug fa-3x mb-3 text-warning"></i> 
                        <h5 class="card-title font-weight-bold">Kondisi Lingkungan</h5>
                        <p class="card-text">Menampilkan laporan kondisi lalat dan kotoran (Layer).</p>
                        <a href="<?= base_url('Dashboard_new/visual_kondisi_lingkungan') ?>" class="btn btn-warning text-white">Buka Laporan</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-star fa-3x mb-3 text-warning"></i> <h5 class="card-title font-weight-bold">Farm VIP Grower</h5>
                        <p class="card-text">Menampilkan daftar farm VIP dengan tipe ternak Grower.</p>
                        <a href="<?= base_url('Dashboard_new/visual_vip_farms') ?>" class="btn btn-warning text-white">Buka Laporan</a>
                    </div>
                </div>
            </div>
    </div> </section>
    </div>

        </div>
    </section>
</div>
