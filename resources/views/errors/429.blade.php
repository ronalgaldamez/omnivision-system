@include('errors.layout', [
    'code' => 429,
    'title' => 'Demasiadas peticiones',
    'description' => 'Has hecho muchas peticiones en poco tiempo. Esperá unos segundos y volvé a intentarlo.',
    'dicebearStyle' => 'bottts',
    'dicebearSeed' => 'too-many',
    'icon' => 'pace',
])
