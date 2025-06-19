<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CapaianMatkul extends Model
{
    protected $table = 'mapping_capaian_matkul';
    protected $primaryKey = 'ID_MAPPING';
    public $incrementing = false;
    public $timestamps = false;

    public static function get_data_datatable($orderType, $valOrder, $search, $start, $perPage, $prodi)
    {
        if (!empty($search)) {
            $conditions[] = "
                (
                    mm.NAMA_MATKUL LIKE '%$search%'
                    OR
                    mc.CAPAIAN LIKE '%$search%'
                    OR
                    mk.KURIKULUM LIKE '%$search%'
                )
            ";
        }

        $conditions[] = "mcm.IS_DELETE IS NULL";
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
                mcm.IS_DELETE ,
                mcm.ID_KURIKULUM ,
                mcm.KODE_MATKUL ,
                mm.NAMA_MATKUL ,
                GROUP_CONCAT(mc.CAPAIAN SEPARATOR ';') AS CAPAIAN ,
                GROUP_CONCAT(mc.KODE_CAPAIAN SEPARATOR ';') AS KODE_CAPAIAN ,
                mk.KURIKULUM 
            FROM 
                mapping_capaian_matkul mcm 
            LEFT JOIN md_matkul mm ON 
                mm.KODE_MATKUL = mcm.KODE_MATKUL 
            LEFT JOIN md_capaian mc ON 
                mc.KODE_CAPAIAN = mcm.KODE_CAPAIAN 
            LEFT JOIN md_kurikulum mk ON 
                mk.ID_KURIKULUM = mcm.ID_KURIKULUM 
                $conditionWhere
            GROUP BY 
                mm.KODE_MATKUL ,
                mk.ID_KURIKULUM 
                $ordering
            LIMIT 
                $start, $perPage
        ");
        
        $Tot = DB::selectOne("
            SELECT
                COUNT(mcm.ID_MAPPING) AS DataTrans
            FROM 
                mapping_capaian_matkul mcm 
            LEFT JOIN md_matkul mm ON 
                mm.KODE_MATKUL = mcm.KODE_MATKUL 
            LEFT JOIN md_capaian mc ON 
                mc.KODE_CAPAIAN = mcm.KODE_CAPAIAN 
            LEFT JOIN md_kurikulum mk ON 
                mk.ID_KURIKULUM = mcm.ID_KURIKULUM 
                $conditionWhere
            GROUP BY 
                mm.KODE_MATKUL ,
                mk.ID_KURIKULUM 
        ");

        return [
            'DATA' => $results,
            'TOTAL_DATA' => $Tot
        ];
    }
}
