<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    protected $stats = [
        'imported' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    protected $brandCache = [];
    protected $categoryCache = [];

    public function model(array $row)
    {
        $brandId = $this->resolveBrand($row['marca'] ?? $row['brand'] ?? null);
        $categoryId = $this->resolveCategory($row['categoria'] ?? $row['category'] ?? null);

        if (!$brandId || !$categoryId) {
            $this->stats['skipped']++;
            $this->stats['errors'][] = "Fila {$row['name']}: marca o categoría no encontrada";
            return null;
        }

        $this->stats['imported']++;

        return new Product([
            'name' => $row['name'],
            'sku' => $row['sku'] ?? null,
            'description' => $row['description'] ?? $row['descripcion'] ?? null,
            'brand_id' => $brandId,
            'category_id' => $categoryId,
            'average_cost' => $row['cost'] ?? $row['costo'] ?? 0,
            'current_stock' => $row['stock'] ?? 0,
            'stock_min' => $row['stock_min'] ?? 0,
            'stock_max' => $row['stock_max'] ?? 0,
            'unit_of_measure' => $row['unit'] ?? $row['unidad'] ?? 'unit',
            'total_value' => ($row['cost'] ?? $row['costo'] ?? 0) * ($row['stock'] ?? 0),
        ]);
    }

    protected function resolveBrand($name)
    {
        if (!$name) return null;
        $key = strtolower(trim($name));
        if (isset($this->brandCache[$key])) {
            return $this->brandCache[$key];
        }
        $brand = Brand::whereRaw('LOWER(name) = ?', [$key])->first();
        $this->brandCache[$key] = $brand?->id;
        return $this->brandCache[$key];
    }

    protected function resolveCategory($name)
    {
        if (!$name) return null;
        $key = strtolower(trim($name));
        if (isset($this->categoryCache[$key])) {
            return $this->categoryCache[$key];
        }
        $category = Category::whereRaw('LOWER(name) = ?', [$key])->first();
        $this->categoryCache[$key] = $category?->id;
        return $this->categoryCache[$key];
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getStats()
    {
        return $this->stats;
    }
}
