<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class KelompokPraktik extends Model
{
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

        $conditionWhere = '';
        if (!empty($conditions)) {
            $conditionWhere = "WHERE " . implode(" AND ", $conditions);
        }

        $results = DB::select("
            SELECT
                tkh.NAMA_KELOMPOK ,
                tkh.ID_KELOMPOK_HEAD,
                mpk.ID_DETSEM ,
                mpk.NAMA_MATKUL ,
                GROUP_CONCAT(DISTINCT tkd.KODE_DOSEN ORDER BY tkd.KODE_DOSEN ASC SEPARATOR ';') AS KODE_DOSEN ,
                GROUP_CONCAT(DISTINCT tkd.NAMA_DOSEN ORDER BY tkd.NAMA_DOSEN ASC SEPARATOR ';') AS DOSEN_LIST ,
                GROUP_CONCAT(DISTINCT tkm.KODE_MAHASISWA ORDER BY tkm.KODE_MAHASISWA ASC SEPARATOR ';') AS KODE_MHS ,
                GROUP_CONCAT(DISTINCT tkm.NAMA_MAHASISWA ORDER BY tkm.NAMA_MAHASISWA ASC SEPARATOR ';') AS MHS_LIST
            FROM
                tb_kelompok_head tkh 
            LEFT JOIN mapping_peta_kurikulum mpk ON
                mpk.ID_DETSEM = tkh.ID_DETSEM 
            LEFT JOIN tb_kelompok_dosen tkd ON
                tkd.ID_KELOMPOK_HEAD = tkh.ID_KELOMPOK_HEAD
            LEFT JOIN tb_kelompok_mhs tkm ON
                tkm.ID_KELOMPOK_HEAD = tkh.ID_KELOMPOK_HEAD
            $conditionWhere
            GROUP BY
                tkh.ID_KELOMPOK_HEAD
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
                    tkh.NAMA_KELOMPOK ,
                    tkh.ID_KELOMPOK_HEAD,
                    mpk.ID_DETSEM ,
                    mpk.NAMA_MATKUL ,
                    GROUP_CONCAT(DISTINCT tkd.NAMA_DOSEN ORDER BY tkd.NAMA_DOSEN ASC SEPARATOR ';') AS KODE_DOSEN ,
                    GROUP_CONCAT(DISTINCT tkd.KODE_DOSEN ORDER BY tkd.KODE_DOSEN ASC SEPARATOR ';') AS DOSEN_LIST ,
                    GROUP_CONCAT(DISTINCT tkm.KODE_MAHASISWA ORDER BY tkm.KODE_MAHASISWA ASC SEPARATOR ';') AS KODE_MHS ,
                    GROUP_CONCAT(DISTINCT tkm.NAMA_MAHASISWA ORDER BY tkm.NAMA_MAHASISWA ASC SEPARATOR ';') AS MHS_LIST
                FROM
                    tb_kelompok_head tkh 
                LEFT JOIN mapping_peta_kurikulum mpk ON
                    mpk.ID_DETSEM = tkh.ID_DETSEM 
                LEFT JOIN tb_kelompok_dosen tkd ON
                    tkd.ID_KELOMPOK_HEAD = tkh.ID_KELOMPOK_HEAD
                LEFT JOIN tb_kelompok_mhs tkm ON
                    tkm.ID_KELOMPOK_HEAD = tkh.ID_KELOMPOK_HEAD
                    $conditionWhere
                GROUP BY
                    tkh.ID_KELOMPOK_HEAD
            ) AS grouped_data;
        ");

        return [
            'DATA' => $results,
            'TOTAL_DATA' => $Tot
        ];
    }
}
