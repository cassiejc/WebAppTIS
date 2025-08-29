<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tambah_Data_Baru_Master_Controller extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper('form');
        $this->load->helper('url');
        $this->load->library('form_validation');
        $this->load->model('M_Dash', 'dash');
        $this->load->model('M_tambah_data_baru_master', 'tambah_data_baru_master');
        $this->load->model('M_Questions');
        $this->load->model('M_master_peternak');
        $this->load->model('M_master_subagen');
        $this->load->model('M_master_agen');
        $this->load->model('M_master_kemitraan');
        $this->load->model('M_master_farm');
        $this->load->model('M_master_lokasi_lainnya');
        $this->load->model('M_master_pakan');
        $this->load->model('M_master_strain'); // Add this line
    }

    // Method untuk Sub Agen
    public function subagen() {
        $this->_handle_form('Sub Agen', 'master_subagen');
    }
    
    // Method untuk Agen
    public function agen() {
        $this->_handle_form('Agen', 'master_agen');
    }
    
    // Method untuk Peternak
    public function peternak() {
        $this->_handle_form('Peternak', 'master_peternak');
    }
    
    // Method untuk Kemitraan
    public function kemitraan() {
        $this->_handle_form('Kemitraan', 'master_kemitraan');
    }
    
    // Method untuk Farm
    public function farm() {
        $this->_handle_form('Farm', 'master_farm');
    }
    
    // Method untuk Lokasi Baru
    public function lokasi_baru() {
        $this->_handle_form('Lokasi Baru', 'master_lokasi_lainnya');
    }
    
    // Method untuk Pakan
    public function pakan() {
        $this->_handle_form('Pakan', 'master_pakan');
    }

    // Method untuk Strain
    public function strain() {
        $this->_handle_form('Strain', 'master_strain');
    }

    // Method umum untuk handle form
    private function _handle_form($kategori, $page) {
        $token = $this->session->userdata('token');
        $user = $this->dash->getUserInfo($token)->row_array();
        $submit = $this->input->post('submit_form');
        
        // Initialize data array
        $data = [
            'kategori_selected' => $kategori,
            'questions_kategori' => [],
            'page_title' => 'Tambah ' . $kategori
        ];

        // Get specific questions based on category
        $data['questions_kategori'] = $this->M_Questions->get_questions_by_page($page);
        
        // Process options for each question based on category
        $this->_process_options($data['questions_kategori'], $kategori, $user);
        
        // Process form submission
        if ($submit && !empty($data['questions_kategori'])) {
            $this->_process_form_submission($data['questions_kategori'], $kategori, $user, $page);
        }

        // Load views
        $this->load->view('templates/dash_h', $data);
        $this->load->view('form_tambah_data_baru_master_view', $data);
        $this->load->view('templates/dash_f', $data);
    }

    private function _process_options(&$questions_kategori, $kategori, $user) {
        switch($kategori) {
            case 'Sub Agen':
                foreach($questions_kategori as &$q) {
                    if ($q['type'] === 'radio' || $q['type'] === 'select') {
                        if (isset($q['combine_options']) && !empty($q['combine_options'])) {
                            $combine_ids = explode(',', $q['combine_options']);
                            $this->db->select('o.option_text')
                                     ->from('options o')
                                     ->where_in('o.questions_id', $combine_ids)
                                     ->where('o.master_sub_area_id', $user['master_sub_area_id']);
                            $q['options'] = $this->db->get()->result_array();
                        } else {
                            $this->db->select('o.option_text')
                                     ->from('options o')
                                     ->where('o.questions_id', $q['questions_id'])
                                     ->where('o.master_sub_area_id', $user['master_sub_area_id']);
                            $q['options'] = $this->db->get()->result_array();
                        }
                    }
                }
                break;
                
            case 'Peternak':
                foreach($questions_kategori as &$q) {
                    if ($q['type'] === 'radio' || $q['type'] === 'select') {
                        if ($q['field_name'] == 'jenis_peternak') {
                            $this->db->select('o.option_text')
                                     ->from('options o')
                                     ->where('o.questions_id', $q['questions_id']);
                            $q['options'] = $this->db->get()->result_array();
                        }
                        elseif (in_array($q['field_name'], ['agen_dari', 'sub_agen_dari', 'kemitraan_dari'])) {
                            if (isset($q['combine_options']) && !empty($q['combine_options'])) {
                                $combine_ids = explode(',', $q['combine_options']);
                                $this->db->select('o.option_text')
                                         ->from('options o')
                                         ->where_in('o.questions_id', $combine_ids)
                                         ->where('o.master_sub_area_id', $user['master_sub_area_id']);
                                $q['options'] = $this->db->get()->result_array();
                            } else {
                                $this->db->select('o.option_text')
                                         ->from('options o')
                                         ->where('o.questions_id', $q['questions_id'])
                                         ->where('o.master_sub_area_id', $user['master_sub_area_id']);
                                $q['options'] = $this->db->get()->result_array();
                            }
                        }
                        else {
                            $this->db->select('o.option_text')
                                     ->from('options o')
                                     ->where('o.questions_id', $q['questions_id']);
                            $q['options'] = $this->db->get()->result_array();
                        }
                    }
                }
                break;
                
            case 'Farm':
                foreach($questions_kategori as &$q) {
                    if ($q['type'] === 'radio' || $q['type'] === 'select') {
                        if ($q['field_name'] == 'tipe_ternak') {
                            $this->db->select('o.option_text')
                                     ->from('options o')
                                     ->where('o.questions_id', $q['questions_id']);
                            $q['options'] = $this->db->get()->result_array();
                        }
                        elseif (isset($q['combine_options']) && !empty($q['combine_options'])) {
                            $combine_ids = explode(',', $q['combine_options']);
                            $this->db->select('o.option_text')
                                     ->from('options o')
                                     ->where_in('o.questions_id', $combine_ids)
                                     ->where('o.master_sub_area_id', $user['master_sub_area_id']);
                            $q['options'] = $this->db->get()->result_array();
                        } else {
                            $this->db->select('o.option_text')
                                     ->from('options o')
                                     ->where('o.questions_id', $q['questions_id'])
                                     ->where('o.master_sub_area_id', $user['master_sub_area_id']);
                            $q['options'] = $this->db->get()->result_array();
                        }
                    }
                }
                break;
                
            case 'Pakan':
                foreach($questions_kategori as &$q) {
                    if ($q['type'] === 'radio' || $q['type'] === 'select') {
                        if ($q['field_name'] == 'tipe_ternak') {
                            $this->db->select('o.option_text')
                                     ->from('options o')
                                     ->where('o.questions_id', $q['questions_id']);
                            $q['options'] = $this->db->get()->result_array();
                        } else {
                            $this->db->select('o.option_text')
                                     ->from('options o')
                                     ->where('o.questions_id', $q['questions_id']);
                            $q['options'] = $this->db->get()->result_array();
                        }
                    }
                }
                break;
                
            case 'Strain':
                foreach($questions_kategori as &$q) {
                    if ($q['type'] === 'radio' || $q['type'] === 'select') {
                        if ($q['field_name'] == 'tipe_ternak') {
                            $this->db->select('o.option_text')
                                     ->from('options o')
                                     ->where('o.questions_id', $q['questions_id']);
                            $q['options'] = $this->db->get()->result_array();
                        } else {
                            $this->db->select('o.option_text')
                                     ->from('options o')
                                     ->where('o.questions_id', $q['questions_id']);
                            $q['options'] = $this->db->get()->result_array();
                        }
                    }
                }
                break;
        }
    }

    private function _process_form_submission($questions_kategori, $kategori, $user, $page) {
        $save_data = [
            'master_sub_area_id' => $user['master_sub_area_id']
        ];
        
        // Get the selected jenis peternak for validation
        $jenis_peternak = null;
        foreach ($questions_kategori as $q) {
            if ($q['field_name'] == 'jenis_peternak') {
                $input_name = 'q' . $q['questions_id'];
                $jenis_peternak = $this->input->post($input_name);
                break;
            }
        }
        
        // Process each question
        foreach ($questions_kategori as $q) {
            $field = $q['field_name'];
            $input_name = 'q' . $q['questions_id'];
            $jawaban = $this->input->post($input_name);
            
            // Validate required fields
            $should_be_required = false;
            if (!empty($q['required'])) {
                if (in_array($field, ['agen_dari', 'sub_agen_dari', 'kemitraan_dari'])) {
                    if (($field == 'agen_dari' && $jenis_peternak == 'Agen') ||
                        ($field == 'sub_agen_dari' && $jenis_peternak == 'Sub Agen') ||
                        ($field == 'kemitraan_dari' && $jenis_peternak == 'Kemitraan')) {
                        $should_be_required = true;
                    }
                } else {
                    $should_be_required = true;
                }
            }
            
            if ($should_be_required && empty($jawaban)) {
                $this->session->set_flashdata('error', 'Mohon isi semua field yang wajib diisi');
                redirect(current_url());
                return;
            }
            
            $save_data[$field] = $jawaban;
        }
        
        // Save data based on category
        $this->_save_data($kategori, $save_data, $user, $page);
        
        $this->session->set_flashdata('success', 'Data berhasil disimpan!');
        
        // Redirect based on category
        if ($kategori == 'Peternak') {
            redirect('Doc_Peternak_Baru_Controller/index');
        } else {
            redirect('Dashboard_new/index');
        }
    }

    private function _save_data($kategori, $save_data, $user, $page) {
        switch($kategori) {
            case 'Agen':
                $this->M_master_agen->insert_master_agen($save_data);
                $this->_add_to_options($page, $save_data['nama_agen'], $user['master_sub_area_id']);
                break;
                
            case 'Kemitraan':
                $this->M_master_kemitraan->insert_master_kemitraan($save_data);
                $this->_add_to_options($page, $save_data['nama_kantor_kemitraan'], $user['master_sub_area_id']);
                break;
                
            case 'Sub Agen':
                $this->M_master_subagen->insert_master_subagen($save_data);
                $this->_add_to_options($page, $save_data['nama_subagen'], $user['master_sub_area_id']);
                break;
                
            case 'Peternak':
                // Process jenis_peternak combination
                if (!empty($save_data['jenis_peternak'])) {
                    $jenis = $save_data['jenis_peternak'];
                    $nama_dari = '';
                    
                    if ($jenis === 'Agen' && !empty($save_data['agen_dari'])) {
                        $nama_dari = $save_data['agen_dari'];
                    } elseif ($jenis === 'Sub Agen' && !empty($save_data['sub_agen_dari'])) {
                        $nama_dari = $save_data['sub_agen_dari'];
                    } elseif ($jenis === 'Kemitraan' && !empty($save_data['kemitraan_dari'])) {
                        $nama_dari = $save_data['kemitraan_dari'];
                    }
                    
                    $save_data['jenis_peternak'] = !empty($nama_dari) ? "$jenis: $nama_dari" : $jenis;
                }
                
                unset($save_data['agen_dari']);
                unset($save_data['sub_agen_dari']); 
                unset($save_data['kemitraan_dari']);
                
                $this->M_master_peternak->insert_master_peternak($save_data);
                $this->_add_to_options($page, $save_data['nama_peternak'], $user['master_sub_area_id']);
                break;
                
            case 'Farm':
                // Get master_peternak_id
                if (!empty($save_data['nama_peternak'])) {
                    $peternak = $this->db->select('master_peternak_id')
                                        ->from('master_peternak')
                                        ->where('nama_peternak', $save_data['nama_peternak'])
                                        ->where('master_sub_area_id', $user['master_sub_area_id'])
                                        ->get()
                                        ->row();
                    
                    if ($peternak) {
                        $save_data['master_peternak_id'] = $peternak->master_peternak_id;
                    }
                }
                
                $save_data['id_user'] = $user['id_user'];
                $this->M_master_farm->insert_master_farm($save_data);
                // Menambahkan parameter id_user saat memanggil _add_farm_to_options
                $this->_add_farm_to_options($save_data, $user['master_sub_area_id'], $user['id_user']);
                break;
                
            case 'Lokasi Baru':
                $this->M_master_lokasi_lainnya->insert_master_lokasi_lainnya($save_data);
                $this->_add_to_options($page, $save_data['nama_lokasi'], $user['master_sub_area_id']);
                break;
                
            case 'Pakan':
                unset($save_data['master_sub_area_id']);
                $this->M_master_pakan->insert_master_pakan($save_data);
                $this->_add_pakan_to_options($save_data);
                break;
                
            case 'Strain':
                unset($save_data['master_sub_area_id']);
                $this->M_master_strain->insert_master_strain($save_data);
                $this->_add_strain_to_options($save_data);
                break;
        }
    }

    private function _add_to_options($page, $option_text, $master_sub_area_id) {
        $questions = $this->db->select('questions_id')
                             ->from('questions')
                             ->where('page', $page)
                             ->where('add_to_options', 1)
                             ->get()
                             ->result_array();
                     
        foreach ($questions as $question) {
            $options_data = [
                'questions_id' => $question['questions_id'],
                'option_text' => $option_text,
                'master_sub_area_id' => $master_sub_area_id
            ];

            $existing = $this->db->where($options_data)->get('options')->num_rows();
            if ($existing == 0) {
                $this->db->insert('options', $options_data);
            }
        }
    }

    private function _add_farm_to_options($save_data, $master_sub_area_id, $id_user) {
    $questions = $this->db->select('questions_id, field_name')
                         ->from('questions')
                         ->where('page', 'master_farm')
                         ->where('add_to_options', 1)
                         ->get()
                         ->result_array();
                 
    foreach ($questions as $question) {
        if ($question['field_name'] === 'nama_farm') {
            $options_data = [
                'questions_id' => $question['questions_id'],
                'option_text' => $save_data['nama_farm'],
                'nama_peternak' => $save_data['nama_peternak'],
                'tipe_ternak' => $save_data['tipe_ternak'],
                'master_sub_area_id' => $master_sub_area_id,
                'id_user' => $id_user // Menambahkan id_user khusus untuk farm
            ];

            $existing = $this->db->where([
                'questions_id' => $question['questions_id'],
                'option_text' => $save_data['nama_farm'],
                'master_sub_area_id' => $master_sub_area_id
            ])->get('options')->num_rows();

            if ($existing == 0) {
                $this->db->insert('options', $options_data);
            }
        }
    }
}

    private function _add_pakan_to_options($save_data) {
    $questions = $this->db->select('questions_id, field_name')
                         ->from('questions')
                         ->where('page', 'master_pakan')
                         ->where('add_to_options', 1)
                         ->get()
                         ->result_array();
                        
    foreach ($questions as $question) {
        if ($question['field_name'] === 'nama_pakan' && !empty($save_data['nama_pakan'])) {
            $options_data = [
                'questions_id' => $question['questions_id'],
                'option_text'  => $save_data['nama_pakan'],
                'tipe_ternak'  => $save_data['tipe_ternak']
            ];

            // langsung insert tanpa cek duplikat
            $this->db->insert('options', $options_data);
        }
    }
}


    private function _add_strain_to_options($save_data) {
        $questions = $this->db->select('questions_id, field_name')
                             ->from('questions')
                             ->where('page', 'master_strain')
                             ->where('add_to_options', 1)
                             ->get()
                             ->result_array();
                        
        foreach ($questions as $question) {
            if ($question['field_name'] === 'nama_strain' && !empty($save_data['nama_strain'])) {
                $options_data = [
                    'questions_id' => $question['questions_id'],
                    'option_text' => $save_data['nama_strain'],
                    'tipe_ternak' => $save_data['tipe_ternak']
                ];

                $existing = $this->db->where([
                    'questions_id' => $question['questions_id'],
                    'option_text' => $save_data['nama_strain']
                ])->get('options')->num_rows();

                if ($existing == 0 && !empty($options_data['option_text'])) {
                    $this->db->insert('options', $options_data);
                }
            }
        }
    }

    // Keep the old index method for backward compatibility (can be removed later)
    public function index() {
        redirect('Dashboard_new/index');
    }
}
