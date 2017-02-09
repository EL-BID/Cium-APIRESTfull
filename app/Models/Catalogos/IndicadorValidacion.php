<?php namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class IndicadorValidacion extends Model {

	protected $table = 'IndicadorValidacion';
   	const CREATED_AT = 'creadoAl';    
    const UPDATED_AT = 'modificadoAl';
    const DELETED_AT = 'borradoAl';
	
	use SoftDeletes;
    protected $dates = ['borradoAl'];
	
	public function IndicadorValidaciones()
    {
        return $this->belongsToMany('App\Models\Catalogos\Indicador', 'IndicadorValidacion', 'idIndicador', 'id');
    }
}
