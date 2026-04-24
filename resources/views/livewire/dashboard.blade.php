<div>
    <h1>Bienvenido, {{ auth()->user()->name }}</h1>
    <p>Roles: {{ implode(', ', auth()->user()->getRoleNames()->toArray()) }}</p>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Cerrar sesión</button>
    </form>
</div>