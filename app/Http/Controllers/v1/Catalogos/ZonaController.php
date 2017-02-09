<?php
namespace App\Http\Controllers\v1\Catalogos;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Request;
use Response;
use Input;
use DB;
use App\Models\Catalogos\Zona;
/**
* Controlador Zona
* 
* @package    CIUM API
* @subpackage Controlador
* @author     Eliecer Ramirez Esquinca <ramirez.esquinca@gmail.com>
* @created    2015-07-20
*
* Controlador `Zona`: Manejo del catálogo zonas o equipos 
*
*/
class ZonaController extends Controller {
	/**
	 * Inicia el contructor para los permisos de visualizacion
	 *	 
	 */
    public function __construct()
    {
        $this->middleware('permisos:GET.AC7A6F6E61|POST.616E6F7A34|PUT.616E6F7A34|DELETE.616E6F7A34');
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
				$zona = Zona::orderBy($order,$orden);
				$search = trim($valor);
				$keyword = $search;
				
				$zona=$zona->whereNested(function($query) use ($keyword)
				{
					
						$query->Where('nombre', 'LIKE', '%'.$keyword.'%'); 
				});
				
				$total= $zona->get();
				$zona = $zona->skip($pagina-1)->take($datos['limite'])->get();
			}
			else
			{
				$zona = Zona::skip($pagina-1)->take($datos['limite'])->orderBy($order,$orden)->get();
				$total=Zona::all();
			}
			
		}
		else
		{
			if(array_key_exists('jurisdiccion',$datos))
			{
				$zona = DB::table('ZonaClues AS u')
					->leftJoin('Zona AS z', 'z.id', '=', 'u.idZona')
					->distinct()
					->select(array("z.id","z.nombre"))
					->where('u.jurisdiccion',$datos["jurisdiccion"])->get();
			}
			else
				$zona = Zona::all();
			$total=$zona;
		}
		
		if(!$zona)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),404);
		} 
		else 
		{
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$zona,"total"=>count($total)),200);
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
		$rules = [
			'nombre' => 'required|min:3|max:150',
			'ZonaClues' =>  'array'
		];
		$v = \Validator::make(Request::json()->all(), $rules );

		if ($v->fails())
		{
			return Response::json($v->errors());
		}
		$datos = Input::json();
		$success = false;
        DB::beginTransaction();
        try 
		{
            $zona = new Zona;
            $zona->nombre = $datos->get('nombre');

            if ($zona->save()) 
			{
				// guarda las unidades medicas que corresponda al equipo zonal creado
				DB::table('ZonaClues')->where('idZona', "$zona->id")->delete();
				
				foreach($datos->get('ZonaClues') as $clues)
				{
					if($clues)								
						DB::table('ZonaClues')->insert(	array('idZona' => "$zona->id", 'clues' => $clues['clues'], 'jurisdiccion' => $clues['jurisdiccion']) );					
				}		
				if(array_key_exists('all',$datos))
					if($datos->get('all'))
						DB::table('ZonaClues')->insert(	array('idZona' => "$zona->id", 'clues' => $datos->get('all')) );
					
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
			return Response::json(array("status"=>201,"messages"=>"Creado","data"=>$zona),201);
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
		$zona = Zona::find($id);

		if(!$zona)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),404);
		} 
		else 
		{
			$zona['ZonaClues'] = DB::table('ZonaClues AS u')
			->leftJoin('Clues AS c', 'c.clues', '=', 'u.clues')
			->select('*')
			->where('idZona',$id)->get();
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$zona),200);
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
		$rules = [
			'nombre' => 'required|min:3|max:150',
			'ZonaClues' =>  'array'
		];
		$v = \Validator::make(Request::json()->all(), $rules );

		if ($v->fails())
		{
			return Response::json($v->errors());
		}
		$datos = Input::json(); 
		$success = false;
        DB::beginTransaction();
        try 
		{
			$zona = Zona::find($id);
			$zona->nombre = $datos->get('nombre');

            if ($zona->save())
			{
				DB::table('ZonaClues')->where('idZona', "$zona->id")->delete();
				
				foreach($datos->get('ZonaClues') as $clues)
				{
					if($clues)								
						DB::table('ZonaClues')->insert(	array('idZona' => "$zona->id", 'clues' => $clues['clues'], 'jurisdiccion' => $clues['jurisdiccion']) );					
				}	
				if(array_key_exists('all',$datos))
					if($datos->get('all'))
						DB::table('ZonaClues')->insert(	array('idZona' => "$zona->id", 'clues' => $datos->get('all')) ); 
                
				$success = true;
			}
		} 
		catch (\Exception $e) 
		{throw $e;
        }
        if ($success)
		{
			DB::commit();
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$zona),200);
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
			$zona = Zona::find($id);
			$zona->delete();
			$success=true;
		} 
		catch (\Exception $e) 
		{
			throw $e;
        }
        if ($success)
		{
			DB::commit();
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$zona),200);
		} 
		else 
		{
			DB::rollback();
			return Response::json(array('status'=> 500,"messages"=>'Error interno del servidor'),500);
		}
	}

}
