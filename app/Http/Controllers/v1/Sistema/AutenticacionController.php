<?php

namespace App\Http\Controllers\V1\Sistema;

use App\Http\Controllers\Controller;
use JWTAuth, JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;

use Illuminate\Http\Request;
use \Hash;
use Response;
use App\Models\Sistema\Usuario;

class AutenticacionController extends Controller
{
    public function accessToken(Request $request)
    {
        // grab credentials from the request
        $credentials = $request->only('email', 'password');

        try {
           
            $usuario = Usuario::where('id',$credentials['email'])->first();

            if(!$usuario) {                
                return response()->json(['error' => 'invalid_credentials'], 401); 
            }

            if(Hash::check($credentials['password'], $usuario->password)){

                $claims = [
                    "sub" => 1,
                    "id" => $usuario->id
                ];

                $payload = JWTFactory::make($claims);
                $token = JWTAuth::encode($payload);
                $permisos =  $permisos = Usuario::obtenerClavesPermisos()->where('usuarios.id','=',$credentials['email'])->get()->lists('clavePermiso');
                return Response::json(["usuario" => $usuario, "access_token" => $token->get(), "permisos" => $permisos ], 200);
            } else {
                return response()->json(['error' => 'invalid_credentials'], 401); 
            }

        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
    }
    public function refreshToken(Request $request){
        try{
            $token =  JWTAuth::parseToken()->refresh();
            return response()->json(['token' => $token], 200);

        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'token_expirado'], 401);  
        } catch (JWTException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function verificar(Request $request)
    {   
        try{
            $obj =  JWTAuth::parseToken()->getPayload();
            return $obj;
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'no_se_pudo_validar_token'], 500);
        }
        
    }
}