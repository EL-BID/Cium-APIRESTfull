<?php namespace App\Models\Formulario;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormularioCapturaValor extends Model 
{
   	protected $table = 'FormularioCapturaValor';
   	const CREATED_AT = 'creadoAl';    
    const UPDATED_AT = 'modificadoAl';
    const DELETED_AT = 'borradoAl';


	use SoftDeletes;
    protected $dates = ['borradoAl'];
	
	
}
?>