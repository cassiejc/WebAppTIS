<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Visiting_Pedaging_Controller extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper(['form', 'url']);
        $this->load->library('form_validation');
        $this->load->model(['M_Dash' => 'dash', 'M_Visiting' => 'visiting', 'M_Questions']);
    }

    public function index() {
        $token = $this->session->userdata('token');
        $user = $this->dash->getUserInfo($token)->row_array();

        if ($this->input->method() === 'post') {
            $this->_handle_form_submission($user);
            return;
        }

        $this->_display_form($user);
    }

    private function _handle_form_submission($user) {
        // Get stored form data from session
        $form_data = $this->session->userdata('visiting_form_data') ?: [];
        $visiting_type = $this->session->userdata('visiting_type');
        $livestock_type = $this->session->userdata('livestock_type');

        // Get questions and add answers to form data
        $questions = $this->M_Questions->get_questions_by_page('visiting_pedaging');
        
        foreach ($questions as $q) {
            $input_name = 'q' . $q['questions_id']; 
            $answer = $this->input->post($input_name);
            $form_data[$q['field_name']] = $answer;
        }
        
        // Prepare and save data
        $data = array_merge([
            'master_sub_area_id' => $user['master_sub_area_id'],
            'visiting_type' => $visiting_type,
            'livestock_type' => $livestock_type
        ], $form_data);
        
        $this->visiting->insert_visiting($data);
        
        // Clear session data
        $this->session->unset_userdata(['visiting_form_data', 'visiting_type', 'livestock_type']);
        
        $this->session->set_flashdata('success', 'Data visiting Pedaging berhasil disimpan!');
        redirect('Dashboard_new/index');
    }

    private function _display_form($user) {
        // Get current sub area info
        $current_sub_area = $this->db->get_where('master_sub_area', 
            ['master_sub_area_id' => $user['master_sub_area_id']])->row_array();

        // Get questions with filtered options using the new model method
        $data = [
            'questions' => $this->M_Questions->get_questions_with_filtered_options(
                'visiting_pedaging', 
                $user['master_sub_area_id'], 
                $user['id_user']
            ),
            'current_sub_area' => $current_sub_area,
            'visiting_type' => $this->session->userdata('visiting_type'),
            'livestock_type' => $this->session->userdata('livestock_type')
        ];
        
        $this->load->view('templates/dash_h', $data);
        $this->load->view('form_visiting_pedaging_view', $data);
        $this->load->view('templates/dash_f', $data);
    }

    /**
     * AJAX method untuk mendapatkan options berdasarkan tipe ternak
     */
    public function get_options_by_livestock_type() {
        $questions_id = $this->input->post('questions_id');
        $livestock_type = $this->input->post('livestock_type');
        $token = $this->session->userdata('token');
        $user = $this->dash->getUserInfo($token)->row_array();
        
        // Use model method to get options
        $options = $this->M_Questions->get_options_by_livestock_type(
            $questions_id, 
            $user['master_sub_area_id'], 
            $user['id_user'], 
            $livestock_type
        );
        
        // Return JSON response
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($options));
    }
}
?>
