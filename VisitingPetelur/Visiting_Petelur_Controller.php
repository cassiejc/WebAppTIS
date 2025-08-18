<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Visiting_Petelur_Controller extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper('form');
        $this->load->helper('url');
        $this->load->library('form_validation');
        $this->load->model('M_Dash', 'dash');
        // $this->load->model('M_visiting_petelur', 'visiting_petelur');
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
            $questions = $this->M_Questions->get_questions_by_page('visiting_petelur');
            $data = [
                'id_user' => $user['id_user'],
                'master_sub_area_id' => $user['master_sub_area_id']
            ];
            
            foreach ($questions as $q) {
                $field = $q['field_name'];
                $input_name = 'q' . $q['questions_id'];
                $data[$field] = $this->input->post($input_name);
            }
            
            $this->visiting_petelur->insert_visiting_petelur($data);
            $this->session->set_flashdata('success', 'Data visiting petelur berhasil disimpan!');
            redirect('Dashboard_new/index');
        }

        // Get questions and their options
        $data['questions'] = $this->M_Questions->get_questions_by_page('visiting_petelur');
        
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
            }
        }

        // Load views
        $this->load->view('templates/dash_h', $data);
        $this->load->view('form_visiting_petelur_view', $data);
        $this->load->view('templates/dash_f', $data);
    }
}
