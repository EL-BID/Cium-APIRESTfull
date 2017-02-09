<?php namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class CriterioValidacionRespuesta extends Model {

	protected $table = 'CriterioValidacionRespuesta';
   	const CREATED_AT = 'creadoAl';    
    const UPDATED_AT = 'modificadoAl';
    const DELETED_AT = 'borradoAl';
	
	use SoftDeletes;
    protected $dates = ['borradoAl'];
	
	public function CriterioRespuestas()
    {
        return $this->belongsToMany('App\Models\Catalogos\Criterio', 'CriterioValidacionRespuestas', 'idCriterio', 'id');
    }
}
