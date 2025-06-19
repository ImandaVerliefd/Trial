<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class KRS extends Model
{
    protected $table = 'tb_perwalian';
    protected $primaryKey = 'ID_PERWALIAN';
    public $incrementing = false;
    public $timestamps = false;

    public static function get_data_perwalian()
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $mahasiswa = Mahasiswa::whereRaw("KODE_MAHASISWA = '" . session('user')[0]['kode_user'] . "' ")->first();

        return DB::select("
            SELECT
                tpm.KODE_PAKET ,
            	mpk.ID_DETSEM ,
            	mpk.KODE_MATKUL ,   
            	mpk.NAMA_MATKUL ,
                tkm.KODE_KELAS ,
                tkm.NAMA_KELAS ,
                trj.HARI ,
                trj.JAM_MULAI ,
                trj.JAM_SELESAI ,
                trj.ID_RUANGAN ,
                trj.NAMA_RUANGAN ,
                trj.KAPASITAS_RUANGAN ,
                trj.ID_METODE,
                trj.KODE ,
                trj.METODE ,
            	GROUP_CONCAT(DISTINCT md.NAMA_DOSEN SEPARATOR ', ') AS NAMA_DOSEN 
            FROM
            	tb_paket_matkul tpm
            LEFT JOIN mapping_peta_kurikulum mpk ON
            	mpk.KODE_MATKUL = tpm.KODE_MATKUL
            AND
            	mpk.KODE_SEMESTER = tpm.KODE_SEMESTER
            AND
            	mpk.ID_TAHUN_AJAR = tpm.ID_TAHUN_AJAR
            LEFT JOIN mapping_subcpmk msc ON
            	msc.ID_DETSEM = mpk.ID_DETSEM
            LEFT JOIN md_dosen md ON
            	msc.KODE_DOSEN = md.KODE_DOSEN
            LEFT JOIN md_matkul mmk ON
            	mmk.KODE_MATKUL = mpk.KODE_MATKUL
            LEFT JOIN md_semester ms ON
            	ms.ID_SEMESTER = mpk.ID_SEMESTER
            LEFT JOIN md_prodi mp ON
            	mp.ID_PRODI = mpk.ID_PRODI
            LEFT JOIN tb_kelas_matkul tkm ON
                tkm.ID_DETSEM = mpk.ID_DETSEM
            LEFT JOIN (
                SELECT 
                    trj.ID_DETSEM,
                    trj.KODE_KELAS,
                    GROUP_CONCAT(trj.KODE SEPARATOR ';') AS KODE,
                    GROUP_CONCAT(trj.HARI SEPARATOR ';') AS HARI,
                    GROUP_CONCAT(trj.JAM_MULAI SEPARATOR ';') AS JAM_MULAI,
                    GROUP_CONCAT(trj.JAM_SELESAI SEPARATOR ';') AS JAM_SELESAI,
                    GROUP_CONCAT(trj.ID_RUANGAN SEPARATOR ';') AS ID_RUANGAN,
                    GROUP_CONCAT(trj.NAMA_RUANGAN SEPARATOR ';') AS NAMA_RUANGAN,
                    GROUP_CONCAT(trj.KAPASITAS_RUANGAN SEPARATOR ';') AS KAPASITAS_RUANGAN,
                    GROUP_CONCAT(trj.ID_METODE SEPARATOR ';') AS ID_METODE, 
                    GROUP_CONCAT(mma.METODE SEPARATOR ';') AS METODE 
                FROM 
                    tb_ruang_jadwal trj 
                LEFT JOIN md_metode_ajar mma ON 
                    mma.ID_METODE = trj.ID_METODE 
                GROUP BY 
                    trj.KODE_KELAS
            ) trj ON 
                trj.KODE_KELAS = tkm.KODE_KELAS
            LEFT JOIN (
                SELECT
                    mta.ID_TAHUN_AJAR
                FROM
                    md_tahun_ajaran mta 
                LEFT JOIN md_mahasiswa mm ON
                    mm.TAHUN_MASUK = mta.ID_TAHUN_AJAR
            ) mta ON
                mta.ID_TAHUN_AJAR = tpm.ID_TAHUN_AJAR 
            WHERE
                tkm.KODE_KELAS IS NOT NULL
            AND
                mpk.IS_DELETE IS NULL
            AND
                tkm.IS_DELETE IS NULL
            AND
                tpm.KODE_SEMESTER = ?
            AND
                tpm.ID_PRODI = ?
            GROUP BY
	            tpm.KODE_PAKET ,
	            tkm.KODE_KELAS ,
	            tpm.KODE_MATKUL ,
	            tpm.KODE_SEMESTER
        ", [$mahasiswa->SEMESTER_ACTIVE, $mahasiswa->ID_PRODI]);
    }

    public static function checking_kelas_kapasitas($ID_DETSEM, $KODE_KELAS)
    {
        $data = DB::selectone("
            SELECT
            	trj.KAPASITAS_RUANGAN
            FROM
                mapping_peta_kurikulum mpk 
            LEFT JOIN tb_kelas_matkul tkm ON 
            	tkm.ID_DETSEM = mpk.ID_DETSEM
            LEFT JOIN tb_ruang_jadwal trj ON
            	tkm.KODE_KELAS = trj.KODE_KELAS
            WHERE
            	mpk.ID_DETSEM = ?
            AND
            	tkm.KODE_KELAS = ?
        ", [$ID_DETSEM, $KODE_KELAS]);

        return $data->KAPASITAS_RUANGAN ?? null;
    }

    public static function checking_kelas($ID_DETSEM, $KODE_KELAS)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        return DB::selectone("
            SELECT
                tp.ID_DETSEM,
                tp.KODE_KELAS,
                COUNT(tp.ID_DETSEM) AS total_students
            FROM
                tb_perwalian tp
            LEFT JOIN mapping_peta_kurikulum mpk ON
                mpk.ID_DETSEM = tp.ID_DETSEM
            WHERE
                tp.ID_DETSEM = ?
            AND
                tp.KODE_KELAS = ?
            GROUP BY
                tp.KODE_KELAS
        ", [$ID_DETSEM, $KODE_KELAS]);
    }

    public static function get_all_kelas()
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $mahasiswa = Mahasiswa::whereRaw("EMAIL_MAHASISWA = '" . session('user')[0]['email'] . "' ")->first();

        return DB::select("
            (
                SELECT
                    tp.ID_DETSEM,
                    tp.KODE_KELAS,
                    COUNT(tp.ID_DETSEM) AS total_students
                FROM
                    tb_perwalian tp
                RIGHT JOIN mapping_peta_kurikulum mpk ON
                    mpk.ID_DETSEM = tp.ID_DETSEM
                WHERE
                    mpk.ID_PRODI = ?
                AND
                    mpk.KODE_SEMESTER = ?
                GROUP BY
                    tp.KODE_KELAS,
                    tp.ID_DETSEM
            ) UNION ALL (
                SELECT
                    tp.ID_DETSEM,
                    tp.KODE_KELAS,
                    COUNT(tp.ID_DETSEM) AS total_students
                FROM
                    tb_perwalian tp
                RIGHT JOIN mapping_peta_kurikulum mpk ON
                    mpk.ID_DETSEM = tp.ID_DETSEM
                WHERE
                    mpk.IS_UMUM = 1
                OR
                    mpk.IS_LINTAS_PRODI = 1
                AND 
                    mpk.ID_LINPROD like '%" . $mahasiswa->ID_PRODI . "%'
                AND
                    mpk.KODE_SEMESTER = ?
            )
        ", [$mahasiswa->ID_PRODI, $mahasiswa->SEMESTER_ACTIVE, $mahasiswa->SEMESTER_ACTIVE]);
    }

    public static function get_data_validasi($kode_mahasiswa)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $semester_aktif = Semester::whereRaw('CURDATE() BETWEEN START_SEMESTER AND END_SEMESTER')->first();

        return DB::select("
            SELECT
                tp.ID_DETSEM,
                tp.KODE_KELAS,
                tp.STATUS_VERIF,
                mpk.KODE_MATKUL,
                mmk.NAMA_MATKUL,
                mmk.SKS,
                mr.NAMA_RUANGAN
            FROM
                tb_perwalian tp
            LEFT JOIN md_mahasiswa mm ON
                mm.KODE_MAHASISWA = tp.KODE_MAHASISWA
            LEFT JOIN mapping_peta_kurikulum mpk ON
                mpk.ID_DETSEM = tp.ID_DETSEM
            LEFT JOIN tb_kelas_matkul tkm ON
                tkm.ID_DETSEM = mpk.ID_DETSEM
            LEFT JOIN tb_ruang_jadwal trj ON
                trj.KODE_KELAS = tkm.KODE_KELAS
            LEFT JOIN md_ruangan mr ON
                mr.ID_RUANGAN = trj.ID_RUANGAN
            LEFT JOIN md_matkul mmk ON
                mmk.KODE_MATKUL = mpk.KODE_MATKUL
            WHERE
                mm.KODE_MAHASISWA = ?
            AND
                tp.ID_SEMESTER = ?
            GROUP BY
                tp.ID_DETSEM, 
                tp.KODE_KELAS
        ", [$kode_mahasiswa, $semester_aktif->ID_SEMESTER]);    
    }
}
