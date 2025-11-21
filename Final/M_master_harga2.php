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
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('master_harga', $data);
    }

    public function update_harga($id_harga, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('id_harga', $id_harga);
        return $this->db->update('master_harga', $data);
    }

    public function delete_harga($id_harga)
    {
        return $this->db->delete('master_harga', ['id_harga' => $id_harga]);
    }

    /**
     * Memproses dan menyimpan harga rata-rata harian untuk telur layer.
     * Fungsi ini spesifik untuk jenis_harga 'harga_jual_telur_layer'.
     *
     * @param int $id_harga ID dari item harga di tabel master_harga.
     * @return bool True jika berhasil.
     */
    public function proses_rata_rata_harian_telur_layer($id_harga)
    {
        $tanggal_hari_ini = date('Y-m-d');
        $jenis_harga = 'harga_jual_telur_layer'; 
        $nilai_rata_rata_hari_ini = 0;
        $jumlah_sumber_data = 0;
        
        $this->db->select('
            AVG(vpl.layer_harga_jual_telur) as rata_rata_baru,
            COUNT(vpl.layer_harga_jual_telur) as jumlah_data_baru
        ');
        $this->db->from('visiting_p_layer vpl');
        $this->db->join('history_user_terpilih h', 'vpl.id_user = h.id_user', 'inner');
        $this->db->where("DATE(vpl.waktu_kunjungan)", $tanggal_hari_ini);
        $this->db->where('h.jenis_harga_key', $jenis_harga);
        $this->db->where('vpl.waktu_kunjungan >= h.start_date');
        $this->db->where('(vpl.waktu_kunjungan <= h.end_date OR h.end_date IS NULL)', NULL, FALSE);
        $data_hari_ini = $this->db->get()->row();
        
        if ($data_hari_ini && $data_hari_ini->jumlah_data_baru > 0) {
            $nilai_rata_rata_hari_ini = $data_hari_ini->rata_rata_baru;
            $jumlah_sumber_data = $data_hari_ini->jumlah_data_baru;
        } else {
            $tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));
            $harga_kemarin = $this->db->get_where('harga_rata_rata_harian', [
                'master_harga_id' => $id_harga,
                'tanggal' => $tanggal_kemarin,
                'jenis_harga' => $jenis_harga
            ])->row();

            if ($harga_kemarin) {
                $nilai_rata_rata_hari_ini = $harga_kemarin->nilai_rata_rata;
            } else {
                $nilai_rata_rata_hari_ini = 0;
            }
        }
        
        $data_harian = [
            'master_harga_id' => $id_harga,
            'jenis_harga' => $jenis_harga, 
            'tanggal' => $tanggal_hari_ini,
            'nilai_rata_rata' => $nilai_rata_rata_hari_ini,
            'jumlah_sumber_data' => $jumlah_sumber_data
        ];

        $this->db->where('master_harga_id', $id_harga);
        $this->db->where('tanggal', $tanggal_hari_ini);
        $this->db->where('jenis_harga', $jenis_harga); 
        $exists = $this->db->get('harga_rata_rata_harian')->num_rows() > 0;

        if ($exists) {
            $this->db->where('master_harga_id', $id_harga);
            $this->db->where('tanggal', $tanggal_hari_ini);
            $this->db->where('jenis_harga', $jenis_harga); 
            $this->db->update('harga_rata_rata_harian', $data_harian);
        } else {
            $this->db->insert('harga_rata_rata_harian', $data_harian);
        }

        $this->update_harga($id_harga, ['nilai_harga' => $nilai_rata_rata_hari_ini]);

        return true;
    }

    /**
     * Menghitung dan menyimpan harga rata-rata bulanan untuk telur layer.
     * Sumber data berasal dari tabel harga_rata_rata_harian.
     *
     * @param int $id_harga ID dari item harga di tabel master_harga.
     * @param int $tahun Tahun yang akan dihitung (misal: 2025).
     * @param int $bulan Bulan yang akan dihitung (misal: 10 untuk Oktober).
     * @return bool True jika berhasil.
     */

    public function hitung_rata_rata_bulanan_telur_layer($id_harga, $tahun, $bulan)
    {
        $jenis_harga = 'harga_jual_telur_layer';

        $this->db->select('
            SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang,
            SUM(jumlah_sumber_data) as total_sumber_data 
        ');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('master_harga_id', $id_harga);
        $this->db->where('jenis_harga', $jenis_harga);
        $this->db->where('YEAR(tanggal)', $tahun);
        $this->db->where('MONTH(tanggal)', $bulan);
        $result = $this->db->get()->row();

        $rata_rata_bulanan = 0;
        $jumlah_sumber_data_bulanan = 0;

        if ($result && $result->total_sumber_data > 0) {
            $rata_rata_bulanan = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_sumber_data_bulanan = $result->total_sumber_data;
        } else {
            $this->db->select('nilai_rata_rata');
            $this->db->from('harga_rata_rata_harian');
            $this->db->where('master_harga_id', $id_harga);
            $this->db->where('jenis_harga', $jenis_harga);
            $this->db->where("CONCAT(YEAR(tanggal), '-', MONTH(tanggal)) <=", "'$tahun-$bulan'", false);
            $this->db->order_by('tanggal', 'DESC');
            $this->db->limit(1);
            $last_price = $this->db->get()->row();
            if ($last_price) {
                $rata_rata_bulanan = $last_price->nilai_rata_rata;
            }
        }

        $data_bulanan = [
            'master_harga_id'    => $id_harga,
            'jenis_harga'        => $jenis_harga,
            'tahun'              => $tahun,
            'bulan'              => $bulan,
            'nilai_rata_rata'    => $rata_rata_bulanan,
            'jumlah_sumber_data' => $jumlah_sumber_data_bulanan
        ];

        $this->db->where([
            'master_harga_id' => $id_harga,
            'jenis_harga'     => $jenis_harga,
            'tahun'           => $tahun,
            'bulan'           => $bulan
        ]);
        $exists = $this->db->get('harga_rata_rata_bulanan')->num_rows() > 0;

        if ($exists) {
            $this->db->where([
                'master_harga_id' => $id_harga,
                'jenis_harga'     => $jenis_harga,
                'tahun'           => $tahun,
                'bulan'           => $bulan
            ]);
            return $this->db->update('harga_rata_rata_bulanan', $data_bulanan);
        } else {
            return $this->db->insert('harga_rata_rata_bulanan', $data_bulanan);
        }
    }
    
    /**
     * Menghitung dan menyimpan harga rata-rata tahunan untuk telur layer.
     * Sumber data berasal dari tabel harga_rata_rata_bulanan.
     *
     * @param int $id_harga ID dari item harga di tabel master_harga.
     * @param int $tahun Tahun yang akan dihitung (misal: 2025).
     * @return bool True jika berhasil.
     */
    public function hitung_rata_rata_tahunan_telur_layer($id_harga, $tahun)
    {
        $jenis_harga = 'harga_jual_telur_layer';

        $this->db->select('
            SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang,
            SUM(jumlah_sumber_data) as total_sumber_data
        ');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where('master_harga_id', $id_harga);
        $this->db->where('jenis_harga', $jenis_harga);
        $this->db->where('tahun', $tahun);
        $result = $this->db->get()->row();

        $rata_rata_tahunan = 0;
        $jumlah_sumber_data_tahunan = 0;

        if ($result && $result->total_sumber_data > 0) {
            $rata_rata_tahunan = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_sumber_data_tahunan = $result->total_sumber_data;
        } else {
            $this->db->select('nilai_rata_rata');
            $this->db->from('harga_rata_rata_bulanan');
            $this->db->where('master_harga_id', $id_harga);
            $this->db->where('jenis_harga', $jenis_harga);
            $this->db->where('tahun <=', $tahun);
            $this->db->order_by('tahun DESC, bulan DESC');
            $this->db->limit(1);
            $last_price = $this->db->get()->row();
            if ($last_price) {
                $rata_rata_tahunan = $last_price->nilai_rata_rata;
            }
        }

        $data_tahunan = [
            'master_harga_id'    => $id_harga,
            'jenis_harga'        => $jenis_harga,
            'tahun'              => $tahun,
            'nilai_rata_rata'    => $rata_rata_tahunan,
            'jumlah_sumber_data' => $jumlah_sumber_data_tahunan
        ];

        $this->db->where([
            'master_harga_id' => $id_harga,
            'jenis_harga'     => $jenis_harga,
            'tahun'           => $tahun
        ]);
        $exists = $this->db->get('harga_rata_rata_tahunan')->num_rows() > 0;

        if ($exists) {
            $this->db->where([
                'master_harga_id' => $id_harga,
                'jenis_harga'     => $jenis_harga,
                'tahun'           => $tahun
            ]);
            return $this->db->update('harga_rata_rata_tahunan', $data_tahunan);
        } else {
            return $this->db->insert('harga_rata_rata_tahunan', $data_tahunan);
        }
    }
    
    public function proses_rata_rata_harian_jagung($id_harga)
    {
        $tanggal_hari_ini = date('Y-m-d');
        $jenis_harga = 'harga_jagung'; 
        $nilai_rata_rata_hari_ini = 0;
        $jumlah_sumber_data = 0;

        $this->db->select('
            AVG(vpl.layer_harga_beli_jagung) as rata_rata_baru,
            COUNT(vpl.layer_harga_beli_jagung) as jumlah_data_baru
        ');
        $this->db->from('visiting_p_layer vpl');
        $this->db->join('history_user_terpilih h', 'vpl.id_user = h.id_user', 'inner');
        $this->db->where("DATE(vpl.waktu_kunjungan)", $tanggal_hari_ini);
        $this->db->where('h.jenis_harga_key', $jenis_harga);
        $this->db->where('vpl.waktu_kunjungan >= h.start_date');
        $this->db->where('(vpl.waktu_kunjungan <= h.end_date OR h.end_date IS NULL)', NULL, FALSE);
        $data_hari_ini = $this->db->get()->row();

        if ($data_hari_ini && $data_hari_ini->jumlah_data_baru > 0) {
            $nilai_rata_rata_hari_ini = $data_hari_ini->rata_rata_baru;
            $jumlah_sumber_data = $data_hari_ini->jumlah_data_baru;
        } else {
            $tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));
            $harga_kemarin = $this->db->get_where('harga_rata_rata_harian', [
                'master_harga_id' => $id_harga,
                'tanggal' => $tanggal_kemarin,
                'jenis_harga' => $jenis_harga
            ])->row();
            $nilai_rata_rata_hari_ini = $harga_kemarin ? $harga_kemarin->nilai_rata_rata : 0;
        }

        $data_harian = [
            'master_harga_id' => $id_harga,
            'jenis_harga' => $jenis_harga,
            'tanggal' => $tanggal_hari_ini,
            'nilai_rata_rata' => $nilai_rata_rata_hari_ini,
            'jumlah_sumber_data' => $jumlah_sumber_data
        ];
        $this->db->where(['master_harga_id' => $id_harga, 'tanggal' => $tanggal_hari_ini, 'jenis_harga' => $jenis_harga]);
        $exists = $this->db->get('harga_rata_rata_harian')->num_rows() > 0;
        if ($exists) {
            $this->db->where(['master_harga_id' => $id_harga, 'tanggal' => $tanggal_hari_ini, 'jenis_harga' => $jenis_harga]);
            $this->db->update('harga_rata_rata_harian', $data_harian);
        } else {
            $this->db->insert('harga_rata_rata_harian', $data_harian);
        }

        $this->update_harga($id_harga, ['nilai_harga' => $nilai_rata_rata_hari_ini]);
        return true;
    }
    
    public function hitung_rata_rata_bulanan_jagung($id_harga, $tahun, $bulan)
    {
        $jenis_harga = 'harga_jagung';

        $this->db->select('
            SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang,
            SUM(jumlah_sumber_data) as total_sumber_data
        ');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where(['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga]);
        $this->db->where('YEAR(tanggal)', $tahun);
        $this->db->where('MONTH(tanggal)', $bulan);
        $result = $this->db->get()->row();

        $rata_rata_bulanan = 0;
        $jumlah_sumber_data_bulanan = 0;

        if ($result && $result->total_sumber_data > 0) {
            $rata_rata_bulanan = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_sumber_data_bulanan = $result->total_sumber_data;
        }

        $data_bulanan = [
            'master_harga_id'    => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun,
            'bulan' => $bulan, 'nilai_rata_rata' => $rata_rata_bulanan,
            'jumlah_sumber_data' => $jumlah_sumber_data_bulanan
        ];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan];
        $exists = $this->db->get_where('harga_rata_rata_bulanan', $where)->num_rows() > 0;

        return $exists ? $this->db->update('harga_rata_rata_bulanan', $data_bulanan, $where) : $this->db->insert('harga_rata_rata_bulanan', $data_bulanan);
    }
    
    public function hitung_rata_rata_tahunan_jagung($id_harga, $tahun)
    {
        $jenis_harga = 'harga_jagung';

        $this->db->select('
            SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang,
            SUM(jumlah_sumber_data) as total_sumber_data
        ');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where(['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun]);
        $result = $this->db->get()->row();

        $rata_rata_tahunan = 0;
        $jumlah_sumber_data_tahunan = 0;

        if ($result && $result->total_sumber_data > 0) {
            $rata_rata_tahunan = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_sumber_data_tahunan = $result->total_sumber_data;
        }

        $data_tahunan = [
            'master_harga_id'    => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun,
            'nilai_rata_rata'    => $rata_rata_tahunan,
            'jumlah_sumber_data' => $jumlah_sumber_data_tahunan
        ];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun];
        $exists = $this->db->get_where('harga_rata_rata_tahunan', $where)->num_rows() > 0;

        return $exists ? $this->db->update('harga_rata_rata_tahunan', $data_tahunan, $where) : $this->db->insert('harga_rata_rata_tahunan', $data_tahunan);
    }    
    
    public function proses_rata_rata_harian_katul($id_harga)
    {
        $tanggal_hari_ini = date('Y-m-d');
        $jenis_harga = 'harga_katul'; 
        $nilai_rata_rata_hari_ini = 0;
        $jumlah_sumber_data = 0;

        $this->db->select('
            AVG(vpl.layer_harga_beli_katul) as rata_rata_baru,
            COUNT(vpl.layer_harga_beli_katul) as jumlah_data_baru
        ');
        $this->db->from('visiting_p_layer vpl');
        $this->db->join('history_user_terpilih h', 'vpl.id_user = h.id_user', 'inner');
        $this->db->where("DATE(vpl.waktu_kunjungan)", $tanggal_hari_ini);
        $this->db->where('h.jenis_harga_key', $jenis_harga);
        $this->db->where('vpl.waktu_kunjungan >= h.start_date');
        $this->db->where('(vpl.waktu_kunjungan <= h.end_date OR h.end_date IS NULL)', NULL, FALSE);
        $data_hari_ini = $this->db->get()->row();

        if ($data_hari_ini && $data_hari_ini->jumlah_data_baru > 0) {
            $nilai_rata_rata_hari_ini = $data_hari_ini->rata_rata_baru;
            $jumlah_sumber_data = $data_hari_ini->jumlah_data_baru;
        } else {
            $tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));
            $harga_kemarin = $this->db->get_where('harga_rata_rata_harian', [
                'master_harga_id' => $id_harga,
                'tanggal' => $tanggal_kemarin,
                'jenis_harga' => $jenis_harga
            ])->row();
            $nilai_rata_rata_hari_ini = $harga_kemarin ? $harga_kemarin->nilai_rata_rata : 0;
        }

        $data_harian = [
            'master_harga_id' => $id_harga,
            'jenis_harga' => $jenis_harga,
            'tanggal' => $tanggal_hari_ini,
            'nilai_rata_rata' => $nilai_rata_rata_hari_ini,
            'jumlah_sumber_data' => $jumlah_sumber_data
        ];
        $this->db->where(['master_harga_id' => $id_harga, 'tanggal' => $tanggal_hari_ini, 'jenis_harga' => $jenis_harga]);
        $exists = $this->db->get('harga_rata_rata_harian')->num_rows() > 0;
        if ($exists) {
            $this->db->where(['master_harga_id' => $id_harga, 'tanggal' => $tanggal_hari_ini, 'jenis_harga' => $jenis_harga]);
            $this->db->update('harga_rata_rata_harian', $data_harian);
        } else {
            $this->db->insert('harga_rata_rata_harian', $data_harian);
        }

        $this->update_harga($id_harga, ['nilai_harga' => $nilai_rata_rata_hari_ini]);
        return true;
    }
    
    public function hitung_rata_rata_bulanan_katul($id_harga, $tahun, $bulan)
    {
        $jenis_harga = 'harga_katul';

        $this->db->select('
            SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang,
            SUM(jumlah_sumber_data) as total_sumber_data
        ');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where(['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga]);
        $this->db->where('YEAR(tanggal)', $tahun);
        $this->db->where('MONTH(tanggal)', $bulan);
        $result = $this->db->get()->row();

        $rata_rata_bulanan = 0;
        $jumlah_sumber_data_bulanan = 0;

        if ($result && $result->total_sumber_data > 0) {
            $rata_rata_bulanan = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_sumber_data_bulanan = $result->total_sumber_data;
        }

        $data_bulanan = [
            'master_harga_id'    => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun,
            'bulan' => $bulan, 'nilai_rata_rata' => $rata_rata_bulanan,
            'jumlah_sumber_data' => $jumlah_sumber_data_bulanan
        ];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan];
        $exists = $this->db->get_where('harga_rata_rata_bulanan', $where)->num_rows() > 0;

        return $exists ? $this->db->update('harga_rata_rata_bulanan', $data_bulanan, $where) : $this->db->insert('harga_rata_rata_bulanan', $data_bulanan);
    }
    
    public function hitung_rata_rata_tahunan_katul($id_harga, $tahun)
    {
        $jenis_harga = 'harga_katul';

        $this->db->select('
            SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang,
            SUM(jumlah_sumber_data) as total_sumber_data
        ');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where(['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun]);
        $result = $this->db->get()->row();

        $rata_rata_tahunan = 0;
        $jumlah_sumber_data_tahunan = 0;

        if ($result && $result->total_sumber_data > 0) {
            $rata_rata_tahunan = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_sumber_data_tahunan = $result->total_sumber_data;
        }

        $data_tahunan = [
            'master_harga_id'    => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun,
            'nilai_rata_rata'    => $rata_rata_tahunan,
            'jumlah_sumber_data' => $jumlah_sumber_data_tahunan
        ];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun];
        $exists = $this->db->get_where('harga_rata_rata_tahunan', $where)->num_rows() > 0;

        return $exists ? $this->db->update('harga_rata_rata_tahunan', $data_tahunan, $where) : $this->db->insert('harga_rata_rata_tahunan', $data_tahunan);
    }
    
    public function proses_rata_rata_harian_afkir($id_harga)
    {
        $tanggal_hari_ini = date('Y-m-d');
        $jenis_harga = 'harga_afkir'; 
        $nilai_rata_rata_hari_ini = 0;
        $jumlah_sumber_data = 0;

        $this->db->select('
            AVG(vpl.layer_harga_afkir) as rata_rata_baru,
            COUNT(vpl.layer_harga_afkir) as jumlah_data_baru
        ');
        $this->db->from('visiting_p_layer vpl');
        $this->db->join('history_user_terpilih h', 'vpl.id_user = h.id_user', 'inner');
        $this->db->where("DATE(vpl.waktu_kunjungan)", $tanggal_hari_ini);
        $this->db->where('h.jenis_harga_key', $jenis_harga);
        $this->db->where('vpl.waktu_kunjungan >= h.start_date');
        $this->db->where('(vpl.waktu_kunjungan <= h.end_date OR h.end_date IS NULL)', NULL, FALSE);
        $data_hari_ini = $this->db->get()->row();

        if ($data_hari_ini && $data_hari_ini->jumlah_data_baru > 0) {
            $nilai_rata_rata_hari_ini = $data_hari_ini->rata_rata_baru;
            $jumlah_sumber_data = $data_hari_ini->jumlah_data_baru;
        } else {
            $tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));
            $harga_kemarin = $this->db->get_where('harga_rata_rata_harian', [
                'master_harga_id' => $id_harga,
                'tanggal' => $tanggal_kemarin,
                'jenis_harga' => $jenis_harga
            ])->row();
            $nilai_rata_rata_hari_ini = $harga_kemarin ? $harga_kemarin->nilai_rata_rata : 0;
        }

        $data_harian = [
            'master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tanggal' => $tanggal_hari_ini,
            'nilai_rata_rata' => $nilai_rata_rata_hari_ini, 'jumlah_sumber_data' => $jumlah_sumber_data
        ];
        $this->db->where(['master_harga_id' => $id_harga, 'tanggal' => $tanggal_hari_ini, 'jenis_harga' => $jenis_harga]);
        if ($this->db->get('harga_rata_rata_harian')->num_rows() > 0) {
            $this->db->where(['master_harga_id' => $id_harga, 'tanggal' => $tanggal_hari_ini, 'jenis_harga' => $jenis_harga]);
            $this->db->update('harga_rata_rata_harian', $data_harian);
        } else {
            $this->db->insert('harga_rata_rata_harian', $data_harian);
        }

        $this->update_harga($id_harga, ['nilai_harga' => $nilai_rata_rata_hari_ini]);
        return true;
    }
    
    public function hitung_rata_rata_bulanan_afkir($id_harga, $tahun, $bulan)
    {
        $jenis_harga = 'harga_afkir';
        $this->db->select('SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang, SUM(jumlah_sumber_data) as total_sumber_data');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where(['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga]);
        $this->db->where('YEAR(tanggal)', $tahun)->where('MONTH(tanggal)', $bulan);
        $result = $this->db->get()->row();

        $rata_rata = 0; $jumlah_data = 0;
        if ($result && $result->total_sumber_data > 0) {
            $rata_rata = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_data = $result->total_sumber_data;
        }

        $data = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan, 'nilai_rata_rata' => $rata_rata, 'jumlah_sumber_data' => $jumlah_data];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan];
        return $this->db->get_where('harga_rata_rata_bulanan', $where)->num_rows() > 0 ? $this->db->update('harga_rata_rata_bulanan', $data, $where) : $this->db->insert('harga_rata_rata_bulanan', $data);
    }
    
    public function hitung_rata_rata_tahunan_afkir($id_harga, $tahun)
    {
        $jenis_harga = 'harga_afkir';
        $this->db->select('SUM(total_nilai_tertimbang_bulanan) as total_nilai_tertimbang_tahunan, SUM(total_sumber_data_bulanan) as total_sumber_data_tahunan');
        $this->db->from('(SELECT (nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang_bulanan, jumlah_sumber_data as total_sumber_data_bulanan FROM harga_rata_rata_bulanan WHERE master_harga_id = ' . $this->db->escape($id_harga) . ' AND jenis_harga = ' . $this->db->escape($jenis_harga) . ' AND tahun = ' . $this->db->escape($tahun) . ') as data_bulanan');
        $result = $this->db->get()->row();

        $rata_rata = 0; $jumlah_data = 0;
        if ($result && $result->total_sumber_data_tahunan > 0) {
            $rata_rata = $result->total_nilai_tertimbang_tahunan / $result->total_sumber_data_tahunan;
            $jumlah_data = $result->total_sumber_data_tahunan;
        }

        $data = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'nilai_rata_rata' => $rata_rata, 'jumlah_sumber_data' => $jumlah_data];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun];
        return $this->db->get_where('harga_rata_rata_tahunan', $where)->num_rows() > 0 ? $this->db->update('harga_rata_rata_tahunan', $data, $where) : $this->db->insert('harga_rata_rata_tahunan', $data);
    }
    
    public function proses_rata_rata_harian_telur_puyuh($id_harga)
    {
        $tanggal_hari_ini = date('Y-m-d');
        $jenis_harga = 'harga_telur_puyuh';
        
        $this->db->select('AVG(vpp.harga_jual_telur_terakhir) as rata_rata_baru, COUNT(vpp.harga_jual_telur_terakhir) as jumlah_data_baru');
        $this->db->from('visiting_p_puyuh vpp');
        $this->db->join('history_user_terpilih h', 'vpp.id_user = h.id_user', 'inner');
        $this->db->where("DATE(vpp.waktu_kunjungan)", $tanggal_hari_ini);
        $this->db->where('h.jenis_harga_key', $jenis_harga);
        $this->db->where('vpp.waktu_kunjungan >= h.start_date');
        $this->db->where('(vpp.waktu_kunjungan <= h.end_date OR h.end_date IS NULL)', NULL, FALSE);
        $data_hari_ini = $this->db->get()->row();

        $nilai_rata_rata = 0; $jumlah_sumber_data = 0;
        if ($data_hari_ini && $data_hari_ini->jumlah_data_baru > 0) {
            $nilai_rata_rata = $data_hari_ini->rata_rata_baru;
            $jumlah_sumber_data = $data_hari_ini->jumlah_data_baru;
        } else {
            $harga_kemarin = $this->db->get_where('harga_rata_rata_harian', ['master_harga_id' => $id_harga, 'tanggal' => date('Y-m-d', strtotime('-1 day')), 'jenis_harga' => $jenis_harga])->row();
            $nilai_rata_rata = $harga_kemarin ? $harga_kemarin->nilai_rata_rata : 0;
        }
        
        $data_harian = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tanggal' => $tanggal_hari_ini, 'nilai_rata_rata' => $nilai_rata_rata, 'jumlah_sumber_data' => $jumlah_sumber_data];
        $where = ['master_harga_id' => $id_harga, 'tanggal' => $tanggal_hari_ini, 'jenis_harga' => $jenis_harga];
        if ($this->db->get_where('harga_rata_rata_harian', $where)->num_rows() > 0) {
            $this->db->update('harga_rata_rata_harian', $data_harian, $where);
        } else { $this->db->insert('harga_rata_rata_harian', $data_harian); }
        $this->update_harga($id_harga, ['nilai_harga' => $nilai_rata_rata]);
        return true;
    }
   
    public function proses_rata_rata_harian_telur_bebek($id_harga)
    {
        $tanggal_hari_ini = date('Y-m-d');
        $jenis_harga = 'harga_telur_bebek';

        $this->db->select('AVG(vpb.harga_jual_telur_terakhir) as rata_rata_baru, COUNT(vpb.harga_jual_telur_terakhir) as jumlah_data_baru');
        $this->db->from('visiting_p_bebek_petelur vpb');
        $this->db->join('history_user_terpilih h', 'vpb.id_user = h.id_user', 'inner');
        $this->db->where("DATE(vpb.waktu_kunjungan)", $tanggal_hari_ini);
        $this->db->where('h.jenis_harga_key', $jenis_harga);
        $this->db->where('vpb.waktu_kunjungan >= h.start_date');
        $this->db->where('(vpb.waktu_kunjungan <= h.end_date OR h.end_date IS NULL)', NULL, FALSE);
        $data_hari_ini = $this->db->get()->row();

        $nilai_rata_rata = 0; $jumlah_sumber_data = 0;
        if ($data_hari_ini && $data_hari_ini->jumlah_data_baru > 0) {
            $nilai_rata_rata = $data_hari_ini->rata_rata_baru;
            $jumlah_sumber_data = $data_hari_ini->jumlah_data_baru;
        } else {
            $harga_kemarin = $this->db->get_where('harga_rata_rata_harian', ['master_harga_id' => $id_harga, 'tanggal' => date('Y-m-d', strtotime('-1 day')), 'jenis_harga' => $jenis_harga])->row();
            $nilai_rata_rata = $harga_kemarin ? $harga_kemarin->nilai_rata_rata : 0;
        }
        
        $data_harian = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tanggal' => $tanggal_hari_ini, 'nilai_rata_rata' => $nilai_rata_rata, 'jumlah_sumber_data' => $jumlah_sumber_data];
        $where = ['master_harga_id' => $id_harga, 'tanggal' => $tanggal_hari_ini, 'jenis_harga' => $jenis_harga];
        if ($this->db->get_where('harga_rata_rata_harian', $where)->num_rows() > 0) {
            $this->db->update('harga_rata_rata_harian', $data_harian, $where);
        } else { $this->db->insert('harga_rata_rata_harian', $data_harian); }
        $this->update_harga($id_harga, ['nilai_harga' => $nilai_rata_rata]);
        return true;
    }
   
    public function proses_rata_rata_harian_bebek_pedaging($id_harga)
    {
        $tanggal_hari_ini = date('Y-m-d');
        $jenis_harga = 'harga_bebek_pedaging';

        $this->db->select('AVG(vpd.pedaging_harga_panen) as rata_rata_baru, COUNT(vpd.pedaging_harga_panen) as jumlah_data_baru');
        $this->db->from('visiting_p_bebek_pedaging vpd');
        $this->db->join('history_user_terpilih h', 'vpd.id_user = h.id_user', 'inner');
        $this->db->where("DATE(vpd.waktu_kunjungan)", $tanggal_hari_ini);
        $this->db->where('h.jenis_harga_key', $jenis_harga);
        $this->db->where('vpd.waktu_kunjungan >= h.start_date');
        $this->db->where('(vpd.waktu_kunjungan <= h.end_date OR h.end_date IS NULL)', NULL, FALSE);
        $data_hari_ini = $this->db->get()->row();

        $nilai_rata_rata = 0; $jumlah_sumber_data = 0;
        if ($data_hari_ini && $data_hari_ini->jumlah_data_baru > 0) {
            $nilai_rata_rata = $data_hari_ini->rata_rata_baru;
            $jumlah_sumber_data = $data_hari_ini->jumlah_data_baru;
        } else {
            $harga_kemarin = $this->db->get_where('harga_rata_rata_harian', ['master_harga_id' => $id_harga, 'tanggal' => date('Y-m-d', strtotime('-1 day')), 'jenis_harga' => $jenis_harga])->row();
            $nilai_rata_rata = $harga_kemarin ? $harga_kemarin->nilai_rata_rata : 0;
        }
        
        $data_harian = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tanggal' => $tanggal_hari_ini, 'nilai_rata_rata' => $nilai_rata_rata, 'jumlah_sumber_data' => $jumlah_sumber_data];
        $where = ['master_harga_id' => $id_harga, 'tanggal' => $tanggal_hari_ini, 'jenis_harga' => $jenis_harga];
        if ($this->db->get_where('harga_rata_rata_harian', $where)->num_rows() > 0) {
            $this->db->update('harga_rata_rata_harian', $data_harian, $where);
        } else { $this->db->insert('harga_rata_rata_harian', $data_harian); }
        $this->update_harga($id_harga, ['nilai_harga' => $nilai_rata_rata]);
        return true;
    }
    
    public function proses_rata_rata_harian_live_bird($id_harga)
    {
        $tanggal_hari_ini = date('Y-m-d');
        $jenis_harga = 'harga_live_bird';

        $this->db->select('AVG(vk.harga_live_bird) as rata_rata_baru, COUNT(vk.harga_live_bird) as jumlah_data_baru');
        $this->db->from('visiting_kemitraan vk');
        $this->db->join('history_user_terpilih h', 'vk.id_user = h.id_user', 'inner');
        $this->db->where("DATE(vk.waktu_kunjungan)", $tanggal_hari_ini);
        $this->db->where('h.jenis_harga_key', $jenis_harga);
        $this->db->where('vk.waktu_kunjungan >= h.start_date');
        $this->db->where('(vk.waktu_kunjungan <= h.end_date OR h.end_date IS NULL)', NULL, FALSE);
        $data_hari_ini = $this->db->get()->row();

        $nilai_rata_rata = 0; $jumlah_sumber_data = 0;
        if ($data_hari_ini && $data_hari_ini->jumlah_data_baru > 0) {
            $nilai_rata_rata = $data_hari_ini->rata_rata_baru;
            $jumlah_sumber_data = $data_hari_ini->jumlah_data_baru;
        } else {
            $harga_kemarin = $this->db->get_where('harga_rata_rata_harian', ['master_harga_id' => $id_harga, 'tanggal' => date('Y-m-d', strtotime('-1 day')), 'jenis_harga' => $jenis_harga])->row();
            $nilai_rata_rata = $harga_kemarin ? $harga_kemarin->nilai_rata_rata : 0;
        }
        
        $data_harian = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tanggal' => $tanggal_hari_ini, 'nilai_rata_rata' => $nilai_rata_rata, 'jumlah_sumber_data' => $jumlah_sumber_data];
        $where = ['master_harga_id' => $id_harga, 'tanggal' => $tanggal_hari_ini, 'jenis_harga' => $jenis_harga];
        if ($this->db->get_where('harga_rata_rata_harian', $where)->num_rows() > 0) {
            $this->db->update('harga_rata_rata_harian', $data_harian, $where);
        } else { $this->db->insert('harga_rata_rata_harian', $data_harian); }
        $this->update_harga($id_harga, ['nilai_harga' => $nilai_rata_rata]);
        return true;
    }
    
    public function hitung_rata_rata_bulanan_telur_puyuh($id_harga, $tahun, $bulan)
    {
        $jenis_harga = 'harga_telur_puyuh';
        $this->db->select('SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang, SUM(jumlah_sumber_data) as total_sumber_data');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('master_harga_id', $id_harga);
        $this->db->where('jenis_harga', $jenis_harga);
        $this->db->where('YEAR(tanggal)', $tahun);
        $this->db->where('MONTH(tanggal)', $bulan);
        $result = $this->db->get()->row();

        $rata_rata = 0; $jumlah_data = 0;
        if ($result && $result->total_sumber_data > 0) {
            $rata_rata = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_data = $result->total_sumber_data;
        }

        $data = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan, 'nilai_rata_rata' => $rata_rata, 'jumlah_sumber_data' => $jumlah_data];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan];
        return $this->db->get_where('harga_rata_rata_bulanan', $where)->num_rows() > 0 ? $this->db->update('harga_rata_rata_bulanan', $data, $where) : $this->db->insert('harga_rata_rata_bulanan', $data);
    }

    public function hitung_rata_rata_tahunan_telur_puyuh($id_harga, $tahun)
    {
        $jenis_harga = 'harga_telur_puyuh';
        $this->db->select('SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang, SUM(jumlah_sumber_data) as total_sumber_data');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where('master_harga_id', $id_harga);
        $this->db->where('jenis_harga', $jenis_harga);
        $this->db->where('tahun', $tahun);
        $result = $this->db->get()->row();

        $rata_rata = 0; $jumlah_data = 0;
        if ($result && $result->total_sumber_data > 0) {
            $rata_rata = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_data = $result->total_sumber_data;
        }
        $data = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'nilai_rata_rata' => $rata_rata, 'jumlah_sumber_data' => $jumlah_data];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun];
        return $this->db->get_where('harga_rata_rata_tahunan', $where)->num_rows() > 0 ? $this->db->update('harga_rata_rata_tahunan', $data, $where) : $this->db->insert('harga_rata_rata_tahunan', $data);
    }
    
    public function hitung_rata_rata_bulanan_telur_bebek($id_harga, $tahun, $bulan)
    {
        $jenis_harga = 'harga_telur_bebek';
        $this->db->select('SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang, SUM(jumlah_sumber_data) as total_sumber_data');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('master_harga_id', $id_harga);
        $this->db->where('jenis_harga', $jenis_harga);
        $this->db->where('YEAR(tanggal)', $tahun);
        $this->db->where('MONTH(tanggal)', $bulan);
        $result = $this->db->get()->row();

        $rata_rata = 0; $jumlah_data = 0;
        if ($result && $result->total_sumber_data > 0) {
            $rata_rata = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_data = $result->total_sumber_data;
        }

        $data = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan, 'nilai_rata_rata' => $rata_rata, 'jumlah_sumber_data' => $jumlah_data];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan];
        return $this->db->get_where('harga_rata_rata_bulanan', $where)->num_rows() > 0 ? $this->db->update('harga_rata_rata_bulanan', $data, $where) : $this->db->insert('harga_rata_rata_bulanan', $data);
    }

    public function hitung_rata_rata_tahunan_telur_bebek($id_harga, $tahun)
    {
        $jenis_harga = 'harga_telur_bebek';
        $this->db->select('SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang, SUM(jumlah_sumber_data) as total_sumber_data');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where('master_harga_id', $id_harga);
        $this->db->where('jenis_harga', $jenis_harga);
        $this->db->where('tahun', $tahun);
        $result = $this->db->get()->row();

        $rata_rata = 0; $jumlah_data = 0;
        if ($result && $result->total_sumber_data > 0) {
            $rata_rata = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_data = $result->total_sumber_data;
        }
        $data = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'nilai_rata_rata' => $rata_rata, 'jumlah_sumber_data' => $jumlah_data];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun];
        return $this->db->get_where('harga_rata_rata_tahunan', $where)->num_rows() > 0 ? $this->db->update('harga_rata_rata_tahunan', $data, $where) : $this->db->insert('harga_rata_rata_tahunan', $data);
    }
    
    public function hitung_rata_rata_bulanan_bebek_pedaging($id_harga, $tahun, $bulan)
    {
        $jenis_harga = 'harga_bebek_pedaging';
        $this->db->select('SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang, SUM(jumlah_sumber_data) as total_sumber_data');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('master_harga_id', $id_harga);
        $this->db->where('jenis_harga', $jenis_harga);
        $this->db->where('YEAR(tanggal)', $tahun);
        $this->db->where('MONTH(tanggal)', $bulan);
        $result = $this->db->get()->row();

        $rata_rata = 0; $jumlah_data = 0;
        if ($result && $result->total_sumber_data > 0) {
            $rata_rata = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_data = $result->total_sumber_data;
        }

        $data = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan, 'nilai_rata_rata' => $rata_rata, 'jumlah_sumber_data' => $jumlah_data];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan];
        return $this->db->get_where('harga_rata_rata_bulanan', $where)->num_rows() > 0 ? $this->db->update('harga_rata_rata_bulanan', $data, $where) : $this->db->insert('harga_rata_rata_bulanan', $data);
    }

    public function hitung_rata_rata_tahunan_bebek_pedaging($id_harga, $tahun)
    {
        $jenis_harga = 'harga_bebek_pedaging';
        $this->db->select('SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang, SUM(jumlah_sumber_data) as total_sumber_data');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where('master_harga_id', $id_harga);
        $this->db->where('jenis_harga', $jenis_harga);
        $this->db->where('tahun', $tahun);
        $result = $this->db->get()->row();

        $rata_rata = 0; $jumlah_data = 0;
        if ($result && $result->total_sumber_data > 0) {
            $rata_rata = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_data = $result->total_sumber_data;
        }
        $data = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'nilai_rata_rata' => $rata_rata, 'jumlah_sumber_data' => $jumlah_data];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun];
        return $this->db->get_where('harga_rata_rata_tahunan', $where)->num_rows() > 0 ? $this->db->update('harga_rata_rata_tahunan', $data, $where) : $this->db->insert('harga_rata_rata_tahunan', $data);
    }
   
    public function hitung_rata_rata_bulanan_live_bird($id_harga, $tahun, $bulan)
    {
        $jenis_harga = 'harga_live_bird';
        $this->db->select('SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang, SUM(jumlah_sumber_data) as total_sumber_data');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('master_harga_id', $id_harga);
        $this->db->where('jenis_harga', $jenis_harga);
        $this->db->where('YEAR(tanggal)', $tahun);
        $this->db->where('MONTH(tanggal)', $bulan);
        $result = $this->db->get()->row();

        $rata_rata = 0; $jumlah_data = 0;
        if ($result && $result->total_sumber_data > 0) {
            $rata_rata = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_data = $result->total_sumber_data;
        }

        $data = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan, 'nilai_rata_rata' => $rata_rata, 'jumlah_sumber_data' => $jumlah_data];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan];
        return $this->db->get_where('harga_rata_rata_bulanan', $where)->num_rows() > 0 ? $this->db->update('harga_rata_rata_bulanan', $data, $where) : $this->db->insert('harga_rata_rata_bulanan', $data);
    }

    public function hitung_rata_rata_tahunan_live_bird($id_harga, $tahun)
    {
        $jenis_harga = 'harga_live_bird';
        $this->db->select('SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang, SUM(jumlah_sumber_data) as total_sumber_data');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where('master_harga_id', $id_harga);
        $this->db->where('jenis_harga', $jenis_harga);
        $this->db->where('tahun', $tahun);
        $result = $this->db->get()->row();

        $rata_rata = 0; $jumlah_data = 0;
        if ($result && $result->total_sumber_data > 0) {
            $rata_rata = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_data = $result->total_sumber_data;
        }
        $data = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'nilai_rata_rata' => $rata_rata, 'jumlah_sumber_data' => $jumlah_data];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun];
        return $this->db->get_where('harga_rata_rata_tahunan', $where)->num_rows() > 0 ? $this->db->update('harga_rata_rata_tahunan', $data, $where) : $this->db->insert('harga_rata_rata_tahunan', $data);
    }

    /**
     * Fungsi helper PRIBADI untuk mengambil nilai rata-rata harian TERBARU
     * dari jenis harga tertentu.
     *
     * @param string $jenis_harga Jenis harga yang ingin dicari.
     * @return float Nilai harga, atau 0 jika tidak ditemukan.
     */
    private function _get_harga_terbaru_harian($jenis_harga)
    {
        $this->db->select('nilai_rata_rata');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', $jenis_harga);
        $this->db->where('tanggal', date('Y-m-d'));
        $result_today = $this->db->get()->row();

        if ($result_today) {
            return $result_today->nilai_rata_rata;
        } else {
            $this->db->select('nilai_rata_rata');
            $this->db->from('harga_rata_rata_harian');
            $this->db->where('jenis_harga', $jenis_harga);
            $this->db->order_by('tanggal', 'DESC');
            $this->db->limit(1);
            $result_last = $this->db->get()->row();
            return $result_last ? $result_last->nilai_rata_rata : 0;
        }
    }
    

    /**
     * [KONSENTRAT LAYER] Menghitung harga pakan jadi berdasarkan rumus komponen.
     *
     * @param int $id_harga ID dari item 'Average Harga Konsentrat Layer' di master_harga.
     * @return bool True jika berhasil.
     */
    public function proses_harga_konsentrat_layer_harian($id_harga)
    {
        $tanggal_hari_ini = date('Y-m-d');
        $jenis_harga = 'harga_konsentrat_layer'; // Jenis harga baru
        $ongkos_mixing = 150;

        $harga_jagung_harian = $this->_get_harga_terbaru_harian('harga_jagung');
        $harga_katul_harian = $this->_get_harga_terbaru_harian('harga_katul');
        
        $this->db->select('nilai_harga');
        $this->db->where('nama_harga', 'Harga Pakan Konsentrat Layer');
        $konsentrat = $this->db->get('master_harga')->row();
        $harga_konsentrat = $konsentrat ? $konsentrat->nilai_harga : 0;

        if ($harga_jagung_harian == 0 || $harga_katul_harian == 0 || $harga_konsentrat == 0) {
            return false; 
        }

        $nilai_akhir = 
            ($harga_jagung_harian * 0.50) +   
            ($harga_katul_harian * 0.15) +    
            ($harga_konsentrat * 0.35) +      
            $ongkos_mixing;

        $data_harian = [
            'master_harga_id' => $id_harga,
            'jenis_harga' => $jenis_harga,
            'tanggal' => $tanggal_hari_ini,
            'nilai_rata_rata' => $nilai_akhir,
            'jumlah_sumber_data' => 1 
        ];
        $where = ['master_harga_id' => $id_harga, 'tanggal' => $tanggal_hari_ini, 'jenis_harga' => $jenis_harga];
        
        if ($this->db->get_where('harga_rata_rata_harian', $where)->num_rows() > 0) {
            $this->db->update('harga_rata_rata_harian', $data_harian, $where);
        } else {
            $this->db->insert('harga_rata_rata_harian', $data_harian);
        }

        $this->update_harga($id_harga, ['nilai_harga' => $nilai_akhir]);

        return true;
    }
    
    public function hitung_rata_rata_bulanan_konsentrat_layer($id_harga, $tahun, $bulan)
    {
        $jenis_harga = 'harga_konsentrat_layer';

        $this->db->select('
            SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang,
            SUM(jumlah_sumber_data) as total_sumber_data
        ');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where(['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga]);
        $this->db->where('YEAR(tanggal)', $tahun);
        $this->db->where('MONTH(tanggal)', $bulan);
        $result = $this->db->get()->row();

        $rata_rata_bulanan = 0;
        $jumlah_sumber_data_bulanan = 0;

        if ($result && $result->total_sumber_data > 0) {
            $rata_rata_bulanan = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_sumber_data_bulanan = $result->total_sumber_data;
        }

        $data_bulanan = [
            'master_harga_id'    => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun,
            'bulan' => $bulan, 'nilai_rata_rata' => $rata_rata_bulanan,
            'jumlah_sumber_data' => $jumlah_sumber_data_bulanan
        ];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan];
        $exists = $this->db->get_where('harga_rata_rata_bulanan', $where)->num_rows() > 0;

        return $exists ? $this->db->update('harga_rata_rata_bulanan', $data_bulanan, $where) : $this->db->insert('harga_rata_rata_bulanan', $data_bulanan);
    }
    
    public function hitung_rata_rata_tahunan_konsentrat_layer($id_harga, $tahun)
    {
        $jenis_harga = 'harga_konsentrat_layer';

        $this->db->select('
            SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang,
            SUM(jumlah_sumber_data) as total_sumber_data
        ');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where(['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun]);
        $result = $this->db->get()->row();

        $rata_rata_tahunan = 0;
        $jumlah_sumber_data_tahunan = 0;

        if ($result && $result->total_sumber_data > 0) {
            $rata_rata_tahunan = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_sumber_data_tahunan = $result->total_sumber_data;
        }

        $data_tahunan = [
            'master_harga_id'    => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun,
            'nilai_rata_rata'    => $rata_rata_tahunan,
            'jumlah_sumber_data' => $jumlah_sumber_data_tahunan
        ];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun];
        $exists = $this->db->get_where('harga_rata_rata_tahunan', $where)->num_rows() > 0;

        return $exists ? $this->db->update('harga_rata_rata_tahunan', $data_tahunan, $where) : $this->db->insert('harga_rata_rata_tahunan', $data_tahunan);
    }

    /**
     * [HPP KONSENTRAT LAYER] Menghitung harga HPP berdasarkan harga konsentrat jadi.
     * Rumus: (Harga Konsentrat Layer Harian) * 3.1
     *
     * @param int $id_harga ID dari item 'Average HPP Konsentrat Layer' di master_harga.
     * @return bool True jika berhasil.
     */
    public function proses_hpp_konsentrat_layer_harian($id_harga)
    {
        $tanggal_hari_ini = date('Y-m-d');
        $jenis_harga = 'hpp_konsentrat_layer';
        
        $harga_konsentrat_harian = $this->_get_harga_terbaru_harian('harga_konsentrat_layer');

        if ($harga_konsentrat_harian == 0) {
            return false; 
        }

        $nilai_akhir = $harga_konsentrat_harian * 3.1;

        $data_harian = [
            'master_harga_id' => $id_harga,
            'jenis_harga' => $jenis_harga,
            'tanggal' => $tanggal_hari_ini,
            'nilai_rata_rata' => $nilai_akhir,
            'jumlah_sumber_data' => 1 
        ];
        $where = ['master_harga_id' => $id_harga, 'tanggal' => $tanggal_hari_ini, 'jenis_harga' => $jenis_harga];
        
        if ($this->db->get_where('harga_rata_rata_harian', $where)->num_rows() > 0) {
            $this->db->update('harga_rata_rata_harian', $data_harian, $where);
        } else {
            $this->db->insert('harga_rata_rata_harian', $data_harian);
        }

        $this->update_harga($id_harga, ['nilai_harga' => $nilai_akhir]);

        return true;
    }
    
    public function hitung_rata_rata_bulanan_hpp_konsentrat_layer($id_harga, $tahun, $bulan)
    {
        $jenis_harga = 'hpp_konsentrat_layer';

        $this->db->select('
            SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang,
            SUM(jumlah_sumber_data) as total_sumber_data
        ');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where(['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga]);
        $this->db->where('YEAR(tanggal)', $tahun);
        $this->db->where('MONTH(tanggal)', $bulan);
        $result = $this->db->get()->row();

        $rata_rata_bulanan = 0;
        $jumlah_sumber_data_bulanan = 0;

        if ($result && $result->total_sumber_data > 0) {
            $rata_rata_bulanan = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_sumber_data_bulanan = $result->total_sumber_data;
        }

        $data_bulanan = [
            'master_harga_id'    => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun,
            'bulan' => $bulan, 'nilai_rata_rata' => $rata_rata_bulanan,
            'jumlah_sumber_data' => $jumlah_sumber_data_bulanan
        ];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan];
        $exists = $this->db->get_where('harga_rata_rata_bulanan', $where)->num_rows() > 0;

        return $exists ? $this->db->update('harga_rata_rata_bulanan', $data_bulanan, $where) : $this->db->insert('harga_rata_rata_bulanan', $data_bulanan);
    }
    
    public function hitung_rata_rata_tahunan_hpp_konsentrat_layer($id_harga, $tahun)
    {
        $jenis_harga = 'hpp_konsentrat_layer';

        $this->db->select('
            SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang,
            SUM(jumlah_sumber_data) as total_sumber_data
        ');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where(['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun]);
        $result = $this->db->get()->row();

        $rata_rata_tahunan = 0;
        $jumlah_sumber_data_tahunan = 0;

        if ($result && $result->total_sumber_data > 0) {
            $rata_rata_tahunan = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_sumber_data_tahunan = $result->total_sumber_data;
        }

        $data_tahunan = [
            'master_harga_id'    => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun,
            'nilai_rata_rata'    => $rata_rata_tahunan,
            'jumlah_sumber_data' => $jumlah_sumber_data_tahunan
        ];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun];
        $exists = $this->db->get_where('harga_rata_rata_tahunan', $where)->num_rows() > 0;

        return $exists ? $this->db->update('harga_rata_rata_tahunan', $data_tahunan, $where) : $this->db->insert('harga_rata_rata_tahunan', $data_tahunan);
    }

    /**
     * [HPP KOMPLIT LAYER] Menghitung harga HPP berdasarkan harga pakan komplit.
     * Rumus: (Harga Pakan Komplit Layer dari master_harga) * 3.1
     *
     * @param int $id_harga ID dari item 'Average HPP Komplit Layer' di master_harga.
     * @return bool True jika berhasil.
     */
    public function proses_hpp_komplit_layer_harian($id_harga)
    {
        $tanggal_hari_ini = date('Y-m-d');
        $jenis_harga = 'hpp_komplit_layer'; 

        $this->db->select('nilai_harga');
        $this->db->where('nama_harga', 'Pakan Komplit Layer');
        $pakan_komplit = $this->db->get('master_harga')->row();
        $harga_pakan_komplit = $pakan_komplit ? $pakan_komplit->nilai_harga : 0;

        if ($harga_pakan_komplit == 0) {
            return false; 
        }

        $nilai_akhir = $harga_pakan_komplit * 3.1;

        $data_harian = [
            'master_harga_id' => $id_harga,
            'jenis_harga' => $jenis_harga,
            'tanggal' => $tanggal_hari_ini,
            'nilai_rata_rata' => $nilai_akhir,
            'jumlah_sumber_data' => 1 
        ];
        $where = ['master_harga_id' => $id_harga, 'tanggal' => $tanggal_hari_ini, 'jenis_harga' => $jenis_harga];
        
        if ($this->db->get_where('harga_rata_rata_harian', $where)->num_rows() > 0) {
            $this->db->update('harga_rata_rata_harian', $data_harian, $where);
        } else {
            $this->db->insert('harga_rata_rata_harian', $data_harian);
        }

        $this->update_harga($id_harga, ['nilai_harga' => $nilai_akhir]);

        return true;
    }
    
    public function hitung_rata_rata_bulanan_hpp_komplit_layer($id_harga, $tahun, $bulan)
    {
        $jenis_harga = 'hpp_komplit_layer';

        $this->db->select('
            SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang,
            SUM(jumlah_sumber_data) as total_sumber_data
        ');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where(['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga]);
        $this->db->where('YEAR(tanggal)', $tahun);
        $this->db->where('MONTH(tanggal)', $bulan);
        $result = $this->db->get()->row();

        $rata_rata_bulanan = 0;
        $jumlah_sumber_data_bulanan = 0;

        if ($result && $result->total_sumber_data > 0) {
            $rata_rata_bulanan = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_sumber_data_bulanan = $result->total_sumber_data;
        }

        $data_bulanan = [
            'master_harga_id'    => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun,
            'bulan' => $bulan, 'nilai_rata_rata' => $rata_rata_bulanan,
            'jumlah_sumber_data' => $jumlah_sumber_data_bulanan
        ];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan];
        $exists = $this->db->get_where('harga_rata_rata_bulanan', $where)->num_rows() > 0;

        return $exists ? $this->db->update('harga_rata_rata_bulanan', $data_bulanan, $where) : $this->db->insert('harga_rata_rata_bulanan', $data_bulanan);
    }
   
    public function hitung_rata_rata_tahunan_hpp_komplit_layer($id_harga, $tahun)
    {
        $jenis_harga = 'hpp_komplit_layer';

        $this->db->select('
            SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang,
            SUM(jumlah_sumber_data) as total_sumber_data
        ');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where(['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun]);
        $result = $this->db->get()->row();

        $rata_rata_tahunan = 0;
        $jumlah_sumber_data_tahunan = 0;

        if ($result && $result->total_sumber_data > 0) {
            $rata_rata_tahunan = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_sumber_data_tahunan = $result->total_sumber_data;
        }

        $data_tahunan = [
            'master_harga_id'    => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun,
            'nilai_rata_rata'    => $rata_rata_tahunan,
            'jumlah_sumber_data' => $jumlah_sumber_data_tahunan
        ];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun];
        $exists = $this->db->get_where('harga_rata_rata_tahunan', $where)->num_rows() > 0;

        return $exists ? $this->db->update('harga_rata_rata_tahunan', $data_tahunan, $where) : $this->db->insert('harga_rata_rata_tahunan', $data_tahunan);
    }
    
    /**
     * [DOC] Memproses dan menyimpan harga harian untuk DOC.
     * Karena harga DOC diinput manual di master_harga, fungsi ini hanya
     * menyalin nilai tersebut ke tabel harian sebagai catatan.
     *
     * @param int $id_harga ID dari item 'Average Harga DOC' di master_harga.
     * @return bool True jika berhasil.
     */
    public function proses_rata_rata_harian_doc($id_harga)
    {
        $tanggal_hari_ini = date('Y-m-d');
        $jenis_harga = 'harga_doc'; 

        $master_item = $this->get_harga_by_id($id_harga);
        if (!$master_item) {
            return false;
        }
        $nilai_harga_manual = $master_item['nilai_harga'];
        
        $data_harian = [
            'master_harga_id'    => $id_harga,
            'jenis_harga'        => $jenis_harga,
            'tanggal'            => $tanggal_hari_ini,
            'nilai_rata_rata'    => $nilai_harga_manual,
            'jumlah_sumber_data' => 1 
        ];

        $where = ['master_harga_id' => $id_harga, 'tanggal' => $tanggal_hari_ini, 'jenis_harga' => $jenis_harga];
        $exists = $this->db->get_where('harga_rata_rata_harian', $where)->num_rows() > 0;

        if ($exists) {
            $this->db->update('harga_rata_rata_harian', $data_harian, $where);
        } else {
            $this->db->insert('harga_rata_rata_harian', $data_harian);
        }
        
        return true;
    }
    
    public function hitung_rata_rata_bulanan_doc($id_harga, $tahun, $bulan)
    {
        $jenis_harga = 'harga_doc';
        $this->db->select('SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang, SUM(jumlah_sumber_data) as total_sumber_data');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where(['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga]);
        $this->db->where('YEAR(tanggal)', $tahun)->where('MONTH(tanggal)', $bulan);
        $result = $this->db->get()->row();

        $rata_rata = 0; $jumlah_data = 0;
        if ($result && $result->total_sumber_data > 0) {
            $rata_rata = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_data = $result->total_sumber_data;
        }

        $data = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan, 'nilai_rata_rata' => $rata_rata, 'jumlah_sumber_data' => $jumlah_data];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan];
        return $this->db->get_where('harga_rata_rata_bulanan', $where)->num_rows() > 0 ? $this->db->update('harga_rata_rata_bulanan', $data, $where) : $this->db->insert('harga_rata_rata_bulanan', $data);
    }
    
    public function hitung_rata_rata_tahunan_doc($id_harga, $tahun)
    {
        $jenis_harga = 'harga_doc';
        $this->db->select('SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang, SUM(jumlah_sumber_data) as total_sumber_data');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where(['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun]);
        $result = $this->db->get()->row();

        $rata_rata = 0; $jumlah_data = 0;
        if ($result && $result->total_sumber_data > 0) {
            $rata_rata = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_data = $result->total_sumber_data;
        }

        $data = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'nilai_rata_rata' => $rata_rata, 'jumlah_sumber_data' => $jumlah_data];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun];
        return $this->db->get_where('harga_rata_rata_tahunan', $where)->num_rows() > 0 ? $this->db->update('harga_rata_rata_tahunan', $data, $where) : $this->db->insert('harga_rata_rata_tahunan', $data);
    }

    /**
     * Fungsi helper PRIBADI untuk mengambil nilai harga dari master_harga berdasarkan NAMA.
     * @param string $nama_harga Nama item harga yang dicari.
     * @return float Nilai harga, atau 0 jika tidak ditemukan.
     */
    private function _get_harga_by_nama($nama_harga)
    {
        $this->db->select('nilai_harga');
        $this->db->from('master_harga');
        $this->db->where('nama_harga', $nama_harga);
        $result = $this->db->get()->row();
        return $result ? (float)$result->nilai_harga : 0;
    }
    
    /**
     * Fungsi helper PRIBADI untuk mengambil nilai rata-rata BULAN SEBELUMNYA
     * dari jenis harga tertentu.
     *
     * @param string $jenis_harga Jenis harga yang ingin dicari (misal: 'harga_doc').
     * @return float Nilai harga, atau 0 jika tidak ditemukan.
     */
    private function _get_harga_bulanan_sebelumnya($jenis_harga)
    {
        $target_date = date('Y-m-d', strtotime('first day of last month'));
        $tahun_lalu = date('Y', strtotime($target_date));
        $bulan_lalu = date('m', strtotime($target_date));

        $this->db->select('nilai_rata_rata');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', $jenis_harga);
        $this->db->where('tahun', $tahun_lalu);
        $this->db->where('bulan', $bulan_lalu);
        $result = $this->db->get()->row();

        return $result ? (float)$result->nilai_rata_rata : 0;
    }
    
    /**
     * [HPP BROILER] Menghitung harga berdasarkan rumus komponen.
     * @param int $id_harga ID dari item 'Average HPP Broiler' di master_harga.
     * @return bool True jika berhasil.
     */
    public function proses_hpp_broiler_harian($id_harga)
    {
        $tanggal_hari_ini = date('Y-m-d');
        $jenis_harga = 'hpp_broiler'; 
        
        $harga_doc_t_minus_1_month = $this->_get_harga_doc_t_minus_1_month();
        
        $cost_komplit_broiler    = $this->_get_harga_terbaru_harian('cost_komplit_broiler');
        $konstanta_tambahan = 35904; // Sesuai rumus Anda: (((650 * 100) / 0.96) + 600 + 3500) / 2
        
        if ($harga_doc_t_minus_1_month == 0 || $cost_komplit_broiler == 0) {
            log_message('error', "Gagal menghitung HPP Broiler: Harga DOC (T-1Bln = $harga_doc_t_minus_1_month) atau Cost Komplit Broiler hari ini ($cost_komplit_broiler) tidak ditemukan (bernilai 0).");
            return false;
        }

        // Kalkulasi diubah ke variabel baru
        // Rumus: (DOC * 96%) + (Cost * 3.21) + 35904
        $nilai_akhir = ($harga_doc_t_minus_1_month * 0.96) + ($cost_komplit_broiler * 1.8) + $konstanta_tambahan;
        // --- [AKHIR PERUBAHAN] ---

        $data_harian = [
            'master_harga_id'    => $id_harga,
            'jenis_harga'        => $jenis_harga,
            'tanggal'            => $tanggal_hari_ini,
            'nilai_rata_rata'    => $nilai_akhir,
            'jumlah_sumber_data' => 1 
        ];

        // Logika UPDATE/INSERT (anti-error 1062)
        $where = ['master_harga_id' => $id_harga, 'tanggal' => $tanggal_hari_ini, 'jenis_harga' => $jenis_harga];
        if ($this->db->get_where('harga_rata_rata_harian', $where)->num_rows() > 0) {
            $this->db->update('harga_rata_rata_harian', $data_harian, $where);
        } else {
            $this->db->insert('harga_rata_rata_harian', $data_harian);
        }

        $this->update_harga($id_harga, ['nilai_harga' => $nilai_akhir]);

        return true;
    }
    
    public function hitung_rata_rata_bulanan_hpp_broiler($id_harga, $tahun, $bulan)
    {
        $jenis_harga = 'hpp_broiler';
        $this->db->select('AVG(nilai_rata_rata) as rata_rata_bulanan, SUM(jumlah_sumber_data) as total_sumber_data');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where(['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga]);
        $this->db->where('YEAR(tanggal)', $tahun)->where('MONTH(tanggal)', $bulan);
        $result = $this->db->get()->row();

        $data_bulanan = [
            'master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan,
            'nilai_rata_rata' => $result->rata_rata_bulanan ?? 0,
            'jumlah_sumber_data' => $result->total_sumber_data ?? 0
        ];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan];
        return $this->db->get_where('harga_rata_rata_bulanan', $where)->num_rows() > 0 ? $this->db->update('harga_rata_rata_bulanan', $data_bulanan, $where) : $this->db->insert('harga_rata_rata_bulanan', $data_bulanan);
    }
    
    public function hitung_rata_rata_tahunan_hpp_broiler($id_harga, $tahun)
    {
        $jenis_harga = 'hpp_broiler';
        $this->db->select('AVG(nilai_rata_rata) as rata_rata_tahunan, SUM(jumlah_sumber_data) as total_sumber_data');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where(['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun]);
        $result = $this->db->get()->row();

        $data_tahunan = [
            'master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun,
            'nilai_rata_rata' => $result->rata_rata_tahunan ?? 0,
            'jumlah_sumber_data' => $result->total_sumber_data ?? 0
        ];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun];
        return $this->db->get_where('harga_rata_rata_tahunan', $where)->num_rows() > 0 ? $this->db->update('harga_rata_rata_tahunan', $data_tahunan, $where) : $this->db->insert('harga_rata_rata_tahunan', $data_tahunan);
    }

    /**
     * [PAKAN KOMPLIT LAYER] Memproses dan menyimpan harga harian.
     * Menyalin nilai dari master_harga ke tabel harian sebagai catatan.
     *
     * @param int $id_harga ID dari item 'Pakan Komplit Layer' di master_harga.
     * @return bool True jika berhasil.
     */
    public function proses_rata_rata_harian_pakan_komplit_layer($id_harga)
    {
        $tanggal_hari_ini = date('Y-m-d');
        $jenis_harga = 'pakan_komplit_layer'; // <-- Jenis harga yang spesifik

        $master_item = $this->get_harga_by_id($id_harga);
        if (!$master_item) {
            return false;
        }
        $nilai_harga_manual = $master_item['nilai_harga'];
        
        $data_harian = [
            'master_harga_id'    => $id_harga,
            'jenis_harga'        => $jenis_harga,
            'tanggal'            => $tanggal_hari_ini,
            'nilai_rata_rata'    => $nilai_harga_manual,
            'jumlah_sumber_data' => 1 
        ];

        $where = ['master_harga_id' => $id_harga, 'tanggal' => $tanggal_hari_ini, 'jenis_harga' => $jenis_harga];
        $exists = $this->db->get_where('harga_rata_rata_harian', $where)->num_rows() > 0;

        if ($exists) {
            $this->db->update('harga_rata_rata_harian', $data_harian, $where);
        } else {
            $this->db->insert('harga_rata_rata_harian', $data_harian);
        }
        
        return true;
    }
    
    public function hitung_rata_rata_bulanan_pakan_komplit_layer($id_harga, $tahun, $bulan)
    {
        $jenis_harga = 'pakan_komplit_layer';
        $this->db->select('SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang, SUM(jumlah_sumber_data) as total_sumber_data');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where(['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga]);
        $this->db->where('YEAR(tanggal)', $tahun)->where('MONTH(tanggal)', $bulan);
        $result = $this->db->get()->row();

        $rata_rata = 0; $jumlah_data = 0;
        if ($result && $result->total_sumber_data > 0) {
            $rata_rata = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_data = $result->total_sumber_data;
        }

        $data = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan, 'nilai_rata_rata' => $rata_rata, 'jumlah_sumber_data' => $jumlah_data];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan];
        return $this->db->get_where('harga_rata_rata_bulanan', $where)->num_rows() > 0 ? $this->db->update('harga_rata_rata_bulanan', $data, $where) : $this->db->insert('harga_rata_rata_bulanan', $data);
    }
    
    /**
     * [GENERIC] Memproses dan menyimpan harga harian untuk item manual.
     * Menyalin nilai dari master_harga ke tabel harian sebagai catatan.
     *
     * @param int $id_harga ID dari item di master_harga.
     * @param string $jenis_harga Kunci 'jenis_harga' yang akan digunakan.
     * @return bool True jika berhasil.
     */
    public function proses_rata_rata_harian_manual($id_harga, $jenis_harga)
    {
        $tanggal_hari_ini = date('Y-m-d');

        $master_item = $this->get_harga_by_id($id_harga);
        if (!$master_item) {
            log_message('error', "proses_rata_rata_harian_manual: Gagal menemukan master_harga dengan ID $id_harga.");
            return false;
        }
        $nilai_harga_manual = $master_item['nilai_harga'];
        
        $data_harian = [
            'master_harga_id'    => $id_harga,
            'jenis_harga'        => $jenis_harga,
            'tanggal'            => $tanggal_hari_ini,
            'nilai_rata_rata'    => $nilai_harga_manual,
            'jumlah_sumber_data' => 1 // Data manual dianggap 1 sumber
        ];

        $where = ['master_harga_id' => $id_harga, 'tanggal' => $tanggal_hari_ini, 'jenis_harga' => $jenis_harga];
        $exists = $this->db->get_where('harga_rata_rata_harian', $where)->num_rows() > 0;

        if ($exists) {
            $this->db->update('harga_rata_rata_harian', $data_harian, $where);
        } else {
            $this->db->insert('harga_rata_rata_harian', $data_harian);
        }
        
        return true;
    }
    
    /**
     * [GENERIC] Menghitung rata-rata bulanan untuk item harga manual/generic.
     */
    public function hitung_rata_rata_bulanan_generic($id_harga, $jenis_harga, $tahun, $bulan)
    {
        $this->db->select('SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang, SUM(jumlah_sumber_data) as total_sumber_data');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where(['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga]);
        $this->db->where('YEAR(tanggal)', $tahun)->where('MONTH(tanggal)', $bulan);
        $result = $this->db->get()->row();

        $rata_rata = 0; $jumlah_data = 0;
        if ($result && $result->total_sumber_data > 0) {
            // Gunakan AVG() jika sumber data selalu 1, atau biarkan tertimbang jika mungkin > 1
            $rata_rata = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_data = $result->total_sumber_data;
        }

        $data = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan, 'nilai_rata_rata' => $rata_rata, 'jumlah_sumber_data' => $jumlah_data];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan];
        return $this->db->get_where('harga_rata_rata_bulanan', $where)->num_rows() > 0 ? $this->db->update('harga_rata_rata_bulanan', $data, $where) : $this->db->insert('harga_rata_rata_bulanan', $data);
    }
    
    /**
     * [BARU] Fungsi helper PRIBADI untuk mengambil harga DOC dari T-1 Bulan (mundur).
     * Sesuai aturan HPP Broiler:
     * 1. Tentukan tanggal 1 bulan lalu (misal: 11 Nov -> 11 Okt).
     * 2. Cari harga PADA atau SEBELUM tanggal tersebut.
     *
     * @return float Nilai harga, atau 0 jika tidak ditemukan.
     */
    private function _get_harga_doc_t_minus_1_month()
    {
        $jenis_harga = 'harga_doc';
        // Tepat 1 bulan lalu. Misal: 11 Nov 2025 -> 11 Okt 2025.
        $target_date = date('Y-m-d', strtotime('-1 month')); 

        // 1. Cari harga PADA ATAU SEBELUM T-1 Bulan (mundur)
        $this->db->select('nilai_rata_rata');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', $jenis_harga);
        $this->db->where('tanggal <=', $target_date); // Pada T-1Bln atau T-1Bln-1d, T-1Bln-2d...
        $this->db->order_by('tanggal', 'DESC');      // Ambil yang paling baru (paling dekat dengan T-1Bln)
        $this->db->limit(1);
        $result_backward = $this->db->get()->row();

        if ($result_backward) {
            return (float)$result_backward->nilai_rata_rata;
        }

        // 2. Fallback: Jika tidak ada data MUNDUR sama sekali (misal: data baru ada T-29 hari)
        $this->db->select('nilai_rata_rata');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', $jenis_harga);
        $this->db->where('tanggal >', $target_date); // Tanggal SETELAH T-1Bln
        $this->db->order_by('tanggal', 'ASC');      // Ambil yang paling dekat
        $this->db->limit(1);
        $result_forward = $this->db->get()->row();

        if ($result_forward) {
            return (float)$result_forward->nilai_rata_rata;
        }
        
        // Jika tidak ada data sama sekali di database
        return 0;
    }
    
        /**
     * Fungsi helper PRIBADI untuk mendapatkan ID dari master_harga berdasarkan nama.
     *
     * @param string $nama_harga Nama item harga yang dicari.
     * @return int|null ID harga jika ditemukan, atau null jika tidak.
     */
    private function _get_id_by_nama_harga($nama_harga)
    {
        $item = $this->db->get_where('master_harga', ['nama_harga' => $nama_harga])->row();
        return $item ? $item->id_harga : null;
    }

    // Di file M_master_harga.php

    public function recalculate_all_prices()
        {
            $tahun_sekarang = date('Y');
            $bulan_sekarang = date('m');

            // ====================================================================
            // BAGIAN 1: PROSES HARGA DATA MENTAH (dari tabel visiting)
            // ====================================================================

            // --- 1. Proses Harga Telur Layer ---
            $id_telur = $this->_get_id_by_nama_harga('Average Harga Telur Layer');
            if ($id_telur) {
                $this->proses_rata_rata_harian_telur_layer($id_telur);
                $this->hitung_rata_rata_bulanan_telur_layer($id_telur, $tahun_sekarang, $bulan_sekarang);
                $this->hitung_rata_rata_tahunan_telur_layer($id_telur, $tahun_sekarang);
            }

            // --- 2. Proses Harga Jagung ---
            $id_jagung = $this->_get_id_by_nama_harga('Average Harga Jagung');
            if ($id_jagung) {
                $this->proses_rata_rata_harian_jagung($id_jagung);
                $this->hitung_rata_rata_bulanan_jagung($id_jagung, $tahun_sekarang, $bulan_sekarang);
                $this->hitung_rata_rata_tahunan_jagung($id_jagung, $tahun_sekarang);
            }

            // --- 3. Proses Harga Katul ---
            $id_katul = $this->_get_id_by_nama_harga('Average Harga Katul');
            if ($id_katul) {
                $this->proses_rata_rata_harian_katul($id_katul);
                $this->hitung_rata_rata_bulanan_katul($id_katul, $tahun_sekarang, $bulan_sekarang);
                $this->hitung_rata_rata_tahunan_katul($id_katul, $tahun_sekarang);
            }

            // --- 4. Proses Harga Afkir ---
            $id_afkir = $this->_get_id_by_nama_harga('Average Harga Afkir'); // Asumsi nama harga
            if ($id_afkir) {
                $this->proses_rata_rata_harian_afkir($id_afkir);
                $this->hitung_rata_rata_bulanan_afkir($id_afkir, $tahun_sekarang, $bulan_sekarang);
                $this->hitung_rata_rata_tahunan_afkir($id_afkir, $tahun_sekarang);
            }

            // --- 5. Proses Harga Telur Puyuh ---
            $id_puyuh = $this->_get_id_by_nama_harga('Average Harga Telur Puyuh'); // Asumsi nama harga
            if ($id_puyuh) {
                $this->proses_rata_rata_harian_telur_puyuh($id_puyuh);
                $this->hitung_rata_rata_bulanan_telur_puyuh($id_puyuh, $tahun_sekarang, $bulan_sekarang);
                $this->hitung_rata_rata_tahunan_telur_puyuh($id_puyuh, $tahun_sekarang);
            }

            // --- 6. Proses Harga Telur Bebek ---
            $id_bebek = $this->_get_id_by_nama_harga('Average Harga Telur Bebek'); // Asumsi nama harga
            if ($id_bebek) {
                $this->proses_rata_rata_harian_telur_bebek($id_bebek);
                $this->hitung_rata_rata_bulanan_telur_bebek($id_bebek, $tahun_sekarang, $bulan_sekarang);
                $this->hitung_rata_rata_tahunan_telur_bebek($id_bebek, $tahun_sekarang);
            }

            // --- 7. Proses Harga Bebek Pedaging ---
            $id_bebek_pedaging = $this->_get_id_by_nama_harga('Average Harga Bebek Pedaging'); // Asumsi nama harga
            if ($id_bebek_pedaging) {
                $this->proses_rata_rata_harian_bebek_pedaging($id_bebek_pedaging);
                $this->hitung_rata_rata_bulanan_bebek_pedaging($id_bebek_pedaging, $tahun_sekarang, $bulan_sekarang);
                $this->hitung_rata_rata_tahunan_bebek_pedaging($id_bebek_pedaging, $tahun_sekarang);
            }

            // --- 8. Proses Harga Live Bird ---
            $id_lb = $this->_get_id_by_nama_harga('Average Harga Live Bird'); // Asumsi nama harga
            if ($id_lb) {
                $this->proses_rata_rata_harian_live_bird($id_lb);
                $this->hitung_rata_rata_bulanan_live_bird($id_lb, $tahun_sekarang, $bulan_sekarang);
                $this->hitung_rata_rata_tahunan_live_bird($id_lb, $tahun_sekarang);
            }

            // ====================================================================
            // BAGIAN 2: PROSES HARGA INPUT MANUAL (Disalin ke tabel harian)
            // ====================================================================
            // Catatan: Harga manual lain (Pre Stater, Stater, Finisher, Ongkir, Konsentrat)
            // TIDAK memiliki fungsi proses harian di model Anda, 
            // sehingga tidak dapat dimasukkan di sini.

            // --- 9. Proses Harga DOC (Manual) ---
            $id_doc = $this->_get_id_by_nama_harga('Average Harga DOC'); // Asumsi nama harga
            if ($id_doc) {
                $this->proses_rata_rata_harian_doc($id_doc);
                $this->hitung_rata_rata_bulanan_doc($id_doc, $tahun_sekarang, $bulan_sekarang);
                $this->hitung_rata_rata_tahunan_doc($id_doc, $tahun_sekarang);
            }

            // --- 10. Proses Pakan Komplit Layer (Manual) ---
            $id_pakan_komplit = $this->_get_id_by_nama_harga('Pakan Komplit Layer'); // Sesuai logika di HPP
            if ($id_pakan_komplit) {
                $this->proses_rata_rata_harian_pakan_komplit_layer($id_pakan_komplit);
                $this->hitung_rata_rata_bulanan_pakan_komplit_layer($id_pakan_komplit, $tahun_sekarang, $bulan_sekarang);
                // Fungsi hitung_rata_rata_tahunan_pakan_komplit_layer() TIDAK ADA di model Anda.
                // Jika Anda ingin menambahkannya, Anda bisa memanggil:
                // $this->hitung_rata_rata_tahunan_generic($id_pakan_komplit, 'pakan_komplit_layer', $tahun_sekarang);
                // (Namun fungsi 'hitung_rata_rata_tahunan_generic' juga tidak ada, hanya ada bulanan_generic)
            }

            // --- 11. Proses Cost Komplit Broiler (Manual) ---
            $id_cost_broiler = $this->_get_id_by_nama_harga('Average Cost Komplit Broiler');
            if ($id_cost_broiler) {
                $this->proses_rata_rata_harian_cost_komplit_broiler($id_cost_broiler);
                $this->hitung_rata_rata_bulanan_cost_komplit_broiler($id_cost_broiler, $tahun_sekarang, $bulan_sekarang);
                $this->hitung_rata_rata_tahunan_cost_komplit_broiler($id_cost_broiler, $tahun_sekarang);
            }

            // ====================================================================
            // BAGIAN 3: PROSES HARGA TURUNAN (Tergantung Bagian 1 & 2)
            // ====================================================================
            // Penting: Harga turunan harus dijalankan SETELAH komponennya diproses.

            // --- 12. Proses Harga Konsentrat Layer (Turunan) ---
            $id_konsentrat = $this->_get_id_by_nama_harga('Average Harga Konsentrat Layer');
            if ($id_konsentrat) {
                // Bergantung pada: Jagung (OK), Katul (OK), dan Manual 'Harga Pakan Konsentrat Layer'
                $this->proses_harga_konsentrat_layer_harian($id_konsentrat);
                $this->hitung_rata_rata_bulanan_konsentrat_layer($id_konsentrat, $tahun_sekarang, $bulan_sekarang);
                $this->hitung_rata_rata_tahunan_konsentrat_layer($id_konsentrat, $tahun_sekarang);
            }
            
            // --- 13. Proses HPP Konsentrat Layer (Turunan) ---
            $id_hpp_konsentrat = $this->_get_id_by_nama_harga('Average HPP Konsentrat Layer');
            if ($id_hpp_konsentrat) {
                // Bergantung pada: Harga Konsentrat Layer (Blok 11)
                $this->proses_hpp_konsentrat_layer_harian($id_hpp_konsentrat);
                $this->hitung_rata_rata_bulanan_hpp_konsentrat_layer($id_hpp_konsentrat, $tahun_sekarang, $bulan_sekarang);
                $this->hitung_rata_rata_tahunan_hpp_konsentrat_layer($id_hpp_konsentrat, $tahun_sekarang);
            }

            // --- 14. Proses HPP Komplit Layer (Turunan) ---
            $id_hpp_komplit = $this->_get_id_by_nama_harga('Average HPP Komplit Layer');
            if ($id_hpp_komplit) {
                // Bergantung pada: Manual 'Pakan Komplit Layer' (Blok 10)
                $this->proses_hpp_komplit_layer_harian($id_hpp_komplit);
                $this->hitung_rata_rata_bulanan_hpp_komplit_layer($id_hpp_komplit, $tahun_sekarang, $bulan_sekarang);
                $this->hitung_rata_rata_tahunan_hpp_komplit_layer($id_hpp_komplit, $tahun_sekarang);
            }

            // --- 15. Proses HPP Broiler (Turunan) ---
            $id_hpp_broiler = $this->_get_id_by_nama_harga('Average HPP Broiler');
            if ($id_hpp_broiler) {
                // Bergantung pada: Harga DOC (Blok 9) dan Cost Komplit Broiler (Blok 12)
                $this->proses_hpp_broiler_harian($id_hpp_broiler);
                $this->hitung_rata_rata_bulanan_hpp_broiler($id_hpp_broiler, $tahun_sekarang, $bulan_sekarang);
                $this->hitung_rata_rata_tahunan_hpp_broiler($id_hpp_broiler, $tahun_sekarang);
            }


            // PENTING: Jangan set_flashdata atau redirect dari dalam Model.
            // Cukup kembalikan status, atau biarkan 'Exception' jika gagal.
            return true; 
        }

        /**
     * [COST KOMPLIT BROILER] Memproses dan menyimpan harga harian untuk Cost Komplit Broiler.
     * Karena harga diinput manual di master_harga, fungsi ini hanya
     * menyalin nilai tersebut ke tabel harian sebagai catatan.
     *
     * @param int $id_harga ID dari item 'Average Cost Komplit Broiler' di master_harga.
     * @return bool True jika berhasil.
     */
    public function proses_rata_rata_harian_cost_komplit_broiler($id_harga)
    {
        $tanggal_hari_ini = date('Y-m-d');
        $jenis_harga = 'cost_komplit_broiler'; 

        $master_item = $this->get_harga_by_id($id_harga);
        if (!$master_item) {
            return false;
        }
        $nilai_harga_manual = $master_item['nilai_harga'];
        
        $data_harian = [
            'master_harga_id'    => $id_harga,
            'jenis_harga'        => $jenis_harga,
            'tanggal'            => $tanggal_hari_ini,
            'nilai_rata_rata'    => $nilai_harga_manual,
            'jumlah_sumber_data' => 1 
        ];

        $where = ['master_harga_id' => $id_harga, 'tanggal' => $tanggal_hari_ini, 'jenis_harga' => $jenis_harga];
        $exists = $this->db->get_where('harga_rata_rata_harian', $where)->num_rows() > 0;

        if ($exists) {
            $this->db->update('harga_rata_rata_harian', $data_harian, $where);
        } else {
            $this->db->insert('harga_rata_rata_harian', $data_harian);
        }
        
        return true;
    }

    public function hitung_rata_rata_bulanan_cost_komplit_broiler($id_harga, $tahun, $bulan)
    {
        $jenis_harga = 'cost_komplit_broiler';
        $this->db->select('SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang, SUM(jumlah_sumber_data) as total_sumber_data');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where(['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga]);
        $this->db->where('YEAR(tanggal)', $tahun)->where('MONTH(tanggal)', $bulan);
        $result = $this->db->get()->row();

        $rata_rata = 0; $jumlah_data = 0;
        if ($result && $result->total_sumber_data > 0) {
            $rata_rata = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_data = $result->total_sumber_data;
        }

        $data = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan, 'nilai_rata_rata' => $rata_rata, 'jumlah_sumber_data' => $jumlah_data];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'bulan' => $bulan];
        return $this->db->get_where('harga_rata_rata_bulanan', $where)->num_rows() > 0 ? $this->db->update('harga_rata_rata_bulanan', $data, $where) : $this->db->insert('harga_rata_rata_bulanan', $data);
    }

    public function hitung_rata_rata_tahunan_cost_komplit_broiler($id_harga, $tahun)
    {
        $jenis_harga = 'cost_komplit_broiler';
        $this->db->select('SUM(nilai_rata_rata * jumlah_sumber_data) as total_nilai_tertimbang, SUM(jumlah_sumber_data) as total_sumber_data');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where(['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun]);
        $result = $this->db->get()->row();

        $rata_rata = 0; $jumlah_data = 0;
        if ($result && $result->total_sumber_data > 0) {
            $rata_rata = $result->total_nilai_tertimbang / $result->total_sumber_data;
            $jumlah_data = $result->total_sumber_data;
        }

        $data = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun, 'nilai_rata_rata' => $rata_rata, 'jumlah_sumber_data' => $jumlah_data];
        $where = ['master_harga_id' => $id_harga, 'jenis_harga' => $jenis_harga, 'tahun' => $tahun];
        return $this->db->get_where('harga_rata_rata_tahunan', $where)->num_rows() > 0 ? $this->db->update('harga_rata_rata_tahunan', $data, $where) : $this->db->insert('harga_rata_rata_tahunan', $data);
    }
}
