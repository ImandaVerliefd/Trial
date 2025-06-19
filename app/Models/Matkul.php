<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Matkul extends Model
{
    protected $table = 'md_matkul';
    protected $primaryKey = 'ID_MATKUL';
    public $incrementing = false;
    public $timestamps = false;

    public static function get_data_datatable($orderType, $valOrder, $search, $start, $perPage, $search_per_colomn)
    {
        if (!empty($search)) {
            $conditions[] = "
                (
                    mm.NAMA_MATKUL LIKE '%$search%'
                    OR
                    mm.SKS LIKE '%$search%'
                    OR
                    mm.JUMLAH_PERTEMUAN LIKE '%$search%'
                    OR
                    mp.PRODI LIKE '%$search%'
                )
            ";
        }

        if (!empty($search_per_colomn)) {
            foreach ($search_per_colomn as $key => $value) {
                $key = $key == 'IS_ACTIVE' ? 'mm.IS_ACTIVE' : $key;
                $key = $key == 'PRODI' ? 'mp.ID_PRODI' : $key;
                $value = $key == 'mm.IS_ACTIVE' ? ($value == 'Aktif' ? 1 : 0) : $value;
                $conditions[] = "$key LIKE '%$value%'";
            }
        }

        $conditionWhere = "";
        if (!empty($conditions)) {
            $conditionWhere = "WHERE " . implode(" AND ", $conditions);
        }

        $results = DB::select("
            SELECT
                mm.* ,
                mp.ID_PRODI,
                mp.JENJANG,
                mp.PRODI
            FROM
                md_matkul mm
            LEFT JOIN md_prodi mp ON 
                mp.ID_PRODI = mm.ID_PRODI 
            $conditionWhere
            ORDER BY
                $valOrder $orderType
            LIMIT 
                $start, $perPage
        ");


        $Tot = DB::selectOne("
            SELECT 
                COUNT(mm.KODE_MATKUL) AS DataTrans
            FROM
                md_matkul mm
            LEFT JOIN md_prodi mp ON 
                mp.ID_PRODI = mm.ID_PRODI 
                $conditionWhere
        ");

        return [
            'DATA' => $results,
            'TOTAL_DATA' => $Tot
        ];
    }

    public static function get_all_matkul()
    {
        return DB::select("
            SELECT
                *
            FROM
                md_matkul mm
            WHERE
                mm.IS_DELETE = 0
                AND
                mm.IS_ACTIVE = 1
        ");
    }

    public static function get_matkul_with_capaian()
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        return DB::select("
            SELECT 
                mm.* ,
                mp.JENJANG ,
                GROUP_CONCAT(tcd.ID_CAPAIAN_DETAIL) AS ID_CAPAIAN_DETAIL ,
                MAX(mk.KURIKULUM) AS KURIKULUM
            FROM 
                md_matkul mm 
            LEFT JOIN tb_capaian_detail tcd ON
                tcd.KODE_MATKUL = mm.KODE_MATKUL 
            LEFT JOIN md_kurikulum mk ON
                mk.ID_KURIKULUM = tcd.ID_KURIKULUM
            LEFT JOIN md_prodi mp ON
                mm.ID_PRODI = mp.ID_PRODI
            GROUP BY 
                mm.KODE_MATKUL ,
                tcd.ID_KURIKULUM
            HAVING 
                ID_CAPAIAN_DETAIL IS NOT NULL
        ");
    }

    public static function get_detail_matkul($kodeKelas, $kodeMatkul,  $idSemester, $kodeSemester)
    {
        $conditions = [];
        $conditionsKelas = '';
        !empty($kodeSemester) && $conditions[] = "mpk.KODE_SEMESTER = '$kodeSemester'";
        !empty($kodeMatkul) && $conditions[] = "tcd.KODE_MATKUL = '$kodeMatkul'";
        !empty($kodeKelas) && $conditionsKelas = "WHERE trj.KODE_KELAS = '$kodeKelas'";


        $conditionWhere = "";
        if (!empty($conditions)) {
            $conditionWhere = "WHERE " . implode(" AND ", $conditions);
        }

        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $mainQuery = DB::selectOne("
            SELECT 	
                mm.KODE_MATKUL ,
                mm.NAMA_MATKUL ,
                mm.SKS ,
                mm.JUMLAH_PERTEMUAN ,
                mp.PRODI ,
                tcd.KODE_CAPAIAN ,
                mk.KURIKULUM ,
                ms.ID_SEMESTER ,
                ms.SEMESTER ,
                ms.TAHUN ,
                mpk.ID_DETSEM ,
                mpk.KODE_SEMESTER ,
                trj.HARI ,
                trj.JAM_MULAI ,
                trj.SKS_MATKUL ,
                trj.JAM_SELESAI ,
                trj.ID_RUANGAN ,
                trj.NAMA_RUANGAN ,
                trj.KAPASITAS_RUANGAN ,
                trj.ID_METODE,
                trj.KODE,
                SUM(tcd.TOTAL_BOBOT) AS TOTAL_BOBOT
            FROM 
                md_matkul mm
            LEFT JOIN md_prodi mp ON 
                mp.ID_PRODI = mm.ID_PRODI 
            LEFT JOIN tb_capaian_detail tcd ON
                tcd.KODE_MATKUL = mm.KODE_MATKUL      
            LEFT JOIN md_kurikulum mk ON 
                mk.ID_KURIKULUM = tcd.ID_KURIKULUM           
            LEFT JOIN mapping_peta_kurikulum mpk ON
                mpk.KODE_MATKUL = tcd.KODE_MATKUL 
                AND
                mpk.ID_KURIKULUM = tcd.ID_KURIKULUM  
            LEFT JOIN md_semester ms ON 
                mpk.ID_SEMESTER = ms.ID_SEMESTER
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
                $conditionsKelas
                GROUP BY 
                    trj.ID_DETSEM
            ) trj ON 
                trj.ID_DETSEM = mpk.ID_DETSEM 
            $conditionWhere
            GROUP BY 
                mm.KODE_MATKUL
        ");
        return $mainQuery;
    }
}
