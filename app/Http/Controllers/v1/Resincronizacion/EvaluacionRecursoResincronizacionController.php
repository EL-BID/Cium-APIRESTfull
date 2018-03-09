<?php
namespace App\Http\Controllers\v1\Resincronizacion;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Response;
use Input;
use DB;

use Request;

use App\Models\Sistema\Usuario;

use App\Models\Catalogos\Accion;
use App\Models\Catalogos\Clues;
use App\Models\Catalogos\ConeClues;

use App\Models\Resincronizacion\HallazgoResincronizacion;
use App\Models\Resincronizacion\EvaluacionRecursoResincronizacion;
use App\Models\Resincronizacion\EvaluacionRecursoCriterioResincronizacion;
use App\Models\Resincronizacion\EvaluacionRecursoRegistroResincronizacion;

use App\Models\Catalogos\CriterioValidacionRespuesta;

/**
* Controlador Evaluación (Recurso)
* 
* @package    CIUM API
* @subpackage Controlador
* @author     Eliecer Ramirez Esquinca <ramirez.esquinca@gmail.com>
* @created    2015-07-20
*
* Controlador `Calidad`: Proporciona los servicios para el manejos de los datos de la evaluacion
*
*/
class EvaluacionRecursoResincronizacionController extends Controller 
{	
	/**
	 * Inicia el contructor para los permisos de visualizacion
	 *	 
	 */
    public function __construct()
    {
        $this->middleware('permisos:GET.7265637572|POST.6F73727563|PUT.7461725265|DELETE.AE61725265');
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
		$datos = Request::all();
		
		$cluesUsuario=$this->permisoZona();
				
		// Si existe el paarametro pagina en la url devolver las filas según sea el caso
		// si no existe parametros en la url devolver todos las filas de la tabla correspondiente
		// esta opción es para devolver todos los datos cuando la tabla es de tipo catálogo
		if(array_key_exists('pagina',$datos))
		{
			$pagina=$datos['pagina'];
			if(isset($datos['order']))
			{
				$order = $datos['order'];
				if(strpos(" ".$order,"-"))
					$orden="desc";
				else
					$orden="asc";
				$order=str_replace("-","",$order); 
			}
			else{
				$order="fechaEvaluacion"; $orden="desc";
			}
			
			if($pagina == 0)
			{
				$pagina = 1;
			}
			// si existe buscar se realiza esta linea para devolver las filas que en el campo que coincidan con el valor que el usuario escribio
			// si no existe buscar devolver las filas con el limite y la pagina correspondiente a la paginación
			if(array_key_exists('buscar',$datos))
			{
				$columna = $datos['columna'];
				$valor   = $datos['valor'];
				$evaluacion = EvaluacionRecursoResincronizacion::with("cone","usuarios","cluess")->distinct()->select("EvaluacionRecursoResincronizacion.id", "EvaluacionRecursoResincronizacion.idUsuario"
					, "EvaluacionRecursoResincronizacion.clues", "EvaluacionRecursoResincronizacion.fechaEvaluacion", "EvaluacionRecursoResincronizacion.cerrado", "EvaluacionRecursoResincronizacion.firma"
					, "EvaluacionRecursoResincronizacion.responsable", "EvaluacionRecursoResincronizacion.email", "EvaluacionRecursoResincronizacion.enviado", "EvaluacionRecursoResincronizacion.creadoAl"
					, "EvaluacionRecursoResincronizacion.modificadoAl", "EvaluacionRecursoResincronizacion.borradoAl")
												->leftJoin('Clues', 'Clues.clues', '=', 'EvaluacionRecursoResincronizacion.clues')
												->leftJoin('usuarios', 'usuarios.id', '=', 'EvaluacionRecursoResincronizacion.idUsuario')
												->whereIn('EvaluacionRecursoResincronizacion.clues',$cluesUsuario)->orderBy($order,$orden);
				
				$search = trim($valor);
				$keyword = $search;
				$evaluacion=$evaluacion->whereNested(function($query) use ($keyword)
				{
					
					$query->Where('Clues.clues', 'LIKE', '%'.$keyword.'%')
						 ->orWhere('fechaEvaluacion', 'LIKE', '%'.$keyword.'%')
						 ->orWhere('Clues.jurisdiccion', 'LIKE', '%'.$keyword.'%')
						 ->orWhere('Clues.nombre', 'LIKE', '%'.$keyword.'%')
						 ->orWhere('usuarios.email', 'LIKE', '%'.$keyword.'%')
						 ->orWhere('cerrado', 'LIKE', '%'.$keyword.'%'); 
				});
				$total = $evaluacion->get();
				$evaluacion = $evaluacion->skip($pagina-1)->take($datos['limite'])->get();
				
				
			}
			else
			{
				$evaluacion = EvaluacionRecursoResincronizacion::with("cone","usuarios","cluess")->select("EvaluacionRecursoResincronizacion.id", "EvaluacionRecursoResincronizacion.idUsuario"
					, "EvaluacionRecursoResincronizacion.clues", "EvaluacionRecursoResincronizacion.fechaEvaluacion", "EvaluacionRecursoResincronizacion.cerrado", "EvaluacionRecursoResincronizacion.firma"
					, "EvaluacionRecursoResincronizacion.responsable", "EvaluacionRecursoResincronizacion.email", "EvaluacionRecursoResincronizacion.enviado", "EvaluacionRecursoResincronizacion.creadoAl"
					, "EvaluacionRecursoResincronizacion.modificadoAl", "EvaluacionRecursoResincronizacion.borradoAl")
												->leftJoin('Clues', 'Clues.clues', '=', 'EvaluacionRecursoResincronizacion.clues')
												->leftJoin('usuarios', 'usuarios.id', '=', 'EvaluacionRecursoResincronizacion.idUsuario')
				->whereIn('EvaluacionRecursoResincronizacion.clues',$cluesUsuario)->skip($pagina-1)->take($datos['limite'])
				->orderBy($order,$orden)->get();
				$total=EvaluacionRecursoResincronizacion::whereIn('EvaluacionRecursoResincronizacion.clues',$cluesUsuario)->get();
			}
			
		}
		else
		{
			$evaluacion = EvaluacionRecursoResincronizacion::with("cone","usuarios","cluess")->whereIn('clues',$cluesUsuario)->get();
			$total=$evaluacion;
		}

		if(!$evaluacion)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),404);
		} 
		else 
		{
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$evaluacion,"total"=>count($total)),200);
			
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
		$datos = Request::json()->all();
		if(array_key_exists("evaluaciones",$datos))
		{
			$rules = [
				"evaluaciones" => 'array'
			];
		} 
		else
		{
			$rules = [
				'clues' => 'required|min:3|max:250'
			];
		}
		$v = \Validator::make($datos, $rules );

		if ($v->fails())
		{
			return Response::json($v->errors());
		}		
		$success = false;
		$date=new \DateTime;
		
        DB::beginTransaction();
        try 
		{
			$evaluacion = '';
			$hayhallazgo = false;
			// valida si el objeto json evaluaciones exista, esto es para los envios masivos de evaluaciones
			$item = (object) $datos;
			$respuesta = array();
							
				
			if(!array_key_exists("idUsuario",$item))
				$item->idUsuario=$usuario->id;
			
			// validar que no exista la evaluacion con la misma fecha
			$fecha = $item->fechaEvaluacion;
			$date = new \DateTime($fecha);
			$fecha = $date->format('Y-m-d');

			$existe_fecha = DB::table('EvaluacionRecursoResincronizacion')
			->where(DB::raw("DATE_FORMAT(fechaEvaluacion, '%Y-%m-%d')"), $fecha)
			->where("clues", $item->clues)->first();
			
			if(!$existe_fecha){

				$usuario = Usuario::where('id', $item->idUsuario)->first();
				$evaluacion = new EvaluacionRecursoResincronizacion ;
				$evaluacion->clues = isset($item->clues) ? $item->clues : $evaluacion->clues;
				$evaluacion->idUsuario = $item->idUsuario;
				$evaluacion->fechaEvaluacion  = $item->fechaEvaluacion ;
				$evaluacion->cerrado = $item->cerrado;
				$evaluacion->firma = array_key_exists("firma",$item) ? $item->firma : '';
				$evaluacion->responsable = array_key_exists("responsable",$item) ? $item->responsable : '';
				$evaluacion->email = array_key_exists("email",$item) ? $item->email : '';
				$evaluacion->enviado = 0;
				
				if ($evaluacion->save()) 
				{
					$success = true;
					// si se guarda la evaluacion correctamente.
					// extrae tosdos los criterios de la evaluación
					$aprobado = 0; $noAprobado = 0; $noAplica = 0;
					foreach($item->criterios as $criterio)
					{
						$criterio = (object) $criterio;
						$evaluacionCriterio = EvaluacionRecursoCriterioResincronizacion::where('idEvaluacionRecurso',$evaluacion->id)
																->where('idCriterio',$criterio->idCriterio)
																->where('idIndicador',$criterio->idIndicador)->first();
						
						if(!$evaluacionCriterio)
							$evaluacionCriterio = new EvaluacionRecursoCriterioResincronizacion;
						
						$evaluacionCriterio->idEvaluacionRecurso = $evaluacion->IdEvaluacionRecurso;
						$evaluacionCriterio->idCriterio = $criterio->idCriterio;
						$evaluacionCriterio->idIndicador = $criterio->idIndicador;
						$evaluacionCriterio->aprobado = $criterio->aprobado;
						
						if ($evaluacionCriterio->save()) 
						{								
							$success = true;
							if($criterio->aprobado == 1)
								$aprobado++;
							else if($criterio->aprobado == 0)
								$noAprobado++;
							else
								$noAplica++;

							$evaluacionRegistro = EvaluacionRecursoRegistroResincronizacion::where('idEvaluacionRecurso',$evaluacion->id)
																->where('idIndicador',$criterio->idIndicador)->first();
							if(!$evaluacionRegistro)
								$evaluacionRegistro = new EvaluacionRecursoRegistroResincronizacion;

							$evaluacionRegistro->idEvaluacionRecurso = $evaluacion->IdEvaluacionRecurso;
							$evaluacionRegistro->idIndicador = $criterio->idIndicador;
							$evaluacionRegistro->total = $aprobado + $noAprobado + $noAplica;
							$evaluacionRegistro->aprobado = $aprobado;
							$evaluacionRegistro->noAprobado = $noAprobado;
							$evaluacionRegistro->noAplica = $noAplica;

							$evaluacionRegistro->save();								
						} 
					}
					// recorrer todos los halazgos encontrados por evaluación
					foreach($item->hallazgos as $hs)
					{
						$hs = (object) $hs;
						if(!array_key_exists("idUsuario",$hs))
							$hs->idUsuario=$usuario->id;
						if(!array_key_exists("idPlazoAccion",$hs))
							$hs->idPlazoAccion=null;
						if(!array_key_exists("resuelto",$hs))
							$hs->resuelto=0;
						$usuario = Usuario::where('id', $hs->idUsuario)->first();
						$usuarioPendiente=$usuario->id;
						
						$hallazgo = HallazgoResincronizacion::where('idIndicador',$hs->idIndicador)->where('idEvaluacion',$evaluacion->id)->first();
						
						if(!$hallazgo)							
							$hallazgo = new HallazgoResincronizacion;
												
						$hallazgo->idUsuario = $hs->idUsuario;
						$hallazgo->idAccion = $hs->idAccion;
						$hallazgo->idEvaluacion = $evaluacion->IdEvaluacionRecurso;
						$hallazgo->idIndicador = $hs->idIndicador;
						$hallazgo->categoriaEvaluacion  = 'RECURSO';
						$hallazgo->idPlazoAccion = $hs->idPlazoAccion;
						$hallazgo->resuelto = $hs->resuelto;
						$hallazgo->descripcion = $hs->descripcion;
						
						if($hallazgo->save())
						{
							$hayhallazgo = true;
							$success=true;
						}								
					}
					if(isset($item->criterio_respuestas)){
						foreach($item->criterio_respuestas as $valorcriterio){
							
							if(isset($valorcriterio)){
								if(is_array($valorcriterio))
									$valorcriterio = (object) $valorcriterio;
								
								foreach($valorcriterio as $res){
									if(is_array($res))
										$res = (object) $res;
									if($res){
										$criterio_respuestas = CriterioValidacionRespuesta::where('tipo','RECURSO')
																						  ->where('idEvaluacion',$evaluacion->id)
																						  ->where('idCriterio',$res->idCriterio)
																						  ->where('idCriterioValidacion',$res->idCriterioValidacion)
																						  ->first();	
																						
										if(!$criterio_respuestas)							
											$criterio_respuestas = new CriterioValidacionRespuesta;
										
										$criterio_respuestas->idEvaluacion = $evaluacion->IdEvaluacionRecurso;
										$criterio_respuestas->idCriterio = $res->idCriterio;
										$criterio_respuestas->idCriterioValidacion  = $res->idCriterioValidacion;
										$criterio_respuestas->tipo = 'RECURSO';
										$criterio_respuestas->respuesta1 = $res->respuesta1;
										$criterio_respuestas->respuesta2 = $res->respuesta2;
										
										$criterio_respuestas->save();
									}									
								}
							}							
						}						
					}
				}				
			}	
			else{
				return Response::json(array("status"=>409,"messages"=>"Existe","data"=>$existe_fecha),200);
			}							
			                       
        } 
		catch (\Exception $e) 
		{
			DB::rollback();			
			throw $e;
        }
        if ($success) 
		{
            DB::commit();            
			return Response::json(array("status"=>201,"messages"=>"Creado","data"=>$evaluacion),201);
        } 
		else 
		{
            DB::rollback();
			return Response::json(array("status"=>500,"messages"=>"Error interno del servidor"),500);
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
	public function show($id)
	{
		$datos = Request::all();
		
		$user = Usuario::where('email', Request::header('X-Usuario'))->first();
		$evaluacion = DB::table('EvaluacionRecursoResincronizacion  AS AS')
			->leftJoin('Clues AS c', 'c.clues', '=', 'AS.clues')
			->leftJoin('ConeClues AS cc', 'cc.clues', '=', 'AS.clues')
			->leftJoin('Cone AS co', 'co.id', '=', 'cc.idCone')
			->leftJoin('usuarios AS us', 'us.id', '=', 'AS.idUsuario')
			->leftJoin('ZonaClues AS zc', 'zc.clues', '=', 'AS.clues')
			->leftJoin('Zona AS z', 'z.id', '=', 'zc.idZona')
            ->select(array('z.nombre as zona','us.email','AS.firma','AS.responsable','AS.fechaEvaluacion', 'AS.cerrado', 'AS.id','AS.clues', 'c.nombre', 'c.domicilio', 'c.codigoPostal', 'c.entidad', 'c.municipio', 'c.localidad', 'c.jurisdiccion', 'c.institucion', 'c.tipoUnidad', 'c.estatus', 'c.estado', 'c.tipologia','co.nombre as nivelCone', 'cc.idCone'))
            ->where('AS.id',"$id");
		if(!array_key_exists("dashboard",$datos))
		{
			$cluesUsuario=$this->permisoZona($user->id);
			$evaluacion = $evaluacion->whereIn('c.clues',$cluesUsuario);
		}
		
		$evaluacion = $evaluacion->first();

		if(!$evaluacion)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),404);
		} 
		else 
		{
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$evaluacion),200);
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
		$success = false;
        DB::beginTransaction();
        try 
		{
			$evaluacion = EvaluacionRecursoResincronizacion::where("id",$id)->where("cerrado",null)->orWhere("cerrado",0)->first();
			if($evaluacion)
			{
				$evaluacion->delete();
				$success=true;
			}
			else{
				return Response::json(array('status'=> 304,"messages"=>'No se puede borrar ya fue cerrado'),304);
			}
		} 
		catch (\Exception $e) 
		{
			throw $e;
        }
        if ($success)
		{
			DB::commit();				
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$evaluacion),200);
		} 
		else 
		{
			DB::rollback();
			return Response::json(array('status'=> 500,"messages"=>'Error interno del servidor'),500);
		}
	}
	
	/**
	 * Obtener la lista de clues que el usuario tiene acceso.
	 *
	 * get session sentry, usuario logueado
	 * Response si la operacion es exitosa devolver un array con el listado de clues
	 * @return array	 
	 */
	public function permisoZona()
	{
		$cluesUsuario=array();
		$clues=array();
		$cone=ConeClues::all(["clues"]);
		$cones=array();
		foreach($cone as $item)
		{
			array_push($cones,$item->clues);
		}		
		$user = Usuario::where('email', Request::header('X-Usuario'))->first();
		if($user->nivel==1)
			$clues = Clues::whereIn('clues',$cones)->get();
		else if($user->nivel==2)
		{
			$result = DB::table('UsuarioJurisdiccion')
				->where('idUsuario', $user->id)
				->get();
		
			foreach($result as $item)
			{
				array_push($cluesUsuario,$item->jurisdiccion);
			}
			$clues = Clues::whereIn('clues',$cones)->whereIn('jurisdiccion',$cluesUsuario)->get();
		}
		else if($user->nivel==3)
		{
			$result = DB::table('UsuarioZona AS u')
			->leftJoin('Zona AS z', 'z.id', '=', 'u.idZona')
			->leftJoin('ZonaClues AS zu', 'zu.idZona', '=', 'z.id')
			->select(array('zu.clues'))
			->where('u.idUsuario', $user->id)
			->get();
			
			foreach($result as $item)
			{
				array_push($cluesUsuario,$item->clues);
			}
			$clues = Clues::whereIn('clues',$cones)->whereIn('clues',$cluesUsuario)->get();
		}
		$cluesUsuario=array();
		foreach($clues as $item)
		{
			array_push($cluesUsuario,$item->clues);
		}
		return $cluesUsuario;
	}
}
?>