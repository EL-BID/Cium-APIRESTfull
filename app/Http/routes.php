<?php 
use Illuminate\Http\Response as HttpResponse;
use App\Models\Sistema\usuario;
/**
 * Route 
 * 
 * @package    CIUM API
 * @subpackage Routes* @author     Eliecer Ramirez Esquinca <ramirez.esquinca@gmail.com>
 * @created    2015-07-20
* Rutas de la aplicación
*
* Aquí es donde se registran todas las rutas para la aplicación.
* Simplemente decirle a laravel los URI que debe responder y poner los filtros que se ejecutará cuando se solicita la URI .
*
*/
Route::get('/', function()
{
});
Route::get("descargar-app",   function()
    {
        $aplicaciones = [];
        $app = DB::select("select * from VersionApp where creadoAl = (SELECT MAX( creadoAl )  FROM VersionApp )");
        
        if(!$app){
            return Response::json(array("status" => 404,"messages" => "No hay resultados"), 404);
        } 
        else{
            return redirect(url('/').$app[0]->path);        
        }
    });
/**
* si se tiene un oauth y expira podemos renovar con el refresh oauth proporcionado
*/
Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);


Route::post('/refresh-oauth', function(){
    try{
        
        $refresh_token =  Crypt::decrypt(Input::get('refresh_token'));
        $access_token = str_replace('Bearer ','',Request::header('Authorization'));	
        $post_request = 'grant_type=refresh_token'
                    .'&client_id='.env('CLIENT_ID')
                    .'&client_secret='.env('CLIENT_SECRET')
                    .'&refresh_token='.$refresh_token
                    .'&access_token='.$access_token; 
                 
                    
        $ch = curl_init();
        $header[]         = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER,     $header);
        curl_setopt($ch, CURLOPT_URL, env('OAUTH_SERVER').'/oauth/access_token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_request);
         
        // Execute & get variables
        $api_response = json_decode(curl_exec($ch)); 
        $curlError = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if($curlError){ 
        	 throw new Exception("Hubo un problema al intentar hacer la autenticacion. cURL problem: $curlError");
        }
        
        if($http_code != 200){
            return Response::json(['error'=>$api_response->error],$http_code);
        }        
        
        //Encriptamos el refresh oauth para que no quede 100% expuesto en la aplicacion web
        $refresh_token_encrypted = Crypt::encrypt($api_response->refresh_token);
                    
        return Response::json(['access_token'=>$api_response->access_token,'refresh_token'=>$refresh_token_encrypted],200);
    }catch(Exception $e){
         return Response::json(['error'=>$e->getMessage()],500);
    }
});

Route::post('/signin', function () {
    try{
        $credentials = Input::only('email', 'password');
        if($credentials["email"] == ""){
            $credentials = Input::json()->all();            
        }
        $post_request = 'grant_type=password'
                    .'&client_id='.env('CLIENT_ID')
                    .'&client_secret='.env('CLIENT_SECRET')
                    .'&username='.$credentials['email']
                    .'&password='.$credentials['password'];       
           
        $ch = curl_init();
        $header[]         = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER,     $header);
        curl_setopt($ch, CURLOPT_URL, env('OAUTH_SERVER').'/oauth/access_token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_request);
      
        // Execute & get variables
        $api_response = json_decode(curl_exec($ch)); 
        $curlError = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if($curlError){ 
        	 throw new Exception("Hubo un problema al intentar hacer la autenticacion. cURL problem: $curlError");
        }
        
        if($http_code != 200){
          if(isset($api_response->error)){
				return Response::json(['error'=>$api_response->error],$http_code);	
			}else{
				return Response::json(['error'=>"Error"],$http_code);
			}
        }        
        //Encriptamos el refresh oauth para que no quede 100% expuesto en la aplicacion web
        $refresh_token_encrypted = Crypt::encrypt($api_response->refresh_token);
        
        return Response::json(['access_token'=>$api_response->access_token,'refresh_token'=>$refresh_token_encrypted],200);
    }catch(Exception $e){
         return Response::json(['error'=>$e->getMessage()],500);
    }
    
});

Route::group([ 'prefix' => 'v1'], function () {
    
    Route::group(['middleware' => 'oauth'], function(){
          Route::post('/permisos-autorizados', function () {     
                //return Response::json(['error'=>"ERROR_PERMISOS"],500);
                $user_email = Request::header('X-Usuario');
                $permisos = App\Models\Sistema\Usuario::obtenerClavesPermisos()->where('usuarios.email','=',$user_email)->get()->lists('clavePermiso');
                return Response::json(['permisos'=>$permisos],200);
           });
           
           Route::post('/validacion-cuenta', function () {
               try{
                    
                    // En este punto deberíamos buscar en la base de datos la cuenta del usuario
                    // que previamente el adminsitrador debió haber regitrado, incluso aunque sea una cuenta
                    // OAuth valida.
                    $user_email = Request::header('X-Usuario');
                    $user = App\Models\Sistema\Usuario::where('email','=',$user_email)->first();

                    if(!$user){
                        return Response::json(['error'=>"CUENTA_VALIDA_NO_AUTORIZADA"],403);
                    }
                    
                    $access_token = str_replace('Bearer ','',Request::header('Authorization'));	
                    $post_request = 'access_token='.$access_token; 
                             
                                
                    $ch = curl_init();
                    $header[]         = 'Content-Type: application/x-www-form-urlencoded';
                    curl_setopt($ch, CURLOPT_HTTPHEADER,     $header);
                    curl_setopt($ch, CURLOPT_URL, env('OAUTH_SERVER').'/oauth/vinculacion');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_request);
                     
                    // Execute & get variables
                    $api_response = json_decode(curl_exec($ch)); 
                    $curlError = curl_error($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    
                    if($curlError){ 
                    	 throw new Exception("Hubo un problema al intentar hacer la vinculación. cURL problem: $curlError");
                    }
                    
                    if($http_code != 200){
                        return Response::json(['error'=>$api_response->error],$http_code);
                    }
                             
                    return Response::json(['data'=>'Vinculación exitosa'],200);
                }catch(Exception $e){
                     return Response::json(['error'=>$e->getMessage()],500);
                }
           });
            
            Route::resource('usuarios',  'v1\Sistema\UsuarioController',   ['only' => ['index', 'show','store', 'update', 'destroy']]);
            Route::resource('roles',     'v1\Sistema\RolController',       ['only' => ['index', 'show','store', 'update', 'destroy']]);
            Route::resource('permisos',  'v1\Sistema\PermisoController',   ['only' => ['index', 'show','store', 'update', 'destroy']]);
            Route::resource('dashboard', 'v1\Sistema\DashboardController', ['only' => ['index']]);
    });
   
   
   Route::get('/restricted', function () {
       return ['data' => 'This has come from a dedicated API subdomain with restricted access.'];
   });
});
/**
* rutas api v1 protegidas con middleware oauth que comprueba si el usuario tiene o no permisos para el recurso solicitado
*/
Route::group(array('prefix' => 'v1', 'middleware' => 'oauth'), function()
{
	//catalogos
	Route::resource('Clues', 'v1\Catalogos\CluesController');
    Route::resource('Cone', 'v1\Catalogos\ConeController');
    Route::resource('Criterio', 'v1\Catalogos\CriterioController');
	Route::resource('Zona', 'v1\Catalogos\ZonaController');
	Route::resource('Nuevo', 'v1\Catalogos\ZonaController');
    Route::resource('Indicador', 'v1\Catalogos\IndicadorController');
    Route::resource('Accion', 'v1\Catalogos\AccionController');
	Route::resource('Alerta', 'v1\Catalogos\AlertaController');
    Route::resource('PlazoAccion', 'v1\Catalogos\PlazoAccionController');
    Route::resource('LugarVerificacion', 'v1\Catalogos\LugarVerificacionController');
    Route::resource('VersionApp', 'v1\Catalogos\VersionAppController');
	
	//transaccion
	Route::resource('EvaluacionRecurso', 'v1\Transacciones\EvaluacionRecursoController');	
	Route::resource('EvaluacionRecursoCriterio', 'v1\Transacciones\EvaluacionRecursoCriterioController');
	Route::resource('EvaluacionCalidad', 'v1\Transacciones\EvaluacionCalidadController');	
	Route::resource('EvaluacionCalidadCriterio', 'v1\Transacciones\EvaluacionCalidadCriterioController');
	Route::resource('Hallazgo', 'v1\Transacciones\HallazgoController');	

    Route::resource('RecursoResincronizacion', 'v1\Resincronizacion\EvaluacionRecursoResincronizacionController');   
    Route::resource('CalidadResincronizacion', 'v1\Resincronizacion\EvaluacionCalidadResincronizacionController');   


    Route::resource('FormularioCaptura', 'v1\Formulario\FormularioCapturaController');
    Route::resource('FormularioCapturaValor', 'v1\Formulario\FormularioCapturaValorController');
});

/**
* Acceso a catálogos sin permisos pero protegidas para que se solicite con un oauth 
*/
Route::group(array('prefix' => 'v1', 'middleware' => 'oauth'), function()
{	
	Route::get('clues', 'v1\Catalogos\CluesController@index');
	Route::get('Clues/{clues}', 'v1\Catalogos\CluesController@show');
    Route::get('Jurisdiccion', 'v1\Catalogos\CluesController@jurisdiccion');
	Route::get('CluesUsuario', 'v1\Catalogos\CluesController@CluesUsuario');
	Route::get('Cone', 'v1\Catalogos\ConeController@index');
	Route::get('Criterio', 'v1\Catalogos\CriterioController@index');
    Route::post('CriterioOrden', 'v1\Catalogos\CriterioController@updateOrden');    
	Route::get('Indicador', 'v1\Catalogos\IndicadorController@index');
	Route::get('Accion', 'v1\Catalogos\AccionController@index');
	Route::get('PlazoAccion', 'v1\Catalogos\PlazoAccionController@index');
	Route::get('LugarVerificacion', 'v1\Catalogos\LugarVerificacionController@index');
	
	Route::get('recurso', 'v1\Transacciones\DashboardController@indicadorRecurso');
	Route::get('recursoDimension', 'v1\Transacciones\DashboardController@indicadorRecursoDimension');
	Route::get('recursoClues', 'v1\Transacciones\DashboardController@indicadorRecursoClues');
	
	Route::get('calidad', 'v1\Transacciones\DashboardController@indicadorCalidad');
	Route::get('calidadDimension', 'v1\Transacciones\DashboardController@indicadorCalidadDimension');
	Route::get('calidadClues', 'v1\Transacciones\DashboardController@indicadorCalidadClues');
	
    Route::get('criterioDash', 'v1\Transacciones\DashboardController@criterio');
    Route::get('criterioDetalle', 'v1\Transacciones\DashboardController@criterioDetalle');
    Route::get('criterioEvaluacion', 'v1\Transacciones\DashboardController@criterioEvaluacion'); 

	Route::get('alertaDash', 'v1\Transacciones\DashboardController@alerta');    
	Route::get('alertaEstricto', 'v1\Transacciones\DashboardController@alertaEstricto');
    Route::get('alertaDetalle', 'v1\Transacciones\DashboardController@alertaDetalle');
    Route::get('alertaEvaluacion', 'v1\Transacciones\DashboardController@alertaEvaluacion');    
	
	Route::get('hallazgoGauge', 'v1\Transacciones\DashboardController@hallazgoGauge');
	Route::get('hallazgoDimension', 'v1\Transacciones\HallazgoController@hallazgoDimension');
	
	Route::get('TopCalidadGlobal', 'v1\Transacciones\DashboardController@topCalidadGlobal');
	Route::get('TopRecursoGlobal', 'v1\Transacciones\DashboardController@topRecursoGlobal');
	Route::get('pieVisita', 'v1\Transacciones\DashboardController@pieVisita');
	
	Route::get('indexCriterios', 'v1\Transacciones\HallazgoController@indexCriterios');
	Route::get('showCriterios', 'v1\Transacciones\HallazgoController@showCriterios');
	
    Route::get('PivotRecurso', 'v1\Transacciones\PivotController@Recurso');
    Route::get('PivotCalidad', 'v1\Transacciones\PivotController@Calidad');

    Route::get('ResetearReportes', 'v1\Transacciones\PivotController@ResetearReportes');
    Route::get('ResetearResincronizacion', 'v1\Resincronizacion\ResetearResincronizacionController@index');

    Route::get('anio-captura/{id}', 'v1\Formulario\FormularioCapturaValorController@anio');

    
    
	/*export
	Route::post('Export', 'v1\ExportController@Export');
	Route::post('exportGenerate', 'v1\ExportController@exportGenerate');*/
});


/**
* permisos por modulo
* Para proteger una ruta hay que agregar el middleware correspondiente según sea el caso de protección
* para peticiones como cátalogos que no se necesita tener permisos se le asigna el middleware oauth
* para peticiones que se necesitan permisos para acceder se asigna el middleware oauth
*/
Route::get('v1/permiso', ['middleware' => 'oauth', 'uses'=>'v1\Sistema\SysModuloController@permiso']);
/**
*Lista criterios evaluacion y estadistica de evaluacion por indicador (Evaluacion Recurso)
*/
Route::get('v1/CriterioEvaluacionRecurso/{cone}/{indicador}/{id}', ['middleware' => 'oauth', 'uses'=>'v1\Transacciones\EvaluacionRecursoCriterioController@CriterioEvaluacion']);
Route::get('v1/CriterioEvaluacionRecursoImprimir/{cone}/{indicador}', ['middleware' => 'oauth', 'uses'=>'v1\Transacciones\EvaluacionRecursoCriterioController@CriterioEvaluacionImprimir']);
Route::get('v1/EstadisticaRecurso/{evaluacion}', ['middleware' => 'oauth', 'uses'=>'v1\Transacciones\EvaluacionRecursoCriterioController@Estadistica']);
Route::get('v1/RecursoCorreo/{id}', ['middleware' => 'oauth', 'uses'=>'v1\Transacciones\EvaluacionRecursoController@Correo']);
/**
* Guardar hallazgos encontrados
*/
Route::post('v1/EvaluacionRecursoHallazgo', ['middleware' => 'oauth', 'uses'=>'v1\Transacciones\EvaluacionRecursoController@Hallazgos']);
/**
* Lista criterios evaluacion y estadistica de evaluacion por indicador (Evaluacion calidad)
*/
Route::get('v1/CriterioEvaluacionCalidad/{cone}/{indicador}/{id}', ['middleware' => 'oauth', 'uses'=>'v1\Transacciones\EvaluacionCalidadCriterioController@CriterioEvaluacion']);
Route::get('v1/CriterioEvaluacionCalidadImprimir/{cone}/{indicador}', ['middleware' => 'oauth', 'uses'=>'v1\Transacciones\EvaluacionCalidadCriterioController@CriterioEvaluacionImprimir']);
Route::get('v1/CriterioEvaluacionCalidadIndicador/{id}', ['middleware' => 'oauth', 'uses'=>'v1\Transacciones\EvaluacionCalidadCriterioController@CriterioEvaluacionCalidadIndicador']);
Route::get('v1/EstadisticaCalidad/{evaluacion}', ['middleware' => 'oauth', 'uses'=>'v1\Transacciones\EvaluacionCalidadCriterioController@Estadistica']);
Route::get('v1/CalidadCorreo/{id}', ['middleware' => 'oauth', 'uses'=>'v1\Transacciones\EvaluacionCalidadController@Correo']);
/**
* Guardar hallazgos encontrados
*/
Route::post('v1/EvaluacionCalidadHallazgo', ['middleware' => 'oauth', 'uses'=>'v1\Transacciones\EvaluacionCalidadController@Hallazgos']);

/**
* Crear catalogo de seleccion jurisdiccion para asignar permisos a usuario
*/
Route::get('v1/jurisdiccion', ['middleware' => 'oauth', 'uses'=>'v1\Catalogos\CluesController@jurisdiccion']);
/**
* Actualizar información del usuario logueado
*/
Route::put('v1/UpdateInfo/{email}', ['middleware' => 'oauth', 'uses'=>'v1\Sistema\UsuarioController@UpdateInfo']);
//end rutas api v1

//genera el pdf
Route::get("v1/pdf", function(){   
    ini_set('memory_limit',   '1024M'); 
    ini_set('max_input_vars', '30000');    
        
        $html = \Session::get('htmlTOpdf');
        $nombre = \Session::get('nombrepdf');
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($html);
        
        \Session::forget('htmlTOpdf');
        \Session::forget('nombrepdf');
        return $pdf->stream($nombre.".pdf");
     
       
});

Route::post("v1/html-pdf", function(){
    $datos      = Input::json();
    $contenido  = str_replace("id=", "class=", urldecode($datos->get("html")));
    $header     = str_replace("id=", "class=", urldecode($datos->get("header")));
    $footer     = str_replace("id=", "class=", urldecode($datos->get("footer")));

    $style = file_get_contents(public_path().'/css/print.css');
    $html  = '<html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <style>
            @page { margin: 60px 50px 35px; content: "Page " counter(page);}
            .header { position: fixed; left: 0px; top: -45px; right: 0px; height: 50px; padding:4px;}
            .footer { position: fixed; left: 0px; bottom: -15px; right: 0px; height: 60px; text-align: center;}
            '.$style.'
        </style>        
        </head>
        <body>
            <div class="header">'.$header.'</div>
                '.$contenido.'
            <div class="footer">'.$footer.'</div>
        </body>
    </html>';
    
    \Session::put("htmlTOpdf", $html);  
    \Session::put("nombrepdf", $datos->get("nombre"));
    return Response::json(array("status" => 200, "messages" => "Operación realizada con exito"), 200);
});