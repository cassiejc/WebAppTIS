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
    
    public function visual_data_kunjungan()
    {
        $token = $this->session->userdata('token');
        $data['user'] = $this->dash->getUserInfo($token)->row_array();

        if (isset($data['user']['group_user']) && $data['user']['group_user'] == 'sales') {
            $this->session->set_flashdata('message', '<div class="alert alert-danger">Akses ditolak! Menu ini tidak tersedia untuk Sales.</div>');
            redirect('Dashboard_new');
            return;
        }

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
        
        $filter_type = $this->input->post('filter_type') ?? 'range'; 

        $default_start = date('Y-m');
        $default_end = date('Y-m');
        $default_quarter = 'Q' . ceil(date('n') / 3);
        $default_quarter_year = date('Y');

        $query_start_date = $default_start;
        $query_end_date = $default_end;

        if ($this->input->post()) {
            if ($filter_type == 'range') {
                $query_start_date = $this->input->post('start_date');
                $query_end_date = $this->input->post('end_date');
                
                $data['selected_quarter'] = $this->input->post('quarter') ?? $default_quarter;
                $data['selected_quarter_year'] = $this->input->post('quarter_year') ?? $default_quarter_year;

            } elseif ($filter_type == 'quarter') {
                $selected_quarter = $this->input->post('quarter');
                $selected_quarter_year = $this->input->post('quarter_year');
                
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
                
                $data['selected_quarter'] = $selected_quarter;
                $data['selected_quarter_year'] = $selected_quarter_year;
            }
        } else {
            $data['selected_quarter'] = $default_quarter;
            $data['selected_quarter_year'] = $default_quarter_year;
        }

        $data['selected_start'] = ($filter_type == 'range') ? $query_start_date : ($this->input->post('start_date') ?? $default_start);
        $data['selected_end'] = ($filter_type == 'range') ? $query_end_date : ($this->input->post('end_date') ?? $default_end);
        $data['filter_type'] = $filter_type;

        $data['performance_data'] = $this->visual->get_surveyor_performance($query_start_date, $query_end_date, $user_id_filter, $area_id_filter);
        $data['area_performance_data'] = $this->visual->get_area_performance($query_start_date, $query_end_date, $data['user']);
        $visit_breakdown_raw = $this->visual->get_visit_breakdown($query_start_date, $query_end_date, $user_id_filter, $area_id_filter); 
        
        $sample_count = $this->visual->get_sample_count_by_range($query_start_date, $query_end_date, $user_id_filter, $area_id_filter);
        $seminar_count = $this->visual->get_seminar_count_by_range($query_start_date, $query_end_date, $user_id_filter, $area_id_filter);
        $new_customer_count = $this->visual->get_new_customer_count_by_range($query_start_date, $query_end_date, $user_id_filter, $area_id_filter);
        
        $data['visit_details_table'] = $this->visual->get_all_visit_details($query_start_date, $query_end_date, $user_id_filter, $area_id_filter);
        
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
            // 'Grower' => 'Demoplot DOC',
            'CRM Broiler' => 'CRM Broiler'
        ];

        $grouped_totals = [];
        $processed_breakdown = []; 

        foreach ($data_to_group as $item) { 
            $raw_kategori = $item['kategori'];
            $jumlah = (int)$item['jumlah_visit'];

            if($raw_kategori === 'CRM Broiler') {
                $processed_breakdown[] = $item;
                continue;
            }

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

        $data['vip_grower_farms'] = $this->visual->get_vip_grower_farms($user_id_filter, $area_id_filter);
        
        $data['js_start_date'] = $query_start_date;
        $data['js_end_date'] = $query_end_date;

        $this->load->view('templates/dash_h', $data);
        $this->load->view('visual_data_kunjungan', $data); 
        $this->load->view('templates/dash_f', $data);
    }

    public function get_data_for_surveyor_ajax()
    {
        $user_id = $this->input->post('user_id');
        $selected_start = $this->input->post('start_date');
        $selected_end = $this->input->post('end_date');
        if (empty($user_id) || empty($selected_start) || empty($selected_end)) {
            $this->output->set_status_header(400)->set_output(json_encode(['error' => 'Parameter tidak valid.']));
            return;
        }

        // 1. Dapatkan data Performa Area untuk user tersebut
        $user_info = $this->visual->get_user_info_by_id($user_id); 
        $area_performance_data = $this->visual->get_area_performance($selected_start, $selected_end, $user_info);

        // 2. Dapatkan data Komposisi Visit untuk user tersebut
        $visit_breakdown_raw = $this->visual->get_visit_breakdown($selected_start, $selected_end, $user_id);
        $sample_count = $this->visual->get_sample_count_by_range($selected_start, $selected_end, $user_id);
        $seminar_count = $this->visual->get_seminar_count_by_range($selected_start, $selected_end, $user_id);
        $new_customer_count = $this->visual->get_new_customer_count_by_range($selected_start, $selected_end, $user_id);
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
            // 'Grower' => 'Demoplot DOC'
        ];

        $grouped_totals = []; $processed_breakdown = [];
        foreach ($data_to_group as $item) {
            $raw_kategori = $item['kategori']; $jumlah = (int)$item['jumlah_visit'];

            if ($raw_kategori === 'CRM Broiler') {
                $processed_breakdown[] = $item; 
                continue;
            }

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
   
    public function get_surveyors_for_area_ajax()
    {
        $area_id = $this->input->post('area_id');
        $selected_start = $this->input->post('start_date');
        $selected_end = $this->input->post('end_date');

        if (empty($area_id) || empty($selected_start) || empty($selected_end)) {
            $this->output->set_status_header(400)->set_output(json_encode(['error' => 'Parameter tidak valid.']));
            return;
        }

        $surveyor_data = $this->visual->get_surveyor_performance(
            $selected_start, 
            $selected_end, 
            null, 
            $area_id
        );

        $user_id_filter = null; 
        
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
            // 'Grower' => 'Demoplot DOC'
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

        $response = [
            'status' => 'success',
            'surveyor_data' => $surveyor_data,
            'composition_data' => $composition_data 
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
                
        $master_labels_bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $master_labels_query = [];
        for ($i = 1; $i <= 12; $i++) {
            $master_labels_query[] = date('M Y', mktime(0, 0, 0, $i, 1, $selected_year));
        }

        $raw_count_data = $this->visual->get_kasus_breakdown_count($selected_year, $user_id_filter); 
        
        $kategori_chart = []; 
        $pivot_chart_data = []; 

        foreach ($raw_count_data as $row) {
            $bulan_tahun = $row['bulan_tahun']; 
            $kat = $row['kategori_kasus'];
            $jumlah = (int)$row['jumlah_kasus']; 

            if (!in_array($kat, $kategori_chart)) $kategori_chart[] = $kat;
            
            $pivot_chart_data[$kat][$bulan_tahun] = $jumlah;
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
            
            foreach ($master_labels_query as $bulan_query) {
                $dataset['data'][] = $pivot_chart_data[$kat][$bulan_query] ?? 0;
            }
            
            $datasets[] = $dataset;
            $color_index++;
        }
        
        $data['chart_labels'] = json_encode($master_labels_bulan); 
        $data['chart_datasets'] = json_encode($datasets); 
        $raw_pivot_data = $this->visual->get_kasus_pivot_by_area($selected_year, $user_id_filter);
        $pivot_table_data = [];
        $categories_table = [];
        foreach ($raw_pivot_data as $row) {
            $area = $row['nama_area'];
            $kategori = $row['kategori_kasus'];
            $jumlah = (int)$row['jumlah'];
            $area_id = $row['master_area_id'] ?? 0;
            if (!isset($pivot_table_data[$area])) {
                $pivot_table_data[$area] = ['nama_area' => $area, 'master_area_id' => $area_id];
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
        
        $all_years_raw = $this->visual->get_all_harga_years();
        $data['all_years'] = $all_years_raw ? array_column($all_years_raw, 'tahun') : [date('Y')];

        $default_selected_years = [date('Y')];
        $selected_years = $this->input->post('tahun') ?? $default_selected_years;

        if ($this->input->method() == 'post' && $this->input->post('submit_filter') && !$this->input->post('tahun')) {
            $selected_years = [];
        }
        $data['selected_years'] = $selected_years;
        
        if ($this->input->method() == 'post') {
            $selected_tipe_ternak = $this->input->post('tipe_ternak');
        } else {
            $selected_tipe_ternak = 'Layer'; 
        }
        
        if (!empty($selected_years)) {
            $data['farm_capacity_list'] = $this->visual->get_farm_capacity_by_year($selected_tipe_ternak, $selected_years);
            
            $raw_chart_data = $this->visual->get_monthly_empty_capacity($selected_tipe_ternak, $selected_years);
            
            $data['chart_labels_monthly'] = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            
            $datasets = [];
            $colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69'];
            $color_index = 0;
            
            $data_by_year = [];
            foreach ($raw_chart_data as $row) {
                $tahun = $row['tahun'];
                $bulan = (int)$row['bulan']; 
                $total_kosong = (int)$row['total_kosong'];
                
                if (!isset($data_by_year[$tahun])) {
                    $data_by_year[$tahun] = array_fill(0, 12, 0); 
                }
                
                $data_by_year[$tahun][$bulan - 1] = $total_kosong; 
            }
            
            foreach ($data_by_year as $tahun => $monthly_data) {
                $datasets[] = [
                    'label' => 'Tahun ' . $tahun,
                    'data' => $monthly_data,
                    'backgroundColor' => $colors[$color_index % count($colors)],
                    'borderColor' => $colors[$color_index % count($colors)],
                    'borderWidth' => 2
                ];
                $color_index++;
            }
            
            $data['chart_data_monthly'] = json_encode($datasets);
            
        } else {
            $data['farm_capacity_list'] = [];
            $data['chart_data_monthly'] = json_encode([]); 
            $data['chart_labels_monthly'] = [];
        }

        $data['all_tipe_ternak'] = $this->visual->get_all_tipe_ternak();
        $data['selected_tipe_ternak'] = $selected_tipe_ternak;

        $this->load->view('templates/dash_h', $data);
        $this->load->view('visual_kandang_kosong_view', $data);
        $this->load->view('templates/dash_f', $data);
    }
    
    private function _get_monthly_data_by_commodity($jenis_komoditas, $tahun = null)
    {
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
                return $this->visual->get_harga_konsentrat_layer_bulanan_chart($tahun);
            case 'pakan_komplit_layer':
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
    
    public function visual_kondisi_lingkungan()
    {
        $token = $this->session->userdata('token');
        $data['user'] = $this->dash->getUserInfo($token)->row_array();
        $data["title"] = "Laporan Kondisi Lingkungan";
        $user_id_login = $data['user']['id_user']; 
        $is_admin = true; 
        if (isset($data['user']['group_user'])) {
            $group = $data['user']['group_user'];
            if ($group === 'surveyor' || $group === 'koordinator') {
                $is_admin = false;
            }
        }
        $data['is_admin'] = $is_admin;

        $selected_year = $this->input->post('tahun') ?: date('Y');
        $data['selected_year'] = $selected_year;
        
        $master_labels = [];
        for ($m = 1; $m <= 12; $m++) {
            $master_labels[] = date('M', mktime(0, 0, 0, $m, 1, $selected_year));
        }
        
        $data['all_areas'] = $this->visual->get_all_areas();
        $selected_areas = [];

        if ($is_admin) {
            if ($this->input->method() == 'post') {
                $selected_areas = $this->input->post('areas') ?? [];
            } else {
                $selected_areas = array_column($data['all_areas'], 'master_area_id');
            }
        }
        $data['selected_areas'] = $selected_areas;
        
        $raw_stacked_data = $this->visual->get_kondisi_lingkungan_monthly(
            $selected_year, 
            $user_id_login, 
            $selected_areas
        );

        $raw_avg_data = $this->visual->get_lingkungan_avg_monthly(
            $selected_year, 
            $user_id_login, 
            $selected_areas
        );
        
        $data_by_pakan = [];
        if (!empty($raw_stacked_data)) {
            foreach ($raw_stacked_data as $row) {
                $pakan = $row['jenis_pakan'] ?? 'Unknown';
                if (!isset($data_by_pakan[$pakan])) {
                    $data_by_pakan[$pakan] = [];
                }
                $data_by_pakan[$pakan][] = $row;
            }
        }
        
        $process_100_percent_stacked_data = function($raw_data, $chart_key, $master_labels) {
            $categories = []; 
            $pivot_data = []; 
            $monthly_totals = [];
            
            if (empty($raw_data)) return ['labels' => $master_labels, 'datasets' => []];

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
            
            if ($chart_key == 'lalat') { 
                $order = ['Normal', 'Sedikit', 'Sedang', 'Banyak']; 
            } elseif ($chart_key == 'kotoran') { 
                $order = ['Kering', 'Lembab', 'Basah', 'Normal']; 
            } else { 
                $order = []; 
            }
            
            usort($categories, function($a, $b) use ($order) {
                $pos_a = array_search($a, $order); 
                $pos_b = array_search($b, $order);
                if ($pos_a === false && $pos_b === false) { return $a <=> $b; }
                if ($pos_a === false) return 1; 
                if ($pos_b === false) return -1;
                return $pos_a <=> $pos_b;
            });
            
            $datasets = [];
            $colors = ['#28a745', '#ffc107', '#dc3545', '#007bff', '#6f42c1', '#fd7e14']; 
            $color_index = 0;
            
            foreach ($categories as $kat) {
                $dataset = [
                    'label' => $kat, 
                    'data' => [], 
                    'raw_counts' => [], 
                    'backgroundColor' => $colors[$color_index % count($colors)]
                ];
                foreach ($master_labels as $bulan) { 
                    $jumlah_kat = $pivot_data[$kat][$bulan] ?? 0;
                    $total_bulan = $monthly_totals[$bulan] ?? 0;
                    $persentase = ($total_bulan > 0) ? ($jumlah_kat / $total_bulan) * 100 : 0;
                    $dataset['data'][] = round($persentase, 2); 
                    $dataset['raw_counts'][] = $jumlah_kat;
                }
                $datasets[] = $dataset; 
                $color_index++;
            }
            
            return ['labels' => $master_labels, 'datasets' => $datasets]; 
        };

        $data['charts_by_pakan'] = [];
        foreach ($data_by_pakan as $pakan_name => $pakan_data) {
            $lalat_chart = $process_100_percent_stacked_data($pakan_data, 'lalat', $master_labels);
            $kotoran_chart = $process_100_percent_stacked_data($pakan_data, 'kotoran', $master_labels);
            
            $data['charts_by_pakan'][] = [
                'pakan_name' => $pakan_name,
                'lalat_data' => $lalat_chart,
                'kotoran_data' => $kotoran_chart
            ];
        }
        
        $process_multi_axis_chart = function($raw_data, $master_labels) {
            if (empty($raw_data)) return ['labels' => $master_labels, 'datasets' => []];

            $pivot_data = [];
            foreach($raw_data as $row) {
                $pivot_data[$row['bulan_tahun']][$row['kategori_chart']] = (float)$row['rata_rata'];
            }
            
            $suhu_values = []; 
            $kelembapan_values = []; 
            $heat_index_values = []; 
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
                        'backgroundColor' => 'rgba(220, 53, 69, 0.7)', 
                        'borderColor' => '#dc3545',
                        'yAxisID' => 'ySuhu', 'order' => 2 
                    ],
                    [
                        'type' => 'bar', 'label' => 'Kelembapan (%)',
                        'data' => $kelembapan_values,
                        'backgroundColor' => 'rgba(0, 123, 255, 0.7)', 
                        'borderColor' => '#007bff',
                        'yAxisID' => 'yKelembapan', 'order' => 2 
                    ],
                    [ 
                        'type' => 'line', 'label' => 'Heat Index (F+RH)',
                        'data' => $heat_index_values,
                        'borderColor' => '#ffc107', 
                        'backgroundColor' => '#ffc107',
                        'yAxisID' => 'yHeatIndex', 
                        'tension' => 0.4,
                        'borderWidth' => 3, 
                        'pointRadius' => 4, 
                        'order' => 1 
                    ]
                ]
            ];
        };

        $data['chart_suhu_kelembapan_hi_data'] = $process_multi_axis_chart($raw_avg_data, $master_labels);

        $this->load->view('templates/dash_h', $data);
        $this->load->view('visual_kondisi_lingkungan_view', $data); 
        $this->load->view('templates/dash_f', $data); 
    }
  
    public function visual_vip_farms()
    {
        $token = $this->session->userdata('token');
        $data['user'] = $this->dash->getUserInfo($token)->row_array();
        $data["title"] = "Laporan Farm VIP (Grower)";

        $user_id_filter = null;
        $area_id_filter = null; 
        $is_admin = true; 
        if (isset($data['user']['group_user'])) {
            $group = $data['user']['group_user'];
            if ($group === 'surveyor') {
                $user_id_filter = $data['user']['id_user'];
                $is_admin = false;
            } elseif ($group === 'koordinator') {
                if (isset($data['user']['master_area_id'])) {
                    $area_id_filter = $data['user']['master_area_id']; 
                }
                 $is_admin = false;
            }
        }

        $data['all_areas'] = $this->visual->get_all_areas();
        $selected_areas = []; 
        if ($is_admin && $this->input->post('area_filter')) { 
            $selected_areas = $this->input->post('areas') ?? []; 
        } elseif ($is_admin) {
            $selected_areas = array_column($data['all_areas'], 'master_area_id');
        }
        $data['selected_areas'] = $selected_areas; 

        $data['vip_grower_farms'] = $this->visual->get_vip_grower_farms($user_id_filter, $area_id_filter, $selected_areas);

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
                'new_csrf_hash' => $this->security->get_csrf_hash() 
            ];
            
            $this->output
                ->set_status_header(400) 
                ->set_content_type('application/json')
                ->set_output(json_encode($response_data));
            return;
        }

        $visit_history = $this->visual->get_farm_visit_history($farm_name);

        $response_data = [
            'status' => 'success',
            'history' => $visit_history,
            'new_csrf_hash' => $this->security->get_csrf_hash() 
        ];

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response_data));
    }
    
    public function get_grower_visit_details()
    {
        $farm_name = $this->input->post('farm_name');
        $visit_id = $this->input->post('visit_id'); 

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

        $detail_data = $this->visual->get_grower_visit_detail($farm_name, $visit_id);

        if (empty($detail_data)) {
            $response_data = [
                'status' => 'error', 
                'message' => 'Data detail tidak ditemukan.',
                'new_csrf_hash' => $this->security->get_csrf_hash()
            ];
            
            $this->output
                ->set_status_header(404) 
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
                
        $all_years_raw = $this->visual->get_all_harga_years();
        $data['all_years'] = $all_years_raw ? array_column($all_years_raw, 'tahun') : [date('Y')];

        $default_selected_years = array_slice($data['all_years'], 0, 3);
        $selected_years = $this->input->post('tahun') ?? $default_selected_years;

        if ($this->input->method() == 'post' && $this->input->post('submit_filter') && !$this->input->post('tahun')) {
            $selected_years = [];
        }
        $data['selected_years'] = $selected_years; 
        $data['latest_telur'] = $this->visual->get_harga_terbaru_by_jenis('harga_jual_telur_layer');
        $data['latest_puyuh'] = $this->visual->get_harga_terbaru_by_jenis('harga_telur_puyuh');
        $data['latest_bebek'] = $this->visual->get_harga_terbaru_by_jenis('harga_telur_bebek');
        $data['latest_lb'] = $this->visual->get_harga_terbaru_by_jenis('harga_live_bird');
        $data['latest_afkir'] = $this->visual->get_harga_terbaru_by_jenis('harga_afkir');
        $raw_telur = $this->_get_monthly_data_by_commodity('telur', null);
        $raw_puyuh = $this->_get_monthly_data_by_commodity('telur_puyuh', null);
        $raw_bebek = $this->_get_monthly_data_by_commodity('telur_bebek', null);
        $raw_lb    = $this->_get_monthly_data_by_commodity('live_bird', null);
        $raw_afkir = $this->_get_monthly_data_by_commodity('afkir', null);

        $data['chart_telur'] = $this->_process_monthly_chart_data($raw_telur, $selected_years);
        $data['chart_puyuh'] = $this->_process_monthly_chart_data($raw_puyuh, $selected_years);
        $data['chart_bebek'] = $this->_process_monthly_chart_data($raw_bebek, $selected_years);
        $data['chart_lb']    = $this->_process_monthly_chart_data($raw_lb, $selected_years);
        $data['chart_afkir'] = $this->_process_monthly_chart_data($raw_afkir, $selected_years);

        $this->load->view('templates/dash_h', $data);
        $this->load->view('visual_harga_view', $data); 
        $this->load->view('templates/dash_f', $data);
    }
    
    private function _process_monthly_chart_data($raw_data, $selected_years = [])
    {
        $labels_final = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        $filtered_raw_data = $raw_data;
        if (!empty($selected_years)) {
            $filtered_raw_data = array_filter($raw_data, function($row) use ($selected_years) {
                return in_array((string)$row['tahun'], $selected_years, true);
            });
        }

        $pivot_data = [];
        $years_found = [];
        foreach ($filtered_raw_data as $row) { 
            $tahun = (int)$row['tahun'];
            $bulan_int = (int)$row['bulan'];
            $pivot_data[$tahun][$bulan_int] = (float)$row['nilai_rata_rata'];
            if (!in_array($tahun, $years_found)) {
                $years_found[] = $tahun;
            }
        }
        rsort($years_found); 
        
        $datasets = [];
        $colors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6f42c1'];
        $color_index = 0;
        
        foreach ($years_found as $tahun) {
            $dataset_data = [];
            for ($m = 1; $m <= 12; $m++) {
                $dataset_data[] = $pivot_data[$tahun][$m] ?? null;
            }
            
            $color = $colors[$color_index % count($colors)];
            
            $datasets[] = [
                'label' => (string)$tahun,
                'data' => $dataset_data,
                'borderColor' => $color,
                'backgroundColor' => $color . '40',
                'fill' => false,
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

    private function _process_comparison_chart_data($raw_data1, $label1, $raw_data2, $label2, $selected_years = [])
    {
        $labels_final = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        $filtered_data1 = $raw_data1;
        if (!empty($selected_years)) {
            $filtered_data1 = array_filter($raw_data1, function($row) use ($selected_years) {
                return in_array((string)$row['tahun'], $selected_years, true);
            });
        }
        $filtered_data2 = $raw_data2;
        if (!empty($selected_years)) {
            $filtered_data2 = array_filter($raw_data2, function($row) use ($selected_years) {
                return in_array((string)$row['tahun'], $selected_years, true);
            });
        }
        
        $pivot_data = [];
        $years_found = [];

        foreach ($filtered_data1 as $row) {
            $tahun = (int)$row['tahun'];
            $bulan_int = (int)$row['bulan'];
            $pivot_data[$tahun][$bulan_int][0] = (float)$row['nilai_rata_rata'];
            if (!in_array($tahun, $years_found)) {
                $years_found[] = $tahun;
            }
        }
        
        foreach ($filtered_data2 as $row) {
            $tahun = (int)$row['tahun'];
            $bulan_int = (int)$row['bulan'];
            $pivot_data[$tahun][$bulan_int][1] = (float)$row['nilai_rata_rata'];
            if (!in_array($tahun, $years_found)) {
                $years_found[] = $tahun;
            }
        }
        
        rsort($years_found); 
        
        $datasets = [];
        $colors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6f42c1'];
        $color_index = 0;
        
        foreach ($years_found as $tahun) {
            $dataset_data_1 = []; 
            $dataset_data_2 = []; 
            
            for ($m = 1; $m <= 12; $m++) {
                $dataset_data_1[] = $pivot_data[$tahun][$m][0] ?? null;
                $dataset_data_2[] = $pivot_data[$tahun][$m][1] ?? null;
            }
            
            $color = $colors[$color_index % count($colors)];
            
            $datasets[] = [
                'label' => $tahun . ' - ' . $label1,
                'data' => $dataset_data_1,
                'borderColor' => $color,
                'backgroundColor' => $color . '40',
                'fill' => false, 
                'tension' => 0.4,
                'borderWidth' => 3,
                'pointRadius' => 4,
                'pointHoverRadius' => 6,
                'pointBackgroundColor' => $color,
                'pointBorderColor' => '#fff',
                'pointBorderWidth' => 2
            ];
            
            $datasets[] = [
                'label' => $tahun . ' - ' . $label2,
                'data' => $dataset_data_2,
                'borderColor' => $color, 
                'backgroundColor' => $color . '10', 
                'fill' => false,
                'borderDash' => [5, 5], 
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
        
        $all_years_raw = $this->visual->get_all_harga_years();
        $data['all_years'] = $all_years_raw ? array_column($all_years_raw, 'tahun') : [date('Y')];

        $default_selected_years = array_slice($data['all_years'], 0, 3);
        $selected_years = $this->input->post('tahun') ?? $default_selected_years;

        if ($this->input->method() == 'post' && $this->input->post('submit_filter') && !$this->input->post('tahun')) {
            $selected_years = [];
        }
        $data['selected_years'] = $selected_years; 
        
        $data['stat_jagung'] = $this->visual->get_harga_terbaru_by_jenis('harga_jagung');
        $data['stat_katul'] = $this->visual->get_harga_terbaru_by_jenis('harga_katul');
        $data['stat_pakan_layer'] = $this->visual->get_harga_terbaru_by_jenis('pakan_komplit_layer'); 
        $data['stat_pakan_broiler'] = $this->visual->get_harga_pakan_broiler_hari_ini();
        $data['stat_konsentrat'] = $this->visual->get_harga_terbaru_by_jenis('harga_konsentrat_layer');
        $data['stat_hpp_konsentrat'] = $this->visual->get_harga_terbaru_by_jenis('hpp_konsentrat_layer');
        $data['stat_hpp_komplit'] = $this->visual->get_harga_terbaru_by_jenis('hpp_komplit_layer');
        $data['stat_hpp_broiler'] = $this->visual->get_harga_terbaru_by_jenis('hpp_broiler');

        $raw_telur = $this->_get_monthly_data_by_commodity('telur', null);
        $raw_lb = $this->_get_monthly_data_by_commodity('live_bird', null);
        $raw_jagung = $this->_get_monthly_data_by_commodity('jagung', null);
        $raw_katul = $this->_get_monthly_data_by_commodity('katul', null);
        $raw_hpp_konsentrat = $this->_get_monthly_data_by_commodity('hpp_konsentrat_layer', null);
        $raw_hpp_komplit = $this->_get_monthly_data_by_commodity('hpp_komplit_layer', null);
        $raw_hpp_broiler = $this->_get_monthly_data_by_commodity('hpp_broiler', null);

        $data['chart_hpp_konsentrat_vs_telur'] = $this->_process_comparison_chart_data(
            $raw_hpp_konsentrat, "HPP (Konsentrat)",
            $raw_telur, "Harga Telur",
            $selected_years 
        );
        
        $data['chart_hpp_komplit_vs_telur'] = $this->_process_comparison_chart_data(
            $raw_hpp_komplit, "HPP (Komplit)",
            $raw_telur, "Harga Telur",
            $selected_years 
        );
        
        $data['chart_hpp_broiler_vs_lb'] = $this->_process_comparison_chart_data(
            $raw_hpp_broiler, "HPP Broiler",
            $raw_lb, "Harga Live Bird",
            $selected_years 
        );
        
        $data['chart_jagung'] = $this->_process_monthly_chart_data($raw_jagung, $selected_years); 
        $data['chart_katul'] = $this->_process_monthly_chart_data($raw_katul, $selected_years); 

        $this->load->view('templates/dash_h', $data);
        $this->load->view('visual_harga_compare_view', $data);
        $this->load->view('templates/dash_f', $data);
    }
    
    // public function visual_data_crm()
    // {
    //     $token = $this->session->userdata('token');
    //     $data['user'] = $this->dash->getUserInfo($token)->row_array();
    //     $data["title"] = "CP APPS";
        
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
        
    //     $filter_type = $this->input->post('filter_type') ?? 'range';
    //     $default_start = date('Y-m');
    //     $default_end = date('Y-m');
    //     $default_quarter = 'Q' . ceil(date('n') / 3);
    //     $default_quarter_year = date('Y');

    //     $query_start_date = $default_start;
    //     $query_end_date = $default_end;

    //     if ($this->input->post()) {
    //         if ($filter_type == 'range') {
    //             $query_start_date = $this->input->post('start_date');
    //             $query_end_date = $this->input->post('end_date');
    //             $data['selected_quarter'] = $this->input->post('quarter') ?? $default_quarter;
    //             $data['selected_quarter_year'] = $this->input->post('quarter_year') ?? $default_quarter_year;

    //         } elseif ($filter_type == 'quarter') {
    //             $selected_quarter = $this->input->post('quarter');
    //             $selected_quarter_year = $this->input->post('quarter_year');
                
    //             switch ($selected_quarter) {
    //                 case 'Q1': $query_start_date = $selected_quarter_year . '-01'; $query_end_date = $selected_quarter_year . '-03'; break;
    //                 case 'Q2': $query_start_date = $selected_quarter_year . '-04'; $query_end_date = $selected_quarter_year . '-06'; break;
    //                 case 'Q3': $query_start_date = $selected_quarter_year . '-07'; $query_end_date = $selected_quarter_year . '-09'; break;
    //                 case 'Q4': default: $query_start_date = $selected_quarter_year . '-10'; $query_end_date = $selected_quarter_year . '-12'; break;
    //             }
                
    //             $data['selected_quarter'] = $selected_quarter;
    //             $data['selected_quarter_year'] = $selected_quarter_year;
    //         }
    //     } else {
    //         $data['selected_quarter'] = $default_quarter;
    //         $data['selected_quarter_year'] = $default_quarter_year;
    //     }

    //     $data['selected_start'] = ($filter_type == 'range') ? $query_start_date : ($this->input->post('start_date') ?? $default_start);
    //     $data['selected_end'] = ($filter_type == 'range') ? $query_end_date : ($this->input->post('end_date') ?? $default_end);
    //     $data['filter_type'] = $filter_type;
    //     // --- [AKHIR LOGIKA FILTER TANGGAL] ---


    //     // Panggil fungsi MODEL yang BARU (get_crm_...)
    //     $data['performance_data'] = $this->visual->get_crm_surveyor_performance($query_start_date, $query_end_date, $user_id_filter, $area_id_filter);
    //     $data['area_performance_data'] = $this->visual->get_crm_area_performance($query_start_date, $query_end_date, $data['user']);
    //     $visit_breakdown_raw = $this->visual->get_crm_visit_breakdown($query_start_date, $query_end_date, $user_id_filter, $area_id_filter); 
        
    //     // Logika grouping SANGAT disederhanakan. Tidak perlu mapping.
    //     $combined_data = $visit_breakdown_raw; 

    //     $grand_total = array_sum(array_column($combined_data, 'jumlah_visit'));
    //     $final_breakdown = [];
    //     if ($grand_total > 0) {
    //         foreach ($combined_data as $item) {
    //             $final_breakdown[] = [
    //                 'kategori' => $item['kategori'],
    //                 'persentase' => ($item['jumlah_visit'] / $grand_total) * 100
    //             ];
    //         }
    //     }
        
    //     usort($final_breakdown, function($a, $b) { return $b['persentase'] <=> $a['persentase']; });
    //     $data['visit_breakdown_data'] = $final_breakdown;
        
    //     // Ambil detail log
    //     $data['visit_details_table'] = $this->visual->get_crm_all_visit_details($query_start_date, $query_end_date, $user_id_filter, $area_id_filter);
        
    //     // Kirim tanggal FINAL ke JavaScript
    //     $data['js_start_date'] = $query_start_date;
    //     $data['js_end_date'] = $query_end_date;

    //     $this->load->view('templates/dash_h', $data);
    //     $this->load->view('visual_data_crm', $data); // Load VIEW BARU
    //     $this->load->view('templates/dash_f', $data);
    // }

    // /**
    //  * [CRM] AJAX: Mengambil data surveyor dan komposisi untuk 1 Area.
    //  */
    // public function get_crm_surveyors_for_area_ajax()
    // {
    //     $area_id = $this->input->post('area_id');
    //     $selected_start = $this->input->post('start_date');
    //     $selected_end = $this->input->post('end_date');

    //     if (empty($area_id) || empty($selected_start) || empty($selected_end)) {
    //         $this->output->set_status_header(400)->set_output(json_encode(['error' => 'Parameter tidak valid.']));
    //         return;
    //     }

    //     // 1. Ambil data performa surveyor untuk area tsb
    //     $surveyor_data = $this->visual->get_crm_surveyor_performance(
    //         $selected_start, 
    //         $selected_end, 
    //         null, // $user_id
    //         $area_id
    //     );

    //     // 2. Ambil data komposisi untuk area tsb
    //     $user_id_filter = null; 
    //     $visit_breakdown_raw = $this->visual->get_crm_visit_breakdown($selected_start, $selected_end, $user_id_filter, $area_id);
        
    //     // Logika kalkulasi persentase (disederhanakan, tanpa mapping)
    //     $combined_data = $visit_breakdown_raw;
    //     $grand_total = array_sum(array_column($combined_data, 'jumlah_visit'));
    //     $final_breakdown = [];
    //     if ($grand_total > 0) {
    //         foreach ($combined_data as $item) {
    //             $final_breakdown[] = ['kategori' => $item['kategori'], 'persentase' => ($item['jumlah_visit'] / $grand_total) * 100];
    //         }
    //     }
    //     usort($final_breakdown, function($a, $b) { return $b['persentase'] <=> $a['persentase']; });
    //     $composition_data = $final_breakdown;

    //     // 3. Kembalikan data
    //     $response = [
    //         'status' => 'success',
    //         'surveyor_data' => $surveyor_data,
    //         'composition_data' => $composition_data 
    //     ];

    //     $this->output->set_content_type('application/json')->set_output(json_encode($response));
    // }
    
    // public function get_crm_data_for_surveyor_ajax()
    // {
    //     $user_id = $this->input->post('user_id');
    //     $selected_start = $this->input->post('start_date');
    //     $selected_end = $this->input->post('end_date');

    //     if (empty($user_id) || empty($selected_start) || empty($selected_end)) {
    //         $this->output->set_status_header(400)->set_output(json_encode(['error' => 'Parameter tidak valid.']));
    //         return;
    //     }

    //     $area_id_filter = null;
    //     $visit_breakdown_raw = $this->visual->get_crm_visit_breakdown($selected_start, $selected_end, $user_id, $area_id_filter);

    //     $combined_data = $visit_breakdown_raw;
    //     $grand_total = array_sum(array_column($combined_data, 'jumlah_visit'));
    //     $final_breakdown = [];
    //     if ($grand_total > 0) {
    //         foreach ($combined_data as $item) {
    //             $final_breakdown[] = ['kategori' => $item['kategori'], 'persentase' => ($item['jumlah_visit'] / $grand_total) * 100];
    //         }
    //     }
    //     usort($final_breakdown, function($a, $b) { return $b['persentase'] <=> $a['persentase']; });
    //     $composition_data = $final_breakdown;


    //     $response = [
    //         'status' => 'success',
    //         'composition_data' => $composition_data,
    //     ];

    //     $this->output->set_content_type('application/json')->set_output(json_encode($response));
    // }
    
    public function get_kasus_data_for_area_ajax()
    {
        $area_id = $this->input->post('area_id');
        $selected_year = $this->input->post('tahun');

        if (empty($selected_year)) {
            $this->output->set_status_header(400)->set_output(json_encode(['error' => 'Parameter Tahun tidak valid.']));
            return;
        }

        $area_filter_to_model = (int)$area_id > 0 ? $area_id : null;
        $user_id_filter = null; 

        $raw_count_data = $this->visual->get_kasus_breakdown_count($selected_year, $user_id_filter, $area_filter_to_model);
        $detail_list = $this->visual->get_kasus_detail_list($selected_year, $user_id_filter, $area_filter_to_model);
        
        $master_labels_bulan_query = [];
        for ($i = 1; $i <= 12; $i++) {
            $master_labels_bulan_query[] = date('M Y', mktime(0, 0, 0, $i, 1, $selected_year));
        }

        $kategori_chart = []; 
        $pivot_chart_data = [];
        foreach ($raw_count_data as $row) {
            $bulan_tahun = $row['bulan_tahun'];
            $kat = $row['kategori_kasus'];
            $jumlah = (int)$row['jumlah_kasus'];
            if (!in_array($kat, $kategori_chart)) $kategori_chart[] = $kat;
            $pivot_chart_data[$kat][$bulan_tahun] = $jumlah;
        }
        
        $datasets = [];
        $colors = ['#28a745', '#ffc107', '#6f42c1', '#dc3545', '#fd7e14', '#17a2b8', '#6c757d'];
        $color_index = 0;
        
        foreach ($kategori_chart as $kat) {
            $dataset = ['label' => $kat, 'data' => [], 'backgroundColor' => $colors[$color_index % count($colors)]];
            
            foreach ($master_labels_bulan_query as $bulan_query) {
                $dataset['data'][] = $pivot_chart_data[$kat][$bulan_query] ?? 0;
            }
            
            $datasets[] = $dataset;
            $color_index++;
        }
        
        $chart_data_response = [
            'datasets' => $datasets
        ];

        $response = [
            'status' => 'success',
            'chart_data' => $chart_data_response,
            'detail_list' => $detail_list,
            'new_csrf_hash' => $this->security->get_csrf_hash() 
        ];

        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
}
