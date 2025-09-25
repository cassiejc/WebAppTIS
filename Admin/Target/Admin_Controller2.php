<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_Controller extends CI_Controller {
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
        $this->load->model('M_target'); 
        $this->load->model('M_edit_user');
    }

    // Method untuk Sub Agen
    public function subagen($id = null) {
        if (!$id) {
            redirect('Admin_Controller/list_data/subagen');
            return;
        }
        $this->_handle_update_form('Sub Agen', 'master_subagen', $id);
    }
    
    // Method untuk Agen
    public function agen($id = null) {
        if (!$id) {
            redirect('Admin_Controller/list_data/agen');
            return;
        }
        $this->_handle_update_form('Agen', 'master_agen', $id);
    }
    
    // Method untuk Peternak
    public function peternak($id = null) {
        if (!$id) {
            redirect('Admin_Controller/list_data/peternak');
            return;
        }
        $this->_handle_update_form('Peternak', 'master_peternak', $id);
    }
    
    // Method untuk Kemitraan
    public function kemitraan($id = null) {
        if (!$id) {
            redirect('Admin_Controller/list_data/kemitraan');
            return;
        }
        $this->_handle_update_form('Kemitraan', 'master_kemitraan', $id);
    }
    
    // Method untuk Farm
    // public function farm($id = null) {
    //     if (!$id) {
    //         redirect('Admin_Controller/list_data/farm');
    //         return;
    //     }
    //     $this->_handle_update_form('Farm', 'master_farm', $id);
    // }

    // Di dalam file Admin_Controller.php

    // ... (kode controller Anda yang lain) ...

    // Method untuk Farm
    public function farm($id = null) {
        if (!$id) {
            redirect('Admin_Controller/list_data/farm');
            return;
        }
        // Arahkan ke method edit_farm yang baru untuk menampilkan form
        $this->edit_farm($id);
    }

    // FUNGSI BARU untuk menampilkan form edit farm (Sudah Anda buat, ini hanya untuk konfirmasi)
    public function edit_farm($id)
    {
        $data['page_title'] = 'Edit Farm';
        
        $data['farm_data'] = $this->M_master_farm->get_farm_by_id($id);
        $data['all_peternak'] = $this->M_master_farm->get_all_peternak();

        // [PENYESUAIAN] Panggil fungsi dengan menyertakan nama page
        $data['tipe_ternak_options'] = $this->M_master_farm->get_options_by_field_name('tipe_ternak', 'master_farm');

        if (empty($data['farm_data'])) {
            show_404();
        }

        $this->load->view('templates/dash_h', $data);
        $this->load->view('farm_edit_view', $data);
        $this->load->view('templates/dash_f', $data);
    }

    public function update_farm()
    {
        // Ambil data dari form
        $master_farm_id = $this->input->post('master_farm_id');
        $new_capacity = $this->input->post('kapasitas_farm');
        $start_date_str = $this->input->post('start_date');

        // ... (Validasi input bisa ditambahkan di sini) ...

        $currentFarm = $this->M_master_farm->get_farm_by_id($master_farm_id);
        $capacityHasChanged = ($currentFarm['kapasitas_farm'] != $new_capacity);
        
        $this->db->trans_start();

        // Logika SCD Type 2 untuk kapasitas tetap sama dan tidak diubah
        if ($capacityHasChanged) {
            $end_date_str = date('Y-m-d', strtotime('-1 day', strtotime($start_date_str)));
            $this->M_master_farm->close_current_capacity_history($master_farm_id, $end_date_str);

            $new_history_data = [
                'master_farm_id' => $master_farm_id,
                'kapasitas'      => $new_capacity,
                'start_date'     => $start_date_str,
                'end_date'       => '9999-12-31'
            ];
            $this->M_master_farm->add_new_capacity_history($new_history_data);
        }
        
        // [PENYESUAIAN] Update data lain di tabel utama (master_farm)
        // Field-field baru ditambahkan di sini
        $data_farm = [
            'nama_farm'          => $this->input->post('nama_farm'),
            'kapasitas_farm'     => $new_capacity,
            'master_peternak_id' => $this->input->post('master_peternak_id'), // ID peternak baru
            'tipe_ternak'        => $this->input->post('tipe_ternak'),         // Tipe ternak baru
            'alamat_farm'        => $this->input->post('alamat_farm')          // Alamat baru
        ];
        $this->M_master_farm->update_farm_data($master_farm_id, $data_farm);
        
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Gagal memperbarui data farm.');
        } else {
            $this->session->set_flashdata('success', 'Data farm berhasil diperbarui!');
        }

        redirect('Admin_Controller/list_data/farm');
    }
    
    // Method untuk Lokasi Baru
    public function lokasi_baru($id = null) {
        if (!$id) {
            redirect('Admin_Controller/list_data/lokasi_baru');
            return;
        }
        $this->_handle_update_form('Lokasi Baru', 'master_lokasi_lainnya', $id);
    }
    
    // Method untuk Pakan
    public function pakan($id = null) {
        if (!$id) {
            redirect('Admin_Controller/list_data/pakan');
            return;
        }
        $this->_handle_update_form('Pakan', 'master_pakan', $id);
    }

    // Method untuk Strain
    public function strain($id = null) {
        if (!$id) {
            redirect('Admin_Controller/list_data/strain');
            return;
        }
        $this->_handle_update_form('Strain', 'master_strain', $id);
    }

    // Gantikan fungsi target() yang lama dengan ini
    public function target($id = null) {
        if (!$id) {
            redirect('Admin_Controller/list_data/target');
            return;
        }
        $this->edit_target($id); 
    }

    // TAMBAHKAN FUNGSI BARU INI
    // public function edit_target($id_target) {
    //     // Ambil info user (untuk konsistensi template)
    //     $token = $this->session->userdata('token');
    //     $user = $this->dash->getUserInfo($token)->row_array();
        
    //     // Ambil data target yang akan diedit dari model
    //     $target_data = $this->M_target->get_target_by_id($id_target);

    //     // Jika data tidak ditemukan, beri pesan error dan kembali
    //     if (!$target_data) {
    //         $this->session->set_flashdata('error', 'Data target tidak ditemukan');
    //         redirect('Admin_Controller/list_data/target');
    //         return;
    //     }
        
    //     // Siapkan data untuk dikirim ke view
    //     $data = [
    //         'page_title' => 'Edit Target',
    //         'target_item' => $target_data, // Kirim data target ke view
    //         'user' => $user
    //     ];
        
    //     // Muat template dan view khusus untuk edit target
    //     $this->load->view('templates/dash_h', $data);
    //     $this->load->view('edit_target_view', $data); // Memuat view custom Anda
    //     $this->load->view('templates/dash_f', $data);
    // }

    // // TAMBAHKAN FUNGSI INI UNTUK PROSES UPDATE
    // // GANTI FUNGSI UPDATE_TARGET ANDA DENGAN KODE LENGKAP INI
    // public function update_target()
    // {
    //     // Ambil data dari form
    //     $id_target = $this->input->post('id_target');
    //     $new_target_value = $this->input->post('target');
    //     $start_date_str = $this->input->post('start_date'); // Ambil tanggal dari kalender

    //     // Ambil data target saat ini untuk perbandingan
    //     $currentTarget = $this->M_target->get_target_by_id($id_target);
    //     $current_user_id = $currentTarget['id_user']; // Dapatkan id_user

    //     // Cek apakah nilai target berubah
    //     $targetHasChanged = ($currentTarget['target'] != $new_target_value);

    //     // --- Memulai Database Transaction ---
    //     $this->db->trans_start();

    //     // JIKA NILAI TARGET BERUBAH, jalankan logika history
    //     if ($targetHasChanged) {
    //         // 1. "Tutup" riwayat yang lama
    //         // Tanggal akhir adalah sehari sebelum start_date yang baru
    //         $end_date_str = date('Y-m-d', strtotime('-1 day', strtotime($start_date_str)));
    //         $this->M_target->close_current_target_history($current_user_id, $end_date_str);

    //         // 2. "Buka" riwayat yang baru
    //         $new_history_data = [
    //             'id_target'  => $id_target,
    //             'id_user'    => $current_user_id,
    //             'target'     => $new_target_value,
    //             'start_date' => $start_date_str, // Gunakan tanggal dari form
    //             'end_date'   => '9999-12-31'   // Penanda record aktif
    //         ];
    //         $this->M_target->add_new_target_history($new_history_data);
    //     }

    //     // 3. Tetap update data di tabel utama (master_target)
    //     // Ini agar mudah mengambil nilai target yang aktif saat ini
    //     $data_target = ['target' => $new_target_value];
    //     $this->M_target->update_target($id_target, $data_target);

    //     // --- Menyelesaikan Database Transaction ---
    //     $this->db->trans_complete();

    //     if ($this->db->trans_status() === FALSE) {
    //         $this->session->set_flashdata('error', 'Gagal memperbarui target.');
    //     } else {
    //         $this->session->set_flashdata('success', 'Target berhasil diperbarui dan riwayat telah disimpan!');
    //     }

    //     redirect('Admin_Controller/list_data/target');
    // }


    public function edit_target($id_target)
    {
        // Ambil data target yang akan diedit
        $existing_data = $this->M_target->get_target_by_id($id_target);

        if (!$existing_data) {
            $this->session->set_flashdata('error', 'Data target tidak ditemukan');
            redirect('Admin_Controller/list_data/target');
            return;
        }
        
        // Siapkan data untuk dikirim ke view generik
        $data = [
            'page_title' => 'Edit Target',
            'kategori_selected' => 'Target', // Untuk konsistensi dengan view
            'edit_id' => $id_target,
            'existing_data' => $existing_data,
            // Ambil pertanyaan dari database, bukan hardcode
            'questions_kategori' => $this->M_Questions->get_questions_by_page('master_target_edit'),
            // Tentukan action form secara eksplisit
            'form_action' => base_url('Admin_Controller/update_target')
        ];

        // Atur nilai default untuk tanggal jika belum ada
        if (!isset($data['existing_data']['start_date'])) {
            $data['existing_data']['start_date'] = date('Y-m-d');
        }
        
        // Muat template dan view generik yang sama dengan form lain
        $this->load->view('templates/dash_h', $data);
        // Anda sekarang menggunakan view generik, bukan 'edit_target_view'
        $this->load->view('form_admin_update_view', $data); 
        $this->load->view('templates/dash_f', $data);
    }

    // Gantikan fungsi update_target() yang lama dengan versi yang lebih cerdas ini
    public function update_target()
    {
        // Ambil ID target dari hidden input
        $id_target = $this->input->post('edit_id');

        // Proses data form secara dinamis
        $questions = $this->M_Questions->get_questions_by_page('master_target_edit');
        $update_data = [];
        foreach ($questions as $q) {
            $field = $q['field_name'];
            $input_name = 'q' . $q['questions_id'];
            $jawaban = $this->input->post($input_name);
            $update_data[$field] = $jawaban;
        }

        // Ambil nilai spesifik dari data yang sudah diproses
        $new_target_value = $update_data['target'];
        $start_date_str = $update_data['start_date'];

        // Ambil data target saat ini untuk perbandingan
        $currentTarget = $this->M_target->get_target_by_id($id_target);
        $current_user_id = $currentTarget['id_user'];

        // Cek apakah nilai target berubah
        $targetHasChanged = ($currentTarget['target'] != $new_target_value);

        $this->db->trans_start();

        if ($targetHasChanged) {
            $end_date_str = date('Y-m-d', strtotime('-1 day', strtotime($start_date_str)));
            $this->M_target->close_current_target_history($current_user_id, $end_date_str);

            $new_history_data = [
                'id_target'  => $id_target,
                'id_user'    => $current_user_id,
                'target'     => $new_target_value,
                'start_date' => $start_date_str,
                'end_date'   => '9999-12-31'
            ];
            $this->M_target->add_new_target_history($new_history_data);
        }

        $data_target = ['target' => $new_target_value];
        $this->M_target->update_target($id_target, $data_target);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Gagal memperbarui target.');
        } else {
            $this->session->set_flashdata('success', 'Target berhasil diperbarui dan riwayat telah disimpan!');
        }

        redirect('Admin_Controller/list_data/target');
    }

    // ... (letakkan dua fungsi baru ini di dalam class Admin_Controller) ...

    /**
     * Menampilkan halaman form untuk menambah target baru.
     */
    public function add_target()
    {
        // Ambil data user yang belum punya target dari model
        $data['users_without_target'] = $this->M_target->get_users_without_target();
        $data['page_title'] = 'Tambah Target Baru';

        // Muat view form yang baru
        $this->load->view('templates/dash_h', $data);
        $this->load->view('add_target_view', $data); 
        $this->load->view('templates/dash_f', $data);
    }

    /**
     * Memproses data dari form tambah target.
     */
    public function create_target_action()
    {
        // Atur aturan validasi
        $this->form_validation->set_rules('id_user', 'User', 'required|is_unique[master_target.id_user]');
        $this->form_validation->set_rules('target', 'Nilai Target', 'required|numeric');
        $this->form_validation->set_rules('start_date', 'Tanggal Mulai', 'required');

        if ($this->form_validation->run() == FALSE) {
            // Jika validasi gagal, kembali ke form dengan pesan error
            $this->session->set_flashdata('error', validation_errors());
            redirect('Admin_Controller/add_target');
        } else {
            $data_target = [
                'id_user' => $this->input->post('id_user'),
                'target'  => $this->input->post('target')
            ];

            $this->db->trans_start();
            
            $new_target_id = $this->M_target->create_target($data_target);

            if (method_exists($this->M_target, 'add_new_target_history')) {
                $history_data = [
                    'id_target'  => $new_target_id,
                    'id_user'    => $this->input->post('id_user'),
                    'target'     => $this->input->post('target'),
                    'start_date' => $this->input->post('start_date'),
                    'end_date'   => '9999-12-31' // Penanda record ini aktif
                ];
                $this->M_target->add_new_target_history($history_data);
            }
            
            // Selesaikan Transaction
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->session->set_flashdata('error', 'Gagal menyimpan data target ke database.');
            } else {
                $this->session->set_flashdata('success', 'Target baru berhasil ditambahkan!');
            }
            
            redirect('Admin_Controller/list_data/target');
        }
    }

    public function user($id = null) {
        if (!$id) {
            redirect('Admin_Controller/list_data/user');
            return;
        }else {
            $this->edit_user($id);
        }
    }

    // Menampilkan form untuk mengedit satu user
    public function edit_user($id_user)
    {
        $data['page_title'] = 'Edit User';
        $data['user'] = $this->M_edit_user->get_user_by_id($id_user);
        
        $data['areas'] = $this->M_edit_user->get_all_areas();
        $data['sub_areas'] = $this->M_edit_user->get_all_sub_areas();

        if (empty($data['user'])) {
            show_404();
        }

        $this->load->view('templates/dash_h', $data);
        $this->load->view('user_edit_view', $data); 
        $this->load->view('templates/dash_f', $data);
    }

    public function update_user()
    {
        $id_user = $this->input->post('id_user');
        $new_area_id = $this->input->post('master_area_id');
        $new_sub_area_id = $this->input->post('master_sub_area_id');
        $start_date_str = $this->input->post('start_date');

        $currentUser = $this->M_edit_user->get_user_by_id($id_user);

        $areaHasChanged = ($currentUser['master_area_id'] != $new_area_id || $currentUser['master_sub_area_id'] != $new_sub_area_id);
        
        $this->db->trans_start();

        if ($areaHasChanged) {
            $end_date_str = date('Y-m-d', strtotime('-1 day', strtotime($start_date_str)));
            $this->M_edit_user->close_current_area_history($id_user, $end_date_str);
            
            $new_history_data = [
                'id_user' => $id_user,
                'master_area_id' => $new_area_id,
                'master_sub_area_id' => $new_sub_area_id,
                'start_date' => $start_date_str, // Hari ini
                'end_date' => '9999-12-31' // Penanda record aktif
            ];
            $this->M_edit_user->add_new_area_history($new_history_data);
        }
        
        $data_user = [
            'master_area_id' => $new_area_id,
            'master_sub_area_id' => $new_sub_area_id,
            'caption' => $this->input->post('caption'),
            'is_active' => $this->input->post('is_active')
        ];
        $this->M_edit_user->update_user($id_user, $data_user);
        
        // --- Menyelesaikan Database Transaction ---
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Gagal memperbarui data user.');
        } else {
            $this->session->set_flashdata('success', 'Data user berhasil diperbarui dan riwayat area telah disimpan!');
        }

        // redirect('admin_controller/list_users');
        redirect('dashboard_new/index');
    }

    // Method umum untuk handle update form
    private function _handle_update_form($kategori, $page, $id) {
        $token = $this->session->userdata('token');
        $user = $this->dash->getUserInfo($token)->row_array();
        $submit = $this->input->post('submit_form');
        
        // Jika tidak ada ID, redirect ke list
        if (!$id) {
            $this->session->set_flashdata('error', 'ID data tidak ditemukan');
            redirect('Dashboard_new/index');
            return;
        }

        // Get existing data
        $existing_data = $this->_get_existing_data($kategori, $id, $user);
        if (!$existing_data) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan');
            redirect('Dashboard_new/index');
            return;
        }
        
        // Initialize data array
        $data = [
            'kategori_selected' => $kategori,
            'questions_kategori' => [],
            'page_title' => 'Edit ' . $kategori,
            'existing_data' => $existing_data,
            'edit_id' => $id
        ];

        // Get specific questions based on category
        $data['questions_kategori'] = $this->M_Questions->get_questions_by_page($page);
        
        // Process options for each question based on category
        $this->_process_options($data['questions_kategori'], $kategori, $user);
        
        // Process form submission
        if ($submit && !empty($data['questions_kategori'])) {
            $this->_process_update_submission($data['questions_kategori'], $kategori, $user, $page, $id, $existing_data);
        }

        // Load views
        $this->load->view('templates/dash_h', $data);
        $this->load->view('form_admin_update_view', $data);
        $this->load->view('templates/dash_f', $data);
    }

    private function _get_existing_data($kategori, $id, $user) {
        switch($kategori) {
            case 'Agen':
                return $this->db->select('*')
                                ->from('master_agen')
                                ->where('master_agen_id', $id)
                                ->where('master_sub_area_id', $user['master_sub_area_id'])
                                ->get()
                                ->row_array();
                
            case 'Kemitraan':
                return $this->db->select('*')
                                ->from('master_kemitraan')
                                ->where('master_kemitraan_id', $id)
                                ->where('master_sub_area_id', $user['master_sub_area_id'])
                                ->get()
                                ->row_array();
                
            case 'Sub Agen':
                return $this->db->select('*')
                                ->from('master_subagen')
                                ->where('master_subagen_id', $id)
                                ->where('master_sub_area_id', $user['master_sub_area_id'])
                                ->get()
                                ->row_array();
                
            case 'Peternak':
                return $this->db->select('*')
                                ->from('master_peternak')
                                ->where('master_peternak_id', $id)
                                ->where('master_sub_area_id', $user['master_sub_area_id'])
                                ->get()
                                ->row_array();
                
            case 'Farm':
                return $this->db->select('*')
                                ->from('master_farm')
                                ->where('master_farm_id', $id)
                                ->where('master_sub_area_id', $user['master_sub_area_id'])
                                ->get()
                                ->row_array();
                
            case 'Lokasi Baru':
                return $this->db->select('*')
                                ->from('master_lokasi_lainnya')
                                ->where('master_lokasi_lainnya_id', $id)
                                ->where('master_sub_area_id', $user['master_sub_area_id'])
                                ->get()
                                ->row_array();
                
            case 'Pakan':
                return $this->db->select('*')
                                ->from('master_pakan')
                                ->where('master_pakan_id', $id)
                                ->get()
                                ->row_array();
                
            case 'Strain':
                return $this->db->select('*')
                                ->from('master_strain')
                                ->where('master_strain_id', $id)
                                ->get()
                                ->row_array();
                
            default:
                return null;
        }
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

    private function _process_update_submission($questions_kategori, $kategori, $user, $page, $id, $existing_data) {
        $update_data = [];
        
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
            
            $update_data[$field] = $jawaban;
        }
        
        // Update data based on category
        $this->_update_data($kategori, $update_data, $user, $page, $id, $existing_data);
        
        $this->session->set_flashdata('success', 'Data berhasil diupdate!');
        redirect('Dashboard_new/index');
    }

    private function _update_data($kategori, $update_data, $user, $page, $id, $existing_data) {
        switch($kategori) {
            case 'Agen':
                $this->db->where('master_agen_id', $id)
                         ->where('master_sub_area_id', $user['master_sub_area_id'])
                         ->update('master_agen', $update_data);
                         
                // Update options if name changed
                if ($existing_data['nama_agen'] != $update_data['nama_agen']) {
                    $this->_update_options($page, $existing_data['nama_agen'], $update_data['nama_agen'], $user['master_sub_area_id']);
                }
                break;
                
            case 'Kemitraan':
                $this->db->where('master_kemitraan_id', $id)
                         ->where('master_sub_area_id', $user['master_sub_area_id'])
                         ->update('master_kemitraan', $update_data);
                         
                // Update options if name changed
                if ($existing_data['nama_kantor_kemitraan'] != $update_data['nama_kantor_kemitraan']) {
                    $this->_update_options($page, $existing_data['nama_kantor_kemitraan'], $update_data['nama_kantor_kemitraan'], $user['master_sub_area_id']);
                }
                break;
                
            case 'Sub Agen':
                $this->db->where('master_subagen_id', $id)
                         ->where('master_sub_area_id', $user['master_sub_area_id'])
                         ->update('master_subagen', $update_data);
                         
                // Update options if name changed
                if ($existing_data['nama_subagen'] != $update_data['nama_subagen']) {
                    $this->_update_options($page, $existing_data['nama_subagen'], $update_data['nama_subagen'], $user['master_sub_area_id']);
                }
                break;
                
            case 'Peternak':
                // Process jenis_peternak combination
                if (!empty($update_data['jenis_peternak'])) {
                    $jenis = $update_data['jenis_peternak'];
                    $nama_dari = '';
                    
                    if ($jenis === 'Agen' && !empty($update_data['agen_dari'])) {
                        $nama_dari = $update_data['agen_dari'];
                    } elseif ($jenis === 'Sub Agen' && !empty($update_data['sub_agen_dari'])) {
                        $nama_dari = $update_data['sub_agen_dari'];
                    } elseif ($jenis === 'Kemitraan' && !empty($update_data['kemitraan_dari'])) {
                        $nama_dari = $update_data['kemitraan_dari'];
                    }
                    
                    $update_data['jenis_peternak'] = !empty($nama_dari) ? "$jenis: $nama_dari" : $jenis;
                }
                
                unset($update_data['agen_dari']);
                unset($update_data['sub_agen_dari']); 
                unset($update_data['kemitraan_dari']);
                
                $this->db->where('master_peternak_id', $id)
                         ->where('master_sub_area_id', $user['master_sub_area_id'])
                         ->update('master_peternak', $update_data);
                         
                // Update options if name changed
                if ($existing_data['nama_peternak'] != $update_data['nama_peternak']) {
                    $this->_update_options($page, $existing_data['nama_peternak'], $update_data['nama_peternak'], $user['master_sub_area_id']);
                }
                break;
                
            case 'Farm':
                // Get master_peternak_id
                if (!empty($update_data['nama_peternak'])) {
                    $peternak = $this->db->select('master_peternak_id')
                                        ->from('master_peternak')
                                        ->where('nama_peternak', $update_data['nama_peternak'])
                                        // ->where('master_sub_area_id', $user['master_sub_area_id'])
                                        ->get()
                                        ->row();
                    
                    if ($peternak) {
                        $update_data['master_peternak_id'] = $peternak->master_peternak_id;
                    }
                }
                
                $this->db->where('master_farm_id', $id)
                         ->where('master_sub_area_id', $user['master_sub_area_id'])
                         ->update('master_farm', $update_data);
                         
                // Update farm options
                if ($existing_data['nama_farm'] != $update_data['nama_farm']) {
                    $this->_update_farm_options($existing_data, $update_data, $user['master_sub_area_id'], $user['id_user']);
                }
                break;
                
            case 'Lokasi Baru':
                $this->db->where('master_lokasi_lainnya_id', $id)
                         ->where('master_sub_area_id', $user['master_sub_area_id'])
                         ->update('master_lokasi_lainnya', $update_data);
                         
                // Update options if name changed
                if ($existing_data['nama_lokasi'] != $update_data['nama_lokasi']) {
                    $this->_update_options($page, $existing_data['nama_lokasi'], $update_data['nama_lokasi'], $user['master_sub_area_id']);
                }
                break;
                
            case 'Pakan':
                $this->db->where('master_pakan_id', $id)
                         ->update('master_pakan', $update_data);
                         
                // Update pakan options
                if ($existing_data['nama_pakan'] != $update_data['nama_pakan']) {
                    $this->_update_pakan_options($existing_data, $update_data);
                }
                break;
                
            case 'Strain':
                $this->db->where('master_strain_id', $id)
                         ->update('master_strain', $update_data);
                         
                // Update strain options
                if ($existing_data['nama_strain'] != $update_data['nama_strain']) {
                    $this->_update_strain_options($existing_data, $update_data);
                }
                break;

            default:
                log_message('error', 'Unknown category in _update_data: ' . $kategori);
                return false;
        }
        
        return true;
    }

    private function _update_options($page, $old_text, $new_text, $master_sub_area_id) {
        $questions = $this->db->select('questions_id')
                             ->from('questions')
                             ->where('page', $page)
                             ->where('add_to_options', 1)
                             ->get()
                             ->result_array();
                     
        foreach ($questions as $question) {
            $this->db->where([
                'questions_id' => $question['questions_id'],
                'option_text' => $old_text,
                'master_sub_area_id' => $master_sub_area_id
            ])
            ->update('options', ['option_text' => $new_text]);
        }
    }

    private function _update_farm_options($existing_data, $update_data, $master_sub_area_id, $id_user) {
        $questions = $this->db->select('questions_id, field_name')
                             ->from('questions')
                             ->where('page', 'master_farm')
                             ->where('add_to_options', 1)
                             ->get()
                             ->result_array();
                     
        foreach ($questions as $question) {
            if ($question['field_name'] === 'nama_farm') {
                $this->db->where([
                    'questions_id' => $question['questions_id'],
                    'option_text' => $existing_data['nama_farm'],
                    'master_sub_area_id' => $master_sub_area_id
                ])
                ->update('options', [
                    'option_text' => $update_data['nama_farm'],
                    'nama_peternak' => $update_data['nama_peternak'],
                    'tipe_ternak' => $update_data['tipe_ternak']
                ]);
            }
        }
    }

    private function _update_pakan_options($existing_data, $update_data) {
        $questions = $this->db->select('questions_id, field_name')
                             ->from('questions')
                             ->where('page', 'master_pakan')
                             ->where('add_to_options', 1)
                             ->get()
                             ->result_array();
                            
        foreach ($questions as $question) {
            if ($question['field_name'] === 'nama_pakan' && !empty($update_data['nama_pakan'])) {
                $this->db->where([
                    'questions_id' => $question['questions_id'],
                    'option_text' => $existing_data['nama_pakan']
                ])
                ->update('options', [
                    'option_text' => $update_data['nama_pakan'],
                    'tipe_ternak' => $update_data['tipe_ternak']
                ]);
            }
        }
    }

    private function _update_strain_options($existing_data, $update_data) {
        $questions = $this->db->select('questions_id, field_name')
                             ->from('questions')
                             ->where('page', 'master_strain')
                             ->where('add_to_options', 1)
                             ->get()
                             ->result_array();
                        
        foreach ($questions as $question) {
            if ($question['field_name'] === 'nama_strain' && !empty($update_data['nama_strain'])) {
                $this->db->where([
                    'questions_id' => $question['questions_id'],
                    'option_text' => $existing_data['nama_strain']
                ])
                ->update('options', [
                    'option_text' => $update_data['nama_strain'],
                    'tipe_ternak' => $update_data['tipe_ternak']
                ]);
            }
        }
    }

    // Method untuk menampilkan list data berdasarkan kategori
    public function list_data($kategori = null) {
        if (!$kategori) {
            redirect('Dashboard_new/index');
            return;
        }

        $token = $this->session->userdata('token');
        $user = $this->dash->getUserInfo($token)->row_array();
        
        // Convert URL parameter to readable category name
        $kategori_map = [
            'agen' => 'Agen',
            'kemitraan' => 'Kemitraan', 
            'subagen' => 'Sub Agen',
            'peternak' => 'Peternak',
            'farm' => 'Farm',
            'lokasi_baru' => 'Lokasi Baru',
            'pakan' => 'Pakan',
            'strain' => 'Strain',
            'target' => 'Target',
            'user' => 'User'
        ];

        if (!isset($kategori_map[$kategori])) {
            $this->session->set_flashdata('error', 'Kategori tidak ditemukan');
            redirect('Dashboard_new/index');
            return;
        }

        $kategori_name = $kategori_map[$kategori];
        
        $data_list = $this->_get_data_list($kategori_name, $user);
        
        $data = [
            'page_title' => 'Daftar ' . $kategori_name,
            'kategori_selected' => $kategori_name,
            'data_list' => $data_list['data'],
            'table_headers' => $data_list['headers'],
            'table_fields' => $data_list['fields'],
            'primary_key' => $data_list['primary_key'],
            'display_field' => $data_list['display_field']
        ];

        $this->load->view('templates/dash_h', $data);
        $this->load->view('data_list_view', $data);
        $this->load->view('templates/dash_f', $data);
    }

    private function _get_data_list($kategori, $user) {
        switch($kategori) {
            case 'Agen':
                $query = $this->db->select('*')
                                 ->from('master_agen')
                                 ->where('master_sub_area_id', $user['master_sub_area_id'])
                                 ->get();
                return [
                    'data' => $query->result_array(),
                    'headers' => ['Nama Agen', 'Alamat', 'Nomor Telepon'],
                    'fields' => ['nama_agen', 'alamat_agen', 'nomor_telepon_agen'],
                    'primary_key' => 'master_agen_id',
                    'display_field' => 'nama_agen'
                ];
                
            case 'Kemitraan':
                $query = $this->db->select('*')
                                 ->from('master_kemitraan')
                                 ->where('master_sub_area_id', $user['master_sub_area_id'])
                                 ->get();
                return [
                    'data' => $query->result_array(),
                    'headers' => ['Nama Kantor Kemitraan', 'Alamat', 'Nomor Telepon'],
                    'fields' => ['nama_kantor_kemitraan', 'alamat_kantor_kemitraan', 'nomor_telepon_kemitraan'],
                    'primary_key' => 'master_kemitraan_id',
                    'display_field' => 'nama_kantor_kemitraan'
                ];
                
            case 'Sub Agen':
                $query = $this->db->select('*')
                                 ->from('master_subagen')
                                 ->where('master_sub_area_id', $user['master_sub_area_id'])
                                 ->get();
                return [
                    'data' => $query->result_array(),
                    'headers' => ['Nama Sub Agen', 'Alamat'],
                    'fields' => ['nama_subagen', 'alamat_subagen'],
                    'primary_key' => 'master_subagen_id',
                    'display_field' => 'nama_subagen'
                ];
                
            case 'Peternak':
                $query = $this->db->select('*')
                                 ->from('master_peternak')
                                 ->where('master_sub_area_id', $user['master_sub_area_id'])
                                 ->get();
                return [
                    'data' => $query->result_array(),
                    'headers' => ['Nama Peternak', 'Jenis Peternak', 'Alamat'],
                    'fields' => ['nama_peternak', 'jenis_peternak', 'alamat_peternak'],
                    'primary_key' => 'master_peternak_id',
                    'display_field' => 'nama_peternak'
                ];
                
            case 'Farm':
                $query = $this->db->select('mf.*, mp.nama_peternak')
                                 ->from('master_farm mf')
                                 ->join('master_peternak mp', 'mf.master_peternak_id = mp.master_peternak_id', 'left')
                                 ->where('mf.master_sub_area_id', $user['master_sub_area_id'])
                                 ->get();
                return [
                    'data' => $query->result_array(),
                    'headers' => ['Nama Farm', 'Nama Peternak', 'Tipe Ternak', 'Alamat', 'Kapasitas'],
                    'fields' => ['nama_farm', 'nama_peternak', 'tipe_ternak', 'alamat_farm', 'kapasitas_farm'],
                    'primary_key' => 'master_farm_id',
                    'display_field' => 'nama_farm'
                ];
                
            case 'Lokasi Baru':
                $query = $this->db->select('*')
                                 ->from('master_lokasi_lainnya')
                                 ->where('master_sub_area_id', $user['master_sub_area_id'])
                                 ->get();
                return [
                    'data' => $query->result_array(),
                    'headers' => ['Nama Lokasi', 'Alamat'],
                    'fields' => ['nama_lokasi', 'alamat_lokasi'],
                    'primary_key' => 'master_lokasi_lainnya_id',
                    'display_field' => 'nama_lokasi'
                ];
                
            case 'Pakan':
                $query = $this->db->select('*')
                                 ->from('master_pakan')
                                 ->get();
                return [
                    'data' => $query->result_array(),
                    'headers' => ['Nama Pakan', 'Tipe Ternak'],
                    'fields' => ['nama_pakan', 'tipe_ternak'],
                    'primary_key' => 'master_pakan_id',
                    'display_field' => 'nama_pakan'
                ];
                
            case 'Strain':
                $query = $this->db->select('*')
                                 ->from('master_strain')
                                 ->get();
                return [
                    'data' => $query->result_array(),
                    'headers' => ['Nama Strain', 'Tipe Ternak'],
                    'fields' => ['nama_strain', 'tipe_ternak'],
                    'primary_key' => 'master_strain_id',
                    'display_field' => 'nama_strain'
                ];

            case 'Target':
                return [
                    'data' => $this->M_target->get_all_target(),
                    'headers' => ['Username', 'Target'],
                    'fields' => ['username', 'target'],
                    'primary_key' => 'id_target',
                    'display_field' => 'username'
                ];

            case 'User':
                return [
                    'data' => $this->M_edit_user->get_all_users(),
                    'headers' => ['Username', 'Nama Lengkap', 'Area', 'Sub Area', 'Status'],
                    'fields' => ['username', 'caption', 'nama_area', 'nama_sub_area', 'is_active'],
                    'primary_key' => 'id_user',
                    'display_field' => 'username'
                ];
                
            default:
                return [
                    'data' => [],
                    'headers' => [],
                    'fields' => [],
                    'primary_key' => 'id',
                    'display_field' => 'name'
                ];
        }
    }

    // Method untuk menghapus data
    public function delete_data($kategori = null, $id = null) {
        if (!$kategori || !$id) {
            $this->session->set_flashdata('error', 'Parameter tidak lengkap');
            redirect('Dashboard_new/index');
            return;
        }

        $token = $this->session->userdata('token');
        $user = $this->dash->getUserInfo($token)->row_array();
        
        // Convert URL parameter to readable category name
        $kategori_map = [
            'agen' => 'Agen',
            'kemitraan' => 'Kemitraan', 
            'subagen' => 'Sub Agen',
            'peternak' => 'Peternak',
            'farm' => 'Farm',
            'lokasi_baru' => 'Lokasi Baru',
            'pakan' => 'Pakan',
            'strain' => 'Strain',
            'target' => 'Target' 
        ];

        if (!isset($kategori_map[$kategori])) {
            $this->session->set_flashdata('error', 'Kategori tidak ditemukan');
            redirect('Dashboard_new/index');
            return;
        }

        $kategori_name = $kategori_map[$kategori];
        
        // Get existing data first for validation and option cleanup
        $existing_data = $this->_get_existing_data($kategori_name, $id, $user);
        if (!$existing_data) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan');
            redirect('Admin_Controller/list_data/' . $kategori);
            return;
        }

        // Perform deletion
        $result = $this->_delete_data($kategori_name, $id, $user, $existing_data);
        
        if ($result) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data');
        }
        
        redirect('Admin_Controller/list_data/' . $kategori);
    }

    private function _delete_data($kategori, $id, $user, $existing_data) {
        switch($kategori) {
            case 'Agen':
                // Delete related options first
                $this->_delete_options('master_agen', $existing_data['nama_agen'], $user['master_sub_area_id']);
                // Delete main data
                $this->db->where('master_agen_id', $id)
                         ->where('master_sub_area_id', $user['master_sub_area_id'])
                         ->delete('master_agen');
                return $this->db->affected_rows() > 0;
                
            case 'Kemitraan':
                // Delete related options first
                $this->_delete_options('master_kemitraan', $existing_data['nama_kantor_kemitraan'], $user['master_sub_area_id']);
                // Delete main data
                $this->db->where('master_kemitraan_id', $id)
                         ->where('master_sub_area_id', $user['master_sub_area_id'])
                         ->delete('master_kemitraan');
                return $this->db->affected_rows() > 0;
                
            case 'Sub Agen':
                // Delete related options first
                $this->_delete_options('master_subagen', $existing_data['nama_subagen'], $user['master_sub_area_id']);
                // Delete main data
                $this->db->where('master_subagen_id', $id)
                         ->where('master_sub_area_id', $user['master_sub_area_id'])
                         ->delete('master_subagen');
                return $this->db->affected_rows() > 0;
                
            case 'Peternak':
                // Delete related options first
                $this->_delete_options('master_peternak', $existing_data['nama_peternak'], $user['master_sub_area_id']);
                // Delete main data
                $this->db->where('master_peternak_id', $id)
                         ->where('master_sub_area_id', $user['master_sub_area_id'])
                         ->delete('master_peternak');
                return $this->db->affected_rows() > 0;
                
            case 'Farm':
                // Delete related options first
                $this->_delete_farm_options($existing_data, $user['master_sub_area_id']);
                // Delete main data
                $this->db->where('master_farm_id', $id)
                         ->where('master_sub_area_id', $user['master_sub_area_id'])
                         ->delete('master_farm');
                return $this->db->affected_rows() > 0;
                
            case 'Lokasi Baru':
                // Delete related options first
                $this->_delete_options('master_lokasi_lainnya', $existing_data['nama_lokasi'], $user['master_sub_area_id']);
                // Delete main data
                $this->db->where('master_lokasi_lainnya_id', $id)
                         ->where('master_sub_area_id', $user['master_sub_area_id'])
                         ->delete('master_lokasi_lainnya');
                return $this->db->affected_rows() > 0;
                
            case 'Pakan':
                // Delete related options first
                $this->_delete_pakan_options($existing_data);
                // Delete main data
                $this->db->where('master_pakan_id', $id)
                         ->delete('master_pakan');
                return $this->db->affected_rows() > 0;
                
            case 'Strain':
                // Delete related options first
                $this->_delete_strain_options($existing_data);
                // Delete main data
                $this->db->where('master_strain_id', $id)
                         ->delete('master_strain');
                return $this->db->affected_rows() > 0;

            case 'Target':
                $this->db->where('id_target', $id)
                         ->delete('targets');
                return $this->db->affected_rows() > 0;
                
            default:
                return false;
        }
    }

    private function _delete_options($page, $option_text, $master_sub_area_id) {
        $questions = $this->db->select('questions_id')
                             ->from('questions')
                             ->where('page', $page)
                             ->where('add_to_options', 1)
                             ->get()
                             ->result_array();
                     
        foreach ($questions as $question) {
            $this->db->where([
                'questions_id' => $question['questions_id'],
                'option_text' => $option_text,
                'master_sub_area_id' => $master_sub_area_id
            ])
            ->delete('options');
        }
    }

    private function _delete_farm_options($existing_data, $master_sub_area_id) {
        $questions = $this->db->select('questions_id, field_name')
                             ->from('questions')
                             ->where('page', 'master_farm')
                             ->where('add_to_options', 1)
                             ->get()
                             ->result_array();
                     
        foreach ($questions as $question) {
            if ($question['field_name'] === 'nama_farm') {
                $this->db->where([
                    'questions_id' => $question['questions_id'],
                    'option_text' => $existing_data['nama_farm'],
                    'master_sub_area_id' => $master_sub_area_id
                ])
                ->delete('options');
            }
        }
    }

    private function _delete_pakan_options($existing_data) {
        $questions = $this->db->select('questions_id, field_name')
                             ->from('questions')
                             ->where('page', 'master_pakan')
                             ->where('add_to_options', 1)
                             ->get()
                             ->result_array();
                            
        foreach ($questions as $question) {
            if ($question['field_name'] === 'nama_pakan' && !empty($existing_data['nama_pakan'])) {
                $this->db->where([
                    'questions_id' => $question['questions_id'],
                    'option_text' => $existing_data['nama_pakan']
                ])
                ->delete('options');
            }
        }
    }

    private function _delete_strain_options($existing_data) {
        $questions = $this->db->select('questions_id, field_name')
                             ->from('questions')
                             ->where('page', 'master_strain')
                             ->where('add_to_options', 1)
                             ->get()
                             ->result_array();
                        
        foreach ($questions as $question) {
            if ($question['field_name'] === 'nama_strain' && !empty($existing_data['nama_strain'])) {
                $this->db->where([
                    'questions_id' => $question['questions_id'],
                    'option_text' => $existing_data['nama_strain']
                ])
                ->delete('options');
            }
        }
    }

    public function index() {
        redirect('Dashboard_new/index');
    }
}
