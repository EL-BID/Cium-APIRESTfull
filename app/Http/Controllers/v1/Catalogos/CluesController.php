<?php
namespace App\Http\Controllers\v1\Catalogos;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Request;
use Response;
use Input;
use DB; 

use App\Models\Sistema\usuario;

use App\Models\Catalogos\Clues;
use App\Models\Catalogos\ConeClues;
/**
* Controlador Clues
* 
* @package    CIUM API
* @subpackage Controlador
* @author     Eliecer Ramirez Esquinca <ramirez.esquinca@gmail.com>
* @created    2015-07-20
*
* Controlador `Clues`: Catálogo de las unidades médicas
*
*/
class CluesController extends Controller {
	/**
	 * Inicia el contructor para los permisos de visualizacion
	 *	 
	 */
    public function __construct()
    {
        $this->middleware('permisos:GET.436C756573|POST.375657C634|PUT.375657C634|DELETE.375657C634');
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
		$jurisdiccion = isset($datos['jurisdiccion']) ? $datos['jurisdiccion'] : '';
		$cone=ConeClues::all(["clues"]);
		$cones=array();
		$cluesUsuario=$this->permisoZona();
		foreach($cone as $item)
		{
			array_push($cones,$item->clues);
		}
		// Si existe el paarametro pagina en la url devolver las filas según sea el caso
		// si no existe parametros en la url devolver todos las filas de la tabla correspondiente
		// esta opción es para devolver todos los datos cuando la tabla es de tipo catálogo
		if(array_key_exists('pagina',$datos))
		{
			$pagina=$datos['pagina'];
			if(isset($datos['order']))
			{
				if(!$datos['order']=="id")
				$order = $datos['order'];
				else
					$order="clues"; 
				if(strpos(" ".$order,"-"))
					$orden="desc";
				else
					$orden="asc";
				$order=str_replace("-","",$order); 
			}
			else{
				$order="Clues.clues"; $orden="asc";
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
				$clues = Clues::with("coneClues")->whereIn('Clues.clues',$cones)->whereIn('Clues.clues',$cluesUsuario)
				->selectRaw("Clues.clues,Clues.nombre,Clues.domicilio,Clues.codigoPostal,Clues.entidad,Clues.municipio,Clues.localidad,Clues.jurisdiccion,Clues.claveJurisdiccion,Clues.institucion,Clues.tipoUnidad,Clues.estatus,Clues.estado,Clues.tipologia,Cone.nombre as cone, Clues.latitud, Clues.longitud")
				->leftJoin('ConeClues', 'ConeClues.clues', '=', 'Clues.clues')
				->leftJoin('Cone', 'Cone.id', '=', 'ConeClues.idCone')
				->orderBy($order,$orden);
				$search = trim($valor);
				$keyword = $search;
				
				$clues=$clues->whereNested(function($query) use ($keyword)
				{
					
						$query->Where('jurisdiccion', 'LIKE', '%'.$keyword.'%')
							 ->orWhere('municipio', 'LIKE', '%'.$keyword.'%')
							 ->orWhere('localidad', 'LIKE', '%'.$keyword.'%')
							 ->orWhere('Clues.nombre', 'LIKE', '%'.$keyword.'%')
							 ->orWhere('Cone.nombre', 'LIKE', '%'.$keyword.'%')
							 ->orWhere('Clues.clues', 'LIKE', '%'.$keyword.'%'); 
				});
				$total = $clues->get();
				$clues = $clues->skip($pagina-1)->take($datos['limite'])->get();
			}
			else
			{
				$clues = Clues::with("coneClues")
				->selectRaw("Clues.clues,Clues.nombre,Clues.domicilio,Clues.codigoPostal,Clues.entidad,Clues.municipio,Clues.localidad,Clues.jurisdiccion,Clues.claveJurisdiccion,Clues.institucion,Clues.tipoUnidad,Clues.estatus,Clues.estado,Clues.tipologia,Cone.nombre as cone, Clues.latitud, Clues.longitud")
				->leftJoin('ConeClues', 'ConeClues.clues', '=', 'Clues.clues')
				->leftJoin('Cone', 'Cone.id', '=', 'ConeClues.idCone')
				->whereIn('Clues.clues',$cones)->whereIn('Clues.clues',$cluesUsuario)->skip($pagina-1)->take($datos['limite'])->orderBy($order,$orden)->get();
				$total=Clues::whereIn('clues',$cones)->get();
			}
			
		}
		else
		{
			$clues = Clues::with("coneClues")
			->selectRaw("Clues.clues,Clues.nombre,Clues.domicilio,Clues.codigoPostal,Clues.entidad,Clues.municipio,Clues.localidad,Clues.jurisdiccion,Clues.claveJurisdiccion,Clues.institucion,Clues.tipoUnidad,Clues.estatus,Clues.estado,Clues.tipologia,Cone.nombre as cone, Clues.latitud, Clues.longitud")
			->leftJoin('ConeClues', 'ConeClues.clues', '=', 'Clues.clues')
			->leftJoin('Cone', 'Cone.id', '=', 'ConeClues.idCone');
			if($jurisdiccion!="")
				$clues=$clues->where("jurisdiccion",$jurisdiccion);
			if(isset($datos["termino"]))
			{
				$value = $datos["termino"];
				$search = trim($value);
				$keyword = $search;
				
				$clues=$clues->whereNested(function($query) use ($keyword)
				{
					
						$query->Where('jurisdiccion', 'LIKE', '%'.$keyword.'%')
							 ->orWhere('municipio', 'LIKE', '%'.$keyword.'%')
							 ->orWhere('localidad', 'LIKE', '%'.$keyword.'%')
							 ->orWhere('Clues.nombre', 'LIKE', '%'.$keyword.'%')
							 ->orWhere('Clues.clues', 'LIKE', '%'.$keyword.'%'); 
				});
			}
			$clues=$clues->get();
			$total=$clues;
		}
	
		if(!$clues)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),404);
		} 
		else 
		{
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$clues,"total"=>count($total)),200);
			
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
		if(strpos(" ".$id , "CS"))
		{
			$clues = Clues::with("coneClues")->where('clues','=',$id)->first();
			$cone = ConeClues::with("cone")->where('clues','=',$id)->first();
			$clues["cone"]=$cone;
		}
		else 
		{
			$clues = Clues::with("coneClues")->where('jurisdiccion','=',$id)->get();
			
			$clues["cone"]="NADA";
		}		
		
		if(!$clues)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),404);
		} 
		else 
		{
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$clues),200);
		}
	}
	
	/**
	 * Muestra una lista de las clues segun el nivel del usuario 1 = Estatal, 2 = jurisdiccional, 3 = zonal
	 *	 	 
	 * @return Response
	 * <code style="color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
	 * <code> Respuesta Error json(array(clues,status) </code>
	 */
	public function CluesUsuario()
	{
		$datos = Request::all();
		// Obtiene el nivel de cone al que pertenece la clues
		$cone=ConeClues::all(["clues"]);
		$cones=array(); $clues=array();
		foreach($cone as $item)
		{
			array_push($cones,$item->clues);
		}	
		$user = Usuario::where('email', Request::header('X-Usuario'))->first();
		
		$cluesUsuario=[];
		// Valida el nivel del usuario 
		if($user->nivel==2)
		{
			$result = DB::table('UsuarioJurisdiccion')
				->where('idUsuario', $user->id)
				->get();
		
			foreach($result as $item)
			{
				array_push($cluesUsuario,$item->jurisdiccion);
			}
		}
		if($user->nivel==3)
		{
			$result = DB::table('UsuarioZona AS u')
			->leftJoin('Zona AS z', 'z.id', '=', 'u.idZona')
			->leftJoin('ZonaClues AS zc', 'zc.idZona', '=', 'z.id')			
			->select(array('zc.clues'))
			->where('u.idUsuario', $user->id)
			->get();
			
			foreach($result as $item)
			{
				array_push($cluesUsuario,$item->jurisdiccion);
			}
		}
		
		$clues = DB::table('Clues AS c')
			->leftJoin('ConeClues AS cc', 'cc.clues', '=', 'c.clues')
			->leftJoin('Cone AS co', 'co.id', '=', 'cc.idCone')
			->leftJoin('ZonaClues AS zc', 'zc.clues', '=', 'c.clues')
			->leftJoin('Zona AS z', 'z.id', '=', 'zc.idZona')
			->distinct()
            ->select(array('z.nombre as zona','co.nombre as cone','c.clues', 'c.nombre', 'c.domicilio', 'c.codigoPostal', 'c.entidad', 'c.municipio', 'c.localidad', 'c.jurisdiccion','c.claveJurisdiccion', 'c.institucion', 'c.tipoUnidad', 'c.estatus', 'c.estado', 'c.tipologia','c.latitud', 'c.longitud'))
			->whereIn('c.clues',$cones);
			
		if($user->nivel==2)
            $clues = $clues->whereIn('c.jurisdiccion',$cluesUsuario);
		if($user->nivel==3)
            $clues = $clues->whereIn('c.clues',$cluesUsuario);
			
		$value=isset($datos["termino"]) ? $datos["termino"] : '';
		$search = trim($value);
		$keyword = $search;
		
		$clues=$clues->whereNested(function($query) use ($keyword)
		{
			
				$query->Where('c.jurisdiccion', 'LIKE', '%'.$keyword.'%')
					 ->orWhere('c.municipio', 'LIKE', '%'.$keyword.'%')
					 ->orWhere('c.localidad', 'LIKE', '%'.$keyword.'%')
					 ->orWhere('c.nombre', 'LIKE', '%'.$keyword.'%')
					 ->orWhere('z.nombre', 'LIKE', '%'.$keyword.'%')
					 ->orWhere('co.nombre', 'LIKE', '%'.$keyword.'%')
					 ->orWhere('c.nombre', 'LIKE', '%'.$keyword.'%')
					 ->orWhere('c.clues', 'LIKE', '%'.$keyword.'%'); 
		});
		$clues=$clues->get();
			
		if(count($clues)>0)
		{
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$clues,"total"=>count($clues)),200);			
		} 
		else 
		{
			return Response::json(array("data"=>$clues),200);			
		}
	}
	
	/**
	 * Muestra una lista de las clues que pertenezca a la jurisdicción.
	 *
	 * Recibe Request tipo json con la clave jurisdiccion a filtrar
	 * @return Response
	 * <code style="color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado), "total": count(resultado)),status) </code>
	 * <code> Respuesta Error json(array("status": 404, "messages": "No hay resultados"),status) </code>
	 */
	public function jurisdiccion()
	{
		$datos = Request::all();
		$jurisdiccion = isset($datos["jurisdiccion"]) ? $datos["jurisdiccion"]:'';
		$cone=ConeClues::all(["clues"]);
		$cones=array();
		foreach($cone as $item)
		{
			array_push($cones,$item->clues);
		}
		
		$clues = DB::table('Clues')
		->distinct()->select(array('jurisdiccion','entidad'))
		->whereIn('clues',$cones)->get();
		$total=$clues;
		
		if(!$clues)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),404);
		} 
		else 
		{
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$clues,"total"=>count($total)),200);
			
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
}
