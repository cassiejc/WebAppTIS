<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_new extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        $this->load->helper('form');
        $this->load->helper('url');
        $this->load->library('form_validation');
        $this->load->model('M_Dash', 'dash');
        $this->load->model('M_Visual', 'visual'); // Load model M_Visual

        if(!$this->session->has_userdata('token')){
            redirect('Home'); 
        }
    }

    /**
     * Method index() sekarang HANYA menampilkan halaman MENU.
     * URL: .../Dashboard_new/index
     */
    public function index()
    {
        $token = $this->session->userdata('token');
        $data['user'] = $this->dash->getUserInfo($token)->row_array();
        $data["title"] = "CP APPS";

        // Hanya load view yang berisi tombol-tombol menu
        $this->load->view('templates/dash_h', $data);
        $this->load->view('page_view/home', $data); // Pastikan home.php berisi menu
        $this->load->view('templates/dash_f', $data);
    }

    /**
     * Method BARU untuk menampilkan SEMUA LAPORAN di satu halaman.
     * URL: .../Dashboard_new/visual_data_kunjungan
     */
    public function visual_data_kunjungan()
    {
        $token = $this->session->userdata('token');
        $data['user'] = $this->dash->getUserInfo($token)->row_array();
        $data["title"] = "Laporan Gabungan";

        // ========================================================================
        // LOGIKA FILTER BERDASARKAN PERAN (ROLE) - DIPERBARUI
        // ========================================================================
        $user_id_filter = null; // Default untuk surveyor
        $area_id_filter = null; // Default untuk koordinator

        if (isset($data['user']['group_user'])) {
            $group = $data['user']['group_user'];
            
            if ($group === 'surveyor') {
                // Jika user adalah surveyor, filter hanya untuk data miliknya
                $user_id_filter = $data['user']['id_user'];
                
            } elseif ($group === 'koordinator') {
                // Jika user adalah koordinator, filter hanya untuk areanya
                // Pastikan data 'master_area_id' ada di $data['user']
                if (isset($data['user']['master_area_id'])) {
                    $area_id_filter = $data['user']['master_area_id'];
                }
            }
            // Jika admin/grup lain, kedua filter tetap null (melihat semua)
        }
        // ========================================================================

        // Logika filter bulan dan tahun (tetap sama)
        if ($this->input->post()) {
            $selected_month = $this->input->post('bulan');
            $selected_year = $this->input->post('tahun');
        } else {
            $selected_month = date('m');
            $selected_year = date('Y');
        }

        // --- PANGGIL FUNGSI MODEL (DIPERBARUI) ---
        // Sekarang kita teruskan $area_id_filter ke SEMUA fungsi
        
        $data['performance_data'] = $this->visual->get_surveyor_performance($selected_month, $selected_year, $user_id_filter, $area_id_filter);
        
        // get_area_performance sudah menerima $data['user'], mungkin sudah benar
        // Tapi jika TIDAK, Anda juga harus memodifikasinya
        $data['area_performance_data'] = $this->visual->get_area_performance($selected_month, $selected_year, $data['user']);

        // --- (PERUBAHAN DIMULAI DI SINI) ---

        // 1. Panggil SEMUA fungsi model untuk pie chart
        $visit_breakdown_raw = $this->visual->get_visit_breakdown($selected_month, $selected_year, $user_id_filter, $area_id_filter); 
        $sample_count = $this->visual->get_sample_count_by_month($selected_month, $selected_year, $user_id_filter, $area_id_filter);
        $seminar_count = $this->visual->get_seminar_count_by_month($selected_month, $selected_year, $user_id_filter, $area_id_filter);
        $new_customer_count = $this->visual->get_new_customer_count_by_month($selected_month, $selected_year, $user_id_filter, $area_id_filter);
        
        // Panggil fungsi model untuk tabel detail (tidak berubah)
        $data['visit_details_table'] = $this->visual->get_all_visit_details($selected_month, $selected_year, $user_id_filter, $area_id_filter);
        
        // 2. Gabungkan HANYA data yang ingin Anda kelompokkan
        $data_to_group = $visit_breakdown_raw; // Mulai dengan data utama
        if ($sample_count > 0) $data_to_group[] = ['kategori' => 'Sample', 'jumlah_visit' => $sample_count];

        // 3. Definisikan pemetaan. (Seminar & New Customers DIHAPUS dari map ini)
        $category_map = [
            // Grup 1: Agen/Subagen/Lainnya 
            'Agen' => 'Agen/Subagen/Lainnya',
            'Subagen' => 'Agen/Subagen/Lainnya',
            'Kantor' => 'Agen/Subagen/Lainnya',
            
            // Grup 2: Others
            'Arap' => 'Others',
            'Bebek Pedaging' => 'Others',
            'Bebek Petelur' => 'Others',
            'Puyuh' => 'Others',
            'Kemitraan' => 'Others',
            'Lainnya' => 'Others',
            'Sample' => 'Others', // <-- Sample akan masuk ke 'Others'

            // Grup 3: Demoplot DOC
            'Grower' => 'Demoplot DOC'
        ];

        $grouped_totals = [];
        $processed_breakdown = []; // Ini akan jadi data final untuk pie chart

        // 4. Loop HANYA data yang perlu dikelompokkan ($data_to_group)
        foreach ($data_to_group as $item) { 
            $raw_kategori = $item['kategori'];
            $jumlah = (int)$item['jumlah_visit'];

            // Cek apakah kategori ini ada di peta pengelompokan kita
            if (isset($category_map[$raw_kategori])) {
                
                $display_kategori = $category_map[$raw_kategori];
                
                if (!isset($grouped_totals[$display_kategori])) {
                    $grouped_totals[$display_kategori] = 0;
                }
                $grouped_totals[$display_kategori] += $jumlah;

            } else {
                // Jika tidak ada di peta, masukkan apa adanya (misal: 'Layer')
                $processed_breakdown[] = $item; 
            }
        }

        // 5. Gabungkan hasil total grup ke array utama
        foreach ($grouped_totals as $kategori => $jumlah) {
            if ($jumlah > 0) {
                $processed_breakdown[] = [
                    'kategori' => $kategori,
                    'jumlah_visit' => $jumlah
                ];
            }
        }

        // 6. SEKARANG baru tambahkan data yang tidak digabung (Seminar & New Customers)
        $combined_data = $processed_breakdown; // Ambil data yg sudah dikelompokkan
        if ($seminar_count > 0) $combined_data[] = ['kategori' => 'Seminar', 'jumlah_visit' => $seminar_count];
        if ($new_customer_count > 0) $combined_data[] = ['kategori' => 'New Customers', 'jumlah_visit' => $new_customer_count];

        // 7. Hitung persentase dari data gabungan FINAL
        $grand_total = array_sum(array_column($combined_data, 'jumlah_visit'));
        $final_breakdown = [];
        if ($grand_total > 0) {
            foreach ($combined_data as $item) {
                $final_breakdown[] = [
                    'kategori' => $item['kategori'],
                    'persentase' => ($item['jumlah_visit'] / $grand_total) * 100
                ];
            }
        }
        
        // --- (SISA KODE SAMA SEPERTI SEBELUMNYA) ---
        usort($final_breakdown, function($a, $b) { return $b['persentase'] <=> $a['persentase']; });
        $data['visit_breakdown_data'] = $final_breakdown;
        $breakdown_labels = array_column($final_breakdown, 'kategori');
        $breakdown_values = array_column($final_breakdown, 'persentase');
        $data['breakdown_chart_labels'] = json_encode($breakdown_labels);
        $data['breakdown_chart_values'] = json_encode($breakdown_values);
        
        $data['selected_month'] = $selected_month;
        $data['selected_year'] = $selected_year;

        $this->load->view('templates/dash_h', $data);
        $this->load->view('visual_data_kunjungan', $data); 
        $this->load->view('templates/dash_f', $data);
    }

// Tambahkan method baru ini di dalam class Dashboard_new

    /**
     * Method BARU untuk halaman Laporan Kasus Penyakit
     * URL: .../Dashboard_new/visual_kasus_penyakit
     */
    /**
     * Menampilkan halaman LAPORAN KASUS PENYAKIT (Chart & Pivot)
     */
    public function visual_kasus_penyakit()
    {
        $data['user'] = $this->dash->getUserInfo($this->session->userdata('token'))->row_array();
        $data["title"] = "Laporan Kasus Penyakit";

        // ========================================================================
        // LOGIKA FILTER BERDASARKAN PERAN (ROLE)
        // ========================================================================
        $user_id_filter = null; // Defaultnya bisa melihat semua data
        if (isset($data['user']['group_user']) && $data['user']['group_user'] === 'surveyor') {
            // Jika user adalah surveyor, filter hanya untuk data miliknya
            $user_id_filter = $data['user']['id_user'];
        }
        // ========================================================================

        $selected_year = $this->input->post('tahun') ?: date('Y');
        
        // Panggil fungsi model dengan menambahkan $user_id_filter
        $raw_stacked_data = $this->visual->get_kasus_breakdown_stacked($selected_year, $user_id_filter);
        
        // --- Sisa fungsi (logika pivot chart) tidak perlu diubah ---
        $labels = []; $kategori_chart = []; $pivot_chart_data = [];
        foreach ($raw_stacked_data as $row) {
            $bulan = $row['bulan_tahun'];
            $kat = $row['kategori_kasus'];
            $jumlah = (int)$row['jumlah'];
            if (!in_array($bulan, $labels)) $labels[] = $bulan;
            if (!in_array($kat, $kategori_chart)) $kategori_chart[] = $kat;
            $pivot_chart_data[$kat][$bulan] = $jumlah;
        }
        $datasets = [];
        $colors = ['#28a745', '#ffc107', '#6f42c1', '#dc3545', '#fd7e14', '#17a2b8', '#6c757d'];
        $color_index = 0;
        foreach ($kategori_chart as $kat) {
            $dataset = ['label' => $kat, 'data' => [], 'backgroundColor' => $colors[$color_index % count($colors)]];
            foreach ($labels as $bulan) {
                $dataset['data'][] = $pivot_chart_data[$kat][$bulan] ?? 0;
            }
            $datasets[] = $dataset;
            $color_index++;
        }
        $data['chart_labels'] = json_encode($labels);
        $data['chart_datasets'] = json_encode($datasets);

        // Panggil fungsi model dengan menambahkan $user_id_filter
        $raw_pivot_data = $this->visual->get_kasus_pivot_by_area($selected_year, $user_id_filter);
        
        // --- Sisa fungsi (logika pivot tabel) tidak perlu diubah ---
        $pivot_table_data = [];
        $categories_table = [];
        foreach ($raw_pivot_data as $row) {
            $area = $row['nama_area'];
            $kategori = $row['kategori_kasus'];
            $jumlah = (int)$row['jumlah'];
            if (!isset($pivot_table_data[$area])) {
                $pivot_table_data[$area] = ['nama_area' => $area];
            }
            $pivot_table_data[$area][$kategori] = $jumlah;
            if (!in_array($kategori, $categories_table)) {
                $categories_table[] = $kategori;
            }
        }
        sort($categories_table);
        $data['pivot_table_data'] = $pivot_table_data;
        $data['pivot_table_categories'] = $categories_table;
        
        // Panggil fungsi model dengan menambahkan $user_id_filter
        $data['kasus_detail_list'] = $this->visual->get_kasus_detail_list($selected_year, $user_id_filter);

        $data['selected_year'] = $selected_year;

        $this->load->view('templates/dash_h', $data);
        $this->load->view('visual_kasus_penyakit', $data); 
        $this->load->view('templates/dash_f', $data);
    }

    public function visual_harga($jenis_komoditas = 'telur')
    {
        $data['user'] = $this->dash->getUserInfo($this->session->userdata('token'))->row_array();
        $data['jenis_terpilih'] = $jenis_komoditas;

        switch($jenis_komoditas) {
            case 'jagung':
                $data["title"] = "Laporan Harga Jagung";
                $data['harga_hari_ini'] = $this->visual->get_harga_jagung_hari_ini();
                $harga_harian = $this->visual->get_harga_jagung_harian_chart();
                $harga_bulanan = $this->visual->get_harga_jagung_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_jagung_tahunan_chart();
                break;
            case 'katul':
                $data["title"] = "Laporan Harga Katul";
                $data['harga_hari_ini'] = $this->visual->get_harga_katul_hari_ini();
                $harga_harian = $this->visual->get_harga_katul_harian_chart();
                $harga_bulanan = $this->visual->get_harga_katul_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_katul_tahunan_chart();
                break;
            case 'afkir':
                $data["title"] = "Laporan Harga Afkir";
                $data['harga_hari_ini'] = $this->visual->get_harga_afkir_hari_ini();
                $harga_harian = $this->visual->get_harga_afkir_harian_chart();
                $harga_bulanan = $this->visual->get_harga_afkir_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_afkir_tahunan_chart();
                break;
            case 'telur_puyuh':
                $data["title"] = "Laporan Harga Telur Puyuh";
                $data['harga_hari_ini'] = $this->visual->get_harga_telur_puyuh_hari_ini();
                $harga_harian = $this->visual->get_harga_telur_puyuh_harian_chart();
                $harga_bulanan = $this->visual->get_harga_telur_puyuh_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_telur_puyuh_tahunan_chart();
                break;
            case 'telur_bebek':
                $data["title"] = "Laporan Harga Telur Bebek";
                $data['harga_hari_ini'] = $this->visual->get_harga_telur_bebek_hari_ini();
                $harga_harian = $this->visual->get_harga_telur_bebek_harian_chart();
                $harga_bulanan = $this->visual->get_harga_telur_bebek_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_telur_bebek_tahunan_chart();
                break;
            case 'bebek_pedaging':
                $data["title"] = "Laporan Harga Bebek Pedaging";
                $data['harga_hari_ini'] = $this->visual->get_harga_bebek_pedaging_hari_ini();
                $harga_harian = $this->visual->get_harga_bebek_pedaging_harian_chart();
                $harga_bulanan = $this->visual->get_harga_bebek_pedaging_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_bebek_pedaging_tahunan_chart();
                break;
            case 'live_bird':
                $data["title"] = "Laporan Harga Live Bird";
                $data['harga_hari_ini'] = $this->visual->get_harga_live_bird_hari_ini();
                $harga_harian = $this->visual->get_harga_live_bird_harian_chart();
                $harga_bulanan = $this->visual->get_harga_live_bird_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_live_bird_tahunan_chart();
                break;

            case 'pakan_broiler':
                $data["title"] = "Laporan Harga Pakan Komplit Broiler";
                $data['harga_hari_ini'] = $this->visual->get_harga_pakan_broiler_hari_ini();
                $harga_harian = $this->visual->get_harga_pakan_broiler_harian_chart();
                $harga_bulanan = $this->visual->get_harga_pakan_broiler_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_pakan_broiler_tahunan_chart();
                break;
            case 'doc':
              $data["title"] = "Laporan Harga DOC"; // Tambahkan title agar judul halaman berubah
              $data['harga_hari_ini'] = $this->visual->get_harga_doc_hari_ini();
              
              $harga_harian = $this->visual->get_harga_doc_harian_chart();
              $harga_bulanan = $this->visual->get_harga_doc_bulanan_chart();
              $harga_tahunan = $this->visual->get_harga_doc_tahunan_chart();
              break;
            case 'pakan_campuran':
                $data["title"] = "Laporan Harga Pakan Campuran";
                $data['harga_hari_ini'] = $this->visual->get_harga_pakan_campuran_hari_ini();
                $harga_harian = $this->visual->get_harga_pakan_campuran_harian_chart();
                $harga_bulanan = $this->visual->get_harga_pakan_campuran_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_pakan_campuran_tahunan_chart();
                break;
            case 'pakan_komplit_layer':
                $data["title"] = "Laporan Harga Pakan Komplit Layer";
                $data['harga_hari_ini'] = $this->visual->get_harga_pakan_komplit_layer_hari_ini();
                $harga_harian = $this->visual->get_harga_pakan_komplit_layer_harian_chart();
                $harga_bulanan = $this->visual->get_harga_pakan_komplit_layer_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_pakan_komplit_layer_tahunan_chart();
                break;
            case 'konsentrat_layer':
                $data["title"] = "Laporan Average Harga Konsentrat Layer";
                $data['harga_hari_ini'] = $this->visual->get_harga_konsentrat_layer_hari_ini();
                $harga_harian = $this->visual->get_harga_konsentrat_layer_harian_chart();
                $harga_bulanan = $this->visual->get_harga_konsentrat_layer_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_konsentrat_layer_tahunan_chart();
                break;
            case 'hpp_konsentrat_layer':
                $data["title"] = "Laporan Average HPP Konsentrat Layer";
                $data['harga_hari_ini'] = $this->visual->get_hpp_konsentrat_layer_hari_ini();
                $harga_harian = $this->visual->get_hpp_konsentrat_layer_harian_chart();
                $harga_bulanan = $this->visual->get_hpp_konsentrat_layer_bulanan_chart();
                $harga_tahunan = $this->visual->get_hpp_konsentrat_layer_tahunan_chart();
                break;
            case 'hpp_komplit_layer':
                $data["title"] = "Laporan Average HPP Komplit Layer";
                $data['harga_hari_ini'] = $this->visual->get_hpp_komplit_layer_hari_ini();
                $harga_harian = $this->visual->get_hpp_komplit_layer_harian_chart();
                $harga_bulanan = $this->visual->get_hpp_komplit_layer_bulanan_chart();
                $harga_tahunan = $this->visual->get_hpp_komplit_layer_tahunan_chart();
                break;

            case 'cost_komplit_broiler':
                $data["title"] = "Laporan Average Cost Komplit Broiler";
                $data['harga_hari_ini'] = $this->visual->get_harga_cost_komplit_broiler_hari_ini();
                $harga_harian = $this->visual->get_harga_cost_komplit_broiler_harian_chart();
                $harga_bulanan = $this->visual->get_harga_cost_komplit_broiler_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_cost_komplit_broiler_tahunan_chart();
                break;

            case 'hpp_broiler':
                $data["title"] = "Laporan Average HPP Broiler";
                $data['harga_hari_ini'] = $this->visual->get_harga_hpp_broiler_hari_ini();
                $harga_harian = $this->visual->get_harga_hpp_broiler_harian_chart();
                $harga_bulanan = $this->visual->get_harga_hpp_broiler_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_hpp_broiler_tahunan_chart();
                break;

            default: // Telur Layer
                $data["title"] = "Laporan Harga Telur Layer";
                $data['jenis_terpilih'] = 'telur';
                $data['harga_hari_ini'] = $this->visual->get_harga_telur_hari_ini();
                $harga_harian = $this->visual->get_harga_telur_harian_chart();
                $harga_bulanan = $this->visual->get_harga_telur_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_telur_tahunan_chart();
                break;
        }

        // --- (Sisa kode untuk memproses data chart tidak perlu diubah) ---
        $labels_harian = []; $data_harian = [];
        foreach ($harga_harian as $row) {
            $labels_harian[] = date('d M Y', strtotime($row['tanggal']));
            $data_harian[] = $row['nilai_rata_rata'];
        }
        $data['chart_harian_labels'] = json_encode($labels_harian);
        $data['chart_harian_data'] = json_encode($data_harian);
        
        $labels_bulanan = []; $data_bulanan = [];
        $nama_bulan = [1=>'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        foreach ($harga_bulanan as $row) {
            $labels_bulanan[] = $nama_bulan[(int)$row['bulan']] . ' ' . $row['tahun'];
            $data_bulanan[] = $row['nilai_rata_rata'];
        }
        $data['chart_bulanan_labels'] = json_encode($labels_bulanan);
        $data['chart_bulanan_data'] = json_encode($data_bulanan);
        
        $labels_tahunan = []; $data_tahunan = [];
        foreach ($harga_tahunan as $row) {
            $labels_tahunan[] = $row['tahun'];
            $data_tahunan[] = $row['nilai_rata_rata'];
        }
        $data['chart_tahunan_labels'] = json_encode($labels_tahunan);
        $data['chart_tahunan_data'] = json_encode($data_tahunan);

        $this->load->view('templates/dash_h', $data);
        $this->load->view('visual_harga_view', $data);
        $this->load->view('templates/dash_f', $data);
    }

    /**
     * Menampilkan halaman Laporan Kandang Kosong
     * URL: .../Dashboard_new/visual_kandang_kosong
     */
    public function visual_kandang_kosong()
    {
        $data['user'] = $this->dash->getUserInfo($this->session->userdata('token'))->row_array();
        $data["title"] = "Laporan Kandang Kosong";

        // 1. Ambil nilai filter dari input POST. Jika tidak ada, gunakan nilai default.
        $selected_tipe_ternak = $this->input->post('tipe_ternak');
        // Default rentang waktu: 12 bulan terakhir
        $start_month = $this->input->post('start_month') ?? date('Y-m', strtotime('-11 months'));
        $end_month = $this->input->post('end_month') ?? date('Y-m');

        // 2. Siapkan array filter untuk dikirim ke model
        $filters = [
            'tipe_ternak' => $selected_tipe_ternak,
            'start_month' => $start_month,
            'end_month' => $end_month
        ];
        
        // 3. Panggil model dengan filter yang sudah disiapkan
        $raw_data = $this->visual->get_monthly_vacancy_percentage($filters);
        
        // --- (Sisa kode untuk mengolah data chart tidak perlu diubah) ---
        $labels = []; $pivot_data = [];
        foreach ($raw_data as $row) {
            $label = date('M Y', mktime(0, 0, 0, $row['bulan'], 1, $row['tahun']));
            if (!in_array($label, $labels)) $labels[] = $label;
            $pivot_data[$row['tipe_ternak']][$label] = (float) number_format($row['persentase_kosong'], 2);
        }
        $datasets = []; $colors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6f42c1']; $color_index = 0;
        foreach ($pivot_data as $tipe_ternak => $data_points) {
            $dataset = ['label' => $tipe_ternak, 'data' => [], 'borderColor' => $colors[$color_index % count($colors)], 'fill' => false, 'tension' => 0.1];
            foreach ($labels as $label) {
                $dataset['data'][] = $data_points[$label] ?? 0;
            }
            $datasets[] = $dataset; $color_index++;
        }

        // 4. Kirim data chart DAN data filter ke view
        $data['chart_labels'] = json_encode($labels);
        $data['chart_datasets'] = json_encode($datasets);
        
        // Data untuk mengisi form filter
        $data['all_tipe_ternak'] = $this->visual->get_all_tipe_ternak();
        $data['selected_tipe_ternak'] = $selected_tipe_ternak;
        $data['start_month'] = $start_month;
        $data['end_month'] = $end_month;

        $this->load->view('templates/dash_h', $data);
        $this->load->view('visual_kandang_kosong_view', $data);
        $this->load->view('templates/dash_f', $data);
    }

    public function visual_harga_compare()
    {
        $data['user'] = $this->dash->getUserInfo($this->session->userdata('token'))->row_array();
        $data["title"] = "Perbandingan Harga Komoditas";

        // --- 1. Daftar Semua Komoditas (untuk dropdown filter) ---
        $data['all_komoditas'] = [
            'telur' => 'Harga Telur Layer',
            'jagung' => 'Harga Jagung',
            'katul' => 'Harga Katul',
            'afkir' => 'Harga Afkir',
            'telur_puyuh' => 'Harga Telur Puyuh',
            'telur_bebek' => 'Harga Telur Bebek',
            'bebek_pedaging' => 'Harga Bebek Pedaging',
            'live_bird' => 'Harga Live Bird',
            'pakan_broiler' => 'Pakan Komplit Broiler',
            'doc' => 'DOC',
            'pakan_campuran' => 'Pakan Campuran',
            'pakan_komplit_layer' => 'Pakan Komplit Layer',
            'konsentrat_layer' => 'Avg Harga Konsentrat Layer',
            'hpp_konsentrat_layer' => 'Avg HPP Konsentrat Layer',
            'hpp_komplit_layer' => 'Avg HPP Komplit Layer',
            'cost_komplit_broiler' => 'Avg Cost Komplit Broiler',
            'hpp_broiler' => 'Avg HPP Broiler'
        ];

        // --- 2. Ambil Input Filter dari URL (GET) ---
        $komoditas1_key = $this->input->get('komoditas1') ?: 'telur'; // Default 1
        $komoditas2_key = $this->input->get('komoditas2') ?: 'jagung'; // Default 2

        $data['selected_komoditas1'] = $komoditas1_key;
        $data['selected_komoditas2'] = $komoditas2_key;

        // --- 3. Ambil Data Bulanan untuk Kedua Komoditas ---
        $data1_raw = $this->_get_monthly_data_by_commodity($komoditas1_key);
        $data2_raw = $this->_get_monthly_data_by_commodity($komoditas2_key);

        // --- 4. Proses Data untuk Chart.js (Pivot & Gabung) ---
        $pivot_data = [];
        $all_labels_sortable = []; // 'YYYY-MM' => 'Jan YYYY'
        $nama_bulan = [1=>'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        // Proses Data 1
        foreach ($data1_raw as $row) {
            $key = $row['tahun'] . '-' . str_pad($row['bulan'], 2, '0', STR_PAD_LEFT);
            $label = $nama_bulan[(int)$row['bulan']] . ' ' . $row['tahun'];
            $all_labels_sortable[$key] = $label;
            $pivot_data[$key][$komoditas1_key] = (float)$row['nilai_rata_rata'];
        }
        
        // Proses Data 2
        foreach ($data2_raw as $row) {
            $key = $row['tahun'] . '-' . str_pad($row['bulan'], 2, '0', STR_PAD_LEFT);
            $label = $nama_bulan[(int)$row['bulan']] . ' ' . $row['tahun'];
            $all_labels_sortable[$key] = $label;
            $pivot_data[$key][$komoditas2_key] = (float)$row['nilai_rata_rata'];
        }

        // Urutkan label berdasarkan 'YYYY-MM'
        ksort($all_labels_sortable);

        // --- 5. Siapkan Array Final untuk Chart.js ---
        $final_labels = [];
        $final_data1 = [];
        $final_data2 = [];

        foreach ($all_labels_sortable as $key => $label) {
            $final_labels[] = $label;
            // Jika data tidak ada di bulan itu, isi dengan 0
            $final_data1[] = $pivot_data[$key][$komoditas1_key] ?? 0;
            $final_data2[] = $pivot_data[$key][$komoditas2_key] ?? 0;
        }

        // Ambil nama lengkap komoditas untuk label chart
        $komoditas1_name = $data['all_komoditas'][$komoditas1_key] ?? $komoditas1_key;
        $komoditas2_name = $data['all_komoditas'][$komoditas2_key] ?? $komoditas2_key;

        $data['chart_labels'] = json_encode($final_labels);
        $data['chart_datasets'] = json_encode([
            [
                'label' => $komoditas1_name,
                'data' => $final_data1,
                'borderColor' => '#007bff', // Biru
                'backgroundColor' => 'rgba(0, 123, 255, 0.1)',
                'fill' => false,
                'tension' => 0.1
            ],
            [
                'label' => $komoditas2_name,
                'data' => $final_data2,
                'borderColor' => '#28a745', // Hijau
                'backgroundColor' => 'rgba(40, 167, 69, 0.1)',
                'fill' => false,
                'tension' => 0.1
            ]
        ]);

        // --- 6. Load View ---
        $this->load->view('templates/dash_h', $data);
        $this->load->view('visual_harga_compare_view', $data); // View BARU
        $this->load->view('templates/dash_f', $data);
    }

    /**
     * Helper pribadi untuk mengambil data bulanan berdasarkan jenis komoditas.
     * Ini adalah versi "mini" dari switch di method visual_harga().
     */
    private function _get_monthly_data_by_commodity($jenis_komoditas)
    {
        switch($jenis_komoditas) {
            case 'jagung':
                return $this->visual->get_harga_jagung_bulanan_chart();
            case 'katul':
                return $this->visual->get_harga_katul_bulanan_chart();
            case 'afkir':
                return $this->visual->get_harga_afkir_bulanan_chart();
            case 'telur_puyuh':
                return $this->visual->get_harga_telur_puyuh_bulanan_chart();
            case 'telur_bebek':
                return $this->visual->get_harga_telur_bebek_bulanan_chart();
            case 'bebek_pedaging':
                return $this->visual->get_harga_bebek_pedaging_bulanan_chart();
            case 'live_bird':
                return $this->visual->get_harga_live_bird_bulanan_chart();
            case 'pakan_broiler':
                return $this->visual->get_harga_pakan_broiler_bulanan_chart();
            case 'doc':
                return $this->visual->get_harga_doc_bulanan_chart();
            case 'pakan_campuran':
                return $this->visual->get_harga_pakan_campuran_bulanan_chart();
            case 'pakan_komplit_layer':
                return $this->visual->get_harga_pakan_komplit_layer_bulanan_chart();
            case 'konsentrat_layer':
                return $this->visual->get_harga_konsentrat_layer_bulanan_chart();
            case 'hpp_konsentrat_layer':
                return $this->visual->get_hpp_konsentrat_layer_bulanan_chart();
            case 'hpp_komplit_layer':
                return $this->visual->get_hpp_komplit_layer_bulanan_chart();
            case 'cost_komplit_broiler':
                return $this->visual->get_harga_cost_komplit_broiler_bulanan_chart();
            case 'hpp_broiler':
                return $this->visual->get_harga_hpp_broiler_bulanan_chart();
            case 'telur':
            default: // Telur Layer
                return $this->visual->get_harga_telur_bulanan_chart();
        }
    }

}
