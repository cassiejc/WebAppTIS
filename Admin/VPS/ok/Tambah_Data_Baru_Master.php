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
        $this->load->model('M_master_strain'); 
    }

    public function sub_agen() {
        $this->_handle_form('Sub Agen', 'master_subagen');
    }
    
    public function agen() {
        $this->_handle_form('Agen', 'master_agen');
    }
    
    public function peternak() {
        $this->_handle_form('Peternak', 'master_peternak');
    }
    
    public function kemitraan() {
        $this->_handle_form('Kemitraan', 'master_kemitraan');
    }
    
    public function farm() {
        $this->_handle_form('Farm', 'master_farm');
    }
    
    public function lokasi_baru() {
        $this->_handle_form('Lokasi Baru', 'master_lokasi_lainnya');
    }
    
    public function pakan() {
        $this->_handle_form('Pakan', 'master_pakan');
    }

    public function strain() {
        $this->_handle_form('Strain', 'master_strain');
    }

    private function _handle_form($kategori, $page) {
        $token = $this->session->userdata('token');
        $user = $this->dash->getUserInfo($token)->row_array();
        $submit = $this->input->post('submit_form');
        
        $data = [
            'kategori_selected' => $kategori,
            'questions_kategori' => [],
            'title' => "CP APPS"
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
                                    ->where_in('o.questions_id', $combine_ids);
                            $q['options'] = $this->db->get()->result_array();
                        } else {
                            $this->db->select('o.option_text')
                                    ->from('options o')
                                    ->where('o.questions_id', $q['questions_id'])
                                    ->where('o.master_area_id', $user['master_area_id']);
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
                                        ->where_in('o.questions_id', $combine_ids);
                                $q['options'] = $this->db->get()->result_array();
                            } else {
                                $this->db->select('o.option_text')
                                        ->from('options o')
                                        ->where('o.questions_id', $q['questions_id']);
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
                                    ->where_in('o.questions_id', $combine_ids);
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
                
            case 'Pakan':
                foreach($questions_kategori as &$q) {
                    if ($q['type'] === 'radio' || $q['type'] === 'select') {
                        // Untuk semua field di Pakan, ambil options dari database
                        $this->db->select('o.option_text')
                                ->from('options o')
                                ->where('o.questions_id', $q['questions_id']);
                        $q['options'] = $this->db->get()->result_array();
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

    // Ganti fungsi _process_form_submission dengan yang ini
    private function _process_form_submission($questions_kategori, $kategori, $user, $page) {
        $save_data = [];
        
        // Add appropriate area IDs based on category
        if ($kategori == 'Kemitraan') { // Kemitraan hanya butuh master_area_id
            $save_data['master_area_id'] = $user['master_area_id'];
        } elseif (in_array($kategori, ['Farm', 'Sub Agen'])) { // Farm dan Sub Agen butuh keduanya
            $save_data['master_sub_area_id'] = $user['master_sub_area_id'];
            $save_data['master_area_id'] = $user['master_area_id'];
        } elseif (!in_array($kategori, ['Peternak', 'Agen'])) {
            $save_data['master_sub_area_id'] = $user['master_sub_area_id'];
        }
        
        if (in_array($kategori, ['Sub Agen', 'Kemitraan'])) {
            $save_data['created_at'] = date('Y-m-d H:i:s');
            $save_data['id_user'] = $user['id_user'];
        }

        // Get conditional values
        $jenis_peternak = $this->input->post('q' . $this->_get_question_id_by_field('jenis_peternak', $questions_kategori));
        $tipe_ternak_pakan = $this->input->post('q' . $this->_get_question_id_by_field('tipe_ternak', $questions_kategori));
        $pilihan_pakan_layer = $this->input->post('q' . $this->_get_question_id_by_field('pilihan_pakan', $questions_kategori));

        // Process each question
        foreach ($questions_kategori as $q) {
            $field = $q['field_name'];
            $input_name = 'q' . $q['questions_id'];
            $jawaban = $this->input->post($input_name);

            // Dynamic validation logic
            $should_be_required = false;
            if (!empty($q['required'])) {
                if ($kategori === 'Pakan') {
                    // Aturan untuk form Pakan
                    if ($field === 'nama_pakan' && $tipe_ternak_pakan !== 'Layer') {
                        $should_be_required = true;
                    } elseif ($field === 'pilihan_pakan' && $tipe_ternak_pakan === 'Layer') {
                        $should_be_required = true;
                    } elseif ($field === 'layer_pilihan_pakan_cp' && $tipe_ternak_pakan === 'Layer' && $pilihan_pakan_layer === 'CP') {
                        $should_be_required = true;
                    } elseif ($field === 'layer_pilihan_pakan_lain' && $tipe_ternak_pakan === 'Layer' && $pilihan_pakan_layer === 'Non CP') {
                        $should_be_required = true;
                    } elseif (!in_array($field, ['nama_pakan', 'pilihan_pakan', 'layer_pilihan_pakan_cp', 'layer_pilihan_pakan_lain'])) {
                        // Wajib untuk field lain yang memang required
                        $should_be_required = true;
                    }
                } elseif ($kategori === 'Peternak') {
                    // Aturan untuk form Peternak
                    if (in_array($field, ['agen_dari', 'sub_agen_dari', 'kemitraan_dari'])) {
                        if (($field == 'agen_dari' && $jenis_peternak == 'Agen') ||
                            ($field == 'sub_agen_dari' && $jenis_peternak == 'Sub Agen') ||
                            ($field == 'kemitraan_dari' && $jenis_peternak == 'Kemitraan')) {
                            $should_be_required = true;
                        }
                    } else {
                        $should_be_required = true;
                    }
                } else {
                    // Aturan default untuk form lain
                    $should_be_required = true;
                }
            }
            
            if ($should_be_required && ($jawaban === '' || is_null($jawaban))) {
                $this->session->set_flashdata('error', 'Mohon isi semua field yang wajib diisi. Field "' . $q['question_text'] . '" tidak boleh kosong.');
                redirect(current_url());
                return;
            }
            
            $save_data[$field] = $jawaban;
        }

        // ... inside _process_form_submission ...
        if ($kategori === 'Pakan') {
            // Logic to handle 'Layer' pakan name
            if (isset($save_data['tipe_ternak']) && $save_data['tipe_ternak'] === 'Layer') {
                if (isset($save_data['pilihan_pakan'])) {
                    if ($save_data['pilihan_pakan'] === 'CP' && !empty($save_data['layer_pilihan_pakan_cp'])) {
                        // If 'CP' is chosen, use its value for nama_pakan
                        $save_data['nama_pakan'] = $save_data['layer_pilihan_pakan_cp'];
                    } elseif ($save_data['pilihan_pakan'] === 'Non CP' && !empty($save_data['layer_pilihan_pakan_lain'])) {
                        // If 'Non CP' is chosen, use its value for nama_pakan
                        $save_data['nama_pakan'] = $save_data['layer_pilihan_pakan_lain'];
                    }
                }
            }
            
        }
        
        if ($kategori === 'Farm' || $kategori === 'Sub Agen') {
            $save_data['latitude'] = $this->input->post('latitude');
            $save_data['longitude'] = $this->input->post('longitude');
            $save_data['location_address'] = $this->input->post('location_address');
        }         
        
        // Save data based on category
        $this->_save_data($kategori, $save_data, $user, $page);
        
        $this->session->set_flashdata('success', 'Data berhasil disimpan!');
        
        redirect('Dashboard_new/index');
    }

    // Tambahkan fungsi helper ini di dalam controller Anda
    private function _get_question_id_by_field($field_name, $questions) {
        foreach ($questions as $q) {
            if ($q['field_name'] == $field_name) {
                return $q['questions_id'];
            }
        }
        return null;
    }
   
    private function _save_data($kategori, $save_data, $user, $page) {
        // Add debugging for Lokasi Baru
        if ($kategori == 'Lokasi Baru') {
            log_message('debug', 'Saving Lokasi Baru with data: ' . print_r($save_data, true));
            log_message('debug', 'User info: ' . print_r($user, true));
            log_message('debug', 'Page: ' . $page);
        }
        
        switch($kategori) {
            case 'Agen':
                $this->M_master_agen->insert_master_agen($save_data);
                $this->_add_to_options($page, $save_data['nama_agen'], null, $user['master_area_id']);
                break;
                
            case 'Kemitraan':
                $this->M_master_kemitraan->insert_master_kemitraan($save_data);
                $this->_add_to_options($page, $save_data['nama_kantor_kemitraan'], null, $user['master_area_id']);
                break;
                
            case 'Sub Agen':
                $this->M_master_subagen->insert_master_subagen($save_data);
                // Kirimkan master_sub_area_id dan master_area_id
                $this->_add_to_options($page, $save_data['nama_subagen'], $user['master_sub_area_id'], $user['master_area_id']);
                break;
                
            case 'Peternak':
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
                $this->_add_peternak_to_options($page, $save_data['nama_peternak']);
                break;
                
            // case 'Farm':
            //     if (!empty($save_data['nama_peternak'])) {
            //         $peternak = $this->db->select('master_peternak_id')
            //                             ->from('master_peternak')
            //                             ->where('nama_peternak', $save_data['nama_peternak'])
            //                             ->get()
            //                             ->row();
                    
            //         if ($peternak) {
            //             $save_data['master_peternak_id'] = $peternak->master_peternak_id;
            //         }
            //     }
                
            //     $save_data['id_user'] = $user['id_user'];
            //     $save_data['created_at'] = date('Y-m-d H:i:s'); 
                
            //     $this->M_master_farm->insert_master_farm($save_data);
            //     $this->_add_farm_to_options($save_data, $user['master_sub_area_id'], $user['master_area_id'], $user['id_user']);
            //     break;

            case 'Farm':
            if (!empty($save_data['nama_peternak'])) {
                $peternak = $this->db->select('master_peternak_id')
                                    ->from('master_peternak')
                                    ->where('nama_peternak', $save_data['nama_peternak'])
                                    ->get()
                                    ->row();
                if ($peternak) {
                    $save_data['master_peternak_id'] = $peternak->master_peternak_id;
                }
            }
            $save_data['id_user'] = $user['id_user'];
            $save_data['created_at'] = date('Y-m-d H:i:s'); 
            $insert_success = $this->M_master_farm->insert_master_farm($save_data);
            if ($insert_success) {
                $new_farm_id = $this->db->insert_id();
            
                if ($new_farm_id && isset($save_data['kapasitas_farm'])) {
                    $this->M_master_farm->create_initial_capacity_history($new_farm_id, $save_data['kapasitas_farm']);
                }
                $this->_add_farm_to_options($save_data, $user['master_sub_area_id'], $user['master_area_id'], $user['id_user']);
            }
            break;
                
            case 'Lokasi Baru':
                log_message('debug', 'Processing Lokasi Baru case');
                
                // Validate required data
                if (empty($save_data['nama_lokasi'])) {
                    log_message('error', 'nama_lokasi is empty in save_data');
                    $this->session->set_flashdata('error', 'Nama lokasi tidak boleh kosong');
                    return false;
                }
                
                // Attempt to insert
                $result = $this->M_master_lokasi_lainnya->insert_master_lokasi_lainnya($save_data);
                
                if ($result) {
                    log_message('debug', 'Successfully inserted lokasi data');
                    $this->_add_to_options($page, $save_data['nama_lokasi'], $user['master_sub_area_id'], null);
                } else {
                    log_message('error', 'Failed to insert lokasi data');
                    $this->session->set_flashdata('error', 'Gagal menyimpan data lokasi');
                    return false;
                }
                break;
                
            case 'Pakan':
                if (isset($save_data['tipe_ternak']) && $save_data['tipe_ternak'] !== 'Layer') {
                    if (isset($save_data['pilihan_pakan'])) {
                        $save_data['pilihan_pakan'] = null;
                    }
                }

                unset($save_data['master_sub_area_id']);
                $this->M_master_pakan->insert_master_pakan($save_data);
                $this->_add_pakan_to_options($save_data);
                break;
                
            case 'Strain':
                unset($save_data['master_sub_area_id']);
                $this->M_master_strain->insert_master_strain($save_data);
                $this->_add_strain_to_options($save_data);
                break;
                
            default:
                log_message('error', 'Unknown category in _save_data: ' . $kategori);
                return false;
        }
        
        return true;
    }

    private function _add_to_options($page, $option_text, $master_sub_area_id, $master_area_id = null) {
        $questions = $this->db->select('questions_id')
                             ->from('questions')
                             ->where('page', $page)
                             ->where('add_to_options', 1)
                             ->get()
                             ->result_array();
                     
        foreach ($questions as $question) {
            $options_data = [
                'questions_id' => $question['questions_id'],
                'option_text' => $option_text
            ];
            
            // Add area IDs based on page type
            if (in_array($page, ['master_agen', 'master_kemitraan']) && !empty($master_area_id)) {
                $options_data['master_area_id'] = $master_area_id;
            } elseif (in_array($page, ['master_farm', 'master_subagen']) && !empty($master_sub_area_id) && !empty($master_area_id)) {
                $options_data['master_sub_area_id'] = $master_sub_area_id;
                $options_data['master_area_id'] = $master_area_id;
            } elseif (!empty($master_sub_area_id)) {
                $options_data['master_sub_area_id'] = $master_sub_area_id;
            }
            // master_peternak tidak menggunakan area ID sama sekali

            $existing = $this->db->where($options_data)->get('options')->num_rows();
            if ($existing == 0) {
                $this->db->insert('options', $options_data);
            }
        }
    }

    private function _add_farm_to_options($save_data, $master_sub_area_id, $master_area_id, $id_user) {
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
                    'master_area_id' => $master_area_id,
                    'id_user' => $id_user 
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
    // Jika tidak ada nama pakan yang akan disimpan, hentikan fungsi.
    if (empty($save_data['nama_pakan'])) {
        return;
    }

    // Tentukan field_name target berdasarkan logika bisnis
    $target_field_name = 'nama_pakan'; // Default untuk Broiler, dll.

    if (isset($save_data['tipe_ternak']) && $save_data['tipe_ternak'] === 'Layer') {
        if (isset($save_data['pilihan_pakan'])) {
            if ($save_data['pilihan_pakan'] === 'CP') {
                $target_field_name = 'layer_pilihan_pakan_cp';
            } elseif ($save_data['pilihan_pakan'] === 'Non CP') {
                $target_field_name = 'layer_pilihan_pakan_lain';
            }
        }
    }

    // Ambil question_id yang spesifik berdasarkan target_field_name yang sudah ditentukan
    $question = $this->db->select('questions_id')
                         ->from('questions')
                         ->where('page', 'master_pakan')
                         ->where('field_name', $target_field_name)
                         ->where('add_to_options', 1) // Pastikan field ini memang untuk ditambahkan ke options
                         ->get()
                         ->row_array();

    // Jika question_id yang sesuai ditemukan, baru lakukan insert
    if ($question) {
        $options_data = [
            'questions_id' => $question['questions_id'], // Gunakan questions_id yang benar
            'option_text'  => $save_data['nama_pakan'],   // Nilai pakan tetap dari 'nama_pakan'
            'tipe_ternak'  => $save_data['tipe_ternak']
        ];

        // Sebaiknya cek duplikasi sebelum insert untuk menghindari data ganda
        $existing = $this->db->where($options_data)->get('options')->num_rows();
        if ($existing == 0) {
            $this->db->insert('options', $options_data);
        }
    }
}

    private function _add_peternak_to_options($page, $option_text) {
        $questions = $this->db->select('questions_id')
                             ->from('questions')
                             ->where('page', $page)
                             ->where('add_to_options', 1)
                             ->get()
                             ->result_array();
                     
        foreach ($questions as $question) {
            $options_data = [
                'questions_id' => $question['questions_id'],
                'option_text' => $option_text
            ];
            // Peternak tidak menggunakan master_area_id maupun master_sub_area_id

            $existing = $this->db->where($options_data)->get('options')->num_rows();
            if ($existing == 0) {
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

    public function index() {
        redirect('Dashboard_new/index');
    }
}
