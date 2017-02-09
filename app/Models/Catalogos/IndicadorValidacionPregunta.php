<?php namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class IndicadorValidacionPregunta extends Model {

	protected $table = 'IndicadorValidacionPregunta';
   	const CREATED_AT = 'creadoAl';    
    const UPDATED_AT = 'modificadoAl';
    const DELETED_AT = 'borradoAl';
	
	use SoftDeletes;
    protected $dates = ['borradoAl'];
	
	public function IndicadorPreguntas()
    {
        return $this->belongsToMany('App\Models\Catalogos\Indicador', 'IndicadorValidacionPregunta', 'idIndicador', 'id');
    }
}
