<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'code', 'address', 'phone', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function zones()
    {
        return $this->hasMany(Zone::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}
