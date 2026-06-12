<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    protected $table = 'detalles_pedidos';
    public $timestamps = false; // Normalmente los detalles no llevan created_at individuales
    protected $fillable = ['pedido_id', 'producto_id', 'cantidad', 'precio_unitario'];
}