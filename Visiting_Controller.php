<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Visiting_Controller extends CI_Controller {
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
            $action = $this->input->post('action');
            
            // Check if this is from the first form (Next button for Peternak)
            if ($action === 'next') {
                // Store form data in session for the second page
                $form_data = [];
                $questions = $this->M_Questions->get_questions_by_page('visiting');
                foreach ($questions as $q) {
                    $field = $q['field_name']; 
                    $input_name = 'q' . $q['questions_id']; 
                    $answer = $this->input->post($input_name);
                    $form_data[$field] = $answer;
                }
                
                // Get the selected visiting type
                $visiting_type = $this->input->post('q81'); // questions_id 81 is the main visiting question
                
                // Get the selected livestock type (Jenis Ternak)
                // We need to find the correct questions_id for Jenis Ternak
                $livestock_type = null;
                
                // First try to get from form_data
                foreach ($form_data as $field => $value) {
                    if ($field === 'jenis_ternak') {
                        $livestock_type = $value;
                        break;
                    }
                }
                
                // If not found in form_data, try to get from POST directly
                if (!$livestock_type) {
                    // Get all POST data to find Jenis Ternak
                    $post_data = $this->input->post();
                    foreach ($post_data as $key => $value) {
                        if (strpos($key, 'q') === 0) { // Check if it's a question field
                            // Get the questions_id from the key
                            $questions_id = substr($key, 1);
                            
                            // Check if this questions_id corresponds to Jenis Ternak
                            $question = $this->db->get_where('questions', ['questions_id' => $questions_id])->row_array();
                            if ($question && $question['field_name'] === 'jenis_ternak') {
                                $livestock_type = $value;
                                break;
                            }
                        }
                    }
                }
                
                // Debug log
                error_log("Livestock type found: " . ($livestock_type ? $livestock_type : 'NULL'));
                error_log("Form data: " . print_r($form_data, true));
                
                // Store data in session
                $this->session->set_userdata('visiting_form_data', $form_data);
                $this->session->set_userdata('visiting_type', $visiting_type);
                $this->session->set_userdata('livestock_type', $livestock_type);
                
                // Debug log for redirect
                error_log("About to redirect. Livestock type: " . ($livestock_type ? $livestock_type : 'NULL'));
                
                                 // Redirect based on livestock type
                 if ($livestock_type === 'Layer') {
                     error_log("Redirecting to Layer_Controller");
                     redirect('Layer_Controller/index');
                 } elseif ($livestock_type === 'Pullet') {
                     error_log("Redirecting to Pullet_Controller");
                     redirect('Pullet_Controller/index');
                 } elseif ($livestock_type === 'Bebek') {
                     error_log("Redirecting to Bebek_Controller");
                     redirect('Bebek_Controller/index');
                 } elseif ($livestock_type === 'Puyuh') {
                     error_log("Redirecting to Puyuh_Controller");
                     redirect('Puyuh_Controller/index');
                 } elseif (in_array($livestock_type, ['Arap', 'Sapi', 'Kambing', 'Babi', 'Other'])) {
                     error_log("Redirecting to Peternak_Other_Controller");
                     redirect('Peternak_Other_Controller/index');
                 } else {
                     // Default redirect for other livestock types
                     error_log("Redirecting to Visiting2_Controller (default)");
                     redirect('Visiting2_Controller/index');
                 }
            } elseif ($action === 'submit') {
                // This is for non-Peternak visiting types (Agen, Sub Agen, Kemitraan, Kantor, WFH/Cafe)
                // Get all form data including dynamic questions
                $form_data = [];
                
                // Get questions from visiting page
                $questions = $this->M_Questions->get_questions_by_page('visiting');
                foreach ($questions as $q) {
                    $field = $q['field_name']; 
                    $input_name = 'q' . $q['questions_id']; 
                    $answer = $this->input->post($input_name);
                    $form_data[$field] = $answer;
                }
                
                // Get visiting type
                $visiting_type = $this->input->post('q81');
                
                // Get dynamic questions based on visiting type
                $page_mapping = [
                    'Agen' => 'visiting_peternak',
                    'Peternak' => 'visiting_peternak',
                    'Kemitraan' => 'visiting_kemitraan',
                    'Sub Agen' => 'visiting_subagen',
                    'WFH/Cafe' => 'visiting_wfh_cafe',
                    'Kantor' => 'visiting_kantor'
                ];
                
                $page = isset($page_mapping[$visiting_type]) ? $page_mapping[$visiting_type] : 'visiting';
                $dynamic_questions = $this->M_Questions->get_questions_by_page($page);
                
                // Add dynamic questions to form data
                foreach ($dynamic_questions as $q) {
                    $field = $q['field_name']; 
                    $input_name = 'q' . $q['questions_id']; 
                    $answer = $this->input->post($input_name);
                    $form_data[$field] = $answer;
                }
                
                // Save complete data
                $data = [
                    'master_sub_area_id' => $user['master_sub_area_id'],
                    'visiting_type' => $visiting_type
                ];
                foreach ($form_data as $field => $value) {
                    $data[$field] = $value;
                }
                
                $this->visiting->insert_visiting($data);
                
                $this->session->set_flashdata('success', 'Data visiting berhasil disimpan!');
                redirect('Dashboard_new/index');
            } else {
                // This is the final submit from the second page
                // Get stored form data from session
                $form_data = $this->session->userdata('visiting_form_data');
                
                // Get questions for visiting page
                $questions = $this->M_Questions->get_questions_by_page('visiting');
                
                // Add answers to form data
                foreach ($questions as $q) {
                    $field = $q['field_name']; 
                    $input_name = 'q' . $q['questions_id']; 
                    $answer = $this->input->post($input_name);
                    $form_data[$field] = $answer;
                }
                
                // Save complete data
                $data = [
                    'master_sub_area_id' => $user['master_sub_area_id'] 
                ];
                foreach ($form_data as $field => $value) {
                    $data[$field] = $value;
                }
                
                $this->visiting->insert_visiting($data);
                
                // Clear session data
                $this->session->unset_userdata('visiting_form_data');
                
                $this->session->set_flashdata('success', 'Data visiting berhasil disimpan!');
                redirect('Dashboard_new/index');
            }
        }

        // Get nama_sub_area dari user ini
        $current_sub_area = $this->db->get_where('master_sub_area', 
            ['master_sub_area_id' => $user['master_sub_area_id']])->row_array();

        // Get questions dan options yang sesuai dengan area user
        $data['questions'] = $this->M_Questions->get_questions_by_page('visiting');
        $data['current_sub_area'] = $current_sub_area;
        
        // Filter opsi berdasarkan sub area user
        foreach ($data['questions'] as &$q) {
            if ($q['type'] === 'radio' || $q['type'] === 'select') {
                // Get options from options table based on master_sub_area_id
                if (isset($q['combine_options']) && !empty($q['combine_options'])) {
                    $combine_ids = explode(',', $q['combine_options']);
                    $this->db->select('o.*')
                            ->from('options o')
                            ->where_in('o.questions_id', $combine_ids)
                            ->where('o.master_sub_area_id', $user['master_sub_area_id']);
                    $q['options'] = $this->db->get()->result_array();
                } else {
                    $this->db->select('o.*')
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
                    $this->db->select('o.*')
                            ->from('options o')
                            ->where_in('o.questions_id', $combine_ids)
                            ->where('o.master_sub_area_id', 0);
                    $global_options = $this->db->get()->result_array();
                } else {
                    $this->db->select('o.*')
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
        $this->load->view('form_visiting_view', $data);
        $this->load->view('templates/dash_f', $data);
    }

    public function load_form_questions() {
        $token = $this->session->userdata('token');
        $user = $this->dash->getUserInfo($token)->row_array();
        
        $visiting_type = $this->input->post('visiting_type');
        
        // Map visiting type to page name
        $page_mapping = [
            'Agen' => 'visiting_peternak',
            'Peternak' => 'visiting_peternak',
            'Kemitraan' => 'visiting_kemitraan',
            'Sub Agen' => 'visiting_subagen',
            'WFH/Cafe' => 'visiting_wfh_cafe',
            'Kantor' => 'visiting_kantor'
        ];
        
        $page = isset($page_mapping[$visiting_type]) ? $page_mapping[$visiting_type] : 'visiting';
        
        // Get questions for the specific page
        $questions = $this->M_Questions->get_questions_by_page($page);
        
        // Filter opsi berdasarkan sub area user
        foreach ($questions as &$q) {
            if ($q['type'] === 'radio' || $q['type'] === 'select') {
                // Get options from options table based on master_sub_area_id
                if (isset($q['combine_options']) && !empty($q['combine_options'])) {
                    $combine_ids = explode(',', $q['combine_options']);
                    $this->db->select('o.*')
                            ->from('options o')
                            ->where_in('o.questions_id', $combine_ids)
                            ->where('o.master_sub_area_id', $user['master_sub_area_id']);
                    $q['options'] = $this->db->get()->result_array();
                } else {
                    $this->db->select('o.*')
                            ->from('options o')
                            ->where('o.questions_id', $q['questions_id'])
                            ->where('o.master_sub_area_id', $user['master_sub_area_id']);
                    $q['options'] = $this->db->get()->result_array();
                }
            }
        }
        
        // Tambahkan options global (master_sub_area_id = 0) untuk semua area
        foreach ($questions as &$q) {
            if ($q['type'] === 'radio' || $q['type'] === 'select') {
                // Get global options (master_sub_area_id = 0)
                if (isset($q['combine_options']) && !empty($q['combine_options'])) {
                    $combine_ids = explode(',', $q['combine_options']);
                    $this->db->select('o.*')
                            ->from('options o')
                            ->where_in('o.questions_id', $combine_ids)
                            ->where('o.master_sub_area_id', 0);
                    $global_options = $this->db->get()->result_array();
                } else {
                    $this->db->select('o.*')
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
        
        $data['questions'] = $questions;
        $data['visiting_type'] = $visiting_type;
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode($data);
    }
} 
