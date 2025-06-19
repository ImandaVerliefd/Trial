<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Mahasiswa extends Model
{
    protected $table = 'md_mahasiswa';
    protected $primaryKey = 'KODE_MAHASISWA';
    public $incrementing = false;
    public $timestamps = false;

    public static function get_data_datatable($orderType, $valOrder, $search, $start, $perPage, $search_per_colomn)
    {
        if (!empty($search)) {
            $conditions[] = "
                (
                    mm.NIM LIKE '%$search%'
                    OR
                    mm.NAMA_MAHASISWA LIKE '%$search%'
                    OR
                    mm.PRODI LIKE '%$search%'
                )
            ";
        }

        if (!empty($search_per_colomn)) {
            foreach ($search_per_colomn as $key => $value) {
                $key = $key == 'IS_ACTIVE' ? 'mm.IS_ACTIVE' : $key;
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
                mm.*
            FROM
                md_mahasiswa mm
                $conditionWhere
            ORDER BY
                $valOrder $orderType
            LIMIT 
                $start, $perPage
        ");

        $Tot = DB::selectOne("
            SELECT 
                COUNT(mm.KODE_MAHASISWA) AS DataTrans
            FROM
                md_mahasiswa mm
                $conditionWhere
        ");

        return [
            'DATA' => $results,
            'TOTAL_DATA' => $Tot
        ];
    }

    public static function get_jadwal_mahasiswa()
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        $semester_aktif = Semester::whereRaw('CURDATE() BETWEEN START_SEMESTER AND END_SEMESTER')->first()->toArray();
        $kode_mahasiswa = session('user')[0]["kode_user"];

        return DB::select("
            SELECT
                tkm.KODE_KELAS ,
                tkm.NAMA_KELAS ,
                mp.PRODI ,
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
                tp.KODE_KELAS AS RUANGAN_TERPILIH ,
                mpk.START_DATE ,
                mpk.END_DATE ,
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
            LEFT JOIN tb_kelas_matkul tkm ON
                tkm.KODE_KELAS = tp.KODE_KELAS
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
                tp.KODE_MAHASISWA = ?
            AND
                tp.ID_SEMESTER = ?
            AND
                tp.STATUS_VERIF = '1'
            GROUP BY
                mpk.NAMA_MATKUL, tkm.KODE_KELAS;
        ", [$kode_mahasiswa, $semester_aktif['ID_SEMESTER']]);
    }

    public static function get_jadwal_ujian_mahasiswa() 
    {

    }


    public static function get_tahun_ajaran()
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        return DB::Select("
            SELECT
                ms.ID_SEMESTER,
                ms.SEMESTER
            FROM
                tp_perwalian tp
            LEFT JOIN md_semester ms ON
                ms.ID_SEMESTER = tp.ID_SEMESTER
            WHERE
                tp.KODE_MAHASIWA = ?
        ", [session('user')[0]['kode_user']]);
    }

    public static function get_khs_mahasiswa($kode_semester)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $kode_mahasiswa = session('user')[0]["kode_user"];

        $condition = !empty($kode_semester) ? "AND ms.ID_SEMESTER = '$kode_semester'" : '';

        return DB::Select("
            SELECT
                ms.ID_SEMESTER,
                ms.SEMESTER
            FROM
                tb_perwalian
            LEFT JOIN md_semester ms ON
                ms.ID_SEMESTER = tp.ID_SEMESTER
            WHERE
                tp.KODE_MAHASISWA = ?
            $condition
            GROUP BY
                tp.ID_SEMESTER
            ORDER BY
                tp.ID_SEMESTER ASC
        ", [session('user')[0]['kode_user']]);
    }
}
