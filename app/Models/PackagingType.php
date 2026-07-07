<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackagingType extends Model
{
    protected $fillable = ['name', 'unit_of_measure'];

    public function packagings()
    {
        return $this->hasMany(ProductPackaging::class);
    }
}
