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
        // LOGIKA FILTER BERDASARKAN PERAN (ROLE)
        // ========================================================================
        $user_id_filter = null; // Defaultnya bisa melihat semua data
        if (isset($data['user']['group_user']) && $data['user']['group_user'] === 'surveyor') {
            // Jika user adalah surveyor, filter hanya untuk data miliknya
            $user_id_filter = $data['user']['id_user'];
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

        // Panggil fungsi model dengan menambahkan $user_id_filter
        $data['performance_data'] = $this->visual->get_surveyor_performance($selected_month, $selected_year, $user_id_filter);
        $data['area_performance_data'] = $this->visual->get_area_performance($selected_month, $selected_year, $data['user']);

        $visit_breakdown = $this->visual->get_visit_breakdown($selected_month, $selected_year, $user_id_filter);
        $seminar_count = $this->visual->get_seminar_count_by_month($selected_month, $selected_year, $user_id_filter);
        $new_customer_count = $this->visual->get_new_customer_count_by_month($selected_month, $selected_year, $user_id_filter);
        $sample_count = $this->visual->get_sample_count_by_month($selected_month, $selected_year, $user_id_filter);

        // --- Sisa fungsi (logika penggabungan data) tidak perlu diubah ---
        $combined_data = $visit_breakdown;
        if ($seminar_count > 0) $combined_data[] = ['kategori' => 'Seminar', 'jumlah_visit' => $seminar_count];
        if ($new_customer_count > 0) $combined_data[] = ['kategori' => 'New Customers', 'jumlah_visit' => $new_customer_count];
        if ($sample_count > 0) $combined_data[] = ['kategori' => 'Sample', 'jumlah_visit' => $sample_count];
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
    
    public function visual_harga_telur()
    {
        $data['user'] = $this->dash->getUserInfo($this->session->userdata('token'))->row_array();
        $data["title"] = "Laporan Harga Telur";

        $data['harga_hari_ini'] = $this->visual->get_harga_telur_hari_ini();
       
        $harga_harian = $this->visual->get_harga_telur_harian_chart();
        $labels_harian = [];
        $data_harian = [];
        foreach ($harga_harian as $row) {
            $labels_harian[] = date('d M Y', strtotime($row['tanggal']));
            $data_harian[] = $row['nilai_rata_rata'];
        }
        $data['chart_harian_labels'] = json_encode($labels_harian);
        $data['chart_harian_data'] = json_encode($data_harian);
        
        $harga_bulanan = $this->visual->get_harga_telur_bulanan_chart();
        $labels_bulanan = [];
        $data_bulanan = [];
        $nama_bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        foreach ($harga_bulanan as $row) {
            $labels_bulanan[] = $nama_bulan[(int)$row['bulan']] . ' ' . $row['tahun'];
            $data_bulanan[] = $row['nilai_rata_rata'];
        }
        $data['chart_bulanan_labels'] = json_encode($labels_bulanan);
        $data['chart_bulanan_data'] = json_encode($data_bulanan);
        
        $harga_tahunan = $this->visual->get_harga_telur_tahunan_chart();
        $labels_tahunan = [];
        $data_tahunan = [];
        foreach ($harga_tahunan as $row) {
            $labels_tahunan[] = $row['tahun'];
            $data_tahunan[] = $row['nilai_rata_rata'];
        }
        $data['chart_tahunan_labels'] = json_encode($labels_tahunan);
        $data['chart_tahunan_data'] = json_encode($data_tahunan);

        $this->load->view('templates/dash_h', $data);
        $this->load->view('visual_harga_telur', $data);
        $this->load->view('templates/dash_f', $data);
    }

}
