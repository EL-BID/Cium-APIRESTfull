<?php
namespace App\Http\Controllers\v1\Transacciones;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Response;
use Input;
use DB;
use Session;
use Schema;
use Request;

use App\Models\Sistema\Usuario;

use App\Models\Catalogos\Clues;
use App\Models\Catalogos\ConeClues;

use App\Models\Transacciones\EvaluacionCalidadRegistro;
/**
* Controlador Dashboard
* 
* @package    CIUM API
* @subpackage Controlador
* @author     Eliecer Ramirez Esquinca <ramirez.esquinca@gmail.com>
* @created    2015-07-20
*
* Controlador `Dashboard`: Maneja los datos para mostrar en cada área del gráfico
*
*/
class DashboardController extends Controller 
{
	/**
	 * Inicia el contructor para los permisos de visualizacion
	 *	 
	 */
    public function __construct()
    {
        $this->middleware('permisos:GET.4441534842|POST.4441534842|PUT.4441534842|DELETE.4441534842');
    }	
    /**
	 * Devuelve los resultados de la petición para el gráfico de Recursos.
	 *
	 * <Ul>Filtro avanzado
	 * <Li> <code>$filtro</code> json con los datos del filtro avanzado</ li>
	 * </Ul>
	 *		    
	 * Todo Los parametros son opcionales
	 * @return Response 
	 * <code style = "color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 404, "messages": "No hay resultados"),status) </code>
	 */
	public function indicadorRecurso()
	{
		/**
		 * @var json $filtro contiene el json de los parametros
		 * @var string $datos recibe todos los parametros
		 * @var string $cluesUsuario contiene las clues por permiso del usuario
		 * @var string $parametro contiene los filtros procesados en query
		 * @var string $nivel muestra el dato de la etiqueta en el grafico
		 */
		DB::statement("SET lc_time_names = 'es_MX'");

		$datos = Request::all();
		$filtro = array_key_exists("filtro",$datos) ? json_decode($datos["filtro"]) : null; 		
		$cluesUsuario=$this->permisoZona();
		
		$parametro = $this->getTiempo($filtro);
		$valor = $this->getParametro($filtro);
		$parametro .= $valor[0];
		$nivel = $valor[1];
		
		// validar la forma de visualizar el grafico por tiempo o por parametros
		if($filtro->visualizar == "tiempo")
			$nivel = "month";
		else 
		{
			if(array_key_exists("nivel",$filtro->um))
			{
				$nivel = $filtro->um->nivel;
				if($nivel == "clues")
				{
					$codigo = is_array($filtro->clues) ? implode("','",$filtro->clues) : $filtro->clues;
					if(is_array($filtro->clues))
						if(count($filtro->clues)>0)
							$cluesUsuario = "'".$codigo."'";					
				}
			}
		}
		
		// obtener las etiquetas del nivel de desglose
		$indicadores = array();
		$nivelD = DB::select("select distinct $nivel from ReporteRecurso where clues in ($cluesUsuario) $parametro order by anio,mes");
		$nivelDesglose=[];		
	
		for($x=0;$x<count($nivelD);$x++)
		{
			$a=$nivelD[$x]->$nivel;
			array_push($nivelDesglose,$a);
		}
		// todos los indicadores que tengan al menos una evaluación
		$indicadores = DB::select("select distinct color,codigo,indicador, 'Recurso' as categoriaEvaluacion from ReporteRecurso where clues in ($cluesUsuario) $parametro order by codigo");
		$serie=[]; $colorInd=[];
		foreach($indicadores as $item)
		{
			array_push($serie,$item->indicador);
			array_push($colorInd,$item->color);
		}
		
		$color = "";
		$a = "";
		// recorrer los indicadores para obtener sus valores con respecto al filtrado		
		for($i=0;$i<count($serie);$i++)
		{
			$datos[$i] = [];
			$c=0;$porcentaje=0; $temp = "";
			for($x=0;$x<count($nivelD);$x++)
			{
				$a=$nivelD[$x]->$nivel;				
				$data["datasets"][$i]["label"]=$serie[$i];
				$sql = "select ReporteRecurso.id,indicador,total,(((aprobado+noAplica)/total)*100) as porcentaje, 
				fechaEvaluacion,dia,mes,anio,day,month,semana,clues,ReporteRecurso.nombre,cone from ReporteRecurso 
				where $nivel = '$a' and indicador = '$serie[$i]' $parametro";
				
				$reporte = DB::select($sql);
				
				if($temp!=$a)
				{
					$c=0;$porcentaje=0;
				}
				$indicador=0;
				// conseguir el color de las alertas
				if($reporte)
				{
					foreach($reporte as $r)
					{
						$porcentaje=$porcentaje+$r->porcentaje;
						$indicador=$r->id;
						$c++;
					}
					$temp = $a;
					$porcentaje = number_format($porcentaje/$c, 2, ".", ",");
					$resultColor=DB::select("select a.color from IndicadorAlerta ia 
					LEFT JOIN Alerta a on a.id=ia.idAlerta 
					where idIndicador=$indicador and ($porcentaje) between minimo and maximo");

					if($resultColor)
						$color = $resultColor[0]->color;
					else 
						$color = "rgb(150,150,150)";
					array_push($datos[$i],$porcentaje);													
				}
				else array_push($datos[$i],0);
				// array para el empaquetado de los datos y poder pintar con la libreria js-chart en angular
				
				$data["datasets"][$i]["backgroundColor"]=$colorInd[$i];
				$data["datasets"][$i]["borderColor"]=$color;
				$data["datasets"][$i]["borderWidth"]=0;
				$data["datasets"][$i]["hoverBackgroundColor"]=$colorInd[$i];
				$data["datasets"][$i]["hoverBorderColor"]=$color;
				$data["datasets"][$i]["data"]=$datos[$i];
			}	
		}
		$data["labels"]=$nivelDesglose;
		if(!$data)
		{
			return Response::json(array("status"=> 404,"messages"=>"No hay resultados"),404);
		} 
		else 
		{
			return Response::json(array("status" => 200, "messages"=>"Operación realizada con exito", 
			"data" => $data, 
			"total" => count($data)),200);			
		}
	}
	
	/**
	 * Devuelve las dimensiones para los filtros de las opciones de recurso.
	 *
	 * <Ul>Filtro avanzado
	 * <Li> <code>$filtro</code> json con los datos del filtro avanzado</ li>
	 * </Ul>
	 *		    
	 * @return Response 
	 * <code style = "color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 404, "messages": "No hay resultados"),status) </code>
	 */
	public function indicadorRecursoDimension()
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

		$nivelD = DB::select("select distinct $nivel from ReporteRecurso where clues in ($cluesUsuario) $parametro $order");
		
		if($nivel == "month")
		{
			$nivelD=$this->getTrimestre($nivelD);		
		}
		if($nivel == "clues")
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
	 * Devuelve el listado de evaluaciones de una unidad médica para el ultimo nivel del gráfico de Recursos.
	 *
	 * <h4>Request</h4>
	 * Request json $clues Clues de la unidad médica
	 * <Ul>Filtro avanzado
	 * <Li> <code>$filtro</code> json con los datos del filtro avanzado</ li>
	 * </Ul>
	 *		    
	 * @return Response 
	 * <code style = "color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 404, "messages": "No hay resultados"),status) </code>
	 */
	public function indicadorRecursoClues()
	{
		$datos = Request::all();
		$clues = $datos["clues"];
		$filtro = array_key_exists("filtro",$datos) ? json_decode($datos["filtro"]) : null;
		$cluesUsuario=$this->permisoZona();
		
		$parametro = $this->getTiempo($filtro);				
			
		$sql = "select distinct codigo,indicador,color from ReporteRecurso where clues='$clues' and clues in ($cluesUsuario) $parametro order by codigo";
		$indicadores = DB::select($sql);
		$cols=[];$serie=[]; $colorInd=[];
		foreach($indicadores as $item)
		{
			array_push($serie,$item->indicador);
			array_push($colorInd,$item->color);
		}
		$sql = "select distinct evaluacion from ReporteRecurso where clues='$clues' and clues in ($cluesUsuario) $parametro";
		
		$nivelD = DB::select($sql);
		$nivelDesglose=[];
		$color = "rgb(150,150,150)";
		
		for($x=0;$x<count($nivelD);$x++)
		{
			$a=$nivelD[$x]->evaluacion;
			array_push($nivelDesglose,"Evaluación #".$a);
		}
				
		for($i=0;$i<count($serie);$i++)
		{
			$datos[$i] = [];
			$c=0;$porcentaje=0; $temp = "";
			for($x=0;$x<count($nivelD);$x++)
			{
				$a=$nivelD[$x]->evaluacion;
				$data["datasets"][$i]["label"]=$serie[$i];
				$sql = "select ReporteRecurso.id,indicador,total,(((aprobado+noAplica)/total)*100) as porcentaje, 
				fechaEvaluacion,dia,mes,anio,day,month,semana,clues,ReporteRecurso.nombre,cone from ReporteRecurso 
				where clues='$clues' and indicador = '$serie[$i]' $parametro";
								
				$reporte = DB::select($sql);
					
				if($temp!=$a) //if($temp!=$serie[$i])
				{
					$c=0;$porcentaje=0;
				}
				$indicador=0;
				if($reporte)
				{
					foreach($reporte as $r)
					{
						$porcentaje=$porcentaje+$r->porcentaje;
						$indicador=$r->id;
						$c++;
					}
					$temp = $a;
					$porcentaje = number_format($porcentaje/$c, 2, '.', ',');
					$resultColor=DB::select("select a.color from IndicadorAlerta ia 
					LEFT JOIN Alerta a on a.id=ia.idAlerta 
					where idIndicador=$indicador and ($porcentaje) between minimo and maximo");

					if($resultColor)
						$color = $resultColor[0]->color;
					else 
						$color = "rgb(150,150,150)";

					array_push($datos[$i],$porcentaje);													
				}
				else array_push($datos[$i],0);							

				$data["datasets"][$i]["backgroundColor"]=$colorInd[$i];
				$data["datasets"][$i]["borderColor"]=$color;
				$data["datasets"][$i]["borderWidth"]=0;
				$data["datasets"][$i]["hoverBackgroundColor"]=$colorInd[$i];
				$data["datasets"][$i]["hoverBorderColor"]=$color;
				$data["datasets"][$i]["data"]=$datos[$i];
			}
		}
		$data["labels"]=$nivelDesglose;
		if(!$data)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),404);
		} 
		else 
		{
			return Response::json(array("status" => 200, "messages"=>"Operación realizada con exito", 
			"data" => $data, 
			"total" => count($data)),200);
			
		}
	}
	
	
	
	
	 /**
	 * Devuelve los resultados de la petición para el gráfico de Calidad.
	 *
	 * <Ul>Filtro avanzado
	 * <Li> <code>$filtro</code> json con los datos del filtro avanzado</ li>
	 * </Ul>
	 *		    
	 * Todo Los parametros son opcionales
	 * @return Response 
	 * <code style = "color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 404, "messages": "No hay resultados"),status) </code>
	 */
	public function indicadorCalidad()
	{
		$datos = Request::all();
		$filtro = array_key_exists("filtro",$datos) ? json_decode($datos["filtro"]) : null; 		
		$cluesUsuario=$this->permisoZona();
		
		$parametro = $this->getTiempo($filtro);
		$valor = $this->getParametro($filtro);
		$parametro .= $valor[0];
		$nivel = $valor[1];
		
		// validar la forma de visualizar el grafico por tiempo o por parametros
		if($filtro->visualizar == "tiempo")
			$nivel = "month";
		else 
		{
			if(array_key_exists("nivel",$filtro->um))
			{
				$nivel = $filtro->um->nivel;
				if($nivel == "clues")
				{
					$codigo = is_array($filtro->clues) ? implode("','",$filtro->clues) : $filtro->clues;
					if(is_array($filtro->clues))
						if(count($filtro->clues)>0)
							$cluesUsuario = "'".$codigo."'";					
				}
			}
		}
		
		// obtener las etiquetas del nivel de desglose
		$indicadores = array();
		$nivelD = DB::select("select distinct $nivel from ReporteCalidad where clues in ($cluesUsuario) $parametro order by anio,mes");
		$nivelDesglose=[];		
	
		for($x=0;$x<count($nivelD);$x++)
		{
			$a=$nivelD[$x]->$nivel;
			array_push($nivelDesglose,$a);
		}
		// todos los indicadores que tengan al menos una evaluación		
		$indicadores = DB::select("select distinct color,codigo,indicador, 'Calidad' as categoriaEvaluacion from ReporteCalidad where clues in ($cluesUsuario) $parametro order by codigo");
		$serie=[]; $colorInd=[];
		foreach($indicadores as $item)
		{
			array_push($serie,$item->indicador);
			array_push($colorInd,$item->color);
		}
		$color = "";
		$a = "";				
		for($i=0;$i<count($serie);$i++)
		{
			$datos[$i] = [];
			$c=0;$porcentaje=0; $temp = "";
			for($x=0;$x<count($nivelD);$x++)
			{
				$a=$nivelD[$x]->$nivel;				
				$data["datasets"][$i]["label"]=$serie[$i];
				$sql = "select ReporteCalidad.id,indicador,total, ((sum(cumple) / count(cumple)) * 100) as porcentaje, 
				fechaEvaluacion,dia,mes,anio,day,month,semana,clues,ReporteCalidad.nombre,cone from ReporteCalidad 
				where $nivel = '$a' and indicador = '$serie[$i]' $parametro 
				group by indicador, anio, mes";
							
				$reporte = DB::select($sql);
								
				$indicador=0;
				// conseguir el color de las alertas
				if($reporte)
				{					
					$porcentaje = number_format($reporte[0]->porcentaje, 2, ".", ",");
					$indicador=$reporte[0]->id;
					$resultColor=DB::select("select a.color from IndicadorAlerta ia 
					LEFT JOIN Alerta a on a.id=ia.idAlerta 
					where idIndicador=$indicador and ($porcentaje) between minimo and maximo");

					if($resultColor)
						$color = $resultColor[0]->color;
					else 
						$color = "rgb(150,150,150)";

					array_push($datos[$i],$porcentaje);													
				}
				else array_push($datos[$i],0);
				// array para el empaquetado de los datos y poder pintar con la libreria js-chart en angular
				
				$data["datasets"][$i]["backgroundColor"]=$colorInd[$i];
				$data["datasets"][$i]["borderColor"]=$color;
				$data["datasets"][$i]["borderWidth"]=0;
				$data["datasets"][$i]["hoverBackgroundColor"]=$colorInd[$i];
				$data["datasets"][$i]["hoverBorderColor"]=$color;
				$data["datasets"][$i]["data"]=$datos[$i];
			}
		}
		$data["labels"]=$nivelDesglose;
		if(!$data)
		{
			return Response::json(array("status"=> 404,"messages"=>"No hay resultados"),404);
		} 
		else 
		{
			return Response::json(array("status" => 200, "messages"=>"Operación realizada con exito", 
			"data" => $data, 
			"total" => count($data)),200);			
		}
	}
	
	/**
	 * Devuelve las dimensiones para los filtros de las opciones de calidad.
	 *
	 * <Ul>Filtro avanzado
	 * <Li> <code>$filtro</code> json con los datos del filtro avanzado</ li>
	 * </Ul>
	 *		    
	 * @return Response 
	 * <code style = "color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 404, "messages": "No hay resultados"),status) </code>
	 */
	public function indicadorCalidadDimension()
	{
		$datos = Request::all();
		$filtro = array_key_exists("filtro",$datos) ? json_decode($datos["filtro"]) : null; 	
		$parametro = $this->getTiempo($filtro);
		$valor = $this->getParametro($filtro);
		$parametro .= $valor[0];
		$nivel = $datos["nivel"];
				
		$cluesUsuario=$this->permisoZona();
		if($nivel == "anio")
			$parametro = "";
		$nivelD = DB::select("select distinct $nivel from ReporteCalidad where clues in ($cluesUsuario) $parametro");
		
		if($nivel == "month")
		{
			$nivelD=$this->getTrimestre($nivelD);			
		}
		if($nivel == "clues")
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
	 * Devuelve el listado de evaluaciones de una unidad médica para el ultimo nivel del gráfico de Calidad.
	 *
	 * <h4>Request</h4>
	 * Request json $clues Clues de la unidad médica
	 * <Ul>Filtro avanzado
	 * <Li> <code>$filtro</code> json con los datos del filtro avanzado</ li>
	 * </Ul>
	 *		    
	 * @return Response 
	 * <code style = "color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 404, "messages": "No hay resultados"),status) </code>
	 */
	public function indicadorCalidadClues()
	{
		$datos = Request::all();
		$clues = $datos["clues"];
		$filtro = array_key_exists("filtro",$datos) ? json_decode($datos["filtro"]) : null;
		$cluesUsuario=$this->permisoZona();
		
		$parametro = $this->getTiempo($filtro);
		$sql = "select distinct codigo,indicador,color from ReporteCalidad where clues='$clues' and clues in ($cluesUsuario) $parametro ";
		
		$sql .= "order by indicador";
		$indicadores = DB::select($sql);
		$cols=[];$serie=[]; $colorInd=[];
		foreach($indicadores as $item)
		{
			array_push($serie,$item->indicador);
			array_push($colorInd,$item->color);
		}
		$sql = "select distinct evaluacion from ReporteCalidad where clues='$clues' and clues in ($cluesUsuario) $parametro";
		
		$nivelD = DB::select($sql);
		$nivelDesglose=[];
		$color = "rgb(150,150,150)";
		
		for($x=0;$x<count($nivelD);$x++)
		{
			$a=$nivelD[$x]->evaluacion;
			array_push($nivelDesglose,"Evaluación #".$a);
		}
				
		for($i=0;$i<count($serie);$i++)
		{
			$datos[$i] = [];
			$c=0;$porcentaje=0; $temp = "";
			for($x=0;$x<count($nivelD);$x++)
			{
				$a=$nivelD[$x]->evaluacion;
				$data["datasets"][$i]["label"]=$serie[$i];

				$sql = "select ReporteCalidad.id,indicador,total, ((sum(cumple) / count(cumple)) * 100) as porcentaje, 
				fechaEvaluacion,dia,mes,anio,day,month,semana,clues,ReporteCalidad.nombre,cone from ReporteCalidad 
				where clues='$clues' and indicador = '$serie[$i]' $parametro
				group by indicador, anio, mes";
								
				$reporte = DB::select($sql);
					
				
				$indicador=0;
				if($reporte)
				{					
					$porcentaje = number_format($reporte[0]->porcentaje, 2, ".", ",");
					$indicador=$reporte[0]->id;
					$resultColor=DB::select("select a.color from IndicadorAlerta ia 
					LEFT JOIN Alerta a on a.id=ia.idAlerta 
					where idIndicador=$indicador and ($porcentaje) between minimo and maximo");

					if($resultColor)
						$color = $resultColor[0]->color;
					else 
						$color = "rgb(150,150,150)";
					array_push($datos[$i],$porcentaje);													
				}
				else array_push($datos[$i],0);
				
				
				$data["datasets"][$i]["backgroundColor"]=$colorInd[$i];
				$data["datasets"][$i]["borderColor"]=$color;
				$data["datasets"][$i]["borderWidth"]=0;
				$data["datasets"][$i]["hoverBackgroundColor"]=$colorInd[$i];
				$data["datasets"][$i]["hoverBorderColor"]=$color;
				$data["datasets"][$i]["data"]=$datos[$i];
			}
		}
		$data["labels"]=$nivelDesglose;
		if(!$data)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),404);
		} 
		else 
		{
			return Response::json(array("status" => 200, "messages"=>"Operación realizada con exito", 
			"data" => $data, 
			"total" => count($data)),200);
			
		}
	}
	
	/**
	 * Devuelve los datos para mostrar las alertas por indicador.
	 *
	 * <Ul>Filtro avanzado
	 * <Li> <code>$filtro</code> json con los datos del filtro avanzado</ li>
	 * </Ul>
	 *		    
	 * @return Response 
	 * <code style = "color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 404, "messages": "No hay resultados"),status) </code>
	 */
	public function alerta()
	{
		/*$datos = Request::all();		
		$filtro = array_key_exists("filtro",$datos) ? json_decode($datos["filtro"]) : null;
		$tipo = $filtro->tipo;
		$cluesUsuario=$this->permisoZona();
		
		$parametro = $this->getTiempo($filtro);
		$valor = $this->getParametro($filtro);
		$parametro .= $valor[0];
		$nivel = $valor[1];	
		
		
		$sql = "select distinct codigo,indicador from Reporte".$tipo." where clues in ($cluesUsuario) $parametro order by codigo";			
		$indicadores = DB::select($sql);
		$serie=[]; $codigo=[];
		foreach($indicadores as $item)
		{
			array_push($serie,$item->indicador);
			array_push($codigo,$item->codigo);
		}
		$data=[]; $temp = "";
		for($i=0;$i<count($serie);$i++)
		{
			if($tipo == "Recurso")
			{
				$sql = "select ReporteRecurso.id,indicador,total,(((aprobado+noAplica)/total)*100) as porcentaje, 
					a.color, fechaEvaluacion,dia,mes,anio,day,month,semana,clues,ReporteRecurso.nombre,cone from ReporteRecurso 
					LEFT JOIN Alerta a on a.id=(select idAlerta from IndicadorAlerta where idIndicador=ReporteRecurso.id and 
					(((aprobado+noAplica)/total)*100) between minimo and maximo ) where indicador = '$serie[$i]'";
			}
			
			if($tipo == "Calidad")
			{				
				$sql = "select ReporteCalidad.id,indicador,total, ((sum(cumple) / count(cumple)) * 100) as porcentaje, 
				fechaEvaluacion,dia,mes,anio,day,month,semana,clues,ReporteCalidad.nombre,cone from ReporteCalidad 
				where indicador = '$serie[$i]'
				group by indicador, anio, mes";
			}
			
			$sql .= " $parametro";
			$reporte = DB::select($sql);
			
			$indicador=0;
			if($reporte)
			{
				if($tipo == "Calidad")
				{
					$porcentaje = number_format($reporte[0]->porcentaje, 2, ".", ",");
					$indicador=$reporte[0]->id;
				}
				else{
					foreach($reporte as $r)
					{
						$a=$serie[$i];
						if($temp!=$a)
						{
							$c=0;$porcentaje=0;
						}
						$porcentaje=$porcentaje+$r->porcentaje;
						$indicador=$r->id;
						$c++;
						$temp = $a;
					}
					$porcentaje = number_format($porcentaje/$c, 2, '.', ',');
				}
				$resultColor=DB::select("select a.color from IndicadorAlerta ia 
				LEFT JOIN Alerta a on a.id=ia.idAlerta 
				where idIndicador=$indicador and ($porcentaje) between minimo and maximo");

				if($resultColor)
					$color = $resultColor[0]->color;
				else 
					$color = "rgb(150,150,150)";

				 array_push($data,array("codigo" => $codigo[$i],"nombre" => $serie[$i],"color" => $color, "porcentaje" => $porcentaje));													
			}
			else array_push($data,array("codigo" => $codigo[$i],"nombre" => $serie[$i],"color" => "#357ebd", "porcentaje" => "N/A"));
		}
		if(!$data)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),200);
		} 
		else 
		{
			return Response::json(array("status" => 200, "messages"=>"Operación realizada con exito", 
			"data" => $data, 
			"total" => count($data)),200);
		}*/
		$datos = Request::all();
		
		$filtro = array_key_exists("filtro",$datos) ? json_decode($datos["filtro"]) : null;			
		$tipo = $filtro->tipo;
		$cluesUsuario=$this->permisoZona();
		
		$parametro = $this->getTiempo($filtro);
		$valor = $this->getParametro($filtro);
		$parametro .= $valor[0];
		$nivel = $valor[1];	
		$where = "";
		$dimen = "indicador";
		$campos = "";
		
		if($tipo == "Recurso"){
			$promedio = "(sum(aprobado) / sum(total)   * 100)";
		}
		else{
			$promedio = "(sum(promedio) / count(clues))";
		}

		

		$sql = "SELECT distinct (select count(cic.id) from ConeIndicadorCriterio cic 
			LEFT JOIN IndicadorCriterio  ic on ic.id = cic.idIndicadorCriterio 
			where cic.idCone = r.idCone and ic.idIndicador = r.id) as criterios, 
					CONVERT($promedio, DECIMAL(4,2))  as porcentaje, $dimen as nombre, codigo FROM Reporte".$tipo." r where clues in ($cluesUsuario) $parametro $where 
			group by $dimen order by codigo";		
		
		$data = DB::select($sql);
		
		if(!$data)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),200);
		} 
		else 
		{
			foreach ($data as $key => $value) {
				$color = DB::select("select a.color from Indicador i 
				LEFT JOIN IndicadorAlerta ia on ia.idIndicador = i.id
				LEFT JOIN Alerta a on a.id = ia.idAlerta 
				where i.codigo = '$value->codigo' and ($value->porcentaje) between minimo and maximo");
				if($color)
					$value->color = $color[0]->color;
			}
			return Response::json(array("status" => 200, "messages"=>"Operación realizada con exito", 
			"data" => $data, 
			"total" => count($data)),200);
		}
	}
	
	/**
	 * Devuelve los datos para mostrar las alertas por indicador de forma estricta es decir los que cumplen o no.
	 *
	 * <Ul>Filtro avanzado
	 * <Li> <code>$filtro</code> json con los datos del filtro avanzado</ li>
	 * </Ul>
	 *		    
	 * @return Response 
	 * <code style = "color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 404, "messages": "No hay resultados"),status) </code>
	 */
	public function alertaEstricto()
	{
		$datos = Request::all();		
		$filtro = array_key_exists("filtro",$datos) ? json_decode($datos["filtro"]) : null;
		$tipo = $filtro->tipo;
		$cluesUsuario=$this->permisoZona();
		
		$parametro = $this->getTiempo($filtro);
		$valor = $this->getParametro($filtro);
		$parametro .= $valor[0];
		$nivel = $valor[1];	
		
		
		$sql = "select distinct codigo,indicador from Reporte".$tipo." where clues in ($cluesUsuario) $parametro order by codigo";			
		$indicadores = DB::select($sql);
		$serie=[]; $codigo=[];
		foreach($indicadores as $item)
		{
			array_push($serie,$item->indicador);
			array_push($codigo,$item->codigo);
		}
		$data=[]; $temp = "";
		for($i=0;$i<count($serie);$i++)
		{
			if($tipo == "Recurso")
			{
				$sql = "select ReporteRecurso.id,indicador,total,fechaEvaluacion,dia,mes,anio,day,month,semana,clues,ReporteRecurso.nombre,cone,
					(select count(noAprobado) from ReporteRecurso where indicador = '$serie[$i]' and noAprobado = 0) as cumple,
					(select count(noAprobado) from ReporteRecurso where indicador = '$serie[$i]' and noAprobado > 0) as nocumple
					from ReporteRecurso 
					where indicador = '$serie[$i]'";
			}
			
			if($tipo == "Calidad")
			{				
				$sql = "select ReporteCalidad.id,indicador,total,fechaEvaluacion,dia,mes,anio,day,month,semana,clues,ReporteCalidad.nombre,cone,
				sum(cumple) as cumple,
				(count(cumple) - sum(cumple)) as nocumple
				from ReporteCalidad 
				where indicador = '$serie[$i]' 
				group by indicador, anio, mes";
			}
			
			$sql .= " $parametro";
			$reporte = DB::select($sql);
			
			$indicador=0;
			if($reporte)
			{
				if($tipo == "Calidad")
				{
					$cumple = $reporte[0]->cumple;
					$nocumple = $reporte[0]->nocumple;
					$porcentaje = ($cumple / ($cumple + $nocumple))*100;
					$porcentaje = number_format($porcentaje, 2, ".", ",");
					$indicador=$reporte[0]->id;
				}
				else{
					foreach($reporte as $r)
					{
						$a=$serie[$i];
						if($temp!=$a)
						{
							$c = 0; $porcentaje = 0;
							$cumple = 0; $nocumple = 0;
						}
						$cumple = $r->cumple;
						$nocumple = $r->nocumple;
						$porcentaje=$porcentaje+(($cumple/($cumple+$nocumple))*100);
						$indicador=$r->id;
						$c++;
						$temp = $a;
					}
					$porcentaje = number_format($porcentaje/$c, 2, '.', ',');
				}

				$resultColor=DB::select("select a.color from IndicadorAlerta ia 
				LEFT JOIN Alerta a on a.id=ia.idAlerta 
				where idIndicador=$indicador and ($porcentaje) between minimo and maximo");

				if($resultColor)
					$color = $resultColor[0]->color;
				else 
					$color = "rgb(150,150,150)";

				 array_push($data,array("codigo" => $codigo[$i],"nombre" => $serie[$i],"color" => $color, "porcentaje" => $porcentaje, "cumple" => $cumple, "noCumple" => $nocumple));													
			}
			else array_push($data,array("codigo" => $codigo[$i],"nombre" => $serie[$i],"color" => "#357ebd", "porcentaje" => "N/A", "cumple" => 0, "noCumple" => 0));
		}
		if(!$data)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),200);
		} 
		else 
		{
			return Response::json(array("status" => 200, "messages"=>"Operación realizada con exito", 
			"data" => $data, 
			"total" => count($data)),200);
		}
	}
	

	/**
	 * Devuelve los datos para mostrar las alertas por nivel de desglose, jurisdiccion, municipio, localidad, cone, clues.
	 *
	 * <Ul>Filtro avanzado
	 * <Li> <code>$filtro</code> json con los datos del filtro avanzado</ li>
	 * </Ul>
	 *		    
	 * @return Response 
	 * <code style = "color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 404, "messages": "No hay resultados"),status) </code>
	 */
	public function alertaDetalle()
	{
		$datos = Request::all();
		
		$filtro = array_key_exists("filtro",$datos) ? json_decode($datos["filtro"]) : null;
		$id = $filtro->id;		
		$tipo = $filtro->tipo;
		$cluesUsuario=$this->permisoZona();
		
		$parametro = $this->getTiempo($filtro);
		$valor = $this->getParametro($filtro);
		$parametro .= $valor[0];
		$nivel = $valor[1];	
		$where = "";
		$dimen = "jurisdiccion";
		$campos = "";
		if(property_exists($filtro, "grado")){
			if($filtro->grado == 1){
				$where = "and jurisdiccion = '".$filtro->valor."'";
				$dimen = "municipio";
			}
			if($filtro->grado == 2){
				$where = "and municipio = '".$filtro->valor."'";
				$dimen = "nombre";
			}
			if($filtro->grado == 4){
				$where = "and evaluacion = '".$filtro->valor."' and codigo = '".$filtro->indicador."'";
				$dimen = "evaluacion";
				$campos = "codigo, clues, nombre, fechaEvaluacion, jurisdiccion, ";
			}		
		}
		if($tipo == "Recurso"){
			$promedio = "(sum(aprobado) / sum(total)   * 100)";
		}
		else{
			$promedio = "(sum(promedio) / count(clues))";
		}

		$color = "(select a.color from Indicador i 
				LEFT JOIN IndicadorAlerta ia on ia.idIndicador = i.id
				LEFT JOIN Alerta a on a.id = ia.idAlerta 
				where i.codigo = '$id' and ($promedio) between minimo and maximo)";
		$sql = "SELECT distinct $campos evaluacion, count(clues) as um, sum(total) as total, (select count(cic.id) from ConeIndicadorCriterio cic 
			LEFT JOIN IndicadorCriterio  ic on ic.id = cic.idIndicadorCriterio 
			where cic.idCone = r.idCone and ic.idIndicador = r.id) as criterios, 
					CONVERT($promedio, DECIMAL(4,2))  as promedio, $color  as color, $dimen, cone FROM Reporte".$tipo." r where clues in ($cluesUsuario) $parametro and codigo = '$id' $where 
			group by $dimen, cone";		

		if(property_exists($filtro, "grado")){
			if($filtro->grado == 3){
				$where = "and nombre = '".$filtro->valor."'";
				$dimen = "evaluacion";
				$sql = "SELECT distinct evaluacion, count(clues) as um, sum(total) as total, (select count(cic.id) from ConeIndicadorCriterio cic LEFT JOIN IndicadorCriterio  ic on ic.id = cic.idIndicadorCriterio where cic.idCone = r.idCone and ic.idIndicador = r.id) as criterios, CONVERT($promedio, DECIMAL(4,2))  as promedio, $color  as color, $dimen, cone FROM Reporte".$tipo." r where clues in ($cluesUsuario) $parametro and codigo = '$id' $where";						
			}
		}	
		$data = DB::select($sql);
		
		if(!$data)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),200);
		} 
		else 
		{
			return Response::json(array("status" => 200, "messages"=>"Operación realizada con exito", 
			"data" => $data, 
			"total" => count($data)),200);
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
	public function alertaEvaluacion()
	{	
		$datos = Request::all();
		$filtro = array_key_exists("filtro",$datos) ? json_decode($datos["filtro"]) : null; 		
		$cluesUsuario=$this->permisoZona();
		
		$parametro = $this->getTiempo($filtro);
		$valor = $this->getParametro($filtro);
		$parametro .= $valor[0];
		$nivel = $valor[1];	
		
		$historial = "";
		$hallazgo = "";

		$criterioCalidad = null;
		$criterioRecurso = null;
		$tipo = $filtro->tipo;
		$id = $filtro->valor;

		$indicador = DB::table("Indicador")->where("codigo",$filtro->indicador)->first();
		if($tipo == "Calidad")
		{
			$hallazgo = DB::table('EvaluacionCalidad  AS AS');
			$registro = DB::table('EvaluacionCalidadRegistro')->where("idEvaluacionCalidad",$id)->where("idIndicador",$indicador->id)->where("borradoAl",null)->get();
			$criterios = array();
			foreach($registro as $item)
			{
				$criterios = DB::select("SELECT cic.aprobado, c.id as idCriterio, ic.idIndicador, lv.id as idlugarVerificacion, c.creadoAl, c.modificadoAl, c.nombre as criterio, lv.nombre as lugarVerificacion 
						FROM EvaluacionCalidadCriterio cic							
						LEFT JOIN IndicadorCriterio ic on ic.idIndicador = cic.idIndicador and ic.idCriterio = cic.idCriterio
						LEFT JOIN Criterio c on c.id = ic.idCriterio
						LEFT JOIN LugarVerificacion lv on lv.id = ic.idlugarVerificacion		
						WHERE cic.idIndicador = $indicador->id and cic.idEvaluacionCalidad = $id and cic.idEvaluacionCalidadRegistro = $item->id 
						and cic.borradoAl is null and ic.borradoAl is null and c.borradoAl is null and lv.borradoAl is null");
				
				$criterioCalidad[$item->expediente] = $criterios;
				$criterioCalidad["criterios"] = $criterios;
			}
		}
		if($tipo == "Recurso")
		{
			$hallazgo = DB::table('EvaluacionRecurso  AS AS');
			
			$criterioRecurso = DB::select("SELECT cic.aprobado, c.id as idCriterio, ic.idIndicador, lv.id as idlugarVerificacion, c.creadoAl, c.modificadoAl, c.nombre as criterio, lv.nombre as lugarVerificacion FROM EvaluacionRecursoCriterio cic							
						LEFT JOIN IndicadorCriterio ic on ic.idIndicador = cic.idIndicador and ic.idCriterio = cic.idCriterio
						LEFT JOIN Criterio c on c.id = ic.idCriterio
						LEFT JOIN LugarVerificacion lv on lv.id = ic.idlugarVerificacion		
						WHERE cic.idIndicador = $indicador->id and cic.idEvaluacionRecurso = $id and c.borradoAl is null and ic.borradoAl is null and cic.borradoAl is null and lv.borradoAl is null");
		}
		$hallazgo = $hallazgo->LEFTJOIN('Clues AS c', 'c.clues', '=', 'AS.clues')
		->LEFTJOIN('ConeClues AS cc', 'cc.clues', '=', 'AS.clues')
		->LEFTJOIN('Cone AS co', 'co.id', '=', 'cc.idCone')
        ->LEFTJOIN('usuarios AS us', 'us.id', '=', 'AS.idUsuario')
        ->select(array('us.email','AS.firma','AS.fechaEvaluacion', 'AS.cerrado', 'AS.id','AS.clues', 'c.nombre', 'c.domicilio', 'c.codigoPostal', 'c.entidad', 'c.municipio', 'c.localidad', 'c.jurisdiccion', 'c.institucion', 'c.tipoUnidad', 'c.estatus', 'c.estado', 'c.tipologia','co.nombre as nivelCone', 'cc.idCone'))
        ->where('AS.id',"$id")->first();

		$hallazgo->indicador = $indicador;
		if($criterioRecurso)
			$hallazgo->criteriosRecurso = $criterioRecurso;
		if($criterioCalidad)
			$hallazgo->criteriosCalidad = $criterioCalidad;
				
		
		
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
	 * Devuelve los datos para las graficas tipo gauge.
	 *
	 * <Ul>Filtro avanzado
	 * <Li> <code>$filtro</code> json con los datos del filtro avanzado</ li>
	 * </Ul>
	 *		    
	 * @return Response 
	 * <code style = "color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 404, "messages": "No hay resultados"),status) </code>
	 */
	public function hallazgoGauge()
	{
		$datos = Request::all();		
		$filtro = array_key_exists("filtro",$datos) ? json_decode($datos["filtro"]) : null;
		$tipo = $filtro->tipo;
		$cluesUsuario=$this->permisoZona();
		
		$parametro = $this->getTiempo($filtro);
		$valor = $this->getParametro($filtro);
		$parametro .= $valor[0];
		$nivel = $valor[1];			
		
		$sql = ""; $sql0 = "";
		
		if($tipo == "Recurso")
		{
			$sql0 .= "noAprobado=0";
		}
		if($tipo == "Calidad")
		{
			$sql0 .= "promedio<100";
		}
		

		if($filtro->estricto){
			$sql1 = "SELECT distinct count(distinct sh.clues) as total FROM  ConeClues sh where sh.clues in ($cluesUsuario) AND sh.clues in (SELECT clues FROM Reporte$tipo WHERE 1=1 $parametro)";
		}else{
			$sql1 = "SELECT distinct count(distinct sh.clues) as total FROM  ConeClues sh where sh.clues in ($cluesUsuario)";
		}

		$verTodosUM = array_key_exists("verTodosUM",$filtro) ? $filtro->verTodosUM : true;

		if(!$verTodosUM)
		{
			if(array_key_exists("jurisdiccion",$filtro->um))
			{
				$codigo = is_array($filtro->um->jurisdiccion) ? implode("','",$filtro->um->jurisdiccion) : $filtro->um->jurisdiccion;
				$codigo = "'".$codigo."'";
				$sql1 .= " AND sh.clues in (SELECT clues FROM Clues c WHERE c.jurisdiccion in ($codigo))";
			}
			if(array_key_exists("municipio",$filtro->um)) 
			{
				$codigo = is_array($filtro->um->municipio) ? implode("','",$filtro->um->municipio) : $filtro->um->municipio;
				$codigo = "'".$codigo."'";
				$sql1 .= " AND sh.clues in (SELECT clues FROM Clues c WHERE c.municipio in ($codigo))";
			}
			if(array_key_exists("zona",$filtro->um)) 
			{
				$codigo = is_array($filtro->um->zona) ? implode("','",$filtro->um->zona) : $filtro->um->zona;
				$codigo = "'".$codigo."'";
				$sql1 .= " AND sh.clues in (SELECT clues FROM Clues c WHERE c.zona in ($codigo))";
			}
			if(array_key_exists("cone",$filtro->um)) 
			{
				$codigo = is_array($filtro->um->cone) ? implode("','",$filtro->um->cone) : $filtro->um->cone;
				$codigo = "'".$codigo."'";
				$sql1 .= " AND sh.clues in (SELECT clues FROM Clues c WHERE c.cone in ($codigo))";
			}
		}

		$sql2 = "SELECT clues FROM Reporte$tipo sh where $sql0  and sh.clues in ($cluesUsuario) $parametro group by clues";
		$sql3 = "SELECT distinct codigo,color,indicador FROM Reporte$tipo sh where $sql0 and sh.clues in ($cluesUsuario) $parametro group by indicador";
		
				
		$data = DB::select($sql1);
		
		if(!$data)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),404);
		} 
		else 
		{	
			$data2 = DB::select($sql2);
			$data3 = DB::select($sql3);
			$resuelto = count($data2);
			$total = $data[0]->total;
			
			$rojo = ($total*.10);
			$nara = ($total*.25);
			$amar = ($total*.5);
			$verd = $total;
			
			$rangos[0] = array('min' => 0,     'max' => $rojo, 'color' => '#DDD');
			$rangos[1] = array('min' => $rojo, 'max' => $nara, 'color' => '#FDC702');
			$rangos[2] = array('min' => $nara, 'max' => $amar, 'color' => '#FF7700');
			$rangos[3] = array('min' => $amar, 'max' => $verd, 'color' => '#C50200');
						
			return Response::json(array("status" => 200, "messages"=>"Operación realizada con exito", 
			"data"  => $data,
			"valor" => $resuelto,
			"rangos"=> $rangos,
			"indicadores" => $data3,
			"total" => $total),200);
		}
	}
	
	
	/**
	 * Devuelve el TOP de las evaluaciones de calidad.
	 *
	 * <Ul>Filtro avanzado
	 * <Li> <code>$filtro</code> json con los datos del filtro avanzado</ li>
	 * </Ul>
	 *		    
	 * @return Response 
	 * <code style = "color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 404, "messages": "No hay resultados"),status) </code>
	 */
	public function topCalidadGlobal()
	{
		$datos = Request::all();		
		$filtro = array_key_exists("filtro",$datos) ? json_decode($datos["filtro"]) : null;
		$top = array_key_exists("top",$filtro) ? $filtro->top : 5;
		$tipo = $filtro->tipo;
		$cluesUsuario=$this->permisoZona();
		
		$parametro = $this->getTiempo($filtro);
		$valor = $this->getParametro($filtro);
		$parametro .= $valor[0];
		$nivel = $valor[1];	
		
		

		$sql = "((select sum(promedio) from ReporteCalidad where clues = c.clues)/(select count(promedio) from ReporteCalidad where clues = c.clues))";
		$sql1 = "select distinct clues,nombre, $sql as porcentaje from ReporteCalidad c where clues in ($cluesUsuario) $parametro and $sql between 80 and 100 order by $sql desc limit 0,$top";						
		$sql2 = "select distinct clues,nombre, $sql as porcentaje from ReporteCalidad c where clues in ($cluesUsuario) $parametro and $sql between 0 and 80  order by $sql asc limit 0,$top";
										
		$data["TOP_MAS"] = DB::select($sql1);
		$data["TOP_MENOS"] = DB::select($sql2);
		
		if(!$data)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),404);
		} 
		else 
		{
			return Response::json(array("status" => 200, "messages"=>"Operación realizada con exito", 
			"data" => $data, 
			"total" => count($data)),200);			
		}
	}
	
	/**
	 * Devuelve TOP de las evaluaciones de recurso.
	 *
	 * <Ul>Filtro avanzado
	 * <Li> <code>$filtro</code> json con los datos del filtro avanzado</ li>
	 * </Ul>
	 *		    
	 * @return Response 
	 * <code style = "color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 404, "messages": "No hay resultados"),status) </code>
	 */
	public function topRecursoGlobal()
	{
		$datos = Request::all();		
		$filtro = array_key_exists("filtro",$datos) ? json_decode($datos["filtro"]) : null;
		$top = array_key_exists("top",$filtro) ? $filtro->top : 5;
		$tipo = $filtro->tipo;
		$cluesUsuario=$this->permisoZona();
		
		$parametro = $this->getTiempo($filtro);
		$valor = $this->getParametro($filtro);
		$parametro .= $valor[0];
		$nivel = $valor[1];						
		
		

		$sql = "((select sum(aprobado) from ReporteRecurso where clues = r.clues)/(select sum(total) from ReporteRecurso where clues = r.clues))*100";
		$sql1 = "select distinct clues,nombre, $sql as porcentaje from ReporteRecurso r where clues in ($cluesUsuario) $parametro and $sql between 80 and 100 order by $sql desc limit 0,$top";		
		$sql2 = "select distinct clues,nombre, $sql as porcentaje from ReporteRecurso r where clues in ($cluesUsuario) $parametro and $sql between 0 and 80 order by $sql asc limit 0,$top ";		
		$data["TOP_MAS"] = DB::select($sql1);
		$data["TOP_MENOS"] = DB::select($sql2);
		
		if(!$data)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),404);
		} 
		else 
		{
			return Response::json(array("status" => 200, "messages"=>"Operación realizada con exito", 
			"data" => $data, 
			"total" => count($data)),200);			
		}
	}
	
	
	/**
	 * Devuelve los datos para generar el gráfico tipo Pie de las evaluaciones recurso y calidad.
	 *
	 * <Ul>Filtro avanzado
	 * <Li> <code>$filtro</code> json con los datos del filtro avanzado</ li>
	 * </Ul>
	 *		    
	 * @return Response 
	 * <code style = "color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 404, "messages": "No hay resultados"),status) </code>
	 */
	public function pieVisita()
	{
		$datos = Request::all();		
		$filtro = array_key_exists("filtro",$datos) ? json_decode($datos["filtro"]) : null;
		$tipo = $filtro->tipo;
		$cluesUsuario=$this->permisoZona();
		
		$parametro = $this->getTiempo($filtro);
		$valor = $this->getParametro($filtro);
		$parametro .= $valor[0];
		$nivel = $valor[1];										
		
		$sql = "SELECT count(distinct clues) as total from Reporte".$tipo." where clues in ($cluesUsuario) $parametro";
		$tot = "SELECT count(clues) as total from Clues where clues in ($cluesUsuario) ";
		$tot=DB::select($tot);
		$totalClues=$tot[0]->total;
		$data = DB::select($sql);
		
		if(!$data)
		{
			
			$data["labels"]=array("Selecciones opciones para mostrar datos");
			$data["datasets"][0]["backgroundColor"] = array("rgb(150,150,150)");
			$data["datasets"][0]["hoverBackgroundColor"] = array("rgb(180,180,180)");

			return Response::json(array("status" => 200, "messages"=>"Operación realizada con exito", 
			"data"  => $data,
			"total" => 0),200);
		} 
		else 
		{	
				
			$total=$data[0]->total;	

			$data["labels"]=array("No Visitado", "Visitado");
			$data["datasets"][0]["data"] = array($totalClues - $total, $total);
			$data["datasets"][0]["backgroundColor"] = array("rgb(180,0,0)", "rgb(0,180,0)");
			$data["datasets"][0]["hoverBackgroundColor"] = array("rgb(200,0,0)", "rgb(0,200,0)");

			return Response::json(array("status" => 200, "messages"=>"Operación realizada con exito", 
			"data"  => $data,
			"total" => $total),200);
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
			->LEFTJOIN('Zona AS z', 'z.id', '=', 'u.idZona')
			->LEFTJOIN('ZonaClues AS zu', 'zu.idZona', '=', 'z.id')
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
	public function getTrimestre($nivelD)
	{
		$bimestre = "";
		foreach($nivelD as $n)
		{
			$bimestre .= ",".strtoupper($n->month);
		}
		$nivelD=array();
		if(strpos($bimestre,"JANUARY") || strpos($bimestre,"FEBRUARY") || strpos($bimestre,"MARCH") )
			array_push($nivelD,array("id" => "1 and 3" , "nombre" => "Enero - Marzo"));
		
		if(strpos($bimestre,"APRIL") || strpos($bimestre,"MAY") || strpos($bimestre,"JUNE"))
			array_push($nivelD,array("id" => "4 and 6" , "nombre" => "Abril - Junio"));
		
		if(strpos($bimestre,"JULY") || strpos($bimestre,"AUGUST") || strpos($bimestre,"SEPTEMBER"))
			array_push($nivelD,array("id" => "7 and 9" , "nombre" => "Julio - Septiembre"));
		
		if(strpos($bimestre,"OCTOBER") || strpos($bimestre,"NOVEMBER") || strpos($bimestre,"DECEMBER"))
			array_push($nivelD,array("id" => "10 and 12" , "nombre" => "Octubre - Diciembre"));

		//////////////////////////////////////////////////////////////////////////////////////////////

		if(strpos($bimestre,"ENERO") || strpos($bimestre,"FEBRERO") || strpos($bimestre,"MARZO"))
			array_push($nivelD,array("id" => "1 and 3" , "nombre" => "Enero - Marzo"));
		
		if(strpos($bimestre,"ABRIL") || strpos($bimestre,"MAYO") || strpos($bimestre,"JUNIO"))
			array_push($nivelD,array("id" => "4 and 6" , "nombre" => "Abril - Junio"));		
		
		if(strpos($bimestre,"JULIO") || strpos($bimestre,"AGOSTO") || strpos($bimestre,"SEPTIEMBRE"))
			array_push($nivelD,array("id" => "7 and 9" , "nombre" => "Julio - Septiembre"));
		
		if(strpos($bimestre,"OCTUBRE") || strpos($bimestre,"NOVIEMBRE") || strpos($bimestre,"DICIEMBRE"))
			array_push($nivelD,array("id" => "10 and 12" , "nombre" => "Octubre - Diciembre"));
		
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
			else $parametro = "";
			
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
		$parametro = "";$nivel = "month";
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


	/**
	 * Devuelve los datos para mostrar las criterios por indicador.
	 *
	 * <Ul>Filtro avanzado
	 * <Li> <code>$filtro</code> json con los datos del filtro avanzado</ li>
	 * </Ul>
	 *		    
	 * @return Response 
	 * <code style = "color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 404, "messages": "No hay resultados"),status) </code>
	 */
	public function criterio()
	{		
		$datos = Request::all();
		
		$filtro = array_key_exists("filtro",$datos) ? json_decode($datos["filtro"]) : null;			
		$tipo = $filtro->tipo;
		$cluesUsuario=$this->permisoZona();
		
		$parametro = $this->getTiempo($filtro);
		$valor = $this->getParametro($filtro);
		$parametro .= $valor[0];
		$nivel = $valor[1];	
		$where = "";
		$dimen = "indicador";
		$campos = "";
		
		if($tipo == "Recurso"){
			$promedio = "(sum(aprobado) / sum(total)   * 100)";
		}
		else{
			$promedio = "(sum(promedio) / count(clues))";
		}

		

		$sql = "SELECT distinct (select count(cic.id) from ConeIndicadorCriterio cic 
			LEFT JOIN IndicadorCriterio  ic on ic.id = cic.idIndicadorCriterio 
			where cic.idCone = r.idCone and ic.idIndicador = r.id) as criterios, 
					CONVERT($promedio, DECIMAL(4,2))  as porcentaje, $dimen as nombre, codigo FROM Reporte".$tipo." r where clues in ($cluesUsuario) $parametro $where 
			group by $dimen order by codigo";		
		
		$data = DB::select($sql);
		
		if(!$data)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),200);
		} 
		else 
		{
			foreach ($data as $key => $value) {
				$color = DB::select("select a.color from Indicador i 
				LEFT JOIN IndicadorAlerta ia on ia.idIndicador = i.id
				LEFT JOIN Alerta a on a.id = ia.idAlerta 
				where i.codigo = '$value->codigo' and ($value->porcentaje) between minimo and maximo");
				if($color)
					$value->color = $color[0]->color;
			}
			$fecha = date("Y-m-d");
			$crear = true;
			if(Schema::hasTable("Temp$tipo")){

				$tiene = DB::select("select codigo from Temp$tipo where temporal = '$fecha'");
				if(count($tiene)>0){
					$crear = false; 
				}
				else{
					DB::select("drop table Temp$tipo");
				}	
			}

			if($crear){
				$sql_new ="CREATE TABLE Temp$tipo AS (";
				if($tipo == "Recurso"){
					$sql_new .= "SELECT distinct i.id AS id,e.id AS evaluacion,i.color AS color,i.codigo AS codigo,
					i.nombre AS indicador, cr.nombre as criterio, cr.id as idCriterio, ec.aprobado,
					e.fechaEvaluacion AS 
					fechaEvaluacion,dayname(e.fechaEvaluacion) AS day,
					dayofmonth(e.fechaEvaluacion) AS dia,
					monthname(e.fechaEvaluacion) AS month,
					month(e.fechaEvaluacion) AS mes,year(e.fechaEvaluacion) AS anio,
					week(e.fechaEvaluacion,3) AS semana,
					e.clues AS clues,c.nombre AS nombre,
					cn.nombre AS cone,
					cn.id as idCone,
					c.jurisdiccion AS jurisdiccion,
					c.municipio AS municipio,
					z.nombre AS zona, 
					'$fecha' as temporal

					from EvaluacionRecursoCriterio ec 
					LEFT JOIN Indicador i on i.id = ec.idIndicador
					LEFT JOIN EvaluacionRecurso e on e.id = ec.idEvaluacionRecurso and e.clues in (SELECT distinct e1.clues from EvaluacionRecurso e1 where month(e1.fechaEvaluacion) = month(e.fechaEvaluacion) and e1.cerrado = '1' and e.fechaEvaluacion = (select max(e2.fechaEvaluacion) from EvaluacionRecurso e2 where e2.clues = e.clues and (year(e2.fechaEvaluacion) = year(e.fechaEvaluacion)) and e2.cerrado = '1'))

					LEFT JOIN Criterio cr on cr.id = ec.idCriterio 
					LEFT JOIN Clues c on c.clues = e.clues 
					LEFT JOIN ConeClues cc on cc.clues = c.clues
					LEFT JOIN Cone cn on cn.id = cc.idCone
					LEFT JOIN ZonaClues zc on zc.clues = e.clues
					LEFT JOIN Zona z on z.id = zc.idZona
					where ec.borradoAl is null and e.borradoAl is null and e.id is not null and e.cerrado = '1'";
				}
				else{
					$sql_new .= "SELECT distinct i.id,e.id as evaluacion,i.color,i.codigo,i.nombre as indicador,cr.nombre as criterio, cr.id as idCriterio, ec.aprobado, 
					e.fechaEvaluacion,DAYNAME(e.fechaEvaluacion) as day, DAYOFMONTH(e.fechaEvaluacion) as dia, 
					MONTHNAME(e.fechaEvaluacion) as month, MONTH(e.fechaEvaluacion) as mes, YEAR(e.fechaEvaluacion) as anio, WEEKOFYEAR(e.fechaEvaluacion) as semana,
					e.clues, c.nombre, cn.nombre as cone, cn.id as idCone, c.jurisdiccion, c.municipio, z.nombre as zona,
					'$fecha' as temporal

					FROM EvaluacionCalidadCriterio ec
					LEFT JOIN  Indicador i on i.id = ec.idIndicador
					LEFT JOIN EvaluacionCalidad e on e.id = ec.idEvaluacionCalidad and e.clues in(SELECT distinct e1.clues from EvaluacionCalidad e1 where MONTH(e1.fechaEvaluacion)=MONTH(e.fechaEvaluacion) and e1.cerrado = '1' ) and e.fechaEvaluacion = (select max(e2.fechaEvaluacion) from EvaluacionCalidad e2 where e2.clues=e.clues and YEAR(e2.fechaEvaluacion)=YEAR(e.fechaEvaluacion) and e2.cerrado = '1')

					LEFT JOIN Criterio cr on cr.id = ec.idCriterio
					LEFT JOIN Clues c on c.clues = e.clues
					LEFT JOIN ConeClues cc on cc.clues = c.clues
					LEFT JOIN Cone cn on cn.id = cc.idCone
					LEFT JOIN ZonaClues zc on zc.clues = e.clues
					LEFT JOIN Zona z on z.id = zc.idZona
					where ec.borradoAl is null and e.borradoAl is null and e.id is not null and e.cerrado = '1'";
				}
				$sql_new .=");";
				$createTempTables = DB::select(DB::raw($sql_new));
			}
			
			return Response::json(array("status" => 200, "messages"=>"Operación realizada con exito", 
			"data" => $data, 
			"total" => count($data)),200);
		}
	}

	/**
	 * Devuelve los datos para mostrar los criterios del indicador seleccionado
	 *
	 * <Ul>Filtro avanzado
	 * <Li> <code>$filtro</code> json con los datos del filtro avanzado</ li>
	 * </Ul>
	 *		    
	 * @return Response 
	 * <code style = "color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 404, "messages": "No hay resultados"),status) </code>
	 */
	public function criterioDetalle()
	{
		$datos = Request::all();
		
		$filtro = array_key_exists("filtro",$datos) ? json_decode($datos["filtro"]) : null;
		$id = $filtro->id;		
		$tipo = $filtro->tipo;
		$cluesUsuario=$this->permisoZona();
		
		$parametro = $this->getTiempo($filtro);
		$valor = $this->getParametro($filtro);
		$parametro .= $valor[0];
		$nivel = $valor[1];	
		
		
		$campos = "criterio as nombre, idCriterio as id,";
		$where = "and codigo = '".$id."'";
		$dimen = "criterio";
		if(property_exists($filtro, "valor"))
			$value = explode("|", $filtro->valor);
		if(property_exists($filtro, "grado")){
			if($filtro->grado == 1){					
				$campos = "jurisdiccion as nombre, jurisdiccion as id,";
				$where = "and idCriterio = '".$value[1]."' and codigo = '".$id."'";
				$dimen = "jurisdiccion";
			}
			if($filtro->grado == 2){				
				$campos = "concat(clues,' ',nombre) as nombre, clues as id,";
				$where = "and idCriterio = '".$value[1]."' and jurisdiccion = '".$value[2]."' and codigo = '".$id."'";
				$dimen = "clues";
			}
			if($filtro->grado == 3){
				$campos = "codigo, clues, nombre, fechaEvaluacion, jurisdiccion, ";
				$where = "and idCriterio = '".$value[1]."' and jurisdiccion = '".$value[2]."' and clues = '".$value[3]."' and codigo = '".$id."'";				
				$dimen = "evaluacion";
			}
			if($filtro->grado == 4){
				$campos = "codigo, clues, nombre, fechaEvaluacion, jurisdiccion, ";
				$where = "and idCriterio = '".$value[1]."' and jurisdiccion = '".$value[2]."' and clues = '".$value[3]."' and evaluacion = '".$value[4]."' and codigo = '".$id."'";				
				$dimen = "evaluacion";
				
			}		
		}
		
		$promedio = "(sum(aprobado) / count(aprobado) * 100)";
		
		$color = "(select a.color from Indicador i 
				LEFT JOIN IndicadorAlerta ia on ia.idIndicador = i.id
				LEFT JOIN Alerta a on a.id = ia.idAlerta 
				where i.codigo = '$id' and ($promedio) between minimo and maximo)";

		$sql = "SELECT distinct $campos evaluacion, count(aprobado) as total, sum(aprobado) as aprobado, 
			(SELECT count(cic.id) from ConeIndicadorCriterio cic 
			LEFT JOIN IndicadorCriterio  ic on ic.id = cic.idIndicadorCriterio 
			where cic.idCone = r.idCone and ic.idIndicador = r.id) as criterios, 
			CONVERT($promedio, DECIMAL(4,2))  as promedio, $color  as color, $dimen, cone 
			FROM Temp".$tipo." r where clues in ($cluesUsuario) $parametro and codigo = '$id' $where 
			group by $dimen";		

		$data = DB::select($sql);
		
		if(!$data)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),200);
		} 
		else 
		{
			return Response::json(array("status" => 200, "messages"=>"Operación realizada con exito", 
			"data" => $data, 
			"total" => count($data)),200);
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
	public function criterioEvaluacion()
	{	
		$datos = Request::all();
		$filtro = array_key_exists("filtro",$datos) ? json_decode($datos["filtro"]) : null; 		
		$cluesUsuario=$this->permisoZona();
		
		$parametro = $this->getTiempo($filtro);
		$valor = $this->getParametro($filtro);
		$parametro .= $valor[0];
		$nivel = $valor[1];	
		
		$historial = "";
		$hallazgo = "";

		$criterioCalidad = null;
		$criterioRecurso = null;
		$tipo = $filtro->tipo;
		$id = $filtro->valor;

		$indicador = DB::table("Indicador")->where("codigo",$filtro->indicador)->first();
		if($tipo == "Calidad")
		{
			$hallazgo = DB::table('EvaluacionCalidad  AS AS');
			$registro = DB::table('EvaluacionCalidadRegistro')->where("idEvaluacionCalidad",$id)->where("idIndicador",$indicador->id)->where("borradoAl",null)->get();
			$criterios = array();
			foreach($registro as $item)
			{
				$criterios = DB::select("SELECT cic.aprobado, c.id as idCriterio, ic.idIndicador, lv.id as idlugarVerificacion, c.creadoAl, c.modificadoAl, c.nombre as criterio, lv.nombre as lugarVerificacion 
						FROM EvaluacionCalidadCriterio cic							
						LEFT JOIN IndicadorCriterio ic on ic.idIndicador = cic.idIndicador and ic.idCriterio = cic.idCriterio
						LEFT JOIN Criterio c on c.id = ic.idCriterio
						LEFT JOIN LugarVerificacion lv on lv.id = ic.idlugarVerificacion		
						WHERE cic.idIndicador = $indicador->id and cic.idEvaluacionCalidad = $id and cic.idEvaluacionCalidadRegistro = $item->id 
						and cic.borradoAl is null and ic.borradoAl is null and c.borradoAl is null and lv.borradoAl is null");
				
				$criterioCalidad[$item->expediente] = $criterios;
				$criterioCalidad["criterios"] = $criterios;
			}
		}
		if($tipo == "Recurso")
		{
			$hallazgo = DB::table('EvaluacionRecurso  AS AS');
			
			$criterioRecurso = DB::select("SELECT cic.aprobado, c.id as idCriterio, ic.idIndicador, lv.id as idlugarVerificacion, c.creadoAl, c.modificadoAl, c.nombre as criterio, lv.nombre as lugarVerificacion FROM EvaluacionRecursoCriterio cic							
						LEFT JOIN IndicadorCriterio ic on ic.idIndicador = cic.idIndicador and ic.idCriterio = cic.idCriterio
						LEFT JOIN Criterio c on c.id = ic.idCriterio
						LEFT JOIN LugarVerificacion lv on lv.id = ic.idlugarVerificacion		
						WHERE cic.idIndicador = $indicador->id and cic.idEvaluacionRecurso = $id and c.borradoAl is null and ic.borradoAl is null and cic.borradoAl is null and lv.borradoAl is null");
		}
		$hallazgo = $hallazgo->LEFTJOIN('Clues AS c', 'c.clues', '=', 'AS.clues')
		->LEFTJOIN('ConeClues AS cc', 'cc.clues', '=', 'AS.clues')
		->LEFTJOIN('Cone AS co', 'co.id', '=', 'cc.idCone')
        ->LEFTJOIN('usuarios AS us', 'us.id', '=', 'AS.idUsuario')
        ->select(array('us.email','AS.firma','AS.fechaEvaluacion', 'AS.cerrado', 'AS.id','AS.clues', 'c.nombre', 'c.domicilio', 'c.codigoPostal', 'c.entidad', 'c.municipio', 'c.localidad', 'c.jurisdiccion', 'c.institucion', 'c.tipoUnidad', 'c.estatus', 'c.estado', 'c.tipologia','co.nombre as nivelCone', 'cc.idCone'))
        ->where('AS.id',"$id")->first();

		$hallazgo->indicador = $indicador;
		if($criterioRecurso)
			$hallazgo->criteriosRecurso = $criterioRecurso;
		if($criterioCalidad)
			$hallazgo->criteriosCalidad = $criterioCalidad;
				
		
		
		if(!$hallazgo)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),404);
		} 
		else 
		{
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$hallazgo),200);
		}
	}
}
?>