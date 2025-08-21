<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    protected $fillable = [
        'name',
        'status',
    ];

    // One Variant has many Values
    public function values()
    {
        return $this->hasMany(VariantValue::class);
    }
}
