<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $table = 'reservas';
    public $timestamps = true;
    protected $fillable = ['mesa_id', 'fecha', 'hora', 'cantidad_personas', 'nombre_cliente', 'telefono', 'estado'];
}