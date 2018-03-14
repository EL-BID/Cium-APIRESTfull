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

use App\Models\Resincronizacion\EvaluacionRecursoResincronizacion;
use App\Models\Resincronizacion\EvaluacionCalidadResincronizacion;

use App\Jobs\ResincronizacionRecurso;
use App\Jobs\ResincronizacionCalidad;
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
class ResetearResincronizacionController extends Controller 
{	
	/**
	 * Inicia el contructor para los permisos de visualizacion
	 *	 
	 */
    public function __construct()
    {
        $this->middleware('permisos:GET.7265637572');
    }

	public function index()
	{
		$datos = Request::all();
				
		$variable = EvaluacionRecursoResincronizacion::all();
		foreach ($variable as $key => $value) {
			$this->dispatch(new ResincronizacionRecurso($value)); 
		}

		$variable = EvaluacionCalidadResincronizacion::all();
		foreach ($variable as $key => $value) {
			$this->dispatch(new ResincronizacionCalidad($value)); 
		}
		return Response::json(array("status" => 200, "messages"=>"Operación realizada con exito"),200);			
	}
}
?>