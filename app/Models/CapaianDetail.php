<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapaianDetail extends Model
{
    protected $table = 'tb_capaian_detail';
    protected $primaryKey = 'ID_CAPAIAN_DETAIL';
    public $incrementing = false;
    public $timestamps = false;
}
