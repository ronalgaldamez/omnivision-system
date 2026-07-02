<?php

use App\Models\ProductPackaging;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('product_packagings')
            ->select('product_id', DB::raw('COUNT(*) as total'))
            ->where('is_default_for_purchase', true)
            ->groupBy('product_id')
            ->having('total', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $ids = DB::table('product_packagings')
                ->where('product_id', $dup->product_id)
                ->where('is_default_for_purchase', true)
                ->orderBy('id')
                ->pluck('id');

            $keepId = $ids->shift();

            DB::table('product_packagings')
                ->whereIn('id', $ids->toArray())
                ->update(['is_default_for_purchase' => false]);
        }
    }

    public function down(): void
    {
        // irreversible intentionalmente
    }
};
