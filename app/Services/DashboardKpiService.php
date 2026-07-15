<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Movement;
use App\Models\Device;
use App\Models\Purchase;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

class DashboardKpiService
{
    public function getInventoryValue(): float
    {
        return (float) Product::sum('total_value');
    }

    public function getTodayEntries(): int
    {
        return Movement::whereDate('created_at', today())
            ->where('type', 'entry')
            ->count();
    }

    public function getTodayExits(): int
    {
        return Movement::whereDate('created_at', today())
            ->whereIn('type', ['exit', 'technician_out', 'requisition_out', 'damage', 'return_to_supplier', 'branch_allocation'])
            ->count();
    }

    public function getMonthlyPurchasesTotal(): float
    {
        return (float) Purchase::whereMonth('purchase_date', now()->month)
            ->whereYear('purchase_date', now()->year)
            ->sum('total');
    }

    public function getMonthlyPurchasesCount(): int
    {
        return Purchase::whereMonth('purchase_date', now()->month)
            ->whereYear('purchase_date', now()->year)
            ->count();
    }

    public function getRecentPurchases(int $limit = 5)
    {
        return Purchase::with('supplier')
            ->latest('purchase_date')
            ->limit($limit)
            ->get();
    }

    public function getTopProducts(int $limit = 10)
    {
        return Movement::select('product_id', DB::raw('SUM(quantity) as total_moved'))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_moved')
            ->limit($limit)
            ->get();
    }

    public function getDevicesByStatus(): array
    {
        return Device::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    public function getNewClientsToday(): int
    {
        return Client::whereDate('created_at', today())->count();
    }

    public function getNewClientsThisMonth(): int
    {
        return Client::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }
}
