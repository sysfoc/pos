<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'barcode',
        'price',
        'cost',
        'tax_percentage',
        'tax_type',
        'threshold',
        'unit_id',
        'discount_percentage',
        'sku',
        'category_id',
        'quantity',
        'status',
        'selling_type',
        'brand_name',
        'manufactured_date',
        'expire_date',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
