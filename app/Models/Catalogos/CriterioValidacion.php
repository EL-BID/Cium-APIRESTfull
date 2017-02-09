<?php namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class CriterioValidacion extends Model {

	protected $table = 'CriterioValidacion';
   	const CREATED_AT = 'creadoAl';    
    const UPDATED_AT = 'modificadoAl';
    const DELETED_AT = 'borradoAl';
	
	use SoftDeletes;
    protected $dates = ['borradoAl'];
	
	public function CriterioValidaciones()
    {
        return $this->belongsToMany('App\Models\Catalogos\Criterio', 'CriterioValidacion', 'idCriterio', 'id');
    }
}
