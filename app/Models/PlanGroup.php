<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanGroup extends Model
{
    protected $fillable = ['name', 'description'];

    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'plan_group_plan')
            ->withTimestamps()
            ->orderBy('name');
    }
}
