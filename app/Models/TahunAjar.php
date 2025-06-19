<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TahunAjar extends Model
{
    protected $table = 'md_tahun_ajaran';
    protected $primaryKey = 'ID_TAHUN_AJAR';
    public $incrementing = false;
    public $timestamps = false;

    public static function get_all_tahun_ajar()
    {
        return DB::select("
            SELECT
                *
            FROM
                md_tahun_ajaran mta
            WHERE
                mta.IS_DELETE IS NULL
        ");
    }
}
