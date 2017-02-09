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

use App\Models\Transacciones\EvaluacionRecurso;
use App\Models\Transacciones\EvaluacionRecursoCriterio;
use App\Models\Transacciones\Hallazgo;

use App\Models\Catalogos\CriterioValidacion;
use App\Models\Catalogos\CriterioValidacionPregunta;
use App\Models\Catalogos\CriterioValidacionRespuesta;
/**
* Controlador EvaluacionRecurso criterio (Recurso)
* 
* @package    CIUM API
* @subpackage Controlador
* @author     Eliecer Ramirez Esquinca <ramirez.esquinca@gmail.com>
* @created    2015-07-20
*
* Controlador `Recurso`: Proporciona los servicios para el manejos de los datos de la evaluacion
*
*/
class EvaluacionRecursoCriterioController extends Controller 
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
				$evaluacionCriterio = EvaluacionRecursoCriterio::orderBy($order,$orden);
				
				$search = trim($valor);
				$keyword = $search;
				$evaluacionCriterio=$evaluacionCriterio->whereNested(function($query) use ($keyword)
				{
					
						$query->Where('idEvaluacionRecurso', 'LIKE', '%'.$keyword.'%')
							 ->orWhere('idCriterio', 'LIKE', '%'.$keyword.'%')
							 ->orWhere('idIndicador', 'LIKE', '%'.$keyword.'%'); 
				});
				$total=$evaluacionCriterio->get();
				$evaluacionCriterio = $evaluacionCriterio->skip($pagina-1)->take($datos['limite'])->get();
			}
			else
			{
				$evaluacionCriterio = EvaluacionRecursoCriterio::skip($pagina-1)->take($datos['limite'])->orderBy($order,$orden)->get();
				$total=EvaluacionRecursoCriterio::get();
			}
			
		}
		else
		{
			$evaluacionCriterio = EvaluacionRecursoCriterio::get();
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
			$evaluacionCriterio = EvaluacionRecursoCriterio::where('idEvaluacionRecurso',$datos->get('idEvaluacionRecurso'))->where('idCriterio',$datos->get('idCriterio'))->first();
				
			if(!$evaluacionCriterio)
				$evaluacionCriterio = new EvaluacionRecursoCriterio;
			
            $evaluacionCriterio->idEvaluacionRecurso= $datos->get('idEvaluacionRecurso');
			$evaluacionCriterio->idCriterio = $datos->get('idCriterio');
			$evaluacionCriterio->idIndicador = $datos->get('idIndicador');
			$evaluacionCriterio->aprobado = $datos->get('aprobado');
			
            if ($evaluacionCriterio->save()) 
			{				
				$success = true;
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
		$indicatores = DB::select("select i.id,i.color,i.codigo,i.nombre from EvaluacionRecursoCriterio erc
						left join Indicador as i on i.id= erc.idIndicador 
						where erc.idEvaluacionRecurso = $evaluacion and i.borradoAl is null and erc.borradoAl is null");
						
		$evaluacionC = DB::table('EvaluacionRecurso AS e')
			->leftJoin('Clues AS c', 'c.clues', '=', 'e.clues')
			->leftJoin('ConeClues AS cc', 'cc.clues', '=', 'e.clues')
			->leftJoin('Cone AS co', 'co.id', '=', 'cc.idCone')
            ->select(array('e.fechaEvaluacion', 'e.cerrado', 'e.id','e.clues', 'c.nombre', 'c.domicilio', 'c.codigoPostal', 'c.entidad', 'c.municipio', 'c.localidad', 'c.jurisdiccion', 'c.institucion', 'c.tipoUnidad', 'c.estatus', 'c.estado', 'c.tipologia','co.nombre as nivelCone', 'cc.idCone'))
            ->where('e.id',"$evaluacion")
			->where('e.borradoAl',null)
			->first();
			
		$cone = $evaluacionC->idCone;
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
				$aprobado = DB::select("SELECT aprobado from EvaluacionRecursoCriterio where idEvaluacionRecurso = $evaluacion
																and idIndicador = $indicator->id
																and idCriterio = $valor->idCriterio and borradoAl is null");
				if($aprobado)
					$valor->aprobado = $aprobado[0]->aprobado;
				else
					$valor->aprobado = 2;
				array_push($criterios,$valor);
			}
			
			$criterios["indicador"] = $indicator;
			$hallazgo = DB::select("SELECT h.idIndicador, h.idAccion, h.idPlazoAccion, h.resuelto, h.descripcion, a.tipo, a.nombre as accion FROM Hallazgo h	
			left join Accion a on a.id = h.idAccion WHERE h.idEvaluacion= $evaluacion and categoriaEvaluacion='RECURSO' and idIndicador = $indicator->id and h.borradoAl is null");
			if($hallazgo)
				$criterios["hallazgo"] = $hallazgo[0];
			
			$indicadores[$indicator->codigo] = $criterios;
		}
		//fin indicador	
		$estadistica = array();
		foreach($indicatores as $item)
		{
			if(!array_key_exists($item->codigo,$estadistica))
			{
				$id = $item->id;
				
				$total = DB::select("SELECT c.id,c.nombre  FROM ConeIndicadorCriterio cic							
						left join IndicadorCriterio ic on ic.id = cic.idIndicadorCriterio
						left join Criterio c on c.id = ic.idCriterio
						left join Indicador i on i.id = ic.idIndicador
						left join LugarVerificacion lv on lv.id = ic.idlugarVerificacion		
						WHERE cic.idCone = $cone and ic.idIndicador = '$id' 
						and c.borradoAl is null and ic.borradoAl is null and cic.borradoAl is null and lv.borradoAl is null order by i.codigo");
						
				$in=[];
				foreach($total as $c)
				{
					$in[]=$c->id;
				}
				
				$aprobado = DB::table('EvaluacionRecursoCriterio')->select('idCriterio')
							->whereIN('idCriterio',$in)
							->where('idEvaluacionRecurso',$evaluacion)
							->where('idIndicador',$id)
							->where('borradoAl',null)->where('aprobado',1)->get();				
				$na = DB::table('EvaluacionRecursoCriterio')
							->select('idCriterio')
							->whereIN('idCriterio',$in)
							->where('idEvaluacionRecurso',$evaluacion)
							->where('aprobado',2)
							->where('borradoAl',null)->get();				
				
				$totalPorciento = number_format((count($aprobado)/(count($total)-count($na)))*100, 2, '.', '');
				
				$item->indicadores["totalCriterios"] = count($total);
				$item->indicadores["totalAprobados"] = count($aprobado);
				$item->indicadores["totalNoAplica"] = count($na);
				$item->indicadores["totalPorciento"] = $totalPorciento;
				$micolor=DB::select("SELECT a.color FROM IndicadorAlerta ia 
									   left join Alerta a on a.id=ia.idAlerta
									   where ia.idIndicador = $id  and $totalPorciento between ia.minimo and ia.maximo");
				if($micolor)
					$micolor=$micolor[0]->color;
				else
					$micolor="rgb(200,200,200)";
				$item->indicadores["totalColor"] = $micolor;
				
				$estadistica[$item->codigo] = $item;				
			}				
		}
		
		
		if(!$indicadores)
		{
			return Response::json(array('status'=> 200,"messages"=>'ok', "data"=> []),200);
		} 
		else 
		{
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito", "data"=>$indicadores, "estadistica"=> $estadistica),200);			
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
			$cerrado = EvaluacionRecurso::where("id",$id)->where("cerrado",null)->orWhere("cerrado",0)->first();			
			if($cerrado)
			{
				$evaluacion = EvaluacionRecursoCriterio::where("idEvaluacionRecurso",$id)->where("idIndicador",$datos["idIndicador"])->get();
				foreach($evaluacion as $item)
				{
					$criterio = EvaluacionRecursoCriterio::find($item->id);
					$criterio->delete();
				}
				$hallazgo = Hallazgo::where("idEvaluacion",$id)->where("categoriaEvaluacion","RECURSO")->where("idIndicador",$datos["idIndicador"])->get();
				foreach($hallazgo as $item)
				{
					$ha = Hallazgo::find($item->id);
					$ha->delete();
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
		
		
		$criterio = DB::select("SELECT c.id as idCriterio, c.habilitarNoAplica, c.tieneValidacion, ic.idIndicador, cic.idCone, lv.id as idlugarVerificacion, c.creadoAl, c.modificadoAl, c.nombre as criterio, lv.nombre as lugarVerificacion FROM ConeIndicadorCriterio cic							
		left join IndicadorCriterio ic on ic.id = cic.idIndicadorCriterio
		left join Criterio c on c.id = ic.idCriterio
		left join LugarVerificacion lv on lv.id = ic.idlugarVerificacion		
		WHERE cic.idCone = $cone and ic.idIndicador = $indicador and c.borradoAl is null and ic.borradoAl is null and cic.borradoAl is null and lv.borradoAl is null order by c.orden ASC");	
			
		$evaluacionCriterio = EvaluacionRecursoCriterio::where('idEvaluacionRecurso',$evaluacion)->where('idIndicador',$indicador)->get();
		$aprobado=array();
		
		$hallazgo=array();
		foreach($evaluacionCriterio as $valor)
		{
			$aprobado[$valor->idCriterio] = $valor->aprobado;			
		}		
		
		if(!$criterio)
		{
			return Response::json(array('status'=> 404,"messages"=>'No encontrado'),200);
		} 
		else 
		{
			$result = DB::select("SELECT h.idIndicador, h.idAccion, h.idPlazoAccion, h.resuelto, h.descripcion, a.tipo FROM Hallazgo h	
			left join Accion a on a.id = h.idAccion WHERE h.idEvaluacion= $evaluacion and categoriaEvaluacion='RECURSO' and idIndicador='$indicador' and h.borradoAl is null");
				
			if($result)
			{
				$hallazgo = $result[0];
			}
			else $hallazgo=0;
			foreach($criterio as $item){
				$item->criterio_validaciones = CriterioValidacion::where("idCriterio",$item->idCriterio)->get();
				$item->criterio_preguntas = CriterioValidacionPregunta::where("idCriterio",$item->idCriterio)->get();
				$item->criterio_respuestas = CriterioValidacionRespuesta::where("idCriterio",$item->idCriterio)
											 ->where('idEvaluacion',$evaluacion)->where('tipo','RECURSO')->get();
			}
			
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$criterio,"total"=>count($criterio), "aprobado" => $aprobado, "hallazgo" => $hallazgo),200);
			
		}
	}
	
	/**
	 * Muestra una lista de los recurso.
	 *
	 * @param string $cone nivel de cone de la evaluación
	 * @param int $indicador identificador del indicador a mostra sus criterios
	 * @param int $evaluacion identificador de la evaluación
	 *					
	 * @return Response
	 * <code style="color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado), "total": count(resultado), "criterios": $criterio,"totalCriterio"=>count($criterio),"hallazgo": $hallazgo, "tiene"=>$tiene),status) </code>
	 * <code> Respuesta Error json(array("status": 404, "messages": "No hay resultados"),status) </code>
	 */
	public function Estadistica($evaluacion)
	{
		try{
			$clues = DB::select("SELECT clues FROM EvaluacionRecurso WHERE id=$evaluacion")[0]->clues;		
			$evaluacionCriterio = EvaluacionRecursoCriterio::with('Evaluaciones')->where('idEvaluacionRecurso',$evaluacion)->get(array('idCriterio','aprobado','id','idIndicador'));
			
			$indicador = [];
			$existe=false;
			foreach($evaluacionCriterio as $item)
			{
				$sql = "SELECT distinct i.id, i.codigo, i.nombre, 
				(SELECT count(id) FROM ConeIndicadorCriterio where borradoAl is null and idIndicadorCriterio in(select id from IndicadorCriterio where idIndicador=ci.idIndicador and borradoAl is null and idCriterio in (SELECT id FROM Criterio where borradoAl is null)) and idCone=cc.idCone) as total, 
				(SELECT count(id) FROM EvaluacionRecursoCriterio where idEvaluacionRecurso = '$evaluacion' and idIndicador = '$item->idIndicador') as evaluado
				FROM ConeClues cc 
				left join ConeIndicadorCriterio cic on cic.idCone = cc.idCone
				left join IndicadorCriterio ci on ci.id = cic.idIndicadorCriterio 
				left join Indicador i on i.id = ci.idIndicador
				where cc.clues = '$clues' and ci.idCriterio = $item->idCriterio and ci.idIndicador = $item->idIndicador and i.id is not null 
				and i.borradoAl is null and ci.borradoAl is null and cic.borradoAl is null ";
				
				$result = DB::select($sql);
				
				if($result)
				{
					$result = (array)$result[0];
					$existe = false; $contador=0;
					
					for($i=0;$i<count($indicador);$i++)
					{
						if(!isset($indicador[$result["codigo"]]))
							$indicador[$result["codigo"]] = [];
						if(array_key_exists($result["codigo"],$indicador[$result["codigo"]]))
						{						
							$indicador[$result["codigo"]][$result["codigo"]] = $result["total"];	
							$indicador[$result["codigo"]]["evaluado"] = $result["evaluado"];					
							$existe = true;
						}
						
					}

					if(!$existe)
					{
						$contador=1;
						$result = Indicador::with("IndicadorAlertas", "IndicadorValidaciones", "IndicadorPreguntas")->find($result["id"])->toArray();
						
						$result[$result["codigo"]] = $contador;
						$indicador[$result["codigo"]] = $result;
					}
				}
				
			}
			
			if(!$indicador)
			{
				return Response::json(array('status'=> 200,"messages"=>'ok', "data"=> []),200);
			} 
			else 
			{
				return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$indicador),200);			
			}
		}catch (\Exception $e){
			throw $e;
        }
	}

	public function CriterioEvaluacionImprimir($cone,$indicador)
	{		
		$datos = Request::all();
		
		
		$criterio = DB::select("SELECT c.id as idCriterio, c.habilitarNoAplica,ic.idIndicador, cic.idCone, lv.id as idlugarVerificacion, c.creadoAl, c.modificadoAl, c.nombre as criterio, lv.nombre as lugarVerificacion FROM ConeIndicadorCriterio cic							
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
}
?>