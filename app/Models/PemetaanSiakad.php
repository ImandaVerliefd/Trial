<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PemetaanSiakad extends Model
{
    protected $table = 'md_siakad';
    protected $primaryKey = 'ID_SIAKAD';
    public $incrementing = false;
    public $timestamps = false;

    public static function get_data_datatable($orderType, $valOrder, $search, $start, $perPage)
    {
        if (!empty($search)) {
            $conditions[] = "
                (
                    mpf.FEEDER LIKE '%$search%'
                    OR
                    mps.SIAKAD LIKE '%$search%'
                )
            ";
        }
        $conditions[] = "mps.IS_DELETE IS NULL";

        $conditionWhere = "";
        if (!empty($conditions)) {
            $conditionWhere = "WHERE " . implode(" AND ", $conditions);
        }

        $results = DB::select("
            SELECT
                mps.* ,
                mpf.FEEDER
            FROM
                md_siakad mps
            LEFT JOIN md_feeder mpf ON
                mps.ID_FEEDER = mpf.ID_FEEDER
                $conditionWhere
            ORDER BY
                $valOrder $orderType
            LIMIT 
                $start, $perPage
        ");

        $Tot = DB::selectOne("
            SELECT 
                COUNT(mps.ID_SIAKAD) AS DataTrans
            FROM
                md_siakad mps
                $conditionWhere
        ");

        return [
            'DATA' => $results,
            'TOTAL_DATA' => $Tot
        ];
    }

    public static function get_siakad_feeder()
    {
        return DB::select("
            SELECT
                mpf.*
            FROM
                md_feeder mpf
            WHERE
                mpf.IS_DELETE IS NULL
        ");
    }

    public static function get_all_siakad_with_feeder()
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        return DB::select("
            SELECT
                ms.ID_FEEDER ,
                mf.FEEDER ,
                GROUP_CONCAT(ms.ID_SIAKAD SEPARATOR ';') AS ID_SIAKAD
            FROM 
                md_feeder mf
            LEFT JOIN md_siakad ms ON
                mf.ID_FEEDER = ms.ID_FEEDER 
            WHERE 
                mf.IS_DELETE IS NULL
            GROUP BY 
                ms.ID_FEEDER 
        ");
    }
}
