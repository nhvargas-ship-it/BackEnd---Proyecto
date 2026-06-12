<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';
    public $timestamps = true;
    protected $fillable = ['mesa_id', 'cliente', 'total', 'estado'];

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }
}