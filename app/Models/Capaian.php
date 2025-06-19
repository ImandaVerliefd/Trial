<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Capaian extends Model
{
    protected $table = 'md_capaian';
    protected $primaryKey = 'KODE_CAPAIAN';
    public $incrementing = false;
    public $timestamps = false;

    public static function get_data_capaian_datatable($orderType, $valOrder, $search, $start, $perPage, $tipe, $prodi)
    {
        if (!empty($search)) {
            $conditions[] = "
                (
                    mc.KODE_CAPAIAN LIKE '%$search%'
                    OR
                    mc.CAPAIAN LIKE '%$search%'
                )
            ";
        }
        $conditions[] = "mc.IS_DELETE = 0";
        $conditions[] = "mc.JENIS_CAPAIAN = '$tipe'";
        $conditions[] = "mc.ID_PRODI = '$prodi'";

        $conditionWhere = "";
        if (!empty($conditions)) {
            $conditionWhere = "WHERE " . implode(" AND ", $conditions);
        }

        $results = DB::select("
            SELECT
                mc.*
            FROM
                md_capaian mc
                $conditionWhere
            ORDER BY
                $valOrder $orderType
            LIMIT 
                $start, $perPage
        ");

        $Tot = DB::selectOne("
            SELECT 
                COUNT(mc.KODE_CAPAIAN) AS DataTrans
            FROM
                md_capaian mc
                $conditionWhere
        ");

        return [
            'DATA' => $results,
            'TOTAL_DATA' => $Tot
        ];
    }

    public static function get_data_detail_capaian_datatable($orderType, $valOrder, $search, $start, $perPage, $prodi)
    {
        if (!empty($search)) {
            $conditions[] = "
                (
                    mc.KODE_CAPAIAN LIKE '%$search%'
                    OR
                    mc.CAPAIAN LIKE '%$search%'
                    OR
                    mc.JENIS_CAPAIAN LIKE '%$search%'
                )
            ";
        }
        $conditions[] = "tcd.IS_DELETE IS NULL";
        $conditions[] = "mc.IS_DELETE = 0 AND mc.IS_ACTIVE = 1";
        $conditions[] = "mm.ID_PRODI = '$prodi'";

        $conditionWhere = "";
        if (!empty($conditions)) {
            $conditionWhere = "WHERE " . implode(" AND ", $conditions);
        }

        $ordering = "";
        if (!empty($valOrder)) {
            $ordering = "
                ORDER BY
                    $valOrder $orderType
            ";
        }

        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $results = DB::select("
            SELECT
                mc.*,
                mm.KODE_MATKUL ,
                mm.NAMA_MATKUL ,
                mp.PRODI ,
                mk.KURIKULUM ,
                GROUP_CONCAT(tcd.ID_CAPAIAN_DETAIL SEPARATOR ';') AS ID_CAPAIAN_DETAIL ,
                MAX(tcd.ID_KURIKULUM) AS ID_KURIKULUM ,
                COUNT(DISTINCT tcd.PERTEMUAN) AS TOT_PERTEMUAN
            FROM
                tb_capaian_detail tcd
            LEFT JOIN md_capaian mc ON
                tcd.KODE_CAPAIAN = mc.KODE_CAPAIAN
            LEFT JOIN md_matkul mm ON
                mm.KODE_MATKUL = tcd.KODE_MATKUL
            LEFT JOIN md_kurikulum mk ON 
                mk.ID_KURIKULUM = tcd.ID_KURIKULUM 
            LEFT JOIN md_prodi mp ON 
                mp.ID_PRODI = mm.ID_PRODI 
                $conditionWhere
            GROUP BY
                tcd.KODE_MATKUL ,
                tcd.ID_KURIKULUM
            HAVING 
                TOT_PERTEMUAN > 0
                $ordering
            LIMIT 
                $start, $perPage
        ");

        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $Tot = DB::selectOne("
            SELECT
                COUNT(tmp.KODE_CAPAIAN) AS DataTrans
            FROM (
                SELECT 
                    mc.KODE_CAPAIAN ,
                    COUNT(DISTINCT tcd.PERTEMUAN) AS TOT_SUB_CAPAIAN
                FROM
                    md_capaian mc
                LEFT JOIN
                    tb_capaian_detail tcd ON tcd.KODE_CAPAIAN = mc.KODE_CAPAIAN
                    AND 
                    tcd.KODE_CAPAIAN = mc.KODE_CAPAIAN
                LEFT JOIN md_matkul mm ON
                    mm.KODE_MATKUL = tcd.KODE_MATKUL
                    $conditionWhere
                GROUP BY
                    tcd.KODE_MATKUL ,
                    tcd.ID_KURIKULUM
                HAVING 
                    TOT_SUB_CAPAIAN > 0
            ) tmp
        ");

        return [
            'DATA' => $results,
            'TOTAL_DATA' => $Tot
        ];
    }

    public static function get_data_tipe_capaian_datatable($orderType, $valOrder, $search, $start, $perPage)
    {
        if (!empty($search)) {
            $conditions[] = "
                (
                    mtc.TIPE_PENILAIAN LIKE '%$search%'
                    OR
                    mtc.KETERANGAN LIKE '%$search%'
                )
            ";
        }
        $conditions[] = "mtc.IS_DELETE = 0";

        $conditionWhere = "";
        if (!empty($conditions)) {
            $conditionWhere = "WHERE " . implode(" AND ", $conditions);
        }

        $results = DB::select("
            SELECT
                mtc.*
            FROM
                md_tipe_capaian mtc
                $conditionWhere
            ORDER BY
                $valOrder $orderType
            LIMIT 
                $start, $perPage
        ");

        $Tot = DB::selectOne("
            SELECT 
                COUNT(mtc.ID_TIPE) AS DataTrans
            FROM
                md_tipe_capaian mtc
                $conditionWhere
        ");

        return [
            'DATA' => $results,
            'TOTAL_DATA' => $Tot
        ];
    }

    public static function get_data_detail_capaian($kodeMatkul, $idKurikulum)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $query = DB::select("
            SELECT
                GROUP_CONCAT(tcd.ID_CAPAIAN_DETAIL SEPARATOR ';') AS ID_CAPAIAN_DETAIL,
                GROUP_CONCAT(tcd.KODE_CAPAIAN SEPARATOR ';') AS KODE_CAPAIAN ,
                tcd.KODE_MATKUL ,
                tcd.NAMA_PEMBELAJARAN ,
                tcd.KAJIAN ,
                tcd.BENTUK_PEMBELAJARAN ,
                tcd.ESTIMASI_WAKTU ,
                tcd.PENGALAMAN ,
                tcd.INDIKATOR ,
                tcd.KRITERIA ,
                tcd.BOBOT ,
                tcd.ID_SIAKAD ,
                GROUP_CONCAT(tcd.PERTEMUAN SEPARATOR ';') AS `PERTEMUAN`,
                mk.KURIKULUM ,
                tcd.ID_KURIKULUM ,
                mc.CAPAIAN ,
                mc.JENIS_CAPAIAN ,
                mm.NAMA_MATKUL ,
                mm.JUMLAH_PERTEMUAN ,
                mpk.ID_SEMESTER ,
                mpk.RUMPUN_MATKUL ,
                mpk.KATEGORI_MATKUL ,
                mpk.DESKRIPSI_MATKUL ,
                ms.SEMESTER ,
                ms.TAHUN ,
                GROUP_CONCAT(md.NAMA_DOSEN SEPARATOR ';') AS NAMA_DOSEN   
            FROM
                tb_capaian_detail tcd
            LEFT JOIN md_capaian mc ON
                mc.KODE_CAPAIAN = tcd.KODE_CAPAIAN
            LEFT JOIN md_matkul mm ON
                mm.KODE_MATKUL = tcd.KODE_MATKUL
            LEFT JOIN mapping_peta_kurikulum mpk ON
                mpk.KODE_MATKUL = mm.KODE_MATKUL 
            LEFT JOIN md_kurikulum mk ON
                mk.ID_KURIKULUM = tcd.ID_KURIKULUM
            LEFT JOIN md_semester ms ON 
                ms.ID_SEMESTER = mpk.ID_SEMESTER 
            LEFT JOIN mapping_subcpmk ms2 ON 
                ms2.ID_CAPAIAN_DETAIL = tcd.ID_CAPAIAN_DETAIL 
            LEFT JOIN md_dosen md ON
                md.KODE_DOSEN = ms2.KODE_DOSEN 
            WHERE 
                tcd.KODE_MATKUL = '$kodeMatkul'
                AND
                tcd.ID_KURIKULUM = '$idKurikulum'
            GROUP BY 
                tcd.ORDERING ,
                tcd.KODE_CAPAIAN
        ");
        return json_decode(json_encode($query), true);
    }

    public static function get_capaian($kodeCapaian)
    {
        return DB::selectOne("
            SELECT 
                mc.*
            FROM 
                md_capaian mc
            WHERE 
                mc.KODE_CAPAIAN = '$kodeCapaian'
        ");
    }
}
