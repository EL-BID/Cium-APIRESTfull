<?php namespace App\Models\Transacciones;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Seguimiento extends Model 
{
   	protected $table = 'Seguimiento';
   	const CREATED_AT = 'creadoAl';    
    const UPDATED_AT = 'modificadoAl';
    const DELETED_AT = 'borradoAl';

	use SoftDeletes;
    protected $dates = ['borradoAl'];
	
	public function Usuario()
    {
        return $this->belongsTo('App\Models\Sistema\Usuario','idUsuario');
    } 
	public function Hallazgo()
    {
        return $this->belongsTo('App\Models\Transacciones\Hallazgo','idHallazgo');
    } 
	
}
?>