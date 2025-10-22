<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_Visual extends CI_Model {
    
    private $table_mapping = [
        'Kantor' => 'visiting_kantor',
        'Agen' => 'visiting_agen',
        'Kemitraan' => 'visiting_kemitraan',
        'Sub Agen' => 'visiting_subagen',
        'Koordinasi' => 'visiting_koordinasi',
        'Grower' => 'visiting_p_grower',
        'Bebek Pedaging' => 'visiting_p_bebek_pedaging',
        'Layer' => 'visiting_p_layer',
        'Bebek Petelur' => 'visiting_p_bebek_petelur',
        'Puyuh' => 'visiting_p_puyuh',
        'Arap' => 'visiting_p_arap',
        'Lainnya' => 'visiting_p_lainnya'
    ];

    public function __construct() {
        parent::__construct();
    }

    // --- DIPERBARUI ---
    public function get_surveyor_performance($month, $year, $user_id = null, $area_id = null) {
    // --- AKHIR DIPERBARUI ---
        $month = (int)$month;
        $year = (int)$year;
        $final_query = '';

        // --- DIPERBARUI: Teruskan SEMUA filter ke fungsi sub-query ---
        $union_query = $this->_build_union_query_for_aktual($month, $year, $user_id, $area_id);
        
        // --- DIPERBARUI: Logika filter utama untuk surveyor vs koordinator ---
        $main_user_filter = "";
        if ($user_id !== null) {
            // Surveyor HANYA melihat dirinya sendiri
            $main_user_filter = "WHERE u.id_user = {$user_id}";
        } elseif ($area_id !== null) {
            // Koordinator melihat SEMUA user di areanya
            $main_user_filter = "WHERE u.master_area_id = {$area_id}";
        }
        // Admin (kedua filter null) melihat semua user
        // --- AKHIR DIPERBARUI ---

        if ($month != 0 && $year != 0) {
            $first_day_of_month = "'{$year}-{$month}-01'";
            $last_day_of_month = "LAST_DAY({$first_day_of_month})";
            $target_date_condition = "ht.start_date <= {$last_day_of_month} AND ht.end_date >= {$first_day_of_month}";

            $final_query = "
                SELECT 
                    u.username AS surveyor_name,
                    COALESCE(ht.target, 0) AS target,
                    COALESCE(visit_counts.aktual, 0) AS aktual,
                    CASE WHEN COALESCE(ht.target, 0) > 0 THEN (COALESCE(visit_counts.aktual, 0) / ht.target * 100) ELSE 0 END AS achievement_percent
                FROM z_master_user u
                LEFT JOIN (SELECT id_user, COUNT(*) as aktual FROM ({$union_query}) as all_visits GROUP BY id_user) as visit_counts ON u.id_user = visit_counts.id_user
                LEFT JOIN history_target ht ON u.id_user = ht.id_user AND {$target_date_condition}
                {$main_user_filter} /* <-- Filter BARU diterapkan di sini */
                ORDER BY achievement_percent DESC, aktual DESC;
            ";
        } else {
            $target_year_filter = ($year != 0) ? "WHERE YEAR(start_date) <= {$year} AND YEAR(end_date) >= {$year}" : "";
            $final_query = "
                SELECT 
                    u.username AS surveyor_name,
                    COALESCE(target_sums.total_target, 0) AS target,
                    COALESCE(visit_counts.aktual, 0) AS aktual,
                    CASE WHEN COALESCE(target_sums.total_target, 0) > 0 THEN (COALESCE(visit_counts.aktual, 0) / target_sums.total_target * 100) ELSE 0 END AS achievement_percent
                FROM z_master_user u
                LEFT JOIN (SELECT id_user, COUNT(*) as aktual FROM ({$union_query}) as all_visits GROUP BY id_user) as visit_counts ON u.id_user = visit_counts.id_user
                LEFT JOIN (SELECT id_user, SUM(target) as total_target FROM history_target {$target_year_filter} GROUP BY id_user) as target_sums ON u.id_user = target_sums.id_user
                {$main_user_filter} /* <-- Filter BARU diterapkan di sini juga */
                ORDER BY achievement_percent DESC, aktual DESC;
            ";
        }
        
        return $this->db->query($final_query)->result_array();
    }

    public function get_area_performance($month, $year, $user = null) {
        $month = (int)$month;
        $year = (int)$year;
        $final_query = '';
        
        $area_filter_sql = '';
        
        // --- DIPERBARUI: Tambahkan 'koordinator' ke kondisi IF ---
        if ($user && isset($user['group_user']) && ($user['group_user'] === 'surveyor' || $user['group_user'] === 'koordinator')) {
        // --- AKHIR DIPERBARUI ---
            if (isset($user['master_area_id'])) {
                // Logika ini sudah benar untuk surveyor DAN koordinator
                $area_filter_sql = "WHERE ma.master_area_id = " . $this->db->escape($user['master_area_id']);
            }
        }

        // --- PENTING: _build_union_query_for_aktual di sini HARUS diubah ---
        // Panggilan ini HARUS meneruskan filter area dari $user
        // Jika tidak, 'aktual' akan menghitung data dari SEMUA area
        
        $user_id_filter = null;
        $area_id_filter = null;
        if ($user && isset($user['group_user'])) {
            if ($user['group_user'] === 'surveyor') {
                $user_id_filter = $user['id_user'];
            } elseif ($user['group_user'] === 'koordinator') {
                $area_id_filter = $user['master_area_id'];
            }
        }
        
        // --- DIPERBARUI: Panggil dengan filter yang benar ---
        $union_query = $this->_build_union_query_for_aktual($month, $year, $user_id_filter, $area_id_filter);
        // --- AKHIR DIPERBARUI ---
        
        if ($month != 0 && $year != 0) {
            // ... (sisa query Anda tidak perlu diubah) ...
            $first_day_of_month = "'{$year}-{$month}-01'";
            $last_day_of_month = "LAST_DAY({$first_day_of_month})";
            $target_date_condition = "mt.start_date <= {$last_day_of_month} AND mt.end_date >= {$first_day_of_month}";

            $final_query = "
                SELECT ma.nama_area, SUM(user_performance.target) AS total_target, SUM(user_performance.aktual) AS total_aktual,
                    CASE WHEN SUM(user_performance.target) > 0 THEN (SUM(user_performance.aktual) / SUM(user_performance.target) * 100) ELSE 0 END AS achievement_percent
                FROM master_area ma
                LEFT JOIN (
                    SELECT u.master_area_id, COALESCE(mt.target, 0) AS target, COALESCE(visit_counts.aktual, 0) AS aktual
                    FROM z_master_user u
                    LEFT JOIN (SELECT id_user, COUNT(*) as aktual FROM ({$union_query}) as all_visits GROUP BY id_user) as visit_counts ON u.id_user = visit_counts.id_user
                    LEFT JOIN history_target mt ON u.id_user = mt.id_user AND {$target_date_condition}
                ) AS user_performance ON ma.master_area_id = user_performance.master_area_id
                {$area_filter_sql} /* <-- FILTER TAMPILAN DITERAPKAN DI SINI */
                GROUP BY ma.master_area_id, ma.nama_area ORDER BY ma.nama_area ASC;
            ";
        } else {
            // ... (sisa query Anda tidak perlu diubah) ...
            $target_year_filter = ($year != 0) ? "WHERE YEAR(start_date) <= {$year} AND YEAR(end_date) >= {$year}" : "";
            $final_query = "
                SELECT ma.nama_area, SUM(user_performance.target) AS total_target, SUM(user_performance.aktual) AS total_aktual,
                    CASE WHEN SUM(user_performance.target) > 0 THEN (SUM(user_performance.aktual) / SUM(user_performance.target) * 100) ELSE 0 END AS achievement_percent
                FROM master_area ma
                LEFT JOIN (
                    SELECT u.master_area_id, COALESCE(target_sums.total_target, 0) AS target, COALESCE(visit_counts.aktual, 0) AS aktual
                    FROM z_master_user u
                    LEFT JOIN (SELECT id_user, COUNT(*) as aktual FROM ({$union_query}) as all_visits GROUP BY id_user) as visit_counts ON u.id_user = visit_counts.id_user
                    LEFT JOIN (SELECT id_user, SUM(target) as total_target FROM history_target {$target_year_filter} GROUP BY id_user) as target_sums ON u.id_user = target_sums.id_user
                ) AS user_performance ON ma.master_area_id = user_performance.master_area_id
                {$area_filter_sql} /* <-- FILTER TAMPILAN DITERAPKAN DI SINI JUGA */
                GROUP BY ma.master_area_id, ma.nama_area ORDER BY ma.nama_area ASC;
            ";
        }

        return $this->db->query($final_query)->result_array();
    }

    // --- DIPERBARUI: Tambahkan $area_id ---
    public function get_visit_breakdown($month, $year, $user_id = null, $area_id = null) {
        $month = (int)$month;
        $year = (int)$year;
        $sub_queries = [];
        $where_clause = '';

        // --- DIPERBARUI: Gunakan alias 't' untuk tabel visit ---
        if ($month != 0 && $year != 0) {
            $where_clause = "WHERE MONTH(t.waktu_kunjungan) = {$month} AND YEAR(t.waktu_kunjungan) = {$year}";
        }
        
        // --- DIPERBARUI: Gunakan alias 't' ---
        $user_filter_sql = '';
        if ($user_id !== null) {
            $user_filter_sql = $where_clause ? " AND t.id_user = {$user_id}" : "WHERE t.id_user = {$user_id}";
        }
        
        // --- BARU: Tambahkan filter area_id menggunakan alias 'u' ---
        $area_filter_sql = '';
        if ($area_id !== null) {
            // Filter ini hanya aktif jika filter user_id TIDAK aktif
            $area_filter_sql = ($where_clause || $user_filter_sql) ? " AND u.master_area_id = {$area_id}" : "WHERE u.master_area_id = {$area_id}";
        }
        // --- AKHIR BARU ---
        
        foreach ($this->table_mapping as $kategori => $nama_tabel) {
            
            // --- DIPERBARUI: Tambahkan JOIN dan semua filter ---
            $sub_queries[] = "
                SELECT '{$kategori}' as kategori 
                FROM {$nama_tabel} t
                LEFT JOIN z_master_user u ON t.id_user = u.id_user
                {$where_clause} {$user_filter_sql} {$area_filter_sql}
            ";
            // --- AKHIR DIPERBARUI ---
        }
        
        $union_query = implode(' UNION ALL ', $sub_queries);

        $final_query = "
            SELECT 
                kategori,
                COUNT(*) AS jumlah_visit
            FROM ({$union_query}) as semua_visit
            WHERE kategori IS NOT NULL AND kategori != ''
            GROUP BY kategori
            ORDER BY jumlah_visit DESC
        ";

        return $this->db->query($final_query)->result_array();
    }

    public function get_kasus_breakdown_stacked($year, $user_id = null) {
        $year = (int)$year;
        $peternakan_tables = ['visiting_p_grower', 'visiting_p_bebek_pedaging', 'visiting_p_layer', 'visiting_p_bebek_petelur', 'visiting_p_puyuh', 'visiting_p_arap', 'visiting_p_lainnya'];
        $sub_queries = [];
        
        $year_filter_sql = ($year != 0) ? "AND YEAR(waktu_kunjungan) = {$year}" : "";
        
        $user_filter_sql = ($user_id !== null) ? "AND id_user = {$user_id}" : "";

        foreach ($peternakan_tables as $table) {
            $sub_queries[] = "
                SELECT 
                    DATE_FORMAT(waktu_kunjungan, '%b %Y') as bulan_tahun,
                    CASE WHEN INSTR(jenis_kasus, ':') > 0 THEN SUBSTRING_INDEX(jenis_kasus, ':', 1) ELSE jenis_kasus END as kategori_kasus
                FROM {$table}
                WHERE jenis_kasus IS NOT NULL AND jenis_kasus != '-' {$year_filter_sql} {$user_filter_sql}
            ";
        }
        $union_query = implode(' UNION ALL ', $sub_queries);

        $final_query = "
            SELECT bulan_tahun, kategori_kasus, COUNT(*) as jumlah
            FROM ({$union_query}) as semua_kasus
            WHERE kategori_kasus IS NOT NULL AND kategori_kasus != '' AND bulan_tahun IS NOT NULL
            GROUP BY bulan_tahun, kategori_kasus
            ORDER BY STR_TO_DATE(CONCAT('01 ', bulan_tahun), '%d %b %Y'), kategori_kasus
        ";
        
        return $this->db->query($final_query)->result_array();
    }

    public function get_kasus_pivot_by_area($year, $user_id = null) {
        $year = (int)$year;
        $peternakan_tables = ['visiting_p_grower', 'visiting_p_bebek_pedaging', 'visiting_p_layer', 'visiting_p_bebek_petelur', 'visiting_p_puyuh', 'visiting_p_arap', 'visiting_p_lainnya'];
        $sub_queries = [];
        
        $year_filter_sql = ($year != 0) ? "AND YEAR(t.waktu_kunjungan) = {$year}" : "";
        
        $user_filter_sql = ($user_id !== null) ? "AND t.id_user = {$user_id}" : "";

        foreach ($peternakan_tables as $table) {
            $sub_queries[] = "
                SELECT t.id_user, CASE WHEN INSTR(t.jenis_kasus, ':') > 0 THEN SUBSTRING_INDEX(t.jenis_kasus, ':', 1) ELSE t.jenis_kasus END as kategori_kasus
                FROM {$table} t
                WHERE t.jenis_kasus IS NOT NULL AND t.jenis_kasus != '-' {$year_filter_sql} {$user_filter_sql}
            ";
        }
        $union_query = implode(' UNION ALL ', $sub_queries);

        $final_query = "
            SELECT ma.nama_area, semua_kasus.kategori_kasus, COUNT(*) as jumlah
            FROM ({$union_query}) as semua_kasus
            JOIN z_master_user u ON semua_kasus.id_user = u.id_user
            JOIN master_area ma ON u.master_area_id = ma.master_area_id
            WHERE semua_kasus.kategori_kasus IS NOT NULL AND semua_kasus.kategori_kasus != ''
            GROUP BY ma.nama_area, semua_kasus.kategori_kasus
            ORDER BY ma.nama_area, semua_kasus.kategori_kasus;
        ";
        
        return $this->db->query($final_query)->result_array();
    }

    // --- DIPERBARUI: Tambahkan $area_id ---
    public function get_all_visit_details($month, $year, $user_id = null, $area_id = null) {
        $month = (int)$month;
        $year = (int)$year;
        
        // --- Filter Logic (Start) ---
        $where_clause = '';
        if ($month != 0 && $year != 0) {
            // Asumsi: Semua tabel PASTI punya 'waktu_kunjungan'
            $where_clause = "WHERE MONTH(t.waktu_kunjungan) = {$month} AND YEAR(t.waktu_kunjungan) = {$year}";
        }
        
        $user_filter_sql = '';
        if ($user_id !== null) {
            // Asumsi: Semua tabel PASTI punya 'id_user'
            $user_filter_sql = $where_clause ? " AND t.id_user = {$user_id}" : "WHERE t.id_user = {$user_id}";
        }

        // --- BARU: Tambahkan filter area_id menggunakan alias 'u' ---
        $area_filter_sql = '';
        if ($area_id !== null) {
            // Filter ini hanya aktif jika filter user_id TIDAK aktif
            $area_filter_sql = ($where_clause || $user_filter_sql) ? " AND u.master_area_id = {$area_id}" : "WHERE u.master_area_id = {$area_id}";
        }
        // --- AKHIR BARU ---
        
        // --- Filter Logic (End) ---
        
        $sub_queries = [];
        $db_name = $this->db->database; // Mendapatkan nama database Anda saat ini

        // Kolom yang kita inginkan, yang MUNGKIN tidak ada di semua tabel
        $desired_columns = [
            'tujuan_kunjungan', 
            'jenis_kasus', 
            'nama_farm',
            'latitude', 
            'longitude', 
            'location_address'
        ];
        
        // Loop melalui mapping tabel Anda
        foreach ($this->table_mapping as $kategori => $nama_tabel) {
            
            // 1. Cek kolom apa saja yang BENAR-BENAR ada di tabel ini
            // Query ini memeriksa kamus data (information_schema)
            $cols_query = $this->db->query("
                SELECT COLUMN_NAME 
                FROM information_schema.COLUMNS 
                WHERE TABLE_SCHEMA = '{$db_name}' AND TABLE_NAME = '{$nama_tabel}'
            ");
            $existing_columns = array_column($cols_query->result_array(), 'COLUMN_NAME');

            // 2. Bangun bagian SELECT
            $select_parts = [];
            
            // Kolom wajib (HARUS ada di semua tabel)
            $select_parts[] = "u.username";
            $select_parts[] = "'{$kategori}' as kategori_visit";
            $select_parts[] = "t.waktu_kunjungan"; // Wajib untuk filter & sorting

            // 3. Loop kolom yang kita inginkan (yang mungkin tidak ada)
            foreach ($desired_columns as $col) {
                if (in_array($col, $existing_columns)) {
                    // Jika kolomnya ADA, pilih datanya
                    $select_parts[] = "t.{$col} as {$col}";
                } else {
                    // Jika kolomnya TIDAK ADA, isi dengan NULL
                    $select_parts[] = "NULL as {$col}";
                }
            }

            $select_string = implode(', ', $select_parts);

            // 4. Bangun sub-query untuk tabel ini
            // --- DIPERBARUI: Tambahkan $area_filter_sql ---
            $sub_queries[] = "
                SELECT {$select_string}
                FROM {$nama_tabel} t
                LEFT JOIN z_master_user u ON t.id_user = u.id_user
                {$where_clause} {$user_filter_sql} {$area_filter_sql}
            ";
            // --- AKHIR DIPERBARUI ---
        }
        
        // Gabungkan semua query menjadi satu
        $union_query = implode(' UNION ALL ', $sub_queries);

        // Buat query final untuk mengurutkan semua hasil gabungan
        $final_query = "
            SELECT * FROM ({$union_query}) as semua_visit
            ORDER BY waktu_kunjungan DESC
        ";
        
        return $this->db->query($final_query)->result_array();
    }

    public function get_kasus_detail_list($year, $user_id = null) {
        $year = (int)$year;
        $peternakan_tables = [
            'visiting_p_grower', 'visiting_p_bebek_pedaging', 'visiting_p_layer',
            'visiting_p_bebek_petelur', 'visiting_p_puyuh', 'visiting_p_arap', 'visiting_p_lainnya'
        ];
        $sub_queries = [];
        
        $year_filter_sql = ($year != 0) ? "AND YEAR(waktu_kunjungan) = {$year}" : "";
        
        $user_filter_sql = ($user_id !== null) ? "AND id_user = {$user_id}" : "";

        foreach ($peternakan_tables as $table) {
            $farm_column_selection = 'nama_farm';
            if ($table === 'visiting_p_layer') {
                $farm_column_selection = 'layer_nama_farm AS nama_farm';
            }

            $sub_queries[] = "
                SELECT 
                    waktu_kunjungan,
                    {$farm_column_selection},
                    jenis_kasus
                FROM {$table}
                WHERE 
                    jenis_kasus IS NOT NULL 
                    AND jenis_kasus != '-'
                    {$year_filter_sql}
                    {$user_filter_sql}
            ";
        }
        
        $union_query = implode(' UNION ALL ', $sub_queries);

        $final_query = "
            SELECT * FROM ({$union_query}) as semua_kasus
            ORDER BY waktu_kunjungan DESC
        ";
        
        return $this->db->query($final_query)->result_array();
    }
    
    private function _build_union_query_for_aktual($month, $year, $user_id = null) {
        $sub_queries = [];
        
        $date_filter_sql = '';
        $date_filter_created_at = '';
        if ($month != 0 && $year != 0) {
            $date_filter_sql = "AND MONTH(waktu_kunjungan) = {$month} AND YEAR(waktu_kunjungan) = {$year}";
            $date_filter_created_at = "WHERE MONTH(created_at) = {$month} AND YEAR(created_at) = {$year}";
        } elseif ($month == 0 && $year != 0) {
            $date_filter_sql = "AND YEAR(waktu_kunjungan) = {$year}";
            $date_filter_created_at = "WHERE YEAR(created_at) = {$year}";
        }
        
        $user_filter_sql = ($user_id !== null) ? "AND id_user = {$user_id}" : "";
        $user_filter_created_at = ($user_id !== null) ? ($date_filter_created_at ? " AND id_user = {$user_id}" : "WHERE id_user = {$user_id}") : "";

        foreach ($this->table_mapping as $table) {
            $sub_queries[] = "SELECT id_user FROM {$table} WHERE id_user IS NOT NULL AND id_user != 0 {$date_filter_sql} {$user_filter_sql}";
        }
        
        $sub_queries[] = "SELECT id_user FROM seminar WHERE id_user IS NOT NULL AND id_user != 0 {$date_filter_sql} {$user_filter_sql}";
        $sub_queries[] = "SELECT id_user FROM sample_form WHERE id_user IS NOT NULL AND id_user != 0 {$date_filter_sql} {$user_filter_sql}";
        $sub_queries[] = "SELECT id_user FROM master_farm {$date_filter_created_at} {$user_filter_created_at}";
        $sub_queries[] = "SELECT id_user FROM master_subagen {$date_filter_created_at} {$user_filter_created_at}";
        $sub_queries[] = "SELECT id_user FROM master_kemitraan {$date_filter_created_at} {$user_filter_created_at}";
        
        return implode(' UNION ALL ', $sub_queries);
    }

    public function get_seminar_count_by_month($month, $year, $user_id = null) {
        $this->db->reset_query();
        if ($month != 0 && $year != 0) {
            $this->db->where('MONTH(waktu_kunjungan)', $month);
            $this->db->where('YEAR(waktu_kunjungan)', $year);
        } elseif ($month == 0 && $year != 0) {
            $this->db->where('YEAR(waktu_kunjungan)', $year);
        }
        if ($user_id !== null) {
            $this->db->where('id_user', $user_id);
        }
        return $this->db->count_all_results('seminar'); 
    }

    public function get_sample_count_by_month($month, $year, $user_id = null) {
        $this->db->reset_query();
        if ($month != 0 && $year != 0) {
            $this->db->where('MONTH(waktu_kunjungan)', $month);
            $this->db->where('YEAR(waktu_kunjungan)', $year);
        } elseif ($month == 0 && $year != 0) {
            $this->db->where('YEAR(waktu_kunjungan)', $year);
        }
        if ($user_id !== null) {
            $this->db->where('id_user', $user_id);
        }
        return $this->db->count_all_results('sample_form'); 
    }
    
    public function get_new_customer_count_by_month($month, $year, $user_id = null) {
        $total_new = 0;
        $tables = ['master_farm', 'master_subagen', 'master_kemitraan'];
        
        foreach ($tables as $table) {
            $this->db->reset_query();
            if ($month != 0 && $year != 0) {
                $this->db->where('MONTH(created_at)', $month);
                $this->db->where('YEAR(created_at)', $year);
            } elseif ($month == 0 && $year != 0) {
                $this->db->where('YEAR(created_at)', $year);
            }
            if ($user_id !== null) {
                $this->db->where('id_user', $user_id);
            }
            $total_new += $this->db->count_all_results($table);
        }
        return $total_new;
    }

    public function get_harga_telur_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_jual_telur_layer');
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')));
        $this->db->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Mengambil data harga jual telur layer bulanan untuk 12 bulan terakhir.
     * @return array
     */
    public function get_harga_telur_bulanan_chart()
    {
        $this->db->select('tahun, bulan, nilai_rata_rata');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_jual_telur_layer');
        $this->db->order_by('tahun', 'DESC');
        $this->db->order_by('bulan', 'DESC');
        $this->db->limit(12);
        $query = $this->db->get();
        return array_reverse($query->result_array());
    }

    /**
     * Mengambil data harga jual telur layer tahunan.
     * @return array
     */
    public function get_harga_telur_tahunan_chart()
    {
        $this->db->select('tahun, nilai_rata_rata');
        $this->db->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'harga_jual_telur_layer');
        $this->db->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Mengambil harga rata-rata telur layer HARI INI saja.
     * @return array|null
     */
    public function get_harga_telur_hari_ini()
    {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_jual_telur_layer');
        $this->db->where('tanggal', date('Y-m-d')); 
        return $this->db->get()->row_array();
    }

    public function get_harga_jagung_hari_ini()
    {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_jagung'); 
        $this->db->where('tanggal', date('Y-m-d'));
        return $this->db->get()->row_array();
    }

    public function get_harga_jagung_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_jagung'); 
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')));
        $this->db->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_harga_jagung_bulanan_chart()
    {
        $this->db->select('tahun, bulan, nilai_rata_rata');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_jagung'); 
        $this->db->order_by('tahun', 'DESC');
        $this->db->order_by('bulan', 'DESC');
        $this->db->limit(12);
        $query = $this->db->get();
        return array_reverse($query->result_array());
    }

    public function get_harga_jagung_tahunan_chart()
    {
        $this->db->select('tahun, nilai_rata_rata');
        $this->db->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'harga_jagung'); 
        $this->db->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_harga_katul_hari_ini()
    {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_katul'); 
        $this->db->where('tanggal', date('Y-m-d'));
        return $this->db->get()->row_array();
    }

    public function get_harga_katul_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_katul'); 
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')));
        $this->db->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_harga_katul_bulanan_chart()
    {
        $this->db->select('tahun, bulan, nilai_rata_rata');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_katul'); 
        $this->db->order_by('tahun', 'DESC');
        $this->db->order_by('bulan', 'DESC');
        $this->db->limit(12);
        $query = $this->db->get();
        return array_reverse($query->result_array());
    }

    public function get_harga_katul_tahunan_chart()
    {
        $this->db->select('tahun, nilai_rata_rata');
        $this->db->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'harga_katul'); 
        $this->db->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_harga_afkir_hari_ini()
    {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_afkir');
        $this->db->where('tanggal', date('Y-m-d'));
        return $this->db->get()->row_array();
    }

    public function get_harga_afkir_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_afkir');
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')));
        $this->db->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_harga_afkir_bulanan_chart()
    {
        $this->db->select('tahun, bulan, nilai_rata_rata');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_afkir');
        $this->db->order_by('tahun DESC, bulan DESC')->limit(12);
        return array_reverse($this->db->get()->result_array());
    }

    public function get_harga_afkir_tahunan_chart()
    {
        $this->db->select('tahun, nilai_rata_rata');
        $this->db->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'harga_afkir');
        $this->db->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_harga_telur_puyuh_hari_ini() {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data')->from('harga_rata_rata_harian');
        return $this->db->where(['jenis_harga' => 'harga_telur_puyuh', 'tanggal' => date('Y-m-d')])->get()->row_array();
    }
    public function get_harga_telur_puyuh_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata')->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_telur_puyuh');
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')))->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }
    public function get_harga_telur_puyuh_bulanan_chart() {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_telur_puyuh');
        $this->db->order_by('tahun DESC, bulan DESC')->limit(12);
        return array_reverse($this->db->get()->result_array());
    }
    public function get_harga_telur_puyuh_tahunan_chart() {
        $this->db->select('tahun, nilai_rata_rata')->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'harga_telur_puyuh')->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_harga_telur_bebek_hari_ini() {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data')->from('harga_rata_rata_harian');
        return $this->db->where(['jenis_harga' => 'harga_telur_bebek', 'tanggal' => date('Y-m-d')])->get()->row_array();
    }
    public function get_harga_telur_bebek_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata')->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_telur_bebek');
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')))->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }
    public function get_harga_telur_bebek_bulanan_chart() {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_telur_bebek');
        $this->db->order_by('tahun DESC, bulan DESC')->limit(12);
        return array_reverse($this->db->get()->result_array());
    }
    public function get_harga_telur_bebek_tahunan_chart() {
        $this->db->select('tahun, nilai_rata_rata')->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'harga_telur_bebek')->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }
    
    public function get_harga_bebek_pedaging_hari_ini() {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data')->from('harga_rata_rata_harian');
        return $this->db->where(['jenis_harga' => 'harga_bebek_pedaging', 'tanggal' => date('Y-m-d')])->get()->row_array();
    }
    public function get_harga_bebek_pedaging_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata')->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_bebek_pedaging');
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')))->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }
    public function get_harga_bebek_pedaging_bulanan_chart() {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_bebek_pedaging');
        $this->db->order_by('tahun DESC, bulan DESC')->limit(12);
        return array_reverse($this->db->get()->result_array());
    }
    public function get_harga_bebek_pedaging_tahunan_chart() {
        $this->db->select('tahun, nilai_rata_rata')->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'harga_bebek_pedaging')->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }
    
    public function get_harga_live_bird_hari_ini() {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data')->from('harga_rata_rata_harian');
        return $this->db->where(['jenis_harga' => 'harga_live_bird', 'tanggal' => date('Y-m-d')])->get()->row_array();
    }
    public function get_harga_live_bird_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata')->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_live_bird');
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')))->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }
    public function get_harga_live_bird_bulanan_chart() {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_live_bird');
        $this->db->order_by('tahun DESC, bulan DESC')->limit(12);
        return array_reverse($this->db->get()->result_array());
    }
    public function get_harga_live_bird_tahunan_chart() {
        $this->db->select('tahun, nilai_rata_rata')->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'harga_live_bird')->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }

    // ========================================================================
    // HARGA PAKAN BROILER
    // ========================================================================
    // public function get_harga_pakan_broiler_hari_ini() {
    //     $this->db->select('nilai_harga as nilai_rata_rata, 1 as jumlah_sumber_data')->from('master_harga');
    //     return $this->db->where('jenis_harga', 'harga_pakan_broiler')->get()->row_array();
    // }
    // public function get_harga_pakan_broiler_harian_chart() { /* Data harian tidak berlaku untuk harga master */ return []; }
    // public function get_harga_pakan_broiler_bulanan_chart() { /* Data bulanan tidak berlaku untuk harga master */ return []; }
    // public function get_harga_pakan_broiler_tahunan_chart() { /* Data tahunan tidak berlaku untuk harga master */ return []; }

    // ========================================================================
    // HARGA PAKAN BROILER
    // ========================================================================
    public function get_harga_pakan_broiler_hari_ini() {
        $this->db->select('nilai_harga as nilai_rata_rata, 1 as jumlah_sumber_data')->from('master_harga');
        return $this->db->where('nama_harga', 'Pakan Komplit Broiler')->get()->row_array();
    }
    public function get_harga_pakan_broiler_harian_chart() { return []; }
    public function get_harga_pakan_broiler_bulanan_chart() { return []; }
    public function get_harga_pakan_broiler_tahunan_chart() { return []; }

    // ========================================================================
    // HARGA DOC
    // ========================================================================
    // public function get_harga_doc_hari_ini() {
    //     $this->db->select('nilai_harga as nilai_rata_rata, 1 as jumlah_sumber_data')->from('master_harga');
    //     return $this->db->where('jenis_harga', 'harga_doc')->get()->row_array();
    // }
    // public function get_harga_doc_harian_chart() { return []; }
    // public function get_harga_doc_bulanan_chart() { return []; }
    // public function get_harga_doc_tahunan_chart() { return []; }

    // ========================================================================
    // HARGA DOC
    // ========================================================================
    // public function get_harga_doc_hari_ini() {
    //     $this->db->select('nilai_harga as nilai_rata_rata, 1 as jumlah_sumber_data')->from('master_harga');
    //     return $this->db->where('nama_harga', 'DOC')->get()->row_array();
    // }
    // public function get_harga_doc_harian_chart() { return []; }
    // public function get_harga_doc_bulanan_chart() { return []; }
    // public function get_harga_doc_tahunan_chart() { return []; }

    // ========================================================================
    // HARGA DOC
    // ========================================================================
    
    /**
     * Mengambil harga rata-rata DOC HARI INI saja.
     */
    public function get_harga_doc_hari_ini() {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_doc');
        $this->db->where('tanggal', date('Y-m-d'));
        return $this->db->get()->row_array();
    }

    /**
     * Mengambil data harga DOC harian untuk 30 hari terakhir.
     */
    public function get_harga_doc_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_doc');
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')));
        $this->db->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Mengambil data harga DOC bulanan untuk 12 bulan terakhir.
     */
    public function get_harga_doc_bulanan_chart() {
        $this->db->select('tahun, bulan, nilai_rata_rata');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_doc');
        $this->db->order_by('tahun DESC, bulan DESC')->limit(12);
        return array_reverse($this->db->get()->result_array());
    }

    /**
     * Mengambil data harga DOC tahunan.
     */
    public function get_harga_doc_tahunan_chart() {
        $this->db->select('tahun, nilai_rata_rata');
        $this->db->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'harga_doc')->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }
    
    public function get_harga_konsentrat_layer_hari_ini() {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data')->from('harga_rata_rata_harian');
        return $this->db->where(['jenis_harga' => 'harga_konsentrat_layer', 'tanggal' => date('Y-m-d')])->get()->row_array();
    }
    public function get_harga_konsentrat_layer_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata')->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_konsentrat_layer');
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')))->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }
    public function get_harga_konsentrat_layer_bulanan_chart() {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_konsentrat_layer');
        $this->db->order_by('tahun DESC, bulan DESC')->limit(12);
        return array_reverse($this->db->get()->result_array());
    }
    public function get_harga_konsentrat_layer_tahunan_chart() {
        $this->db->select('tahun, nilai_rata_rata')->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'harga_konsentrat_layer')->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }

    // ========================================================================
    // HPP KONSENTRAT LAYER
    // ========================================================================
    public function get_hpp_konsentrat_layer_hari_ini() {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data')->from('harga_rata_rata_harian');
        return $this->db->where(['jenis_harga' => 'hpp_konsentrat_layer', 'tanggal' => date('Y-m-d')])->get()->row_array();
    }
    public function get_hpp_konsentrat_layer_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata')->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'hpp_konsentrat_layer');
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')))->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }
    public function get_hpp_konsentrat_layer_bulanan_chart() {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'hpp_konsentrat_layer');
        $this->db->order_by('tahun DESC, bulan DESC')->limit(12);
        return array_reverse($this->db->get()->result_array());
    }
    public function get_hpp_konsentrat_layer_tahunan_chart() {
        $this->db->select('tahun, nilai_rata_rata')->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'hpp_konsentrat_layer')->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }

    // ========================================================================
    // HPP KOMPLIT LAYER
    // ========================================================================
    public function get_hpp_komplit_layer_hari_ini() {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data')->from('harga_rata_rata_harian');
        return $this->db->where(['jenis_harga' => 'hpp_komplit_layer', 'tanggal' => date('Y-m-d')])->get()->row_array();
    }
    public function get_hpp_komplit_layer_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata')->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'hpp_komplit_layer');
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')))->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }
    public function get_hpp_komplit_layer_bulanan_chart() {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'hpp_komplit_layer');
        $this->db->order_by('tahun DESC, bulan DESC')->limit(12);
        return array_reverse($this->db->get()->result_array());
    }
    public function get_hpp_komplit_layer_tahunan_chart() {
        $this->db->select('tahun, nilai_rata_rata')->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'hpp_komplit_layer')->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }

    // ========================================================================
    // [COST KOMPLIT BROILER] - FUNGSI UNTUK REPORT
    // ========================================================================

    public function get_harga_cost_komplit_broiler_hari_ini() {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data')->from('harga_rata_rata_harian');
        return $this->db->where(['jenis_harga' => 'cost_komplit_broiler', 'tanggal' => date('Y-m-d')])->get()->row_array();
    }
    public function get_harga_cost_komplit_broiler_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata')->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'cost_komplit_broiler');
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')))->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }
    public function get_harga_cost_komplit_broiler_bulanan_chart() {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'cost_komplit_broiler');
        $this->db->order_by('tahun DESC, bulan DESC')->limit(12);
        return array_reverse($this->db->get()->result_array());
    }
    public function get_harga_cost_komplit_broiler_tahunan_chart() {
        $this->db->select('tahun, nilai_rata_rata')->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'cost_komplit_broiler')->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }

    // ========================================================================
    // [HPP BROILER] - FUNGSI UNTUK REPORT
    // ========================================================================

    public function get_harga_hpp_broiler_hari_ini() {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data')->from('harga_rata_rata_harian');
        return $this->db->where(['jenis_harga' => 'hpp_broiler', 'tanggal' => date('Y-m-d')])->get()->row_array();
    }
    public function get_harga_hpp_broiler_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata')->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'hpp_broiler');
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')))->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }
    public function get_harga_hpp_broiler_bulanan_chart() {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'hpp_broiler');
        $this->db->order_by('tahun DESC, bulan DESC')->limit(12);
        return array_reverse($this->db->get()->result_array());
    }
    public function get_harga_hpp_broiler_tahunan_chart() {
        $this->db->select('tahun, nilai_rata_rata')->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'hpp_broiler')->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Helper function to get all unique livestock types from the farm master.
     * This is used to populate the filter dropdown in the view.
     * @return array
     */
    public function get_all_tipe_ternak()
    {
        $this->db->select('tipe_ternak');
        $this->db->from('master_farm');
        $this->db->distinct();
        $this->db->order_by('tipe_ternak', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Mengambil data persentase kandang kosong bulanan per tipe ternak.
     * VERSI BARU: Menggunakan 'nama_farm' sebagai kunci join dan kapasitas dinamis.
     * @param array $filters Filter yang diterapkan.
     * @return array Data yang siap untuk diolah menjadi chart.
     */
    public function get_monthly_vacancy_percentage($filters = [])
    {
        // --- Membangun klausa WHERE dinamis (logika filter tetap sama) ---
        $where_clauses = [];
        if (!empty($filters['tipe_ternak'])) {
            $where_clauses[] = "mf.tipe_ternak = " . $this->db->escape($filters['tipe_ternak']);
        }
        if (!empty($filters['start_month'])) {
            $start_date = date('Y-m-01', strtotime($filters['start_month']));
            $where_clauses[] = "ktb.waktu_kunjungan >= " . $this->db->escape($start_date);
        }
        if (!empty($filters['end_month'])) {
            $end_date = date('Y-m-t', strtotime($filters['end_month']));
            $where_clauses[] = "ktb.waktu_kunjungan <= " . $this->db->escape($end_date);
        }
        
        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = "AND " . implode(" AND ", $where_clauses);
        }
        // --- Akhir dari pembuatan klausa WHERE ---

        $sql = "
            WITH 
            -- Tahap 1: Gabungkan semua sumber data 'populasi terisi' dengan 'nama_farm'
            semua_kunjungan AS (
                -- Menggunakan alias 'nama_farm' untuk menyeragamkan nama kolom
                SELECT layer_nama_farm AS nama_farm, waktu_kunjungan, (layer_pakai_pakan_cp + layer_selain_pakan_cp) AS jumlah_terisi FROM visiting_p_layer
                UNION ALL
                SELECT nama_farm, waktu_kunjungan, efektif_terisi_petelur AS jumlah_terisi FROM visiting_p_arap
                UNION ALL
                SELECT nama_farm, waktu_kunjungan, efektif_terisi_petelur AS jumlah_terisi FROM visiting_p_bebek_petelur
                UNION ALL
                SELECT nama_farm, waktu_kunjungan, efektif_terisi_petelur AS jumlah_terisi FROM visiting_p_puyuh
                UNION ALL
                SELECT nama_farm, waktu_kunjungan, efektif_terisi_pedaging AS jumlah_terisi FROM visiting_p_grower
                UNION ALL
                SELECT nama_farm, waktu_kunjungan, efektif_terisi_pedaging AS jumlah_terisi FROM visiting_p_bebek_pedaging
            ),
            -- Tahap 2: Ambil kunjungan TERAKHIR berdasarkan 'nama_farm' di setiap bulan
            kunjungan_terakhir_bulanan AS (
                SELECT
                    nama_farm,
                    waktu_kunjungan,
                    jumlah_terisi,
                    ROW_NUMBER() OVER(
                        PARTITION BY nama_farm, YEAR(waktu_kunjungan), MONTH(waktu_kunjungan) 
                        ORDER BY waktu_kunjungan DESC
                    ) as rn
                FROM semua_kunjungan
                WHERE nama_farm IS NOT NULL AND nama_farm != ''
            )
            -- Tahap 3: Agregasi final dengan join menggunakan 'nama_farm'
            SELECT 
                mf.tipe_ternak,
                YEAR(ktb.waktu_kunjungan) AS tahun,
                MONTH(ktb.waktu_kunjungan) AS bulan,
                (SUM(hfc.kapasitas) - SUM(ktb.jumlah_terisi)) / SUM(hfc.kapasitas) * 100 AS persentase_kosong
            FROM kunjungan_terakhir_bulanan ktb
            -- JOIN diubah menggunakan 'nama_farm'
            JOIN history_farm_capacity hfc ON ktb.nama_farm = hfc.nama_farm
                                        AND ktb.waktu_kunjungan BETWEEN hfc.start_date AND hfc.end_date
            -- Tetap join ke master_farm untuk mendapatkan tipe_ternak
            JOIN master_farm mf ON ktb.nama_farm = mf.nama_farm
            WHERE ktb.rn = 1 {$where_sql} -- Filter dinamis tetap di sini
            GROUP BY mf.tipe_ternak, tahun, bulan
            ORDER BY tahun, bulan, mf.tipe_ternak;
        ";

        $query = $this->db->query($sql);
        return $query->result_array();
    }

}
