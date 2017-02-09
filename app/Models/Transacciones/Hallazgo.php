<?php namespace App\Models\Transacciones;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hallazgo extends Model 
{
   	protected $table = 'Hallazgo';
   	const CREATED_AT = 'creadoAl';    
    const UPDATED_AT = 'modificadoAl';
    const DELETED_AT = 'borradoAl';

	use SoftDeletes;
    protected $dates = ['borradoAl'];
	
	public function Accion()
    {
        return $this->belongsTo('App\Models\Catalogos\Accion','idAccion');
    } 
	public function Plazo()
    {
        return $this->belongsTo('App\Models\Catalogos\PlazoAccion','idPlazoAccion');
    } 
	public function Usuario()
    {
        return $this->belongsTo('App\Models\Sistema\Usuario','idUsuario');
    } 
	public function Indicador()
    {
        return $this->belongsTo('App\Models\Catalogos\Indicador','idIndicador');
    } 
}
?>