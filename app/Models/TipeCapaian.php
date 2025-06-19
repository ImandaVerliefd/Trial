<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipeCapaian extends Model
{
    protected $table = 'md_tipe_capaian';
    protected $primaryKey = 'ID_TIPE';
    public $incrementing = false;
    public $timestamps = false;
}
