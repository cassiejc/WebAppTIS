<?php
class M_master_farm extends CI_Model {

    // Fungsi ini sudah ada, tidak perlu diubah
    public function insert_master_farm($data) {
        return $this->db->insert('master_farm', $data);
    }

    // Fungsi ini sudah ada, tidak perlu diubah
    public function get_questions_by_page($page = 'master_farm') {
        $this->db->where('page', $page);
        return $this->db->get('questions')->result_array();
    }

    // --- TAMBAHKAN FUNGSI-FUNGSI BARU DI BAWAH INI ---

    /**
     * Mengambil data farm berdasarkan ID-nya.
     * Dibutuhkan untuk membandingkan kapasitas lama dan baru.
     */
    public function get_farm_by_id($id) {
        return $this->db->get_where('master_farm', ['master_farm_id' => $id])->row_array();
    }

    /**
     * Mengupdate data di tabel utama (master_farm).
     */
    public function update_farm_data($id, $data) {
        $this->db->where('master_farm_id', $id);
        return $this->db->update('master_farm', $data);
    }
    
    /**
     * Menutup riwayat kapasitas yang aktif saat ini.
     * Mencari record dengan end_date '9999-12-31' dan mengupdatenya.
     */
    public function close_current_capacity_history($master_farm_id, $end_date) {
        $this->db->where('master_farm_id', $master_farm_id);
        $this->db->where('end_date', '9999-12-31'); // Cari record yang masih aktif
        $this->db->update('history_farm_capacity', ['end_date' => $end_date]);
    }

    /**
     * Menambahkan record riwayat kapasitas yang baru.
     */
    public function add_new_capacity_history($data) {
        return $this->db->insert('history_farm_capacity', $data);
    }

    /**
     * [SANGAT DISARANKAN] Membuat record riwayat awal saat farm pertama kali dibuat.
     * Panggil fungsi ini di controller saat Anda menambahkan farm baru.
     */
    public function create_initial_capacity_history($master_farm_id, $kapasitas) {
        $data = [
            'master_farm_id' => $master_farm_id,
            'kapasitas'      => $kapasitas,
            'start_date'     => date('Y-m-d'), // Tanggal hari ini
            'end_date'       => '9999-12-31'   // Tanda record ini aktif
        ];
        return $this->db->insert('history_farm_capacity', $data);
    }

    public function get_all_peternak() {
        $this->db->order_by('nama_peternak', 'ASC');
        return $this->db->get('master_peternak')->result_array();
    }

    public function get_options_by_field_name($field_name, $page) {
        // 1. Cari ID pertanyaan berdasarkan field_name DAN page
        $this->db->where('field_name', $field_name);
        $this->db->where('page', $page); // <-- Tambahan filter berdasarkan page
        $question = $this->db->get('questions')->row();

        // 2. Jika pertanyaan tidak ditemukan, kembalikan array kosong
        if (!$question) {
            return [];
        }

        // 3. Ambil semua opsi yang terhubung dengan ID pertanyaan tersebut
        $this->db->select('option_text');
        $this->db->from('options');
        $this->db->where('questions_id', $question->questions_id);
        $this->db->order_by('option_text', 'ASC');
        
        return $this->db->get()->result_array();
    }
}
