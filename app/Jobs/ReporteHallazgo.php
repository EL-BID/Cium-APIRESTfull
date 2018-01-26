<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use DB;

class ReporteHallazgo extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $evaluacion;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($evaluacion)
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
        
        $resultado = DB::select("SELECT DISTINCT
        i.id AS id,
        i.color AS color,
        i.codigo AS codigo,
        i.nombre AS indicador,
        a.nombre AS accion,
        h.descripcion AS descripcion,
        h.categoriaEvaluacion AS categoria,
        h.idEvaluacion AS idEvaluacion,
        h.resuelto AS resuelto,
        DAYNAME(h.creadoAl) AS day,
        DAYOFMONTH(h.creadoAl) AS dia,
        MONTHNAME(h.creadoAl) AS month,
        MONTH(h.creadoAl) AS mes,
        YEAR(h.creadoAl) AS anio,
        WEEK(h.creadoAl, 3) AS semana,
        (CASE
            WHEN ISNULL(e.fechaEvaluacion) THEN r.fechaEvaluacion
            ELSE e.fechaEvaluacion
        END) AS fechaEvaluacion,
        cc.clues AS clues,
        c.nombre AS nombre,
        c.jurisdiccion AS jurisdiccion,
        c.municipio AS municipio,
        c.localidad AS localidad,
        cn.nombre AS cone,
        h.creadoAl AS creadoAl
    FROM
        Hallazgo h
        LEFT JOIN Indicador i ON i.id = h.idIndicador
        LEFT JOIN Accion a ON a.id = h.idAccion
        LEFT JOIN EvaluacionCalidad e ON e.id = h.idEvaluacion AND h.categoriaEvaluacion = 'CALIDAD'
        LEFT JOIN EvaluacionRecurso r ON r.id = h.idEvaluacion AND h.categoriaEvaluacion = 'RECURSO'
        LEFT JOIN Clues c ON c.clues= e.clues OR  c.clues = r.clues
        LEFT JOIN ConeClues cc ON cc.clues =c.clues
        LEFT JOIN Cone cn ON cn.id = cc.idCone
    WHERE
        (ISNULL(h.borradoAl) AND ISNULL(e.borradoAl) and (e.id = ".$evaluacion->id." or r.id = ".$evaluacion->id."))");

    foreach ($resultado as $key => $value) {
            $value = json_encode($value);
            $value = str_replace("'", " ", $value);
            $value = json_decode($value);
            DB::select("
            insert into ReporteHallazgos (id, color, codigo, indicador, accion, descripcion, categoria, idEvaluacion, resuelto, day, dia,  mes, month, anio, semana, fechaEvaluacion, clues, nombre, jurisdiccion, municipio, localidad, cone, creadoAl) 
            values (
                    '$value->id',
                    '$value->color',
                    '$value->codigo',
                    '$value->indicador',
                    '$value->accion',
                    '$value->descripcion',
                    '$value->categoria',
                    '$value->idEvaluacion',
                    '$value->resuelto',
                    '$value->day',
                    '$value->dia',                    
                    '$value->mes',
                    '$value->month',
                    '$value->anio',
                    '$value->semana',
                    '$value->fechaEvaluacion',
                    '$value->clues',
                    '$value->nombre',
                    '$value->jurisdiccion',
                    '$value->municipio',
                    '$value->localidad',
                    '$value->cone',
                    '$value->creadoAl'
                )
            ");
        }
    }
}
