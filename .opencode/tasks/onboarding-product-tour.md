# Onboarding / Product Tour

**Tipo:** Feature
**Prioridad:** Media
**Rama sugerida:** `feature/onboarding`
**Estimación:** ~medio día

## Descripción
Sistema de onboarding interactivo que muestra un tour guiado la primera vez que un usuario accede a cada módulo del sistema.

## Archivos afectados

### Nuevos
- `app/Livewire/Onboarding.php` — Componente Livewire global
- `resources/views/livewire/onboarding-modal.blade.php` — Modal con pasos
- `config/onboarding.php` — Pasos por módulo/ruta

### Modificados
- `resources/views/components/layouts/app.blade.php` — Montar el componente

### Base de datos
- Agregar columna `onboarding` tipo JSON a `users` (o crear `user_settings`)
- Migración: `add_onboarding_column_to_users_table`

## Lógica
1. Al cargar cada ruta, el layout verifica si el usuario ya completó el onboarding de ese módulo
2. Si no, muestra un modal con botones "Anterior / Siguiente / Saltar / Entendido"
3. Se guarda en `user->onboarding` un array con los módulos completados

## Pasos de ejemplo por módulo

### Importar Productos (`admin.imports.products`)
1. "Subí tu archivo Excel o CSV con los productos"
2. "Revisá la vista previa con las primeras filas"
3. "Confirmá la importación"
4. "Revisá el resumen de resultados"

### Catálogo (`admin.catalog`)
1. "Gestioná marcas, categorías y modelos"
2. "Usá las pestañas para alternar entre secciones"

## Dependencias
- Alpine.js (ya incluido con Livewire)
- Ninguna externa

## Riesgos
- Bajo. No afecta lógica de negocio ni BD existente.
