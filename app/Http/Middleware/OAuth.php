<?php namespace App\Http\Middleware;

use Closure;
use Request;
use Response;
use Exception;
use App;


class OAuth {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		try{
			
			$access_token = str_replace('Bearer ','',Request::header('Authorization'));	
			
	        $ch = curl_init();
	        curl_setopt($ch, CURLOPT_URL, env('OAUTH_SERVER').'/oauth/check/'.$access_token.'/'.Request::header('X-Usuario'));
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			
	        // Execute & get variables
	        $api_response = json_decode(curl_exec($ch)); 
	        $curlError = curl_error($ch);
	        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	        
	        if($curlError){ 
	        	 throw new Exception("Hubo un problema al validar el token de acceso. cURL problem: $curlError"); 
	         
	        // Tet if there is a 4XX error (request went through but erred). 
	        }
	        
	        if($http_code != 200){
				if(isset($api_response->error)){
					return Response::json(['error'=>$api_response->error],$http_code);	
				}else{
					return Response::json(['error'=>"Error"],$http_code);
				}
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

        if(isset($action["controller"])){
			$value = explode('\\',$action["controller"]);
			$value = explode('@',$value[count($value)-1]);
		}
		else{
			$value[0] = "Session";
			$value[1] = "Session";
		}
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
