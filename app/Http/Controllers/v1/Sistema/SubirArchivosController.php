<?php
/**
 * Controlador SubirArchivosController
 * 
 * @package    plataforma API
 * @subpackage Controlador
 * @author     Eliecer Ramirez Esquinca <ramirez.esquinca@gmail.com>
 * @created    2016-06-01
 */
namespace App\Http\Controllers\v1\Sistema;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Request;
use Response;
use Input;
use URL;

/**
*
* Controlador `SubirArchivosController`: Sube, muestra, elimina un archivo en el servidor
*
*/
class SubirArchivosController extends Controller{
	
	/**
	 * Valida que el archivo corresponda segun los parametros del desarrollador
	 *
	 * <h4>Request</h4>
	 * Recibe un input request tipo json de los datos a subir, con la configuracion del desarrollador tipo de extension y tamaño maximo
	 *
	 * @return Response
	 * <code style="color:green"> r = 1 ok</code>
	 * <code> r = 2 No tiene extensión correcta</code>
	 * <code> r = 3 Tamaño permintido no vaido</code>
	 * <code> r = 4 Agun error ocurrio al mover el archivo a temporal</code>
	 */
	public function subir(){
		$ext = ""; $max = ""; $nom = "";
		if(isset($_REQUEST['maximo'])){
			$max = $_REQUEST['maximo'];
		}
		
		if(isset($_REQUEST['extension'])){
			$ext = $_REQUEST['extension'];
		}

		if(isset($_REQUEST['nombre'])){
			$nom = $_REQUEST['nombre'];
		}
			

		@$ruta=$_REQUEST['ruta'];
		@$archivo = $_FILES[$_REQUEST["file"]]; 
		@$extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
		
		if($ext!=""){
			if(!stripos(" ".$ext,$extension)){
				return Response::json((array("r"=>2,"msg"=>"el archivo No tiene la extension correcta")));
				die();
			}
		}
		if($max!=""){
			if ($archivo["size"] > ($max*1024)*1025){
				return Response::json((array("r"=>3,"msg"=>"el archivo Exede el limite")));
				die();
			}
		}
				
		$time = time();
		$name = $nom."_".$time.".$extension";
		
		if($ruta!=""){
			$nombre=$ruta."/".$name;
		}
		if (!file_exists(public_path()."/adjunto/".$ruta)){
			mkdir(public_path()."/adjunto/".$ruta, null, true);				
		}
		if (move_uploaded_file($archivo['tmp_name'], public_path()."/adjunto/$nombre")){			
			return Response::json(array("r"=>1,"msg"=>$name));
		} 
		else {
			return Response::json(array("r"=>4,"msg"=>$name));
		}
	}
	/**
	 * Muestra el o los archivos que corresponda al listado del modelo en angular (cliente web/ios/android)
	 *
	 * <h4>Request</h4>
	 * Recibe un input request tipo json de la lista de archivos a mostrar
	 *
	 * @return Response
	 * <code style="color:green"> r = 1 ok</code>
	 * <code> r = 0 No existe el archivo</code>
	 */
	public function mostrar(){
	    $file = $_REQUEST['file'];	
		$ruta = $_REQUEST['ruta'];
		$nombre = public_path()."/adjunto/";
		if($ruta != "")
			$nombre = $nombre."/".$ruta;
		$directorio_escaneado = scandir($nombre);
		$archivos = array();
		foreach ($file as $key => $value) {
			$archivos[] = $value;
		}
		
		if(count($archivos) > 0){
			return Response::json(array("r"=>1,"msg"=>$archivos));
		}
		else{
			return Response::json(array("r"=>0,"msg"=>"no existe"));
		}
	}
	/**
	 * elimina un archivo del servidor
	 *
	 * <h4>Request</h4>
	 * Recibe un input request tipo json del nombre de archivo a eliminar
	 *
	 * @return Response
	 * <code style="color:green"> r = 1 operacion realizada</code>
	 */
	public function eliminar(){
    	$file=$_REQUEST['file'];
		$ruta=$_REQUEST['ruta'];
		
		if ($file!="") {			
			if($ruta!=""){
				$file="/adjunto/".$ruta."/".$file;
			}
			
			if (file_exists(public_path()."$file")) {
				unlink(public_path()."/$file");	
				return Response::json(array("r"=>1,"msg"=>$file));
			}
			else 
				return Response::json(array("r"=>1,"msg"=>"no existe"));
		}
  	}
}
