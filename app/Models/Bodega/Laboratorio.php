<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class Laboratorio extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.laboratorio';
    protected $primaryKey  = 'id';
    public $timestamps = false;


    public function prodbod(){
        return $this->HasMany('App\Models\Bodega\ProductoBodega', 'idprod', 'id')
        ->with('existencia');
    }

}
?>