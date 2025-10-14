<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_master_harga extends CI_Model {

    public function get_all_harga()
    {
        return $this->db->get('master_harga')->result_array();
    }

    public function get_harga_by_id($id)
    {
        return $this->db->get_where('master_harga', ['id_harga' => $id])->row_array();
    }

    public function create_harga($data)
    {
        // Tambahkan created_at dan updated_at saat membuat data baru
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('master_harga', $data);
    }

    // FUNGSI PENTING UNTUK UPDATE
    public function update_harga($id_harga, $data)
    {
        // Set 'updated_at' ke waktu saat ini setiap kali ada update
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('id_harga', $id_harga);
        return $this->db->update('master_harga', $data);
    }

    public function delete_harga($id_harga)
    {
        return $this->db->delete('master_harga', ['id_harga' => $id_harga]);
    }

    /**
     * Menghitung ulang harga rata-rata telur dari tabel visiting_p_layer
     * berdasarkan user terpilih dan memperbarui tabel master_harga.
     * @param int $id_harga ID dari item harga yang akan diupdate
     * @return bool True jika berhasil, false jika gagal atau tidak ada data.
     */
    public function calculate_and_update_average_harga_telur_layer($id_harga)
    {
        // 1. SELECT: Pilih nilai rata-rata dari kolom harga yang diinginkan
        $this->db->select('AVG(vpl.layer_harga_jual_telur) as rata_rata');
        $this->db->from('visiting_p_layer vpl');

        // 2. JOIN: Gabungkan dengan tabel histori untuk filtering
        $this->db->join('history_user_terpilih h', 'vpl.id_user = h.id_user', 'inner');
        
        // 3. FILTER UTAMA: Menggunakan 'waktu_kunjungan' sebagai kolom tanggal
        $this->db->where('vpl.waktu_kunjungan >= h.start_date'); 
        $this->db->where('(vpl.waktu_kunjungan <= h.end_date OR h.end_date IS NULL)', NULL, FALSE);
        
        // Ambil hasil perhitungan
        $result = $this->db->get()->row();

        // 4. UPDATE: Jika ada hasil, perbarui tabel master_harga
        if ($result && $result->rata_rata !== null) {
            $new_average = $result->rata_rata;
            
            $update_data = [
                'nilai_harga' => $new_average
            ];

            return $this->update_harga($id_harga, $update_data);
        }

        return false;
    }

}
