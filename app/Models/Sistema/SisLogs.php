<?php namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class SisLogs extends Model{
    
    protected $table = 'sys_logs';  
    
    use SoftDeletes; 
    
}