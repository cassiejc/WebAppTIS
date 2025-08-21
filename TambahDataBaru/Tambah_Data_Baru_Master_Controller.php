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


    }

    public function index() {
        $token = $this->session->userdata('token');
        $user = $this->dash->getUserInfo($token)->row_array();
        $kategori = $this->input->post('kategori_tambah');
        $submit = $this->input->post('submit_form');
        
        // Initialize data array
        $data = [
            'kategori_selected' => $kategori,
            'questions_kategori' => []
        ];

        // Get initial category selection questions
        $data['questions'] = $this->M_Questions->get_questions_by_page('tambah_data_baru_master');
        
        // If category is selected, get specific questions
        if ($kategori) {
            switch($kategori) {
                case 'Agen':
                    $data['questions_kategori'] = $this->M_Questions->get_questions_by_page('master_agen');
                    break;
                case 'Kemitraan':
                    $data['questions_kategori'] = $this->M_Questions->get_questions_by_page('master_kemitraan');
                    break;
                case 'Sub Agen':
                    $data['questions_kategori'] = $this->M_Questions->get_questions_by_page('master_subagen');
                    
                    // Get list of Agen from options table
                    foreach($data['questions_kategori'] as &$q) {
                        if ($q['type'] === 'radio' || $q['type'] === 'select') {
                            if (isset($q['combine_options']) && !empty($q['combine_options'])) {
                                // Get combined options from multiple questions
                                $combine_ids = explode(',', $q['combine_options']);
                                $this->db->select('o.option_text')
                                         ->from('options o')
                                         ->where_in('o.questions_id', $combine_ids)
                                         ->where('o.master_sub_area_id', $user['master_sub_area_id']);
                                $q['options'] = $this->db->get()->result_array();
                            } else {
                                // Regular options for this question
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
                    $data['questions_kategori'] = $this->M_Questions->get_questions_by_page('master_peternak');
    
                    // Get list of options from options table with filtering
                    foreach($data['questions_kategori'] as &$q) {
                        if ($q['type'] === 'radio' || $q['type'] === 'select') {
                            if ($q['field_name'] == 'jenis_peternak') {
                                // Regular options for jenis_peternak
                                $this->db->select('o.option_text')
                                         ->from('options o')
                                         ->where('o.questions_id', $q['questions_id']);
                                $q['options'] = $this->db->get()->result_array();
                            }
                            elseif (in_array($q['field_name'], ['agen_dari', 'sub_agen_dari', 'kemitraan_dari'])) {
                                // Filter options based on master_sub_area_id for dependent fields
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
                                // Regular options for other fields
                                $this->db->select('o.option_text')
                                         ->from('options o')
                                         ->where('o.questions_id', $q['questions_id']);
                                $q['options'] = $this->db->get()->result_array();
                            }
                        }
                    }
                    break;
                case 'Farm':
                    $data['questions_kategori'] = $this->M_Questions->get_questions_by_page('master_farm');
                    // Get list of options
                    foreach($data['questions_kategori'] as &$q) {
                        if ($q['type'] === 'radio' || $q['type'] === 'select') {
                            if ($q['field_name'] == 'tipe_ternak') {
                                // Regular options for tipe_ternak
                                $this->db->select('o.option_text')
                                         ->from('options o')
                                         ->where('o.questions_id', $q['questions_id']);
                                $q['options'] = $this->db->get()->result_array();
                            }
                            elseif (isset($q['combine_options']) && !empty($q['combine_options'])) {
                                // Get combined options from multiple questions
                                $combine_ids = explode(',', $q['combine_options']);
                                $this->db->select('o.option_text')
                                         ->from('options o')
                                         ->where_in('o.questions_id', $combine_ids)
                                         ->where('o.master_sub_area_id', $user['master_sub_area_id']);
                                $q['options'] = $this->db->get()->result_array();
                            } else {
                                // Regular options for other questions
                                $this->db->select('o.option_text')
                                         ->from('options o')
                                         ->where('o.questions_id', $q['questions_id'])
                                         ->where('o.master_sub_area_id', $user['master_sub_area_id']);
                                $q['options'] = $this->db->get()->result_array();
                            }
                        }
                    }
                    break;    
                case 'Lokasi Baru':
                    $data['questions_kategori'] = $this->M_Questions->get_questions_by_page('master_lokasi_lainnya');
                    break;  
                case 'Pakan':
                    $data['questions_kategori'] = $this->M_Questions->get_questions_by_page('master_pakan');
                    // Get list of options
                    foreach($data['questions_kategori'] as &$q) {
                        if ($q['type'] === 'radio' || $q['type'] === 'select') {
                            if ($q['field_name'] == 'tipe_ternak') {
                                // Regular options for tipe_ternak
                                $this->db->select('o.option_text')
                                         ->from('options o')
                                         ->where('o.questions_id', $q['questions_id']);
                                $q['options'] = $this->db->get()->result_array();
                            } else {
                                // Regular options for other questions
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

        // Process form submission only when submit button is clicked
        if ($submit && !empty($data['questions_kategori'])) {
            $save_data = [
                'master_sub_area_id' => $user['master_sub_area_id']
            ];
            
            // Get the selected jenis peternak to determine which fields should be required
            $jenis_peternak = null;
            foreach ($data['questions_kategori'] as $q) {
                if ($q['field_name'] == 'jenis_peternak') {
                    $input_name = 'q' . $q['questions_id'];
                    $jenis_peternak = $this->input->post($input_name);
                    break;
                }
            }
            
            foreach ($data['questions_kategori'] as $q) {
                $field = $q['field_name'];
                $input_name = 'q' . $q['questions_id'];
                $jawaban = $this->input->post($input_name);
                
                // Determine if this field should be required based on jenis_peternak selection
                $should_be_required = false;
                if (!empty($q['required'])) {
                    if (in_array($field, ['agen_dari', 'sub_agen_dari', 'kemitraan_dari'])) {
                        // These fields are only required based on jenis_peternak selection
                        if (($field == 'agen_dari' && $jenis_peternak == 'Agen') ||
                            ($field == 'sub_agen_dari' && $jenis_peternak == 'Sub Agen') ||
                            ($field == 'kemitraan_dari' && $jenis_peternak == 'Kemitraan')) {
                            $should_be_required = true;
                        }
                    } else {
                        // Other required fields are always required
                        $should_be_required = true;
                    }
                }
                
                // Validate required fields
                if ($should_be_required && empty($jawaban)) {
                    $this->session->set_flashdata('error', 'Mohon isi semua field yang wajib diisi');
                    redirect('Tambah_Data_Baru_Master_Controller/index');
                    return;
                }
                
                $save_data[$field] = $jawaban;
            }
            
            // Save data based on category
            switch($kategori) {
                case 'Agen':
                    // Insert to master_agen table
                    $this->M_master_agen->insert_master_agen($save_data);
                    
                    // Get questions that need their options added
                    $questions = $this->db->select('questions_id')
                                         ->from('questions')
                                         ->where('page', 'master_agen')
                                         ->where('add_to_options', 1)
                                         ->get()
                                         ->result_array();
                         
                    foreach ($questions as $question) {
                        $options_data = [
                            'questions_id' => $question['questions_id'],
                            'option_text' => $save_data['nama_agen'],
                            'master_sub_area_id' => $user['master_sub_area_id']
                        ];
        
                        // Check if option already exists
                        $existing = $this->db->where($options_data)->get('options')->num_rows();
        
                        if ($existing == 0) {
                            $this->db->insert('options', $options_data);
                        }
                    }
                    break;
                case 'Kemitraan':
                    // Insert to master_kemitraan table
                    $this->M_master_kemitraan->insert_master_kemitraan($save_data);
                    
                    // Get questions that need their options added
                    $questions = $this->db->select('questions_id')
                                         ->from('questions')
                                         ->where('page', 'master_kemitraan')
                                         ->where('add_to_options', 1)
                                         ->get()
                                         ->result_array();
                         
                    foreach ($questions as $question) {
                        $options_data = [
                            'questions_id' => $question['questions_id'],
                            'option_text' => $save_data['nama_kantor_kemitraan'],
                            'master_sub_area_id' => $user['master_sub_area_id']
                        ];

                        // Check if option already exists
                        $existing = $this->db->where($options_data)->get('options')->num_rows();

                        if ($existing == 0) {
                            $this->db->insert('options', $options_data);
                        }
                    }
                    break;
                case 'Sub Agen':
                    $this->M_master_subagen->insert_master_subagen($save_data);
                    // Get questions that need their options added
                    $questions = $this->db->select('questions_id')
                                         ->from('questions')
                                         ->where('page', 'master_subagen')
                                         ->where('add_to_options', 1)
                                         ->get()
                                         ->result_array();
                         
                    foreach ($questions as $question) {
                        $options_data = [
                            'questions_id' => $question['questions_id'],
                            'option_text' => $save_data['nama_subagen'],
                            'master_sub_area_id' => $user['master_sub_area_id']
                        ];

                        // Check if option already exists
                        $existing = $this->db->where($options_data)->get('options')->num_rows();

                        if ($existing == 0) {
                            $this->db->insert('options', $options_data);
                        }
                    }
                    break;                
                case 'Peternak':
                    // Sebelum insert, gabungkan jenis_peternak dan nama yang dipilih
                    if (!empty($save_data['jenis_peternak'])) {
                        $jenis = $save_data['jenis_peternak'];
                        $nama_dari = '';
                        
                        // Ambil nama berdasarkan jenis peternak
                        if ($jenis === 'Agen' && !empty($save_data['agen_dari'])) {
                            $nama_dari = $save_data['agen_dari'];
                        } elseif ($jenis === 'Sub Agen' && !empty($save_data['sub_agen_dari'])) {
                            $nama_dari = $save_data['sub_agen_dari'];
                        } elseif ($jenis === 'Kemitraan' && !empty($save_data['kemitraan_dari'])) {
                            $nama_dari = $save_data['kemitraan_dari'];
                        }
                        
                        // Gabungkan dalam format "Jenis: Nama"
                        $save_data['jenis_peternak'] = !empty($nama_dari) ? "$jenis: $nama_dari" : $jenis;
                    }
                    
                    // Hapus field yang tidak digunakan
                    unset($save_data['agen_dari']);
                    unset($save_data['sub_agen_dari']); 
                    unset($save_data['kemitraan_dari']);
                    
                    $this->M_master_peternak->insert_master_peternak($save_data);
                    
                    // Get questions that need their options added
                    $questions = $this->db->select('questions_id')
                                         ->from('questions')
                                         ->where('page', 'master_peternak')
                                         ->where('add_to_options', 1)
                                         ->get()
                                         ->result_array();
                         
                    foreach ($questions as $question) {
                        $options_data = [
                            'questions_id' => $question['questions_id'],
                            'option_text' => $save_data['nama_peternak'],
                            'master_sub_area_id' => $user['master_sub_area_id']
                        ];

                        // Check if option already exists
                        $existing = $this->db->where($options_data)->get('options')->num_rows();

                        if ($existing == 0) {
                            $this->db->insert('options', $options_data);
                        }
                    }
                    
                    $this->session->set_flashdata('success', 'Data peternak berhasil disimpan!');
                    redirect('Doc_Peternak_Baru_Controller/index'); // Changed redirect
                    break;
                case 'Farm':
                    // Get master_peternak_id based on selected nama_peternak
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
                    
                    // Add id_user from logged in user
                    $save_data['id_user'] = $user['id_user'];
                    
                    // Make sure tipe_ternak is included in save_data
                    if (empty($save_data['tipe_ternak'])) {
                        // Get the questions_id for tipe_ternak
                        $tipe_ternak_q = $this->db->select('questions_id')
                                                 ->from('questions')
                                                 ->where('field_name', 'tipe_ternak')
                                                 ->where('page', 'master_farm')
                                                 ->get()
                                                 ->row();
                                                 
                        if ($tipe_ternak_q) {
                            $input_name = 'q' . $tipe_ternak_q->questions_id;
                            $save_data['tipe_ternak'] = $this->input->post($input_name);
                        }
                    }
                    
                    // Insert to master_farm table
                    $this->M_master_farm->insert_master_farm($save_data);
                    
                    // Get questions that need their options added
                    $questions = $this->db->select('questions_id, field_name')
                                         ->from('questions')
                                         ->where('page', 'master_farm')
                                         ->where('add_to_options', 1)
                                         ->get()
                                         ->result_array();
                         
                    foreach ($questions as $question) {
                        // For nama_farm options
                        if ($question['field_name'] === 'nama_farm') {
                            $options_data = [
                                'questions_id' => $question['questions_id'],
                                'option_text' => $save_data['nama_farm'],
                                'nama_peternak' => $save_data['nama_peternak'],
                                'tipe_ternak' => $save_data['tipe_ternak'],
                                'master_sub_area_id' => $user['master_sub_area_id']
                            ];

                            // Check if option already exists
                            $existing = $this->db->where([
                                'questions_id' => $question['questions_id'],
                                'option_text' => $save_data['nama_farm'],
                                'master_sub_area_id' => $user['master_sub_area_id']
                            ])->get('options')->num_rows();

                            if ($existing == 0) {
                                $this->db->insert('options', $options_data);
                            }
                        }
                    }
                    break;
                case 'Lokasi Baru':
                    $this->M_master_lokasi_lainnya->insert_master_lokasi_lainnya($save_data);
                                        $questions = $this->db->select('questions_id')
                                         ->from('questions')
                                         ->where('page', 'master_lokasi_lainnya')
                                         ->where('add_to_options', 1)
                                         ->get()
                                         ->result_array();
                         
                    foreach ($questions as $question) {
                        $options_data = [
                            'questions_id' => $question['questions_id'],
                            'option_text' => $save_data['nama_lokasi'],
                            'master_sub_area_id' => $user['master_sub_area_id']
                        ];

                        // Check if option already exists
                        $existing = $this->db->where($options_data)->get('options')->num_rows();

                        if ($existing == 0) {
                            $this->db->insert('options', $options_data);
                        }
                    }
                    break;
                case 'Pakan':
                    // Remove master_sub_area_id from save_data for Pakan
                    unset($save_data['master_sub_area_id']);
                    
                    // Get questions_id for nama_pakan
                    $nama_pakan_q = $this->db->select('questions_id')
                            ->from('questions')
                            ->where('field_name', 'nama_pakan')
                            ->where('page', 'master_pakan')
                            ->get()
                            ->row();

                    if ($nama_pakan_q) {
                        $input_name = 'q' . $nama_pakan_q->questions_id;
                        $save_data['nama_pakan'] = $this->input->post($input_name);
                    }
                    
                    // Insert to master_pakan table
                    $this->M_master_pakan->insert_master_pakan($save_data);

                    // Get questions that need their options added
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
                                'option_text' => $save_data['nama_pakan'],
                                'tipe_ternak' => $save_data['tipe_ternak']
                            ];

                            // Check if option already exists
                            $existing = $this->db->where([
                                'questions_id' => $question['questions_id'],
                                'option_text' => $save_data['nama_pakan']
                            ])->get('options')->num_rows();

                            if ($existing == 0 && !empty($options_data['option_text'])) {
                                $this->db->insert('options', $options_data);
                            }
                        }
                    }
                    break;
            }
            
            $this->session->set_flashdata('success', 'Data berhasil disimpan!');
            redirect('Dashboard_new/index');
        }

        // Load views
        $this->load->view('templates/dash_h', $data);
        $this->load->view('form_tambah_data_baru_master_view', $data);
        $this->load->view('templates/dash_f', $data);
    }
}
