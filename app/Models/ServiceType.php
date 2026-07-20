<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'requires_noc', 'requires_ot', 'requires_contract', 'requires_potential'];

    public function articles()
    {
        return $this->belongsToMany(KnowledgeBaseArticle::class, 'article_service_type', 'service_type_id', 'article_id');
    }
}