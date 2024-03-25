<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class Cie extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'esq_rdacaa.cie10';
    protected $primaryKey  = 'cie10_id';
    public $timestamps = false;

}
?>