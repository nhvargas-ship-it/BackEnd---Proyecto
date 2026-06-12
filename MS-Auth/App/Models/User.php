<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // Tu tabla real se llama usuarios
    protected $table = 'usuarios'; 
    
    // Activamos timestamps porque tu tabla sí tiene created_at y updated_at
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'correo',
        'usuario',
        'contrasena',
        'rol',
        'token',
        'sesion_activa',
        'estado'
    ];

    // Ocultamos la columna contrasena al retornar respuestas JSON
    protected $hidden = [
        'contrasena'
    ];
}