<?php namespace App\Models\Resincronizacion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluacionRecursoRegistroResincronizacion extends Model 
{
   	protected $table = 'EvaluacionRecursoRegistroResincronizacion';
   	const CREATED_AT = 'creadoAl';    
    const UPDATED_AT = 'modificadoAl';
    const DELETED_AT = 'borradoAl';

	use SoftDeletes;
    protected $dates = ['borradoAl'];
	
	public function Registros()
    {
        return $this->belongsTo('App\Models\Resincronizacion\EvaluacionRecursoResincronizacion','idEvaluacionRecursoRegistro');
    } 
}
?>