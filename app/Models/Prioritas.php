<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prioritas extends Model
{
    protected $table = 'prioritas';

    protected $primaryKey = 'id_prioritas';

    protected $fillable = ['nama_prioritas'];
}
