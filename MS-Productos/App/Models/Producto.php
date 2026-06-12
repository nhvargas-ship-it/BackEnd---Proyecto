<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';
    public $timestamps = true;
    protected $fillable = ['nombre', 'descripcion', 'precio', 'categoria_id', 'estado'];

    // Relación inversa
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}