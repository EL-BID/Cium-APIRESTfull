<?php
namespace App\Http\Controllers\v1\Transacciones;

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

use App\Models\Transacciones\Hallazgo;
use App\Models\Transacciones\EvaluacionRecurso;
use App\Models\Transacciones\EvaluacionRecursoCriterio;
use App\Models\Transacciones\EvaluacionRecursoRegistro;

use App\Models\Catalogos\CriterioValidacionRespuesta;

use App\Jobs\ReporteRecurso;
use App\Jobs\ReporteHallazgo;
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
class EvaluacionRecursoController extends Controller 
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

				$indicadores = [];
				if($datos['indicador'] != ''){
					$variable = EvaluacionRecursoCriterio::select('idEvaluacionRecurso')->distinct()->where("idIndicador", $datos['indicador'])->get();
					foreach ($variable as $key => $value) {
						$indicadores[] = $value->idEvaluacionRecurso;
					}
				}
			
				$fecha = $datos['hasta']; 
				$nuevafecha = strtotime ( '+1 day' , strtotime ( $fecha ) ) ;
				$hasta = date ( 'Y-m-d' , $nuevafecha );
				
				$columna = $datos['columna'];
				$valor   = $datos['valor'];
				$evaluacion = EvaluacionRecurso::with("cone","usuarios","cluess")
				->distinct()->select("EvaluacionRecurso.*")
				->leftJoin('Clues', 'Clues.clues', '=', 'EvaluacionRecurso.clues')
				->leftJoin('usuarios', 'usuarios.id', '=', 'EvaluacionRecurso.idUsuario')
				->leftJoin('ConeClues AS cc', 'cc.clues', '=', 'EvaluacionRecurso.clues')
				->leftJoin('Cone AS co', 'co.id', '=', 'cc.idCone')

				->where('Clues.jurisdiccion', 'LIKE', '%'.$datos['jurisdiccion'].'%')
				->where('usuarios.email', 'LIKE', '%'.$datos['email'].'%')
				->where('co.nombre', 'LIKE', '%'.$datos['cone'].'%')	
				->whereIn('EvaluacionRecurso.clues',$cluesUsuario);

				if($datos['desde'] != '' && $datos['hasta'] != '')
				{
					$evaluacion=$evaluacion->whereBetween('fechaEvaluacion', [$datos['desde'], $hasta]);
				}
				if(count($indicadores) > 0){
					$evaluacion=$evaluacion->whereIn('EvaluacionRecurso.id',$indicadores);
				}
				
				$search = trim($valor);
				$keyword = $search;
				$evaluacion=$evaluacion->whereNested(function($query) use ($keyword)
				{
					$query->Where('Clues.clues', 'LIKE', '%'.$keyword.'%')					 				
					 ->orWhere('Clues.nombre', 'LIKE', '%'.$keyword.'%'); 
 
				});
				$total=$evaluacion->get();
				$evaluacion = $evaluacion->skip($pagina-1)->take($datos['limite'])
				->orderBy($order,$orden)->get();
				
				
			}
			else
			{
				$evaluacion = EvaluacionRecurso::with("cone","usuarios","cluess")->select("EvaluacionRecurso.id", "EvaluacionRecurso.idUsuario"
					, "EvaluacionRecurso.clues", "EvaluacionRecurso.fechaEvaluacion", "EvaluacionRecurso.cerrado", "EvaluacionRecurso.firma"
					, "EvaluacionRecurso.responsable", "EvaluacionRecurso.email", "EvaluacionRecurso.enviado", "EvaluacionRecurso.creadoAl"
					, "EvaluacionRecurso.modificadoAl", "EvaluacionRecurso.borradoAl")
												->leftJoin('Clues', 'Clues.clues', '=', 'EvaluacionRecurso.clues')
												->leftJoin('usuarios', 'usuarios.id', '=', 'EvaluacionRecurso.idUsuario')
				->whereIn('EvaluacionRecurso.clues',$cluesUsuario)->skip($pagina-1)->take($datos['limite'])
				->orderBy($order,$orden)->get();
				$total=EvaluacionRecurso::whereIn('EvaluacionRecurso.clues',$cluesUsuario)->get();
			}
			
		}
		else
		{
			$evaluacion = EvaluacionRecurso::with("cone","usuarios","cluess")->whereIn('clues',$cluesUsuario)->get();
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
			$hayhallazgo = false;
			// valida si el objeto json evaluaciones exista, esto es para los envios masivos de evaluaciones
			$datos = (object) $datos;
			$respuesta = array();
			if(array_key_exists("evaluaciones",$datos))
			{				
				foreach($datos->evaluaciones as $item)
				{
					$item = (object) $item;
					if(!array_key_exists("idUsuario",$item))
						$item->idUsuario=$usuario->id;
					
					$usuario = Usuario::where('id', $item->idUsuario)->first();

					// validar que no exista la evaluacion con la misma fecha
					$fecha = $item->fechaEvaluacion;
					$date = new \DateTime($fecha);
					$fecha = $date->format('Y-m-d');

					$existe_fecha = DB::table('EvaluacionRecurso')
					->where(DB::raw("DATE_FORMAT(fechaEvaluacion, '%Y-%m-%d')"), $fecha)
					->where("clues", $item->clues)->first();
					
					if(!$existe_fecha){

						$evaluacion = new EvaluacionRecurso ;
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
								$evaluacionCriterio = EvaluacionRecursoCriterio::where('idEvaluacionRecurso',$evaluacion->id)
																		->where('idCriterio',$criterio->idCriterio)
																		->where('idIndicador',$criterio->idIndicador)->first();
								
								if(!$evaluacionCriterio)
									$evaluacionCriterio = new EvaluacionRecursoCriterio;
								
								$evaluacionCriterio->idEvaluacionRecurso = $evaluacion->id;
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

									$evaluacionRegistro = EvaluacionRecursoRegistro::where('idEvaluacionRecurso',$evaluacion->id)
																		->where('idIndicador',$criterio->idIndicador)->first();
									if(!$evaluacionRegistro)
										$evaluacionRegistro = new EvaluacionRecursoRegistro;

									$evaluacionRegistro->idEvaluacionRecurso = $evaluacion->id;
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
								
								$hallazgo = Hallazgo::where('idIndicador',$hs->idIndicador)->where('idEvaluacion',$evaluacion->id)->first();
								
								if(!$hallazgo)							
									$hallazgo = new Hallazgo;
														
								$hallazgo->idUsuario = $hs->idUsuario;
								$hallazgo->idAccion = $hs->idAccion;
								$hallazgo->idEvaluacion = $evaluacion->id;
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
							if(isset($item->criterio_respuestas))
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
											
											$criterio_respuestas->idEvaluacion = $evaluacion->id;
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
						$respuesta[] = $evaluacion;
					}
				}				
			}
			// si la evaluación es un json de un solo formulario
			else
			{
				$usuario = Usuario::where('email', Request::header('X-Usuario'))->first();
				$datos = (object) $datos;
				if(!array_key_exists("idUsuario",$datos))
					$datos->idUsuario=$usuario->id;
				if(!array_key_exists("fechaEvaluacion",$datos))
					$datos->fechaEvaluacion =$date->format('Y-m-d H:i:s');

				// validar que no exista la evaluacion con la misma fecha
				$fecha = $datos->fechaEvaluacion;
				$date2 = new \DateTime($fecha);
				$fecha = $date2->format('Y-m-d');

				$existe_fecha = DB::table('EvaluacionRecurso')
				->where(DB::raw("DATE_FORMAT(fechaEvaluacion, '%Y-%m-%d')"), $fecha)
				->where("clues", $datos->clues)->first();
				
				if(!$existe){
					$evaluacion = new EvaluacionRecurso ;
					$evaluacion->clues = $datos->clues;
					$evaluacion->idUsuario = $datos->idUsuario;
					$evaluacion->fechaEvaluacion  = $datos->fechaEvaluacion ;
					if(array_key_exists("cerrado",$datos))
						$evaluacion->cerrado = $datos->cerrado;
					$evaluacion->firma = array_key_exists("firma",$datos) ? $datos->firma : '';
					$evaluacion->responsable = array_key_exists("responsable",$datos) ? $datos->responsable : '';
					$evaluacion->email = array_key_exists("email",$datos) ? $datos->email : '';
					$evaluacion->enviado = 0;

					if ($evaluacion->save()) 
					{
						$success = true;
						$respuesta = $evaluacion;
					}
				} else {
					return Response::json(array("status"=>409,"messages"=>"Esta evaluacion no fue sincronizada debido a que ya existe una con la misma fecha","data"=>0),200);
				}
			}                        
        } 
		catch (\Exception $e) 
		{
			throw $e;
        }
        if ($success) 
		{
            DB::commit();
            if($evaluacion->cerrado)
			{
				$this->dispatch(new ReporteRecurso($evaluacion));
				//$spr = DB::select('call sp_recurso()');	
				if($hayhallazgo){
					$this->dispatch(new ReporteHallazgo($evaluacion));
				}
				//	$sph = DB::select('call sp_hallazgo()');
			}
			return Response::json(array("status"=>201,"messages"=>"Creado","data"=>$respuesta),201);
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
		$evaluacion = DB::table('EvaluacionRecurso  AS AS')
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
	public function update($id)
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
        DB::beginTransaction();
        try 
		{
			$hayhallazgo = false;
			$usuario = Usuario::where('email', Request::header('X-Usuario'))->first();
			// valida si el objeto json evaluaciones exista, esto es para los envios masivos de evaluaciones
			$datos = (object) $datos;
			$respuesta = array();
			if(array_key_exists("evaluaciones",$datos))
			{				
				foreach($datos->evaluaciones as $item)
				{
					$item = (object) $item;
					if(!array_key_exists("idUsuario",$item))
						$item->idUsuario=$usuario->id;
					$usuario = Usuario::where('id', $item->idUsuario)->first();
					$evaluacion = EvaluacionRecurso::find($item->id);

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
						$success=true;
						
						// si se guarda la evaluacion correctamente.
						// extrae tosdos los criterios de la evaluación
						$aprobado = 0; $noAprobado = 0; $noAplica = 0;
						foreach($item->criterios as $criterio)
						{
							$criterio = (object) $criterio;
							
							$borrado = DB::table('EvaluacionRecursoCriterio')								
							->where('idEvaluacionRecurso',$evaluacion->id)
							->where('idCriterio',$criterio->idCriterio)
							->where('idIndicador',$criterio->idIndicador)
							->update(['borradoAL' => NULL]);
					
							$evaluacionCriterio = EvaluacionRecursoCriterio::where('idEvaluacionRecurso',$evaluacion->id)
																	->where('idCriterio',$criterio->idCriterio)
																	->where('idIndicador',$criterio->idIndicador)->first();
							
							if(!$evaluacionCriterio)
								$evaluacionCriterio = new EvaluacionRecursoCriterio;
							
							$evaluacionCriterio->idEvaluacionRecurso = $evaluacion->id;
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

								$evaluacionRegistro = EvaluacionRecursoRegistro::where('idEvaluacionRecurso',$evaluacion->id)
																	->where('idIndicador',$criterio->idIndicador)->first();
								if(!$evaluacionRegistro)
									$evaluacionRegistro = new EvaluacionRecursoRegistro;

								$evaluacionRegistro->idEvaluacionRecurso = $evaluacion->id;
								$evaluacionRegistro->idIndicador = $criterio->idIndicador;
								$evaluacionRegistro->total = $aprobado + $noAprobado + $noAplica;
								$evaluacionRegistro->aprobado = $aprobado;
								$evaluacionRegistro->noAprobado = $noAprobado;
								$evaluacionRegistro->noAplica = $noAplica;

								$evaluacionRegistro->save();									
							}

							if(count($item->hallazgos)==0)
							{
								$hallazgos = Hallazgo::where('idIndicador',$criterio->idIndicador)->where('idEvaluacion',$evaluacion->id)->get();
								foreach($hallazgos as $hz)
								{
									$hallazgo = Hallazgo::find($hz->id);
									$hallazgo->delete();
								}
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
							
							$borrado = DB::table('Hallazgo')					
							->where('idIndicador',$hs->idIndicador)
							->where('idEvaluacion',$evaluacion->id)
							->update(['borradoAL' => NULL]);
							
							$hallazgo = Hallazgo::where('idIndicador',$hs->idIndicador)->where('idEvaluacion',$evaluacion->id)->first();															
							if(!$hallazgo)							
								$hallazgo = new Hallazgo;					
								
							$hallazgo->idUsuario = $hs->idUsuario;
							$hallazgo->idAccion = $hs->idAccion;
							$hallazgo->idEvaluacion = $evaluacion->id;
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
						if(isset($item->criterio_respuestas))
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
										
										$criterio_respuestas->idEvaluacion = $evaluacion->id;
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
					$respuesta[] = $evaluacion;
				}				
			}
			// si la evaluación es un json de un solo formulario
			else
			{
				$usuario = Usuario::where('email', Request::header('X-Usuario'))->first();
				$datos = (object) $datos;
				if(!array_key_exists("idUsuario",$datos))
					$datos->idUsuario=$usuario->id;
				$evaluacion = EvaluacionRecurso::find($datos->id);
				$evaluacion->clues = $datos->clues;
				$evaluacion->idUsuario = $datos->idUsuario;
				$evaluacion->fechaEvaluacion  = $datos->fechaEvaluacion ;
				if(array_key_exists("cerrado",$datos))
					$evaluacion->cerrado = $datos->cerrado;
				$evaluacion->firma = array_key_exists("firma",$datos) ? $datos->firma : '';
				$evaluacion->responsable = array_key_exists("responsable",$datos) ? $datos->responsable : '';
				$evaluacion->email = array_key_exists("email",$datos) ? $datos->email : '';
				$evaluacion->enviado = 0;
				if ($evaluacion->save()) 
				{
					$success = true;
					$respuesta = $evaluacion;
				}
			}                 			
		} 
		catch (\Exception $e) 
		{
			throw $e;
        }
        if ($success)
		{
			DB::commit();
			if($evaluacion->cerrado)
			{
				$this->dispatch(new ReporteRecurso($evaluacion));
				if($hayhallazgo){
					$this->dispatch(new ReporteHallazgo($evaluacion));
				}

				/*$spr = DB::select('call sp_recurso()');	
				if($hayhallazgo)
					$sph = DB::select('call sp_hallazgo()');*/
			}
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$respuesta),200);
		} 
		else 
		{
			DB::rollback();
			return Response::json(array('status'=> 304,"messages"=>'No modificado'),304);
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
			$evaluacion = EvaluacionRecurso::where("id",$id)->where("cerrado",null)->orWhere("cerrado",0)->first();
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
	 * Guarde un hallazgo de la evaluación recurso. para generar un hallazgo con un criterio que no se cumpla en el indicador
	 *
	 * <h4>Request</h4>
	 * Input request json de los recursos a almacenar en la tabla correspondiente
	 * @return Response
	 * <code style="color:green"> Respuesta Ok json(array("status": 201, "messages": "Creado", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 500, "messages": "Error interno del servidor"),status) </code>
	 */
	public function Hallazgos()
	{
		$datos = Input::json(); 
		$success = false;
		$date=new \DateTime;
		$idIndicador = $datos->get('idIndicador');
		$idEvaluacion = $datos->get('idEvaluacion');
        DB::beginTransaction();
        try 
		{
			$usuario = Usuario::where('email', Request::header('X-Usuario'))->first();
			$borrado = DB::table('Hallazgo')					
			->where('idIndicador',$idIndicador)
			->where('idEvaluacion',$idEvaluacion)
			->update(['borradoAL' => NULL]);
			
			$usuarioPendiente=$usuario->id;
			$hallazgo = Hallazgo::where('idIndicador',$idIndicador)->where('idEvaluacion',$idEvaluacion)->first();
			
			$nuevo=false;
			if(!$hallazgo)
			{
				$nuevo=true;
				$hallazgo = new Hallazgo;
			}					
			
			if($datos->get('aprobado')==0)
			{
				if($datos->get('idAccion'))
				{
					$hallazgo->idUsuario = $usuario->id;
					$hallazgo->idAccion = $datos->get('idAccion');
					$hallazgo->idEvaluacion = $idEvaluacion;
					$hallazgo->idIndicador = $datos->get('idIndicador');
					$hallazgo->categoriaEvaluacion  = 'RECURSO';
					$hallazgo->idPlazoAccion = array_key_exists('idPlazoAccion',$datos) ? $datos->get('idPlazoAccion') : 0;
					$hallazgo->resuelto = $datos->get('resuelto');
					$hallazgo->descripcion = $datos->get('descripcion');
										
					
					if($hallazgo->save())
					{
						$success=true;
					}
				}
			}
			else
			{
				if($hallazgo->id)
				{
					$hallazgo = Hallazgo::find($hallazgo->id);
					$hallazgo->delete();
					$success=true;
				}
			}
		} 
		catch (\Exception $e) 
		{
			throw $e;
        }
        if ($success) 
		{
            DB::commit();
			return Response::json(array("status"=>201,"messages"=>"Creado","data"=>$hallazgo),201);
        } 
		else 
		{
            DB::rollback();
			return Response::json(array("status"=>500,"messages"=>"Error interno del servidor"),500);
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


	/**
	 * Enviar al correo la evaluacion del responsable de la unidad medica.
	 *
	 * <h4>Request</h4>
	 * Input request json de los recursos a enviar por correo
	 * @return Response
	 * <code style="color:green"> Respuesta Ok json(array("status": 200, "messages": "Enviado", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 500, "messages": "Error interno del servidor"),status) </code>
	 */
	public function Correo($id){
		$data = [];
		try{
			$success = true;
		    $evaluacion = DB::table('EvaluacionRecurso  AS AS')
		            ->leftJoin('Clues AS c', 'c.clues', '=', 'AS.clues')
		            ->leftJoin('ConeClues AS cc', 'cc.clues', '=', 'AS.clues')
		            ->leftJoin('Cone AS co', 'co.id', '=', 'cc.idCone')
		            ->leftJoin('usuarios AS us', 'us.id', '=', 'AS.idUsuario')
		            ->leftJoin('ZonaClues AS zc', 'zc.clues', '=', 'AS.clues')
		            ->leftJoin('Zona AS z', 'z.id', '=', 'zc.idZona')
		            ->select(array('z.nombre as zona','AS.email','AS.firma','AS.responsable','AS.fechaEvaluacion', 'AS.cerrado', 'AS.id','AS.clues', 'c.nombre', 'c.domicilio', 'c.codigoPostal', 'c.entidad', 'c.municipio', 'c.localidad', 'c.jurisdiccion', 'c.institucion', 'c.tipoUnidad', 'c.estatus', 'c.estado', 'c.tipologia','co.nombre as nivelCone', 'cc.idCone'))
		            ->where('AS.id',"$id")->first();
		    $data["evaluacion"] = $evaluacion;
		    
		    $indicatores = DB::select("select i.id,i.color,i.codigo,i.nombre from EvaluacionRecursoCriterio erc
		                        left join Indicador as i on i.id= erc.idIndicador 
		                        where erc.idEvaluacionRecurso = $id and i.borradoAl is null and erc.borradoAl is null order by i.codigo");
		                        
		    $indicadores = [];      
	        $cone = $evaluacion->idCone;
	        //inicia llenado de indicadores
	        foreach($indicatores as $indicator)
	        {
	            $criterio = DB::select("SELECT c.id as idCriterio, ic.idIndicador, cic.idCone, lv.id as idlugarVerificacion, c.creadoAl, c.modificadoAl, c.nombre as criterio, lv.nombre as lugarVerificacion 
	            FROM ConeIndicadorCriterio cic                          
	            left join IndicadorCriterio ic on ic.id = cic.idIndicadorCriterio
	            left join Criterio c on c.id = ic.idCriterio
	            left join LugarVerificacion lv on lv.id = ic.idlugarVerificacion        
	            WHERE cic.idCone = $cone and ic.idIndicador = $indicator->id 
	            and cic.borradoAl is null and ic.borradoAl is null and c.borradoAl is null and lv.borradoAl is null");                              
	            $criterios = array();       
	            foreach($criterio as $valor)
	            {
	                $aprobado = DB::select("SELECT aprobado from EvaluacionRecursoCriterio where idEvaluacionRecurso = $id
	                                                                and idIndicador = $indicator->id
	                                                                and idCriterio = $valor->idCriterio and borradoAl is null");
	                if($aprobado)
	                    $valor->aprobado = $aprobado[0]->aprobado;
	                else
	                    $valor->aprobado = 2;
	                if(!array_key_exists($valor->lugarVerificacion, $criterios))
	                    $criterios[$valor->lugarVerificacion] = [];
	                array_push($criterios[$valor->lugarVerificacion],$valor);
	            }
	            
	            $criterios["indicador"] = $indicator;
	            $hallazgo = DB::select("SELECT h.idIndicador, h.idAccion, h.idPlazoAccion, h.resuelto, h.descripcion, a.tipo, a.nombre as accion FROM Hallazgo h    
	            left join Accion a on a.id = h.idAccion WHERE h.idEvaluacion= $id and categoriaEvaluacion='RECURSO' and idIndicador = $indicator->id and h.borradoAl is null");
	            if($hallazgo)
	                $criterios["hallazgo"] = $hallazgo[0];
	            if(!isset($indicatores[$indicator->codigo])){
	            	$indicatores[$indicator->codigo] = [];
	            }
	            $indicatores[$indicator->codigo] = $criterios;
	        } 
	        //fin indicador 
	        $estadistica = array();
	        foreach($indicatores as $item)
	        {
	            if(!array_key_exists($item->codigo,$estadistica))
	            {
	                $indicador = $item->id;
	                
	                $total = DB::select("SELECT c.id,c.nombre  FROM ConeIndicadorCriterio cic                           
	                        left join IndicadorCriterio ic on ic.id = cic.idIndicadorCriterio
	                        left join Criterio c on c.id = ic.idCriterio
	                        left join Indicador i on i.id = ic.idIndicador
	                        left join LugarVerificacion lv on lv.id = ic.idlugarVerificacion        
	                        WHERE cic.idCone = $cone and ic.idIndicador = '$indicador' 
	                        and c.borradoAl is null and ic.borradoAl is null and cic.borradoAl is null and lv.borradoAl is null order by i.codigo");
	                        
	                $in=[];
	                foreach($total as $c)
	                {
	                    $in[]=$c->id;
	                }
	                
	                $aprobado = DB::table('EvaluacionRecursoCriterio')->select('idCriterio')
	                            ->whereIN('idCriterio',$in)
	                            ->where('idEvaluacionRecurso',$id)
	                            ->where('idIndicador',$indicador)
	                            ->where('borradoAl',null)->where('aprobado',1)->get();              
	                $na = DB::table('EvaluacionRecursoCriterio')
	                            ->select('idCriterio')
	                            ->whereIN('idCriterio',$in)
	                            ->where('idEvaluacionRecurso',$id)
	                            ->where('aprobado',2)
	                            ->where('borradoAl',null)->get();               
	                
	                $totalPorciento = number_format((count($aprobado)/(count($total)-count($na)))*100, 2, '.', '');
	                
	                $item->indicadores["totalCriterios"] = count($total);
	                $item->indicadores["totalAprobados"] = count($aprobado);
	                $item->indicadores["totalNoAplica"] = count($na);
	                $item->indicadores["totalPorciento"] = $totalPorciento;
	                $micolor=DB::select("SELECT a.color FROM IndicadorAlerta ia 
	                                       left join Alerta a on a.id=ia.idAlerta
	                                       where ia.idIndicador = $indicador  and $totalPorciento between ia.minimo and ia.maximo");
	                if($micolor)
	                    $micolor=$micolor[0]->color;
	                else
	                    $micolor="rgb(200,200,200)";
	                $item->indicadores["totalColor"] = $micolor;
	                
	                $estadistica[$item->codigo] = $item;                
	            }               
	        }
	        
	        $data["indicadores"] = $indicadores;
	        $data["estadistica"] = $estadistica;
		    \Mail::send('emails.recurso', $data, function($message) use($data){
		        $message->to($data["evaluacion"]->email, "Evaluacion Recurso")->subject('CIUM');
		    });
		    $envio = EvaluacionRecurso::find($id);
		    $envio->enviado = 1;
		    $envio->save();
		}		
	    catch (\Exception $e) 
		{
			$success = false;
			throw $e;
        }
	    if ($success) 
		{
			return Response::json(array("status"=>200,"messages"=>"Enviado","data"=>$envio),200);
        } 
		else 
		{
			return Response::json(array("status"=>500,"messages"=>"Error interno del servidor"),500);
        }
	}
}
?>