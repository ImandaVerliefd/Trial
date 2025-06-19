<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodeAjar extends Model
{
    protected $table = 'md_metode_ajar';
    protected $primaryKey = 'ID_METODE';
    public $incrementing = false;
    public $timestamps = false;
}
