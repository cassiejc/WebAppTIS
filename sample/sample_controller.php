<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FormSampleController extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper('form');
        $this->load->helper('url');
        $this->load->library('form_validation');
        $this->load->model('M_Dash', 'dash'); 
        $this->load->model('M_FormSample', 'sample_form');
        $this->load->model('M_Questions');
        
    }

    public function index() {
        $token = $this->session->userdata('token');
        $user = $this->dash->getUserInfo($token)->row_array();

        if ($this->input->method() === 'post') {
            $questions = $this->M_Questions->get_questions_by_page('sample');
            $data = [
                'id_user' => $user['id_user'] 
            ];
            foreach ($questions as $q) {
                $field = $q['field_name']; 
                $input_name = 'q' . $q['questions_id']; 
                $answer = $this->input->post($input_name);
                
                // Jika jawaban adalah "Other", ambil nilai dari input text "Other"
                if ($answer === 'Other') {
                    $other_input_name = $input_name . '_other';
                    $other_value = $this->input->post($other_input_name);
                    if (!empty($other_value)) {
                        $data[$field] = $other_value; // Simpan nilai "Other" ke database
                    } else {
                        $data[$field] = $answer; // Jika kosong, simpan "Other"
                    }
                } else {
                    $data[$field] = $answer;
                }
            }
            
            $this->sample_form->insert_sample($data);
            $this->session->set_flashdata('success', 'Data sample berhasil disimpan!');
            redirect('FormSampleController/index');
        }

        $data['questions'] = $this->M_Questions->get_questions_by_page('sample');
        $this->load->view('templates/dash_h', $data);
        $this->load->view('form_sample_view', $data);
        $this->load->view('templates/dash_f', $data);
    }
}
