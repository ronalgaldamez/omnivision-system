<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'address', 'service',
        'document_type', 'document_number', 'email',
        'nit', 'nrc', 'dui_expedition_date', 'dui_expedition_place',
        'nationality', 'marital_status', 'spouse_name',
        'occupation', 'workplace', 'position', 'monthly_income',
        'boss_name', 'work_phone', 'work_address',
        'billing_address',
        'latitude', 'longitude', 'nro_luz',
        'installation_address', 'notes',
        'branch_id', 'zone_id', 'plan_id', 'contract_date',
        'departamento', 'municipio', 'distrito',
        'gps_token',
        'gps_token_expires_at',
        'docs_token',
        'docs_token_expires_at',
        'uploaded_docs',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'contract_date' => 'date',
        'dui_expedition_date' => 'date',
        'gps_token_expires_at' => 'datetime',
        'docs_token_expires_at' => 'datetime',
        'uploaded_docs' => 'array',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function workOrders()
    {
        return $this->hasManyThrough(WorkOrder::class, Ticket::class);
    }

    public function phones()
    {
        return $this->hasMany(ClientPhone::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
}