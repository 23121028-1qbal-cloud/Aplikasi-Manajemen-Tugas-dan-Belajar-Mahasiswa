<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusTugas extends Model
{
    protected $table = 'status_tugas';

    protected $primaryKey = 'id_status';

    protected $fillable = ['nama_status'];
}
