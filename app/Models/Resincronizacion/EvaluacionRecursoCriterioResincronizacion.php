<?php namespace App\Models\Resincronizacion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluacionRecursoCriterioResincronizacion extends Model 
{
   	protected $table = 'EvaluacionRecursoCriterioResincronizacion';
   	const CREATED_AT = 'creadoAl';    
    const UPDATED_AT = 'modificadoAl';
    const DELETED_AT = 'borradoAl';

	use SoftDeletes;
    protected $dates = ['borradoAl'];
	
	public function Evaluaciones()
    {
        return $this->belongsTo('App\Models\Resincronizacion\EvaluacionRecursoResincronizacion','idCriterio');
    } 
}
?>