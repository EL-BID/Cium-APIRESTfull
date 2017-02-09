<?php namespace App\Models\Transacciones;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluacionCalidadRegistro extends Model 
{
   	protected $table = 'EvaluacionCalidadRegistro';
   	const CREATED_AT = 'creadoAl';    
    const UPDATED_AT = 'modificadoAl';
    const DELETED_AT = 'borradoAl';

	use SoftDeletes;
    protected $dates = ['borradoAl'];
	
	public function Registros()
    {
        return $this->belongsTo('App\Models\Transacciones\EvaluacionCalidad','idEvaluacionCalidadRegistro');
    } 
}
?>