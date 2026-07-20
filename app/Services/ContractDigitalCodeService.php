<?php

namespace App\Services;

use App\Models\Contract;
use Illuminate\Support\Str;

class ContractDigitalCodeService
{
    /**
     * Genera un código único para el contrato.
     * Formato: CON-YYYY-XXXXX (ej: CON-2026-00001)
     */
    public function generate(): string
    {
        return Contract::generateDigitalCode();
    }

    /**
     * Valida que un código tenga el formato correcto.
     */
    public function validate(string $code): bool
    {
        return (bool) preg_match('/^CON-\d{4}-\d{5}$/', $code);
    }

    /**
     * Extrae el año de un código de contrato.
     */
    public function extractYear(string $code): ?string
    {
        if (! $this->validate($code)) {
            return null;
        }

        return explode('-', $code)[1];
    }

    /**
     * Extrae el número secuencial de un código de contrato.
     */
    public function extractNumber(string $code): ?int
    {
        if (! $this->validate($code)) {
            return null;
        }

        return (int) explode('-', $code)[2];
    }
}
