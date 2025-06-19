<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class Kelas extends Model
{
    public static function get_detail_data_kelas($id_detsem, $kode_kelas, $id_kehadiran)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        $detail = DB::selectone("
            SELECT
                mpk.* ,
                ms.SEMESTER ,
                mm.SKS ,
                mm.JUMLAH_PERTEMUAN ,
                tkm.KODE_KELAS ,
                tkm.NAMA_KELAS ,
                tkm.KAPASITAS ,
                trj.KODE ,
                trj.HARI ,
                trj.JAM_MULAI ,
                trj.JAM_SELESAI ,
                trj.ID_RUANGAN ,
                trj.NAMA_RUANGAN ,
                GROUP_CONCAT(DISTINCT md.NAMA_DOSEN SEPARATOR ';') AS NAMA_DOSEN
            FROM
                mapping_peta_kurikulum mpk
            LEFT JOIN tb_kelas_matkul tkm ON
                tkm.ID_DETSEM = mpk.ID_DETSEM
            LEFT JOIN mapping_subcpmk msc ON
                msc.ID_DETSEM = mpk.ID_DETSEM
            LEFT JOIN md_dosen md ON
                md.KODE_DOSEN = msc.KODE_DOSEN
            LEFT JOIN md_semester ms ON
                ms.ID_SEMESTER = mpk.ID_SEMESTER
            LEFT JOIN md_matkul mm ON
                mm.KODE_MATKUL = mpk.KODE_MATKUL
            LEFT JOIN (
                SELECT 
                    trj.ID_DETSEM,
                    trj.KODE_KELAS,
                    GROUP_CONCAT(trj.KODE SEPARATOR ';') AS KODE,
                    GROUP_CONCAT(trj.HARI SEPARATOR ';') AS HARI,
                    GROUP_CONCAT(trj.JAM_MULAI SEPARATOR ';') AS JAM_MULAI,
                    GROUP_CONCAT(trj.JAM_SELESAI SEPARATOR ';') AS JAM_SELESAI,
                    GROUP_CONCAT(trj.ID_RUANGAN SEPARATOR ';') AS ID_RUANGAN,
                    GROUP_CONCAT(trj.NAMA_RUANGAN SEPARATOR ';') AS NAMA_RUANGAN
                FROM 
                    tb_ruang_jadwal trj 
                LEFT JOIN md_metode_ajar mma ON 
                    mma.ID_METODE = trj.ID_METODE 
                GROUP BY 
                    trj.KODE_KELAS
            ) trj ON 
                trj.KODE_KELAS = tkm.KODE_KELAS
            WHERE 
                mpk.ID_DETSEM = ?
            AND
                tkm.KODE_KELAS = ?
        ", [$id_detsem, $kode_kelas]);

        $condition = !empty($id_kehadiran) ? 'AND tla.ID_KEHADIRAN = ' . $id_kehadiran : "";

        $mahasiswa = DB::select("
            SELECT
                mm.KODE_MAHASISWA,
                mm.NIM,
                mm.NAMA_MAHASISWA,
                tla.STATUS
            FROM
                tb_perwalian tp
            LEFT JOIN md_mahasiswa mm ON
                tp.KODE_MAHASISWA = mm.KODE_MAHASISWA
            LEFT JOIN tb_list_absensi tla ON
                tla.KODE_MAHASISWA = mm.KODE_MAHASISWA
            WHERE 
                tp.ID_DETSEM = ?
            AND
                tp.KODE_KELAS = ?
            AND
                tp.STATUS_VERIF = 1
            $condition
            GROUP BY mm.KODE_MAHASISWA
        ", [$id_detsem, $kode_kelas]);

        $tot_pertemuan = DB::Selectone("
            SELECT
                count(tk.ID_KEHADIRAN) AS tot_pertemuan
            FROM
                tb_kehadiran tk 
            WHERE 
                tk.ID_DETSEM = ?
            AND
                tk.KODE_KELAS = ?
        ", [$id_detsem, $kode_kelas]);

        return [
            'detail'        => $detail,
            'mahasiswa'     => $mahasiswa,
            'tot_pertemuan' => $tot_pertemuan 
        ];
    }

    public static function check_open_kehadiran($id_detsem, $kode_kelas, $isNull)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        $condition[] = "tk.ID_DETSEM = ?";
        $condition[] = "tk.KODE_KELAS = ?";
        $condition[] = $isNull ? "tba.TANGGAL_PERTEMUAN IS NULL" : "tba.TANGGAL_PERTEMUAN IS NOT NULL";

        $conditionWhere = "";
        if (!empty($condition)) {
            $conditionWhere = "WHERE " . implode(" AND ", $condition);
        }

        return DB::select("
            SELECT
                tk.*
            FROM
                tb_kehadiran tk
            LEFT JOIN tb_berita_acara tba ON
                tba.ID_KEHADIRAN = tk.ID_KEHADIRAN
            $conditionWhere
        ", [$id_detsem, $kode_kelas]);
    }

    public static function daftar_pertemuan($IdDetsem, $KodeKelas)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        return DB::select("
            SELECT
            	tk.ID_KEHADIRAN,
            	tk.SESSION_NUMBER,
            	tba.TANGGAL_PERTEMUAN,
            	tk.START_KELAS,
            	tk.END_KELAS,
            	(
            		SELECT
            			count(tla2.ID)
            		FROM
            			tb_list_absensi tla2
            		LEFT JOIN tb_kehadiran tk2 ON
            			tla2.ID_KEHADIRAN = tk2.ID_KEHADIRAN
            		WHERE
            			tk2.ID_DETSEM = tk.ID_DETSEM
                    AND
            			tk2.ID_KEHADIRAN = tk.ID_KEHADIRAN
                ) AS TOTAL_MAHASISWA,
            	(
            		SELECT
            			count(tla2.ID)
            		FROM
            			tb_list_absensi tla2
            		LEFT JOIN tb_kehadiran tk2 ON
            			tla2.ID_KEHADIRAN = tk2.ID_KEHADIRAN
            		WHERE
            			tla2.STATUS = 1
            		AND
                    	tk2.ID_DETSEM = tk.ID_DETSEM
                    AND
            			tk2.ID_KEHADIRAN = tk.ID_KEHADIRAN
                ) AS ABSENSI
            FROM
            	tb_kehadiran tk
            LEFT JOIN tb_list_absensi tla ON
            	tk.ID_KEHADIRAN = tla.ID_KEHADIRAN
            LEFT JOIN md_mahasiswa mm ON
            	mm.KODE_MAHASISWA = tla.KODE_MAHASISWA
            LEFT JOIN tb_berita_acara tba ON
            	tba.ID_KEHADIRAN = tk.ID_KEHADIRAN
            WHERE
                tk.ID_DETSEM = '" . $IdDetsem . "'
            AND
                tk.KODE_KELAS = '" . $KodeKelas . "'
            GROUP BY
	            tk.ID_KEHADIRAN 
        ");
    }

    public static function rekapitulasi_kehadiran($IdDetsem, $KodeKelas)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        return DB::select("
            SELECT
            	DISTINCT mm.NIM,
            	mm.NAMA_MAHASISWA,
            	(
            	    SELECT
            	    	count(tla2.ID)
            	    FROM
            	    	tb_list_absensi tla2
            	    LEFT JOIN tb_kehadiran tk2 ON
            	    	tla2.ID_KEHADIRAN = tk2.ID_KEHADIRAN
            	    WHERE
            	    	tk2.ID_DETSEM = tk.ID_DETSEM
            	    AND
                        tla2.KODE_MAHASISWA = tla.KODE_MAHASISWA
                ) AS total_kehadiran,
            	(
                	SELECT
                		count(tla2.ID)
                	FROM
                		tb_list_absensi tla2
                	LEFT JOIN tb_kehadiran tk2 ON
                		tla2.ID_KEHADIRAN = tk2.ID_KEHADIRAN
                	WHERE
                		tla2.STATUS = 1
            		AND
                        tk2.ID_DETSEM = tk.ID_DETSEM
            		AND
                        tla2.KODE_MAHASISWA = tla.KODE_MAHASISWA
                ) AS hadir,
            	(
            	    SELECT
            	    	count(tla2.ID)
            	    FROM
            	    	tb_list_absensi tla2
            	    LEFT JOIN tb_kehadiran tk2 ON
            	    	tla2.ID_KEHADIRAN = tk2.ID_KEHADIRAN
            	    WHERE
            	    	tla2.STATUS = 2
            		AND
                        tk2.ID_DETSEM = tk.ID_DETSEM
            		AND
                        tla2.KODE_MAHASISWA = tla.KODE_MAHASISWA
                ) AS sakit,
                (
            	    SELECT
            	    	count(tla2.ID)
            	    FROM
            	    	tb_list_absensi tla2
            	    LEFT JOIN tb_kehadiran tk2 ON
            	    	tla2.ID_KEHADIRAN = tk2.ID_KEHADIRAN
            	    WHERE
            	    	tla2.STATUS = 3
            		AND
                        tk2.ID_DETSEM = tk.ID_DETSEM
            		AND
                        tla2.KODE_MAHASISWA = tla.KODE_MAHASISWA
                ) AS ijin,
            	(
            	    SELECT
            	    	count(tla2.ID)
            	    FROM
            	    	tb_list_absensi tla2
            	    LEFT JOIN tb_kehadiran tk2 ON
            	    	tla2.ID_KEHADIRAN = tk2.ID_KEHADIRAN
            	    WHERE
            	    	tla2.STATUS = 0
            	    AND
                        tk2.ID_DETSEM = tk.ID_DETSEM
            	    AND
                        tla2.KODE_MAHASISWA = tla.KODE_MAHASISWA
                ) AS alpha
            FROM
            	tb_kehadiran tk
            LEFT JOIN tb_list_absensi tla ON
            	tla.ID_KEHADIRAN = tk.ID_KEHADIRAN
            LEFT JOIN md_mahasiswa mm ON
            	mm.KODE_MAHASISWA = tla.KODE_MAHASISWA
            WHERE
                tk.ID_DETSEM = '" . $IdDetsem . "'
            AND
                tk.KODE_KELAS = '" . $KodeKelas . "'
            GROUP BY 
	            mm.NIM,
            	tk.ID_KEHADIRAN
            ORDER BY
            	mm.NIM
        ");
    }

    public static function kehadiran_peserta($IdDetsem, $KodeKelas)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        return DB::select("
            SELECT
                mm.NIM,
                mm.NAMA_MAHASISWA,
                tk.SESSION_NUMBER,
                tla.STATUS
            FROM
                tb_kehadiran tk
            LEFT JOIN tb_list_absensi tla ON
                tk.ID_KEHADIRAN= tla.ID_KEHADIRAN
            LEFT JOIN md_mahasiswa mm ON
                mm.KODE_MAHASISWA = tla.KODE_MAHASISWA
            WHERE
                tk.ID_DETSEM = '" . $IdDetsem . "'
            AND
                tk.KODE_KELAS = '" . $KodeKelas . "'
            ORDER BY
                mm.NIM
        ");
    }

    public static function berita_acara($IdDetsem, $KodeKelas)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        return DB::select("
            SELECT
                tk.SESSION_NUMBER,
                tba.MATERI,
                tba.TANGGAL_PERTEMUAN,
                tba.CATATAN,
                tba.METODE_PEMBELAJARAN,
                tba.METODE_PELAKSANAAN
            FROM
                tb_kehadiran tk
            LEFT JOIN tb_berita_acara tba ON
                tba.ID_KEHADIRAN = tk.ID_KEHADIRAN
            WHERE
                tk.ID_DETSEM = '" . $IdDetsem . "'
            AND
                tk.KODE_KELAS = '" . $KodeKelas . "'
        ");
    }

    public static function getDetail($IdDetsem, $KodeKelas)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        return DB::selectone("
            SELECT
                mp.PRODI ,
                mp.JENJANG ,
                ms.SEMESTER ,
                mpk.ID_DETSEM ,
                mm.ID_PRODI ,
                mpk.ID_SEMESTER ,
                mm.KODE_MATKUL ,
                mm.NAMA_MATKUL ,
                mm.SKS ,
                tkm.NAMA_KELAS ,
                trj.HARI ,
                trj.SKS_MATKUL ,
                trj.JAM_MULAI ,
                trj.JAM_SELESAI ,
                trj.ID_RUANGAN ,
                trj.NAMA_RUANGAN ,
                trj.KAPASITAS_RUANGAN ,
                trj.ID_METODE ,
                trj.METODE ,
                trj.KODE ,
                GROUP_CONCAT(DISTINCT md.NAMA_DOSEN SEPARATOR ';') AS NAMA_DOSEN
            FROM
                mapping_peta_kurikulum mpk
            LEFT JOIN tb_kelas_matkul tkm ON
                mpk.ID_DETSEM = tkm.ID_DETSEM
            LEFT JOIN md_matkul mm ON
                mm.KODE_MATKUL = mpk.KODE_MATKUL
            LEFT JOIN mapping_subcpmk msc ON
                msc.ID_DETSEM = mpk.ID_DETSEM
            LEFT JOIN md_dosen md ON
                md.KODE_DOSEN = msc.KODE_DOSEN
            LEFT JOIN md_prodi mp ON
                mp.ID_PRODI = mm.ID_PRODI
            LEFT JOIN md_semester ms ON
                ms.ID_SEMESTER = mpk.ID_SEMESTER
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
                LEFT JOIN tb_kelas_matkul tkm ON
                    tkm.KODE_KELAS = trj.KODE_KELAS
                WHERE
                    tkm.KODE_KELAS = '" . $KodeKelas . "'
                GROUP BY 
                    trj.ID_DETSEM
            ) trj ON 
                trj.ID_DETSEM = mpk.ID_DETSEM 
            WHERE
                mpk.ID_DETSEM = '" . $IdDetsem . "'
            AND
                tkm.KODE_KELAS = '" . $KodeKelas . "'
        ");
    }

    public static function getSemester($kodeMHS)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        return DB::select("
            SELECT
                ms.ID_SEMESTER,
                ms.SEMESTER
            FROM
                tb_perwalian tp
            LEFT JOIN md_semester ms ON
                ms.ID_SEMESTER = tp.ID_SEMESTER
            WHERE
                tp.KODE_MAHASISWA = '" . $kodeMHS . "'
            GROUP BY
                ms.ID_SEMESTER
        ");
    }

    public static function getMatkul($kodeMHS)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        return DB::select("
            SELECT
                ms.SEMESTER ,
                mpk.ID_DETSEM ,
                mm.KODE_MATKUL ,
                mm.NAMA_MATKUL ,
                mm.SKS ,
                tkm.KODE_KELAS ,
                tkm.NAMA_KELAS
            FROM
                tb_perwalian tp
            LEFT JOIN mapping_peta_kurikulum mpk ON
                mpk.ID_DETSEM = tp.ID_DETSEM
            LEFT JOIN md_matkul mm ON
                mm.KODE_MATKUL = mpk.KODE_MATKUL
            LEFT JOIN tb_kelas_matkul tkm ON
                tkm.KODE_KELAS = tp.KODE_KELAS
            LEFT JOIN md_semester ms ON
                ms.ID_SEMESTER = mpk.ID_SEMESTER
            WHERE
                tp.KODE_MAHASISWA = '" . $kodeMHS . "'
        ");
    }

    public static function getRekapitulasi($IdSemester)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        return DB::Select("
            SELECT
                mm.KODE_MATKUL ,
                mm.NAMA_MATKUL ,
                tkm.NAMA_KELAS ,
                (
                    SELECT
                        count(tla2.ID)
                    FROM
                        tb_list_absensi tla2
                    LEFT JOIN tb_kehadiran tk2 ON
                        tla2.ID_KEHADIRAN = tk2.ID_KEHADIRAN
                    WHERE
                        tk2.ID_DETSEM = tp.ID_DETSEM
                    AND
                    	tk2.KODE_KELAS = tk.KODE_KELAS
                    AND
                    	tla2.KODE_MAHASISWA = tp.KODE_MAHASISWA
                ) AS TOTAL_KEHADIRAN,
                (
                    SELECT
                        count(tla2.ID)
                    FROM
                        tb_list_absensi tla2
                    LEFT JOIN tb_kehadiran tk2 ON
                        tla2.ID_KEHADIRAN = tk2.ID_KEHADIRAN
                    WHERE
                        tla2.STATUS = 1
                    AND
                        tk2.ID_DETSEM = tp.ID_DETSEM
                    AND
                    	tla2.KODE_MAHASISWA = tp.KODE_MAHASISWA
                ) AS KEHADIRAN,
                 (
                    SELECT
                        count(tla2.ID)
                    FROM
                        tb_list_absensi tla2
                    LEFT JOIN tb_kehadiran tk2 ON
                        tla2.ID_KEHADIRAN = tk2.ID_KEHADIRAN
                    WHERE
                        tla2.STATUS = 2
                    AND
                        tk2.ID_DETSEM = tp.ID_DETSEM
                    AND
                    	tla2.KODE_MAHASISWA = tp.KODE_MAHASISWA
                ) AS SAKIT,
                (
                    SELECT
                        count(tla2.ID)
                    FROM
                        tb_list_absensi tla2
                    LEFT JOIN tb_kehadiran tk2 ON
                        tla2.ID_KEHADIRAN = tk2.ID_KEHADIRAN
                    WHERE
                        tla2.STATUS = 3
                    AND
                        tk2.ID_DETSEM = tp.ID_DETSEM
                    AND
                    	tla2.KODE_MAHASISWA = tp.KODE_MAHASISWA
                ) AS IJIN,
                (
                    SELECT
                        count(tla2.ID)
                    FROM
                        tb_list_absensi tla2
                    LEFT JOIN tb_kehadiran tk2 ON
                        tla2.ID_KEHADIRAN = tk2.ID_KEHADIRAN
                    WHERE
                        tla2.STATUS = 0
                    AND
                        tk2.ID_DETSEM = tp.ID_DETSEM
                    AND
                    	tla2.KODE_MAHASISWA = tp.KODE_MAHASISWA
                ) AS ALPHA
            FROM
                tb_perwalian tp
            LEFT JOIN mapping_peta_kurikulum mpk ON
                mpk.ID_DETSEM = tp.ID_DETSEM
            LEFT JOIN md_matkul mm ON
                mm.KODE_MATKUL = mpk.KODE_MATKUL
            LEFT JOIN tb_kelas_matkul tkm ON
                tkm.KODE_KELAS = tp.KODE_KELAS
            LEFT JOIN tb_kehadiran tk ON
                tk.ID_DETSEM = mpk.ID_DETSEM
            LEFT JOIN tb_list_absensi tla ON
                tla.ID_KEHADIRAN = tk.ID_KEHADIRAN
            WHERE
                tp.ID_SEMESTER = ?
            AND
                tp.KODE_MAHASISWA = ?
            GROUP BY
                mm.KODE_MATKUL
        ", [$IdSemester, session('user')[0]['kode_user']]);
    }

    public static function getKehadiran($IdSemester)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        return DB::Select("
            SELECT
                mm2.KODE_MATKUL ,
                mm2.NAMA_MATKUL,
                tkm.NAMA_KELAS , 
                mm.NIM,
                mm.NAMA_MAHASISWA,
                tk.SESSION_NUMBER,
                tla.STATUS AS STATUS_KEHADIRAN
            FROM
                tb_perwalian tp
            LEFT JOIN mapping_peta_kurikulum mpk ON
                mpk.ID_DETSEM = tp.ID_DETSEM
            LEFT JOIN md_mahasiswa mm ON
                mm.KODE_MAHASISWA = tp.KODE_MAHASISWA
            LEFT JOIN md_matkul mm2 ON
                mm2.KODE_MATKUL = mpk.KODE_MATKUL
            LEFT JOIN tb_kelas_matkul tkm ON
                tkm.KODE_KELAS = tp.KODE_KELAS
            LEFT JOIN tb_kehadiran tk ON
                tk.ID_DETSEM = mpk.ID_DETSEM
            LEFT JOIN tb_list_absensi tla ON
            	tla.ID_KEHADIRAN = tk.ID_KEHADIRAN
            WHERE
                tp.ID_SEMESTER = '" . $IdSemester . "'
            AND
                tp.KODE_MAHASISWA = '" . session('user')[0]['kode_user'] . "'
            GROUP BY
                mm2.KODE_MATKUL,
                tk.SESSION_NUMBER
        ");
    }

    public static function getDetailPertemuan($IdDetsem, $KodeKelas)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        return DB::Select("
            SELECT
            	tk.SESSION_NUMBER ,
            	tba.TANGGAL_PERTEMUAN ,
            	tla.STATUS,
            	md.NAMA_DOSEN,
            	tba.MATERI
            FROM
            	tb_perwalian tp
            LEFT JOIN mapping_peta_kurikulum mpk ON
            	mpk.ID_DETSEM = tp.ID_DETSEM
            LEFT JOIN tb_kehadiran tk ON
            	tk.ID_DETSEM = tp.ID_DETSEM
            LEFT JOIN tb_list_absensi tla ON
            	tla.ID_KEHADIRAN = tk.ID_KEHADIRAN
            LEFT JOIN tb_berita_acara tba ON
            	tba.ID_KEHADIRAN = tk.ID_KEHADIRAN
            LEFT JOIN mapping_subcpmk msc ON
            	msc.ID_DETSEM = mpk.ID_DETSEM
            LEFT JOIN
            (
            	SELECT
            		md2.KODE_DOSEN,
            		md2.NAMA_DOSEN
            	FROM
            		md_dosen md2
            	LEFT JOIN mapping_subcpmk msc2 ON
            		msc2.KODE_DOSEN = md2.KODE_DOSEN
            	LEFT JOIN tb_capaian_detail tcd ON
            		tcd.ID_CAPAIAN_DETAIL = msc2.ID_CAPAIAN_DETAIL
            ) md ON
                md.KODE_DOSEN = msc.KODE_DOSEN
            WHERE
                tp.ID_DETSEM = '" . $IdDetsem . "'
            AND
                tp.KODE_KELAS = '" . $KodeKelas . "'
            GROUP BY
                tp.ID_DETSEM,
                tp.KODE_KELAS,
                tk.SESSION_NUMBER
        ");
    }
}
