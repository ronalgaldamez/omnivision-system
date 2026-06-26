@include('errors.layout', [
    'code' => 500,
    'title' => 'Error interno',
    'description' => 'Algo salió mal de nuestro lado. El equipo ya fue notificado. Intentá de nuevo en unos minutos.',
    'dicebearStyle' => 'bottts',
    'dicebearSeed' => 'broken',
    'icon' => 'settings',
])
