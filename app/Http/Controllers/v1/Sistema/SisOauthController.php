<?php
namespace App\Http\Controllers\V1\Sistema;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use JWTAuth, JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;

use Request;
use Response;
use Input;
use Session;
use Crypt;
use Mail;
use App\Models\Sistema\Usuario;

/**
* Controlador Oauth
* 
* @package    Plataforma API
* @subpackage Controlador
* @author     Eliecer Ramirez Esquinca <ramirez.esquinca@gmail.com>
* @created    2015-07-20
*
* Controlador `Oauth`: Manejo de la obtension del token de acceso
*
*/
class SisOauthController extends Controller {

	/**
	 * Renueva el token de acceso si ya caduco
	 *
	 * <h4>Input</h4>
	 * Recibe un input request con el refresh_token
	 *
	 * @return Response
	 * <code style="color:green"> Respuesta Ok json(array("access_token": "token", "access_token": "token"),status) </code>
	 * <code> Respuesta Error json(array(error), status) </code>
	*/
	public function refreshToken(){
		
		try{
            $token =  JWTAuth::parseToken()->refresh();
            return response()->json(['access_token' => $token], 200);

        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'token_expirado'], 401);  
        } catch (JWTException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }        	
	}

	/**
	 * Crear un nuevo token de acceso con las credenciales del usuario
	 *
	 * <h4>Request</h4>
	 * Recibe un input request tipo json de los datos de acceso OAUTH del usuario
	 *
	 * @return Response
	 * <code style="color:green"> Respuesta Ok json(array("access_token": "token", "access_token": "token"),status) </code>
	 * <code> Respuesta Error json(array(error), status) </code>
	*/
	public function accessToken(){
		try{
	        $credentials = Input::only("email", "password");
			// Si no se puede recibir como POST recibir entonces como json
			if($credentials["email"] == ""){
				$credentials = Input::json()->all();			
			}

	        $post_request =  "grant_type=password"
	                    	."&client_id=".env("CLIENT_ID")
	                    	."&client_secret=".env("CLIENT_SECRET")
	                    	."&username=".$credentials["email"]
	                    	."&password=".$credentials["password"]; 
	                         
	        $ch = curl_init();
	        $header[] = "Content-Type: application/x-www-form-urlencoded";
	        curl_setopt($ch, CURLOPT_HTTPHEADER,     $header);
	        curl_setopt($ch, CURLOPT_URL, env("OAUTH_SERVER")."/oauth/access_token");
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_request);
	      
	        // Execute & get variables
	        $html = curl_exec($ch);
	        $api_response = json_decode($html); 
	        $curlError = curl_error($ch);
	        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	        curl_close($ch);
	        if($curlError){ 
	        	 throw new Exception("Hubo un problema al intentar hacer la autenticacion. cURL problem: $curlError");
	        }
	        
	        if($http_code != 200){
	          if(isset($api_response->error)){
					return Response::json(["error" => $api_response->error], $http_code);	
				}else{
					return Response::json(["error" => $api_response], $http_code);
				}
	        } 
			
			try{				

				$data = Usuario::where("id", $credentials["email"])->first();					
				if(!$data)
					return Response::json(["error" => "CUENTA-VALIDA-NO-AUTORIZADA", "mensaje" => "NO ENCONTRADO"], 403);		
				Session::put('usuario', $data);
			}
			catch (\Exception $e){
				return Response::json(["error" => "CUENTA-VALIDA-NO-AUTORIZADA", "mensaje" => $e->getMessage()], 403);
			}
	        //Encriptamos el refresh token para que no quede 100% expuesto en la aplicacion web

	        $refresh_token_encrypted = Crypt::encrypt($api_response->refresh_token);
			
	       	//validar cuenta y grupos
	        $permisos =  $permisos = Usuario::obtenerClavesPermisos()->where('usuarios.id','=',$credentials["email"])->get()->lists('clavePermiso');

	        try{
	        	$claims = [
                    "sub" => 1,
                    "id" => $data->id,
                    "access_token" => $api_response->access_token,
                    "refresh_token" => $refresh_token_encrypted
                ];

                $payload = JWTFactory::make($claims);
                $token = JWTAuth::encode($payload);
	        }catch(JWTException $e){
	        	return Response::json(["error" => $e->getMessage()], 500);
	        }
			$usuario = $this->getPerfil(true, $api_response->access_token, $data->id);
			
			if($usuario)
				$data = $usuario->data;			
	        return Response::json(["usuario" => $data, "access_token" => $token->get(), "permisos" => $permiso ], 200);
	    }catch(Exception $e){
	         return Response::json(["error" => $e->getMessage()], 500);
	    }		
	}

	/**
	 * Actualiza el perfil del usuario.
	 *
	 * <h4>Request</h4>
	 * Recibe un Input Request con el json de los datos
	 * @param  string  $email que corresponde al identificador del usuario a actualizar. 
	 *	 
	 * @return Response
	 * <code style="color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 500, "messages": "Error interno del servidor"),status) </code>
	 */
	public function perfil()
	{
		$this->getPerfil(false);
	}
	
	public function getPerfil($valor, $token = null, $email  = null){		
		try{
			if(!$valor){
				$obj =  JWTAuth::parseToken()->getPayload();
				$token = $obj->get('access_token');
				$email = $obj->get('email');
			}
			if($email){
				$access_token = 'Bearer '.$token;	
			
				$ch = curl_init();
				$headers = array(
					"Content-Type: application/x-www-form-urlencoded",       					
					"X-Usuario: ".$email,
					"Authorization: ".$access_token
				);
				curl_setopt($ch, CURLOPT_HTTPHEADER,     $headers);
				curl_setopt($ch, CURLOPT_URL, env('OAUTH_SERVER').'/api/v1/perfil');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
				
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
						return Response::json(['error'=>$api_response],$http_code);
					}
				}
				if($valor)
					return $api_response;
				else
					return Response::json(array("status" =>200,"messages" => "Ok", "data" => $api_response),200);			
		    }
			else{
				return Response::json(array("status" =>404,"messages" => "No encontrado"),404);	
			}
		}catch(\Exception $e){
			return Response::json(["error" => $e->getMessage()], 500);
		}
	}

	/**
	 * Actualiza el perfil del usuario.
	 *
	 * <h4>Request</h4>
	 * Recibe un Input Request con el json de los datos
	 * @param  string  $email que corresponde al identificador del usuario a actualizar. 
	 *	 
	 * @return Response
	 * <code style="color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 500, "messages": "Error interno del servidor"),status) </code>
	 */
	public function actualizarFoto($email)
	{
		try{
			$obj =  JWTAuth::parseToken()->getPayload();
			if($email == $obj->get('email')){
				$datos = json_encode(Request::json()->all());
				$access_token = 'Bearer '.$obj->get('access_token');		
			
				$ch = curl_init();
				$headers = array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen($datos),     					
					'X-Usuario: '.$email,
					'Authorization: '.$access_token
				);
				curl_setopt($ch, CURLOPT_HTTPHEADER,     $headers);
				curl_setopt($ch, CURLOPT_URL, env('OAUTH_SERVER').'/api/v1/actualizar-foto/'.$email);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
				curl_setopt($ch, CURLOPT_POSTFIELDS, $datos);
				
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
						return Response::json(['error'=>$api_response],$http_code);
					}
				}   
				return Response::json(array("status" =>200,"messages" => "Ok", "data" => $api_response),200);
			}											
			else{
				return Response::json(array("status" =>404,"messages" => "No encontrado"),404);	
			}
		}catch(\Exception $e){
			return Response::json(["error" => $e->getMessage()], 500);
		}
	}
	/**
	 * Actualiza el perfil del usuario.
	 *
	 * <h4>Request</h4>
	 * Recibe un Input Request con el json de los datos
	 * @param  string  $email que corresponde al identificador del usuario a actualizar. 
	 *	 
	 * @return Response
	 * <code style="color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 500, "messages": "Error interno del servidor"),status) </code>
	 */
	public function actualizarPerfil($email)
	{
		try{
			$obj =  JWTAuth::parseToken()->getPayload();
			if($email == $obj->get('email')){
				$datos = json_encode(Request::json()->all());
				$access_token = 'Bearer '.$obj->get('access_token');		
			
				$ch = curl_init();
				$headers = array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen($datos),     					
					'X-Usuario: '.$email,
					'Authorization: '.$access_token
				);		
				curl_setopt($ch, CURLOPT_HTTPHEADER,     $headers);
				curl_setopt($ch, CURLOPT_URL, env('OAUTH_SERVER').'/api/v1/actualizar-perfil/'.$email);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
				curl_setopt($ch, CURLOPT_POSTFIELDS, $datos);
				
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
						return Response::json(['error'=>$api_response],$http_code);
					}
				}   
				return Response::json(array("status" =>200,"messages" => "Ok", "data" => $api_response),200);
			}else{
				return Response::json(array("status" =>404,"messages" => "No encontrado"),404);	
			}
		}catch(\Exception $e){
			return Response::json(["error" => $e->getMessage()], 500);
		}
	}
}
