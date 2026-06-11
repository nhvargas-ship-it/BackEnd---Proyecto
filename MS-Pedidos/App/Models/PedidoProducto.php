<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoProducto extends Model {
    protected $table = 'pedido_productos';
    protected $fillable = ['pedido_id', 'producto_id', 'cantidad', 'precio_unitario', 'subtotal'];
    public $timestamps = false;
}