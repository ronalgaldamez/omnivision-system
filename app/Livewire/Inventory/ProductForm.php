<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use App\Models\ProductModel;
use App\Models\PackagingType;
use App\Models\ProductPackaging;
use App\Models\UnitOfMeasure;
use App\Models\Movement;
use App\Services\InventoryService;
use App\Traits\HasFormPersistence;
use App\Traits\ManagesProductPackaging;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProductForm extends Component
{
    use HasFormPersistence;
    use ManagesProductPackaging;

    public $editingId = null;

    public $productList = [];

    public bool $hasUnsavedChanges = false;

    // Campos del formulario actual
    public $currentName = '';

    public $currentUnit = 'unidad';

    public $currentMeasureValue = null;

    public $currentStockMin = 0;

    public $currentStockMax = null;

    public $currentDescription = '';

    public $currentBrandId = '';

    public $currentModelId = '';

    public $currentCategoryId = '';

    public $currentPackagingTypeId = '';

    public $currentPackagingQuantity = 1;

    public $showImport = false;

    public $importUrl = '';

    public $importError = null;

    public $showImportPreview = false;

    public $importPreview = [];

    public $importSkipped = 0;

    public $importSearch = '';

    public $importStatusFilter = '';

    // Modal de búsqueda de modelo
    public $showModelModal = false;

    public $modelSearchTerm = '';

    public $modelSearchResults = [];

    public $selectedModelDisplay = '';

    // Búsqueda de categoría
    public $categorySearch = '';

    public $categoryResults = [];

    public $showCategoryModal = false;

    public $categoryList = [];

    public $categoryListSearch = '';

    // Búsqueda de marca
    public $brandSearch = '';

    public $brandResults = [];

    public $showBrandModal = false;

    public $brandList = [];

    public $brandListSearch = '';

    // Modal de confirmación de acciones (editar/eliminar)
    public $showConfirmModal = false;

    public $modalAction = null;

    public $modalItemIndex = null;

    public $modalMessage = '';

    // Modal de confirmación de guardado (similar a compras)
    public $showSaveConfirmModal = false;

    protected $rules = [
        'currentName' => 'required|string|max:255',
        'currentUnit' => 'required|string|max:50',
        'currentStockMin' => 'required|integer|min:0',
        'currentStockMax' => 'nullable|integer|min:0',
        'currentDescription' => 'nullable|string',
        'currentBrandId' => 'nullable|exists:brands,id',
        'currentModelId' => 'nullable|exists:product_models,id',
        'currentCategoryId' => 'nullable|exists:categories,id',
    ];

    protected function persistableProperties(): array
    {
        return [
            'currentName', 'currentUnit',
            'currentStockMin', 'currentStockMax', 'currentDescription',
            'currentBrandId', 'currentModelId', 'currentCategoryId',
            'currentPackagingTypeId', 'currentPackagingQuantity',
            'selectedModelDisplay', 'productList',
            'categorySearch', 'brandSearch',
        ];
    }

    protected function detectUnsavedChanges(): bool
    {
        if ($this->editingId) {
            return false;
        }
        return ! empty($this->productList) || $this->currentName !== '';
    }

    public function updated($name, $value): void
    {
        if (in_array($name, $this->persistableProperties(), true)) {
            $this->persistState();
        }
    }

    public function mount($id = null)
    {
        if ($id) {
            $this->editingId = $id;
            $product = Product::findOrFail($id);
            $this->currentName = $product->name;
            $this->currentUnit = $product->unit_of_measure ?? 'unidad';
            $this->currentStockMin = intval($product->stock_min ?? 0);
            $this->currentStockMax = intval($product->stock_max ?? 0);
            $this->currentDescription = $product->description;
            $this->currentBrandId = $product->brand_id;
            $this->currentModelId = $product->model_id;
            $this->currentCategoryId = $product->category_id;
            if ($this->currentModelId) {
                $model = ProductModel::with('brand', 'category')->find($this->currentModelId);
                if ($model) {
                    $this->selectedModelDisplay = "{$model->brand->name} - {$model->name} - {$model->category->name}";
                }
            }
            $this->currentProductId = $product->id;
            $this->loadPackagingsForProduct($product->id);
            $this->initPackagingState();
        } else {
            $this->productList = [];
            $this->resetCurrent();
            $this->initPackagingState();
            if (session()->has($this->persistenceKey())) {
                $this->restorePersistedState();
            }
        }
    }

    public function resetCurrent()
    {
        $this->currentName = '';
        $this->currentUnit = 'unidad';
        $this->currentStockMin = 0;
        $this->currentStockMax = null;
        $this->currentDescription = '';
        $this->currentBrandId = '';
        $this->currentModelId = '';
        $this->currentCategoryId = '';
        $this->currentPackagingTypeId = '';
        $this->currentPackagingQuantity = 1;
        $this->selectedModelDisplay = '';
        $this->modelSearchTerm = '';
        $this->modelSearchResults = [];
    }

    // Modal de búsqueda de modelos
    public function openModelModal()
    {
        $this->showModelModal = true;
        $this->modelSearchTerm = '';
        $this->modelSearchResults = [];
    }

    public function updatedModelSearchTerm()
    {
        if (strlen($this->modelSearchTerm) >= 2) {
            $this->modelSearchResults = ProductModel::with('brand', 'category')
                ->where('name', 'like', '%'.$this->modelSearchTerm.'%')
                ->orWhereHas('brand', fn ($q) => $q->where('name', 'like', '%'.$this->modelSearchTerm.'%'))
                ->orWhereHas('category', fn ($q) => $q->where('name', 'like', '%'.$this->modelSearchTerm.'%'))
                ->limit(10)
                ->get();
        } else {
            $this->modelSearchResults = [];
        }
    }

    public function selectModel($id)
    {
        $model = ProductModel::with('brand', 'category')->find($id);
        if ($model) {
            $this->currentModelId = $model->id;
            $this->currentBrandId = $model->brand_id;
            $this->currentCategoryId = $model->category_id;
            $this->selectedModelDisplay = "{$model->brand->name} - {$model->name} - {$model->category->name}";
            $this->showModelModal = false;
            $this->persistState();
        }
    }

    public function clearModelSelection()
    {
        $this->currentModelId = '';
        $this->currentBrandId = '';
        $this->currentCategoryId = '';
        $this->selectedModelDisplay = '';
        $this->persistState();
    }

    // ==================== CATEGORÍA ====================
    public function updatedCategorySearch()
    {
        if (strlen($this->categorySearch) >= 2) {
            $this->categoryResults = \App\Models\Category::where('name', 'like', '%'.$this->categorySearch.'%')
                ->orderBy('name')->limit(10)->get();
        } else {
            $this->categoryResults = [];
        }
    }

    public function selectCategory($id)
    {
        $cat = \App\Models\Category::find($id);
        if ($cat) {
            $this->currentCategoryId = $cat->id;
            $this->categorySearch = $cat->name;
            $this->categoryResults = [];
            $this->showCategoryModal = false;
            $this->persistState();
        }
    }

    public function clearCategory()
    {
        $this->currentCategoryId = '';
        $this->categorySearch = '';
        $this->categoryResults = [];
    }

    public function openCategoryModal()
    {
        $this->categoryListSearch = '';
        $this->categoryList = \App\Models\Category::orderBy('name')->take(50)->get();
        $this->showCategoryModal = true;
    }

    public function closeCategoryModal()
    {
        $this->showCategoryModal = false;
        $this->categoryListSearch = '';
        $this->categoryList = [];
    }

    public function updatedCategoryListSearch()
    {
        if (strlen($this->categoryListSearch) >= 2) {
            $this->categoryList = \App\Models\Category::where('name', 'like', '%'.$this->categoryListSearch.'%')
                ->orderBy('name')->take(50)->get();
        } else {
            $this->categoryList = \App\Models\Category::orderBy('name')->take(50)->get();
        }
    }

    // ==================== MARCA ====================
    public function updatedBrandSearch()
    {
        if (strlen($this->brandSearch) >= 2) {
            $this->brandResults = \App\Models\Brand::where('name', 'like', '%'.$this->brandSearch.'%')
                ->orderBy('name')->limit(10)->get();
        } else {
            $this->brandResults = [];
        }
    }

    public function selectBrand($id)
    {
        $brand = \App\Models\Brand::find($id);
        if ($brand) {
            $this->currentBrandId = $brand->id;
            $this->brandSearch = $brand->name;
            $this->brandResults = [];
            $this->showBrandModal = false;
            $this->persistState();
        }
    }

    public function clearBrand()
    {
        $this->currentBrandId = '';
        $this->brandSearch = '';
        $this->brandResults = [];
    }

    public function openBrandModal()
    {
        $this->brandListSearch = '';
        $this->brandList = \App\Models\Brand::orderBy('name')->take(50)->get();
        $this->showBrandModal = true;
    }

    public function closeBrandModal()
    {
        $this->showBrandModal = false;
        $this->brandListSearch = '';
        $this->brandList = [];
    }

    public function updatedBrandListSearch()
    {
        if (strlen($this->brandListSearch) >= 2) {
            $this->brandList = \App\Models\Brand::where('name', 'like', '%'.$this->brandListSearch.'%')
                ->orderBy('name')->take(50)->get();
        } else {
            $this->brandList = \App\Models\Brand::orderBy('name')->take(50)->get();
        }
    }

    public function addToList()
    {
        $this->validate();

        $nameKey = strtolower(trim($this->currentName));
        $exists = collect($this->productList)->contains(fn ($p) => strtolower(trim($p['name'])) === $nameKey);
        if ($exists) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Ese producto ya está en la lista.']);
            return;
        }

        $this->productList[] = [
            'name' => $this->currentName,
            'unit_of_measure' => $this->currentUnit,
            'measure_value' => $this->currentMeasureValue,
            'stock_min' => $this->currentStockMin,
            'stock_max' => $this->currentStockMax,
            'description' => $this->currentDescription,
            'brand_id' => $this->currentBrandId,
            'model_id' => $this->currentModelId,
            'category_id' => $this->currentCategoryId,
            'packaging_type_id' => $this->currentPackagingTypeId ?: null,
            'packaging_quantity' => $this->currentPackagingTypeId ? $this->currentPackagingQuantity : null,
        ];

        $this->resetCurrent();
        $this->persistState();
        $this->dispatch('productAdded');
    }

    public function confirmAction($action, $index = null)
    {
        $this->modalAction = $action;
        $this->modalItemIndex = $index;

        if ($action === 'edit') {
            $this->modalMessage = '¿Editar este producto? Los datos se cargarán en el formulario para modificarlos.';
        } elseif ($action === 'delete') {
            $this->modalMessage = '¿Eliminar este producto de la lista?';
        } elseif ($action === 'clear_list') {
            $this->modalMessage = '¿Limpiar toda la lista de productos pendientes? Se perderán los productos no guardados.';
        }

        $this->showConfirmModal = true;
    }

    public function executeAction()
    {
        if ($this->modalAction === 'delete') {
            $this->removeFromList($this->modalItemIndex);
            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Producto eliminado de la lista.']);
        } elseif ($this->modalAction === 'edit') {
            $this->editItem($this->modalItemIndex);
            $this->dispatch('show-toast', ['type' => 'info', 'message' => 'Producto cargado para edición.']);
        } elseif ($this->modalAction === 'clear_list') {
            $this->clearList();
        }
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->showConfirmModal = false;
        $this->modalAction = null;
        $this->modalItemIndex = null;
        $this->modalMessage = '';
    }

    public function editItem($index)
    {
        $item = $this->productList[$index];
        $this->currentName = $item['name'];
        $this->currentUnit = $item['unit_of_measure'];
        $this->currentMeasureValue = $item['measure_value'];
        $this->currentStockMin = $item['stock_min'];
        $this->currentStockMax = $item['stock_max'];
        $this->currentDescription = $item['description'];
        $this->currentBrandId = $item['brand_id'];
        $this->currentModelId = $item['model_id'];
        $this->currentCategoryId = $item['category_id'];
        $this->currentPackagingTypeId = $item['packaging_type_id'] ?? '';
        $this->currentPackagingQuantity = $item['packaging_quantity'] ?? 1;
        if ($this->currentModelId) {
            $model = ProductModel::with('brand', 'category')->find($this->currentModelId);
            if ($model) {
                $this->selectedModelDisplay = "{$model->brand->name} - {$model->name} - {$model->category->name}";
            }
        }
        $this->removeFromList($index, false);
    }

    public function removeFromList($index, $showMessage = true)
    {
        unset($this->productList[$index]);
        $this->productList = array_values($this->productList);
        $this->persistState();
    }

    public function clearList()
    {
        $this->productList = [];
        $this->persistState();
        $this->dispatch('show-toast', ['type' => 'info', 'message' => 'Lista de productos limpiada.']);
    }

    // Confirmación para guardar múltiples productos
    public function confirmSaveAll()
    {
        if (empty($this->productList)) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Agrega al menos un producto antes de guardar.']);

            return;
        }
        $this->showSaveConfirmModal = true;
    }

    public function saveAll()
    {
        foreach ($this->productList as $prod) {
            $prod['brand_id'] = $prod['brand_id'] ?: null;
            $prod['model_id'] = $prod['model_id'] ?: null;
            $prod['category_id'] = $prod['category_id'] ?: null;

            $packagingTypeId = $prod['packaging_type_id'] ?? null;
            $packagingQty = $prod['packaging_quantity'] ?? null;
            $initialStock = (float) ($prod['stock'] ?? 0);
            $initialCost = $prod['costo'] ?? null;

            unset($prod['packaging_type_id'], $prod['packaging_quantity'], $prod['stock'], $prod['costo']);

            $product = Product::create($prod);

            if ($packagingTypeId && $packagingQty > 0) {
                $type = PackagingType::find($packagingTypeId);
                $name = ($type?->name ?? 'Empaque').' x'.rtrim(rtrim(number_format($packagingQty, 4), '0'), '.');
                ProductPackaging::create([
                    'product_id' => $product->id,
                    'packaging_type_id' => $packagingTypeId,
                    'name' => $name,
                    'quantity_in_base_unit' => $packagingQty,
                    'is_default_for_purchase' => true,
                ]);
            }

            if ($initialStock > 0) {
                $baseStock = ($packagingTypeId && $packagingQty > 0)
                    ? $initialStock * (float) $packagingQty
                    : $initialStock;

                $rawCost = ($initialCost !== null && $initialCost !== '') ? (float) $initialCost : 0;
                $perUnitCost = $rawCost;
                if ($packagingTypeId && $packagingQty > 0) {
                    $perUnitCost = $rawCost / (float) $packagingQty;
                }

                $movement = Movement::create([
                    'product_id' => $product->id,
                    'type' => 'entry',
                    'quantity' => $baseStock,
                    'description' => 'Inventario inicial',
                    'user_id' => Auth::id(),
                    'reference_type' => 'initial_stock',
                    'reference_id' => $product->id,
                ]);

                app(InventoryService::class)->processPurchaseEntry($product, $baseStock, $perUnitCost, $movement);
            }
        }
        $this->clearPersistedState();
        $this->dispatch('clear-unsaved-changes');
        $this->js('window.__suppressBeforeUnload = true');
        $this->dispatch('show-toast', ['type' => 'success', 'message' => count($this->productList).' producto(s) creado(s) correctamente.']);
        $this->redirectRoute('products.index');
    }

    // Confirmación para actualizar un producto (edición)
    public function confirmUpdate()
    {
        $this->validate();
        $this->showSaveConfirmModal = true;
    }

    public function update()
    {
        $product = Product::findOrFail($this->editingId);
        $product->update([
            'name' => $this->currentName,
            'unit_of_measure' => $this->currentUnit,
            'measure_value' => $this->currentMeasureValue,
            'stock_min' => $this->currentStockMin,
            'stock_max' => $this->currentStockMax,
            'description' => $this->currentDescription,
            'brand_id' => $this->currentBrandId ?: null,
            'model_id' => $this->currentModelId ?: null,
            'category_id' => $this->currentCategoryId ?: null,
        ]);
        $this->clearPersistedState();
        $this->dispatch('clear-unsaved-changes');
        $this->js('window.__suppressBeforeUnload = true');
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Producto actualizado correctamente.']);
        $this->redirectRoute('products.index');
    }

    public function render()
    {
        $units = UnitOfMeasure::where('is_active', true)->orderBy('name')->get();

        return view('livewire.inventory.products.form', compact('units'))->layout('components.layouts.app');
    }

    // ==================== IMPORTAR DESDE GOOGLE SHEETS (CSV) ====================
    public function importFromUrl()
    {
        $this->validate([
            'importUrl' => 'required|url',
        ]);

        $this->runImport();
    }

    public function refreshImport()
    {
        $this->runImport();
    }

    private function runImport()
    {
        $this->importError = null;
        $this->importSearch = '';
        $this->importStatusFilter = '';

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(20)->get($this->importUrl);
            if (! $response->ok()) {
                $this->importError = 'No se pudo leer la URL. Verificá que esté publicada como CSV.';
                return;
            }

            $content = $response->body();
            if (stripos($content, '<html') !== false || stripos($content, '<table') !== false) {
                $this->importError = 'La URL devolvió una página web, no un CSV. Publicá la hoja en formato "CSV", no "Página web".';
                return;
            }

            $parsed = $this->parseCsvRows($content);
            if (empty($parsed['rows'])) {
                $this->importError = 'No se encontraron filas válidas. Revisá que la primera fila tenga encabezados.';
                return;
            }

            if (! array_key_exists('name', $parsed['colMap'])) {
                $this->importError = 'No se reconoció la columna de nombre. Encabezados detectados: '.implode(', ', array_filter($parsed['headers'])).'.';
                return;
            }

            $unitsMap = [];
            foreach (UnitOfMeasure::all() as $u) {
                $unitsMap[strtolower($u->code)] = $u->code;
                $unitsMap[strtolower($u->name)] = $u->code;
                if ($u->symbol) {
                    $unitsMap[strtolower($u->symbol)] = $u->code;
                }
            }

            $packagingMap = [];
            foreach (PackagingType::all() as $pt) {
                $packagingMap[strtolower(trim($pt->name))] = $pt->id;
            }

            $existingSkus = Product::whereNotNull('sku')->pluck('sku')->map(fn ($s) => strtolower(trim($s)))->all();
            $existingNames = Product::pluck('name')->map(fn ($n) => strtolower(trim($n)))->all();

            $pendingSkus = collect($this->productList)->pluck('sku')->filter()->map(fn ($s) => strtolower(trim($s)))->all();
            $pendingNames = collect($this->productList)->pluck('name')->map(fn ($n) => strtolower(trim($n)))->all();

            $existingSkus = array_fill_keys(array_merge($existingSkus, $pendingSkus), true);
            $existingNames = array_fill_keys(array_merge($existingNames, $pendingNames), true);

            $this->importPreview = [];
            $this->importSkipped = 0;
            foreach ($parsed['rows'] as $row) {
                $name = $row['name'] ?? null;
                if (! $name) {
                    $this->importSkipped++;
                    continue;
                }

                $sku = $row['sku'] ?: null;
                $nameKey = strtolower(trim($name));
                $skuKey = $sku ? strtolower(trim($sku)) : null;

                $isExisting = false;
                if (($skuKey && isset($existingSkus[$skuKey])) || isset($existingNames[$nameKey])) {
                    $isExisting = true;
                }

                $existingNames[$nameKey] = true;
                if ($skuKey) {
                    $existingSkus[$skuKey] = true;
                }

                $packagingTypeId = '';
                $pkgName = $row['packaging_type'] ?? null;
                if ($pkgName && isset($packagingMap[strtolower(trim($pkgName))])) {
                    $packagingTypeId = $packagingMap[strtolower(trim($pkgName))];
                }
                $packagingQty = $row['packaging_quantity'] ?? '';
                $stock = $row['stock'] ?? null;
                $costo = $row['costo'] ?? null;

                // Si NO hay empaque pero "cant_por_empaque" trae un número, la fila viene corrida:
                // el stock quedó en "cant_por_empaque" y el costo en "stock".
                if ($packagingTypeId === '' && is_numeric($packagingQty) && (float) $packagingQty > 0) {
                    $stock = $packagingQty;
                    $costo = $row['stock'] ?? null;
                    $packagingQty = '';
                }

                $this->importPreview[] = [
                    'name' => $name,
                    'sku' => $sku,
                    'unit_of_measure' => $this->resolveUnitCode($row['unit'] ?? null, $unitsMap),
                    'measure_value' => null,
                    'stock_min' => (int) ($row['stock_min'] ?? 0),
                    'stock_max' => ($row['stock_max'] !== null && $row['stock_max'] !== '') ? (int) $row['stock_max'] : null,
                    'description' => $row['description'] ?? null,
                    'stock' => $stock,
                    'costo' => $costo,
                    'brand_id' => null,
                    'model_id' => null,
                    'category_id' => null,
                    'packaging_type_id' => $packagingTypeId,
                    'packaging_quantity' => $packagingQty,
                    'status' => $isExisting ? 'existing' : 'new',
                ];
            }

            if (empty($this->importPreview)) {
                $this->importError = 'No se encontraron productos con nombre válido.';
                return;
            }

            $this->showImportPreview = true;
        } catch (\Exception $e) {
            $this->importError = 'Error al importar: '.$e->getMessage();
        }
    }

    public function confirmImport()
    {
        $new = [];
        $skipped = 0;
        foreach ($this->importPreview as $row) {
            if (($row['status'] ?? 'new') === 'existing') {
                $skipped++;
                continue;
            }
            unset($row['status']);
            $new[] = $row;
        }

        $this->productList = array_merge($this->productList, $new);
        $count = count($new);
        $this->importPreview = [];
        $this->importSkipped = 0;
        $this->showImportPreview = false;
        $this->persistState();

        $message = "{$count} producto(s) agregado(s) a la lista.";
        if ($skipped > 0) {
            $message .= " {$skipped} omitido(s) por ya existir.";
        }
        $this->dispatch('show-toast', ['type' => 'success', 'message' => $message]);
    }

    public function cancelImport()
    {
        $this->importPreview = [];
        $this->importSkipped = 0;
        $this->showImportPreview = false;
        $this->importSearch = '';
        $this->importStatusFilter = '';
    }

    private function resolveUnitCode($value, $unitsMap)
    {
        if (! $value) return 'unidad';
        $v = strtolower(trim($value));
        return $unitsMap[$v] ?? 'unidad';
    }

    private function parseCsvRows($content)
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $lines = array_values(array_filter(array_map('trim', $lines)));
        if (count($lines) < 2) return ['headers' => [], 'colMap' => [], 'rows' => []];

        $headers = str_getcsv(array_shift($lines));
        $headers = array_map(fn ($h) => $this->normalizeHeader($h), $headers);
        $colMap = $this->buildColumnMap($headers);

        $rows = [];
        foreach ($lines as $line) {
            $cells = str_getcsv($line);
            if (count($cells) < count($headers)) {
                $cells = array_pad($cells, count($headers), '');
            }
            $rows[] = [
                'name' => $this->cell($cells, $colMap['name'] ?? null),
                'sku' => $this->cell($cells, $colMap['sku'] ?? null),
                'unit' => $this->cell($cells, $colMap['unit'] ?? null),
                'description' => $this->cell($cells, $colMap['description'] ?? null),
                'stock_min' => $this->cell($cells, $colMap['stock_min'] ?? null),
                'stock_max' => $this->cell($cells, $colMap['stock_max'] ?? null),
                'stock' => $this->cell($cells, $colMap['stock'] ?? null),
                'costo' => $this->cell($cells, $colMap['costo'] ?? null),
                'packaging_type' => $this->cell($cells, $colMap['packaging_type'] ?? null),
                'packaging_quantity' => $this->cell($cells, $colMap['packaging_quantity'] ?? null),
            ];
        }
        return ['headers' => $headers, 'colMap' => $colMap, 'rows' => $rows];
    }

    private function normalizeHeader($h)
    {
        $h = strtolower(trim($h));
        $h = strtr($h, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', 'ü' => 'u']);
        $h = preg_replace('/[^a-z0-9]+/', '_', $h);
        return trim($h, '_');
    }

    private function buildColumnMap($headers)
    {
        $aliases = [
            'name' => ['nombre', 'name', 'producto', 'product', 'material', 'articulo', 'item', 'nombre_del_producto', 'nombre_producto'],
            'sku' => ['sku', 'codigo', 'code'],
            'unit' => ['unidad', 'unit', 'medida', 'um', 'unidad_de_medida', 'unit_of_measure'],
            'description' => ['descripcion', 'description', 'detalle'],
            'stock_min' => ['stock_min', 'minimo', 'min'],
            'stock_max' => ['stock_max', 'maximo', 'max'],
            'stock' => ['stock', 'existencia', 'cantidad', 'inventario', 'qty', 'cant'],
            'costo' => ['costo', 'cost', 'costo_unitario', 'costo_promedio', 'precio', 'costo_unit'],
            'packaging_type' => ['empaque', 'empaque_tipo', 'tipo_empaque', 'packaging', 'envase'],
            'packaging_quantity' => ['cant_por_empaque', 'cantidad_por_empaque', 'cant_empaque', 'unidades_por_empaque', 'cantidad_empaque'],
        ];

        $map = [];
        foreach ($headers as $i => $h) {
            foreach ($aliases as $canonical => $names) {
                if (in_array($h, $names, true)) {
                    $map[$canonical] = $i;
                    break;
                }
            }
        }
        return $map;
    }

    private function cell($cells, $index)
    {
        if ($index === null || ! isset($cells[$index])) return null;
        $v = trim($cells[$index]);
        return $v === '' ? null : $v;
    }
}
