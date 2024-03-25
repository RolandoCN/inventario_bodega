<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class EntregaPaquete extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.entrega_paquete';
    protected $primaryKey  = 'identrega_paquete';
    public $timestamps = false;

    public function paquete(){
        return $this->belongsTo('App\Models\Bodega\Paquete', 'idpaquete', 'id_paquete');
    }
}
?>