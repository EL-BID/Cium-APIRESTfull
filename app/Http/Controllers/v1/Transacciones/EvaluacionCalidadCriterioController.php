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
use App\Models\Catalogos\Indicador;
use App\Models\Catalogos\IndicadorAlerta;
use App\Models\Catalogos\IndicadorCriterio;
use App\Models\Catalogos\ConeIndicadorCriterio;
use App\Models\Catalogos\LugarVerificacion;

use App\Models\Transacciones\Hallazgo;
use App\Models\Transacciones\EvaluacionCalidad;
use App\Models\Transacciones\EvaluacionCalidadCriterio;
use App\Models\Transacciones\EvaluacionCalidadRegistro;

use App\Models\Catalogos\CriterioValidacion;
use App\Models\Catalogos\CriterioValidacionPregunta;
use App\Models\Catalogos\CriterioValidacionRespuesta;
/**
* Controlador Evaluacion criterio (calidad)
* 
* @package    CIUM API
* @subpackage Controlador
* @author     Eliecer Ramirez Esquinca <ramirez.esquinca@gmail.com>
* @created    2015-07-20
*
* Controlador `Criterios Calidad`: Maneja los datos para los criterios de las evaluaciones
*
*/
class EvaluacionCalidadCriterioController extends Controller 
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
				$order="id"; $orden="asc";
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
				$evaluacionCriterio = EvaluacionCalidadCriterio::orderBy($order,$orden);
				
				$search = trim($valor);
				$keyword = $search;
				$evaluacionCriterio=$evaluacionCriterio->whereNested(function($query) use ($keyword)
				{
					
						$query->Where('idEvaluacionCalidad', 'LIKE', '%'.$keyword.'%')
							 ->orWhere('idCriterio', 'LIKE', '%'.$keyword.'%')
							 ->orWhere('idEvaluacion', 'LIKE', '%'.$keyword.'%')
							 ->orWhere('idEvaluacionCalidadRegistro', 'LIKE', '%'.$keyword.'%'); 
				});
				
				$total=$evaluacionCriterio->get();
				$evaluacionCriterio = $evaluacionCriterio->skip($pagina-1)->take($datos['limite'])->get();
			}
			else
			{
				$evaluacionCriterio = EvaluacionCalidadCriterio::skip($pagina-1)->take($datos['limite'])->orderBy($order,$orden)->get();
				$total=EvaluacionCalidadCriterio::get();
			}
			
		}
		else
		{
			$evaluacionCriterio = EvaluacionCalidadCriterio::get();
			$total=$evaluacionCriterio;
		}

		if(!$evaluacionCriterio)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),404);
		} 
		else 
		{
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$evaluacionCriterio,"total"=>count($total)),200);
			
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
		$datos = Input::json(); 
		$success = false;
		$date=new \DateTime;
		
        DB::beginTransaction();
        try 
		{
			$usuario = Usuario::where('email', Request::header('X-Usuario'))->first();	
			// valida que el expediente no exista para hacer un insert, en caso contrario hacer un update
			$registro = EvaluacionCalidadRegistro::where('idEvaluacionCalidad',$datos->get('idEvaluacionCalidad'))
												 ->where('expediente',$datos->get('expediente'))
												 ->where('idIndicador',$datos->get('idIndicador'))->first();
			if(!$registro)
				$registro = new EvaluacionCalidadRegistro;
			
			$registro->idEvaluacionCalidad = $datos->get('idEvaluacionCalidad');
			$registro->idIndicador = $datos->get('idIndicador');
			$registro->expediente = $datos->get('expediente');
			$registro->columna = $datos->get('columna');
			$registro->cumple = $datos->get('cumple');
			$registro->promedio = $datos->get('promedio');
			$registro->totalCriterio = $datos->get('totalCriterio');
			
			if($registro->save())
			{
				// valida que el criterio no exista para hacer un insert, en caso contrario hacer un update
				$evaluacionCriterio = EvaluacionCalidadCriterio::where('idEvaluacionCalidadRegistro',$registro->id)->where('idEvaluacionCalidad',$datos->get('idEvaluacionCalidad'))->where('idCriterio',$datos->get('idCriterio'))->first();
					
				if(!$evaluacionCriterio)
					$evaluacionCriterio = new EvaluacionCalidadCriterio;
				
				$evaluacionCriterio->idEvaluacionCalidad = $datos->get('idEvaluacionCalidad');
				$evaluacionCriterio->idEvaluacionCalidadRegistro = $registro->id;
				$evaluacionCriterio->idCriterio = $datos->get('idCriterio');
				$evaluacionCriterio->idIndicador = $datos->get('idIndicador');
				$evaluacionCriterio->aprobado = $datos->get('aprobado');
				
				if ($evaluacionCriterio->save()) 
				{				
					$success = true;
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
			return Response::json(array("status"=>201,"messages"=>"Creado","data"=>$evaluacionCriterio),201);
        } 
		else 
		{
            DB::rollback();
			return Response::json(array("status"=>500,"messages"=>"Error interno del servidor"),500);
        }
		
	}

	/**
	 * Visualizar el recurso especificado.
	 *
	 * @param  int  $evaluacion que corresponde al recurso a mostrar el detalle
	 * Response si el recurso es encontrado devolver el registro y estado 200, si no devolver error con estado 404
	 * @return Response
	 */
	public function show($evaluacion)
	{
		$data=[];
			
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
			$data["criterios"][$indicador->codigo]=$criteriosx;
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
				  and ecc.borradoAl is null and c.borradoAl is null";	
			
				$criterios__ = DB::select($sql);
				$criterios = [];
				foreach($criterios__ as $criterio)
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
					$criterios[$criterio->idCriterio] = $criterio;	
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
		
		if(!$data)
		{
			return Response::json(array('status'=> 200, "messages"=> 'ok', "data"=> $data),200);
		} 
		else 
		{
			return Response::json(array("status"=> 200, "messages"=>"Operación realizada con exito", "data"=> $data, "total"=> count($indicadores),"hallazgos"=>$hallazgo),200);			
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
		$datos = Request::all(); 
		$success = false;
        DB::beginTransaction();
        try 
		{
			$cerrado = $evaluacion = EvaluacionCalidad::where("id",$id)->where("cerrado",null)->orWhere("cerrado",0)->first();
			if($cerrado)
			{
				if(isset($datos["expediente"]))
				{
					$evaluacion = EvaluacionCalidadRegistro::where("idEvaluacionCalidad",$id)->where("idIndicador",$datos["idIndicador"])->where("expediente",$datos["expediente"])->get();
					
					foreach($evaluacion as $item)
					{
						$criterios = EvaluacionCalidadCriterio::where("idEvaluacionCalidad",$id)->where("idIndicador",$datos["idIndicador"])->where("idEvaluacionCalidadRegistro",$item->id)->get();
						foreach($criterios as $i)
						{
							$criterio = EvaluacionCalidadCriterio::find($i->id);
							$criterio->delete();
						}
						$registro = EvaluacionCalidadRegistro::find($item->id);
						$registro->delete();
					}
				}
				else
				{
					$evaluacion = EvaluacionCalidadCriterio::where("idEvaluacionCalidad",$id)->where("idIndicador",$datos["idIndicador"])->get();
					$registroEv = EvaluacionCalidadRegistro::where("idEvaluacionCalidad",$id)->where("idIndicador",$datos["idIndicador"])->get();
					foreach($evaluacion as $item)
					{
						$criterio = EvaluacionCalidadCriterio::find($item->id);
						$criterio->delete();
					}
					foreach($registroEv as $item)
					{
						$registro = EvaluacionCalidadRegistro::find($item->id);
						$registro->delete();
					}
					$hallazgo = Hallazgo::where("idEvaluacion",$id)->where("categoriaEvaluacion","CALIDAD")->where("idIndicador",$datos["idIndicador"])->get();
					foreach($hallazgo as $item)
					{
						$ha = Hallazgo::find($item->id);
						$ha->delete();
					}
				}
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
	 * Devuelve la lista de criterios segun corresponda para el nivel de cone e indicador
	 *
	 * @param string $cone nivel de cone de la evaluación
	 * @param int $indicador identificador del indicador a mostra sus criterios
	 * @param int $evaluacion identificador de la evaluación
	 *					
	 * @return Response
	 * <code style="color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado), "total": count(resultado), "criterios": $criterio,"totalCriterio"=>count($criterio),"hallazgo": $hallazgo, "tiene"=>$tiene),status) </code>
	 * <code> Respuesta Error json(array("status": 404, "messages": "No hay resultados"),status) </code>
	 */
	public function CriterioEvaluacion($cone,$indicador,$evaluacion)
	{		
		$datos = Request::all();
		$data = array();
		$criterios = array();
		$criterio = DB::select("SELECT c.id as idCriterio,c.habilitarNoAplica, c.tieneValidacion, ic.idIndicador, cic.idCone, lv.id as idlugarVerificacion, c.creadoAl, c.modificadoAl, c.nombre as criterio, lv.nombre as lugarVerificacion FROM ConeIndicadorCriterio cic							
		left join IndicadorCriterio ic on ic.id = cic.idIndicadorCriterio
		left join Criterio c on c.id = ic.idCriterio
		left join LugarVerificacion lv on lv.id = ic.idlugarVerificacion		
		WHERE cic.idCone = $cone and ic.idIndicador = $indicador
		and c.borradoAl is null and ic.borradoAl is null and cic.borradoAl is null and lv.borradoAl is null order by c.orden ASC");
		$totalCriterio = count($criterio);
		$CalidadRegistro = EvaluacionCalidadRegistro::where('idEvaluacionCalidad',$evaluacion)->where('idIndicador',$indicador)->get();	
		$tiene=0; 
		
		if($criterio)
		{
			
			$hallazgo=array();
			foreach($CalidadRegistro as $registro)
			{
				$evaluacionCriterio = EvaluacionCalidadCriterio::where('idEvaluacionCalidad',$evaluacion)
									->where('idIndicador',$indicador)
									->where('idEvaluacionCalidadRegistro',$registro->id)->get();
				
				$aprobado=array();
				
				foreach($evaluacionCriterio as $valor)
				{
					if($valor->aprobado == '1')
					{
						$aprobado[$valor->idCriterio]=1;
					}
					else if($valor->aprobado == '2')
					{
						$aprobado[$valor->idCriterio]=2;
					}
					else
					{	
						$aprobado[$valor->idCriterio]=0;			
					}
				}				
				$registro["aprobado"] = $aprobado;	
				if($CalidadRegistro->toArray())						
					$data[$registro->expediente]=$registro;
				$tiene=1;
			}
		}
		
		if(!$criterio)
		{
			return Response::json(array('status'=> 404,"messages"=>'No se encontro criterios'),200);
		} 
		else 
		{
			$result = DB::select("SELECT h.idIndicador, h.expediente, h.idAccion, h.idPlazoAccion, h.resuelto, h.descripcion, a.tipo 
			FROM Hallazgo h	
			left join Accion a on a.id = h.idAccion WHERE h.idEvaluacion = $evaluacion and categoriaEvaluacion='CALIDAD' and h.borradoAl is null");
				
			if($result)
			{
				foreach($result as $r)
				{
					$hallazgo[$r->idIndicador][$r->expediente] = $r;
				}
			}
			else $hallazgo=0;
			
			foreach($criterio as $item){
				$item->criterio_validaciones = CriterioValidacion::where("idCriterio",$item->idCriterio)->get();
				$item->criterio_preguntas = CriterioValidacionPregunta::where("idCriterio",$item->idCriterio)->get();
				$item->criterio_respuestas = CriterioValidacionRespuesta::where("idCriterio",$item->idCriterio)
											 ->where('idEvaluacion',$evaluacion)->where('tipo','CALIDAD')->get();
			}
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$data,"criterios"=>$criterio,"total"=>count($data),"totalCriterio"=>count($criterio),"hallazgos" => $hallazgo,"tiene"=>$tiene),200);
			
		}
	}	
	
	
	/**
	 * Devuelve la estadistica por indicador de la evaluación
	 *
	 * @param int $evaluacion identificador de la evaluacion
	 * @return Response
	 * <code style="color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 200, "messages": "ok"),status) </code>
	 */
	public function Estadistica($evaluacion)
	{		
		$clues = DB::select("SELECT clues FROM EvaluacionCalidad WHERE id=$evaluacion")[0]->clues;
		
		$CalidadRegistro = EvaluacionCalidadRegistro::where('idEvaluacionCalidad',$evaluacion)->get();
		$columna=[]; $col=0;
		
		foreach($CalidadRegistro as $registro)
		{
			$evaluacionCriterio = EvaluacionCalidadCriterio::where('idEvaluacionCalidadRegistro',$registro->id)
								->where('idEvaluacionCalidad',$evaluacion)
								->get(array('idCriterio','aprobado','id','idIndicador'));			
			$indicadores = [];
			
			foreach($evaluacionCriterio as $item)
			{
				$sql = "SELECT distinct i.id, i.codigo, i.nombre, i.indicacion, 
				(SELECT count(id) FROM ConeIndicadorCriterio where borradoAl is null and idIndicadorCriterio in(select id from IndicadorCriterio where borradoAl is null and idIndicador=ci.idIndicador) and idCone=cc.idCone) as total 
				FROM ConeClues cc 
				left join ConeIndicadorCriterio cic on cic.idCone = cc.idCone
				left join IndicadorCriterio ci on ci.id = cic.idIndicadorCriterio 
				left join Indicador i on i.id = ci.idIndicador
				where cc.clues = '$clues' and ci.idCriterio = $item->idCriterio and ci.idIndicador = $registro->idIndicador and i.id is not null
				and ci.borradoAl is null and cic.borradoAl is null and i.borradoAl is null";
				
				$result = DB::select($sql);
				
				if($result)
				{
					$result = (array)$result[0];
					$existe = false; $contador=0;
					for($i=0;$i<count($indicadores);$i++)
					{
						if(array_key_exists($result["codigo"],$indicadores[$i]))
						{						
							$indicadores[$i][$result["codigo"]]=$indicadores[$i][$result["codigo"]]+1;						
							$existe = true;
						}
					}
					if(!$existe)
					{
						$contador=1;
						
						$result[$result["codigo"]] = $contador;
						array_push($indicadores,$result);
					}
				}
				
			}
			if(!array_key_exists($registro->expediente,$columna))
				$columna[$registro->expediente]=array();
			if($indicadores)
				array_push($columna[$registro->expediente], $indicadores[0]);			
		}
		if(!$columna)
		{
			return Response::json(array('status'=> 200,"messages"=>'ok', "data"=> []),200);
		} 
		else 
		{
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$columna),200);			
		}
	}

	public function CriterioEvaluacionImprimir($cone,$indicador)
	{		
		$datos = Request::all();
		
		
		$criterio = DB::select("SELECT c.id as idCriterio,c.habilitarNoAplica, ic.idIndicador, cic.idCone, lv.id as idlugarVerificacion, c.creadoAl, c.modificadoAl, c.nombre as criterio, lv.nombre as lugarVerificacion FROM ConeIndicadorCriterio cic							
		left join IndicadorCriterio ic on ic.id = cic.idIndicadorCriterio
		left join Criterio c on c.id = ic.idCriterio
		left join LugarVerificacion lv on lv.id = ic.idlugarVerificacion		
		WHERE cic.idCone = $cone and ic.idIndicador = $indicador and c.borradoAl is null and ic.borradoAl is null and cic.borradoAl is null and lv.borradoAl is null order by c.orden ASC");	
		
		$criterio["indicador"] = DB::select("SELECT * FROM Indicador where id = '$indicador'")[0];
		if(!$criterio)
		{
			return Response::json(array('status'=> 404,"messages"=>'No encontrado'),200);
		} 
		else 
		{
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$criterio,"total"=>count($criterio)),200);			
		}	
	}

	public function CriterioEvaluacionCalidadIndicador($id)
	{		
		$datos = Request::all();
		$criterio = DB::select("SELECT * FROM EvaluacionCalidadRegistro WHERE idEvaluacionCalidad = $id and borradoAl is null");	
		
		if(!$criterio)
		{
			return Response::json(array('status'=> 404,"messages"=>'No encontrado'),200);
		} 
		else 
		{
			foreach ($criterio as $value) {
				$indicado = $value->idIndicador;
				$registro  = $value->id;
				$temp = Indicador::with("IndicadorAlertas", "IndicadorValidaciones", "IndicadorPreguntas")->find($indicado);
				
				$indicador[$temp->codigo] = $temp;
				$indicador[$temp->codigo]->totalCriterio = $value->totalCriterio;
				$total = DB::select("SELECT count(idCriterio) as total FROM EvaluacionCalidadCriterio where idIndicador = '$indicado' and idEvaluacionCalidadRegistro = '$registro'")[0]->total;
				$indicador[$temp->codigo]->completo = true;
				if($total != $value->totalCriterio)
					$indicador[$temp->codigo]->completo = false;
				$indicador[$temp->codigo]->completo = !$indicador[$temp->codigo]->completo ? false : true;
			}
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$indicador),200);			
		}	
	}
}
?>