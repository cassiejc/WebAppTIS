<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Visiting_Petelur_Controller extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper('form');
        $this->load->helper('url');
        $this->load->library('form_validation');
        $this->load->model('M_Dash', 'dash');
        // $this->load->model('M_visiting_petelur', 'visiting_petelur'); // Model not found, using direct DB instead
        $this->load->model('M_Questions');
    }

    public function index() {
        // Get user info from session
        $token = $this->session->userdata('token');
        $user = $this->dash->getUserInfo($token)->row_array();
        
        // Get current sub area name
        $current_sub_area = $this->db->select('nama_sub_area')
                                    ->from('master_sub_area')
                                    ->where('master_sub_area_id', $user['master_sub_area_id'])
                                    ->get()
                                    ->row_array();
        
        $data['current_sub_area'] = $current_sub_area;
        
        // Process form submission
        if ($this->input->method() === 'post') {
            // Get the selected tipe_ternak to determine which questions were used
            $tipe_ternak = $this->input->post('tipe_ternak');
            $page = ($tipe_ternak === 'Layer') ? 'layer' : 'visiting_petelur';
            
            $questions = $this->M_Questions->get_questions_by_page($page);
            $data = [
                'id_user' => $user['id_user'],
                'master_sub_area_id' => $user['master_sub_area_id']
            ];
            
            foreach ($questions as $q) {
                $field = $q['field_name'];
                $input_name = 'q' . $q['questions_id'];
                $data[$field] = $this->input->post($input_name);
            }
            
            // Insert data using direct database query instead of model
            $this->db->insert('visiting_petelur', $data);
            $this->session->set_flashdata('success', 'Data visiting petelur berhasil disimpan!');
            redirect('Dashboard_new/index');
        }

        // Get initial questions for visiting_petelur page and ensure tipe_ternak is first 
        $all_questions = $this->M_Questions->get_questions_by_page('visiting_petelur');
        $data['questions'] = array();
        $other_questions = array();
        
        // Separate tipe_ternak question from others
        foreach($all_questions as $q) {
            if ($q['field_name'] === 'tipe_ternak') {
                // Put tipe_ternak first
                array_unshift($data['questions'], $q);
            } else {
                $other_questions[] = $q;
            }
        }
        
        // Add other questions after tipe_ternak
        $data['questions'] = array_merge($data['questions'], $other_questions);
        
        // Get options for select fields
        foreach($data['questions'] as &$q) {
            if ($q['type'] === 'select') {
                if ($q['field_name'] === 'nama_farm') {
                    // Get farm options with filtering
                    $this->db->select('o.option_text')
                             ->from('options o')
                             ->where('o.questions_id', $q['questions_id'])
                             ->where('o.master_sub_area_id', $user['master_sub_area_id']);
                    $q['options'] = $this->db->get()->result_array();
                }
                else if ($q['field_name'] === 'pakan_petelur') {
                    // Get pakan options
                    $this->db->select('o.option_text')
                             ->from('options o')
                             ->where('o.questions_id', $q['questions_id']);
                    $q['options'] = $this->db->get()->result_array();
                }
                else if ($q['field_name'] === 'tipe_ternak') {
                    // Get tipe ternak options
                    $this->db->select('o.option_text')
                             ->from('options o')
                             ->where('o.questions_id', $q['questions_id']);
                    $q['options'] = $this->db->get()->result_array();
                }
            }
        }

        // Load views
        $this->load->view('templates/dash_h', $data);
        $this->load->view('form_visiting_petelur_view', $data);
        $this->load->view('templates/dash_f', $data);
    }

    // AJAX method to get questions based on tipe_ternak selection
    public function get_questions_by_type() {
        $tipe_ternak = $this->input->post('tipe_ternak');
        $page = ($tipe_ternak === 'Layer') ? 'layer' : 'visiting_petelur';
        
        // Get user info for filtering options
        $token = $this->session->userdata('token');
        $user = $this->dash->getUserInfo($token)->row_array();
        
        $questions = $this->M_Questions->get_questions_by_page($page);
        
        // FIXED: Don't add tipe_ternak question again for visiting_petelur page
        // Only add it for layer page since layer page doesn't have tipe_ternak originally
        if ($page === 'layer') {
            // For layer page, add tipe_ternak question manually at the top
            $tipe_ternak_question = $this->M_Questions->get_questions_by_page('visiting_petelur');
            foreach($tipe_ternak_question as $q) {
                if ($q['field_name'] === 'tipe_ternak') {
                    // Get options for tipe_ternak
                    $this->db->select('o.option_text')
                             ->from('options o')
                             ->where('o.questions_id', $q['questions_id']);
                    $q['options'] = $this->db->get()->result_array();
                    
                    // Add it to the beginning of questions array
                    array_unshift($questions, $q);
                    break;
                }
            }
        }
        
        // Get options for other select fields
        foreach($questions as &$q) {
            if ($q['type'] === 'select' && $q['field_name'] !== 'tipe_ternak') {
                if ($q['field_name'] === 'nama_farm' || 
                    ($tipe_ternak === 'Layer' && $q['field_name'] === 'layer_nama_farm')) {
                    // SOLUTION: For Layer type, use nama_farm options even for layer_nama_farm field
                    // Get the questions_id for nama_farm from visiting_petelur page
                    if ($tipe_ternak === 'Layer' && $q['field_name'] === 'layer_nama_farm') {
                        // Find nama_farm question from visiting_petelur page
                        $nama_farm_questions = $this->M_Questions->get_questions_by_page('visiting_petelur');
                        $nama_farm_question_id = null;
                        
                        foreach($nama_farm_questions as $nf_q) {
                            if ($nf_q['field_name'] === 'nama_farm') {
                                $nama_farm_question_id = $nf_q['questions_id'];
                                break;
                            }
                        }
                        
                        // Use nama_farm question_id for options
                        if ($nama_farm_question_id) {
                            $this->db->select('o.option_text')
                                     ->from('options o')
                                     ->where('o.questions_id', $nama_farm_question_id)
                                     ->where('o.master_sub_area_id', $user['master_sub_area_id']);
                            $q['options'] = $this->db->get()->result_array();
                        }
                    } else {
                        // Normal nama_farm handling
                        $this->db->select('o.option_text')
                                 ->from('options o')
                                 ->where('o.questions_id', $q['questions_id'])
                                 ->where('o.master_sub_area_id', $user['master_sub_area_id']);
                        $q['options'] = $this->db->get()->result_array();
                    }
                }
                else {
                    // Other select fields (pakan_petelur, etc.)
                    $this->db->select('o.option_text')
                             ->from('options o')
                             ->where('o.questions_id', $q['questions_id']);
                    $q['options'] = $this->db->get()->result_array();
                }
            }
        }
        
        echo json_encode($questions);
    }
}
