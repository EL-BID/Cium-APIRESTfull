<?php
namespace App\Http\Controllers\v1\Formulario;

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

use App\Models\Formulario\FormularioCaptura;
use App\Models\Formulario\FormularioCapturaVariable;
use App\Models\Formulario\FormularioCapturaUsuarios;

/**
* Controlador FormularioCaptura 
* 
* @package    CIUM API
* @subpackage Controlador
* @author     Eliecer Ramirez Esquinca <ramirez.esquinca@gmail.com>
* @created    2015-07-20
*
* Controlador `Calidad`: Proporciona los servicios para el manejos de los datos de la evaluacion
*
*/
class FormularioCapturaController extends Controller 
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
				$order="clave"; $orden="desc";
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
				$evaluacion = FormularioCaptura::orderBy($order,$orden);
				
				$search = trim($valor);
				$keyword = $search;
				$evaluacion=$evaluacion->whereNested(function($query) use ($keyword)
				{
					
					$query->Where('nombre', 'LIKE', '%'.$keyword.'%')
						 ->orWhere('clave', 'LIKE', '%'.$keyword.'%'); 
				});
				$total = $evaluacion->get();
				$evaluacion = $evaluacion->skip($pagina-1)->take($datos['limite'])->get();
				
				
			}
			else
			{
				$evaluacion = FormularioCaptura::skip($pagina-1)->take($datos['limite'])
				->orderBy($order,$orden)->get();
				$total=FormularioCaptura::get();
			}
			
		}
		else
		{
			$evaluacion = FormularioCaptura::get();
			$total=$evaluacion;
		}

		if(!$evaluacion)
		{
			return Response::json(array('status'=> 404,"messages"=>'No hay resultados'),404);
		} 
		else 
		{
			return Response::json(array("status"=>200,"messages"=>"Operación realizada con exito","data"=>$evaluacion,"total"=>count($total)),200);
			
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
            $data = new FormularioCaptura;
            $success = $this->campos($datos, $data);

        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(["status" => 500, 'error' => $e->getMessage()], 500);
        } 
        if ($success){
            DB::commit();
            return Response::json(array("status" => 201,"messages" => "Creado","data" => $data), 201);
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
        	$data = FormularioCaptura::find($id);

            if(!$data){
                return Response::json(['error' => "No se encuentra el recurso que esta buscando."], HttpResponse::HTTP_NOT_FOUND);
            }
            
            $success = $this->campos($datos, $data);

        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(["status" => 500, 'error' => $e->getMessage()], 500);
        } 
        if($success){
			DB::commit();
			return Response::json(array("status" => 200, "messages" => "Operación realizada con exito", "data" => $data), 200);
		} 
		else {
			DB::rollback();
			return Response::json(array("status" => 304, "messages" => "No modificado"),200);
		}
	}

	public function campos($datos, $data){
		$success = false;

        $data->nombre 		= property_exists($datos, "nombre") ? $datos->nombre : $data->nombre;	
        $data->clave 		= property_exists($datos, "clave")  ? $datos->clave  : $data->clave;	
        if ($data->save()) {  

        	if(property_exists($datos, "formulario_captura_variable")){
        		$fcv = array_filter($datos->formulario_captura_variable, function($v){return $v !== null;});
        		FormularioCapturaVariable::where("idFormularioCaptura", $data->id)->delete();
        		foreach ($fcv as $key => $value) {
        			$value = (object) $value;
        			if($value != null){
        				DB::update("update FormularioCapturaVariable set borradoAl = null where idFormularioCaptura = $data->id and nombre = '$value->nombre' ");
        				$item = FormularioCapturaVariable::where("idFormularioCaptura", $data->id)->where("nombre", $value->nombre)->first();

        				if(!$item)
            				$item = new FormularioCapturaVariable;

            			$item->idFormularioCaptura	= $data->id;
            			$item->nombre          		= $value->nombre;

            			if($item->save()){ 
            				if(property_exists($value, "formulario_captura_usuarios")){
				        		$fcv = array_filter($value->formulario_captura_usuarios, function($v){return $v !== null;});

				        		DB::table("FormularioCapturaUsuarios")->where("idFormularioCapturaVariable", $item->id)->delete();
				        		foreach ($fcv as $k => $val) {
				        			
				        			if($val != null){
				        				DB::table('FormularioCapturaUsuarios')->insert(	
				        					array(
				        						'idFormularioCapturaVariable' => "$item->id", 
				        						'idUsuarios' => $val
				        					) 
				        				);
				            			
				            		}
				        		}
				        	}  
            			}
            		}
        		}
        	}
        	     	
			$success = true;
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
		$data = FormularioCaptura::with('FormularioCapturaVariable')->find($id);			
		
		if(!$data){
			return Response::json(array("status"=> 404,"messages" => "No hay resultados"),404);
		} 
		else {	
			foreach ($data->FormularioCapturaVariable as $key => $value) {
				$variable = DB::table('FormularioCapturaUsuarios')->where('idFormularioCapturaVariable', $value->id)->get();
				$usuarios = [];
				foreach ($variable as $k => $v) {
					$usuarios[] = $v->idUsuarios;				
				}
				$value->formulario_captura_usuarios = $usuarios;		
			}

			return Response::json(array("status" => 200, "messages" => "Operación realizada con exito", "data" => $data), 200);
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
			$evaluacion = FormularioCaptura::where("id",$id)->delete();
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
	
}
?>