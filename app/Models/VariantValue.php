<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariantValue extends Model
{
    protected $fillable = [
        'variant_id',
        'value',
        'status',
    ];

    // Each value belongs to a Variant
    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }
}
