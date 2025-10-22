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
        $data["title"] = "Laporan Gabungan";
       
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
        if ($this->input->post()) {
            $selected_month = $this->input->post('bulan');
            $selected_year = $this->input->post('tahun');
        } else {
            $selected_month = date('m');
            $selected_year = date('Y');
        }
        
        $data['performance_data'] = $this->visual->get_surveyor_performance($selected_month, $selected_year, $user_id_filter, $area_id_filter);
        $data['area_performance_data'] = $this->visual->get_area_performance($selected_month, $selected_year, $data['user']);
        $visit_breakdown_raw = $this->visual->get_visit_breakdown($selected_month, $selected_year, $user_id_filter, $area_id_filter); 
        $sample_count = $this->visual->get_sample_count_by_month($selected_month, $selected_year, $user_id_filter, $area_id_filter);
        $seminar_count = $this->visual->get_seminar_count_by_month($selected_month, $selected_year, $user_id_filter, $area_id_filter);
        $new_customer_count = $this->visual->get_new_customer_count_by_month($selected_month, $selected_year, $user_id_filter, $area_id_filter);
        
        $data['visit_details_table'] = $this->visual->get_all_visit_details($selected_month, $selected_year, $user_id_filter, $area_id_filter);
        
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

    public function visual_kasus_penyakit()
    {
        $data['user'] = $this->dash->getUserInfo($this->session->userdata('token'))->row_array();
        $data["title"] = "Laporan Kasus Penyakit";
        $user_id_filter = null; 
        if (isset($data['user']['group_user']) && $data['user']['group_user'] === 'surveyor') {
            $user_id_filter = $data['user']['id_user'];
        }

        $selected_year = $this->input->post('tahun') ?: date('Y');
        $raw_stacked_data = $this->visual->get_kasus_breakdown_stacked($selected_year, $user_id_filter);
        
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
            $dataset = [
                'label' => $kat, 
                'data' => [], 
                'backgroundColor' => $colors[$color_index % count($colors)]
            ];
            foreach ($labels as $bulan) {
                $dataset['data'][] = $pivot_chart_data[$kat][$bulan] ?? 0;
            }
            $datasets[] = $dataset;
            $color_index++;
        }
        $data['chart_labels'] = json_encode($labels);
        $data['chart_datasets'] = json_encode($datasets);

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

    public function visual_harga($jenis_terpilih = 'telur')
    {
        $data['user'] = $this->dash->getUserInfo($this->session->userdata('token'))->row_array();
        $mapping_jenis = [
            'telur' => 'harga_jual_telur_layer',
            'jagung' => 'harga_jagung',
            'katul' => 'harga_katul',
            'afkir' => 'harga_afkir',
            'telur_puyuh' => 'harga_telur_puyuh',
            'telur_bebek' => 'harga_telur_bebek',
            'bebek_pedaging' => 'harga_bebek_pedaging',
            'live_bird' => 'harga_live_bird',
            'pakan_broiler' => 'harga_pakan_broiler',
            'doc' => 'harga_doc',
            'konsentrat_layer' => 'harga_konsentrat_layer',
            'hpp_konsentrat_layer' => 'hpp_konsentrat_layer',
            'hpp_komplit_layer' => 'hpp_komplit_layer',
            'cost_komplit_broiler' => 'cost_komplit_broiler',
            'hpp_broiler' => 'hpp_broiler'
        ];

        $jenis_harga_key = $mapping_jenis[$jenis_terpilih] ?? 'harga_jual_telur_layer';
        $data['harga_hari_ini'] = $this->visual->get_harga_terbaru_by_jenis($jenis_harga_key);
        $data['jenis_terpilih'] = $jenis_terpilih;

        // $data['selected_bulan_harian'] = $this->input->post('bulan_harian') ?: date('m');
        // $data['selected_tahun_harian'] = $this->input->post('tahun_harian') ?: date('Y');
        // $data['selected_tahun_bulanan'] = $this->input->post('tahun_bulanan') ?: date('Y');

        $data['selected_bulan_harian'] = $this->input->get('bulan_harian') ?: date('m'); // <--- GANTI ke get()
        $data['selected_tahun_harian'] = $this->input->get('tahun_harian') ?: date('Y'); // <--- GANTI ke get()
        // Default: Tahun saat ini untuk grafik bulanan
        $data['selected_tahun_bulanan'] = $this->input->get('tahun_bulanan') ?: date('Y'); // <--- GANTI ke get()

        switch($jenis_terpilih) {
            case 'jagung':
                $data["title"] = "Laporan Harga Jagung";
                $harga_harian = $this->visual->get_harga_jagung_harian_chart($data['selected_tahun_harian'], $data['selected_bulan_harian']);
                $harga_bulanan = $this->visual->get_harga_jagung_bulanan_chart($data['selected_tahun_bulanan']);
                $harga_tahunan = $this->visual->get_harga_jagung_tahunan_chart();
                break;
            case 'katul':
                $data["title"] = "Laporan Harga Katul";
                $harga_harian = $this->visual->get_harga_katul_harian_chart();
                $harga_bulanan = $this->visual->get_harga_katul_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_katul_tahunan_chart();
                break;
            case 'afkir':
                $data["title"] = "Laporan Harga Afkir";
                $harga_harian = $this->visual->get_harga_afkir_harian_chart();
                $harga_bulanan = $this->visual->get_harga_afkir_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_afkir_tahunan_chart();
                break;
            case 'telur_puyuh':
                $data["title"] = "Laporan Harga Telur Puyuh";
                $harga_harian = $this->visual->get_harga_telur_puyuh_harian_chart();
                $harga_bulanan = $this->visual->get_harga_telur_puyuh_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_telur_puyuh_tahunan_chart();
                break;
            case 'telur_bebek':
                $data["title"] = "Laporan Harga Telur Bebek";
                $harga_harian = $this->visual->get_harga_telur_bebek_harian_chart();
                $harga_bulanan = $this->visual->get_harga_telur_bebek_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_telur_bebek_tahunan_chart();
                break;
            case 'bebek_pedaging':
                $data["title"] = "Laporan Harga Bebek Pedaging";
                $harga_harian = $this->visual->get_harga_bebek_pedaging_harian_chart();
                $harga_bulanan = $this->visual->get_harga_bebek_pedaging_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_bebek_pedaging_tahunan_chart();
                break;
            case 'live_bird':
                $data["title"] = "Laporan Harga Live Bird";
                $harga_harian = $this->visual->get_harga_live_bird_harian_chart();
                $harga_bulanan = $this->visual->get_harga_live_bird_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_live_bird_tahunan_chart();
                break;

            case 'pakan_broiler':
                $data["title"] = "Laporan Harga Pakan Komplit Broiler";
                $harga_harian = $this->visual->get_harga_pakan_broiler_harian_chart();
                $harga_bulanan = $this->visual->get_harga_pakan_broiler_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_pakan_broiler_tahunan_chart();
                break;
            case 'doc':
              $data["title"] = "Laporan Harga DOC"; 
              $harga_harian = $this->visual->get_harga_doc_harian_chart();
              $harga_bulanan = $this->visual->get_harga_doc_bulanan_chart();
              $harga_tahunan = $this->visual->get_harga_doc_tahunan_chart();
              break;
            case 'pakan_campuran':
                $data["title"] = "Laporan Harga Pakan Campuran";
                $harga_harian = $this->visual->get_harga_pakan_campuran_harian_chart();
                $harga_bulanan = $this->visual->get_harga_pakan_campuran_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_pakan_campuran_tahunan_chart();
                break;
            case 'pakan_komplit_layer':
                $data["title"] = "Laporan Harga Pakan Komplit Layer";
                $harga_harian = $this->visual->get_harga_pakan_komplit_layer_harian_chart();
                $harga_bulanan = $this->visual->get_harga_pakan_komplit_layer_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_pakan_komplit_layer_tahunan_chart();
                break;
            case 'konsentrat_layer':
                $data["title"] = "Laporan Average Harga Konsentrat Layer";
                $harga_harian = $this->visual->get_harga_konsentrat_layer_harian_chart();
                $harga_bulanan = $this->visual->get_harga_konsentrat_layer_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_konsentrat_layer_tahunan_chart();
                break;
            case 'hpp_konsentrat_layer':
                $data["title"] = "Laporan Average HPP Konsentrat Layer";
                $harga_harian = $this->visual->get_hpp_konsentrat_layer_harian_chart();
                $harga_bulanan = $this->visual->get_hpp_konsentrat_layer_bulanan_chart();
                $harga_tahunan = $this->visual->get_hpp_konsentrat_layer_tahunan_chart();
                break;
            case 'hpp_komplit_layer':
                $data["title"] = "Laporan Average HPP Komplit Layer";
                $harga_harian = $this->visual->get_hpp_komplit_layer_harian_chart();
                $harga_bulanan = $this->visual->get_hpp_komplit_layer_bulanan_chart();
                $harga_tahunan = $this->visual->get_hpp_komplit_layer_tahunan_chart();
                break;

            case 'cost_komplit_broiler':
                $data["title"] = "Laporan Average Cost Komplit Broiler";
                $harga_harian = $this->visual->get_harga_cost_komplit_broiler_harian_chart();
                $harga_bulanan = $this->visual->get_harga_cost_komplit_broiler_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_cost_komplit_broiler_tahunan_chart();
                break;

            case 'hpp_broiler':
                $data["title"] = "Laporan Average HPP Broiler";
                $harga_harian = $this->visual->get_harga_hpp_broiler_harian_chart();
                $harga_bulanan = $this->visual->get_harga_hpp_broiler_bulanan_chart();
                $harga_tahunan = $this->visual->get_harga_hpp_broiler_tahunan_chart();
                break;

            case 'telur':
            default: 
                $data["title"] = "Laporan Harga Telur Layer";
                $data['jenis_terpilih'] = 'telur';
                $harga_harian = $this->visual->get_harga_telur_harian_chart($data['selected_tahun_harian'], $data['selected_bulan_harian']);
                $harga_bulanan = $this->visual->get_harga_telur_bulanan_chart($data['selected_tahun_bulanan']);
                $harga_tahunan = $this->visual->get_harga_telur_tahunan_chart();
                break;
        }

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
    
    public function visual_kandang_kosong()
    {
        $data['user'] = $this->dash->getUserInfo($this->session->userdata('token'))->row_array();
        $data["title"] = "Laporan Kandang Kosong";
        $selected_tipe_ternak = $this->input->post('tipe_ternak');
        $start_month = $this->input->post('start_month') ?? date('Y-m', strtotime('-11 months'));
        $end_month = $this->input->post('end_month') ?? date('Y-m');
        $filters = [
            'tipe_ternak' => $selected_tipe_ternak,
            'start_month' => $start_month,
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

    public function visual_harga_compare()
    {
        $data['user'] = $this->dash->getUserInfo($this->session->userdata('token'))->row_array();
        $data["title"] = "Perbandingan Harga Komoditas";

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

        $komoditas1_key = $this->input->get('komoditas1') ?: 'telur'; 
        $komoditas2_key = $this->input->get('komoditas2') ?: 'jagung';

        $data['selected_komoditas1'] = $komoditas1_key;
        $data['selected_komoditas2'] = $komoditas2_key;

        $data1_raw = $this->_get_monthly_data_by_commodity($komoditas1_key);
        $data2_raw = $this->_get_monthly_data_by_commodity($komoditas2_key);

        $pivot_data = [];
        $all_labels_sortable = []; 
        $nama_bulan = [1=>'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        foreach ($data1_raw as $row) {
            $key = $row['tahun'] . '-' . str_pad($row['bulan'], 2, '0', STR_PAD_LEFT);
            $label = $nama_bulan[(int)$row['bulan']] . ' ' . $row['tahun'];
            $all_labels_sortable[$key] = $label;
            $pivot_data[$key][$komoditas1_key] = (float)$row['nilai_rata_rata'];
        }
        
        foreach ($data2_raw as $row) {
            $key = $row['tahun'] . '-' . str_pad($row['bulan'], 2, '0', STR_PAD_LEFT);
            $label = $nama_bulan[(int)$row['bulan']] . ' ' . $row['tahun'];
            $all_labels_sortable[$key] = $label;
            $pivot_data[$key][$komoditas2_key] = (float)$row['nilai_rata_rata'];
        }

        ksort($all_labels_sortable);

        $final_labels = [];
        $final_data1 = [];
        $final_data2 = [];

        foreach ($all_labels_sortable as $key => $label) {
            $final_labels[] = $label;
            $final_data1[] = $pivot_data[$key][$komoditas1_key] ?? 0;
            $final_data2[] = $pivot_data[$key][$komoditas2_key] ?? 0;
        }

        $komoditas1_name = $data['all_komoditas'][$komoditas1_key] ?? $komoditas1_key;
        $komoditas2_name = $data['all_komoditas'][$komoditas2_key] ?? $komoditas2_key;

        $data['chart_labels'] = json_encode($final_labels);
        $data['chart_datasets'] = json_encode([
            [
                'label' => $komoditas1_name,
                'data' => $final_data1,
                'borderColor' => '#007bff', 
                'backgroundColor' => 'rgba(0, 123, 255, 0.1)',
                'fill' => false,
                'tension' => 0.1
            ],
            [
                'label' => $komoditas2_name,
                'data' => $final_data2,
                'borderColor' => '#28a745', 
                'backgroundColor' => 'rgba(40, 167, 69, 0.1)',
                'fill' => false,
                'tension' => 0.1
            ]
        ]);

        // --- 6. Load View ---
        $this->load->view('templates/dash_h', $data);
        $this->load->view('visual_harga_compare_view', $data); 
        $this->load->view('templates/dash_f', $data);
    }
    
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
            default: 
                return $this->visual->get_harga_telur_bulanan_chart();
        }
    }

    public function visual_kondisi_lingkungan()
    {
        // 1. Standard User & Title Setup
        $token = $this->session->userdata('token');
        $data['user'] = $this->dash->getUserInfo($token)->row_array();
        $data["title"] = "Laporan Kondisi Lingkungan";
        
        // 2. Setup Filter (Surveyor/Koordinator)
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

        // 3. Get Date Filters - HANYA TAHUN
        $selected_year = $this->input->post('tahun') ?: date('Y');
        $data['selected_year'] = $selected_year;
        
        // 4. Panggil Model 
        $raw_data = $this->visual->get_kondisi_lingkungan_monthly($selected_year, $user_id_filter, $area_id_filter);
        
        // 5. Proses Data untuk Chart (LOGIKA 100% STACKED)
        $process_100_percent_stacked_data = function($raw_data, $chart_key) {
            $labels = []; 
            $categories = [];
            $pivot_data = []; 
            $monthly_totals = [];

            foreach ($raw_data as $row) {
                if ($row['kategori_chart'] != $chart_key) continue; 

                $bulan = $row['bulan_tahun'];
                $kat = $row['nilai'];
                $jumlah = (int)$row['jumlah'];

                if (!in_array($bulan, $labels)) $labels[] = $bulan;
                if (!in_array($kat, $categories)) $categories[] = $kat;
                
                if (!isset($pivot_data[$kat][$bulan])) $pivot_data[$kat][$bulan] = 0;
                $pivot_data[$kat][$bulan] += $jumlah;

                if (!isset($monthly_totals[$bulan])) $monthly_totals[$bulan] = 0;
                $monthly_totals[$bulan] += $jumlah;
            }

            usort($labels, function($a, $b) {
                return strtotime('01 ' . $a) - strtotime('01 ' . $b);
            });
            
            if ($chart_key == 'lalat') {
                $order = ['Normal', 'Sedikit', 'Sedang', 'Banyak'];
            } else {
                $order = ['Kering', 'Lembab', 'Basah', 'Normal'];
            }
            
            usort($categories, function($a, $b) use ($order) {
                $pos_a = array_search($a, $order);
                $pos_b = array_search($b, $order);
                return ($pos_a === false ? 99 : $pos_a) <=> ($pos_b === false ? 99 : $pos_b);
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

                foreach ($labels as $bulan) {
                    $jumlah_kat = $pivot_data[$kat][$bulan] ?? 0;
                    $total_bulan = $monthly_totals[$bulan] ?? 0;
                    
                    $persentase = ($total_bulan > 0) ? ($jumlah_kat / $total_bulan) * 100 : 0;
                    $dataset['data'][] = round($persentase, 2); 
                    $dataset['raw_counts'][] = $jumlah_kat;
                }
                $datasets[] = $dataset;
                $color_index++;
            }
            
            // PENTING: Return array PHP biasa, BUKAN string JSON
            return [
                'labels' => $labels,
                'datasets' => $datasets
            ];
        };
        
        // Siapkan data untuk view
        $data['chart_lalat_data'] = $process_100_percent_stacked_data($raw_data, 'lalat');
        $data['chart_kotoran_data'] = $process_100_percent_stacked_data($raw_data, 'kotoran');

        // 6. Load Views
        $this->load->view('templates/dash_h', $data);
        $this->load->view('visual_kondisi_lingkungan_view', $data); 
        $this->load->view('templates/dash_f', $data);
    }
}
