<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model {
    protected $table = 'usuarios'; // Nombre de la tabla asignada
    protected $fillable = ['usuario', 'correo', 'password', 'token', 'logged', 'session_active']; [cite: 82, 83, 225]
    
    // Desactivar timestamps si la tabla dada no los tiene
    public $timestamps = false;
}