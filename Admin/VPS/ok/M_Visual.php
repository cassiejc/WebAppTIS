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
        'Arap' => 'visiting_p_arap'
    ];

    public function __construct() {
        parent::__construct();
    }

    public function get_surveyor_performance($month, $year, $user_id = null) {
        $month = (int)$month;
        $year = (int)$year;
        $final_query = '';

        $union_query = $this->_build_union_query_for_aktual($month, $year, $user_id);
        
        $main_user_filter = ($user_id !== null) ? "WHERE u.id_user = {$user_id}" : "";

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
                {$main_user_filter} /* <-- Filter diterapkan di sini */
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
                {$main_user_filter} /* <-- Filter diterapkan di sini juga */
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
        // Pengecekan spesifik HANYA untuk group_user 'surveyor'
        if ($user && isset($user['group_user']) && $user['group_user'] === 'surveyor') {
            if (isset($user['master_area_id'])) {
                $area_filter_sql = "WHERE ma.master_area_id = " . $this->db->escape($user['master_area_id']);
            }
        }
        $union_query = $this->_build_union_query_for_aktual($month, $year, null);
        
        if ($month != 0 && $year != 0) {
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

    public function get_visit_breakdown($month, $year, $user_id = null) {
        $month = (int)$month;
        $year = (int)$year;
        $sub_queries = [];
        $where_clause = '';
        if ($month != 0 && $year != 0) {
            $where_clause = "WHERE MONTH(waktu_kunjungan) = {$month} AND YEAR(waktu_kunjungan) = {$year}";
        }
        $user_filter_sql = '';
        if ($user_id !== null) {
            $user_filter_sql = $where_clause ? " AND id_user = {$user_id}" : "WHERE id_user = {$user_id}";
        }
        
        foreach ($this->table_mapping as $kategori => $nama_tabel) {
            $sub_queries[] = "SELECT '{$kategori}' as kategori FROM {$nama_tabel} {$where_clause} {$user_filter_sql}";
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
        $peternakan_tables = ['visiting_p_grower', 'visiting_p_bebek_pedaging', 'visiting_p_layer', 'visiting_p_bebek_petelur', 'visiting_p_puyuh', 'visiting_p_arap'];
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
        $peternakan_tables = ['visiting_p_grower', 'visiting_p_bebek_pedaging', 'visiting_p_layer', 'visiting_p_bebek_petelur', 'visiting_p_puyuh', 'visiting_p_arap'];
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

    public function get_kasus_detail_list($year, $user_id = null) {
        $year = (int)$year;
        $peternakan_tables = [
            'visiting_p_grower', 'visiting_p_bebek_pedaging', 'visiting_p_layer',
            'visiting_p_bebek_petelur', 'visiting_p_puyuh', 'visiting_p_arap'
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
        $this->db->where('tanggal', date('Y-m-d')); // Menggunakan tanggal hari ini
        return $this->db->get()->row_array();
    }
}
