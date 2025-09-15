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
        $this->load->model(['M_Dash' => 'dash', 'M_Visiting' => 'visiting', 'M_Questions', 'Location_model']);
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

    // Individual methods for each visiting type
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
        
        $livestock_type = $form_data['jenis_ternak'] ?? null;
        
        // Store data in session including location if provided
        $session_data = [
            'visiting_form_data' => $form_data,
            'visiting_type' => 'Peternak',
            'livestock_type' => $livestock_type
        ];

        // Save location data if provided
        $this->_save_location_data($user['id_user']);

        $this->session->set_userdata($session_data);
        
        // Redirect based on livestock type
        $redirect_map = [
            'Pedaging' => 'Visiting_Pedaging_Controller/index',
            'Petelur' => 'Visiting_Petelur_Controller/index'
        ];
        
        $redirect_url = $redirect_map[$livestock_type] ?? 'Visiting2_Controller/index';
        redirect($redirect_url);
    }

    private function _handle_direct_submission($visiting_config, $user) {
        $form_data = $this->M_Questions->process_visiting_form_data(
            ['visiting', $visiting_config['page']], 
            $this->input->post(),
            $user
        );
        
        $data = array_merge([
            'master_sub_area_id' => $user['master_sub_area_id'],
            'id_user' => $user['id_user'] // Add id_user here
        ], $form_data);
        
        // Insert visiting data
        $visiting_result = $this->visiting->insert_visiting($data, $visiting_config['page']);
        
        if ($visiting_result) {
            // Save location data if provided
            $this->_save_location_data($user['id_user']);
            
            $this->session->set_flashdata('success', 'Data visiting berhasil disimpan!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data visiting!');
        }
        
        redirect('Dashboard_new/index');
    }

    private function _save_location_data($user_id) {
        $latitude = $this->input->post('user_latitude');
        $longitude = $this->input->post('user_longitude');
        $address = $this->input->post('user_address');

        if (!empty($latitude) && !empty($longitude)) {
            $location_data = [
                'id_user' => $user_id,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'address' => $address,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->Location_model->save_user_location($location_data);
        }
    }

    private function _prepare_form_data($type, $visiting_config, $user) {
        $current_sub_area = $this->db->get_where('master_sub_area', 
            ['master_sub_area_id' => $user['master_sub_area_id']])->row_array();
        
        $questions = $this->M_Questions->get_visiting_questions_combined(
            ['visiting', $visiting_config['page']], 
            $user
        );
        
        return [
            'questions' => $questions,
            'current_sub_area' => $current_sub_area,
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
