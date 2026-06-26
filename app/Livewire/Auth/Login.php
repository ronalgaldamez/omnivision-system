<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    protected $layout = 'components.layouts.app';

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ];

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();
                $this->addError('email', 'Tu cuenta ha sido desactivada. Contacta al administrador.');
                return;
            }

            session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        $this->addError('email', 'Las credenciales no coinciden con nuestros registros.');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
