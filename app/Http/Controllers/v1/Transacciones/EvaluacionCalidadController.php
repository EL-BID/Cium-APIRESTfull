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

use App\Models\Transacciones\EvaluacionCalidad;
use App\Models\Transacciones\EvaluacionCalidadCriterio;
use App\Models\Transacciones\EvaluacionCalidadRegistro;
use App\Models\Transacciones\Hallazgo;

use App\Models\Catalogos\CriterioValidacionRespuesta;

use App\Jobs\ReporteCalidad;
use App\Jobs\ReporteHallazgo;
/**
* Controlador Evaluación (calidad)
* 
* @package    CIUM API
* @subpackage Controlador
* @author     Eliecer Ramirez Esquinca <ramirez.esquinca@gmail.com>
* @created    2015-07-20
*
* Controlador `Criterios Calidad`: Maneja los datos para los criterios de las evaluaciones
*
*/
class EvaluacionCalidadController extends Controller 
{	
	/**
	 * Inicia el contructor para los permisos de visualizacion
	 *	 
	 */
    public function __construct()
    {
        $this->middleware('permisos:GET.63616C6964|POST.646164696C|PUT.616C696461|DELETE.C696466964');
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
				$evaluacion = EvaluacionCalidad::with("cone","usuarios","cluess")->distinct()->select("EvaluacionCalidad.id", "EvaluacionCalidad.idUsuario"
					, "EvaluacionCalidad.clues", "EvaluacionCalidad.fechaEvaluacion", "EvaluacionCalidad.cerrado", "EvaluacionCalidad.firma"
					, "EvaluacionCalidad.responsable", "EvaluacionCalidad.email", "EvaluacionCalidad.enviado", "EvaluacionCalidad.creadoAl"
					, "EvaluacionCalidad.modificadoAl", "EvaluacionCalidad.borradoAl")
												->leftJoin('Clues', 'Clues.clues', '=', 'EvaluacionCalidad.clues')
												->leftJoin('usuarios', 'usuarios.id', '=', 'EvaluacionCalidad.idUsuario')
												->whereIn('EvaluacionCalidad.clues',$cluesUsuario)->orderBy($order,$orden);
				
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
				$total=$evaluacion->get();
				$evaluacion = $evaluacion->skip($pagina-1)->take($datos['limite'])->get();
			}
			else
			{
				$evaluacion = EvaluacionCalidad::with("cone","usuarios","cluess")->select("EvaluacionCalidad.id", "EvaluacionCalidad.idUsuario"
					, "EvaluacionCalidad.clues", "EvaluacionCalidad.fechaEvaluacion", "EvaluacionCalidad.cerrado", "EvaluacionCalidad.firma"
					, "EvaluacionCalidad.responsable", "EvaluacionCalidad.email", "EvaluacionCalidad.enviado", "EvaluacionCalidad.creadoAl"
					, "EvaluacionCalidad.modificadoAl", "EvaluacionCalidad.borradoAl")
												->leftJoin('Clues', 'Clues.clues', '=', 'EvaluacionCalidad.clues')
												->leftJoin('usuarios', 'usuarios.id', '=', 'EvaluacionCalidad.idUsuario')
				->whereIn('EvaluacionCalidad.clues',$cluesUsuario)->skip($pagina-1)->take($datos['limite'])->orderBy($order,$orden)->get();
				$total=EvaluacionCalidad::with("cone","usuarios")->whereIn('EvaluacionCalidad.clues',$cluesUsuario)->get();
			}
			
		}
		else
		{
			$evaluacion = EvaluacionCalidad::with("cone","usuarios", "cluess")->whereIn('clues',$cluesUsuario)->get();
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
			$usuario = Usuario::where('email', Request::header('X-Usuario'))->first();
			// valida si el objeto json evaluaciones exista, esto es para los envios masivos de evaluaciones
			if(array_key_exists("evaluaciones",$datos))
			{
				$datos = (object) $datos;
				$respuesta = array();
				foreach($datos->evaluaciones as $item)
				{
					$item = (object) $item;
					if(!array_key_exists("idUsuario",$item))
						$item->idUsuario=$usuario->id;

					// validar que no exista la evaluacion con la misma fecha
					$fecha = $item->fechaEvaluacion;
					$date = new \DateTime($fecha);
					$fecha = $date->format('Y-m-d');

					$existe_fecha = DB::table('EvaluacionCalidad')
					->where(DB::raw("DATE_FORMAT(fechaEvaluacion, '%Y-%m-%d')"), $fecha)
					->where("clues", $item->clues)->first();
					
					if(!$existe){
						$evaluacion = new EvaluacionCalidad;
						$evaluacion->clues = isset($item->clues) ? $item->clues : $evaluacion->clues;
						$evaluacion->idUsuario = $item->idUsuario;
						$evaluacion->fechaEvaluacion = $item->fechaEvaluacion;
						$evaluacion->cerrado = $item->cerrado;
						$evaluacion->firma = array_key_exists("firma",$item) ? $item->firma : '';
						$evaluacion->responsable = array_key_exists("responsable",$item) ? $item->responsable : '';
						$evaluacion->email = array_key_exists("email",$item) ? $item->email : '';
						
						if ($evaluacion->save()) 
						{
							$success = true;
							// si se guarda la evaluacion correctamente.
							// extrae tosdos los registros (columna-expediente) de la evaluación
							foreach($item->registros as $reg)
							{
								$reg = (object) $reg;
								if(!array_key_exists("idUsuario",$reg))
									$reg->idUsuario=$usuario->id;			
								$registro = EvaluacionCalidadRegistro::where('idEvaluacionCalidad',$evaluacion->id)
																	 ->where('expediente',$reg->expediente)
																	 ->where('idIndicador',$reg->idIndicador)->first();
								if(!$registro)
									$registro = new EvaluacionCalidadRegistro;
								
								$registro->idEvaluacionCalidad = $evaluacion->id;
								$registro->idIndicador = $reg->idIndicador;
								$registro->expediente = $reg->expediente;
								$registro->columna = $reg->columna;
								$registro->cumple = $reg->cumple;
								$registro->promedio = $reg->promedio;
								$registro->totalCriterio = $reg->totalCriterio;
								
								if($registro->save())
								{
									// si se guarda la columna correctamente.
									// extrae tosdos los criterios de la evaluación
									foreach($reg->criterios as $criterio)
									{
										$criterio = (object) $criterio;
										$evaluacionCriterio = EvaluacionCalidadCriterio::where('idEvaluacionCalidad',$evaluacion->id)
																				->where('idCriterio',$criterio->idCriterio)
																				->where('idIndicador',$criterio->idIndicador)
																				->where('idEvaluacionCalidadRegistro',$registro->id)->first();
										
										if(!$evaluacionCriterio)
											$evaluacionCriterio = new EvaluacionCalidadCriterio;
										
										$evaluacionCriterio->idEvaluacionCalidad = $evaluacion->id;
										$evaluacionCriterio->idEvaluacionCalidadRegistro = $registro->id;
										$evaluacionCriterio->idCriterio = $criterio->idCriterio;
										$evaluacionCriterio->idIndicador = $criterio->idIndicador;
										$evaluacionCriterio->aprobado = $criterio->aprobado;
										
										if ($evaluacionCriterio->save()) 
										{								
											$success = true;
										} 
									}
								}
							}
							// recorrer todos los halazgos encontrados por evaluación
							if(isset($item->hallazgos) && (is_object($item->hallazgos)|| is_array($item->hallazgos)))
							foreach($item->hallazgos as $keyhs => $valhs )
							{
								if($valhs != null){
									foreach($valhs as $ks => $hs){										
										if($hs != null){$hs = (object) $hs;
											if(!array_key_exists("idUsuario",$hs))
												$hs->idUsuario=$usuario->id;
											if(!array_key_exists("idPlazoAccion",$hs))
												$hs->idPlazoAccion=null;
											if(!array_key_exists("resuelto",$hs))
												$hs->resuelto=0;
											$usuario = Usuario::where('id', $hs->idUsuario)->first();
											$usuarioPendiente=$usuario->id;
											
											$borrado = DB::table('Hallazgo')					
											->where('idIndicador',$keyhs)
											->where('expediente',$ks)
											->where('idEvaluacion',$evaluacion->id)
											->update(['borradoAL' => NULL]);
											
											$hallazgo = Hallazgo::where('idIndicador', $keyhs)->where('expediente', $ks)->where('idEvaluacion', $evaluacion->id)->first();
							
											if(!$hallazgo)							
												$hallazgo = new Hallazgo;										
																
											$hallazgo->idUsuario = $hs->idUsuario;
											$hallazgo->idAccion = $hs->idAccion;
											$hallazgo->idEvaluacion = $evaluacion->id;
											$hallazgo->idIndicador = $keyhs;
											$hallazgo->expediente = $ks;
											$hallazgo->categoriaEvaluacion = 'CALIDAD';
											$hallazgo->idPlazoAccion = $hs->idPlazoAccion;
											$hallazgo->resuelto = $hs->resuelto;
											$hallazgo->descripcion = $hs->descripcion;
											
											if($hallazgo->save())
											{								
												$hayhallazgo = true;
												$success=true;								
											}
										}
									}
								}							
							}
							if(isset($item->criterio_respuestas))
							foreach($item->criterio_respuestas as $valor){
								if($valor){
									foreach($valor as $valorcriterio){
										if($valorcriterio){
											if(is_array($valorcriterio))
												$valorcriterio = (object) $valorcriterio;
											foreach($valorcriterio as $res){
												if(is_array($res))
													$res = (object) $res;
												if($res){
													$criterio_respuestas = CriterioValidacionRespuesta::where('tipo','CALIDAD')
																									  ->where('idEvaluacion',$evaluacion->id)
																									  ->where('expediente',$res->expediente)
																									  ->where('idCriterio',$res->idCriterio)
																									  ->where('idCriterioValidacion',$res->idCriterioValidacion)
																									  ->first();	
																										
													if(!$criterio_respuestas)							
														$criterio_respuestas = new CriterioValidacionRespuesta;
													
													$criterio_respuestas->idEvaluacion = $evaluacion->id;
													$criterio_respuestas->idCriterio = $res->idCriterio;
													$criterio_respuestas->expediente = $res->expediente;
													$criterio_respuestas->idCriterioValidacion  = $res->idCriterioValidacion;
													$criterio_respuestas->tipo = 'CALIDAD';
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
						$respuesta[] = $evaluacion;
					}
				}				
			}
			// si la evaluación es un json de un solo formulario
			else
			{
				$datos = (object) $datos; 
				if(!array_key_exists("idUsuario",$datos))
					$datos->idUsuario=$usuario->id;
				if(!array_key_exists("fechaEvaluacion",$datos))
					$datos->fechaEvaluacion=$date->format('Y-m-d H:i:s');

				$fecha = $datos->fechaEvaluacion;
				$date2 = new \DateTime($fecha);
				$fecha = $date2->format('Y-m-d');

				$existe_fecha = DB::table('EvaluacionCalidad')
				->where(DB::raw("DATE_FORMAT(fechaEvaluacion, '%Y-%m-%d')"), $fecha)
				->where("clues", $datos->clues)->first();
				
				if(!$existe){

					$evaluacion = new EvaluacionCalidad;
					$evaluacion->clues = $datos->clues;
					$evaluacion->idUsuario = $datos->idUsuario;
					$evaluacion->fechaEvaluacion = $datos->fechaEvaluacion;
					if(array_key_exists("cerrado",$datos))
						$evaluacion->cerrado = $datos->cerrado;
					$evaluacion->firma = array_key_exists("firma",$datos) ? $datos->firma : '';
					$evaluacion->responsable = array_key_exists("responsable",$datos) ? $datos->responsable : '';
					$evaluacion->email = array_key_exists("email",$datos) ? $datos->email : '';
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
				$this->dispatch(new ReporteCalidad($evaluacion));
				if($hayhallazgo){
					$this->dispatch(new ReporteHallazgo($evaluacion));
				}
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
		$evaluacion = DB::table('EvaluacionCalidad AS e')
			->leftJoin('Clues AS c', 'c.clues', '=', 'e.clues')
			->leftJoin('ConeClues AS cc', 'cc.clues', '=', 'e.clues')
			->leftJoin('Cone AS co', 'co.id', '=', 'cc.idCone')
            ->leftJoin('usuarios AS us', 'us.id', '=', 'e.idUsuario')
			->leftJoin('ZonaClues AS zc', 'zc.clues', '=', 'e.clues')
			->leftJoin('Zona AS z', 'z.id', '=', 'zc.idZona')
            ->select(array('z.nombre as zona','us.email','e.firma','e.responsable','e.fechaEvaluacion', 'e.cerrado', 'e.id','e.clues', 'c.nombre', 'c.domicilio', 'c.codigoPostal', 'c.entidad', 'c.municipio', 'c.localidad', 'c.jurisdiccion', 'c.institucion', 'c.tipoUnidad', 'c.estatus', 'c.estado', 'c.tipologia','co.nombre as nivelCone', 'cc.idCone'))
            ->where('e.id',"$id");
			
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
		$date=new \DateTime;
		
        DB::beginTransaction();
        try 
		{
			$hayhallazgo = false;
			$usuario = Usuario::where('email', Request::header('X-Usuario'))->first();
			// valida si el objeto json evaluaciones exista, esto es para los envios masivos de evaluaciones
			if(array_key_exists("evaluaciones",$datos))
			{
				$datos = (object) $datos;
				$respuesta = array();
				foreach($datos->evaluaciones as $item)
				{
					$item = (object) $item;
					if(!array_key_exists("idUsuario",$item))
						$item->idUsuario=$usuario->id;
					$usuario = Usuario::where('id', $item->idUsuario)->first();
					$evaluacion = EvaluacionCalidad::find($item->id);;
					$evaluacion->clues = isset($item->clues) ? $item->clues : $evaluacion->clues;
					$evaluacion->idUsuario = $item->idUsuario;
					$evaluacion->fechaEvaluacion = $item->fechaEvaluacion;
					$evaluacion->cerrado = $item->cerrado;
					$evaluacion->firma = array_key_exists("firma",$item) ? $item->firma : '';
					$evaluacion->responsable = array_key_exists("responsable",$item) ? $item->responsable : '';
					$evaluacion->email = array_key_exists("email",$item) ? $item->email : '';
					
					if ($evaluacion->save()) 
					{
						$success = true;
						// si se guarda la evaluacion correctamente.
						// extrae tosdos los registros (columna-expediente) de la evaluación

						foreach($item->registros as $reg)
						{
							$reg = (object) $reg;
							if(!array_key_exists("idUsuario",$reg))
								$reg->idUsuario=$usuario->id;
							$borrado = DB::table('EvaluacionCalidadRegistro')								
							->where('idEvaluacionCalidad',$evaluacion->id)
							->where('expediente',$reg->expediente)
							->where('idIndicador',$reg->idIndicador)
							->update(['borradoAL' => NULL]);
							
							$registro = EvaluacionCalidadRegistro::where('idEvaluacionCalidad',$evaluacion->id)
																 ->where('expediente',$reg->expediente)
																 ->where('idIndicador',$reg->idIndicador)->first();
							if(!$registro)
								$registro = new EvaluacionCalidadRegistro;
							
							$registro->idEvaluacionCalidad = $evaluacion->id;
							$registro->idIndicador = $reg->idIndicador;
							$registro->expediente = $reg->expediente;
							$registro->columna = $reg->columna;
							$registro->cumple = array_key_exists("cumple",$reg) ? $reg->cumple : '';
							$registro->promedio = array_key_exists("promedio",$reg) ? $reg->promedio : '';
							$registro->totalCriterio = $reg->totalCriterio;
							
							if(count($item->hallazgos)==0)
							{
								$hallazgos = Hallazgo::where('idIndicador',$registro->idIndicador)->where('idEvaluacion',$evaluacion->id)->get();
								foreach($hallazgos as $hz)
								{
									$hallazgo = Hallazgo::find($hz->id);
									$hallazgo->delete();
								}
							}
							if(array_key_exists("cumple",$reg)&&array_key_exists("promedio",$reg))
							if($registro->save())
							{
								// si se guarda la columna correctamente.
								// extrae tosdos los criterios de la evaluación

								foreach($reg->criterios as $criterio)
								{
									$criterio = (object) $criterio;
									$borrado = DB::table('EvaluacionCalidadCriterio')								
									->where('idEvaluacionCalidad',$evaluacion->id)
									->where('idCriterio',$criterio->idCriterio)
									->where('idIndicador',$criterio->idIndicador)
									->where('idEvaluacionCalidadRegistro',$registro->id)
									->update(['borradoAL' => NULL]);
									$evaluacionCriterio = EvaluacionCalidadCriterio::where('idEvaluacionCalidad',$evaluacion->id)
																			->where('idCriterio',$criterio->idCriterio)
																			->where('idIndicador',$criterio->idIndicador)
																			->where('idEvaluacionCalidadRegistro',$registro->id)->first();
									
									if(!$evaluacionCriterio)
										$evaluacionCriterio = new EvaluacionCalidadCriterio;
									
									$evaluacionCriterio->idEvaluacionCalidad = $evaluacion->id;
									$evaluacionCriterio->idEvaluacionCalidadRegistro = $registro->id;
									$evaluacionCriterio->idCriterio = $criterio->idCriterio;
									$evaluacionCriterio->idIndicador = $criterio->idIndicador;
									$evaluacionCriterio->aprobado = $criterio->aprobado;
									
									if ($evaluacionCriterio->save()) 
									{								
										$success = true;
									} 
								}
							}
						}
						
						// recorrer todos los halazgos encontrados por evaluación

						if(isset($item->hallazgos) && (is_object($item->hallazgos)|| is_array($item->hallazgos)))
						foreach($item->hallazgos as $keyhs => $valhs )
						{
							if($valhs != null){
								foreach($valhs as $ks => $hs){										
									if($hs != null){$hs = (object) $hs;
										if(!array_key_exists("idUsuario",$hs))
											$hs->idUsuario=$usuario->id;
										if(!array_key_exists("idPlazoAccion",$hs))
											$hs->idPlazoAccion=null;
										if(!array_key_exists("resuelto",$hs))
											$hs->resuelto=0;
										$usuario = Usuario::where('id', $hs->idUsuario)->first();
										$usuarioPendiente=$usuario->id;
										
										$borrado = DB::table('Hallazgo')					
										->where('idIndicador',$keyhs)
										->where('expediente',$ks)
										->where('idEvaluacion',$evaluacion->id)
										->update(['borradoAL' => NULL]);
										
										$hallazgo = Hallazgo::where('idIndicador', $keyhs)->where('expediente', $ks)->where('idEvaluacion', $evaluacion->id)->first();
						
										if(!$hallazgo)							
											$hallazgo = new Hallazgo;										
															
										$hallazgo->idUsuario = $hs->idUsuario;
										$hallazgo->idAccion = $hs->idAccion;
										$hallazgo->idEvaluacion = $evaluacion->id;
										$hallazgo->idIndicador = $keyhs;
										$hallazgo->expediente = $ks;
										$hallazgo->categoriaEvaluacion = 'CALIDAD';
										$hallazgo->idPlazoAccion = $hs->idPlazoAccion;
										$hallazgo->resuelto = $hs->resuelto;
										$hallazgo->descripcion = $hs->descripcion;
										
										if($hallazgo->save())
										{								
											$hayhallazgo = true;
											$success=true;								
										}
									}
								}
							}							
						}
						if(isset($item->criterio_respuestas))
						foreach($item->criterio_respuestas as $valor){
							if($valor){
								foreach($valor as $valorcriterio){
									if($valorcriterio){
										if(is_array($valorcriterio))
											$valorcriterio = (object) $valorcriterio;
										foreach($valorcriterio as $res){
											if(is_array($res))
												$res = (object) $res;
											if($res){
												$criterio_respuestas = CriterioValidacionRespuesta::where('tipo','CALIDAD')
																								  ->where('idEvaluacion',$evaluacion->id)
																								  ->where('expediente',$res->expediente)
																								  ->where('idCriterio',$res->idCriterio)
																								  ->where('idCriterioValidacion',$res->idCriterioValidacion)
																								  ->first();	
																									
												if(!$criterio_respuestas)							
													$criterio_respuestas = new CriterioValidacionRespuesta;
												
												$criterio_respuestas->idEvaluacion = $evaluacion->id;
												$criterio_respuestas->idCriterio = $res->idCriterio;
												$criterio_respuestas->expediente = $res->expediente;
												$criterio_respuestas->idCriterioValidacion  = $res->idCriterioValidacion;
												$criterio_respuestas->tipo = 'CALIDAD';
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
					$respuesta[] = $evaluacion;					
				}				
			}
			// si la evaluación es un json de un solo formulario
			else
			{
				$datos = (object) $datos; 
				if(!array_key_exists("idUsuario",$datos))
					$datos->idUsuario=$usuario->id;
				if(!array_key_exists("fechaEvaluacion",$datos))
					$datos->fechaEvaluacion=$date->format('Y-m-d H:i:s');
				$evaluacion = EvaluacionCalidad::find($datos->id);;
				$evaluacion->clues = $datos->clues;
				$evaluacion->idUsuario = $datos->idUsuario;
				$evaluacion->fechaEvaluacion = $datos->fechaEvaluacion;
				if(array_key_exists("cerrado",$datos))
					$evaluacion->cerrado = $datos->cerrado;
				$evaluacion->firma = array_key_exists("firma",$datos) ? $datos->firma : '';
				$evaluacion->responsable = array_key_exists("responsable",$datos) ? $datos->responsable : '';
				$evaluacion->email = array_key_exists("email",$datos) ? $datos->email : '';
				
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
				$this->dispatch(new ReporteCalidad($evaluacion));
				if($hayhallazgo){
					$this->dispatch(new ReporteHallazgo($evaluacion));
				}
				/*$spr = DB::select('call sp_calidad()');	
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
			$evaluacion = EvaluacionCalidad::where("id",$id)->where("cerrado",null)->orWhere("cerrado",0)->first();
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
	 * Guarde un hallazgo de la evaluación calidad. para generar un hallazgo el promedio de la suma de los criterios debe ser menos al 80% por indicador
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
			
			if(!$hallazgo)
				$hallazgo = new Hallazgo;				
			
			if($datos->get('aprobado')==0)
			{
				if($datos->get('accion'))
				{
					$hallazgo->idUsuario = $usuario->id;
					$hallazgo->idAccion = $datos->get('accion');
					$hallazgo->idEvaluacion = $idEvaluacion;
					$hallazgo->idIndicador = $datos->get('idIndicador');
					$hallazgo->categoriaEvaluacion = 'CALIDAD';
					$hallazgo->idPlazoAccion = array_key_exists('plazoAccion',$datos) ? $datos->get('plazoAccion') : 0;
					$hallazgo->resuelto = $datos->get('resuelto');
					$hallazgo->descripcion = $datos->get('hallazgo');														
					
					$hallazgo->resuelto = 0;				
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
			$clues = Clues::whereIn('clues',$cones)->whereIn('jurisdiccion',$clues)->get();
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
		try{
			$success = true;
		    $data = []; 
		    $evaluacion = DB::table('EvaluacionCalidad AS e')
		            ->leftJoin('Clues AS c', 'c.clues', '=', 'e.clues')
		            ->leftJoin('ConeClues AS cc', 'cc.clues', '=', 'e.clues')
		            ->leftJoin('Cone AS co', 'co.id', '=', 'cc.idCone')
		            ->leftJoin('usuarios AS us', 'us.id', '=', 'e.idUsuario')
		            ->leftJoin('ZonaClues AS zc', 'zc.clues', '=', 'e.clues')
		            ->leftJoin('Zona AS z', 'z.id', '=', 'zc.idZona')
		            ->select(array('z.nombre as zona','e.email','e.firma','e.responsable','e.fechaEvaluacion', 'e.cerrado', 'e.id','e.clues', 'c.nombre', 'c.domicilio', 'c.codigoPostal', 'c.entidad', 'c.municipio', 'c.localidad', 'c.jurisdiccion', 'c.institucion', 'c.tipoUnidad', 'c.estatus', 'c.estado', 'c.tipologia','co.nombre as nivelCone', 'cc.idCone'))
		            ->where('e.id',"$id")->first();

		    $data["evaluacion"] = $evaluacion;
		    $evaluacion = $id;
		    $sql="select distinct i.color as clr,i.id,i.codigo,i.nombre as indicador,c.nombre as cone,cc.idCone from EvaluacionCalidad e 
		        left join EvaluacionCalidadRegistro er on er.idEvaluacionCalidad = e.id
		        left join Indicador i on i.id = er.idIndicador
		        left join ConeClues cc on cc.clues = e.clues
		        left join Cone c on c.id = cc.idCone
		        where e.id='$evaluacion' and er.borradoAl is null and e.borradoAl is null order by i.codigo";      
		        $indicadores = DB::select($sql);
		        $hallazgo = array();
		        foreach($indicadores as $indicador)
		        {
		            $criteriosx = DB::select("SELECT c.id,c.nombre, l.nombre as lugarVerificacion FROM IndicadorCriterio ic 
		                left join ConeIndicadorCriterio cic on cic.idCone = '$indicador->idCone'
		                left join Criterio c on c.id = ic.idCriterio
		                left join LugarVerificacion l on l.id = ic.idLugarVerificacion
		                where ic.idIndicador = '$indicador->id' and ic.id=cic.idIndicadorCriterio
		                and ic.borradoAl is null and cic.borradoAl is null and c.borradoAl is null and l.borradoAl is null order by c.orden ASC");  
		            $totalx = count($criteriosx);
		            $miscriterios = [];
		            foreach ($criteriosx as $key => $value) {
		                if(!array_key_exists($value->lugarVerificacion, $miscriterios))
		                    $miscriterios[$value->lugarVerificacion] = [];
		                array_push($miscriterios[$value->lugarVerificacion], $value);
		            }
		            $data["criterios"][$indicador->codigo]=$miscriterios;
		            $data["criterios"][$indicador->codigo]["totalCriterios"] = $totalx;
		        
		            $data["indicadores"][$indicador->codigo] = $indicador;
		            
		            $sql="select id, idIndicador, columna, expediente, cumple, promedio, totalCriterio 
		                  from EvaluacionCalidadRegistro 
		                  where idEvaluacionCalidad='$evaluacion' and idIndicador='$indicador->id' and borradoAl is null order By expediente asc";  
		            
		            $resultH = DB::select("SELECT h.idIndicador, h.idAccion, h.idPlazoAccion, h.resuelto, h.descripcion, a.tipo, a.nombre as accion FROM Hallazgo h 
		            left join Accion a on a.id = h.idAccion WHERE h.idEvaluacion = $evaluacion and categoriaEvaluacion='CALIDAD' and idIndicador='$indicador->id' and h.borradoAl is null");
		                
		            if($resultH)
		            {
		                $hallazgo[$indicador->codigo] = $resultH[0];
		            }
		            
		            $registros = DB::select($sql);
		            $bien=0;$suma=0; $columna = 0;
		            $esAprobado = 0; $esNoAprobado = 0;
		            foreach($registros as $registro)
		            {
		                $aprobado=array();
		                $noAplica=array();
		                $noAprobado=array();
		                $sql="select ecc.id, ecc.aprobado, ecc.idCriterio, c.nombre 
		                  from EvaluacionCalidadCriterio  ecc
		                  left join Criterio c on c.id = ecc.idCriterio
		                  where ecc.idEvaluacionCalidadRegistro='$registro->id' 
		                  and ecc.idEvaluacionCalidad='$evaluacion' 
		                  and ecc.idIndicador='$indicador->id' 
		                  and ecc.borradoAl is null and c.borradoAl is null order by c.orden";   
		            
		                $criterios = DB::select($sql);
		                foreach($criterios as $criterio)
		                {
		                    if($criterio->aprobado == '1')
		                    {
		                        array_push($aprobado,$criterio->idCriterio);
		                        $bien++;
		                    }
		                    else if($criterio->aprobado == '2')
		                    {
		                        array_push($noAplica,$criterio->idCriterio);
		                        $bien++;
		                    }
		                    else
		                    {
		                        array_push($noAprobado,$criterio->idCriterio);                              
		                    }   
		                }
		                $data["datos"][$indicador->codigo][$registro->expediente] = $criterios;
		                
		                $data["indicadores"][$indicador->codigo]->columnas[$registro->expediente]["total"]=count($aprobado)+count($noAplica);               
		                $data["indicadores"][$indicador->codigo]->columnas[$registro->expediente]["expediente"]=$registro->expediente;
		                if(count($noAprobado)>0)
		                    $esNoAprobado++;
		                else
		                    $esAprobado++;
		                $data["indicadores"][$indicador->codigo]->aprobado = $esAprobado;
		                $data["indicadores"][$indicador->codigo]->noAprobado = $esNoAprobado;
		                $suma+=count($aprobado)+count($noAplica);
		                
		                $totalPorciento = number_format(((count($aprobado)+count($noAplica))/(count($criteriosx)))*100, 2, '.', '');
		                $color=DB::select("SELECT a.color FROM IndicadorAlerta ia 
		                                       left join Alerta a on a.id=ia.idAlerta
		                                       where ia.idIndicador = $indicador->id  and $totalPorciento between ia.minimo and ia.maximo");
		                                        
		                if($color)
		                    $color=$color[0]->color;
		                else $color="rgb(200,200,200)";
		                $data["indicadores"][$indicador->codigo]->columnas[$registro->expediente]["color"]=$color;
		                $columna++;
		            }
		            $data["indicadores"][$indicador->codigo]->totalCriterio=count($criteriosx)*$columna;
		            $data["indicadores"][$indicador->codigo]->totalColumnas=$columna;
		            $data["indicadores"][$indicador->codigo]->sumaCriterio=$suma;
		            
		            $denominador = ($data["indicadores"][$indicador->codigo]->totalCriterio);
		            if($denominador == 0) $denominador = 1;
		            $totalPorciento = number_format(($suma/$denominador)*100, 2, '.', '');
		            $color=DB::select("SELECT a.color FROM IndicadorAlerta ia 
		                                       left join Alerta a on a.id=ia.idAlerta
		                                       where ia.idIndicador = $indicador->id  and $totalPorciento between ia.minimo and ia.maximo");
		                    
		                if($color)
		                    $color=$color[0]->color;
		                else $color="rgb(200,200,200)";
		            $data["indicadores"][$indicador->codigo]->porciento=$totalPorciento;    
		            $data["indicadores"][$indicador->codigo]->color=$color;
		        }

		    $data["hallazgos"] = $hallazgo;
		    \Mail::send('emails.calidad', $data, function($message) use($data){
		        $message->to($data["evaluacion"]->email, "Evaluacion Calidad")->subject('CIUM');
		    });
		    $envio = EvaluacionCalidad::find($id);
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