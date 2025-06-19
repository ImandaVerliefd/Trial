<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Semester extends Model
{
    protected $table = 'md_semester';
    protected $primaryKey = 'ID_SEMESTER';
    public $incrementing = false;
    public $timestamps = false;

    public static function get_all_semester()
    {
        return DB::select("
            SELECT
                *
            FROM
                md_semester ms
            WHERE
                ms.IS_DELETE IS NULL
        ");
    }

    public static function get_history_kehadiran()
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        return DB::select("
            SELECT
                ms.SEMESTER ,
                mp.PRODI ,
                tkm.KODE_KELAS ,
                tkm.NAMA_KELAS ,
                mpk.ID_DETSEM ,
                mpk.KODE_MATKUL ,
                mpk.NAMA_MATKUL ,
                mm.SKS
            FROM
                tb_perwalian tp
            LEFT JOIN mapping_peta_kurikulum mpk ON
                mpk.ID_DETSEM = tp.ID_DETSEM
            LEFT JOIN md_matkul mm ON
                mm.KODE_MATKUL = mpk.KODE_MATKUL
            LEFT JOIN md_prodi mp ON
                mp.ID_PRODI = mpk.ID_PRODI
            LEFT JOIN md_semester ms ON
                ms.ID_SEMESTER = mpk.ID_SEMESTER
            LEFT JOIN tb_kelas_matkul tkm ON
                tkm.ID_DETSEM = mpk.ID_DETSEM
            WHERE
                EXISTS (
                    SELECT 1
                    FROM
                        mapping_subcpmk msc2
                    WHERE
                        msc2.ID_DETSEM = mpk.ID_DETSEM
                    AND
                        msc2.KODE_DOSEN = '" . session('user')[0]['kode_user'] . "'
                )
            GROUP BY
                mpk.NAMA_MATKUL, tkm.KODE_KELAS;
        ");
    }

    public static function get_data_datatable($orderType, $valOrder, $search, $start, $perPage)
    {
        if (!empty($search)) {
            $conditions[] = "
                (
                    ms.SEMESTER LIKE '%$search%'
                    OR
                    ms.TAHUN LIKE '%$search%'
                    OR
                    mk.KURIKULUM LIKE '%$search%'
                )
            ";
        }

        $conditions[] = "ms.IS_DELETE IS NULL";

        $conditionWhere = "";
        if (!empty($conditions)) {
            $conditionWhere = "WHERE " . implode(" AND ", $conditions);
        }

        $results = DB::select("
            SELECT
                ms.* ,
                mk.KURIKULUM
            FROM
                md_semester ms
            LEFT JOIN md_kurikulum mk ON
                mk.ID_KURIKULUM = ms.ID_KURIKULUM
                $conditionWhere
            ORDER BY
                $valOrder $orderType
            LIMIT 
                $start, $perPage
        ");


        $Tot = DB::selectOne("
            SELECT
                COUNT(ms.ID_SEMESTER) AS DataTrans
            FROM
                md_semester ms
            LEFT JOIN md_kurikulum mk ON
                mk.ID_KURIKULUM = ms.ID_KURIKULUM
                $conditionWhere
        ");

        return [
            'DATA' => $results,
            'TOTAL_DATA' => $Tot
        ];
    }
}
