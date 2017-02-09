<?php
namespace App\Http\Controllers\v1\Catalogos;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Request;
use Response;
use Input;
use DB; 
use Event;
use App\Models\Catalogos\Indicador;
use App\Models\Catalogos\IndicadorAlerta;
use App\Models\Catalogos\IndicadorValidacion;
use App\Models\Catalogos\IndicadorValidacionPregunta;
/**
* Controlador Indicador
* 
* @package    CIUM API
* @subpackage Controlador
* @author     Eliecer Ramirez Esquinca <ramirez.esquinca@gmail.com>
* @created    2015-07-20
*
* Controlador `Indicador`: Manejo del catálogo de las alertas para cada indicador
*
*/
class IndicadorController extends Controller {
	/**
	 * Inicia el contructor para los permisos de visualizacion
	 *	 
	 */
    public function __construct()
    {
        $this->middleware('permisos:GET.496E646963|POST.369646E694|PUT.369646E694|DELETE.369646E694');
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
				$indicador = Indicador::with("IndicadorAlertas", "IndicadorValidaciones", "IndicadorPreguntas")->orderBy("codigo","ASC");
				
				$search = trim($valor);
				$keyword = $search;
				$indicador=$indicador->whereNested(function($query) use ($keyword)
				{
					
						$query->Where('nombre', 'LIKE', '%'.$keyword.'%')
							 ->orWhere('codigo', 'LIKE', '%'.$keyword.'%')
							 ->orWhere('categoria', 'LIKE', '%'.$keyword.'%'); 
				});
				$indicador->orderBy("codigo","asc");
				$total = $indicador->get();
				$indicador = $indicador->skip($pagina-1)->take($datos['limite'])->get();
			}
			else
			{
				$indicador = Indicador::with("IndicadorAlertas", "IndicadorValidaciones", "IndicadorPreguntas")->skip($pagina-1)->take($datos['limite'])->orderBy("codigo","ASC")->get();
				$total=Indicador::all();
			}
			
		}
		else
		{
			$indicador = Indicador::with("IndicadorAlertas", "IndicadorValidaciones", "IndicadorPreguntas");
			if(array_key_exists('categoria',$datos)){
				$indicador = $indicador->where("categoria",$datos["categoria"]);
			}
			$indicador->orderBy("codigo","asc");
			$indicador = $indicador->get();
			$total=$indicador;
		}

		if(!$indicador)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),404);
		} 
		else 
		{
			if(array_key_exists('cone',$datos)){
				
				$cone = $datos["cone"];
				$temp = array();
				foreach($indicador as $item){
					$criterio = DB::select("SELECT c.id FROM ConeIndicadorCriterio cic							
					left join IndicadorCriterio ic on ic.id = cic.idIndicadorCriterio
					left join Criterio c on c.id = ic.idCriterio		
					WHERE cic.idCone = $cone and ic.idIndicador = ".$item->id."
					and c.borradoAl is null and ic.borradoAl is null and cic.borradoAl is null");
					if($criterio){
						array_push($temp,$item);
					}
				}
				$indicador = $temp;
			}
		
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$indicador,"total"=>count($total)),200);			
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
			'nombre' => 'required|min:3|max:250',
			'color' =>  'required',
			'codigo' =>  'required',
			'categoria' =>  'required',
			'indicador_alertas' =>  'array'
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
			$color = $datos->get('color');
			
            $indicador = new Indicador;
            $indicador->codigo = $datos->get('codigo');
			$indicador->nombre = $datos->get('nombre');
			$indicador->categoria = $datos->get('categoria');
			$indicador->indicacion = $datos->get('indicacion');
			$indicador->color = $color;

            if ($indicador->save())
			{	
				// guardar la semaforización para mostrar en los reportes y en el dashboard los colores 
				// correspondientes a los valores que tome el indicador 
				$alertas = $datos->get('indicador_alertas');
				$pregunt = $datos->get('indicador_preguntas');
				$validar = $datos->get('indicador_validaciones');				
				
				for($i=0;$i<count($alertas);$i++)
				{
					$indicador_alertas =  new IndicadorAlerta;
					
					$indicador_alertas->minimo = $alertas[$i]["minimo"];
					$indicador_alertas->maximo = $alertas[$i]["maximo"];
					$indicador_alertas->idAlerta = $alertas[$i]["idAlerta"];
					$indicador_alertas->idIndicador = $indicador->id;
					$indicador_alertas->save();									
				}
				
				
				for($i=0;$i<count($pregunt);$i++)
				{					
					$indicador_preguntas =  new IndicadorValidacionPregunta;
					
					$indicador_preguntas->id = $pregunt[$i]["id"];
					$indicador_preguntas->nombre = $pregunt[$i]["nombre"];
					$indicador_preguntas->tipo = $pregunt[$i]["tipo"];
					$indicador_preguntas->constante = $pregunt[$i]["constante"];
					$indicador_preguntas->valorConstante = $pregunt[$i]["valorConstante"];
					$indicador_preguntas->fechaSistema = $pregunt[$i]["fechaSistema"];
					$indicador_preguntas->idIndicador = $indicador->id;
					$indicador_preguntas->save();									
				}
				
				for($i=0;$i<count($validar);$i++)
				{
					$indicador_validaciones =  new IndicadorValidacion;
					
					$indicador_validaciones->pregunta1 = $validar[$i]["pregunta1"];
					$indicador_validaciones->operadorAritmetico = $validar[$i]["operadorAritmetico"];
					$indicador_validaciones->pregunta2 = $validar[$i]["pregunta2"];
					$indicador_validaciones->unidadMedida = $validar[$i]["unidadMedida"];
					$indicador_validaciones->operadorLogico = $validar[$i]["operadorLogico"];
					$indicador_validaciones->valorComparativo = $validar[$i]["valorComparativo"];
					$indicador_validaciones->idIndicador = $indicador->id;
					$indicador_validaciones->save();									
				}				
				
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
			return Response::json(array("status"=>201,"messages"=>"Creado","data"=>$indicador),201);
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
		$indicador = Indicador::with("IndicadorAlertas", "IndicadorValidaciones", "IndicadorPreguntas")->find($id);

		if(!$indicador)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),404);
		} 
		else 
		{
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$indicador),200);
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
			'nombre' => 'required|min:3|max:250',
			'color' =>  'required',
			'codigo' =>  'required',
			'categoria' =>  'required',
			'indicador_alertas' =>  'array'
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
			$color = $datos->get('color');
			
			$indicador = Indicador::find($id);
			$indicador->codigo = $datos->get('codigo');
			$indicador->nombre = $datos->get('nombre');
			$indicador->categoria = $datos->get('categoria');
			$indicador->indicacion = $datos->get('indicacion');
			$indicador->color = $color;
			
            if ($indicador->save())
			{					
				$alertas = $datos->get('indicador_alertas');
				$pregunt = $datos->get('indicador_preguntas');
				$validar = $datos->get('indicador_validaciones');				

				for($i=0;$i<count($alertas);$i++)
				{
					$indicador_alertas = IndicadorAlerta::where('idIndicador',$indicador->id)->where('idAlerta',$alertas[$i]["idAlerta"])->first();
					if(!$indicador_alertas)						
						$indicador_alertas =  new IndicadorAlerta;
					
					$indicador_alertas->minimo = $alertas[$i]["minimo"];
					$indicador_alertas->maximo = $alertas[$i]["maximo"];
					$indicador_alertas->idAlerta = $alertas[$i]["idAlerta"];
					$indicador_alertas->idIndicador = $indicador->id;
					$indicador_alertas->save();									
				}
				
				for($i=0;$i<count($pregunt);$i++)
				{
					$indicador_preguntas = IndicadorValidacionPregunta::where('idIndicador',$indicador->id)->where('id',$pregunt[$i]["id"])->first();
					if(!$indicador_preguntas)						
						$indicador_preguntas =  new IndicadorValidacionPregunta;
					
					$indicador_preguntas->id = $pregunt[$i]["id"];
					$indicador_preguntas->nombre = $pregunt[$i]["nombre"];
					$indicador_preguntas->tipo = $pregunt[$i]["tipo"];
					$indicador_preguntas->constante = $pregunt[$i]["constante"];
					$indicador_preguntas->valorConstante = $pregunt[$i]["valorConstante"];
					$indicador_preguntas->fechaSistema = $pregunt[$i]["fechaSistema"];
					$indicador_preguntas->idIndicador = $indicador->id;
					$indicador_preguntas->save();									
				}
				
				for($i=0;$i<count($validar);$i++)
				{					
					$indicador_validaciones = IndicadorValidacion::where('idIndicador',$indicador->id)->where('id',$validar[$i]["id"])->first();
					if(!$indicador_validaciones)						
						$indicador_validaciones =  new IndicadorValidacion;
					
					$indicador_validaciones->pregunta1 = $validar[$i]["pregunta1"];
					$indicador_validaciones->operadorAritmetico = $validar[$i]["operadorAritmetico"];
					$indicador_validaciones->pregunta2 = $validar[$i]["pregunta2"];
					$indicador_validaciones->unidadMedida = $validar[$i]["unidadMedida"];
					$indicador_validaciones->operadorLogico = $validar[$i]["operadorLogico"];
					$indicador_validaciones->valorComparativo = $validar[$i]["valorComparativo"];
					$indicador_validaciones->idIndicador = $indicador->id;
					$indicador_validaciones->save();									
				}							
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
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$indicador),200);
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
			$indicador = Indicador::find($id);
			$indicador->delete();
			$success=true;
		} 
		catch (\Exception $e) 
		{
			throw $e;
        }
        if ($success)
		{
			DB::commit();
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$indicador),200);
		} 
		else 
		{
			DB::rollback();
			return Response::json(array('status'=> 500,"messages"=>'Error interno del servidor'),500);
		}
	}

}
