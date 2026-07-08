<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Setting;
use App\Models\ServiceType;
use App\Models\KnowledgeBaseArticle;

class SettingsManager extends Component
{
    // ========== PROPIEDADES EXISTENTES ==========
    public $otRequired = false;
    public $invoiceRequiredForDevices = false;
    public $modules = [];
    public $nocPollingInterval = 30;

    // ========== PROPIEDADES DE TIPOS DE SERVICIO ==========
    public $serviceTypes;
    public $showServiceModal = false;
    public $editingServiceId = null;
    public $serviceName = '';
    public $serviceRequiresNoc = [];
    public $serviceRequiresNocModal = false;

    // ========== PROPIEDADES DE BASE DE CONOCIMIENTO ==========
    public $articles;
    public $showKbModal = false;
    public $editingArticleId = null;
    public $kbTitle = '';
    public $kbContent = '';
    public $kbPriority = '';
    public $kbCategory = '';
    public $selectedKbServiceTypes = [];

    // ========== REGLAS DE VALIDACIÓN ==========
    protected $rules = [
        'otRequired' => 'boolean',
        'modules.*' => 'boolean',
        'nocPollingInterval' => 'required|integer|min:5|max:300',
        'serviceName' => 'required|string|max:255|unique:service_types,name',
        'serviceRequiresNoc' => 'boolean',
        // Reglas para KB
        'kbTitle' => 'required|string|max:255',
        'kbContent' => 'required|string',
        'kbPriority' => 'nullable|in:P1,P2,P3,P4',
        'kbCategory' => 'nullable|string|max:100',
        'selectedKbServiceTypes' => 'array',
        'selectedKbServiceTypes.*' => 'exists:service_types,id',
    ];

    public function mount()
    {
        // Configuración existente
        $this->otRequired = Setting::get('ot_required', 'false') === 'true';
        $this->invoiceRequiredForDevices = Setting::get('invoice_required_for_devices', 'false') === 'true';

        $configModules = config('modules.modules', []);
        foreach ($configModules as $key => $default) {
            $dbValue = Setting::get('module_' . $key);
            if ($dbValue !== null) {
                $this->modules[$key] = $dbValue === 'true';
            } else {
                $this->modules[$key] = $default;
            }
        }

        $this->nocPollingInterval = (int) Setting::get('noc_polling_interval', 30);

        // Cargar tipos de servicio y artículos
        $this->loadServiceTypes();
        $this->loadArticles();
    }

    // ========== MÉTODOS EXISTENTES (sin cambios) ==========
    public function updatedOtRequired()
    {
        Setting::set('ot_required', $this->otRequired ? 'true' : 'false');
        $this->dispatch('show-toast', type: 'success', message: 'Configuración guardada.');
    }

    public function updatedInvoiceRequiredForDevices()
    {
        Setting::set('invoice_required_for_devices', $this->invoiceRequiredForDevices ? 'true' : 'false');
        $this->dispatch('show-toast', type: 'success', message: 'Configuración guardada.');
    }

    public function updatedModules($value, $key)
    {
        Setting::set('module_' . $key, $value ? 'true' : 'false');
        $this->dispatch('show-toast', type: 'success', message: "Módulo {$key} " . ($value ? 'activado' : 'desactivado'));
        \Artisan::call('config:clear');
        \Artisan::call('route:clear');
        \Artisan::call('view:clear');
    }

    public function updatedNocPollingInterval()
    {
        $this->validate(['nocPollingInterval' => 'required|integer|min:5|max:300']);
        Setting::set('noc_polling_interval', (string) $this->nocPollingInterval);
        $this->dispatch('show-toast', type: 'success', message: 'Intervalo de notificaciones guardado.');
    }

    // ========== MÉTODOS PARA TIPOS DE SERVICIO ==========
    public function loadServiceTypes()
    {
        $this->serviceTypes = ServiceType::orderBy('name')->get();
        foreach ($this->serviceTypes as $type) {
            $this->serviceRequiresNoc[$type->id] = $type->requires_noc;
        }
    }

    public function updatedServiceRequiresNoc($value, $id)
    {
        $serviceType = ServiceType::find($id);
        if ($serviceType) {
            $serviceType->update(['requires_noc' => $value]);
            $this->dispatch('show-toast', type: 'success', message: "Tipo de servicio '{$serviceType->name}' actualizado.");
        }
    }

    public function openServiceModal()
    {
        $this->resetServiceForm();
        $this->showServiceModal = true;
    }

    public function editService($id)
    {
        $serviceType = ServiceType::findOrFail($id);
        $this->editingServiceId = $serviceType->id;
        $this->serviceName = $serviceType->name;
        $this->serviceRequiresNocModal = $serviceType->requires_noc;
        $this->showServiceModal = true;
    }

    public function saveService()
    {
        $this->validate([
            'serviceName' => 'required|string|max:255|unique:service_types,name,' . $this->editingServiceId,
            'serviceRequiresNocModal' => 'boolean',
        ]);

        if ($this->editingServiceId) {
            $serviceType = ServiceType::findOrFail($this->editingServiceId);
            $serviceType->update([
                'name' => $this->serviceName,
                'requires_noc' => $this->serviceRequiresNocModal,
            ]);
            $message = "Tipo de servicio '{$this->serviceName}' actualizado.";
        } else {
            ServiceType::create([
                'name' => $this->serviceName,
                'requires_noc' => $this->serviceRequiresNocModal,
            ]);
            $message = "Tipo de servicio '{$this->serviceName}' creado.";
        }

        $this->dispatch('show-toast', type: 'success', message: $message);
        $this->showServiceModal = false;
        $this->resetServiceForm();
        $this->loadServiceTypes();
    }

    public function deleteService($id)
    {
        $serviceType = ServiceType::findOrFail($id);
        $serviceType->delete();
        $this->dispatch('show-toast', type: 'success', message: "Tipo de servicio '{$serviceType->name}' eliminado.");
        $this->loadServiceTypes();
    }

    public function closeServiceModal()
    {
        $this->showServiceModal = false;
        $this->resetServiceForm();
    }

    private function resetServiceForm()
    {
        $this->editingServiceId = null;
        $this->serviceName = '';
        $this->serviceRequiresNocModal = false;
    }

    // ========== MÉTODOS PARA BASE DE CONOCIMIENTO ==========
    public function loadArticles()
    {
        $this->articles = KnowledgeBaseArticle::with('serviceTypes')->orderBy('title')->get();
    }

    public function openKbModal()
    {
        $this->resetKbForm();
        $this->showKbModal = true;
    }

    public function editArticle($id)
    {
        $article = KnowledgeBaseArticle::with('serviceTypes')->findOrFail($id);
        $this->editingArticleId = $article->id;
        $this->kbTitle = $article->title;
        $this->kbContent = $article->content;
        $this->kbPriority = $article->priority ?? '';
        $this->kbCategory = $article->category;
        $this->selectedKbServiceTypes = $article->serviceTypes->pluck('id')->toArray();
        $this->showKbModal = true;
    }

    public function saveArticle()
    {
        $this->validate([
            'kbTitle' => 'required|string|max:255',
            'kbContent' => 'required|string',
            'kbPriority' => 'nullable|in:P1,P2,P3,P4',
            'kbCategory' => 'nullable|string|max:100',
            'selectedKbServiceTypes' => 'array',
            'selectedKbServiceTypes.*' => 'exists:service_types,id',
        ]);

        if ($this->editingArticleId) {
            $article = KnowledgeBaseArticle::findOrFail($this->editingArticleId);
            $article->update([
                'title' => $this->kbTitle,
                'content' => $this->kbContent,
                'priority' => $this->kbPriority,
                'category' => $this->kbCategory,
            ]);
            $article->serviceTypes()->sync($this->selectedKbServiceTypes);
            $message = "Artículo '{$this->kbTitle}' actualizado.";
        } else {
            $article = KnowledgeBaseArticle::create([
                'title' => $this->kbTitle,
                'content' => $this->kbContent,
                'priority' => $this->kbPriority,
                'category' => $this->kbCategory,
            ]);
            $article->serviceTypes()->sync($this->selectedKbServiceTypes);
            $message = "Artículo '{$this->kbTitle}' creado.";
        }

        $this->dispatch('show-toast', type: 'success', message: $message);
        $this->showKbModal = false;
        $this->resetKbForm();
        $this->loadArticles();
    }

    public function deleteArticle($id)
    {
        $article = KnowledgeBaseArticle::findOrFail($id);
        $article->delete();
        $this->dispatch('show-toast', type: 'success', message: "Artículo '{$article->title}' eliminado.");
        $this->loadArticles();
    }

    public function closeKbModal()
    {
        $this->showKbModal = false;
        $this->resetKbForm();
    }

    private function resetKbForm()
    {
        $this->editingArticleId = null;
        $this->kbTitle = '';
        $this->kbContent = '';
        $this->kbPriority = '';
        $this->kbCategory = '';
        $this->selectedKbServiceTypes = [];
    }

    public function render()
    {
        return view('livewire.admin.settings-manager')->layout('components.layouts.app');
    }
}