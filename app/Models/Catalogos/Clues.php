<?php namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Clues extends Model {

	protected $table = 'Clues';
	
	public function coneClues()
    {
		return $this->belongsTo('App\Models\Catalogos\ConeClues','clues','clues','clues');
    }
}
