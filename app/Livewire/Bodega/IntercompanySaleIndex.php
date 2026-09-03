<?php

namespace App\Livewire\Bodega;

use App\Models\IntercompanySale;
use Livewire\Component;
use Livewire\WithPagination;

class IntercompanySaleIndex extends Component
{
    use WithPagination;

    public function render()
    {
        $sales = IntercompanySale::with('sellerBranch', 'buyerBranch', 'user', 'items')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.bodega.intercompany-sale-index', compact('sales'))
            ->layout('components.layouts.app');
    }
}
