<?php 
/**
 * Controlador RolController
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

use App\Models\Sistema\Rol as Rol;
use App\Models\Sistema\Permiso as Permiso;

use Input, Response,  Validator, DB;
use Illuminate\Http\Response as HttpResponse;

/**
*
* Controlador `RolController`: administra los roles a los que pertencerán los usuarios
*
*/
class RolController extends Controller {

	/**
	 * Inicia el contructor para los permisos de visualizacion
	 *	 
	 */
    public function __construct()
    {
        $this->middleware('permisos:GET.524F4C4553|POST.524F4C4553|PUT.524F4C4553|DELETE.524F4C4553');
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
	public function index(){
		try{
            $elementos_por_pagina = 50;
            $pagina = Input::get('pagina');
            if(!$pagina){
                $pagina = 1;
            }

            $query = Input::get('query');
        
            if($query){
            	$roles = Rol::where('nombre','LIKE','%'.$query.'%');
            } else {
                $roles = Rol::getModel();
            }

            $totales = $roles->count();
            $roles = $roles->skip(($pagina-1)*$elementos_por_pagina)
                        ->take($elementos_por_pagina)->get();

            /*if($roles){
				$roles->load('permisos');
			}*/

            return Response::json(['data'=>$roles,'totales'=>$totales],200);
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
			
			$data = Rol::create($datos);
			$permisos = array();
			
			if($data){
				if(isset($datos['permisos'])){
					foreach($datos['permisos'] as $permiso){
						$permisos[] = $permiso['id'];
					}
				}
							
				if(count($permisos)>0)
					$data->permisos()->sync($permisos);
				else
					$data->permisos()->sync([]);
			
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
		//
		// validar los datos de entrada
		$this->ValidarParametros(Input::json()->all());		
		// recibir los datos de entrada
		$datos = Input::json()->all();	
		// iniciar bandera para retornar informacion	
		$success = false;

		// iniciar la transaccion para a fectar a la base de datos
        DB::beginTransaction();
		try {
			
			$data = Rol::find($id);
			
			if($data){							
				$data->nombre = $datos['nombre'];
				$data->save();					
								
				$permisos = array();
				if(isset($datos['permisos'])){
					foreach($datos['permisos'] as $permiso){
						$permisos[] = $permiso['id'];
					}
				}
							
				if(count($permisos)>0)
					$data->permisos()->sync($permisos);
				else
					$data->permisos()->sync([]);
			
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
           	$data = Rol::find($id);
           	
			if(!$data){
				throw new Exception('No existe el rol');
			}
			
			$data->permisos;
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
			Rol::destroy($id);
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
			'nombre'	=> 'required',
			'permisos' 	=> 'array',					
		];
		$v = Validator::make($request, $reglas, $mensajes);

		if ($v->fails()) {
			return Response::json(['error' => $v->errors()], HttpResponse::HTTP_CONFLICT);
	    }
	}
}