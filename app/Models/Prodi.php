<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Prodi extends Model
{
    protected $table = 'md_prodi';
    protected $primaryKey = 'ID_PRODI';
    public $incrementing = false;
    public $timestamps = false;

    public static function get_data_datatable($orderType, $valOrder, $search, $start, $perPage)
    {
        if (!empty($search)) {
            $conditions[] = "
                (
                    mp.PRODI LIKE '%$search%'
                )
            ";
        }

        $conditionWhere = "";
        if (!empty($conditions)) {
            $conditionWhere = "WHERE " . implode(" AND ", $conditions);
        }

        $results = DB::select("
            SELECT
                mp.*
            FROM
                md_prodi mp
            $conditionWhere
            ORDER BY
                $valOrder $orderType
            LIMIT 
                $start, $perPage
        ");


        $Tot = DB::selectOne("
            SELECT 
                COUNT(mp.ID_PRODI) AS DataTrans
            FROM
                md_prodi mp
                $conditionWhere
        ");

        return [
            'DATA' => $results,
            'TOTAL_DATA' => $Tot
        ];
    }
}
