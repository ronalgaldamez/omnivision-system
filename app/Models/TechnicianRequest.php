<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TechnicianRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'technician_id',
        'work_order_id',
        'status',
        'request_code',
        'code_expires_at',
        'notes'
    ];

    protected $casts = [
        'code_expires_at' => 'datetime',
    ];

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function products()
    {
        return $this->hasMany(RequestProduct::class);
    }

    public function isExpired()
    {
        return $this->code_expires_at && now()->greaterThan($this->code_expires_at);
    }

    public function generateCode()
    {
        // Generar código único de 6 caracteres alfanuméricos mayúsculas
        do {
            $code = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        } while (self::where('request_code', $code)->exists());

        $this->request_code = $code;
        $this->code_expires_at = now()->addHours(24);
        $this->save();
    }

    public function getQrCodeAttribute()
    {
        if (!$this->request_code) {
            return '';
        }
        // Generar SVG y convertirlo a base64 para usar como src de imagen
        $svg = QrCode::size(300)->generate($this->request_code);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    protected static function booted()
    {
        static::creating(function ($request) {
            $request->request_code = $request->generateUniqueCode();
        });
    }

    public function generateUniqueCode($length = 6)
    {
        do {
            $code = strtoupper(substr(bin2hex(random_bytes($length)), 0, $length));
        } while (self::where('request_code', $code)->exists());
        return $code;
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
