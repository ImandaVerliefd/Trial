<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Penjadwalan extends Model
{
    public static function get_data_datatable($orderType, $valOrder, $search, $start, $perPage, $prodi)
    {
        DB::statement("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '');");
        if (!empty($search)) {
            $conditions[] = "
                (
                    mm.NAMA_MATKUL LIKE '%$search%'
                    OR
                    mm.SKS LIKE '%$search%'
                    OR
                    mm.JUMLAH_PERTEMUAN LIKE '%$search%'
                    OR
                    mpk.KURIKULUM LIKE '%$search%'
                    OR
                    mpk.PRODI LIKE '%$search%'
                    OR
                    mta.TAHUN_AJAR LIKE '%$search%'
                )
            ";
        }

        $conditions[] = "mm.IS_DELETE = 0";
        $conditions[] = "mm.IS_ACTIVE = 1";
        $conditions[] = "mpk.IS_DELETE IS NULL";
        $conditions[] = "mpk.ID_PRODI = '$prodi'";

        $conditionWhere = "";
        if (!empty($conditions)) {
            $conditionWhere = "WHERE " . implode(" AND ", $conditions);
        }

        $mainQuery = "
            SELECT 
                mpk.*,
                mm.IS_ACTIVE,
                mm.IS_DELETE AS MATKUL_DELETE ,
                ms2.TAHUN ,
                GROUP_CONCAT(md.NAMA_DOSEN SEPARATOR ';') AS DOSEN_PENGAMPU ,
                mta.TAHUN_AJAR ,
                trj.HARI ,
                trj.JAM_MULAI ,
                trj.SKS_MATKUL ,
                trj.JAM_SELESAI ,
                trj.ID_RUANGAN ,
                trj.NAMA_RUANGAN ,
                trj.KAPASITAS_RUANGAN ,
                trj.ID_METODE,
                trj.KODE
            FROM 
                mapping_peta_kurikulum mpk
            LEFT JOIN md_matkul mm ON 
                mm.KODE_MATKUL = mpk.KODE_MATKUL 
            LEFT JOIN md_semester ms2 ON 
                ms2.ID_SEMESTER = mpk.ID_SEMESTER 
            LEFT JOIN mapping_subcpmk ms ON 
                ms.ID_DETSEM = mpk.ID_DETSEM
            LEFT JOIN md_dosen md ON
                md.KODE_DOSEN = ms.KODE_DOSEN 
            LEFT JOIN md_tahun_ajaran mta ON
                mta.ID_TAHUN_AJAR = mpk.ID_TAHUN_AJAR 
            LEFT JOIN (
                SELECT 
                    trj.ID_DETSEM,
                    GROUP_CONCAT(trj.KODE SEPARATOR ';') AS KODE,
                    GROUP_CONCAT(trj.HARI SEPARATOR ';') AS HARI,
                    GROUP_CONCAT(trj.JAM_MULAI SEPARATOR ';') AS JAM_MULAI,
                    GROUP_CONCAT(trj.JAM_SELESAI SEPARATOR ';') AS JAM_SELESAI,
                    GROUP_CONCAT(trj.ID_RUANGAN SEPARATOR ';') AS ID_RUANGAN,
                    GROUP_CONCAT(trj.NAMA_RUANGAN SEPARATOR ';') AS NAMA_RUANGAN,
                    GROUP_CONCAT(trj.KAPASITAS_RUANGAN SEPARATOR ';') AS KAPASITAS_RUANGAN,
                    GROUP_CONCAT(trj.SKS_MATKUL SEPARATOR ';') AS SKS_MATKUL,
                    GROUP_CONCAT(trj.ID_METODE SEPARATOR ';') AS ID_METODE, 
                    GROUP_CONCAT(mma.METODE SEPARATOR ';') AS METODE 
                FROM 
                    tb_ruang_jadwal trj 
                LEFT JOIN md_metode_ajar mma ON 
                    mma.ID_METODE = trj.ID_METODE 
                GROUP BY 
                    trj.ID_DETSEM
            ) trj ON 
                trj.ID_DETSEM = mpk.ID_DETSEM 
            $conditionWhere
            GROUP BY 
                mpk.ID_DETSEM 
            ORDER BY
                $valOrder $orderType
            LIMIT 
                $start, $perPage
        ";

        $results = DB::select($mainQuery);



        $Tot = DB::selectOne("
            SELECT
                COUNT(*) AS DataTrans
            FROM
                (
                SELECT
                    mpk.*,
                    mm.IS_ACTIVE,
                    mm.IS_DELETE AS MATKUL_DELETE,
                    ms2.TAHUN,
                    GROUP_CONCAT( md.NAMA_DOSEN SEPARATOR ';' ) AS DOSEN_PENGAMPU 
                FROM
                    mapping_peta_kurikulum mpk
                    LEFT JOIN md_matkul mm ON mm.KODE_MATKUL = mpk.KODE_MATKUL
                    LEFT JOIN md_semester ms2 ON ms2.ID_SEMESTER = mpk.ID_SEMESTER
                    LEFT JOIN mapping_subcpmk ms ON ms.ID_DETSEM = mpk.ID_DETSEM
                    LEFT JOIN md_dosen md ON md.KODE_DOSEN = ms.KODE_DOSEN 
                    $conditionWhere
                GROUP BY
                mpk.ID_DETSEM 
                ) AS subquery;
        ");

        return [
            'DATA' => $results,
            'TOTAL_DATA' => $Tot
        ];
    }

    public static function get_detail_sub_cpmk($kodeKelas, $kodeMatkul, $idSemester, $kodeSemester)
    {
        return DB::select("
            SELECT 	
                mm.KODE_MATKUL ,
                mm.NAMA_MATKUL ,
                mm.SKS ,
                mm.JUMLAH_PERTEMUAN ,
                tcd.ID_KURIKULUM ,
                mm.ID_PRODI ,
                mc.CAPAIAN ,
                tcd.* ,
                ms.KODE_DOSEN ,
                ms.ID_MAPPING
            FROM 
                tb_capaian_detail tcd 
            LEFT JOIN md_capaian mc ON
                mc.KODE_CAPAIAN = tcd.KODE_CAPAIAN
            LEFT JOIN md_matkul mm ON
                mm.KODE_MATKUL = tcd.KODE_MATKUL
            LEFT JOIN (
                    SELECT 
                        ms.ID_DETSEM ,
                        ms.ID_CAPAIAN_DETAIL ,
                        ms.KODE_DOSEN ,
                        ms.ID_MAPPING 
                    FROM 
                        mapping_subcpmk ms 
                    WHERE 
                        ms.KODE_KELAS = '$kodeKelas'
                ) ms ON 
                ms.ID_CAPAIAN_DETAIL = tcd.ID_CAPAIAN_DETAIL
            LEFT JOIN mapping_peta_kurikulum mpk ON
                mpk.KODE_MATKUL = tcd.KODE_MATKUL 
                AND
                mpk.ID_KURIKULUM = tcd.ID_KURIKULUM  
            WHERE 
                tcd.KODE_MATKUL = '$kodeMatkul'
                AND
                mpk.ID_SEMESTER = '$idSemester'
                AND
                mpk.KODE_SEMESTER = '$kodeSemester'
            ORDER BY 
                CAST(tcd.PERTEMUAN AS UNSIGNED) ASC
        ");
    }

    public static function list_kelas_by_id_detsem($idDetsem)
    {
        $query = "
            SELECT
                tkm.*
            FROM
                tb_kelas_matkul tkm
            WHERE
                tkm.ID_DETSEM = '$idDetsem'
        ";

        return DB::select($query);
    }

    public static function list_kelas_now($idDetsem)
    {
        $query = "
            SELECT 
                tkm.NAMA_KELAS ,
                GROUP_CONCAT(trj.HARI  SEPARATOR ';') AS HARI ,
                GROUP_CONCAT(trj.JAM_MULAI  SEPARATOR ';') AS JAM_MULAI ,
                GROUP_CONCAT(trj.JAM_SELESAI  SEPARATOR ';') AS JAM_SELESAI ,
                GROUP_CONCAT(trj.NAMA_RUANGAN  SEPARATOR ';') AS NAMA_RUANGAN ,
                GROUP_CONCAT(trj.KAPASITAS_RUANGAN  SEPARATOR ';') AS KAPASITAS_RUANGAN ,
                GROUP_CONCAT(trj.SKS_MATKUL SEPARATOR ';') AS SKS_MATKUL 
            FROM 
                tb_ruang_jadwal trj
            LEFT JOIN tb_kelas_matkul tkm ON
                trj.KODE_KELAS = tkm.KODE_KELAS
            WHERE
                trj.ID_DETSEM = '$idDetsem'
            GROUP BY 
                tkm.NAMA_KELAS 
        ";

        return DB::select($query);
    }
}
