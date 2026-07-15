<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;
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

    public function inventories()
    {
        return $this->hasMany(BranchInventory::class);
    }
}
