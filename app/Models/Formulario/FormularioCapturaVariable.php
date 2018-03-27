<?php namespace App\Models\Formulario;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormularioCapturaVariable extends Model 
{
   	protected $table = 'FormularioCapturaVariable';
   	const CREATED_AT = 'creadoAl';    
    const UPDATED_AT = 'modificadoAl';
    const DELETED_AT = 'borradoAl';


	use SoftDeletes;
    protected $dates = ['borradoAl'];
	
	public function FormularioCapturaValor()
    {
        return $this->hasMany('App\Models\Formulario\FormularioCapturaValor','idFormularioCapturaVariable');
    }
  public function FormularioCapturaUsuarios()
    {
        return $this->hasMany('App\Models\Formulario\FormularioCapturaUsuarios','idFormularioCapturaVariable');
    }
     
}
?>