<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FormSeminarController extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper('form');
        $this->load->helper('url');
        $this->load->library('form_validation');
        $this->load->model('M_Dash', 'dash'); 
        $this->load->model('M_seminar', 'seminar');
        $this->load->model('M_Questions');
    }

    // Tampilkan form input seminar
    public function index() {
        // Get user id from session
        $token = $this->session->userdata('token');
        $user = $this->dash->getUserInfo($token)->row_array();
        
        // Proses simpan jika ada POST
        if ($this->input->method() === 'post') {
            $questions = $this->M_Questions->get_questions_by_page('seminar');
            $data = [
                'id_user' => $user['id_user'],
                // 'latitude' => $this->input->post('latitude'),
                // 'longitude' => $this->input->post('longitude'),
                // 'address' => $this->input->post('address'),
                // 'created_at' => date('Y-m-d H:i:s')
            ];
            
            foreach ($questions as $q) {
                $field = $q['field_name'];
                $input_name = 'q' . $q['questions_id'];
                $data[$field] = $this->input->post($input_name);
            }
            
            $this->seminar->insert_seminar($data);
            $this->session->set_flashdata('success', 'Data seminar berhasil disimpan!');
            redirect('Dashboard_new/index');
        }

        // Get questions and their options
        $data['questions'] = $this->M_Questions->get_questions_by_page('seminar');

        foreach($data['questions'] as &$q) {
            if ($q['type'] === 'radio' || $q['type'] === 'select') {
                // Gunakan method dari M_Questions untuk konsistensi
                $q['options'] = $this->M_Questions->get_question_options($q, $user['master_sub_area_id']);
            }
        }

        // Tampilkan form
        $this->load->view('templates/dash_h', $data);
        $this->load->view('form_seminar_view', $data);
        $this->load->view('templates/dash_f', $data);
    }
}
