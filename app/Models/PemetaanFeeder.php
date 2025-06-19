<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PemetaanFeeder extends Model
{
    protected $table = 'md_feeder';
    protected $primaryKey = 'ID_FEEDER';
    public $incrementing = false;
    public $timestamps = false;

    public static function get_data_datatable($orderType, $valOrder, $search, $start, $perPage)
    {
        if (!empty($search)) {
            $conditions[] = "
                (
                    mpf.FEEDER LIKE '%$search%'
                )
            ";
        }
        $conditions[] = "mpf.IS_DELETE IS NULL";

        $conditionWhere = "";
        if (!empty($conditions)) {
            $conditionWhere = "WHERE " . implode(" AND ", $conditions);
        }

        $results = DB::select("
            SELECT
                mpf.*
            FROM
                md_feeder mpf
                $conditionWhere
            ORDER BY
                $valOrder $orderType
            LIMIT 
                $start, $perPage
        ");

        $Tot = DB::selectOne("
            SELECT 
                COUNT(mpf.ID_FEEDER) AS DataTrans
            FROM
                md_feeder mpf
                $conditionWhere
        ");

        return [
            'DATA' => $results,
            'TOTAL_DATA' => $Tot
        ];
    }
}
