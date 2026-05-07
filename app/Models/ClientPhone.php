<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPhone extends Model
{
    protected $fillable = ['client_id', 'number', 'type'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}