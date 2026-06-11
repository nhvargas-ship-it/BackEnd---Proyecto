<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model {
    protected $table = 'reservas';
    protected $fillable = ['nombre_cliente', 'telefono', 'cantidad_personas', 'fecha', 'hora', 'mesa_id', 'observaciones', 'estado']; // Estados: Pendiente, Confirmada, Cancelada, Finalizada [cite: 138, 139, 140, 141, 142, 143, 144, 160]
    public $timestamps = false;

    public function mesa() {
        return $this->belongsTo(Mesa::class, 'mesa_id');
    }
}