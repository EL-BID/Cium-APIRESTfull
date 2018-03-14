<?php

namespace App\Jobs;

use App\Models\Resincronizacion\EvaluacionCalidadResincronizacion;

use App\Models\Transacciones\EvaluacionCalidad;
use App\Models\Transacciones\EvaluacionCalidadCriterio;
use App\Models\Transacciones\EvaluacionCalidadRegistro;
use App\Models\Transacciones\Hallazgo;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use DB;
use App\Jobs\ReporteCalidad;
use App\Jobs\ReporteHallazgo;
class ResincronizacionCalidad extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $evaluacion;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(EvaluacionCalidadResincronizacion $evaluacion)
    {
        $this->evaluacion = $evaluacion;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $idevaluacion = $this->evaluacion->id;

        $item = DB::table('EvaluacionCalidadResincronizacion')
        ->where("id", $idevaluacion)->first();

        if($item){
            // validar que no exista la evaluacion con la misma fecha
            $fecha = $item->fechaEvaluacion;
            $date = new \DateTime($fecha);
            $fecha = $date->format('Y-m-d');

            $existe_fecha = DB::table('EvaluacionCalidad')
            ->where(DB::raw("DATE_FORMAT(fechaEvaluacion, '%Y-%m-%d')"), $fecha)
            ->where("clues", $item->clues)->first();

            if(!$existe_fecha){
                $evaluacion = new EvaluacionCalidad;
                $evaluacion->clues = isset($item->clues) ? $item->clues : $evaluacion->clues;
                $evaluacion->idUsuario = $item->idUsuario;
                $evaluacion->fechaEvaluacion = $item->fechaEvaluacion;
                $evaluacion->cerrado = $item->cerrado;
                $evaluacion->firma = array_key_exists("firma",$item) ? $item->firma : '';
                $evaluacion->responsable = array_key_exists("responsable",$item) ? $item->responsable : '';
                $evaluacion->email = array_key_exists("email",$item) ? $item->email : '';
                $evaluacion->enviado = $item->enviado ? $item->enviado : 0;
                
                if ($evaluacion->save()) 
                { 
                    // 
                    $registros = DB::table('EvaluacionCalidadRegistroResincronizacion')
                    ->where("idEvaluacionCalidad", $idevaluacion)->get();
                    foreach($registros as $reg)
                    {
                        $registro = new EvaluacionCalidadRegistro;
                        
                        $registro->idEvaluacionCalidad = $evaluacion->id;
                        $registro->idIndicador = $reg->idIndicador;
                        $registro->expediente = $reg->expediente;
                        $registro->columna = $reg->columna;
                        $registro->cumple = $reg->cumple;
                        $registro->promedio = $reg->promedio;
                        $registro->totalCriterio = $reg->totalCriterio;
                        
                        if($registro->save())
                        {
                            // 
                            $criterios = DB::table('EvaluacionCalidadCriterioResincronizacion')
                            ->where("idEvaluacionCalidad", $idevaluacion)
                            ->where("idEvaluacionCalidadRegistro", $reg->id)->get();
                            foreach($criterios as $criterio)
                            {
                                $evaluacionCriterio = new EvaluacionCalidadCriterio;
                                
                                $evaluacionCriterio->idEvaluacionCalidad = $evaluacion->id;
                                $evaluacionCriterio->idEvaluacionCalidadRegistro = $registro->id;
                                $evaluacionCriterio->idCriterio = $criterio->idCriterio;
                                $evaluacionCriterio->idIndicador = $criterio->idIndicador;
                                $evaluacionCriterio->aprobado = $criterio->aprobado;
                                
                                if ($evaluacionCriterio->save()) 
                                {                               
                                    $success = true;
                                } 
                            }

                            $success = true;
                        }
                    }
                    
                    // 
                    $hallazgos = DB::table('HallazgoResincronizacion')
                    ->where("idEvaluacion", $idevaluacion)
                    ->where("categoriaEvaluacion", "CALIDAD")->get();
                    foreach($hallazgos as $hs )
                    {                                            
                        $hallazgo = new Hallazgo;                                       
                                            
                        $hallazgo->idUsuario = $hs->idUsuario;
                        $hallazgo->idAccion = $hs->idAccion;
                        $hallazgo->idEvaluacion = $evaluacion->id;
                        $hallazgo->idIndicador = $hs->idIndicador;
                        $hallazgo->expediente = $hs->expediente;
                        $hallazgo->categoriaEvaluacion = 'CALIDAD';
                        $hallazgo->idPlazoAccion = $hs->idPlazoAccion;
                        $hallazgo->resuelto = $hs->resuelto;
                        $hallazgo->descripcion = $hs->descripcion;
                        
                        if($hallazgo->save())
                        {                          
                            $success=true;                              
                        }                   
                    }

                    $this->dispatch(new ReporteCalidad($evaluacion));

                    if(count($hallazgos) > 0){
                        $this->dispatch(new ReporteHallazgo($evaluacion));
                    }
                }
            }
        }           
    }
}
