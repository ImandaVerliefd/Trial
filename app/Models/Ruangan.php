<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Ruangan extends Model
{
    protected $table = 'md_ruangan';
    protected $primaryKey = 'ID_RUANGAN';
    public $incrementing = false;
    public $timestamps = false;

    public static function get_data_datatable($orderType, $valOrder, $search, $start, $perPage)
    {
        if (!empty($search)) {
            $conditions[] = "
                (
                    mr.RUANGAN LIKE '%$search%'
                    OR
                    mr.TAHUN LIKE '%$search%'
                )
            ";
        }

        $conditions[] = "mr.IS_ACTIVE = 1";
        $conditions[] = "mr.IS_DELETE = 0";

        $conditionWhere = "";
        if (!empty($conditions)) {
            $conditionWhere = "WHERE " . implode(" AND ", $conditions);
        }

        $results = DB::select("
            SELECT
                mr.*
            FROM
                md_ruangan mr
            $conditionWhere
            ORDER BY
                $valOrder $orderType
            LIMIT 
                $start, $perPage
        ");


        $Tot = DB::selectOne("
            SELECT 
                COUNT(mr.ID_RUANGAN) AS DataTrans
            FROM
                md_ruangan mr
                $conditionWhere
        ");

        return [
            'DATA' => $results,
            'TOTAL_DATA' => $Tot
        ];
    }
}
