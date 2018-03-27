<?php
namespace App\Http\Controllers\v1\Formulario;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Response;
use Input;
use DB;

use Request;

use App\Models\Sistema\Usuario;

use App\Models\Formulario\FormularioCaptura;
use App\Models\Formulario\FormularioCapturaValor;
use App\Models\Formulario\FormularioCapturaVariable;
use App\Models\Formulario\FormularioCapturaUsuarios;

/**
* Controlador FormularioCapturaValor 
* 
* @package    CIUM API
* @subpackage Controlador
* @author     Eliecer Ramirez Esquinca <ramirez.esquinca@gmail.com>
* @created    2015-07-20
*
* Controlador `Calidad`: Proporciona los servicios para el manejos de los datos de la evaluacion
*
*/
class FormularioCapturaValorController extends Controller 
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
		
		$permisos = $this->permisoFormularioCaptura();
				
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
				$order="anio"; $orden="desc";
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
				$evaluacion = DB::table("FormularioCapturaValor as fcval")->distinct()
				->select("fcval.*","fcv.nombre as variable", "fc.clave", "fc.nombre as indicador")->orderBy($order,$orden)
				->leftJoin("FormularioCapturaVariable as fcv", "fcv.id", "=", "fcval.idFormularioCapturaVariable")
				->leftJoin("FormularioCapturaUsuarios as fcu", "fcu.idFormularioCapturaVariable", "=", "fcv.id")				
				->leftJoin("FormularioCaptura as fc", "fc.id", "=", "fcv.idFormularioCaptura")
				->whereIn("fc.id",$permisos["indicadores"])
				->whereIn("fcv.id",$permisos["variables"]);
				
				$search = trim($valor);
				$keyword = $search;
				$evaluacion=$evaluacion->whereNested(function($query) use ($keyword)
				{
					
					$query->where('fcval.anio', 'LIKE', '%'.$keyword.'%')
					->where('fcval.mes', 'LIKE', '%'.$keyword.'%')
					->where('fcv.nombre', 'LIKE', '%'.$keyword.'%')
					->where('fc.clave', 'LIKE', '%'.$keyword.'%')
					->where('fc.nombre', 'LIKE', '%'.$keyword.'%'); 
				});
				$total = $evaluacion->get();
				$evaluacion = $evaluacion->get();								
			}
			else
			{
				$evaluacion = DB::table("FormularioCapturaValor as fcval")->distinct()
				->select("fcval.*","fcv.nombre as variable", "fc.clave", "fc.nombre as indicador")->orderBy($order,$orden)
				->leftJoin("FormularioCapturaVariable as fcv", "fcv.id", "=", "fcval.idFormularioCapturaVariable")
				->leftJoin("FormularioCapturaUsuarios as fcu", "fcu.idFormularioCapturaVariable", "=", "fcv.id")				
				->leftJoin("FormularioCaptura as fc", "fc.id", "=", "fcv.idFormularioCaptura")
				->whereIn("fc.id",$permisos["indicadores"])
				->whereIn("fcv.id",$permisos["variables"])
				->orderBy($order,$orden)->get();

				$total=FormularioCapturaValor::get();
			}
			
		}
		else
		{
			$evaluacion = DB::table("FormularioCapturaValor as fcval")->distinct()
				->select("fcval.*","fcv.nombre as variable", "fc.clave", "fc.nombre as indicador")->orderBy($order,$orden)
				->leftJoin("FormularioCapturaVariable as fcv", "fcv.id", "=", "fcval.idFormularioCapturaVariable")
				->leftJoin("FormularioCapturaUsuarios as fcu", "fcu.idFormularioCapturaVariable", "=", "fcv.id")				
				->leftJoin("FormularioCaptura as fc", "fc.id", "=", "fcv.idFormularioCaptura")
				->whereIn("fc.id",$permisos["indicadores"])
				->whereIn("fcv.id",$permisos["variables"])
				->get();
			$total=$evaluacion;
		}

		if(!$evaluacion)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),404);
		} 
		else 
		{
			$data = [];
			foreach ($evaluacion as $key => $value) {
				$value->indicador = strtoupper("($value->clave) ".$value->indicador);
				if(!array_key_exists($value->anio, $data)){
					$data[$value->anio] = [];
				}

				if(!array_key_exists($value->indicador, $data[$value->anio])){
					$data[$value->anio][$value->indicador] = [];
				}

				if(!array_key_exists($value->variable, $data[$value->anio][$value->indicador])){
					$data[$value->anio][$value->indicador][$value->variable] = [];
				}

				$data[$value->anio][$value->indicador][$value->variable][$value->mes] = $value->valor;
			}
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$data,"total"=>count($total)),200);			
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
	public function store(){			
		$datos = (object) Input::json()->all();	
		$success = false;

        DB::beginTransaction();
        try{
            
            $success = $this->campos($datos);

        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(["status" => 500, 'error' => $e->getMessage()], 500);
        } 
        if ($success){
            DB::commit();
            return Response::json(array("status" => 201,"messages" => "Creado","data" => $datos), 201);
        } 
        else{
            DB::rollback();
            return Response::json(array("status" => 409,"messages" => "Conflicto"), 200);
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
	public function update($id){
		
		$datos = (object) Input::json()->all();		
		$success = false;
        
        DB::beginTransaction();
        try{
        	            
            $success = $this->campos($datos);

        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(["status" => 500, 'error' => $e->getMessage()], 500);
        } 
        if($success){
			DB::commit();
			return Response::json(array("status" => 200, "messages" => "Operación realizada con exito", "data" => $datos), 200);
		} 
		else {
			DB::rollback();
			return Response::json(array("status" => 304, "messages" => "No modificado"),200);
		}
	}

	public function campos($datos){
		$success = false;
		$permisos = $this->permisoFormularioCaptura();		
		foreach ($datos as $key => $variables) {			
			foreach ($variables as $kv => $valores) {
				foreach ($valores as $key => $val) {
					if(is_array($val))
						$val = (object) $val;

					$var = FormularioCapturaValor::where("idUsuarios", $permisos["usuario"]->id)
					->where("idFormularioCapturaVariable", $val->idFormularioCapturaVariable)
					->where("anio", $val->anio)
					->where("mes", $val->mes)					
					->first();
					if(property_exists($val, "valor")){
				        $var->valor = $val->valor;	
				    }

			        if ($var->save()) {      	
						$success = true;
					}  
				}
			}
		}
        
		return $success;     						
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
	public function show($id){
		$permisos = $this->permisoFormularioCaptura();
		$data = [];
		$indicadores = DB::table("FormularioCaptura")->whereIn("id",$permisos["indicadores"])->get();
		
		foreach ($indicadores as $key => $value) {
			$variables = DB::table("FormularioCapturaVariable")->where("idFormularioCaptura", $value->id)->whereIn("id",$permisos["variables"])->get();
			foreach ($variables as $kv => $var) {
				$valores = DB::table("FormularioCapturaValor")->where("idUsuarios", $permisos["usuario"]->id)->where("idFormularioCapturaVariable", $var->id)->where("anio", $id)->get();
				if($valores){
					foreach ($valores as $key => $val) {
						if(!array_key_exists($value->nombre, $data)){
							$data[$value->nombre] = [];
						}

						if(!array_key_exists($var->nombre, $data[$value->nombre])){
							$data[$value->nombre][$var->nombre] = [];
						}

						$data[$value->nombre][$var->nombre][$val->mes] = $val;
					}
				}else{
					$meses = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
					foreach ($meses as $key => $val) {
						$valor = new FormularioCapturaValor;

						$valor->idFormularioCapturaVariable = $var->id;
						$valor->anio = $id;
						$valor->mes = $val;
						$valor->idUSuarios = $permisos["usuario"]->id;					
						$valor->save();

						if(!array_key_exists($value->nombre, $data)){
							$data[$value->nombre] = [];
						}

						if(!array_key_exists($var->nombre, $data[$value->nombre])){
							$data[$value->nombre][$var->nombre] = [];
						}

						$data[$value->nombre][$var->nombre][$val] = $valor;

					}					
				}
			}
		}	
		
		if(!$data){
			return Response::json(array("status"=> 404,"messages" => "No hay resultados"),404);
		} 
		else {							
			return Response::json(array("status" => 200, "messages" => "Operación realizada con exito", "data" => $data), 200);
		}
	}

	public function anio($id){
		$anios = DB::table("FormularioCapturaValor")->where("anio", $id)->first();
		if(!$anios){
			$permisos = $this->permisoFormularioCaptura();
			$data = []; $meses = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
			$indicadores = DB::table("FormularioCaptura")->whereIn("id",$permisos["indicadores"])->get();
			foreach ($indicadores as $key => $value) {
				$variables = DB::table("FormularioCapturaVariable")->where("idFormularioCaptura", $value->id)->whereIn("id",$permisos["variables"])->get();
				foreach ($variables as $kv => $var) {
					
					foreach ($meses as $key => $val) {
						$valor = new FormularioCapturaValor;

						$valor->idFormularioCapturaVariable = $var->id;
						$valor->anio = $id;
						$valor->mes = $val;
						$valor->idUSuarios = $permisos["usuario"]->id;					
						$valor->save();

						if(!array_key_exists($value->nombre, $data)){
							$data[$value->nombre] = [];
						}

						if(!array_key_exists($var->nombre, $data[$value->nombre])){
							$data[$value->nombre][$var->nombre] = [];
						}

						$data[$value->nombre][$var->nombre][$val] = $valor;

					}
				}
			}	

			return Response::json(array("status"=> 200,"messages" => "Nuevo", "data" => $data),200);
		} else{
			return Response::json(array("status" => 400, "messages" => "Este año ya tiene datos"), 200);
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
			$evaluacion = FormularioCapturaValor::where("id",$id)->delete();
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

	
	public function permisoFormularioCaptura()
	{
		$user = Usuario::where('email', Request::header('X-Usuario'))->first();
		$fcu = FormularioCapturaUsuarios::where("idUsuarios", $user->id)->get();
		
		$variables = [];
		foreach ($fcu as $key => $value) {
			if(!in_array($value->idFormularioCapturaVariable, $variables)){
				$variables[] = $value->idFormularioCapturaVariable;
			}
		}
		
		$fcv = FormularioCapturaVariable::whereIn("id", $variables)->get();

		$indicadores = [];
		foreach ($fcv as $key => $value) {
			if(!in_array($value->idFormularioCaptura, $indicadores)){
				$indicadores[] = $value->idFormularioCaptura;
			}
		}
		return array("usuario" => $user, "indicadores" => $indicadores, "variables" => $variables);
	}
}
?>