<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackagingType extends Model
{
    protected $fillable = ['name'];

    public function packagings()
    {
        return $this->hasMany(ProductPackaging::class);
    }
}
