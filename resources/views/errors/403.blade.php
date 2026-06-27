@include('errors.layout', [
    'code' => 403,
    'title' => 'Acceso denegado',
    'description' => 'No tenés permiso para acceder a esta sección. Si creés que es un error, contactá al administrador del sistema.',
    'dicebearStyle' => 'lorelei',
    'dicebearSeed' => 'access-denied',
    'icon' => 'lock',
])
