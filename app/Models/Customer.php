<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Customer extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'user_id',
        'cnic',
        'ntn_number',
        'fbr_number',
    ];

    public function getAvatarUrl()
    {
        return Storage::url($this->avatar);
    }
}
