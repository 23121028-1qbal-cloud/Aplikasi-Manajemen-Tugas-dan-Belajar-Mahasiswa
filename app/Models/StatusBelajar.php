<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusBelajar extends Model
{
    protected $table = 'status_belajar';

    protected $primaryKey = 'id_status_belajar';

    protected $fillable = ['nama_status'];
}
