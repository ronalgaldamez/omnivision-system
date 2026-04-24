<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserForm extends Component
{
    public $userId;
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $selectedRole = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6|confirmed',
        'selectedRole' => 'required|exists:roles,name',
    ];

    public function mount($id = null)
    {
        if ($id) {
            $user = User::findOrFail($id);
            $this->userId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->selectedRole = $user->roles->first()->name ?? '';
            $this->rules['email'] = 'required|email|unique:users,email,' . $id;
            $this->rules['password'] = 'nullable|min:6|confirmed';
        }
    }

    public function save()
    {
        $this->validate();

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }

        $user = User::updateOrCreate(['id' => $this->userId], $userData);
        $user->syncRoles([$this->selectedRole]);

        session()->flash('message', 'Usuario guardado correctamente.');
        return redirect()->route('admin.users.index');
    }

    public function render()
    {
        $roles = Role::all();
        return view('livewire.admin.users.user-form', compact('roles'));
    }
}