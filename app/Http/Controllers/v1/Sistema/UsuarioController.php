<?php 
/**
 * Controlador UsuarioController
 * 
 * @package    plataforma API
 * @subpackage Controlador
 * @author     Eliecer Ramirez Esquinca <ramirez.esquinca@gmail.com>
 * @created    2016-06-01
 */
namespace App\Http\Controllers\v1\Sistema;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Models\Sistema\Usuario as Usuario;

use Input, Response,  Validator;
use Illuminate\Http\Response as HttpResponse;
use DB;
/**
*
* Controlador `UsuarioController`: administracion de usuario
*
*/
class UsuarioController extends Controller {

	/**
	 * Inicia el contructor para los permisos de visualizacion
	 *	 
	 */
    public function __construct()
    {
        $this->middleware('permisos:GET.4C49535441|POST.41444D494E|PUT.41444D494E|DELETE.41444D494E');
    }

	/**
	 * Muestra una lista de los recurso según los parametros a procesar en la petición.
	 *
	 * <h3>Lista de parametros Request:</h3>
	 * <Ul>Paginación
	 * <Li> <code>$pagina</code> numero del puntero(offset) para la sentencia limit </ li>
	 * <Li> <code>$limite</code> numero de filas a mostrar por página</ li>	 
	 * </Ul>
	 * <Ul>Busqueda
	 * <Li> <code>$valor</code> string con el valor para hacer la busqueda</ li>
	 * <Li> <code>$order</code> campo de la base de datos por la que se debe ordenar la información. Por Defaul es ASC, pero si se antepone el signo - es de manera DESC</ li>	 
	 * </Ul>
	 *
	 * Ejemplo ordenamiento con respecto a id:
	 * <code>
	 * http://url?pagina=1&limite=5&order=id ASC 
	 * </code>
	 * <code>
	 * http://url?pagina=1&limite=5&order=-id DESC
	 * </code>
	 *
	 * Todo Los parametros son opcionales, pero si existe pagina debe de existir tambien limite
	 * @return Response 
	 * <code style="color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 404, "messages": "No hay resultados"),status) </code>
	 */
	public function index()
	{
		try{
            $elementos_por_pagina = 50;
            $pagina = Input::get('pagina');
            if(!$pagina){
                $pagina = 1;
            }

            $query = Input::get('query');
        
            if($query){
            	$usuarios = Usuario::where('email','LIKE','%'.$query.'%');
            } else {
                $usuarios = Usuario::getModel();
            }

            $totales = $usuarios->count();
            $usuarios = $usuarios->skip(($pagina-1)*$elementos_por_pagina)
                        ->take($elementos_por_pagina)->get();

            return Response::json(["status" => 200, 'data'=>$usuarios,'totales'=>$totales],200);
        }catch(Exception $ex){
            return Response::json(['error'=>$e->getMessage()],500);
        }
	}

	/**
	 * Crear un nuevo registro en la base de datos con los datos enviados
	 *
	 * <h4>Request</h4>
	 * Recibe un input request tipo json de los datos a almacenar en la tabla correspondiente
	 *
	 * @return Response
	 * <code style="color:green"> Respuesta Ok json(array("status": 201, "messages": "Creado", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 500, "messages": "Error interno del servidor"),status) </code>
	 */
	public function store()
	{
		// validar los datos de entrada
		$this->ValidarParametros(Input::json()->all());		
		// recibir los datos de entrada
		$datos = Input::json()->all();	
		// iniciar bandera para retornar informacion	
		$success = false;

		// iniciar la transaccion para a fectar a la base de datos
        DB::beginTransaction();
		try {			
			$data = new Usuario;

			$data->email = $datos['email'];
			$data->nivel = $datos['nivel'];
			$data->save();
				
			$roles = array();
			if($data){
				if(isset($datos['roles'])){
					foreach($datos['roles'] as $rol){
						$roles[] = $rol['id'];
					}
				}
							
				if(count($roles)>0)
					$data->roles()->sync($roles);
				else
					$data->roles()->sync([]);

				if($datos["nivel"]!=1)
				{
					if(count($datos['UsuarioZona'])>0)
					{
						DB::table('UsuarioZona')->where('idUsuario', "$data->id")->delete();				
						DB::table('UsuarioJurisdiccion')->where('idUsuario', "$data->id")->delete();
						
						foreach($datos['UsuarioZona'] as $zona)
						{
							if($zona!="")
							{
								if($datos["nivel"]==3)
									DB::table('UsuarioZona')->insert(	array('idUsuario' => "$data->id", 'idZona' => $zona["id"]) );	
								if($datos["nivel"]==2)
									DB::table('UsuarioJurisdiccion')->insert(	array('idUsuario' => "$data->id", 'jurisdiccion' => $zona["id"]) );	
							}					
						}
					}				
				}

				$success = true;				
			}
		}
		catch (Exception $e) {
			return Response::json(['error' => $e->getMessage()], HttpResponse::HTTP_CONFLICT);
        }
        if ($success){
            DB::commit();
			return Response::json(array("status" => 201,"messages" => "Creado","data" => $data), 201);
        } 
		else{
            DB::rollback();
			return Response::json(array("status" => 409,"messages" => "Conflicto"), 409);
        }
	}

	/**
	 * Actualizar el  registro especificado en el la base de datos
	 *
	 * <h4>Request</h4>
	 * Recibe un Input Request con el json de los datos
	 *
	 * @param  int  $id que corresponde al identificador del dato a actualizar 	 
	 * @return Response
	 * <code style="color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 304, "messages": "No modificado"),status) </code>
	 */
	public function update($id){
		// validar los datos de entrada
		$this->ValidarParametros(Input::json()->all());		
		// recibir los datos de entrada
		$datos = Input::json()->all();	
		// iniciar bandera para retornar informacion	
		$success = false;

		// iniciar la transaccion para a fectar a la base de datos
        DB::beginTransaction();
		try {
			
			$data = Usuario::find($id);
			
			if($data){
				$data->email = $datos['email'];
				$data->nivel = $datos['nivel'];
				$data->save();									
				
				$roles = array();
				if(isset($datos['roles'])){
					foreach($datos['roles'] as $rol){
						$roles[] = $rol['id'];
					}
				}
							
				if(count($roles)>0)
					$data->roles()->sync($roles);
				else
					$data->roles()->sync([]);

				if($datos["nivel"]!=1)
				{
					if(count($datos['UsuarioZona'])>0)
					{
						DB::table('UsuarioZona')->where('idUsuario', "$data->id")->delete();
						DB::table('UsuarioJurisdiccion')->where('idUsuario', "$data->id")->delete();
						
						foreach($datos['UsuarioZona'] as $zona)
						{
							if($zona!="")
							{
								if($datos["nivel"]==3)
									DB::table('UsuarioZona')->insert(	array('idUsuario' => "$data->id", 'idZona' => $zona["id"]) );	
								if($datos["nivel"]==2)
									DB::table('UsuarioJurisdiccion')->insert(	array('idUsuario' => "$data->id", 'jurisdiccion' => $zona["id"]) );	
							}					
						}
					}				
				}
			
				$success = true;
			}
		}	
		catch (Exception $e){
		    return Response::json(['error' => $e->getMessage()], HttpResponse::HTTP_CONFLICT);
		}
        if ($success) {
        	DB::commit();
			return Response::json(array("status" =>200,"messages" => "Operación realizada con exito","data" => $data),200);
        } 
		else {
			DB::rollback();
			return Response::json(array("status" =>404,"messages" => "No exite el recurso"),404);
        }
	}

	/**
	 * Devuelve la información del registro especificado.
	 *
	 * @param  int  $id que corresponde al identificador del recurso a mostrar
	 *
	 * @return Response
	 * <code style="color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 404, "messages": "No hay resultados"),status) </code>
	 */
	public function show($id){
		try{
			$data = Usuario::find($id);
			if(!$data){
				return Response::json(array("status"=> 404,"messages" => "No hay resultados"),404);
			}
			$data->load('roles.permisos');

			$data["nivel"] = $data->nivel;
			if($data->nivel==2)
			{
				$data['UsuarioZona'] = DB::table('UsuarioJurisdiccion')		
				->select(array("jurisdiccion as id","jurisdiccion as nombre"))
				->where('idUsuario',$id)->get();
			}
			else if($data->nivel==3)
			{
				$data['UsuarioZona'] = DB::table('UsuarioZona AS u')
				->leftJoin('Zona AS c', 'c.id', '=', 'u.idZona')			
				->select('*')
				->where('idUsuario',$id)->get();
			}
			else
				$data['UsuarioZona']=array();

        	return Response::json(array("status" =>200,"messages" => "Operación realizada con exito","data" => $data),200);
       	}catch(Exception $e){
             return Response::json(['error' => $e->getMessage()], HttpResponse::HTTP_CONFLICT);
    	}
	}

	/**
	 * Elimine el registro especificado del la base de datos (softdelete).
	 *
	 * @param  int  $id que corresponde al identificador del dato a eliminar
	 *
	 * @return Response
	 * <code style="color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 500, "messages": "Error interno del servidor"),status) </code>
	 */
	public function destroy($id)
	{
		try {
			Usuario::destroy($id);
			return Response::json(array("status" =>200,"messages" => "Eliminado"),200);
		} catch (Exception $e) {
		   return Response::json(['error' => $e->getMessage()], HttpResponse::HTTP_CONFLICT);
		}
	}

	/**
	 * Validad los parametros recibidos, Esto no tiene ruta de acceso es un metodo privado del controlador.
	 *
	 * @param  Request  $request que corresponde a los parametros enviados por el cliente
	 *
	 * @return Response
	 * <code> Respuesta Error json con los errores encontrados </code>
	 */
	private function ValidarParametros($request){
		$mensajes = [
			'required' 		=> "required",
			'email' 		=> "email",
			'accepted' 		=> "accepted",
			'confirmed' 	=> "confirmed",
			'unique' 		=> "unique",
			'url' 			=> "url",
			'date' 			=> "date"
		];
		$reglas = [
			'email'		=> 'required|email',
			'roles' 	=> 'array',					
		];
		$v = Validator::make($request, $reglas, $mensajes);

		if ($v->fails()) {
			return Response::json(['error' => $v->errors()], HttpResponse::HTTP_CONFLICT);
	    }
	}
}