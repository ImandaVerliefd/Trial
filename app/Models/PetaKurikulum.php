<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PetaKurikulum extends Model
{
    protected $table = 'mapping_peta_kurikulum';
    protected $primaryKey = 'ID_DETSEM';
    public $incrementing = false;
    public $timestamps = false;

    public static function get_data_datatable($orderType, $valOrder, $search, $start, $perPage, $prodi)
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
                    mk.KURIKULUM LIKE '%$search%'
                     OR
                    mp.PRODI LIKE '%$search%'
                )
            ";
        }

        $conditions[] = "mpk.IS_DELETE IS NULL";
        $conditions[] = "mp.ID_PRODI = '$prodi'";

        $conditionWhere = "";
        if (!empty($conditions)) {
            $conditionWhere = "WHERE " . implode(" AND ", $conditions);
        }

        $results = DB::select("
            SELECT
                mpk.ID_DETSEM ,
                mpk.ID_SEMESTER ,
                mpk.ID_KURIKULUM ,
                mpk.KODE_SEMESTER ,
                mpk.START_DATE ,
                mpk.END_DATE ,
                mpk.RUMPUN_MATKUL ,
                mpk.KATEGORI_MATKUL ,
                mpk.DESKRIPSI_MATKUL ,
                mm.IS_UMUM ,
                mm.IS_LINTAS_PRODI ,
                mm.ID_LINPROD ,
                mm.IS_LAPANGAN ,
                mm.KODE_MATKUL ,
                mm.NAMA_MATKUL ,
                mm.JUMLAH_PERTEMUAN ,
                mm.SKS ,
                mm.IS_ACTIVE ,
                mm.ID_PRODI ,
                ms.SEMESTER ,
                ms.TAHUN ,
                mk.KURIKULUM ,
                mp.PRODI
            FROM
                mapping_peta_kurikulum mpk
            LEFT JOIN md_matkul mm ON 
                mpk.KODE_MATKUL = mm.KODE_MATKUL 
            LEFT JOIN md_kurikulum mk ON 
                mk.ID_KURIKULUM = mpk.ID_KURIKULUM 
            LEFT JOIN md_prodi mp ON 
                mp.ID_PRODI = mm.ID_PRODI 
            LEFT JOIN md_semester ms ON 
                ms.ID_SEMESTER = mpk.ID_SEMESTER
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
                mapping_peta_kurikulum mpk
            LEFT JOIN md_matkul mm ON 
                mpk.KODE_MATKUL = mm.KODE_MATKUL 
            LEFT JOIN md_kurikulum mk ON 
                mk.ID_KURIKULUM = mpk.ID_KURIKULUM 
            LEFT JOIN md_prodi mp ON 
                mp.ID_PRODI = mm.ID_PRODI 
            LEFT JOIN md_semester ms ON 
                ms.ID_SEMESTER = mpk.ID_SEMESTER
                $conditionWhere
        ");

        return [
            'DATA' => $results,
            'TOTAL_DATA' => $Tot
        ];
    }

    public static function get_data_by_matkulsem($kodeMatkul, $idSem, $idKurikulum)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        return DB::select("
            SELECT
                *
            FROM
                mapping_peta_kurikulum mpk
            WHERE
                mpk.KODE_MATKUL = '$kodeMatkul'
                AND
                mpk.ID_SEMESTER = '$idSem'
                AND
                mpk.ID_KURIKULUM = '$idKurikulum'
        ");
    }

    public static function get_data_all_mapping_matkul()
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        return DB::select("
            SELECT
                mpk.* ,                
                trj.HARI ,
                trj.JAM_MULAI ,
                trj.SKS_MATKUL ,
                trj.JAM_SELESAI ,
                trj.ID_RUANGAN ,
                trj.NAMA_RUANGAN ,
                trj.KAPASITAS_RUANGAN ,
                trj.ID_METODE,
                trj.KODE ,
            FROM
                mapping_peta_kurikulum mpk 
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
        ");
    }
}
