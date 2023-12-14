<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'supplier';
    protected $primaryKey = 'id_supplier';
    protected $guarded = [];

 // Define the relationship with the Purchase model
    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'supplier_id', 'id_supplier');
    }
}
