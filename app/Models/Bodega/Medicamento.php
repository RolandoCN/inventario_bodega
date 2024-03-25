<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class Medicamento extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.medicamentos';
    protected $primaryKey  = 'coditem';
    public $timestamps = false;

    public function prodbod(){
        return $this->hasMany('App\Models\Bodega\ProductoBodega', 'idprod', 'coditem')->whereIn('idbodega',[1,17])
        ->with('lote');
    }
    
}
?>