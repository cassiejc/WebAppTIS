<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tambah_Data_Baru_Master_Controller extends CI_Controller {

    private $current_user; 

    public function __construct() {
        parent::__construct();
        
        // 1. Load Helpers & Libraries
        $this->load->helper(['form', 'url']);
        $this->load->library(['form_validation', 'session']);
        
        // 2. Load Models
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

        // 3. Authentication (Cek Login & Ambil Data User)
        $token = $this->session->userdata('token');
        
        // Jika tidak ada token, tendang ke login
        if (empty($token)) {
            $this->session->set_flashdata('error', 'Silakan login terlebih dahulu.');
            redirect('login'); 
            exit;
        }

        // Ambil data user dan simpan di variabel class
        $this->current_user = $this->dash->getUserInfo($token)->row_array();

        // Validasi jika user tidak ditemukan di database
        if (!$this->current_user) {
            redirect('login/logout');
            exit;
        }
    }

    private function _check_access($allowed_groups) {
        // Ambil group user, ubah ke huruf kecil
        $my_group = isset($this->current_user['group_user']) ? strtolower($this->current_user['group_user']) : '';

        // ATURAN 1: Administrator selalu boleh akses (Bypass)
        if ($my_group === 'administrator') {
            return; 
        }

        // ATURAN 2: Cek apakah group user ada di daftar yang diizinkan
        if (!in_array($my_group, $allowed_groups)) {
            
            // PERUBAHAN DI SINI: Redirect ke Home/Dashboard dengan pesan error
            $this->session->set_flashdata('error', 'Maaf, Anda tidak memiliki akses ke menu tersebut.');
            redirect('Dashboard_new/index');
            exit; // Wajib exit agar script di bawahnya tidak jalan
        }
    }


    // 1. Group: Surveyor, Koordinator, Administrator
    public function sub_agen() {
        $this->_check_access(['koordinator', 'surveyor']);
        $this->_handle_form('Sub Agen', 'master_subagen');
    }
    
    // 2. Group: Administrator Only
    public function agen() {
        $this->_check_access([]); // Array kosong = Hanya Admin
        $this->_handle_form('Agen', 'master_agen');
    }
    
    // 3. Group: Surveyor, Koordinator, Administrator
    public function peternak() {
        $this->_check_access(['koordinator', 'surveyor']);
        $this->_handle_form('Peternak', 'master_peternak');
    }
    
    // 4. Group: Administrator Only
    public function kemitraan() {
        $this->_check_access([]);
        $this->_handle_form('Kemitraan', 'master_kemitraan');
    }
    
    // 5. Group: Surveyor, Koordinator, Administrator
    public function farm() {
        $this->_check_access(['koordinator', 'surveyor']);
        $this->_handle_form('Farm', 'master_farm');
    }
    
    // 6. Group: Administrator Only
    public function lokasi_baru() {
        $this->_check_access([]);
        $this->_handle_form('Lokasi Baru', 'master_lokasi_lainnya');
    }
    
    // 7. Group: Administrator Only
    public function pakan() {
        $this->_check_access([]);
        $this->_handle_form('Pakan', 'master_pakan');
    }

    // 8. Group: Administrator Only
    public function strain() {
        $this->_check_access([]);
        $this->_handle_form('Strain', 'master_strain');
    }

    public function index() {
        redirect('Dashboard_new/index');
    }

    // =========================================================================
    // PRIVATE LOGIC (Form Handling & Data Processing)
    // =========================================================================

    private function _handle_form($kategori, $page) {
        // Gunakan data user yang sudah diambil di __construct
        $user = $this->current_user;
        
        $submit = $this->input->post('submit_form');
        
        $data = [
            'kategori_selected' => $kategori,
            'questions_kategori' => [],
            'title' => "CP APPS"
        ];

        $data['questions_kategori'] = $this->M_Questions->get_questions_by_page($page);

        // Logika menyembunyikan field berdasarkan Role
        if ($kategori == 'Farm' && array_key_exists('group_user', $user) && !is_null($user['group_user'])) {
            $user_group = strtolower($user['group_user']);

            $fields_to_remove = [];

            if ($user_group !== 'administrator') {
                $fields_to_remove[] = 'master_area_id';
            }

            if ($user_group !== 'administrator' && $user_group !== 'koordinator') {
                $fields_to_remove[] = 'master_sub_area_id';
            }

            if (!empty($fields_to_remove)) {
                $filtered_questions = [];
                foreach ($data['questions_kategori'] as $question) {
                    if (!in_array($question['field_name'], $fields_to_remove)) {
                        $filtered_questions[] = $question;
                    }
                }
                $data['questions_kategori'] = $filtered_questions;
            }
        }
                
        $this->_process_options($data['questions_kategori'], $kategori, $user);
        
        if ($submit && !empty($data['questions_kategori'])) {
            $this->_process_form_submission($data['questions_kategori'], $kategori, $user, $page);
        }

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
                        elseif($q['field_name'] == 'master_sub_area_id') {
                            $this->db->select('master_sub_area_id as id, nama_sub_area as option_text');
                            $this->db->from('master_sub_area');
                            $this->db->order_by('nama_sub_area', 'ASC');
                            $q['options'] = $this->db->get()->result_array();
                        }
                        elseif($q['field_name'] == 'master_area_id') {
                            $this->db->select('master_area_id as id, nama_area as option_text');
                            $this->db->from('master_area');
                            $this->db->order_by('nama_area', 'ASC');
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

    private function _process_form_submission($questions_kategori, $kategori, $user, $page) {
        $save_data = [];
        
        if ($kategori == 'Kemitraan') {
            $save_data['master_area_id'] = $user['master_area_id'];
        } elseif ($kategori == 'Farm') {
            $user_group = (array_key_exists('group_user', $user) && !is_null($user['group_user'])) ? strtolower($user['group_user']) : '';

            if ($user_group !== 'administrator') {
                $save_data['master_area_id'] = $user['master_area_id'];
            }

            if ($user_group === 'surveyor' || $user_group === '') { 
                $save_data['master_sub_area_id'] = $user['master_sub_area_id'];
            }
    
        } elseif ($kategori == 'Sub Agen') { 
            $save_data['master_sub_area_id'] = $user['master_sub_area_id'];
            $save_data['master_area_id'] = $user['master_area_id'];
        
        } elseif (!in_array($kategori, ['Peternak', 'Agen'])) {
            $save_data['master_sub_area_id'] = $user['master_sub_area_id'];
        }
        
        if (in_array($kategori, ['Sub Agen', 'Kemitraan'])) {
            $save_data['created_at'] = date('Y-m-d H:i:s');
            $save_data['id_user'] = $user['id_user'];
        }

        $jenis_peternak = $this->input->post('q' . $this->_get_question_id_by_field('jenis_peternak', $questions_kategori));
        $tipe_ternak_pakan = $this->input->post('q' . $this->_get_question_id_by_field('tipe_ternak', $questions_kategori));
        $pilihan_pakan_layer = $this->input->post('q' . $this->_get_question_id_by_field('pilihan_pakan', $questions_kategori));

        foreach ($questions_kategori as $q) {
            $field = $q['field_name'];
            $input_name = 'q' . $q['questions_id'];
            $jawaban = $this->input->post($input_name);

            $should_be_required = false;
            if (!empty($q['required'])) {
                if ($kategori === 'Pakan') {
                    if ($field === 'nama_pakan' && $tipe_ternak_pakan !== 'Layer') {
                        $should_be_required = true;
                    } elseif ($field === 'pilihan_pakan' && $tipe_ternak_pakan === 'Layer') {
                        $should_be_required = true;
                    } elseif ($field === 'layer_pilihan_pakan_cp' && $tipe_ternak_pakan === 'Layer' && $pilihan_pakan_layer === 'CP') {
                        $should_be_required = true;
                    } elseif ($field === 'layer_pilihan_pakan_lain' && $tipe_ternak_pakan === 'Layer' && $pilihan_pakan_layer === 'Non CP') {
                        $should_be_required = true;
                    } elseif (!in_array($field, ['nama_pakan', 'pilihan_pakan', 'layer_pilihan_pakan_cp', 'layer_pilihan_pakan_lain'])) {
                        $should_be_required = true;
                    }
                } elseif ($kategori === 'Peternak') {
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

        if ($kategori === 'Pakan') {
            if (isset($save_data['tipe_ternak']) && $save_data['tipe_ternak'] === 'Layer') {
                if (isset($save_data['pilihan_pakan'])) {
                    if ($save_data['pilihan_pakan'] === 'CP' && !empty($save_data['layer_pilihan_pakan_cp'])) {
                        $save_data['nama_pakan'] = $save_data['layer_pilihan_pakan_cp'];
                    } elseif ($save_data['pilihan_pakan'] === 'Non CP' && !empty($save_data['layer_pilihan_pakan_lain'])) {
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
        
        if ($this->_check_is_duplicate($kategori, $save_data)) {
            $this->session->set_flashdata('error', 'Data ini sudah pernah didaftarkan. Tolong lakukan pengecekan ulang.');
            
            redirect(current_url());
            
            // Stop proses
            return; 
        }

        $result = $this->_save_data($kategori, $save_data, $user, $page);
        
        if ($result) {
            $this->session->set_flashdata('success', 'Data berhasil disimpan!');
        } else {
            if (!$this->session->flashdata('error')) {
                 $this->session->set_flashdata('error', 'Terjadi kesalahan saat menyimpan data.');
            }
        }
        
        redirect('Dashboard_new/index');
    }

    private function _get_question_id_by_field($field_name, $questions) {
        foreach ($questions as $q) {
            if ($q['field_name'] == $field_name) {
                return $q['questions_id'];
            }
        }
        return null;
    }
   
    private function _save_data($kategori, $save_data, $user, $page) {
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
                
                    if ($new_farm_id && isset($save_data['kapasitas_farm']) && isset($save_data['nama_farm'])) {
                        $this->M_master_farm->create_initial_capacity_history(
                            $new_farm_id, 
                            $save_data['kapasitas_farm'],
                            $save_data['nama_farm']
                        );
                    }
                    
                    $sub_area_id = $save_data['master_sub_area_id'] ?? null;
                    $area_id = $save_data['master_area_id'] ?? null;
                    
                    $this->_add_farm_to_options($save_data, $sub_area_id, $area_id, $user['id_user'], $new_farm_id); 
                }
                break;
                    
            case 'Lokasi Baru':
                log_message('debug', 'Processing Lokasi Baru case');
                
                if (empty($save_data['nama_lokasi'])) {
                    log_message('error', 'nama_lokasi is empty in save_data');
                    $this->session->set_flashdata('error', 'Nama lokasi tidak boleh kosong');
                    return false;
                }
                
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
            
            if (in_array($page, ['master_agen', 'master_kemitraan']) && !empty($master_area_id)) {
                $options_data['master_area_id'] = $master_area_id;
            } elseif (in_array($page, ['master_farm', 'master_subagen']) && !empty($master_sub_area_id) && !empty($master_area_id)) {
                $options_data['master_sub_area_id'] = $master_sub_area_id;
                $options_data['master_area_id'] = $master_area_id;
            } elseif (!empty($master_sub_area_id)) {
                $options_data['master_sub_area_id'] = $master_sub_area_id;
            }

            $existing = $this->db->where($options_data)->get('options')->num_rows();
            if ($existing == 0) {
                $this->db->insert('options', $options_data);
            }
        }
    }

    private function _add_farm_to_options($save_data, $master_sub_area_id, $master_area_id, $id_user, $new_farm_id) {
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
                    'id_user' => $id_user,
                    'master_farm_id' => $new_farm_id
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
        if (empty($save_data['nama_pakan'])) {
            return;
        }

        $target_field_name = 'nama_pakan'; 

        if (isset($save_data['tipe_ternak']) && $save_data['tipe_ternak'] === 'Layer') {
            if (isset($save_data['pilihan_pakan'])) {
                if ($save_data['pilihan_pakan'] === 'CP') {
                    $target_field_name = 'layer_pilihan_pakan_cp';
                } elseif ($save_data['pilihan_pakan'] === 'Non CP') {
                    $target_field_name = 'layer_pilihan_pakan_lain';
                }
            }
        }

        $question = $this->db->select('questions_id')
                             ->from('questions')
                             ->where('page', 'master_pakan')
                             ->where('field_name', $target_field_name)
                             ->where('add_to_options', 1) 
                             ->get()
                             ->row_array();

        if ($question) {
            $options_data = [
                'questions_id' => $question['questions_id'], 
                'option_text'  => $save_data['nama_pakan'],   
                'tipe_ternak'  => $save_data['tipe_ternak']
            ];

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

    // =========================================================================
    // VALIDASI DUPLIKASI DATA
    // =========================================================================
    private function _check_is_duplicate($kategori, $save_data) {
        $table = '';
        $field_check = '';
        $value_check = '';
        $additional_where = [];

        switch ($kategori) {
            case 'Agen':
                $table = 'master_agen';
                $field_check = 'nama_agen';
                $value_check = $save_data['nama_agen'];
                break;

            case 'Sub Agen':
                $table = 'master_subagen';
                $field_check = 'nama_subagen';
                $value_check = $save_data['nama_subagen'];
                // Opsional: Jika nama sub agen boleh sama asalkan beda sub_area, uncomment bawah ini:
                $additional_where = ['master_sub_area_id' => $save_data['master_sub_area_id']];
                break;

            case 'Peternak':
                $table = 'master_peternak';
                $field_check = 'nama_peternak';
                $value_check = $save_data['nama_peternak'];
                break;

            case 'Farm':
                $table = 'master_farm';
                $field_check = 'nama_farm';
                $value_check = $save_data['nama_farm'];
                // Opsional: Cek duplikasi per Sub Area (jadi nama farm sama di area beda boleh)
                $additional_where = ['master_sub_area_id' => $save_data['master_sub_area_id']];
                // if (isset($save_data['tipe_ternak'])) {
                //     $additional_where['tipe_ternak'] = $save_data['tipe_ternak'];
                // }
                break;

            case 'Kemitraan':
                $table = 'master_kemitraan';
                $field_check = 'nama_kantor_kemitraan';
                $value_check = $save_data['nama_kantor_kemitraan'];
                break;

            case 'Lokasi Baru':
                $table = 'master_lokasi_lainnya';
                $field_check = 'nama_lokasi';
                $value_check = $save_data['nama_lokasi'];
                break;
            
            case 'Pakan':
                $table = 'master_pakan';
                $field_check = 'nama_pakan';
                $value_check = $save_data['nama_pakan'];
                
                // Filter tambahan berdasarkan tipe_ternak bisa sama
                if (isset($save_data['tipe_ternak'])) {
                    $additional_where['tipe_ternak'] = $save_data['tipe_ternak'];
                }
                break;

            case 'Strain':
                $table = 'master_strain';
                $field_check = 'nama_strain';
                $value_check = $save_data['nama_strain'];
                break;

            default:
                return false; // Kategori tidak dikenali, anggap tidak duplikat
        }

        if (!empty($table) && !empty($field_check) && !empty($value_check)) {
            $this->db->where($field_check, $value_check);
            
            // Tambahan where jika ada (misal cek per area)
            if (!empty($additional_where)) {
                $this->db->where($additional_where);
            }

            $query = $this->db->get($table);

            if ($query->num_rows() > 0) {
                return true; // DATA SUDAH ADA (DUPLIKAT)
            }
        }

        return false; // DATA AMAN (BELUM ADA)
    }
}
