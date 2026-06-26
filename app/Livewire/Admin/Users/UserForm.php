<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use App\Models\User;
use App\Models\Role;
use App\Enums\PermissionEnum;
use Illuminate\Support\Facades\Hash;

class UserForm extends Component
{
    public $userId;
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $selectedRole = '';
    public $isActive = true;
    public $permissionsPersonalized = false;
    public $selectedPermissions = [];
    public $activeTab = '';

    public function mount($id = null)
    {
        if ($id) {
            $user = User::with(['roles', 'permissions'])->findOrFail($id);
            $this->userId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->isActive = $user->is_active;
            $this->selectedRole = $user->roles->first()->name ?? '';
            $this->permissionsPersonalized = $user->hasPersonalizedPermissions();

            if ($this->permissionsPersonalized) {
                $this->selectedPermissions = $user->permissions->pluck('name')->toArray();
            }
        }

        if (empty($this->activeTab)) {
            $this->activeTab = '';
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'selectedRole' => 'required|exists:roles,name',
            'isActive' => 'boolean',
            'permissionsPersonalized' => 'boolean',
            'selectedPermissions' => 'array',
            'selectedPermissions.*' => 'string',
        ];

        if ($this->userId) {
            $rules['email'] = 'required|email|unique:users,email,' . $this->userId;
            $rules['password'] = 'nullable|min:6|confirmed';
        }

        return $rules;
    }

    /**
     * Cuando se activa el toggle de personalización,
     * precargar los permisos del rol seleccionado.
     */
    public function updatedPermissionsPersonalized($value)
    {
        if ($value && $this->selectedRole) {
            $role = Role::findByName($this->selectedRole);
            $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        }
        $this->activeTab = '';
    }

    /**
     * Al cambiar de rol, si la personalización está activa,
     * recargar los permisos base desde el nuevo rol.
     */
    public function updatedSelectedRole($value)
    {
        if ($this->permissionsPersonalized && $value) {
            $role = Role::findByName($value);
            $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        }
    }

    public function save()
    {
        $this->validate();

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => $this->isActive,
        ];

        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }

        $user = User::updateOrCreate(['id' => $this->userId], $userData);
        $user->syncRoles([$this->selectedRole]);

        if ($this->permissionsPersonalized && !empty($this->selectedPermissions)) {
            $user->syncPermissions($this->selectedPermissions);
        } else {
            $user->syncPermissions([]);
        }

        session()->flash('message', 'Usuario guardado correctamente.');
    }

    /**
     * Todos los permisos del sistema organizados jerárquicamente:
     * Módulo → Acceso al módulo → Menú → Acciones
     * Se muestran todos, no solo los del rol, para poder agregar extras.
     */
    public function getRolePermissionsProperty(): array
    {
        $modules = [
            'Inventario'            => PermissionEnum::inventory(),
            'Proveedores / Compras' => PermissionEnum::suppliers(),
            'Órdenes de Trabajo'    => PermissionEnum::workOrders(),
            'Técnicos'              => PermissionEnum::technicians(),
            'Reportes'              => PermissionEnum::reports(),
            'Soporte / Tickets'     => PermissionEnum::support(),
            'Panel NOC'             => PermissionEnum::noc(),
            'Clientes'              => PermissionEnum::clients(),
            'Administración'        => PermissionEnum::admin(),
            'SLA'                   => PermissionEnum::sla(),
            'Campo / Móvil'         => PermissionEnum::field(),
            'Supervisor de Zona'    => PermissionEnum::supervisor(),
        ];

        $result = [];

        foreach ($modules as $label => $enumCases) {
            $values = array_map(fn($c) => $c->value, $enumCases);

            $gates   = array_values(array_filter($values, fn($p) => str_starts_with($p, 'access_')));
            $menus   = array_values(array_filter($values, fn($p) => str_ends_with($p, '_menu')));
            $actions = array_values(array_filter($values, fn($p) => !str_starts_with($p, 'access_') && !str_ends_with($p, '_menu')));

            $result[$label] = [
                'gates'   => $gates,
                'menus'   => $menus,
                'actions' => $actions,
            ];
        }

        return $result;
    }

    public function render()
    {
        $role = null;
        $rolePermNames = [];

        if ($this->selectedRole) {
            $role = Role::with('permissions')->where('name', $this->selectedRole)->first();
            if ($role) {
                $rolePermNames = $role->permissions->pluck('name')->toArray();
            }
        }

        $roles = Role::all();
        $grouped = $this->rolePermissions;
        $tabModules = array_keys($grouped);

        return view('livewire.admin.users.user-form', compact('roles', 'grouped', 'tabModules', 'rolePermNames'));
    }
}
