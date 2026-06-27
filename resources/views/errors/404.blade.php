@include('errors.layout', [
    'code' => 404,
    'title' => 'Página no encontrada',
    'description' => 'La página que buscás no existe o fue movida. Revisá la dirección o volvé al inicio.',
    'dicebearStyle' => 'bottts',
    'dicebearSeed' => 'not-found',
    'icon' => 'explore',
])
