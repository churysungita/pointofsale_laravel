<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesDetails extends Model
{
    use HasFactory;

    protected $table = 'sales_detail';
    protected $primaryKey = 'sales_detail_id';
    protected $guarded = [];

    public function product()
    {
        return $this->hasOne(Product::class, 'product_id', 'product_id');
    }
}
