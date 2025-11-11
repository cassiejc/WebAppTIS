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
        $this->load->model('M_Visual', 'visual'); 

        if(!$this->session->has_userdata('token')){
            redirect('Home'); 
        }
    }
    
    public function index()
    {
        $token = $this->session->userdata('token');
        $data['user'] = $this->dash->getUserInfo($token)->row_array();
        $data["title"] = "CP APPS";

        $this->load->view('templates/dash_h', $data);
        $this->load->view('page_view/home', $data); 
        $this->load->view('templates/dash_f', $data);
    }
    
    // GANTI SELURUH FUNGSI INI di Dashboard_new.php

    public function visual_data_kunjungan()
    {
        $token = $this->session->userdata('token');
        $data['user'] = $this->dash->getUserInfo($token)->row_array();
        $data["title"] = "CP APPS";
        
        $user_id_filter = null; 
        $area_id_filter = null; 

        if (isset($data['user']['group_user'])) {
            $group = $data['user']['group_user'];
            if ($group === 'surveyor') {
                $user_id_filter = $data['user']['id_user'];
            } elseif ($group === 'koordinator') {
                if (isset($data['user']['master_area_id'])) {
                    $area_id_filter = $data['user']['master_area_id'];
                }
            }
        }

        // --- [LOGIKA FILTER BARU YANG DIPERBAIKI] ---
        
        $filter_type = $this->input->post('filter_type') ?? 'range'; // Default 'range'

        // Siapkan nilai default untuk semua input
        $default_start = date('Y-m');
        $default_end = date('Y-m');
        $default_quarter = 'Q' . ceil(date('n') / 3);
        $default_quarter_year = date('Y');

        // Variabel ini akan menampung rentang tanggal FINAL untuk dikirim ke Model
        $query_start_date = $default_start;
        $query_end_date = $default_end;

        if ($this->input->post()) {
            if ($filter_type == 'range') {
                // Tipe: Range. Ambil nilai dari input range.
                $query_start_date = $this->input->post('start_date');
                $query_end_date = $this->input->post('end_date');
                
                // Simpan nilai input triwulan (yang tersembunyi) untuk dikirim kembali ke view
                $data['selected_quarter'] = $this->input->post('quarter') ?? $default_quarter;
                $data['selected_quarter_year'] = $this->input->post('quarter_year') ?? $default_quarter_year;

            } elseif ($filter_type == 'quarter') {
                // Tipe: Triwulan. Ambil nilai dari input triwulan.
                $selected_quarter = $this->input->post('quarter');
                $selected_quarter_year = $this->input->post('quarter_year');
                
                // Terjemahkan triwulan ke rentang tanggal FINAL
                switch ($selected_quarter) {
                    case 'Q1':
                        $query_start_date = $selected_quarter_year . '-01';
                        $query_end_date = $selected_quarter_year . '-03';
                        break;
                    case 'Q2':
                        $query_start_date = $selected_quarter_year . '-04';
                        $query_end_date = $selected_quarter_year . '-06';
                        break;
                    case 'Q3':
                        $query_start_date = $selected_quarter_year . '-07';
                        $query_end_date = $selected_quarter_year . '-09';
                        break;
                    case 'Q4':
                    default:
                        $query_start_date = $selected_quarter_year . '-10';
                        $query_end_date = $selected_quarter_year . '-12';
                        break;
                }
                
                // Simpan nilai input triwulan untuk dikirim kembali ke view
                $data['selected_quarter'] = $selected_quarter;
                $data['selected_quarter_year'] = $selected_quarter_year;
            }
        } else {
            // Halaman dimuat pertama kali, gunakan semua default
            $data['selected_quarter'] = $default_quarter;
            $data['selected_quarter_year'] = $default_quarter_year;
        }

        // Kirim nilai input range (yang mungkin tersembunyi) kembali ke view
        // Jika tipe 'range', gunakan nilai query. Jika tipe 'quarter', gunakan nilai POST (jika ada) atau default.
        $data['selected_start'] = ($filter_type == 'range') ? $query_start_date : ($this->input->post('start_date') ?? $default_start);
        $data['selected_end'] = ($filter_type == 'range') ? $query_end_date : ($this->input->post('end_date') ?? $default_end);
        $data['filter_type'] = $filter_type;

        // --- [AKHIR LOGIKA FILTER BARU] ---


        // Model HANYA menerima $query_start_date dan $query_end_date yang sudah dihitung
        $data['performance_data'] = $this->visual->get_surveyor_performance($query_start_date, $query_end_date, $user_id_filter, $area_id_filter);
        $data['area_performance_data'] = $this->visual->get_area_performance($query_start_date, $query_end_date, $data['user']);
        $visit_breakdown_raw = $this->visual->get_visit_breakdown($query_start_date, $query_end_date, $user_id_filter, $area_id_filter); 
        
        $sample_count = $this->visual->get_sample_count_by_range($query_start_date, $query_end_date, $user_id_filter, $area_id_filter);
        $seminar_count = $this->visual->get_seminar_count_by_range($query_start_date, $query_end_date, $user_id_filter, $area_id_filter);
        $new_customer_count = $this->visual->get_new_customer_count_by_range($query_start_date, $query_end_date, $user_id_filter, $area_id_filter);
        
        $data['visit_details_table'] = $this->visual->get_all_visit_details($query_start_date, $query_end_date, $user_id_filter, $area_id_filter);
        
        // ... (sisa logika grouping $data_to_group Anda tetap sama) ...
        $data_to_group = $visit_breakdown_raw; 
        if ($sample_count > 0) $data_to_group[] = ['kategori' => 'Sample', 'jumlah_visit' => $sample_count];

        $category_map = [
            'Agen' => 'Agen/Subagen/Lainnya',
            'Subagen' => 'Agen/Subagen/Lainnya',
            'Kantor' => 'Agen/Subagen/Lainnya',
            'Arap' => 'Others',
            'Bebek Pedaging' => 'Others',
            'Bebek Petelur' => 'Others',
            'Puyuh' => 'Others',
            'Kemitraan' => 'Others',
            'Lainnya' => 'Others',
            'Sample' => 'Others', 
            'Grower' => 'Demoplot DOC'
        ];

        $grouped_totals = [];
        $processed_breakdown = []; 

        foreach ($data_to_group as $item) { 
            $raw_kategori = $item['kategori'];
            $jumlah = (int)$item['jumlah_visit'];

            if (isset($category_map[$raw_kategori])) {
                $display_kategori = $category_map[$raw_kategori];
                if (!isset($grouped_totals[$display_kategori])) {
                    $grouped_totals[$display_kategori] = 0;
                }
                $grouped_totals[$display_kategori] += $jumlah;
            } else {
                $processed_breakdown[] = $item; 
            }
        }

        foreach ($grouped_totals as $kategori => $jumlah) {
            if ($jumlah > 0) {
                $processed_breakdown[] = [
                    'kategori' => $kategori,
                    'jumlah_visit' => $jumlah
                ];
            }
        }

        $combined_data = $processed_breakdown; 
        if ($seminar_count > 0) $combined_data[] = ['kategori' => 'Seminar', 'jumlah_visit' => $seminar_count];
        if ($new_customer_count > 0) $combined_data[] = ['kategori' => 'New Customers', 'jumlah_visit' => $new_customer_count];

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
        // ... (akhir logika grouping) ...

        $data['vip_grower_farms'] = $this->visual->get_vip_grower_farms($user_id_filter, $area_id_filter);
        
        // [BARU] Kirim tanggal FINAL yang benar ke JavaScript untuk AJAX
        $data['js_start_date'] = $query_start_date;
        $data['js_end_date'] = $query_end_date;

        $this->load->view('templates/dash_h', $data);
        $this->load->view('visual_data_kunjungan', $data); 
        $this->load->view('templates/dash_f', $data);
    }

    public function get_data_for_surveyor_ajax()
    {
        // Ambil input dari POST request
        $user_id = $this->input->post('user_id');
        
        // --- [GANTI BAGIAN INI] ---
        // $selected_month = $this->input->post('bulan');
        // $selected_year = $this->input->post('tahun');
        // --- [MENJADI INI] ---
        $selected_start = $this->input->post('start_date');
        $selected_end = $this->input->post('end_date');
        // --- [AKHIR PERUBAHAN] ---


        // --- [GANTI VALIDASI INI] ---
        // if (empty($user_id)) {
        // --- [MENJADI INI] ---
        if (empty($user_id) || empty($selected_start) || empty($selected_end)) {
            $this->output->set_status_header(400)->set_output(json_encode(['error' => 'Parameter tidak valid.']));
            return;
        }

        // 1. Dapatkan data Performa Area untuk user tersebut
        // Kita perlu mendapatkan info user untuk memanggil get_area_performance
        $user_info = $this->visual->get_user_info_by_id($user_id); 
        
        // --- [GANTI PARAMETER INI] ---
        // $area_performance_data = $this->visual->get_area_performance($selected_month, $selected_year, $user_info);
        // --- [MENJADI INI] ---
        $area_performance_data = $this->visual->get_area_performance($selected_start, $selected_end, $user_info);

        // 2. Dapatkan data Komposisi Visit untuk user tersebut
        // --- [GANTI SEMUA FUNGSI INI] ---
        // $visit_breakdown_raw = $this->visual->get_visit_breakdown($selected_month, $selected_year, $user_id);
        // $sample_count = $this->visual->get_sample_count_by_month($selected_month, $selected_year, $user_id);
        // $seminar_count = $this->visual->get_seminar_count_by_month($selected_month, $selected_year, $user_id);
        // $new_customer_count = $this->visual->get_new_customer_count_by_month($selected_month, $selected_year, $user_id);
        // --- [MENJADI INI] ---
        $visit_breakdown_raw = $this->visual->get_visit_breakdown($selected_start, $selected_end, $user_id);
        $sample_count = $this->visual->get_sample_count_by_range($selected_start, $selected_end, $user_id);
        $seminar_count = $this->visual->get_seminar_count_by_range($selected_start, $selected_end, $user_id);
        $new_customer_count = $this->visual->get_new_customer_count_by_range($selected_start, $selected_end, $user_id);
        // --- [AKHIR PERUBAHAN] ---

        // Lakukan grouping dan kalkulasi persentase (logika ini SAMA, tidak perlu diubah)
        $data_to_group = $visit_breakdown_raw;
        if ($sample_count > 0) $data_to_group[] = ['kategori' => 'Sample', 'jumlah_visit' => $sample_count];
        
        $category_map = [
            'Agen' => 'Agen/Subagen/Lainnya',
            'Subagen' => 'Agen/Subagen/Lainnya',
            'Kantor' => 'Agen/Subagen/Lainnya',
            'Arap' => 'Others',
            'Bebek Pedaging' => 'Others',
            'Bebek Petelur' => 'Others',
            'Puyuh' => 'Others',
            'Kemitraan' => 'Others',
            'Lainnya' => 'Others',
            'Sample' => 'Others', 
            'Grower' => 'Demoplot DOC'
        ];

        $grouped_totals = []; $processed_breakdown = [];
        foreach ($data_to_group as $item) {
            $raw_kategori = $item['kategori']; $jumlah = (int)$item['jumlah_visit'];
            if (isset($category_map[$raw_kategori])) {
                $display_kategori = $category_map[$raw_kategori];
                if (!isset($grouped_totals[$display_kategori])) $grouped_totals[$display_kategori] = 0;
                $grouped_totals[$display_kategori] += $jumlah;
            } else { $processed_breakdown[] = $item; }
        }
        foreach ($grouped_totals as $kategori => $jumlah) {
            if ($jumlah > 0) $processed_breakdown[] = ['kategori' => $kategori, 'jumlah_visit' => $jumlah];
        }
        $combined_data = $processed_breakdown;
        if ($seminar_count > 0) $combined_data[] = ['kategori' => 'Seminar', 'jumlah_visit' => $seminar_count];
        if ($new_customer_count > 0) $combined_data[] = ['kategori' => 'New Customers', 'jumlah_visit' => $new_customer_count];
        
        $grand_total = array_sum(array_column($combined_data, 'jumlah_visit'));
        $final_breakdown = [];
        if ($grand_total > 0) {
            foreach ($combined_data as $item) {
                $final_breakdown[] = ['kategori' => $item['kategori'], 'persentase' => ($item['jumlah_visit'] / $grand_total) * 100];
            }
        }
        usort($final_breakdown, function($a, $b) { return $b['persentase'] <=> $a['persentase']; });
        $composition_data = $final_breakdown;


        // 3. Kembalikan data dalam format JSON
        $response = [
            'status' => 'success',
            'area_data' => $area_performance_data,
            'composition_data' => $composition_data,
        ];

        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
   
    // GANTI FUNGSI INI DI Dashboard_new.php
    public function get_surveyors_for_area_ajax()
    {
        // Ambil input dari POST request
        $area_id = $this->input->post('area_id');
        $selected_start = $this->input->post('start_date');
        $selected_end = $this->input->post('end_date');

        if (empty($area_id) || empty($selected_start) || empty($selected_end)) {
            $this->output->set_status_header(400)->set_output(json_encode(['error' => 'Parameter tidak valid.']));
            return;
        }

        // Panggil fungsi performa surveyor
        $surveyor_data = $this->visual->get_surveyor_performance(
            $selected_start, 
            $selected_end, 
            null, 
            $area_id
        );

        // 2. [BARU] Ambil dan proses data komposisi untuk AREA
        // (Logika disalin dari visual_data_kunjungan() dan get_data_for_surveyor_ajax())
        $user_id_filter = null; // Filter berdasarkan area, bukan user
        
        $visit_breakdown_raw = $this->visual->get_visit_breakdown($selected_start, $selected_end, $user_id_filter, $area_id);
        $sample_count = $this->visual->get_sample_count_by_range($selected_start, $selected_end, $user_id_filter, $area_id);
        $seminar_count = $this->visual->get_seminar_count_by_range($selected_start, $selected_end, $user_id_filter, $area_id);
        $new_customer_count = $this->visual->get_new_customer_count_by_range($selected_start, $selected_end, $user_id_filter, $area_id);
        
        $data_to_group = $visit_breakdown_raw;
        if ($sample_count > 0) $data_to_group[] = ['kategori' => 'Sample', 'jumlah_visit' => $sample_count];
        
        $category_map = [
            'Agen' => 'Agen/Subagen/Lainnya',
            'Subagen' => 'Agen/Subagen/Lainnya',
            'Kantor' => 'Agen/Subagen/Lainnya',
            'Arap' => 'Others',
            'Bebek Pedaging' => 'Others',
            'Bebek Petelur' => 'Others',
            'Puyuh' => 'Others',
            'Kemitraan' => 'Others',
            'Lainnya' => 'Others',
            'Sample' => 'Others', 
            'Grower' => 'Demoplot DOC'
        ];

        $grouped_totals = []; $processed_breakdown = [];
        foreach ($data_to_group as $item) {
            $raw_kategori = $item['kategori']; $jumlah = (int)$item['jumlah_visit'];
            if (isset($category_map[$raw_kategori])) {
                $display_kategori = $category_map[$raw_kategori];
                if (!isset($grouped_totals[$display_kategori])) $grouped_totals[$display_kategori] = 0;
                $grouped_totals[$display_kategori] += $jumlah;
            } else { $processed_breakdown[] = $item; }
        }
        foreach ($grouped_totals as $kategori => $jumlah) {
            if ($jumlah > 0) $processed_breakdown[] = ['kategori' => $kategori, 'jumlah_visit' => $jumlah];
        }
        $combined_data = $processed_breakdown;
        if ($seminar_count > 0) $combined_data[] = ['kategori' => 'Seminar', 'jumlah_visit' => $seminar_count];
        if ($new_customer_count > 0) $combined_data[] = ['kategori' => 'New Customers', 'jumlah_visit' => $new_customer_count];
        
        $grand_total = array_sum(array_column($combined_data, 'jumlah_visit'));
        $final_breakdown = [];
        if ($grand_total > 0) {
            foreach ($combined_data as $item) {
                $final_breakdown[] = ['kategori' => $item['kategori'], 'persentase' => ($item['jumlah_visit'] / $grand_total) * 100];
            }
        }
        usort($final_breakdown, function($a, $b) { return $b['persentase'] <=> $a['persentase']; });
        $composition_data = $final_breakdown; // Ini adalah data komposisi baru

        // 3. Kembalikan data dalam format JSON
        $response = [
            'status' => 'success',
            'surveyor_data' => $surveyor_data,
            'composition_data' => $composition_data // <-- Tambahkan data komposisi
        ];

        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function visual_kasus_penyakit()
    {
        $data['user'] = $this->dash->getUserInfo($this->session->userdata('token'))->row_array();
        $data["title"] = "Laporan Kasus Penyakit";
        $user_id_filter = null; 
        if (isset($data['user']['group_user']) && $data['user']['group_user'] === 'surveyor') {
            $user_id_filter = $data['user']['id_user'];
        }

        $selected_year = $this->input->post('tahun') ?: date('Y');
        
        // 1. Pastikan $this->visual->get_kasus_breakdown_stacked() 
        //    adalah versi BARU (yang ada Window Function SQL)
        $raw_stacked_data = $this->visual->get_kasus_breakdown_stacked($selected_year, $user_id_filter);
        
        $labels = []; $kategori_chart = []; $pivot_chart_data = [];
        foreach ($raw_stacked_data as $row) {
            $bulan = $row['bulan_tahun'];
            $kat = $row['kategori_kasus'];
            
            // --- PERUBAHAN 1 ---
            // Kita ambil 'persentase' (float) BUKAN 'jumlah' (int)
            $persentase = (float)$row['persentase']; // <-- DIUBAH
            
            if (!in_array($bulan, $labels)) $labels[] = $bulan;
            if (!in_array($kat, $kategori_chart)) $kategori_chart[] = $kat;
            
            // --- PERUBAHAN 2 ---
            // Simpan data persentase ke pivot array
            $pivot_chart_data[$kat][$bulan] = $persentase; // <-- DIUBAH
        }
        
        $datasets = [];
        $colors = ['#28a745', '#ffc107', '#6f42c1', '#dc3545', '#fd7e14', '#17a2b8', '#6c757d'];
        $color_index = 0;
        foreach ($kategori_chart as $kat) {
            $dataset = [
                'label' => $kat, 
                'data' => [], 
                'backgroundColor' => $colors[$color_index % count($colors)]
            ];
            foreach ($labels as $bulan) {
                // Ini akan otomatis mengambil data persentase yang sudah kita simpan
                $dataset['data'][] = $pivot_chart_data[$kat][$bulan] ?? 0;
            }
            $datasets[] = $dataset;
            $color_index++;
        }
        $data['chart_labels'] = json_encode($labels);
        $data['chart_datasets'] = json_encode($datasets); // Sekarang $datasets berisi persentase

        // --- Bagian bawah (pivot table & detail) tidak perlu diubah ---
        $raw_pivot_data = $this->visual->get_kasus_pivot_by_area($selected_year, $user_id_filter);
        
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
        
        $data['kasus_detail_list'] = $this->visual->get_kasus_detail_list($selected_year, $user_id_filter);

        $data['selected_year'] = $selected_year;

        $this->load->view('templates/dash_h', $data);
        $this->load->view('visual_kasus_penyakit', $data); 
        $this->load->view('templates/dash_f', $data);
    }
    
    public function visual_kandang_kosong()
    {
        $data['user'] = $this->dash->getUserInfo($this->session->userdata('token'))->row_array();
        $data["title"] = "Laporan Kandang Kosong";

        $selected_tipe_ternak = $this->input->post('tipe_ternak');
        $start_month = $this->input->post('start_month') ?? date('Y-m', strtotime('-11 months'));
        $end_month = $this->input->post('end_month') ?? date('Y-m');
        
        $filters = [
            'tipe_ternak' => $selected_tipe_ternak, 
            'end_month' => $end_month
        ];
        
        $raw_data = $this->visual->get_monthly_vacancy_percentage($filters);
        
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
        $data['chart_labels'] = json_encode($labels);
        $data['chart_datasets'] = json_encode($datasets);
        $data['all_tipe_ternak'] = $this->visual->get_all_tipe_ternak();
        $data['selected_tipe_ternak'] = $selected_tipe_ternak;
        $data['start_month'] = $start_month;
        $data['end_month'] = $end_month;
    
        $this->load->view('templates/dash_h', $data);
        $this->load->view('visual_kandang_kosong_view', $data);
        $this->load->view('templates/dash_f', $data);
    }
    
    private function _get_monthly_data_by_commodity($jenis_komoditas, $tahun = null)
    {
        // $tahun sekarang bersifat opsional dan default-nya null
        switch($jenis_komoditas) {
            case 'jagung':
                return $this->visual->get_harga_jagung_bulanan_chart($tahun);
            case 'katul':
                return $this->visual->get_harga_katul_bulanan_chart($tahun);
            case 'afkir':
                return $this->visual->get_harga_afkir_bulanan_chart($tahun);
            case 'telur_puyuh':
                return $this->visual->get_harga_telur_puyuh_bulanan_chart($tahun);
            case 'telur_bebek':
                return $this->visual->get_harga_telur_bebek_bulanan_chart($tahun);
            case 'bebek_pedaging':
                return $this->visual->get_harga_bebek_pedaging_bulanan_chart($tahun);
            case 'live_bird':
                return $this->visual->get_harga_live_bird_bulanan_chart($tahun);
            case 'pakan_broiler':
                return $this->visual->get_harga_pakan_broiler_bulanan_chart($tahun);
            case 'doc':
                return $this->visual->get_harga_doc_bulanan_chart($tahun);
            case 'pakan_campuran':
                // Mapping Pakan Campuran ke konsentrat_layer
                return $this->visual->get_harga_konsentrat_layer_bulanan_chart($tahun);
            case 'pakan_komplit_layer':
                // Mapping Pakan Komplit Layer ke hpp_komplit_layer
                return $this->visual->get_hpp_komplit_layer_bulanan_chart($tahun);
            case 'konsentrat_layer':
                return $this->visual->get_harga_konsentrat_layer_bulanan_chart($tahun);
            case 'hpp_konsentrat_layer':
                return $this->visual->get_hpp_konsentrat_layer_bulanan_chart($tahun);
            case 'hpp_komplit_layer':
                return $this->visual->get_hpp_komplit_layer_bulanan_chart($tahun);
            case 'cost_komplit_broiler':
                return $this->visual->get_harga_cost_komplit_broiler_bulanan_chart($tahun);
            case 'hpp_broiler':
                return $this->visual->get_harga_hpp_broiler_bulanan_chart($tahun);
            case 'telur':
            default: 
                return $this->visual->get_harga_telur_bulanan_chart($tahun);
        }
    }

    // public function visual_kondisi_lingkungan()
    // {
    //     // 1. Standard User & Title Setup
    //     $token = $this->session->userdata('token');
    //     $data['user'] = $this->dash->getUserInfo($token)->row_array();
    //     $data["title"] = "Laporan Kondisi Lingkungan";
        
    //     // 2. Setup Filter (Surveyor/Koordinator)
    //     $user_id_filter = null; 
    //     $area_id_filter = null; 
    //     if (isset($data['user']['group_user'])) {
    //         $group = $data['user']['group_user'];
    //         if ($group === 'surveyor') {
    //             $user_id_filter = $data['user']['id_user'];
    //         } elseif ($group === 'koordinator') {
    //             if (isset($data['user']['master_area_id'])) {
    //                 $area_id_filter = $data['user']['master_area_id'];
    //             }
    //         }
    //     }

    //     // 3. Get Date Filter & Master Labels
    //     $selected_year = $this->input->post('tahun') ?: date('Y');
    //     $data['selected_year'] = $selected_year;
        
    //     $master_labels = [];
    //     for ($m = 1; $m <= 12; $m++) {
    //         // [PERUBAHAN] Ubah format 'M Y' (Jan 2025) menjadi 'M' (Jan)
    //         $master_labels[] = date('M', mktime(0, 0, 0, $m, 1, $selected_year));
    //     }
        
    //     // 4. Get Pakan Filter
    //     $all_pakan_options_raw = $this->visual->get_all_pakan_layer_options();
    //     $all_pakan_options = array_column($all_pakan_options_raw, 'pakan');
    //     $data['all_pakan_options'] = $all_pakan_options; 
    //     $selected_pakan_filter = [];
        
    //     if ($this->input->method() == 'post') {
    //         $selected_pakan_filter = $this->input->post('pakan') ?? []; 
    //     } else {
    //         $selected_pakan_filter = $all_pakan_options;
    //     }
    //     $data['selected_pakan'] = $selected_pakan_filter; 

    //     // 5. Panggil Model
    //     // Pastikan model M_Visual juga diperbarui agar SQL-nya mengembalikan 'Jan' bukan 'Jan 2025'
    //     $raw_stacked_data = $this->visual->get_kondisi_lingkungan_monthly(
    //         $selected_year, $user_id_filter, $area_id_filter, $selected_pakan_filter 
    //     );
    //     $raw_avg_data = $this->visual->get_lingkungan_avg_monthly(
    //         $selected_year, $user_id_filter, $area_id_filter, $selected_pakan_filter
    //     );
        
    //     // 6. Proses Data STACKED (Lalat, Kotoran) - (Tidak Berubah)
    //     $process_100_percent_stacked_data = function($raw_data, $chart_key, $master_labels) {
    //         $categories = []; $pivot_data = []; $monthly_totals = [];
    //         foreach ($raw_data as $row) {
    //             if ($row['kategori_chart'] != $chart_key) continue; 
    //             $bulan = $row['bulan_tahun']; // Ini sekarang 'Jan', 'Feb', dst.
    //             $kat = $row['nilai']; 
    //             $jumlah = (int)$row['jumlah'];
    //             if (!in_array($kat, $categories)) $categories[] = $kat;
    //             if (!isset($pivot_data[$kat][$bulan])) $pivot_data[$kat][$bulan] = 0;
    //             $pivot_data[$kat][$bulan] += $jumlah;
    //             if (!isset($monthly_totals[$bulan])) $monthly_totals[$bulan] = 0;
    //             $monthly_totals[$bulan] += $jumlah;
    //         }
    //         if ($chart_key == 'lalat') { $order = ['Normal', 'Sedikit', 'Sedang', 'Banyak']; } 
    //         elseif ($chart_key == 'kotoran') { $order = ['Kering', 'Lembab', 'Basah', 'Normal']; } 
    //         else { $order = []; }
    //         usort($categories, function($a, $b) use ($order) {
    //             $pos_a = array_search($a, $order); $pos_b = array_search($b, $order);
    //             if ($pos_a === false && $pos_b === false) { return $a <=> $b; }
    //             if ($pos_a === false) return 1; if ($pos_b === false) return -1;
    //             return $pos_a <=> $pos_b;
    //         });
    //         $datasets = [];
    //         $colors = ['#28a745', '#ffc107', '#dc3545', '#007bff', '#6f42c1', '#fd7e14']; 
    //         $color_index = 0;
    //         foreach ($categories as $kat) {
    //             $dataset = ['label' => $kat, 'data' => [], 'raw_counts' => [], 'backgroundColor' => $colors[$color_index % count($colors)]];
    //             foreach ($master_labels as $bulan) { // $master_labels sekarang ('Jan', 'Feb', ...)
    //                 $jumlah_kat = $pivot_data[$kat][$bulan] ?? 0;
    //                 $total_bulan = $monthly_totals[$bulan] ?? 0;
    //                 $persentase = ($total_bulan > 0) ? ($jumlah_kat / $total_bulan) * 100 : 0;
    //                 $dataset['data'][] = round($persentase, 2); 
    //                 $dataset['raw_counts'][] = $jumlah_kat;
    //             }
    //             $datasets[] = $dataset; $color_index++;
    //         }
    //         return ['labels' => $master_labels, 'datasets' => $datasets]; 
    //     };
        
    //     // 7. [PERUBAHAN] Proses Data AVERAGE (Suhu, Kelembapan, HI) - (Tidak Berubah)
    //     $process_multi_axis_chart = function($raw_data, $master_labels) {
    //         $pivot_data = [];
    //         // Pivot data: [ 'Jan' => ['suhu' => 29.5, 'kelembapan' => 80, 'heat_index' => 165.1], ... ]
    //         foreach($raw_data as $row) {
    //             $pivot_data[$row['bulan_tahun']][$row['kategori_chart']] = (float)$row['rata_rata'];
    //         }
            
    //         $suhu_values = [];
    //         $kelembapan_values = [];
    //         $heat_index_values = []; 

    //         // Loop pakai master_labels untuk menjamin 12 bulan
    //         foreach ($master_labels as $bulan) { // $master_labels sekarang ('Jan', 'Feb', ...)
    //             $suhu_values[] = $pivot_data[$bulan]['suhu'] ?? null;
    //             $kelembapan_values[] = $pivot_data[$bulan]['kelembapan'] ?? null;
    //             $heat_index_values[] = $pivot_data[$bulan]['heat_index'] ?? null; 
    //         }
            
    //         return [
    //             'labels' => $master_labels,
    //             'datasets' => [
    //                 [
    //                     'type' => 'bar', 
    //                     'label' => 'Suhu (°C)',
    //                     'data' => $suhu_values,
    //                     'backgroundColor' => 'rgba(220, 53, 69, 0.7)', 
    //                     'borderColor' => '#dc3545',
    //                     'yAxisID' => 'ySuhu', 
    //                     'order' => 2 
    //                 ],
    //                 [
    //                     'type' => 'bar', 
    //                     'label' => 'Kelembapan (%)',
    //                     'data' => $kelembapan_values,
    //                     'backgroundColor' => 'rgba(0, 123, 255, 0.7)', 
    //                     'borderColor' => '#007bff',
    //                     'yAxisID' => 'yKelembapan', 
    //                     'order' => 2 
    //                 ],
    //                 [ 
    //                     'type' => 'line', 
    //                     'label' => 'Heat Index (F+RH)',
    //                     'data' => $heat_index_values,
    //                     'borderColor' => '#ffc107', 
    //                     'backgroundColor' => '#ffc107',
    //                     'yAxisID' => 'yHeatIndex', 
    //                     'tension' => 0.4,
    //                     'borderWidth' => 3,
    //                     'pointRadius' => 4,
    //                     'order' => 1 
    //                 ]
    //             ]
    //         ];
    //     };

    //     // 8. Panggil semua fungsi proses
    //     $data['chart_lalat_data'] = $process_100_percent_stacked_data($raw_stacked_data, 'lalat', $master_labels);
    //     $data['chart_kotoran_data'] = $process_100_percent_stacked_data($raw_stacked_data, 'kotoran', $master_labels);
        
    //     $data['chart_suhu_kelembapan_hi_data'] = $process_multi_axis_chart($raw_avg_data, $master_labels);

    //     // 9. Load Views
    //     $this->load->view('templates/dash_h', $data);
    //     $this->load->view('visual_kondisi_lingkungan_view', $data); 
    //     $this->load->view('templates/dash_f', $data); 
    // }

    // GANTI FUNGSI INI DI Dashboard_new.php
public function visual_kondisi_lingkungan()
{
    // 1. Standard User & Title Setup
    $token = $this->session->userdata('token');
    $data['user'] = $this->dash->getUserInfo($token)->row_array();
    $data["title"] = "Laporan Kondisi Lingkungan";
    
    // 2. Setup Filter (Surveyor/Koordinator)
    $user_id_filter = null; 
    $area_id_filter = null; 
    
    // [LOGIKA BARU] Cek Admin
    $is_admin = true; 
    
    if (isset($data['user']['group_user'])) {
        $group = $data['user']['group_user'];
        if ($group === 'surveyor') {
            $user_id_filter = $data['user']['id_user'];
            $is_admin = false; // [BARU]
        } elseif ($group === 'koordinator') {
            if (isset($data['user']['master_area_id'])) {
                $area_id_filter = $data['user']['master_area_id'];
            }
            $is_admin = false; // [BARU]
        }
    }
    $data['is_admin'] = $is_admin; // [BARU] Kirim ke view

    // 3. Get Date Filter & Master Labels
    $selected_year = $this->input->post('tahun') ?: date('Y');
    $data['selected_year'] = $selected_year;
    
    $master_labels = [];
    for ($m = 1; $m <= 12; $m++) {
        $master_labels[] = date('M', mktime(0, 0, 0, $m, 1, $selected_year));
    }
    
    // 4. Get Pakan Filter (Logika ini tetap sama)
    $all_pakan_options_raw = $this->visual->get_all_pakan_layer_options();
    $all_pakan_options = array_column($all_pakan_options_raw, 'pakan');
    $data['all_pakan_options'] = $all_pakan_options; 
    $selected_pakan_filter = [];
    
    if ($this->input->method() == 'post') {
        $selected_pakan_filter = $this->input->post('pakan') ?? []; 
    } else {
        $selected_pakan_filter = $all_pakan_options;
    }
    $data['selected_pakan'] = $selected_pakan_filter; 

    // 5. [BARU] Get Area Filter (Hanya untuk Admin)
    $data['all_areas'] = $this->visual->get_all_areas(); // Pastikan Anda punya fungsi ini (dari visual_vip_farms)
    $selected_areas = []; // Default kosong

    if ($is_admin) {
        if ($this->input->method() == 'post') {
             // Jika form disubmit, ambil dari post
            $selected_areas = $this->input->post('areas') ?? [];
        } else {
             // Jika admin & load pertama kali, pilih semua area by default
            $selected_areas = array_column($data['all_areas'], 'master_area_id');
        }
    }
    // Jika bukan admin, $selected_areas tetap array kosong []
    $data['selected_areas'] = $selected_areas; // Kirim ke view untuk re-check

    // 6. Panggil Model
    // [PERUBAHAN] Tambahkan $selected_areas ke panggilan model
    $raw_stacked_data = $this->visual->get_kondisi_lingkungan_monthly(
        $selected_year, $user_id_filter, $area_id_filter, $selected_pakan_filter, $selected_areas
    );
    $raw_avg_data = $this->visual->get_lingkungan_avg_monthly(
        $selected_year, $user_id_filter, $area_id_filter, $selected_pakan_filter, $selected_areas
    );
    
    // ... (Sisa fungsi, $process_100_percent_stacked_data, $process_multi_axis_chart, dll... TETAP SAMA) ...
    // 7. Proses Data STACKED (Lalat, Kotoran) - (Tidak Berubah)
    $process_100_percent_stacked_data = function($raw_data, $chart_key, $master_labels) {
        $categories = []; $pivot_data = []; $monthly_totals = [];
        foreach ($raw_data as $row) {
            if ($row['kategori_chart'] != $chart_key) continue; 
            $bulan = $row['bulan_tahun']; 
            $kat = $row['nilai']; 
            $jumlah = (int)$row['jumlah'];
            if (!in_array($kat, $categories)) $categories[] = $kat;
            if (!isset($pivot_data[$kat][$bulan])) $pivot_data[$kat][$bulan] = 0;
            $pivot_data[$kat][$bulan] += $jumlah;
            if (!isset($monthly_totals[$bulan])) $monthly_totals[$bulan] = 0;
            $monthly_totals[$bulan] += $jumlah;
        }
        if ($chart_key == 'lalat') { $order = ['Normal', 'Sedikit', 'Sedang', 'Banyak']; } 
        elseif ($chart_key == 'kotoran') { $order = ['Kering', 'Lembab', 'Basah', 'Normal']; } 
        else { $order = []; }
        usort($categories, function($a, $b) use ($order) {
            $pos_a = array_search($a, $order); $pos_b = array_search($b, $order);
            if ($pos_a === false && $pos_b === false) { return $a <=> $b; }
            if ($pos_a === false) return 1; if ($pos_b === false) return -1;
            return $pos_a <=> $pos_b;
        });
        $datasets = [];
        $colors = ['#28a745', '#ffc107', '#dc3545', '#007bff', '#6f42c1', '#fd7e14']; 
        $color_index = 0;
        foreach ($categories as $kat) {
            $dataset = ['label' => $kat, 'data' => [], 'raw_counts' => [], 'backgroundColor' => $colors[$color_index % count($colors)]];
            foreach ($master_labels as $bulan) { 
                $jumlah_kat = $pivot_data[$kat][$bulan] ?? 0;
                $total_bulan = $monthly_totals[$bulan] ?? 0;
                $persentase = ($total_bulan > 0) ? ($jumlah_kat / $total_bulan) * 100 : 0;
                $dataset['data'][] = round($persentase, 2); 
                $dataset['raw_counts'][] = $jumlah_kat;
            }
            $datasets[] = $dataset; $color_index++;
        }
        return ['labels' => $master_labels, 'datasets' => $datasets]; 
    };
    
    // 8. Proses Data AVERAGE (Suhu, Kelembapan, HI) - (Tidak Berubah)
    $process_multi_axis_chart = function($raw_data, $master_labels) {
        $pivot_data = [];
        foreach($raw_data as $row) {
            $pivot_data[$row['bulan_tahun']][$row['kategori_chart']] = (float)$row['rata_rata'];
        }
        
        $suhu_values = []; $kelembapan_values = []; $heat_index_values = []; 
        foreach ($master_labels as $bulan) { 
            $suhu_values[] = $pivot_data[$bulan]['suhu'] ?? null;
            $kelembapan_values[] = $pivot_data[$bulan]['kelembapan'] ?? null;
            $heat_index_values[] = $pivot_data[$bulan]['heat_index'] ?? null; 
        }
        
        return [
            'labels' => $master_labels,
            'datasets' => [
                [
                    'type' => 'bar', 'label' => 'Suhu (°C)',
                    'data' => $suhu_values,
                    'backgroundColor' => 'rgba(220, 53, 69, 0.7)', 'borderColor' => '#dc3545',
                    'yAxisID' => 'ySuhu', 'order' => 2 
                ],
                [
                    'type' => 'bar', 'label' => 'Kelembapan (%)',
                    'data' => $kelembapan_values,
                    'backgroundColor' => 'rgba(0, 123, 255, 0.7)', 'borderColor' => '#007bff',
                    'yAxisID' => 'yKelembapan', 'order' => 2 
                ],
                [ 
                    'type' => 'line', 'label' => 'Heat Index (F+RH)',
                    'data' => $heat_index_values,
                    'borderColor' => '#ffc107', 'backgroundColor' => '#ffc107',
                    'yAxisID' => 'yHeatIndex', 'tension' => 0.4,
                    'borderWidth' => 3, 'pointRadius' => 4, 'order' => 1 
                ]
            ]
        ];
    };

    // 9. Panggil semua fungsi proses
    $data['chart_lalat_data'] = $process_100_percent_stacked_data($raw_stacked_data, 'lalat', $master_labels);
    $data['chart_kotoran_data'] = $process_100_percent_stacked_data($raw_stacked_data, 'kotoran', $master_labels);
    $data['chart_suhu_kelembapan_hi_data'] = $process_multi_axis_chart($raw_avg_data, $master_labels);

    // 10. Load Views
    $this->load->view('templates/dash_h', $data);
    $this->load->view('visual_kondisi_lingkungan_view', $data); 
    $this->load->view('templates/dash_f', $data); 
}
  
    public function visual_vip_farms()
    {
        // 1. Standard User & Title Setup
        $token = $this->session->userdata('token');
        $data['user'] = $this->dash->getUserInfo($token)->row_array();
        $data["title"] = "Laporan Farm VIP (Grower)";

        // 2. Setup Filter Peran (Surveyor/Koordinator) - Tetap sama
        $user_id_filter = null;
        $area_id_filter = null; // Filter bawaan untuk koordinator
        $is_admin = true; // Asumsi admin defaultnya
        if (isset($data['user']['group_user'])) {
            $group = $data['user']['group_user'];
            if ($group === 'surveyor') {
                $user_id_filter = $data['user']['id_user'];
                $is_admin = false;
            } elseif ($group === 'koordinator') {
                if (isset($data['user']['master_area_id'])) {
                    $area_id_filter = $data['user']['master_area_id']; // Koord hanya lihat areanya
                }
                 $is_admin = false;
            }
        }

        // BARU: Ambil data untuk filter area
        $data['all_areas'] = $this->visual->get_all_areas();
        $selected_areas = []; // Default kosong

        // BARU: Proses filter area dari form (HANYA JIKA ADMIN)
        if ($is_admin && $this->input->post('area_filter')) { // Cek apakah form filter disubmit
            $selected_areas = $this->input->post('areas') ?? []; // Ambil area yg dicentang
        } elseif ($is_admin) {
             // Jika admin & tidak submit filter, tampilkan semua (ambil semua ID area)
            $selected_areas = array_column($data['all_areas'], 'master_area_id');
        }
        // Jika bukan admin, $selected_areas tetap array kosong (tidak dipakai)

        $data['selected_areas'] = $selected_areas; // Kirim ke view untuk re-check

        // 3. Panggil Model dengan filter area tambahan (jika admin)
        // Jika bukan admin, $selected_areas akan kosong dan tidak berpengaruh di model
        $data['vip_grower_farms'] = $this->visual->get_vip_grower_farms($user_id_filter, $area_id_filter, $selected_areas);

        // 4. Load Views
        $this->load->view('templates/dash_h', $data);
        $this->load->view('visual_vip_view', $data);
        $this->load->view('templates/dash_f', $data);
    }
    
    public function get_visit_history_for_farm()
    {       
        $farm_name = $this->input->post('farm_name');
        
        if (empty($farm_name)) {
            $response_data = [
                'status' => 'error', 
                'message' => 'Nama farm tidak boleh kosong.',
                'new_csrf_hash' => $this->security->get_csrf_hash() // Tetap kirim hash baru
            ];
            
            $this->output
                ->set_status_header(400) // Bad Request
                ->set_content_type('application/json')
                ->set_output(json_encode($response_data));
            return;
        }

        // Panggil fungsi model yang baru kita buat
        $visit_history = $this->visual->get_farm_visit_history($farm_name);

        // Siapkan data sukses
        $response_data = [
            'status' => 'success',
            'history' => $visit_history,
            'new_csrf_hash' => $this->security->get_csrf_hash() // Kirim hash baru
        ];

        // Kirim kembali data sebagai JSON
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response_data));
    }
    
    public function get_grower_visit_details()
    {
        $farm_name = $this->input->post('farm_name');
        $visit_id = $this->input->post('visit_id'); // Ini adalah timestamp lengkap

        if (empty($farm_name) || empty($visit_id)) {
            $response_data = [
                'status' => 'error', 
                'message' => 'Parameter tidak lengkap (nama farm atau ID visit).',
                'new_csrf_hash' => $this->security->get_csrf_hash()
            ];
            
            $this->output
                ->set_status_header(400) 
                ->set_content_type('application/json')
                ->set_output(json_encode($response_data));
            return;
        }

        // Panggil fungsi model BARU yang tadi kita buat
        $detail_data = $this->visual->get_grower_visit_detail($farm_name, $visit_id);

        if (empty($detail_data)) {
            $response_data = [
                'status' => 'error', 
                'message' => 'Data detail tidak ditemukan.',
                'new_csrf_hash' => $this->security->get_csrf_hash()
            ];
            
            $this->output
                ->set_status_header(404) // Not Found
                ->set_content_type('application/json')
                ->set_output(json_encode($response_data));
            return;
        }
        $response_data = [
            'status' => 'success',
            'details' => $detail_data,
            'new_csrf_hash' => $this->security->get_csrf_hash()
        ];

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response_data));
    }

    public function visual_harga($jenis_komoditas_usang = 'telur')
    {
        $data['user'] = $this->dash->getUserInfo($this->session->userdata('token'))->row_array();
        $data["title"] = "Laporan Harga Komoditas Utama";
        
        // Logika $selected_year DIHAPUS

        // 1. Ambil DATA HARGA TERAKHIR (Stat Cards) - (Tidak berubah)
        $data['latest_telur'] = $this->visual->get_harga_terbaru_by_jenis('harga_jual_telur_layer');
        $data['latest_puyuh'] = $this->visual->get_harga_terbaru_by_jenis('harga_telur_puyuh');
        $data['latest_bebek'] = $this->visual->get_harga_terbaru_by_jenis('harga_telur_bebek');
        $data['latest_lb'] = $this->visual->get_harga_terbaru_by_jenis('harga_live_bird');
        $data['latest_afkir'] = $this->visual->get_harga_terbaru_by_jenis('harga_afkir');

        // 2. Ambil DATA BULANAN (untuk Grafik)
        // Kita panggil helper dengan $tahun = null untuk mengambil SEMUA tahun
        $raw_telur = $this->_get_monthly_data_by_commodity('telur', null);
        $raw_puyuh = $this->_get_monthly_data_by_commodity('telur_puyuh', null);
        $raw_bebek = $this->_get_monthly_data_by_commodity('telur_bebek', null);
        $raw_lb    = $this->_get_monthly_data_by_commodity('live_bird', null);
        $raw_afkir = $this->_get_monthly_data_by_commodity('afkir', null);

        // 3. Proses data menjadi format multi-tahun (labels + datasets)
        // Fungsi _process_monthly_chart_data akan kita ubah total
        $data['chart_telur'] = $this->_process_monthly_chart_data($raw_telur);
        $data['chart_puyuh'] = $this->_process_monthly_chart_data($raw_puyuh);
        $data['chart_bebek'] = $this->_process_monthly_chart_data($raw_bebek);
        $data['chart_lb']    = $this->_process_monthly_chart_data($raw_lb);
        $data['chart_afkir'] = $this->_process_monthly_chart_data($raw_afkir);

        // 4. Load View
        $this->load->view('templates/dash_h', $data);
        $this->load->view('visual_harga_view', $data); 
        $this->load->view('templates/dash_f', $data);
    }
    
    // *** FUNGSI INI DIPERBAIKI: 'fill' => false ***
    private function _process_monthly_chart_data($raw_data)
    {
        // Label sumbu X sekarang hanya nama bulan
        $labels_final = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        // 1. Pivot data mentah ke [Tahun][Bulan]
        $pivot_data = [];
        $years_found = [];
        foreach ($raw_data as $row) {
            $tahun = (int)$row['tahun'];
            $bulan_int = (int)$row['bulan'];
            $pivot_data[$tahun][$bulan_int] = (float)$row['nilai_rata_rata'];
            if (!in_array($tahun, $years_found)) {
                $years_found[] = $tahun;
            }
        }
        // Urutkan tahun, dari terbaru ke terlama
        rsort($years_found); 
        
        // 2. Siapkan "datasets" (garis) untuk setiap tahun yang ditemukan
        $datasets = [];
        $colors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6f42c1'];
        $color_index = 0;
        
        foreach ($years_found as $tahun) {
            $dataset_data = []; // Data untuk 12 bulan di tahun ini
            
            // Isi array data 12 bulan
            for ($m = 1; $m <= 12; $m++) {
                // Ambil data jika ada, jika tidak, isi 'null' (agar garis terputus)
                $dataset_data[] = $pivot_data[$tahun][$m] ?? null;
            }
            
            $color = $colors[$color_index % count($colors)];
            
            // Tambahkan 1 garis (dataset) untuk tahun ini
            $datasets[] = [
                'label' => (string)$tahun, // Label legend (mis: "2024")
                'data' => $dataset_data,
                'borderColor' => $color,
                'backgroundColor' => $color . '40', // Opacity 25% (lebih tebal)
                'fill' => false, // *** DIUBAH MENJADI FALSE ***
                'tension' => 0.4,
                'borderWidth' => 3,
                'pointRadius' => 4,
                'pointHoverRadius' => 6,
                'pointBackgroundColor' => $color,
                'pointBorderColor' => '#fff',
                'pointBorderWidth' => 2
            ];
            $color_index++;
        }
        
        // 3. Kembalikan struktur data lengkap untuk Chart.js
        // Kita encode sebagai JSON agar aman saat dicetak di Javascript
        return json_encode([
            'labels' => $labels_final, 
            'datasets' => $datasets
        ]);
    }

    // *** FUNGSI INI DIPERBAIKI: 'fill' => false ***
    private function _process_comparison_chart_data($raw_data1, $label1, $raw_data2, $label2)
    {
        $labels_final = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        $pivot_data = [];
        $years_found = [];

        // 1. Pivot data mentah 1 (misal: HPP)
        foreach ($raw_data1 as $row) {
            $tahun = (int)$row['tahun'];
            $bulan_int = (int)$row['bulan'];
            $pivot_data[$tahun][$bulan_int][0] = (float)$row['nilai_rata_rata'];
            if (!in_array($tahun, $years_found)) {
                $years_found[] = $tahun;
            }
        }
        
        // 2. Pivot data mentah 2 (misal: Telur)
        foreach ($raw_data2 as $row) {
            $tahun = (int)$row['tahun'];
            $bulan_int = (int)$row['bulan'];
            $pivot_data[$tahun][$bulan_int][1] = (float)$row['nilai_rata_rata'];
            if (!in_array($tahun, $years_found)) {
                $years_found[] = $tahun;
            }
        }
        
        rsort($years_found); // Urutkan tahun, dari terbaru ke terlama
        
        $datasets = [];
        $colors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6f42c1'];
        $color_index = 0;
        
        foreach ($years_found as $tahun) {
            $dataset_data_1 = []; // Data HPP
            $dataset_data_2 = []; // Data Telur
            
            for ($m = 1; $m <= 12; $m++) {
                $dataset_data_1[] = $pivot_data[$tahun][$m][0] ?? null;
                $dataset_data_2[] = $pivot_data[$tahun][$m][1] ?? null;
            }
            
            $color = $colors[$color_index % count($colors)];
            
            // Tambahkan dataset untuk Data 1 (misal: HPP)
            $datasets[] = [
                'label' => $tahun . ' - ' . $label1,
                'data' => $dataset_data_1,
                'borderColor' => $color,
                'backgroundColor' => $color . '40',
                'fill' => false, // *** DIUBAH MENJADI FALSE ***
                'tension' => 0.4,
                'borderWidth' => 3,
                'pointRadius' => 4,
                'pointHoverRadius' => 6,
                'pointBackgroundColor' => $color,
                'pointBorderColor' => '#fff',
                'pointBorderWidth' => 2
            ];
            
            // Tambahkan dataset untuk Data 2 (misal: Telur)
            // Dibuat putus-putus (borderDash) agar beda
            $datasets[] = [
                'label' => $tahun . ' - ' . $label2,
                'data' => $dataset_data_2,
                'borderColor' => $color, // Warna sama, tapi style beda
                'backgroundColor' => $color . '10', 
                'fill' => false, // (Sudah false)
                'borderDash' => [5, 5], // Garis putus-putus
                'tension' => 0.4,
                'borderWidth' => 3,
                'pointRadius' => 4,
                'pointHoverRadius' => 6,
                'pointBackgroundColor' => $color,
                'pointBorderColor' => '#fff',
                'pointBorderWidth' => 2
            ];
            
            $color_index++;
        }
        
        return json_encode([
            'labels' => $labels_final, 
            'datasets' => $datasets
        ]);
    }

    public function visual_harga_compare()
    {
        $data['user'] = $this->dash->getUserInfo($this->session->userdata('token'))->row_array();
        $data["title"] = "Dashboard Analisis Harga";

        // 1. Ambil Data Stat Cards (Harga Terakhir)
        $data['stat_jagung'] = $this->visual->get_harga_terbaru_by_jenis('harga_jagung');
        $data['stat_katul'] = $this->visual->get_harga_terbaru_by_jenis('harga_katul');
        $data['stat_pakan_layer'] = $this->visual->get_harga_terbaru_by_jenis('pakan_komplit_layer'); // Sesuai mapping Anda
        $data['stat_pakan_broiler'] = $this->visual->get_harga_pakan_broiler_hari_ini(); // Fungsi khusus
        $data['stat_konsentrat'] = $this->visual->get_harga_terbaru_by_jenis('harga_konsentrat_layer');
        $data['stat_hpp_konsentrat'] = $this->visual->get_harga_terbaru_by_jenis('hpp_konsentrat_layer');
        $data['stat_hpp_komplit'] = $this->visual->get_harga_terbaru_by_jenis('hpp_komplit_layer');
        $data['stat_hpp_broiler'] = $this->visual->get_harga_terbaru_by_jenis('hpp_broiler');

        // 2. Ambil Data Bulanan (Semua Tahun)
        $raw_telur = $this->_get_monthly_data_by_commodity('telur', null);
        $raw_lb = $this->_get_monthly_data_by_commodity('live_bird', null);
        $raw_jagung = $this->_get_monthly_data_by_commodity('jagung', null);
        $raw_katul = $this->_get_monthly_data_by_commodity('katul', null);
        $raw_hpp_konsentrat = $this->_get_monthly_data_by_commodity('hpp_konsentrat_layer', null);
        $raw_hpp_komplit = $this->_get_monthly_data_by_commodity('hpp_komplit_layer', null);
        $raw_hpp_broiler = $this->_get_monthly_data_by_commodity('hpp_broiler', null);

        // 3. Proses Data untuk Chart
        
        // Chart 1: HPP Konsentrat vs Telur
        $data['chart_hpp_konsentrat_vs_telur'] = $this->_process_comparison_chart_data(
            $raw_hpp_konsentrat, "HPP (Konsentrat)",
            $raw_telur, "Harga Telur"
        );
        
        // Chart 2: HPP Komplit vs Telur
        $data['chart_hpp_komplit_vs_telur'] = $this->_process_comparison_chart_data(
            $raw_hpp_komplit, "HPP (Komplit)",
            $raw_telur, "Harga Telur"
        );
        
        // Chart 3: HPP Broiler vs Live Bird
        $data['chart_hpp_broiler_vs_lb'] = $this->_process_comparison_chart_data(
            $raw_hpp_broiler, "HPP Broiler",
            $raw_lb, "Harga Live Bird"
        );
        
        // Chart 4: Trend Jagung (pakai helper lama)
        $data['chart_jagung'] = $this->_process_monthly_chart_data($raw_jagung);
        
        // Chart 5: Trend Katul (pakai helper lama)
        $data['chart_katul'] = $this->_process_monthly_chart_data($raw_katul);

        // 4. Load View
        $this->load->view('templates/dash_h', $data);
        $this->load->view('visual_harga_compare_view', $data); // Ini file view baru
        $this->load->view('templates/dash_f', $data);
    }
}
