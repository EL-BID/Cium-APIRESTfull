<?php namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class ConeIndicadorCriterio extends Model 
{

   	protected $table = 'ConeIndicadorCriterio';
	const CREATED_AT = 'creadoAl';    
    const UPDATED_AT = 'modificadoAl';
    const DELETED_AT = 'borradoAl';
	
	use SoftDeletes;
    protected $dates = ['borradoAl'];
	public function LugarVerificaciones()
    {
        return $this->belongsToMany('App\Models\Catalogos\LugarVerificacion','LugarVerificacionIndicadorCriterio','idConeIndicadorCriterio','idLugarVerificacion')->withPivot("id");
    }
}
?>