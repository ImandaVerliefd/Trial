<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DosenWali extends Model
{
    protected $table = 'tb_dosen_wali';
    protected $primaryKey = 'ID_DOSEN_WALI';
    public $incrementing = false;
    public $timestamps = false;

    public static function get_data_datatable($orderType, $valOrder, $search, $start, $perPage)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        if (!empty($search)) {
            $conditions[] = "
                (
                    tdw.NAMA_DOSEN LIKE '%$search%'
                    OR
                    tdw.NAMA_MAHASISWA LIKE '%$search%'
                )
            ";
        }

        $conditionWhere = "WHERE tdw.IS_ACTIVE = 1";
        if (!empty($conditions)) {
            $conditionWhere = "WHERE tdw.IS_ACTIVE = 1 AND" . implode(" AND ", $conditions);
        }

        $results = DB::select("
            SELECT
                tdw.KODE_DOSEN,
                tdw.LOG_TIME,
                tdw.NAMA_DOSEN,
                GROUP_CONCAT(tdw.NAMA_MAHASISWA ORDER BY tdw.NAMA_MAHASISWA ASC SEPARATOR ', ') AS MAHASISWA_LIST,
                GROUP_CONCAT(tdw.ID_DOSEN_WALI ORDER BY tdw.ID_DOSEN_WALI ASC SEPARATOR ', ') AS ID_DOSEN_WALI,
                GROUP_CONCAT(tdw.KODE_MAHASISWA ORDER BY tdw.KODE_MAHASISWA ASC SEPARATOR ', ') AS KODE_MAHASISWA
            FROM
                tb_dosen_wali tdw
                $conditionWhere
            GROUP BY
                tdw.KODE_DOSEN
            ORDER BY
                $valOrder $orderType
            LIMIT 
                $start, $perPage
        ");
        
        $Tot = DB::selectOne("
            SELECT 
                COUNT(*) AS DataTrans
            FROM (
                SELECT 
                    tdw.KODE_DOSEN
                FROM 
                    tb_dosen_wali tdw
                $conditionWhere
                GROUP BY 
                    tdw.KODE_DOSEN
            ) AS grouped_data;
        ");

        return [
            'DATA' => $results,
            'TOTAL_DATA' => $Tot
        ];
    }

    public static function get_data_datatable_for_dosen($orderType, $valOrder, $search, $start, $perPage, $search_per_colomn)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        if (!empty($search)) {
            $conditions[] = "
                (
                    mm.NIM LIKE '%$search%'
                    OR
                    tdw.NAMA_MAHASISWA LIKE '%$search%'
                )
            ";
        }
        if (!empty($search_per_colomn)) {
            foreach ($search_per_colomn as $key => $value) {
                $key = $key == 'IS_ACTIVE' ? 'mm.IS_ACTIVE' : $key;
                $key = $key == 'NAMA_MAHASISWA' ? 'tdw.NAMA_MAHASISWA' : $key;
                $value = $key == 'mm.IS_ACTIVE' ? ($value == 'Aktif' ? 1 : 0) : $value;
                $conditions[] = "$key LIKE '%$value%'";
            }
        }

        $conditionWhere = "WHERE tdw.IS_ACTIVE = 1 AND md.ID_USER = '" . session('user')[0]['id_user'] . "'";
        if (!empty($conditions)) {
            $conditionWhere = "WHERE tdw.IS_ACTIVE = 1 AND md.ID_USER = '" . session('user')[0]['id_user'] . "' AND " . implode(" AND ", $conditions);
        }

        $results = DB::select("
            SELECT
                tdw.KODE_DOSEN,
                tdw.LOG_TIME,
                tdw.NAMA_DOSEN,
                tdw.NAMA_MAHASISWA,
                mm.NIM,
                mm.IS_ACTIVE,
                mm.TAHUN_MASUK,
                mm.KODE_MAHASISWA,
                mm.EMAIL_MAHASISWA
            FROM
                tb_dosen_wali tdw
            LEFT JOIN md_mahasiswa mm ON 
                tdw.KODE_MAHASISWA = mm.KODE_MAHASISWA
            LEFT JOIN md_dosen md ON 
                tdw.KODE_DOSEN = md.KODE_DOSEN
                $conditionWhere
            ORDER BY
                $valOrder $orderType
            LIMIT 
                $start, $perPage
        ");
        
        $Tot = DB::selectOne("
            SELECT 
                COUNT(*) AS DataTrans
            FROM 
                tb_dosen_wali tdw
            LEFT JOIN md_mahasiswa mm ON 
                tdw.KODE_MAHASISWA = mm.KODE_MAHASISWA
            LEFT JOIN md_dosen md ON 
                tdw.KODE_DOSEN = md.KODE_DOSEN
            $conditionWhere
        ");

        return [
            'DATA' => $results,
            'TOTAL_DATA' => $Tot
        ];
    }
    
    public static function all_data_dosen()
    {
        return DB::select("
            SELECT
                *
            FROM
                tb_dosen_wali tdw
            WHERE
                tdw.IS_ACTIVE = 1
            ");
    }

    public static function join_data_dosen()
    {
        return DB::select("
            SELECT
                *
            FROM
                tb_dosen_wali tdw
            LEFT JOIN md_mahasiswa mm ON 
                tdw.KODE_MAHASISWA = mm.KODE_MAHASISWA
            LEFT JOIN md_dosen md ON 
                tdw.KODE_DOSEN = md.KODE_DOSEN
            WHERE
                tdw.IS_ACTIVE = 1
                AND md.ID_USER = '" . session('user')[0]['id_user'] . "'
            ");
    }
}
