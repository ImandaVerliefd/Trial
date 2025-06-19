<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Dosen extends Model
{
    protected $table = 'md_dosen';
    protected $primaryKey = 'KODE_DOSEN';
    public $incrementing = false;
    public $timestamps = false;

    public static function get_data_datatable($orderType, $valOrder, $search, $start, $perPage, $search_per_colomn)
    {
        if (!empty($search)) {
            $conditions[] = "
                (
                    md.NIP_DOSEN LIKE '%$search%'
                    OR
                    md.NAMA_DOSEN LIKE '%$search%'
                    OR
                    md.JABATAN LIKE '%$search%'
                )
            ";
        }

        if (!empty($search_per_colomn)) {
            foreach ($search_per_colomn as $key => $value) {
                $key = $key == 'IS_ACTIVE' ? 'IS_ACTIVE' : $key;
                $value = $key == 'IS_ACTIVE' ? ($value == 'Aktif' ? 1 : 0) : $value;
                $conditions[] = "$key LIKE '%$value%'";
            }
        }

        $conditionWhere = "";
        if (!empty($conditions)) {
            $conditionWhere = "WHERE " . implode(" AND ", $conditions);
        }

        $results = DB::select("
            SELECT
                md.*
            FROM
                md_dosen md
                $conditionWhere
            ORDER BY
                $valOrder $orderType
            LIMIT 
                $start, $perPage
        ");

        $Tot = DB::selectOne("
            SELECT 
                COUNT(md.KODE_DOSEN) AS DataTrans
            FROM
                md_dosen md
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
                md.*
            FROM
                md_dosen md
        ");
    }

    public static function get_jadwal_dosen($id, $dosen)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $semester_aktif = Semester::whereRaw('CURDATE() BETWEEN START_SEMESTER AND END_SEMESTER')->first()->toArray(); // Get Tahun Ajaran

        $condition = [];
        $condition[] = $id == "" ? "tp.ID_SEMESTER = '{$semester_aktif['ID_SEMESTER']}'" : "tp.ID_SEMESTER = '$id'";
        $condition[] = $dosen == true ? "EXISTS (
                    SELECT 1
                    FROM
                        mapping_subcpmk msc2
                    WHERE
                        msc2.ID_DETSEM = mpk.ID_DETSEM
                    AND
                        msc2.KODE_DOSEN = '" . session('user')[0]['kode_user'] . "'
                )" : "";

        $conditionWhere = "";
        if (!empty($condition)) {
            $conditionWhere = implode(" AND ", $condition);
        }

        return DB::select("
            SELECT
                ms.SEMESTER ,
                mp.PRODI ,
                tkm.KODE_KELAS ,
                tkm.NAMA_KELAS ,
                mpk.ID_DETSEM ,
                mpk.KODE_MATKUL ,
                mpk.NAMA_MATKUL ,
                trj.HARI ,
                trj.JAM_MULAI ,
                trj.SKS_MATKUL ,
                trj.JAM_SELESAI ,
                trj.ID_RUANGAN ,
                trj.NAMA_RUANGAN ,
                trj.KAPASITAS_RUANGAN ,
                trj.ID_METODE ,
                trj.KODE ,
                ms.START_SEMESTER ,
                ms.END_SEMESTER ,
                mm.SKS ,
                GROUP_CONCAT(DISTINCT md.NAMA_DOSEN ORDER BY md.NAMA_DOSEN SEPARATOR ', ') AS NAMA_DOSEN
            FROM
                tb_perwalian tp
            LEFT JOIN mapping_peta_kurikulum mpk ON
                mpk.ID_DETSEM = tp.ID_DETSEM
            LEFT JOIN mapping_subcpmk msc ON
                msc.ID_DETSEM = mpk.ID_DETSEM
            LEFT JOIN md_dosen md ON
                md.KODE_DOSEN = msc.KODE_DOSEN
            LEFT JOIN md_matkul mm ON
                mm.KODE_MATKUL = mpk.KODE_MATKUL
            LEFT JOIN md_prodi mp ON
                mp.ID_PRODI = mpk.ID_PRODI
            LEFT JOIN md_semester ms ON
                ms.ID_SEMESTER = mpk.ID_SEMESTER
            LEFT JOIN tb_kelas_matkul tkm ON
                tkm.ID_DETSEM = mpk.ID_DETSEM
            LEFT JOIN (
                SELECT
                    trj.KODE_KELAS,
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
                    trj.ID_DETSEM, trj.KODE_KELAS
            ) trj ON 
                trj.KODE_KELAS = tkm.KODE_KELAS
            WHERE
                $conditionWhere
            GROUP BY
                mpk.NAMA_MATKUL, tkm.KODE_KELAS;
        ");
    }

    public static function get_all_jadwal()
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $semester_aktif = Semester::whereRaw('CURDATE() BETWEEN START_SEMESTER AND END_SEMESTER')->first()->toArray(); // Get Tahun Ajaran

        $results = DB::select("
            SELECT
                ms.SEMESTER,
                mp.PRODI,
                mpk.KODE_MATKUL,
                mpk.NAMA_MATKUL,
                tkm.NAMA_KELAS ,
                trj.HARI ,
                trj.JAM_MULAI ,
                trj.SKS_MATKUL ,
                trj.JAM_SELESAI ,
                trj.ID_RUANGAN ,
                trj.NAMA_RUANGAN ,
                trj.KAPASITAS_RUANGAN ,
                trj.ID_METODE,
                trj.KODE ,
                ms.START_SEMESTER,
                ms.END_SEMESTER,
                mm.SKS,
                GROUP_CONCAT(DISTINCT md.NAMA_DOSEN ORDER BY md.NAMA_DOSEN SEPARATOR ', ') AS NAMA_DOSEN
            FROM
                tb_perwalian tp
            LEFT JOIN mapping_peta_kurikulum mpk ON
                mpk.ID_DETSEM = tp.ID_DETSEM
            LEFT JOIN mapping_subcpmk msc ON
                msc.ID_DETSEM = mpk.ID_DETSEM
            LEFT JOIN md_dosen md ON
                md.KODE_DOSEN = msc.KODE_DOSEN
            LEFT JOIN md_matkul mm ON
                mm.KODE_MATKUL = mpk.KODE_MATKUL
            LEFT JOIN md_prodi mp ON
                mp.ID_PRODI = mpk.ID_PRODI
            LEFT JOIN md_semester ms ON
                ms.ID_SEMESTER = mpk.ID_SEMESTER
            LEFT JOIN tb_kelas_matkul tkm ON
                tkm.ID_DETSEM = mpk.ID_DETSEM
            LEFT JOIN (
                SELECT
                    trj.KODE_KELAS,
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
                    trj.ID_DETSEM,
                    trj.KODE_KELAS
            ) trj ON 
                trj.KODE_KELAS = tkm.KODE_KELAS 
            WHERE
                tp.ID_SEMESTER = '{$semester_aktif['ID_SEMESTER']}'
            GROUP BY
                mpk.NAMA_MATKUL, tkm.NAMA_KELAS
        ");

        $tot_data_query = DB::selectOne("
            SELECT COUNT(*) AS DataTrans
            FROM (
                SELECT mpk.NAMA_MATKUL
                FROM tb_perwalian tp
                LEFT JOIN mapping_peta_kurikulum mpk ON mpk.ID_DETSEM = tp.ID_DETSEM
                LEFT JOIN mapping_subcpmk msc ON msc.ID_DETSEM = mpk.ID_DETSEM
                LEFT JOIN md_dosen md ON md.KODE_DOSEN = msc.KODE_DOSEN
                WHERE tp.ID_SEMESTER = '{$semester_aktif['ID_SEMESTER']}'
                GROUP BY mpk.NAMA_MATKUL, md.NAMA_DOSEN
            ) AS subquery
        ");

        $tot_data = $tot_data_query->DataTrans ?? 0;

        return [
            'DATA'        => $results,
            'TOTAL_DATA'  => $tot_data
        ];
    }
}
