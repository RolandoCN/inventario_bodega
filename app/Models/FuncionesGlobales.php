<?php
    
    function listarMenuSession(){
    
        $lista_modulo = array(); // iniciamos la variable de retorno como un arreglo vacio
        if(auth()->guest()){ // si no hay usuarios logueados no retornamos nada en el menu
            goto FINALM;
        }


        $idperfil=auth()->user()->perfil->id_perfil;
        $perfil=App\Models\Personal\Perfil::where('id_perfil',$idperfil)->where('estado','A')->first();

        //si no tiene un perfil activo mandamos el menu vacio
        if(is_null($perfil)){
            goto FINALM;
        }
        
        
        $gestiones_listado= App\Models\Personal\GestionMenu::select('id_gestion','estado')
        ->groupBy('id_gestion','estado')
        ->where('estado','A')
        ->get();

        // dd($idperfil);
            
        $lista=[];
        foreach($gestiones_listado as $key=> $dataGestion){
            $consultaAcceso=App\Models\Personal\PerfilAcceso::with('menu')
            ->where('id_perfil',$idperfil)
            ->where('id_gestion', $dataGestion->id_gestion)
            ->get();

            if(sizeof($consultaAcceso)>0){
                $nombreGestion=App\Models\Personal\Gestion::where('id_gestion', $dataGestion->id_gestion)->first();

                array_push($lista_modulo,["gestion"=>$nombreGestion->descripcion,"icono"=>$nombreGestion->icono, "rutas"=>$consultaAcceso]);
            }
        }
       
        FINALM:
    
        return $lista_modulo; // retornamos el menu solo con las gestiones que le pertenecen al usuario

    }

    function hola(){
        return true;
    }

    