<?php namespace App\Models\Resincronizacion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluacionRecursoResincronizacion extends Model 
{
   	protected $table = 'EvaluacionRecursoResincronizacion';
   	const CREATED_AT = 'creadoAl';    
    const UPDATED_AT = 'modificadoAl';
    const DELETED_AT = 'borradoAl';

	use SoftDeletes;
    protected $dates = ['borradoAl'];
	
	public function criterios()
    {
        return $this->hasMany('App\Models\Resincronizacion\EvaluacionRecursoCriterioResincronizacion','idCriterio');
    }
	public function cone()
    {
		return $this->belongsTo('App\Models\Catalogos\ConeClues','clues', 'clues');
    }
	public function cluess()
    {
		return $this->belongsTo('App\Models\Catalogos\Clues','clues','clues');
    }
	public function Usuarios()
    {
		return $this->belongsTo('App\Models\Sistema\Usuario','idUsuario');
    }
}

?>