<?php namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class IndicadorCriterio extends Model 
{

   	protected $table = 'IndicadorCriterio';
	const CREATED_AT = 'creadoAl';    
    const UPDATED_AT = 'modificadoAl';
    const DELETED_AT = 'borradoAl';
	
	use SoftDeletes;
    protected $dates = ['borradoAl'];
	public function Cones()
    {
        return $this->belongsToMany('App\Models\Catalogos\Cone','ConeIndicadorCriterio','idCone','idIndicadorCriterio')->withPivot("id");
    }
}
?>