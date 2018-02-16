<?php

namespace App\Jobs;

use App\Models\Transacciones\EvaluacionCalidad;
use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use DB;

class ReporteCalidad extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $evaluacion;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(EvaluacionCalidad $evaluacion)
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
        $evaluacion = $this->evaluacion;
        $resultado = DB::SELECT("SELECT 
        i.id AS id,		     
        e.id AS evaluacion,
        i.color AS color,
        i.codigo AS codigo,
        i.nombre AS indicador,
        ec.totalCriterio AS total_criterio,
        
        (SELECT count(id) FROM EvaluacionCalidadCriterio where idIndicador = i.id and idEvaluacionCalidad = e.id and aprobado = 1) AS aprobado_cri,
        (SELECT count(id) FROM EvaluacionCalidadCriterio where idIndicador = i.id and idEvaluacionCalidad = e.id and aprobado = 0) AS noAprobado_cri,
        (SELECT count(id) FROM EvaluacionCalidadCriterio where idIndicador = i.id and idEvaluacionCalidad = e.id and aprobado = 2) AS noAplica_cri,

        (SELECT count(id) from EvaluacionCalidadRegistro where idEvaluacionCalidad = e.id and idIndicador = i.id and cumple = 1  and borradoAl is null) AS aprobado_exp,
        (SELECT count(id) from EvaluacionCalidadRegistro where idEvaluacionCalidad = e.id and idIndicador = i.id and cumple = 0  and borradoAl is null)  AS noAprobado_exp,
        
        e.fechaEvaluacion AS fechaEvaluacion,
        DAYNAME(e.fechaEvaluacion) AS day,
        DAYOFMONTH(e.fechaEvaluacion) AS dia,
        UCASE(MONTHNAME(e.fechaEvaluacion)) AS month,
        MONTH(e.fechaEvaluacion) AS mes,
        YEAR(e.fechaEvaluacion) AS anio,
        WEEK(e.fechaEvaluacion, 3) AS semana,
        e.clues AS clues,
        c.nombre AS nombre,
        cn.id AS idCone,
        cn.nombre AS cone,
        c.jurisdiccion AS jurisdiccion,
        c.claveJurisdiccion  AS clave_jurisdiccion,
        c.municipio AS municipio,
        c.claveMunicipio  AS clave_municipio,
        c.localidad AS localidad,
        c.claveLocalidad  AS clave_localidad,
        z.nombre AS zona
    FROM
        EvaluacionCalidadRegistro ec
        LEFT JOIN Indicador i ON i.id = ec.idIndicador
        LEFT JOIN EvaluacionCalidad e ON e.id = ec.idEvaluacionCalidad
        LEFT JOIN Clues c ON  c.clues = e.clues
        LEFT JOIN ConeClues cc ON cc.clues = c.clues
        LEFT JOIN Cone cn ON cn.id = cc.idCone
        LEFT JOIN ZonaClues zc ON zc.clues = e.clues
        LEFT JOIN Zona z ON z.id = zc.idZona
    WHERE
        ec.borradoAl is null
            AND e.borradoAl is null
            AND e.id IS NOT NULL
            AND e.cerrado = '1'
            and e.id = ".$evaluacion->id."
	group by evaluacion, indicador"
        );

        foreach ($resultado as $key => $value) {

            $value = json_encode($value);
            $value = str_replace("'", " ", $value);
            $value = json_decode($value);

            $value->total_exp = intval($value->aprobado_exp) + intval($value->noAprobado_exp) ;
            $value->promedio_exp = (intval($value->aprobado_exp) / intval($value->total_exp)) * 100;
            if($value->promedio_exp == 100){
                $value->cumple_exp = 1;
            } else {
                $value->cumple_exp = 0;
            }

            $value->total_cri = intval($value->aprobado_cri) + intval($value->noAprobado_cri) +intval($value->noAplica_cri);
            $value->promedio_cri = (intval($value->aprobado_cri) / intval($value->total_cri)) * 100;
            if($value->promedio_cri == 100){
                $value->cumple_cri = 1;
            } else {
                $value->cumple_cri = 0;
            }

            DB::SELECT("
            INSERT INTO ReporteCalidad (id, evaluacion, color, codigo, indicador, total_cri, aprobado_cri, noAprobado_cri, noAplica_cri, promedio_cri, cumple_cri, total_exp, aprobado_exp, noAprobado_exp, promedio_exp, cumple_exp, fechaEvaluacion, day, dia, month, mes, anio, semana, clues, nombre, idCone, cone, jurisdiccion, clave_jurisdiccion, municipio, clave_municipio, localidad, clave_localidad, zona) 
            values (
                    '$value->id',
                    '$value->evaluacion',
                    '$value->color',
                    '$value->codigo',
                    '$value->indicador',
                    '$value->total_cri',
                    '$value->aprobado_cri',
                    '$value->noAprobado_cri',
                    '$value->noAplica_cri',
                    '$value->promedio_cri',
                    '$value->cumple_cri',
                    '$value->total_exp',
                    '$value->aprobado_exp',
                    '$value->noAprobado_exp',
                    '$value->promedio_exp',
                    '$value->cumple_exp',
                    '$value->fechaEvaluacion',
                    '$value->day',
                    '$value->dia',
                    '$value->month',
                    '$value->mes',
                    '$value->anio',
                    '$value->semana',
                    '$value->clues',
                    '$value->nombre',
                    '$value->idCone',
                    '$value->cone',                    
                    '$value->jurisdiccion',
                    '$value->clave_jurisdiccion',
                    '$value->municipio',
                    '$value->clave_municipio',
                    '$value->localidad',
                    '$value->clave_localidad',
                    '$value->zona'
                )
            ");
        }
    }
}
