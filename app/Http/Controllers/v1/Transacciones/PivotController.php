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

use App\Models\Transacciones\EvaluacionRecurso;
use App\Models\Transacciones\EvaluacionCalidad;

use App\Jobs\ReporteRecurso;
use App\Jobs\ReporteCalidad;
use App\Jobs\ReporteHallazgo;
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
class PivotController extends Controller 
{
	/**
	 * Inicia el contructor para los permisos de visualizacion
	 *	 
	 */
    public function __construct()
    {
        $this->middleware('permisos:GET.4441534842|POST.4441534842|PUT.4441534842|DELETE.4441534842');
	}
	
	public function ResetearReportes(){
		DB::select("TRUNCATE TABLE ReporteRecurso");
		DB::select("TRUNCATE TABLE ReporteCalidad");
		DB::select("TRUNCATE TABLE ReporteHallazgos");

		$variable = EvaluacionRecurso::all();
		foreach ($variable as $key => $value) {
			$this->dispatch(new ReporteRecurso($value)); 
			$this->dispatch(new ReporteHallazgo($value));
		}

		$variable = EvaluacionCalidad::all();
		foreach ($variable as $key => $value) {
			$this->dispatch(new ReporteCalidad($value)); 
			$this->dispatch(new ReporteHallazgo($value));
		}
		return Response::json(array("status" => 200, "messages"=>"Operación realizada con exito"),200);			
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
	public function Recurso(){
		/**
		 * @var json $filtro contiene el json de los parametros
		 * @var string $datos recibe todos los parametros
		 * @var string $cluesUsuario contiene las clues por permiso del usuario
		 * @var string $parametro contiene los filtros procesados en query
		 * @var string $nivel muestra el dato de la etiqueta en el grafico
		 */
		DB::statement("SET lc_time_names = 'es_MX'");

		$datos = Request::all();
		
		$data = DB::table('ReporteRecurso')
		->select('cone', 'jurisdiccion', 'municipio', 'zona', 'clues', 'nombre AS unidad_medica', 'indicador', 'aprobado', 'noAprobado', 'noAplica', 'anio', 'mes', 'total AS total_criterios', 'promedio', 'estricto_pasa')->get();
		if(!$data)
		{
			return Response::json(array("status"=> 404,"messages"=>"No hay resultados"),204);
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
	public function Calidad(){
		DB::statement("SET lc_time_names = 'es_MX'");

		$datos = Request::all();
		
		$data = DB::table('ReporteCalidad')
		->select('cone', 'jurisdiccion', 'municipio', 'zona', 'clues', 'nombre AS unidad_medica', 'indicador', 'total_cri', 'promedio_cri', 'cumple_cri', 'aprobado_cri', 'noAprobado_cri', 'noAplica_cri', 'total_exp', 'promedio_exp', 'cumple_exp', 'aprobado_exp', 'noAprobado_exp', 'anio', 'mes' )->get();
		if(!$data)
		{
			return Response::json(array("status"=> 404,"messages"=>"No hay resultados"),204);
		} 
		else 
		{
			return Response::json(array("status" => 200, "messages"=>"Operación realizada con exito", 
			"data" => $data, 
			"total" => count($data)),200);			
		}
	}
}
?>