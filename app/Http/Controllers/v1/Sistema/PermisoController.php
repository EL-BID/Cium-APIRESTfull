<?php 
/**
 * Controlador PermisoController
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

use App\Models\Sistema\Permiso as Permiso;

use Input, Response,  Validator;
use Illuminate\Http\Response as HttpResponse;

/**
*
* Controlador `PermisoController`: lista los permisos diponibles en el sistema
*
*/
class PermisoController extends Controller {

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
		$query = Input::get('query');
		
		if($query){
			$permisos = Permiso::where(function($query_where)use($query){
				$query_where->where('clave','LIKE','%'.$query.'%')
							->orWhere('grupo','LIKE','%'.$query.'%')
							->orWhere('descripcion','LIKE','%'.$query.'%');
			})->get();
		} else {
			$permisos = Permiso::all();
		}
		
		return Response::json(['data'=>$permisos],200);
	}
}