<?php namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class CriterioValidacionPregunta extends Model {

	protected $table = 'CriterioValidacionPregunta';
   	const CREATED_AT = 'creadoAl';    
    const UPDATED_AT = 'modificadoAl';
    const DELETED_AT = 'borradoAl';
	
	use SoftDeletes;
    protected $dates = ['borradoAl'];
	
	public function CriterioPreguntas()
    {
        return $this->belongsToMany('App\Models\Catalogos\Criterio', 'CriterioValidacionPregunta', 'idCriterio', 'id');
    }
}
