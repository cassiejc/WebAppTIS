<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Visiting_Pedaging_Controller extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper('form');
        $this->load->helper('url');
        $this->load->library('form_validation');
        $this->load->model('M_Dash', 'dash'); 
        $this->load->model('M_Visiting', 'visiting');
        $this->load->model('M_Questions');
    }

    public function index() {
        $token = $this->session->userdata('token');
        $user = $this->dash->getUserInfo($token)->row_array();

        if ($this->input->method() === 'post') {
            // Get stored form data from session
            $form_data = $this->session->userdata('visiting_form_data');
            $visiting_type = $this->session->userdata('visiting_type');
            $livestock_type = $this->session->userdata('livestock_type');

            // Get questions for visiting pedaging page
            $questions = $this->M_Questions->get_questions_by_page('visiting_pedaging');
            
            // Add answers to form data
            foreach ($questions as $q) {
                $field = $q['field_name']; 
                $input_name = 'q' . $q['questions_id']; 
                $answer = $this->input->post($input_name);
                $form_data[$field] = $answer;
            }
            
            // Save complete data
            $data = [
                'master_sub_area_id' => $user['master_sub_area_id'],
                'visiting_type' => $visiting_type,
                'livestock_type' => $livestock_type
            ];
            foreach ($form_data as $field => $value) {
                $data[$field] = $value;
            }
            
            $this->visiting->insert_visiting($data);
            
            // Clear session data
            $this->session->unset_userdata('visiting_form_data');
            $this->session->unset_userdata('visiting_type');
            $this->session->unset_userdata('livestock_type');
            
            $this->session->set_flashdata('success', 'Data visiting Pedaging berhasil disimpan!');
            redirect('Dashboard_new/index');
        }

        // Get nama_sub_area dari user ini
        $current_sub_area = $this->db->get_where('master_sub_area', 
            ['master_sub_area_id' => $user['master_sub_area_id']])->row_array();

        // Get questions dan options untuk visiting pedaging
        $data['questions'] = $this->M_Questions->get_questions_by_page('visiting_pedaging');
        $data['current_sub_area'] = $current_sub_area;
        $data['visiting_type'] = $this->session->userdata('visiting_type');
        $data['livestock_type'] = $this->session->userdata('livestock_type');
        
        // Filter opsi berdasarkan sub area user dan tambahkan field_name ke setiap pertanyaan
        foreach ($data['questions'] as &$q) {
            // Add field_name as data attribute for JavaScript identification
            $q['data_field'] = $q['field_name'];
            
            if ($q['type'] === 'radio' || $q['type'] === 'select') {
                // Get options from options table based on master_sub_area_id
                if (isset($q['combine_options']) && !empty($q['combine_options'])) {
                    $combine_ids = explode(',', $q['combine_options']);
                    $this->db->select('o.*, o.tipe_ternak')
                            ->from('options o')
                            ->where_in('o.questions_id', $combine_ids)
                            ->where('o.master_sub_area_id', $user['master_sub_area_id']);
                    $q['options'] = $this->db->get()->result_array();
                } else {
                    $this->db->select('o.*, o.tipe_ternak')
                            ->from('options o')
                            ->where('o.questions_id', $q['questions_id'])
                            ->where('o.master_sub_area_id', $user['master_sub_area_id']);
                    $q['options'] = $this->db->get()->result_array();
                }
            }
        }
        
        // Tambahkan options global (master_sub_area_id = 0) untuk semua area
        foreach ($data['questions'] as &$q) {
            if ($q['type'] === 'radio' || $q['type'] === 'select') {
                // Get global options (master_sub_area_id = 0)
                if (isset($q['combine_options']) && !empty($q['combine_options'])) {
                    $combine_ids = explode(',', $q['combine_options']);
                    $this->db->select('o.*, o.tipe_ternak')
                            ->from('options o')
                            ->where_in('o.questions_id', $combine_ids)
                            ->where('o.master_sub_area_id', 0);
                    $global_options = $this->db->get()->result_array();
                } else {
                    $this->db->select('o.*, o.tipe_ternak')
                            ->from('options o')
                            ->where('o.questions_id', $q['questions_id'])
                            ->where('o.master_sub_area_id', 0);
                    $global_options = $this->db->get()->result_array();
                }
                
                // Merge global options with existing options
                if (!empty($global_options)) {
                    if (isset($q['options'])) {
                        $q['options'] = array_merge($q['options'], $global_options);
                    } else {
                        $q['options'] = $global_options;
                    }
                }
            }
        }

        $this->load->view('templates/dash_h', $data);
        $this->load->view('form_visiting_pedaging_view', $data);
        $this->load->view('templates/dash_f', $data);
    }

    // AJAX method untuk mendapatkan options berdasarkan tipe ternak
    public function get_options_by_livestock_type() {
        $questions_id = $this->input->post('questions_id');
        $livestock_type = $this->input->post('livestock_type');
        $token = $this->session->userdata('token');
        $user = $this->dash->getUserInfo($token)->row_array();
        
        // Get options berdasarkan tipe ternak
        $this->db->select('o.*')
                ->from('options o')
                ->where('o.questions_id', $questions_id)
                ->where('o.master_sub_area_id', $user['master_sub_area_id']);
                
        // Filter berdasarkan tipe ternak jika ada
        if (!empty($livestock_type)) {
            $this->db->where('o.tipe_ternak', $livestock_type);
        }
        
        $options = $this->db->get()->result_array();
        
        // Get global options juga
        $this->db->select('o.*')
                ->from('options o')
                ->where('o.questions_id', $questions_id)
                ->where('o.master_sub_area_id', 0);
                
        if (!empty($livestock_type)) {
            $this->db->where('o.tipe_ternak', $livestock_type);
        }
        
        $global_options = $this->db->get()->result_array();
        
        // Merge options
        if (!empty($global_options)) {
            $options = array_merge($options, $global_options);
        }
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode($options);
    }
}
