<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    protected $table = 'mesas';
    public $timestamps = true;
    protected $fillable = ['numero_mesa', 'capacidad', 'ubicacion', 'estado'];
}