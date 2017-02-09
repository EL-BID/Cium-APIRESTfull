<?php namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class VersionApp extends Model{
    
    
    protected $table = 'VersionApp';
    const CREATED_AT = 'creadoAl';    
    const UPDATED_AT = 'modificadoAl';
    const DELETED_AT = 'borradoAl';
    
    use SoftDeletes;
    protected $dates = ['borradoAl'];
}