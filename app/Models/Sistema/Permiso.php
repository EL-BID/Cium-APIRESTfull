<?php namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model {

	public function roles(){
		return $this->belongsToMany('App\Models\Sistema\Rol','permiso_rol', 'permiso_id', 'rol_id');
	}
}
