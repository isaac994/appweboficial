<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modelo extends Model
{
    use HasFactory;

    protected $table = 'modelos';
    protected $primaryKey = 'id_modelo';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'id_marca',
    ];

    /**
     * Relación con Marca - Un modelo pertenece a una marca
     */
    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca', 'id_marca');
    }

    /**
     * Relación con Productos - Un modelo puede tener muchos productos
     */
    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_modelo', 'id_modelo');
    }
}
