<?php

namespace App\Http\Middleware;

use Closure;
use Log;


class ValidaRutaPerfil
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
       
        if(auth()->guest()){ // si no hay usuarios logueados
            goto NOPERMIRIR;
        }

        $idperfil=auth()->user()->perfil->id_perfil;
        $perfil=\App\Models\Personal\Perfil::where('id_perfil',$idperfil)
        ->where('estado','A')->first();

        //si no tiene un perfil activo mandamos el menu vacio
        if(is_null($perfil)){
            // return $idperfil;
            goto NOPERMIRIR;
        }
        
        $metodo = $_SERVER['REQUEST_METHOD'];
        if($metodo!="GET" && $metodo!="get" && $metodo!="Get"){ goto PERMITIR; }

        //verficamos si tiene la ruta asignada
        $rutaLlamada = \Request::route()->uri; // obtenemos el nombre de la ruta que se esta llamando
      
        if($perfil->descripcion=="SuperAdmin" && $rutaLlamada=="logs"){
            goto PERMITIR;
        }

        $rutaLlamada = \Request::route()->uri;
        
        #verificamos acceso
        $consultaAcceso=\App\Models\Personal\PerfilAcceso::with('menu')
        ->where('id_perfil',$idperfil)
        // ->where('id_gestion', $dataGestion->id_gestion)
        ->get();

        foreach($consultaAcceso as $data){
            $verificaRuta=\App\Models\Personal\Menu::where('id_menu',$data->id_menu)
            ->where('url', '/'.$rutaLlamada)
            ->where('estado', 'A')
            ->first();

            if(!is_null($verificaRuta)){
                goto PERMITIR;  
            }
        }

        // si no se encuentran coincidencias se redirecciona al login
        NOPERMIRIR:
        // return $rutaLlamada;
        return redirect('/');

        PERMITIR:
      
        return $next($request);
        
    }
}
 