<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    use HasFactory;

    protected $table = 'rides';

    protected $fillable = [
        'route_name',
        'unit_name',
        'tid',
        'stop_id',
        'stop_name',
        'plan_time',
        'exec_time',
        'diff'
    ];
}
