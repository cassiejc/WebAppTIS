<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Visiting_Petelur_Controller extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->helper(['form', 'url']);
        $this->load->library('form_validation');
        $this->load->model(['M_Dash' => 'dash', 'M_Questions']);
    }

    public function index() {
        $user = $this->_get_user_info();
        $current_sub_area = $this->_get_current_sub_area($user['master_sub_area_id']);
        
        $data = [
            'current_sub_area' => $current_sub_area,
            'questions' => $this->M_Questions->get_form_questions('visiting_petelur', $user)
        ];

        if ($this->input->method() === 'post') {
            $this->_handle_form_submission($user);
            return;
        }

        $this->_load_views($data);
    }

    public function get_questions_by_type() {
        $tipe_ternak = $this->input->post('tipe_ternak');
        $user = $this->_get_user_info();
        
        $questions = $this->M_Questions->get_questions_by_livestock_type($tipe_ternak, $user);
        
        echo json_encode($questions);
    }

    private function _get_user_info() {
        $token = $this->session->userdata('token');
        return $this->dash->getUserInfo($token)->row_array();
    }

    private function _get_current_sub_area($master_sub_area_id) {
        return $this->db->select('nama_sub_area')
                       ->from('master_sub_area')
                       ->where('master_sub_area_id', $master_sub_area_id)
                       ->get()
                       ->row_array();
    }

    private function _handle_form_submission($user) {
        $tipe_ternak = $this->input->post('tipe_ternak');
        $page = ($tipe_ternak === 'Layer') ? 'layer' : 'visiting_petelur';
        
        $insert_data = $this->M_Questions->process_form_data($page, $this->input->post(), $user);
        
        if ($this->db->insert('visiting_petelur', $insert_data)) {
            $this->session->set_flashdata('success', 'Data visiting petelur berhasil disimpan!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data visiting petelur!');
        }
        
        redirect('Dashboard_new/index');
    }

    private function _load_views($data) {
        $this->load->view('templates/dash_h', $data);
        $this->load->view('form_visiting_petelur_view', $data);
        $this->load->view('templates/dash_f', $data);
    }
}
