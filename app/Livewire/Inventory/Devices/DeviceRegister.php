<?php

namespace App\Livewire\Inventory\Devices;

use App\Models\Device;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\DeviceStatus;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;

class DeviceRegister extends Component
{
    use WithFileUploads;

    public $product_id = '';
    public $purchase_id = '';
    public $productSearch = '';
    public $productResults = [];
    public $purchaseSearch = '';
    public $purchaseResults = [];
    public $showProductModal = false;
    public $productList = [];
    public $productListSearch = '';
    public $showPurchaseModal = false;
    public $purchaseList = [];
    public $purchaseListSearch = '';
    public $purchaseDeviceWarning = '';
    public $quantity = 1;
    public $rows = [];
    public $jsonFile = null;
    public $importMode = false;
    public $showConfirmSave = false;
    public $confirmMessage = '';
    public $existingMacs = [];

    public $draftRestored = false;

    public bool $skipDraftSave = false;

    public function mount($purchase_id = null)
    {
        if ($purchase_id) {
            $purchase = Purchase::with('supplier')->find($purchase_id);
            if ($purchase) {
                $this->purchase_id = $purchase->id;
                $this->purchaseSearch = $purchase->invoice_number . ' - ' . ($purchase->supplier?->name ?? '');
            }
        }

        if ($draft = session()->get('device_register_draft')) {
            $this->product_id = $draft['product_id'] ?? '';
            $this->productSearch = $draft['productSearch'] ?? '';
            $this->purchase_id = $draft['purchase_id'] ?? '';
            $this->purchaseSearch = $draft['purchaseSearch'] ?? '';
            $this->quantity = $draft['quantity'] ?? 1;
            $this->rows = $draft['rows'] ?? [];
            $this->draftRestored = true;
            $this->importMode = false;
        }
    }

    protected function rules()
    {
        return [
            'product_id' => 'required|exists:products,id',
            'rows.*.mac_address' => 'required|string|max:50',
            'rows.*.pon_sn' => 'nullable|string|max:50',
        ];
    }

    public function updatedProductSearch()
    {
        if (strlen($this->productSearch) >= 2) {
            $this->productResults = Product::where('name', 'like', '%' . $this->productSearch . '%')
                ->orWhere('sku', 'like', '%' . $this->productSearch . '%')
                ->limit(10)->get();
        } else {
            $this->productResults = [];
        }
    }

    public function selectProduct($id)
    {
        $product = Product::find($id);
        if ($product) {
            $this->product_id = $product->id;
            $this->productSearch = $product->name . ' (' . $product->sku . ')';
            $this->productResults = [];
            $this->generateRows();
        }
    }

    public function clearProduct()
    {
        $this->product_id = '';
        $this->productSearch = '';
        $this->productResults = [];
        $this->rows = [];
    }

    public function openProductModal()
    {
        $this->productListSearch = '';
        $this->productList = Product::orderBy('name')->take(50)->get();
        $this->showProductModal = true;
    }

    public function closeProductModal()
    {
        $this->showProductModal = false;
        $this->productListSearch = '';
        $this->productList = [];
    }

    public function updatedProductListSearch()
    {
        if (strlen($this->productListSearch) >= 2) {
            $this->productList = Product::where('name', 'like', '%' . $this->productListSearch . '%')
                ->orWhere('sku', 'like', '%' . $this->productListSearch . '%')
                ->orderBy('name')->take(50)->get();
        } else {
            $this->productList = Product::orderBy('name')->take(50)->get();
        }
    }

    public function selectProductFromList($id)
    {
        $this->selectProduct($id);
        $this->closeProductModal();
    }

    public function updatedPurchaseSearch()
    {
        if (strlen($this->purchaseSearch) >= 2) {
            $this->purchaseResults = Purchase::with('supplier')
                ->where('invoice_number', 'like', '%' . $this->purchaseSearch . '%')
                ->orWhereHas('supplier', fn($q) => $q->where('name', 'like', '%' . $this->purchaseSearch . '%'))
                ->limit(10)->get();
        } else {
            $this->purchaseResults = [];
        }
    }

    public function selectPurchase($id)
    {
        $this->purchase_id = $id;
        $purchase = Purchase::with('supplier')->find($id);
        $this->purchaseSearch = $purchase?->invoice_number . ' - ' . ($purchase?->supplier?->name ?? '');
        $this->purchaseResults = [];
        $this->showPurchaseModal = false;

        $existing = Device::with('product')
            ->where('purchase_id', $id)
            ->selectRaw('product_id, COUNT(*) as count')
            ->groupBy('product_id')
            ->get();

        if ($existing->isNotEmpty()) {
            $total = $existing->sum('count');
            $this->purchaseDeviceWarning = "{$total} dispositivo(s) ya registrado(s) con esta factura.";
        } else {
            $this->purchaseDeviceWarning = '';
        }
    }

    public function clearPurchase()
    {
        $this->purchase_id = '';
        $this->purchaseSearch = '';
        $this->purchaseDeviceWarning = '';
    }

    public function openPurchaseModal()
    {
        $this->purchaseListSearch = '';
        $this->purchaseList = Purchase::with('supplier')->latest()->take(50)->get();
        $this->showPurchaseModal = true;
    }

    public function closePurchaseModal()
    {
        $this->showPurchaseModal = false;
        $this->purchaseListSearch = '';
        $this->purchaseList = [];
    }

    public function updatedPurchaseListSearch()
    {
        if (strlen($this->purchaseListSearch) >= 2) {
            $this->purchaseList = Purchase::with('supplier')
                ->where('invoice_number', 'like', '%' . $this->purchaseListSearch . '%')
                ->orWhereHas('supplier', fn($q) => $q->where('name', 'like', '%' . $this->purchaseListSearch . '%'))
                ->latest()->take(50)->get();
        } else {
            $this->purchaseList = Purchase::with('supplier')->latest()->take(50)->get();
        }
    }

    public function updated($property)
    {
        if (in_array($property, ['showConfirmSave', 'showProductModal', 'showPurchaseModal', 'jsonFile', 'draftRestored', 'productResults', 'purchaseResults', 'productList', 'purchaseList', 'existingMacs', 'purchaseDeviceWarning'])) {
            return;
        }
        $this->saveDraft();
    }

    public function dehydrate()
    {
        if (!$this->skipDraftSave) {
            $this->saveDraft();
        }
    }

    private function saveDraft(): void
    {
        session()->put('device_register_draft', [
            'product_id' => $this->product_id,
            'productSearch' => $this->productSearch,
            'purchase_id' => $this->purchase_id,
            'purchaseSearch' => $this->purchaseSearch,
            'quantity' => $this->quantity,
            'rows' => $this->rows,
        ]);
    }

    public function generateRows()
    {
        $this->rows = [];
        for ($i = 0; $i < $this->quantity; $i++) {
            $this->rows[] = [
                'mac_address' => '',
                'pon_sn' => '',
                'default_ip' => '',
                'default_username' => '',
                'default_password' => '',
                'default_ssid1' => '',
                'default_lan_key' => '',
            ];
        }
    }

    public function updatedQuantity()
    {
        if ($this->product_id && $this->quantity > 0) {
            $this->generateRows();
        }
    }

    public function updatedJsonFile()
    {
        if (!$this->product_id) {
            $this->dispatch('show-toast', type: 'error', message: 'Seleccioná un producto antes de importar.');
            $this->jsonFile = null;
            return;
        }

        $this->validate(['jsonFile' => 'file|mimes:json,txt|max:2048']);
        $content = $this->jsonFile->get();
        $data = json_decode($content, true);

        if (!is_array($data)) {
            $this->dispatch('show-toast', type: 'error', message: 'El archivo JSON no es válido.');
            return;
        }

        $this->rows = [];
        foreach ($data as $entry) {
            if (isset($entry['mac_address'])) {
                $this->rows[] = [
                    'mac_address' => $entry['mac_address'] ?? '',
                    'pon_sn' => $entry['pon_sn'] ?? '',
                    'default_ip' => $entry['default_ip'] ?? '',
                    'default_username' => $entry['default_username'] ?? '',
                    'default_password' => $entry['default_password'] ?? '',
                    'default_ssid1' => $entry['default_ssid1'] ?? '',
                    'default_lan_key' => $entry['default_lan_key'] ?? '',
                ];
            }
        }

        $this->quantity = count($this->rows);
        if ($this->quantity === 0) {
            $this->dispatch('show-toast', type: 'error', message: 'No se encontraron dispositivos en el archivo.');
        } else {
            $this->dispatch('show-toast', type: 'success', message: "{$this->quantity} dispositivos cargados desde JSON.");
            $this->importMode = false;
        }
    }

    public function removeRow($index)
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
        $this->quantity = count($this->rows);
    }

    public function requestSave()
    {
        $this->validate();

        if (empty($this->rows)) {
            $this->dispatch('show-toast', type: 'error', message: 'Agregá al menos un dispositivo.');
            return;
        }

        $macs = array_column($this->rows, 'mac_address');
        $intraDuplicates = array_diff_assoc($macs, array_unique($macs));
        if (!empty($intraDuplicates)) {
            $this->dispatch('show-toast', type: 'error', message: 'MAC duplicadas en la carga: ' . implode(', ', array_unique($intraDuplicates)));
            return;
        }

        if ($this->purchase_id) {
            $existingInPurchase = Device::where('purchase_id', $this->purchase_id)->count();
            if ($existingInPurchase > 0) {
                $this->dispatch('show-toast', type: 'error', message: "La factura seleccionada ya tiene {$existingInPurchase} dispositivo(s) registrado(s). Usá otra factura o limpia la selección.");
                return;
            }
        }

        $this->existingMacs = Device::whereIn('mac_address', $macs)->pluck('mac_address')->toArray();

        if (!empty($this->existingMacs)) {
            $count = count($this->existingMacs);
            $this->dispatch('show-toast', type: 'error', message: "{$count} MAC(s) ya existen en el sistema. Revisá la carga y eliminá las duplicadas antes de guardar.");
            return;
        }

        $this->confirmMessage = '¿Guardar ' . count($this->rows) . ' dispositivo(s)?';
        $this->showConfirmSave = true;
    }

    public function confirmSave()
    {
        $this->showConfirmSave = false;

        DB::beginTransaction();
        try {
            foreach ($this->rows as $row) {
                Device::create([
                    'product_id' => $this->product_id,
                    'purchase_id' => $this->purchase_id ?: null,
                    'mac_address' => $row['mac_address'],
                    'pon_sn' => $row['pon_sn'] ?: null,
                    'default_ip' => $row['default_ip'] ?: null,
                    'default_username' => $row['default_username'] ?: null,
                    'default_password' => $row['default_password'] ?: null,
                    'default_ssid1' => $row['default_ssid1'] ?: null,
                    'default_lan_key' => $row['default_lan_key'] ?: null,
                ]);
            }

            DB::commit();
            $this->skipDraftSave = true;
            session()->forget('device_register_draft');
            $this->dispatch('show-toast', type: 'success', message: count($this->rows) . ' dispositivos registrados.');
            $this->reset(['rows', 'product_id', 'productSearch', 'purchase_id', 'purchaseSearch', 'quantity', 'jsonFile', 'existingMacs', 'purchaseDeviceWarning']);
            $this->quantity = 1;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function cancelSave()
    {
        $this->showConfirmSave = false;
        $this->dispatch('show-toast', type: 'info', message: 'Guardado cancelado.');
    }

    public function resetForm()
    {
        $this->skipDraftSave = true;
        session()->forget('device_register_draft');
        $this->reset(['rows', 'product_id', 'productSearch', 'purchase_id', 'purchaseSearch', 'quantity', 'jsonFile', 'existingMacs', 'purchaseDeviceWarning', 'importMode']);
        $this->quantity = 1;
        $this->dispatch('show-toast', type: 'info', message: 'Formulario limpiado.');
    }

    public function render()
    {
        $statuses = DeviceStatus::where('is_active', true)->get();
        return view('livewire.inventory.devices.device-register', compact('statuses'))->layout('components.layouts.app');
    }
}
