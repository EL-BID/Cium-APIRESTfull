<?php namespace App\Models\Formulario;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormularioCaptura extends Model 
{
   	protected $table = 'FormularioCaptura';
   	const CREATED_AT = 'creadoAl';    
    const UPDATED_AT = 'modificadoAl';
    const DELETED_AT = 'borradoAl';


	  use SoftDeletes;
    protected $dates = ['borradoAl'];
	
	public function FormularioCapturaVariable()
    {
        return $this->hasMany('App\Models\Formulario\FormularioCapturaVariable','idFormularioCaptura');
    } 
}
?>