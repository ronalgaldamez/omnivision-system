<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Profile extends Component
{
    public $name;
    public $email;
    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    // Propiedad para el estilo de avatar
    public $avatarStyle = 'initials';

    // Control del modal de selección de avatar
    public $showAvatarModal = false;

    // Lista de estilos disponibles en DiceBear (estilo => etiqueta)
    public $availableStyles = [
        'initials' => 'Iniciales',
        'adventurer' => 'Aventurero',
        'adventurer-neutral' => 'Aventurero Neutro',
        'avataaars' => 'Avataaars',
        'big-ears' => 'Orejones',
        'big-smile' => 'Gran Sonrisa',
        'bottts' => 'Robots',
        'croodles' => 'Croodles',
        'fun-emoji' => 'Emoji Divertido',
        'icons' => 'Iconos',
        'identicon' => 'Identicon',
        'lorelei' => 'Lorelei',
        'micah' => 'Micah',
        'miniavs' => 'Miniavs',
        'notionists' => 'Notionistas',
        'open-peeps' => 'Open Peeps',
        'personas' => 'Personas',
        'pixel-art' => 'Pixel Art',
        'rings' => 'Anillos',
        'shapes' => 'Formas',
        'thumbs' => 'Pulgares',
    ];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'current_password' => 'nullable|required_with:new_password|current_password',
            'new_password' => 'nullable|min:8|confirmed',
        ];
    }

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->avatarStyle = $user->avatar_style ?? 'initials';
    }

    // Abre el modal para cambiar avatar
    public function openAvatarModal()
    {
        $this->showAvatarModal = true;
    }

    // Cierra el modal
    public function closeAvatarModal()
    {
        $this->showAvatarModal = false;
    }

    // Se ejecuta al seleccionar un estilo en el modal
    public function updatedAvatarStyle()
    {
        $user = Auth::user();
        $user->avatar_style = $this->avatarStyle;
        $user->save();
        $this->dispatch('show-toast', type: 'success', message: 'Avatar actualizado.');
        $this->showAvatarModal = false; // Cierra el modal al elegir
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
        ]);

        $user = Auth::user();
        $user->name = $this->name;
        $user->email = $this->email;
        $user->save();

        $this->dispatch('show-toast', type: 'success', message: 'Perfil actualizado correctamente.');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($this->new_password);
        $user->save();

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->dispatch('show-toast', type: 'success', message: 'Contraseña actualizada correctamente.');
    }

    public function render()
    {
        return view('livewire.profile')->layout('components.layouts.app');
    }
}