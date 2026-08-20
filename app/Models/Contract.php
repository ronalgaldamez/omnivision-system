<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Contract extends Model
{
    protected $fillable = [
        'contract_digital_code',
        'ticket_id',
        'client_id',
        'plan_id',
        'zone_id',
        'service_type',
        'price',
        'status',
        'installation_address',
        'latitude',
        'longitude',
        'signed_pdf_path',
        'signed_at',
        'sent_at',
        'contract_terms',
        'contract_date',
        'payment_date',
        'created_by',
        'contract_type',
        'service_contracted',
        'access_type',
        'speed',
        'technology',
        'modem_serial',
        'term_months',
        'installation_cost',
        'benefit',
        'extra_tvs',
        'tv_install_fee',
        'monthly_extra_fee',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'contract_date' => 'date',
            'signed_at' => 'datetime',
            'sent_at' => 'datetime',
            'extra_tvs' => 'integer',
            'tv_install_fee' => 'decimal:2',
            'monthly_extra_fee' => 'decimal:2',
        ];
    }

    protected static function booted()
    {
        static::creating(function (Contract $contract) {
            if (empty($contract->contract_digital_code)) {
                $contract->contract_digital_code = static::generateDigitalCode();
            }
        });
    }

    public static function generateDigitalCode(): string
    {
        $year = now()->format('Y');
        $last = static::whereYear('created_at', $year)
            ->lockForUpdate()
            ->orderByDesc('contract_digital_code')
            ->value('contract_digital_code');

        $next = $last ? (int) Str::after($last, "CON-{$year}-") + 1 : 1;

        return "CON-{$year}-" . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    // ─── Relaciones ───

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ContractDocument::class);
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(ContractSignature::class);
    }

    public function charges(): HasMany
    {
        return $this->hasMany(ContractCharge::class);
    }

    // ─── Utilidades ───

    public function serviceTypeName(): string
    {
        return str_replace('_', ' ', $this->service_type);
    }

    public function isFullySigned(): bool
    {
        $hasClient = $this->signatures()->where('type', 'client')->exists();
        $hasSalesRep = $this->signatures()->where('type', 'sales_rep')->exists();

        return $hasClient && $hasSalesRep;
    }

    public function hasRequiredDocuments(): bool
    {
        $required = ['dui_front', 'dui_back'];
        $existing = $this->documents()->pluck('type')->toArray();

        return empty(array_diff($required, $existing));
    }

    public function isReadyToFinalize(): bool
    {
        return $this->hasRequiredDocuments() && $this->isFullySigned();
    }

    public function hasPdf(): bool
    {
        return app(\App\Services\ContractPdfService::class)->hasPdf($this);
    }

    /**
     * Cuota mensual total = precio del plan + recargo recurrente (ej. +$1 por TV extra).
     */
    public function monthlyTotal(): float
    {
        return (float) $this->price + (float) $this->monthly_extra_fee;
    }

    /**
     * Cargo único total de instalación = costo de instalación + cargo por TVs extra.
     */
    public function installFeeTotal(): float
    {
        return (float) ($this->installation_cost ?? 0) + (float) $this->tv_install_fee;
    }
}
