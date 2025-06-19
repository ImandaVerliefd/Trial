<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LogAkademik extends Model
{
    protected $table = 'tb_log_akademik';
    protected $primaryKey = 'ID_LOG';
    public $incrementing = false;
    public $timestamps = false;
}