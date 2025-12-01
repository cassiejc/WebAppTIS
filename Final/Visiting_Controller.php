<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Visiting_Controller extends CI_Controller {
    
    private $valid_types = [
        'kantor' => ['page' => 'visiting_kantor', 'title' => 'Kantor', 'action' => 'submit'],
        'agen' => ['page' => 'visiting_agen', 'title' => 'Agen', 'action' => 'submit'],
        'peternak' => ['page' => 'visiting_peternak', 'title' => 'Peternak', 'action' => 'next'],
        'kemitraan' => ['page' => 'visiting_kemitraan', 'title' => 'Kemitraan', 'action' => 'submit'],
        'subagen' => ['page' => 'visiting_subagen', 'title' => 'Sub Agen', 'action' => 'submit'],
        'koordinasi' => ['page' => 'visiting_koordinasi', 'title' => 'Koordinasi', 'action' => 'submit']
    ];

    public function __construct() {
        parent::__construct();
        $this->load->helper(['form', 'url']);
        $this->load->library('form_validation');
        $this->load->model(['M_Dash' => 'dash', 'M_Visiting' => 'visiting', 'M_Questions', 'M_master_harga']);
    }

    public function index() {
        redirect('Dashboard_new/index');
    }

    public function visiting_type($type = null) {
        if (!$type || !isset($this->valid_types[$type])) {
            show_404();
            return;
        }

        $user = $this->_get_user_info();
        $visiting_config = $this->valid_types[$type];

        if ($this->input->method() === 'post') {
            $this->_handle_form_submission($type, $visiting_config, $user);
            return;
        }

        $data = $this->_prepare_form_data($type, $visiting_config, $user);
        $this->_load_views($data);
    }

    public function kantor() { $this->visiting_type('kantor'); }
    public function agen() { $this->visiting_type('agen'); }
    public function peternak() { $this->visiting_type('peternak'); }
    public function kemitraan() { $this->visiting_type('kemitraan'); }
    public function subagen() { $this->visiting_type('subagen'); }
    public function koordinasi() { $this->visiting_type('koordinasi'); }

    public function load_form_questions() {
        $user = $this->_get_user_info();
        $visiting_type = $this->input->post('visiting_type');
        
        $page_mapping = [
            'Agen' => 'visiting_agen',
            'Peternak' => 'visiting_peternak',
            'Kemitraan' => 'visiting_kemitraan',
            'Sub Agen' => 'visiting_subagen',
            'Koordinasi' => 'visiting_koordinasi',
            'Kantor' => 'visiting_kantor'
        ];
        
        $page = isset($page_mapping[$visiting_type]) ? $page_mapping[$visiting_type] : 'visiting';
        $questions = $this->M_Questions->get_visiting_questions($page, $user);
        
        header('Content-Type: application/json');
        echo json_encode([
            'questions' => $questions,
            'visiting_type' => $visiting_type
        ]);
    }

    private function _get_user_info() {
        $token = $this->session->userdata('token');
        return $this->dash->getUserInfo($token)->row_array();
    }

    private function _handle_form_submission($type, $visiting_config, $user) {
        $action = $this->input->post('action');
        
        if ($action === 'next' && $type === 'peternak') {
            $this->_handle_peternak_submission($visiting_config, $user);
        } else {
            $this->_handle_direct_submission($visiting_config, $user);
        }
    }

    private function _handle_peternak_submission($visiting_config, $user) {
        $form_data = $this->M_Questions->process_visiting_form_data(
            ['visiting', 'visiting_peternak'], 
            $this->input->post(),
            $user
        );
        
        $form_data['latitude'] = $this->input->post('latitude');
        $form_data['longitude'] = $this->input->post('longitude');
        $form_data['location_address'] = $this->input->post('location_address');
        
        $livestock_type = $form_data['jenis_ternak'] ?? null;
        
        $this->session->set_userdata([
            'visiting_form_data' => $form_data,
            'visiting_type' => 'Peternak',
            'livestock_type' => $livestock_type
        ]);
        
        $redirect_map = [
            'Pedaging' => 'Visiting_Pedaging_Controller/index',
            'Petelur' => 'Visiting_Petelur_Controller/index',
            'Lainnya' => 'Visiting_Lainnya_Controller/index'
        ];
        
        $redirect_url = $redirect_map[$livestock_type] ?? 'Visiting2_Controller/index';
        redirect($redirect_url);
    }

    private function _handle_direct_submission($visiting_config, $user) {
        try {
            $form_data = $this->M_Questions->process_visiting_form_data(
                ['visiting', $visiting_config['page']], 
                $this->input->post(),
                $user
            );
            
            $location_data = [
                'latitude' => $this->input->post('latitude'),
                'longitude' => $this->input->post('longitude'),
                'location_address' => $this->input->post('location_address')
            ];
            
            $data = array_merge(
                [
                    'id_user' => $user['id_user']
                ],
                $location_data,
                $form_data
            );

            $data['waktu_kunjungan'] = date('Y-m-d H:i:s');
            
            // 1. Simpan data visiting (ini sudah ada di kode Anda)
            $this->visiting->insert_visiting($data, $visiting_config['page']);
            
            // --- PERUBAHAN DIMULAI DI SINI ---
            
            // 2. Panggil fungsi hitung ulang harga dari model
            try {
                // Panggil fungsi model yang sudah kita pusatkan
                $this->M_master_harga->recalculate_all_prices();
                
                // 3. Ubah pesan sukses
                $this->session->set_flashdata('success', 'Data visiting berhasil disimpan DAN semua harga berhasil dihitung ulang!');
            
            } catch (Exception $recalc_error) {
                // 4. Jika simpan data BERHASIL, tapi hitung ulang GAGAL
                log_message('error', 'Gagal hitung ulang harga setelah submit visiting (direct): ' . $recalc_error->getMessage());
                $this->session->set_flashdata('warning', 'Data visiting berhasil disimpan, TAPI gagal melakukan hitung ulang harga otomatis. Error: ' . $recalc_error->getMessage());
            }
            // --- PERUBAHAN SELESAI ---

        } catch (Exception $e) {
            // Ini adalah catch jika simpan data visiting GAGAL
            $this->session->set_flashdata('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
        
        redirect('Dashboard_new/index');
    }

    private function _prepare_form_data($type, $visiting_config, $user) {
        $nama_lokasi = ''; 

        if (isset($user['group_user']) && $user['group_user'] === 'koordinator') {
            $area = $this->db->get_where('master_area', ['master_area_id' => $user['master_area_id']])->row_array();
            if ($area) {
                $nama_lokasi = $area['nama_area'];
            }
        } else {
            $sub_area = $this->db->get_where('master_sub_area', ['master_sub_area_id' => $user['master_sub_area_id']])->row_array();
            if ($sub_area) {
                $nama_lokasi = $sub_area['nama_sub_area'];
            }
        }
        
        $questions = $this->M_Questions->get_visiting_questions_combined(
            ['visiting', $visiting_config['page']], 
            $user
        );
        
        return [
            'title' => 'CP APPS',
            'questions' => $questions,
            'nama_lokasi_header' => $nama_lokasi, 
            'visiting_type' => $visiting_config['title'],
            'action_type' => $visiting_config['action']
        ];
    }

    private function _load_views($data) {
        $this->load->view('templates/dash_h', $data);
        $this->load->view('form_visiting_view', $data);
        $this->load->view('templates/dash_f', $data);
    }
}
