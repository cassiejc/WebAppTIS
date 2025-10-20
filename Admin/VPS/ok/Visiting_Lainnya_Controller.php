<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Visiting_Lainnya_Controller extends CI_Controller {

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
        try {
            // Mengambil data sesi yang mungkin diperlukan
            $form_data_session = $this->session->userdata('visiting_form_data') ?: [];
            $tujuan_kunjungan = $form_data_session['tujuan_kunjungan'] ?? '-';
            
            $jenis_kasus = '-';
            if ($tujuan_kunjungan === 'Kasus' && isset($form_data_session['jenis_kasus'])) {
                $jenis_kasus = $form_data_session['jenis_kasus'];
            }

            // --- OPTIMASI: Gunakan fungsi process_form_data dari Model ---
            // Model akan menangani pengambilan pertanyaan dan pembersihan nilai input (angka, desimal, dll)
            // berdasarkan konfigurasi di $field_types.
            $processed_data = $this->M_Questions->process_form_data(
                'visiting_lainnya', 
                $this->input->post(), 
                $user
            );

            // Gabungkan data yang diproses oleh model dengan data lainnya
            $final_data = array_merge($processed_data, [
                'tujuan_kunjungan' => $tujuan_kunjungan,
                'jenis_kasus'      => $jenis_kasus,
                'latitude'         => $this->input->post('latitude'),
                'longitude'        => $this->input->post('longitude'),
                'location_address' => $this->input->post('location_address'),
                'waktu_kunjungan'  => date('Y-m-d H:i:s')
            ]);
            
            // Hapus data yang tidak perlu disimpan di tabel visiting (jika ada)
            // Contoh: 'tipe_ternak' mungkin tidak perlu jika tidak ada kolomnya
            // unset($final_data['tipe_ternak']);

            // Insert data menggunakan 'Lainnya' sebagai penanda tipe
            $this->visiting->insert_visiting($final_data, 'Lainnya');
            
            $this->session->set_flashdata('success', 'Data visiting berhasil disimpan!');

        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
        
        // Selalu bersihkan sesi setelah proses selesai
        $this->session->unset_userdata(['visiting_form_data', 'visiting_type', 'livestock_type']);
        
        redirect('Dashboard_new/index');
    }

    private function _display_form($user) {
        $nama_lokasi = '';

        // Cek peran user untuk menentukan nama lokasi
        if (isset($user['group_user']) && $user['group_user'] === 'koordinator') {
            $area = $this->db->get_where('master_area', ['master_area_id' => $user['master_area_id']])->row_array();
            if ($area) {
                $nama_lokasi = $area['nama_area'];
            }
        } else {
            // Logika untuk TIS dan peran lainnya
            $sub_area = $this->db->get_where('master_sub_area', ['master_sub_area_id' => $user['master_sub_area_id']])->row_array();
            if ($sub_area) {
                $nama_lokasi = $sub_area['nama_sub_area'];
            }
        }

        $all_questions = $this->M_Questions->get_form_questions(
            'visiting_lainnya', 
            $user
        );

        foreach ($all_questions as $key => $question) {
            if (isset($question['field_name']) && $question['field_name'] == 'nama_farm') {
                $sub_area_id = $user['master_sub_area_id'] ?? null;
                $this->db->select('nama_farm');
                $this->db->from('master_farm');

                if ($sub_area_id) {
                    $this->db->where('master_sub_area_id', $sub_area_id);
                }
                $this->db->where('tipe_ternak', 'Lainnya');
                $this->db->order_by('nama_farm', 'ASC');

                $filtered_farms = $this->db->get()->result_array();
                $new_options = [];
                if (!empty($filtered_farms)) {
                    foreach ($filtered_farms as $farm) {
                        $new_options[] = [
                            'option_value' => $farm['nama_farm'],
                            'option_text' => $farm['nama_farm']
                        ];
                    }
                } 
                $all_questions[$key]['options'] = $new_options;
                break; // Keluar dari loop setelah menemukan dan memperbarui pertanyaan
            }
        }

        $data = [
            'title' => 'CP APPS',
            'questions' => $all_questions,
            'nama_lokasi_header' => $nama_lokasi,
            'visiting_type' => $this->session->userdata('visiting_type'),
            'livestock_type' => $this->session->userdata('livestock_type')
        ];
        
        $this->load->view('templates/dash_h', $data);
        $this->load->view('form_visiting_lainnya_view', $data);
        $this->load->view('templates/dash_f', $data);
    }

    /**
     * AJAX method untuk mendapatkan options berdasarkan tipe ternak.
     * Fungsi ini sudah benar dan tidak perlu diubah.
     */
    public function get_options_by_livestock_type() {
        $questions_id = $this->input->post('questions_id');
        $livestock_type = $this->input->post('livestock_type');
        $token = $this->session->userdata('token');
        $user = $this->dash->getUserInfo($token)->row_array();
        
        $options = $this->M_Questions->get_options_by_livestock_type(
            $questions_id, 
            $user['master_sub_area_id'], 
            $livestock_type
        );
        
        // Return JSON response
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($options));
    }
    
}
