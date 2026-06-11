<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model {
    protected $table = 'productos'; // Nombre de la tabla en tu base de datos
    protected $fillable = ['nombre', 'categoria', 'precio', 'disponibilidad'];
    
    // Desactivar timestamps si la tabla dada por el docente no los incluye
    public $timestamps = false;
}