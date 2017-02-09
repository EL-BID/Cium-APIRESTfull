<?php
namespace App\Http\Controllers\v1\Transacciones;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Request;
use Response;
use Input;
use DB;


use App\Models\Sistema\Usuario;

use App\Models\Catalogos\Clues;
use App\Models\Catalogos\ConeClues;
/**
* Controlador Hallazgo
* 
* @package    CIUM API
* @subpackage Controlador
* @author     Eliecer Ramirez Esquinca <ramirez.esquinca@gmail.com>
* @created    2015-07-20
*
* Controlador `Hallazgo`: Maneja los datos para mostrar en modulo hallazgo
*
*/
class HallazgoController extends Controller {
	/**
	 * Inicia el contructor para los permisos de visualizacion
	 *	 
	 */
    public function __construct()
    {
        $this->middleware('permisos:GET.568616C6CF|POST.677A616C6C|PUT.677A616C6C|DELETE.677A616C6C');
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
	 * <Ul>Filtro avanzado
	 * <Li> <code>$filtro</code> json con los datos del filtro avanzado</ li>
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
		$filtro = array_key_exists("filtro",$datos) ? json_decode($datos["filtro"]) : null; 		
		$cluesUsuario=$this->permisoZona();
		
		$parametro = $this->getTiempo($filtro);
		$valor = $this->getParametro($filtro);
		$parametro .= $valor[0];
		$nivel = $valor[1];
				
		
		$historial = "";
		if(!$filtro->historial)					
			$historial= " and fechaEvaluacion = (select max(fechaEvaluacion) from ReporteHallazgos where codigo = h.codigo and clues = h.clues)";
				
		$indicadores = array();
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
			$pagina = $pagina-1;
			$limite = $datos['limite'];
			if(array_key_exists('buscar',$datos))
			{
				$columna = $datos['columna'];
				$valor   = $datos['valor'];
				
				$search = trim($valor);
				$keyword = $search;
				$sql = "select distinct clues,nombre,jurisdiccion, municipio, cone from ReporteHallazgos h where clues in ($cluesUsuario) $parametro";
								
				$hallazgo = DB::select($sql.$historial." and 
				(jurisdiccion LIKE '%$keyword%' or municipio LIKE '%$keyword%' or nombre LIKE '%$keyword%' or clues LIKE '%$keyword%' or cone LIKE '%$keyword%') 
				order by $order $orden limit $pagina,$limite");			
								
				$total=count(DB::select($sql.$historial." and 
				(jurisdiccion LIKE '%$keyword%' or municipio LIKE '%$keyword%' or nombre LIKE '%$keyword%' or clues LIKE '%$keyword%' or cone LIKE '%$keyword%') 
				order by $order $orden"));
			}
			else
			{
				$hallazgo = DB::select("select distinct clues,nombre,jurisdiccion, municipio, cone from ReporteHallazgos h where clues in ($cluesUsuario) $parametro $historial  
				order by $order $orden limit $pagina,$limite");
				
				$total = count(DB::select("select distinct clues,nombre,jurisdiccion, municipio, cone from ReporteHallazgos h where clues in ($cluesUsuario) $parametro $historial "));
				
				$indicadores = DB::select("select distinct color,codigo,indicador,categoria from ReporteHallazgos h where clues in ($cluesUsuario) $historial order by codigo");
			}			
		}
		else
		{
			$indicadores = DB::select("select distinct color,codigo,indicador,categoria from ReporteHallazgos h where clues in ($cluesUsuario) $historial order by codigo");
			$hallazgo = DB::select("select distinct clues,nombre,jurisdiccion, municipio, cone from ReporteHallazgos h where clues in ($cluesUsuario) $parametro $historial order by $order $orden");			
			$total=count($hallazgo);
		}
		
		if(!$hallazgo)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),404);
		} 
		else 
		{
			$tempIndicador=array();
			$totalIndicador = count($indicadores);
			foreach($indicadores as $item)
			{
				$noexiste=true;
				$code = $item->codigo;
				$totals=DB::select("SELECT count(distinct clues) as total FROM ReporteHallazgos h WHERE codigo = '$code' $historial");
				if($totals)
				{					
					$item->total = $totals[0]->total;
					array_push($tempIndicador,$item);					
				}
			}
			if(count($tempIndicador)>0)
				$indicadores=$tempIndicador;
			
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$hallazgo, "indicadores"=> $indicadores,"totalIndicador"=>$totalIndicador, "total"=>$total),200);			
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
		$filtro = array_key_exists("filtro",$datos) ? json_decode($datos["filtro"]) : null; 		
		$cluesUsuario=$this->permisoZona();
		
		$parametro = $this->getTiempo($filtro);
		$valor = $this->getParametro($filtro);
		$parametro .= $valor[0];
		$nivel = $valor[1];	
		
		$historial = "";
		if(!$filtro->historial)					
			$historial= " and fechaEvaluacion = (select max(fechaEvaluacion) from ReporteHallazgos where codigo = h.codigo and clues = h.clues and clues = h.clues)";
		
		if($filtro->nivel<3)
		{
			if($filtro->nivel==1)
			{
				$hallazgo = DB::select("select distinct color,codigo,indicador,categoria from ReporteHallazgos h where clues in ($cluesUsuario) and clues = '$id' $parametro $historial order by codigo");
			}
			if($filtro->nivel==2)
			{
				$um = $filtro->umActiva;
				$hallazgo = DB::select("select distinct color,codigo,indicador,categoria,clues,nombre,jurisdiccion,fechaEvaluacion,idEvaluacion from ReporteHallazgos h where clues in ($cluesUsuario) and clues = '$um' and codigo = '$id' $parametro $historial order by codigo");
			}			
		}
		else{
			$criterioCalidad = null;
			$criterioRecurso = null;
			$tipo = $filtro->tipo;
			$indicador = DB::table("Indicador")->where("codigo",$filtro->indicadorActivo)->first();
			if($tipo == "CALIDAD")
			{
				$hallazgo = DB::table('EvaluacionCalidad  AS AS');
				$registro = DB::table('EvaluacionCalidadRegistro')->where("idEvaluacionCalidad",$id)->where("idIndicador",$indicador->id)->where("borradoAl",null)->get();
				$criterios = array();
				foreach($registro as $item)
				{
					$criterios = DB::select("SELECT cic.aprobado, c.id as idCriterio, ic.idIndicador, lv.id as idlugarVerificacion, c.creadoAl, c.modificadoAl, c.nombre as criterio, lv.nombre as lugarVerificacion 
							FROM EvaluacionCalidadCriterio cic							
							left join IndicadorCriterio ic on ic.idIndicador = cic.idIndicador and ic.idCriterio = cic.idCriterio
							left join Criterio c on c.id = ic.idCriterio
							left join LugarVerificacion lv on lv.id = ic.idlugarVerificacion		
							WHERE cic.idIndicador = $indicador->id and cic.idEvaluacionCalidad = $id and cic.idEvaluacionCalidadRegistro = $item->id 
							and cic.borradoAl is null and ic.borradoAl is null and c.borradoAl is null and lv.borradoAl is null");
					
					$criterioCalidad[$item->expediente] = $criterios;
					$criterioCalidad["criterios"] = $criterios;
				}
			}
			if($tipo == "RECURSO")
			{
				$hallazgo = DB::table('EvaluacionRecurso  AS AS');
				
				$criterioRecurso = DB::select("SELECT cic.aprobado, c.id as idCriterio, ic.idIndicador, lv.id as idlugarVerificacion, c.creadoAl, c.modificadoAl, c.nombre as criterio, lv.nombre as lugarVerificacion FROM EvaluacionRecursoCriterio cic							
							left join IndicadorCriterio ic on ic.idIndicador = cic.idIndicador and ic.idCriterio = cic.idCriterio
							left join Criterio c on c.id = ic.idCriterio
							left join LugarVerificacion lv on lv.id = ic.idlugarVerificacion		
							WHERE cic.idIndicador = $indicador->id and cic.idEvaluacionRecurso = $id and c.borradoAl is null and ic.borradoAl is null and cic.borradoAl is null and lv.borradoAl is null");
			}
			$hallazgo = $hallazgo->leftJoin('Clues AS c', 'c.clues', '=', 'AS.clues')
			->leftJoin('ConeClues AS cc', 'cc.clues', '=', 'AS.clues')
			->leftJoin('Cone AS co', 'co.id', '=', 'cc.idCone')
            ->leftJoin('usuarios AS us', 'us.id', '=', 'AS.idUsuario')
            ->select(array('us.email','AS.firma','AS.fechaEvaluacion', 'AS.cerrado', 'AS.id','AS.clues', 'c.nombre', 'c.domicilio', 'c.codigoPostal', 'c.entidad', 'c.municipio', 'c.localidad', 'c.jurisdiccion', 'c.institucion', 'c.tipoUnidad', 'c.estatus', 'c.estado', 'c.tipologia','co.nombre as nivelCone', 'cc.idCone'))
            ->where('AS.id',"$id")->first();
	
			$hallazgo->indicador = $indicador;
			if($criterioRecurso)
				$hallazgo->criteriosRecurso = $criterioRecurso;
			if($criterioCalidad)
				$hallazgo->criteriosCalidad = $criterioCalidad;
				
		}
		
		if(!$hallazgo)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),404);
		} 
		else 
		{
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$hallazgo),200);
		}
	}
	 
	
	/**
	 * Recupera las dimensiones para el filtrado de hallazgo.
	 *
	 * <h4>Request</h4>
	 * Request json $filtro contiene un json con el filtro
	 * 
	 * @return Response
	 * <code style="color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado), "total": count(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 404, "messages": "No hay resultados"),status) </code>
	 */
	public function hallazgoDimension()
	{
		$datos = Request::all();
		$filtro = array_key_exists("filtro",$datos) ? json_decode($datos["filtro"]) : null; 	
		$parametro = $this->getTiempo($filtro);
		$valor = $this->getParametro($filtro);
		$parametro .= $valor[0];
		$nivel = $datos["nivel"];
				
		$cluesUsuario=$this->permisoZona();

		$order = "";
		if(stripos($nivel,"indicador"))
			$order = "order by color";
		if($nivel == "anio")
			$parametro = "";
		$nivelD = DB::select("select distinct $nivel from ReporteHallazgos where clues in ($cluesUsuario) $parametro $order");
		
		if($nivel=="month")
		{
			$nivelD=$this->getBimestre($nivelD);			
		}
		if($nivel=="clues")
		{
			$in=[];
			foreach($nivelD as $i)
				$in[]=$i->clues;
				
			$nivelD = Clues::whereIn("clues",$in)->get();
		}
		if(!$nivelD)
		{
			return Response::json(array("status"=> 404,"messages"=>"No hay resultados"),404);
		} 
		else 
		{
			return Response::json(array("status" => 200, "messages"=>"Operación realizada con exito", 
			"data" => $nivelD, 
			"total" => count($nivelD)),200);
		}
	}
	
	/**
	 * Visualizar la lista de los criterios que tienen problemas.
	 *
	 *<h4>Request</h4>
	 * Request json $filtro que corresponde al filtrado
	 *
	 * @return Response
	 * <code style="color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado), "total": count(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 404, "messages": "No hay resultados"),status) </code>
	 */
	public function indexCriterios()
	{	
		$datos = Request::all();
		$filtro = array_key_exists("filtro",$datos) ? json_decode($datos["filtro"]) : null; 		
		$cluesUsuario=$this->permisoZona();
		
		$parametro = $this->getTiempo($filtro);
		$valor = $this->getParametro($filtro);
		$parametro .= $valor[0];
		$nivel = $valor[1];
		
		$historial = "";
		if(!$filtro->historial)					
			$historial= " and fechaEvaluacion = (select max(fechaEvaluacion) from ReporteHallazgos where codigo = h.codigo and clues = h.clues)";
		
		$hallazgo = DB::select("select distinct id,color,codigo,indicador,categoria, idEvaluacion from ReporteHallazgos h where clues in ($cluesUsuario) $parametro $historial");
		$criterios["RECURSO"] = array();
		$criterios["CALIDAD"] = array();
		foreach($hallazgo as $item)
		{
			$criterioCalidad = array();
			$criterioRecurso = array();
			if($item->categoria == "CALIDAD")
			{				
				$criterioCalidad = DB::select("SELECT e.clues, i.codigo,i.color,i.nombre as indicador, cic.aprobado, c.id as idCriterio, ic.idIndicador, lv.id as idlugarVerificacion, c.nombre as criterio, lv.nombre as lugarVerificacion 
						FROM EvaluacionCalidadCriterio cic	
						left join EvaluacionCalidad e on e.id = cic.idEvaluacionCalidad
						left join IndicadorCriterio ic on ic.idIndicador = cic.idIndicador and ic.idCriterio = cic.idCriterio
						left join Indicador i on i.id = ic.idIndicador
						left join Criterio c on c.id = ic.idCriterio
						left join LugarVerificacion lv on lv.id = ic.idlugarVerificacion		
						WHERE cic.idIndicador = $item->id and cic.idEvaluacionCalidad = $item->idEvaluacion 
						and cic.aprobado=0 and cic.borradoAl is null and ic.borradoAl is null and c.borradoAl is null and lv.borradoAl is null");								
			}
			if($item->categoria == "RECURSO")
			{
				$criterioRecurso = DB::select("SELECT i.codigo,i.color,i.nombre as indicador, cic.aprobado, c.id as idCriterio, ic.idIndicador, lv.id as idlugarVerificacion, c.nombre as criterio, lv.nombre as lugarVerificacion FROM EvaluacionRecursoCriterio cic							
						left join IndicadorCriterio ic on ic.idIndicador = cic.idIndicador and ic.idCriterio = cic.idCriterio
						left join Indicador i on i.id = ic.idIndicador
						left join Criterio c on c.id = ic.idCriterio
						left join LugarVerificacion lv on lv.id = ic.idlugarVerificacion		
						WHERE cic.idIndicador = $item->id and cic.idEvaluacionRecurso = $item->idEvaluacion and cic.aprobado=0 and c.borradoAl is null and ic.borradoAl is null and cic.borradoAl is null and lv.borradoAl is null");
			}
			
			if($criterioRecurso)
			{					
				foreach($criterioRecurso as $value)
				{
					if(!array_key_exists($value->idCriterio,$criterios["RECURSO"]))
					{
						$value->total = 1;						
						$criterios["RECURSO"][$value->idCriterio] = $value;
					}
					else
						$criterios["RECURSO"][$value->idCriterio]->total++;
				}
			}
			if($criterioCalidad)
			{				
				foreach($criterioCalidad as $value)
				{					
					if(!array_key_exists($value->idCriterio,$criterios["CALIDAD"]))
					{
						$value->exp = 1;
						$value->total = array();
						array_push($value->total, $value->clues);												
						$criterios["CALIDAD"][$value->idCriterio] = $value;
					}
					else
					{
						$criterios["CALIDAD"][$value->idCriterio]->exp++;
						if(!in_array($value->clues, $criterios["CALIDAD"][$value->idCriterio]->total))
							array_push($criterios["CALIDAD"][$value->idCriterio]->total, $value->clues);		
					}													
				}					
			}
		}
		
		if(!$criterios)
		{
			return Response::json(array("status"=> 404,"messages"=>"No hay resultados"),404);
		} 
		else 
		{
			foreach ($criterios["CALIDAD"] as $key => $value) {
				$value->total = count($value->total);
			}			
			return Response::json(array("status" => 200, "messages"=>"Operación realizada con exito", 
			"data" => $criterios, 
			"total" => count($criterios)),200);
		}
	}
	
	/**
	 * Devuelve la lista de las unidades medicas afectadas.
	 *
	 *<h4>Request</h4>
	 * Request json $filtro que corresponde al filtrado
	 *
	 * @return Response
	 * <code style="color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 404, "messages": "No hay resultados"),status) </code>
	 */
	public function showCriterios()
	{	
		$datos = Request::all();
		$filtro = array_key_exists("filtro",$datos) ? json_decode($datos["filtro"]) : null; 		
		$cluesUsuario=$this->permisoZona();
		
		$parametro = $this->getTiempo($filtro);
		$valor = $this->getParametro($filtro);
		$parametro .= $valor[0];
		$nivel = $valor[1];	
		
		$historial = "";
		if(!$filtro->historial)	
			$historial= " and fechaEvaluacion = (select max(fechaEvaluacion) from ReporteHallazgos where codigo = h.codigo and clues = h.clues)";
		
		$idIndicador = $filtro->criterio->indicador;
		$idCriterio  = $filtro->criterio->criterio;
		$sql = "";
		if($filtro->tipo == "CALIDAD")
		{		
			$evaluacion = DB::select("SELECT distinct idEvaluacionCalidad as id FROM EvaluacionCalidadCriterio where idIndicador = $idIndicador and idCriterio = $idCriterio and borradoAl is null");								
			$sql = "SELECT distinct idEvaluacionCalidad as id FROM EvaluacionCalidadCriterio where idEvaluacionCalidad";
		}
		if($filtro->tipo == "RECURSO")
		{
			$evaluacion = DB::select("SELECT distinct idEvaluacionRecurso as id FROM EvaluacionRecursoCriterio where idIndicador = $idIndicador and idCriterio = $idCriterio and borradoAl is null");								
			$sql = "SELECT distinct idEvaluacionRecurso as id FROM EvaluacionRecursoCriterio where idEvaluacionRecurso";
		}
		$hallazgo = array();
		foreach($evaluacion as $item)
		{
			$codigo = $filtro->indicadorActivo;
			$existe = DB::select($sql." = $item->id and idCriterio = $idCriterio and idIndicador = $idIndicador and aprobado = 0");
			if($existe)
			{
				$array = DB::select("select distinct color,codigo,indicador,categoria,clues,nombre,jurisdiccion,fechaEvaluacion,idEvaluacion from ReporteHallazgos h where clues in ($cluesUsuario) and codigo = '$codigo' and idEvaluacion = $item->id $parametro $historial");
				if($array)
					array_push($hallazgo,$array[0]);
			}
		}
		if(!$hallazgo)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),404);
		} 
		else 
		{
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$hallazgo),200);
		}
	}
	/**
	 * Obtener la lista de clues que el usuario tiene acceso.
	 *
	 * get session sentry, usuario logueado
	 * Response si la operacion es exitosa devolver un string con las clues separadas por coma
	 * @return string	 
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
			array_push($cluesUsuario,"'".$item->clues."'");
		}
		return implode(",",$cluesUsuario);
	}
	
	/**
	 * Obtener la lista del bimestre que corresponda un mes.
	 *
	 * @param string $nivelD que corresponde al numero del mes
	 * @return array
	 */
	public function getBimestre($nivelD)
	{
		$bimestre="";
		foreach($nivelD as $n)
		{
			$bimestre.=",".strtoupper($n->month);
		}
		$nivelD=array();
		if(strpos($bimestre,"JANUARY") || strpos($bimestre,"FEBRUARY"))
			array_push($nivelD,array("id" => "1 and 2" , "nombre" => "Enero -Febrero"));
		
		if(strpos($bimestre,"MARCH") || strpos($bimestre,"APRIL"))
			array_push($nivelD,array("id" => "3 and 4" , "nombre" => "Marzo - Abril"));
		
		if(strpos($bimestre,"MAY") || strpos($bimestre,"JUNE"))
			array_push($nivelD,array("id" => "5 and 6" , "nombre" => "Mayo - Junio"));
		
		if(strpos($bimestre,"JULY") || strpos($bimestre,"AUGUST"))
			array_push($nivelD,array("id" => "7 and 8" , "nombre" => "Julio - Agosto"));
		
		if(strpos($bimestre,"SEPTEMBER") || strpos($bimestre,"OCTOBER"))
			array_push($nivelD,array("id" => "9 and 10" , "nombre" => "Septiembre - Octubre"));
		
		if(strpos($bimestre,"NOVEMBER") || strpos($bimestre,"DECEMBER"))
			array_push($nivelD,array("id" => "10 and 11" , "nombre" => "Noviembre - Diciembre"));

		//////////////////////////////////////////////////////////////////////////////////////////////

		if(strpos($bimestre,"ENERO") || strpos($bimestre,"FEBRERO"))
			array_push($nivelD,array("id" => "1 and 2" , "nombre" => "Enero - Febrero"));
		
		if(strpos($bimestre,"MARZO") || strpos($bimestre,"ABRIL"))
			array_push($nivelD,array("id" => "3 and 4" , "nombre" => "Marzo - Abril"));
		
		if(strpos($bimestre,"MAYO") || strpos($bimestre,"JUNIO"))
			array_push($nivelD,array("id" => "5 and 6" , "nombre" => "Mayo - Junio"));
		
		if(strpos($bimestre,"JULIO") || strpos($bimestre,"AGOSTO"))
			array_push($nivelD,array("id" => "7 and 8" , "nombre" => "Julio - Agosto"));
		
		if(strpos($bimestre,"SEPTIEMBRE") || strpos($bimestre,"OCTUBRE"))
			array_push($nivelD,array("id" => "9 and 10" , "nombre" => "Septiembre - Octubre"));
		
		if(strpos($bimestre,"NOVIEMBRE") || strpos($bimestre,"DICIEMBRE"))
			array_push($nivelD,array("id" => "10 and 11" , "nombre" => "Noviembre - Diciembre"));
		
		return $nivelD;
	}
	/**
	 * Genera los filtros de tiempo para el query.
	 *
	 * @param json $filtro Corresponde al filtro 
	 * @return string
	 */
	public function getTiempo($filtro)
	{
		/**		 
		 * @var string $cluesUsuario contiene las clues por permiso del usuario
		 *	 
		 * @var array $anio array con los años para filtrar
		 * @var array $bimestre bimestre del año a filtrar
		 * @var string $de si se quiere hacer un filtro por fechas este marca el inicio
		 * @var string $hasta si se quiere hacer un filtro por fechas este marca el final
		 */
					
		$anio = array_key_exists("anio",$filtro) ? is_array($filtro->anio) ? implode(",",$filtro->anio) : $filtro->anio : date("Y");
		$bimestre = array_key_exists("bimestre",$filtro) ? $filtro->bimestre : 'todos';		
		$de = array_key_exists("de",$filtro) ? $filtro->de : '';
		$hasta = array_key_exists("hasta",$filtro) ? $filtro->hasta : '';
		
		// procesamiento para los filtros de tiempo
		if($de != "" && $hasta != "")
		{
			$de = date("Y-m-d", strtotime($de));
			$hasta = date("Y-m-d", strtotime($hasta));
			$parametro = " and fechaEvaluacion between '$de' and '$hasta'";
		}
		else
		{
			if($anio != "todos")
				$parametro = " and anio in($anio)";
			else $parametro="";
			
			if($bimestre != "todos")
			{
				if(is_array($bimestre))
				{
					$parametro .= " and ";
					foreach($bimestre as $item)
					{
						 $parametro .= " mes between $item or";
					}
					$parametro .= " 1=1";
				}
				else{
					$parametro .= " and mes between $bimestre";
				}
			}
		}
		return $parametro;
	}
	/**
	 * Genera los filtros de parametro para el query.
	 *
	 * @param json $filtro Corresponde al filtro 
	 * @return string
	 */
	public function getParametro($filtro)
	{		
		// si trae filtros contruir el query	
		$parametro="";$nivel = "month";
		$verTodosIndicadores = array_key_exists("verTodosIndicadores",$filtro) ? $filtro->verTodosIndicadores : true;		
		if(!$verTodosIndicadores)
		{
			$nivel = "month";
			if(array_key_exists("indicador",$filtro))
			{
				$codigo = is_array($filtro->indicador) ? implode("','",$filtro->indicador) : $filtro->indicador;
				if(is_array($filtro->indicador))
					if(count($filtro->indicador)>0)
					{
						$codigo = "'".$codigo."'";
						$parametro .= " and codigo in($codigo)";	
					}	
			}
		}
		$verTodosUM = array_key_exists("verTodosUM",$filtro) ? $filtro->verTodosUM : true;
		if(!$verTodosUM)
		{
			if(array_key_exists("jurisdiccion",$filtro->um))
			{
				if(count($filtro->um->jurisdiccion)>1)
					$nivel = "jurisdiccion";
				else{
					if($filtro->um->tipo == "municipio")
						$nivel = "municipio";
					else
						$nivel = "zona";
				}
				$codigo = is_array($filtro->um->jurisdiccion) ? implode("','",$filtro->um->jurisdiccion) : $filtro->um->jurisdiccion;
				$codigo = "'".$codigo."'";
				$parametro .= " and jurisdiccion in($codigo)";
			}
			if(array_key_exists("municipio",$filtro->um)) 
			{
				if(count($filtro->um->municipio)>1)
					$nivel = "municipio";
				else
					$nivel = "clues";
				$codigo = is_array($filtro->um->municipio) ? implode("','",$filtro->um->municipio) : $filtro->um->municipio;
				$codigo = "'".$codigo."'";
				$parametro .= " and municipio in($codigo)";
			}
			if(array_key_exists("zona",$filtro->um)) 
			{
				if(count($filtro->um->zona)>1)
					$nivel = "zona";
				else
					$nivel = "clues";
				$codigo = is_array($filtro->um->zona) ? implode("','",$filtro->um->zona) : $filtro->um->zona;
				$codigo = "'".$codigo."'";
				$parametro .= " and zona in($codigo)";
			}
			if(array_key_exists("cone",$filtro->um)) 
			{
				if(count($filtro->um->cone)>1)
					$nivel = "cone";
				else
					$nivel = "jurisdiccion";
				$codigo = is_array($filtro->um->cone) ? implode("','",$filtro->um->cone) : $filtro->um->cone;
				$codigo = "'".$codigo."'";
				$parametro .= " and cone in($codigo)";
			}
		}
		return array($parametro,$nivel);
	}
}
