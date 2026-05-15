<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeBaseArticle extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'priority', 'category'];

    public function serviceTypes()
    {
        return $this->belongsToMany(ServiceType::class, 'article_service_type', 'article_id', 'service_type_id');
    }
}