<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PaketMatkul extends Model
{
    protected $table = 'tb_paket_matkul';
    protected $primaryKey = 'KODE_PAKET';
    public $incrementing = false;
    public $timestamps = false;


    public static function get_data_datatable($orderType, $valOrder, $search, $start, $perPage)
    {
        if (!empty($search)) {
            $conditions[] = "
                (
                    GROUP_CONCAT(mm.NAMA_MATKUL SEPARATOR ', ') LIKE '%$search%'
                )
            ";
        }

        $conditionWhere = "";
        if (!empty($conditions)) {
            $conditionWhere = "WHERE " . implode(" AND ", $conditions);
        }

        $results = DB::select("
            SELECT 
                tpm.KODE_PAKET ,
                MAX(mp.JENJANG) AS JENJANG ,
                GROUP_CONCAT(mm.NAMA_MATKUL SEPARATOR ', ') AS MATKUL ,
                SUM(mm.SKS) AS TOT_SKS ,
                MAX(mm.ID_PRODI) AS ID_PRODI ,
                MAX(mp.PRODI) AS PRODI ,
                MAX(tpm.ID_TAHUN_AJAR) AS ID_TAHUN_AJAR ,
                MAX(tpm.KODE_SEMESTER) AS KODE_SEMESTER ,
                MAX(mta.TAHUN_AJAR) AS TAHUN_AJAR
            FROM 
                tb_paket_matkul tpm 
            LEFT JOIN md_matkul mm ON 
                mm.KODE_MATKUL = tpm.KODE_MATKUL
            LEFT JOIN md_prodi mp ON 
                tpm.ID_PRODI = mp.ID_PRODI 
            LEFT JOIN md_tahun_ajaran mta ON 
                mta.ID_TAHUN_AJAR = tpm.ID_TAHUN_AJAR 
                $conditionWhere
            GROUP BY 
                tpm.KODE_PAKET
            ORDER BY
                $valOrder $orderType
            LIMIT 
                $start, $perPage
        ");


        $Tot = DB::selectOne("
            SELECT 
                COUNT(tpm.KODE_PAKET) AS DataTrans
            FROM 
                tb_paket_matkul tpm 
            LEFT JOIN md_matkul mm ON 
                mm.KODE_MATKUL = tpm.KODE_MATKUL 
            LEFT JOIN md_tahun_ajaran mta ON 
                mta.ID_TAHUN_AJAR = tpm.ID_TAHUN_AJAR 
                $conditionWhere
        ");

        return [
            'DATA' => $results,
            'TOTAL_DATA' => $Tot
        ];
    }

    public static function get_detail_paket($kodepaket)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $query = "
            SELECT 
                tpm.KODE_PAKET ,
                tpm.KODE_SEMESTER ,
                GROUP_CONCAT(tpm.KODE_MATKUL SEPARATOR ';') AS KODE_MATKUL ,
                MAX(tpm.ID_TAHUN_AJAR) AS ID_TAHUN_AJAR ,
                GROUP_CONCAT(mm.NAMA_MATKUL SEPARATOR ';') AS NAMA_MATKUL ,
                SUM(mm.SKS) AS TOT_SKS ,
                MAX(mm.ID_PRODI) AS ID_PRODI ,
                MAX(mta.TAHUN_AJAR) AS TAHUN_AJAR
            FROM 
                tb_paket_matkul tpm 
            LEFT JOIN md_matkul mm ON 
                mm.KODE_MATKUL = tpm.KODE_MATKUL 
            LEFT JOIN md_tahun_ajaran mta ON 
                mta.ID_TAHUN_AJAR = tpm.ID_TAHUN_AJAR 
            WHERE 
                tpm.KODE_PAKET = '" . $kodepaket . "'
            GROUP BY 
                tpm.KODE_PAKET
        ";

        return DB::select($query);
    }
}
