<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Kurikulum extends Model
{
    protected $table = 'md_kurikulum';
    protected $primaryKey = 'ID_KURIKULUM';
    public $incrementing = false;
    public $timestamps = false;

    public static function get_data_datatable($orderType, $valOrder, $search, $start, $perPage)
    {
        if (!empty($search)) {
            $conditions[] = "
                (
                    mk.KURIKULUM LIKE '%$search%'
                    OR
                    mk.TAHUN LIKE '%$search%'
                )
            ";
        }

        $conditions[] = "mk.IS_DELETE = 0";

        $conditionWhere = "";
        if (!empty($conditions)) {
            $conditionWhere = "WHERE " . implode(" AND ", $conditions);
        }

        $results = DB::select("
            SELECT
                mk.*
            FROM
                md_kurikulum mk
            $conditionWhere
            ORDER BY
                $valOrder $orderType
            LIMIT 
                $start, $perPage
        ");


        $Tot = DB::selectOne("
            SELECT 
                COUNT(mk.ID_KURIKULUM) AS DataTrans
            FROM
                md_kurikulum mk
                $conditionWhere
        ");

        return [
            'DATA' => $results,
            'TOTAL_DATA' => $Tot
        ];
    }
}
