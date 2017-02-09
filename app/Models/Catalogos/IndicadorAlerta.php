<?php namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class IndicadorAlerta extends Model {
	
	protected $table = 'IndicadorAlerta';
	const CREATED_AT = 'creadoAl';    
    const UPDATED_AT = 'modificadoAl';
    const DELETED_AT = 'borradoAl';

	use SoftDeletes;
    protected $dates = ['borradoAl'];
	
	public function IndicadorAlertas()
    {
        return $this->belongsToMany('App\Models\Catalogos\Indicador', 'IndicadorAlerta', 'idIndicador', 'id');
    }
}
