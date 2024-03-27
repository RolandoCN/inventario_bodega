<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $connection = 'mysql';
    protected $table = 'items';
    protected $primaryKey  = 'iditems';
    public $timestamps = false;

    public function marca(){
        return $this->belongsTo('App\Models\Bodega\Marca', 'id_marca', 'idmarca');
    }

    public function modelo(){
        return $this->belongsTo('App\Models\Bodega\Modelo', 'idmodelo', 'idmodelo');
    }

}
?>