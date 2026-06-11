<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mesa extends Model {
    protected $table = 'mesas';
    protected $fillable = ['numero_mesa', 'capacidad', 'estado']; // Estados: Disponible, Reservada, Ocupada, Fuera de servicio [cite: 113, 114, 115, 125]
    public $timestamps = false;

    public function reservas() {
        return $this->hasMany(Reserva::class, 'mesa_id');
    }
}