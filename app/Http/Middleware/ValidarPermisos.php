<?php namespace App\Http\Middleware;

use Closure;
use Request;
use Response;
use Exception;
use App;


class ValidarPermisos {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next,$permisos)
	{
		try{

			$http_code = 200;
			$array_permisos = explode('|', $permisos);
			$metodo = $request->method();

			$permisos = array();
			foreach ($array_permisos as $permiso) {
				$partes_permiso = explode('.', $permiso);
				if($metodo == $partes_permiso[0]){
					$permisos[] = $partes_permiso[1];
				}
			}

			$user_email = Request::header('X-Usuario');

			$acceso = App\Models\Sistema\Usuario::obtenerClavesPermisos()
									->where('usuarios.email','=',$user_email)
									->whereIn('permisos.clave',$permisos)
									->get();
			
			if(count($acceso) == 0){
				$http_code = 403;
			}

	        if($http_code != 200){
	        	return Response::json(["error"=>"Error", "status" => 403],$http_code);
	        }   
	        // Si llegamos a este punto el token es valido
			return $next($request);
	        
	    }catch(Exception $e){
	    	
			return Response::json(['error'=>$e->getMessage()],500);
	    }
		
		
	}

	/**
     * Run the after request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Response  $response
     */
    public function terminate($request, $response)
    {
        $user_email = Request::header('X-Usuario');
        $usuario = App\Models\Sistema\Usuario::where("email", $user_email)->first();

        $mac = exec('getmac');
        $mac = explode(" ", $mac);

        $action = $request->route()->getAction();
		$value = explode('\\',$action["controller"]);
		$value = explode('@',$value[count($value)-1]);

        $log = new App\Models\Sistema\SisLogs;
        $log->id 		   = time();
       // $log->servidor_id  = env("SERVIDOR_ID");
        $log->usuarios_id  = $usuario->id;
        $log->ip           = $request->ip();
        $log->mac          = $mac[0];
        $log->tipo         = $request->getMethod()."(".$value[1].")";
        $log->ruta         = $request->path();
        $log->controlador  = $value[0];
        //$log->tabla        = $adminInstance->getTable();
        $log->peticion     = json_encode($request);
        $log->respuesta    = json_encode($response);
        $log->info         = $request->server('HTTP_USER_AGENT');

        $log->save();
    }

}
