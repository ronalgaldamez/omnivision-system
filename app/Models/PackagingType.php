<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackagingType extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'unit_of_measure'];

    public function packagings()
    {
        return $this->hasMany(ProductPackaging::class);
    }
}
