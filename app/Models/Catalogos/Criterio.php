<?php namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Criterio extends Model 
{

   	protected $table = 'Criterio';

   	const CREATED_AT = 'creadoAl';    
    const UPDATED_AT = 'modificadoAl';
    const DELETED_AT = 'borradoAl';

	use SoftDeletes;
    protected $dates = ['borradoAl'];
	
    
    public function Evaluaciones()
	{
	    return $this->belongsToMany('App\Models\Catalogos\Evaluacion','idEvaluacion');
	} 
	public function Indicadores()
    {
        return $this->belongsToMany('App\Models\Catalogos\Indicador','IndicadorCriterio','idCriterio','idIndicador')->withPivot("id","idLugarVerificacion")->where("IndicadorCriterio.borradoAl",null);                   
    }
	public function Cones()
    {
        return $this->belongsToMany('App\Models\Catalogos\Cone','ConeCriterio','idCriterio','idCone');
    }
	public function LugarVerificaciones()
    {
        return $this->belongsToMany('App\Models\Catalogos\LugarVerificacion','LugarVerificacionCriterio','idCriterio','idLugarVerificacion');
    }

    public function CriterioValidaciones()
    {
        return $this->hasMany('App\Models\Catalogos\CriterioValidacion','idCriterio');
    }
    
    public function CriterioPreguntas()
    {
        return $this->hasMany('App\Models\Catalogos\CriterioValidacionPregunta', 'idCriterio');
    }

    public function CriterioRespuestas()
    {
        return $this->hasMany('App\Models\Catalogos\CriterioValidacionRespuesta', 'idCriterio');
    }
}
?>