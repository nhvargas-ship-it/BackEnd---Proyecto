<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model {
    protected $table = 'pedidos';
    protected $fillable = ['mesa_id', 'estado', 'subtotal', 'total', 'cantidad_total'];
    public $timestamps = false;

    // Relación para traer los productos asignados a esta comanda
    public function items() {
        return $this->hasMany(PedidoProducto::class, 'pedido_id');
    }
}