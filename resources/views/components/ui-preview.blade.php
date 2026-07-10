@extends('components.layouts.app')

@section('title', 'UI Components Preview')

@section('content')
<div class="max-w-5xl mx-auto space-y-10 pb-16">

    {{-- Header --}}
    <x-ui.card title="UI Components Preview" subtitle="Galería de componentes reutilizables del sistema" icon="palette">
        <p class="text-sm text-gray-600">Esta vista sirve como documentación viva de los componentes. Todos respetan la paleta, tipografía y espaciado del sistema.</p>
    </x-ui.card>

    {{-- Buttons --}}
    <x-ui.card title="Buttons" icon="smart_button">
        <div class="space-y-6">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Variants</p>
                <div class="flex flex-wrap gap-3">
                    <x-ui.button variant="primary" icon="save">Guardar</x-ui.button>
                    <x-ui.button variant="success" icon="check_circle">Resolver</x-ui.button>
                    <x-ui.button variant="danger" icon="delete">Eliminar</x-ui.button>
                    <x-ui.button variant="warning" icon="engineering">Crear OT</x-ui.button>
                    <x-ui.button variant="secondary" icon="close">Cancelar</x-ui.button>
                    <x-ui.button variant="ghost" icon="arrow_back">Volver</x-ui.button>
                </div>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Sizes</p>
                <div class="flex flex-wrap items-center gap-3">
                    <x-ui.button variant="primary" size="sm" icon="add">Small</x-ui.button>
                    <x-ui.button variant="primary" size="md" icon="add">Medium</x-ui.button>
                    <x-ui.button variant="primary" size="lg" icon="add">Large</x-ui.button>
                </div>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Link as button</p>
                <x-ui.button variant="primary" icon="link" href="#">Ir a algún lado</x-ui.button>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Loading state</p>
                <x-ui.button variant="primary" loading>Procesando</x-ui.button>
            </div>
        </div>
    </x-ui.card>

    {{-- Inputs --}}
    <x-ui.card title="Inputs" icon="edit_note">
        <div class="space-y-6 max-w-lg">
            <x-ui.input label="Nombre" icon="badge" placeholder="Nombre completo" required />
            <x-ui.input label="Correo" type="email" icon="mail" placeholder="correo@ejemplo.com" />
            <x-ui.input label="Teléfono" icon="phone" placeholder="0000-0000" />
            <x-ui.input label="Con error" icon="warning" name="demo_error" error="Este campo es obligatorio" />
            <x-ui.input label="Deshabilitado" icon="lock" placeholder="No editable" disabled />
        </div>
    </x-ui.card>

    {{-- Selects --}}
    <x-ui.card title="Selects" icon="list">
        <div class="space-y-6 max-w-lg">
            <x-ui.select label="Departamento" icon="map">
                <option value="">Seleccionar departamento</option>
                <option value="1">San Salvador</option>
                <option value="2">La Libertad</option>
                <option value="3">Santa Ana</option>
            </x-ui.select>
            <x-ui.select label="Deshabilitado" icon="lock" disabled>
                <option value="">No disponible</option>
            </x-ui.select>
            <x-ui.select label="Con error" name="demo_select_error" error="Seleccione una opción">
                <option value="">Seleccionar</option>
                <option value="1">Opción 1</option>
            </x-ui.select>
        </div>
    </x-ui.card>

    {{-- Textareas --}}
    <x-ui.card title="Textareas" icon="sticky_note_2">
        <div class="space-y-6 max-w-lg">
            <x-ui.textarea label="Descripción" icon="edit_note" placeholder="Describe el problema..." />
            <x-ui.textarea label="Con error" icon="warning" name="demo_textarea_error" error="La descripción es requerida" />
        </div>
    </x-ui.card>

    {{-- Checkboxes --}}
    <x-ui.card title="Checkboxes" icon="check_box">
        <div class="space-y-4 max-w-lg">
            <x-ui.checkbox label="Acepta recibir promociones" description="Se enviarán correos con ofertas y novedades" />
            <x-ui.checkbox label="Sin precio" description="Más adelante se le puede asignar un paquete al cliente" />
            <x-ui.checkbox label="Deshabilitado" description="No disponible actualmente" disabled />
        </div>
    </x-ui.card>

    {{-- Toggles --}}
    <x-ui.card title="Toggles" icon="toggle_on">
        <div class="space-y-6 max-w-lg">
            <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                <x-ui.toggle label="Crear OT" description="Generar orden de trabajo directamente desde el ticket" onColor="amber" />
            </div>
            <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                <x-ui.toggle label="Requiere NOC" description="Se enviará al panel NOC para su resolución" onColor="blue" />
            </div>
            <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                <x-ui.toggle label="Deshabilitado" description="Opción no disponible" disabled />
            </div>
        </div>
    </x-ui.card>

    {{-- Badges --}}
    <x-ui.card title="Badges" icon="circle">
        <div class="space-y-4">
            <div class="flex flex-wrap gap-3">
                <x-ui.badge variant="success" icon="check_circle">Activo</x-ui.badge>
                <x-ui.badge variant="danger" icon="error">Cancelado</x-ui.badge>
                <x-ui.badge variant="warning" icon="warning">Pendiente</x-ui.badge>
                <x-ui.badge variant="info" icon="info">En proceso</x-ui.badge>
                <x-ui.badge variant="neutral">Borrador</x-ui.badge>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.badge variant="success" size="md">Grande</x-ui.badge>
                <x-ui.badge variant="warning" size="sm">Pequeño</x-ui.badge>
            </div>
        </div>
    </x-ui.card>

    {{-- Alerts --}}
    <x-ui.card title="Alerts / Info boxes" icon="warning">
        <div class="space-y-4">
            <x-ui.alert variant="info" title="Información">
                <p>El ticket se enviará al panel del NOC para su revisión y derivación.</p>
            </x-ui.alert>
            <x-ui.alert variant="success" title="Completado">
                <p>OT generada correctamente. Ticket en seguimiento.</p>
            </x-ui.alert>
            <x-ui.alert variant="warning" title="¿Cuándo usar cada opción?">
                <ul>
                    <li><strong>Crear OT</strong> — El problema requiere visita técnica en campo.</li>
                    <li><strong>Requiere NOC</strong> — El problema puede resolverse de forma remota.</li>
                    <li><strong>Ninguno</strong> — Se soluciona desde L1.</li>
                </ul>
            </x-ui.alert>
            <x-ui.alert variant="danger" title="Error" :dismissible="true">
                <p>El cliente seleccionado ya no existe.</p>
            </x-ui.alert>
        </div>
    </x-ui.card>

    {{-- Modal Preview --}}
    <x-ui.card title="Modal" icon="open_in_new">
        <div class="space-y-6">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Variantes de modal</p>
                <div class="flex flex-wrap gap-3">
                    {{-- Confirmar guardar --}}
                    <div x-data="{ open: false }">
                        <span @click="open = true">
                            <x-ui.button variant="primary" size="sm" icon="save">Guardar</x-ui.button>
                        </span>
                        <div x-show="open" x-cloak class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                            @keydown.escape.window="open = false">
                            <div class="relative mx-auto p-5 w-full max-w-md"
                                x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                                        <h3 class="text-lg font-semibold flex items-center gap-2">
                                            <span class="material-symbols-outlined text-gray-500">help</span> Confirmar acción
                                        </h3>
                                        <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600 transition">
                                            <span class="material-symbols-outlined">close</span>
                                        </button>
                                    </div>
                                    <div class="p-6 text-center">
                                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                                            <span class="material-symbols-outlined text-blue-600 text-2xl">help</span>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900">¿Guardar los cambios?</h3>
                                        <p class="text-sm text-gray-600 mt-2">Se actualizarán los datos del cliente seleccionado.</p>
                                    </div>
                                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row-reverse gap-3">
                                        <span @click="open = false"><x-ui.button variant="primary" icon="check">Sí, continuar</x-ui.button></span>
                                        <span @click="open = false"><x-ui.button variant="secondary">Cancelar</x-ui.button></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Confirmar eliminar --}}
                    <div x-data="{ open: false }">
                        <span @click="open = true">
                            <x-ui.button variant="danger" size="sm" icon="delete">Eliminar</x-ui.button>
                        </span>
                        <div x-show="open" x-cloak class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                            @keydown.escape.window="open = false">
                            <div class="relative mx-auto p-5 w-full max-w-md"
                                x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                                        <h3 class="text-lg font-semibold flex items-center gap-2">
                                            <span class="material-symbols-outlined text-gray-500">warning</span> Confirmar eliminación
                                        </h3>
                                        <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600 transition">
                                            <span class="material-symbols-outlined">close</span>
                                        </button>
                                    </div>
                                    <div class="p-6 text-center">
                                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
                                            <span class="material-symbols-outlined text-red-600 text-2xl">delete</span>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900">¿Eliminar este registro?</h3>
                                        <p class="text-sm text-gray-600 mt-2">Esta acción no se puede deshacer. Se eliminarán todos los datos asociados.</p>
                                    </div>
                                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row-reverse gap-3">
                                        <span @click="open = false"><x-ui.button variant="danger" icon="delete">Sí, eliminar</x-ui.button></span>
                                        <span @click="open = false"><x-ui.button variant="secondary">Cancelar</x-ui.button></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Confirmar cancelar / salir --}}
                    <div x-data="{ open: false }">
                        <span @click="open = true">
                            <x-ui.button variant="secondary" size="sm" icon="arrow_back">Cancelar</x-ui.button>
                        </span>
                        <div x-show="open" x-cloak class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                            @keydown.escape.window="open = false">
                            <div class="relative mx-auto p-5 w-full max-w-md"
                                x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                                        <h3 class="text-lg font-semibold flex items-center gap-2">
                                            <span class="material-symbols-outlined text-gray-500">arrow_back</span> ¿Salir del formulario?
                                        </h3>
                                        <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600 transition">
                                            <span class="material-symbols-outlined">close</span>
                                        </button>
                                    </div>
                                    <div class="p-6 text-center">
                                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-gray-100 mb-4">
                                            <span class="material-symbols-outlined text-gray-600 text-2xl">logout</span>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900">¿Salir?</h3>
                                        <p class="text-sm text-gray-600 mt-2">Los cambios no guardados se perderán.</p>
                                    </div>
                                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row-reverse gap-3">
                                        <span @click="open = false"><x-ui.button variant="secondary" icon="logout">Salir</x-ui.button></span>
                                        <span @click="open = false"><x-ui.button variant="primary">Seguir editando</x-ui.button></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Confirmar solucionar --}}
                    <div x-data="{ open: false }">
                        <span @click="open = true">
                            <x-ui.button variant="success" size="sm" icon="check_circle">Solucionar</x-ui.button>
                        </span>
                        <div x-show="open" x-cloak class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                            @keydown.escape.window="open = false">
                            <div class="relative mx-auto p-5 w-full max-w-md"
                                x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                                        <h3 class="text-lg font-semibold flex items-center gap-2">
                                            <span class="material-symbols-outlined text-gray-500">check_circle</span> Solucionar ticket
                                        </h3>
                                        <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600 transition">
                                            <span class="material-symbols-outlined">close</span>
                                        </button>
                                    </div>
                                    <div class="p-6 text-center">
                                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-green-100 mb-4">
                                            <span class="material-symbols-outlined text-green-600 text-2xl">check_circle</span>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900">¿Solucionar ticket?</h3>
                                        <p class="text-sm text-gray-600 mt-2">Se guardarán todos los datos y se marcará el ticket como resuelto.</p>
                                        <p class="text-sm text-gray-500 mt-1">Tiempo transcurrido: <strong>00:12:35</strong></p>
                                    </div>
                                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row-reverse gap-3">
                                        <span @click="open = false"><x-ui.button variant="success" icon="check_circle">Sí, solucionar</x-ui.button></span>
                                        <span @click="open = false"><x-ui.button variant="secondary">Cancelar</x-ui.button></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Confirmar limpiar --}}
                    <div x-data="{ open: false }">
                        <span @click="open = true">
                            <x-ui.button variant="danger" size="sm" icon="delete_sweep">Limpiar</x-ui.button>
                        </span>
                        <div x-show="open" x-cloak class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                            @keydown.escape.window="open = false">
                            <div class="relative mx-auto p-5 w-full max-w-md"
                                x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                                        <h3 class="text-lg font-semibold flex items-center gap-2">
                                            <span class="material-symbols-outlined text-gray-500">delete_sweep</span> Limpiar campos
                                        </h3>
                                        <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600 transition">
                                            <span class="material-symbols-outlined">close</span>
                                        </button>
                                    </div>
                                    <div class="p-6 text-center">
                                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
                                            <span class="material-symbols-outlined text-red-600 text-2xl">delete_sweep</span>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900">Limpiar campos</h3>
                                        <p class="text-sm text-gray-600 mt-2">¿Estás seguro de limpiar todos los campos? Se perderán los datos ingresados.</p>
                                    </div>
                                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row-reverse gap-3">
                                        <span @click="open = false"><x-ui.button variant="danger" icon="delete_sweep">Sí, limpiar</x-ui.button></span>
                                        <span @click="open = false"><x-ui.button variant="secondary">Cancelar</x-ui.button></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Confirmar generar NOC --}}
                    <div x-data="{ open: false }">
                        <span @click="open = true">
                            <x-ui.button variant="primary" size="sm" icon="send">Generar NOC</x-ui.button>
                        </span>
                        <div x-show="open" x-cloak class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                            @keydown.escape.window="open = false">
                            <div class="relative mx-auto p-5 w-full max-w-md"
                                x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                                        <h3 class="text-lg font-semibold flex items-center gap-2">
                                            <span class="material-symbols-outlined text-gray-500">send</span> Generar ticket NOC
                                        </h3>
                                        <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600 transition">
                                            <span class="material-symbols-outlined">close</span>
                                        </button>
                                    </div>
                                    <div class="p-6 text-center">
                                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                                            <span class="material-symbols-outlined text-blue-600 text-2xl">send</span>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900">¿Generar ticket para NOC?</h3>
                                        <p class="text-sm text-gray-600 mt-2">El ticket se enviará al panel del NOC para su revisión y derivación.</p>
                                    </div>
                                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row-reverse gap-3">
                                        <span @click="open = false"><x-ui.button variant="primary" icon="send">Sí, generar</x-ui.button></span>
                                        <span @click="open = false"><x-ui.button variant="secondary">Cancelar</x-ui.button></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-ui.card>

    {{-- Toast Preview --}}
    <x-ui.card title="Toast / Notificaciones" icon="notifications">
        <div class="space-y-4">
            <p class="text-sm text-gray-600">Usá estos botones para disparar notificaciones. Usan el mismo sistema <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">$dispatch('show-toast')</code> que ya existe en el sistema.</p>
            <div class="space-y-3">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Tipo Success (verde)</p>
                    <div class="flex flex-wrap gap-2">
                        <span @click="$dispatch('show-toast', { type: 'success', message: 'Cliente creado correctamente.' })">
                            <x-ui.button variant="success" size="sm" icon="check_circle">Creado</x-ui.button>
                        </span>
                        <span @click="$dispatch('show-toast', { type: 'success', message: 'Cliente actualizado correctamente.' })">
                            <x-ui.button variant="success" size="sm" icon="check_circle">Actualizado</x-ui.button>
                        </span>
                        <span @click="$dispatch('show-toast', { type: 'success', message: 'Ticket resuelto correctamente. Tiempo total: 00:12:35.' })">
                            <x-ui.button variant="success" size="sm" icon="check_circle">Resuelto</x-ui.button>
                        </span>
                        <span @click="$dispatch('show-toast', { type: 'success', message: 'OT generada correctamente. Ticket en seguimiento.' })">
                            <x-ui.button variant="success" size="sm" icon="check_circle">OT generada</x-ui.button>
                        </span>
                        <span @click="$dispatch('show-toast', { type: 'success', message: 'Movimiento registrado exitosamente.' })">
                            <x-ui.button variant="success" size="sm" icon="check_circle">Movimiento</x-ui.button>
                        </span>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Tipo Error / Danger (rojo)</p>
                    <div class="flex flex-wrap gap-2">
                        <span @click="$dispatch('show-toast', { type: 'error', message: 'El DUI ya pertenece a otro cliente.' })">
                            <x-ui.button variant="danger" size="sm" icon="error">DUI duplicado</x-ui.button>
                        </span>
                        <span @click="$dispatch('show-toast', { type: 'error', message: 'El NC ingresado ya pertenece a otro cliente.' })">
                            <x-ui.button variant="danger" size="sm" icon="error">NC duplicado</x-ui.button>
                        </span>
                        <span @click="$dispatch('show-toast', { type: 'error', message: 'El cliente seleccionado ya no existe.' })">
                            <x-ui.button variant="danger" size="sm" icon="error">Cliente no existe</x-ui.button>
                        </span>
                        <span @click="$dispatch('show-toast', { type: 'error', message: 'Error al guardar: el campo nombre es obligatorio.' })">
                            <x-ui.button variant="danger" size="sm" icon="error">Validación</x-ui.button>
                        </span>
                        <span @click="$dispatch('show-toast', { type: 'error', message: 'Dispositivo con MAC duplicada.' })">
                            <x-ui.button variant="danger" size="sm" icon="error">MAC duplicada</x-ui.button>
                        </span>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Tipo Info (azul)</p>
                    <div class="flex flex-wrap gap-2">
                        <span @click="$dispatch('show-toast', { type: 'info', message: 'Nuevo ticket requiere atención del NOC.' })">
                            <x-ui.button variant="primary" size="sm" icon="info">NOC pendiente</x-ui.button>
                        </span>
                        <span @click="$dispatch('show-toast', { type: 'info', message: 'Ticket asignado al técnico Juan Pérez.' })">
                            <x-ui.button variant="primary" size="sm" icon="info">Asignado</x-ui.button>
                        </span>
                        <span @click="$dispatch('show-toast', { type: 'info', message: 'Envío ENV-00042 marcado como en tránsito.' })">
                            <x-ui.button variant="primary" size="sm" icon="info">Envío actualizado</x-ui.button>
                        </span>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Tipo Warning (ámbar)</p>
                    <div class="flex flex-wrap gap-2">
                        <span @click="$dispatch('show-toast', { type: 'warning', message: 'Esta zona no tiene cobertura de internet.' })">
                            <x-ui.button variant="warning" size="sm" icon="warning">Sin cobertura</x-ui.button>
                        </span>
                        <span @click="$dispatch('show-toast', { type: 'warning', message: 'El stock del producto está por debajo del mínimo.' })">
                            <x-ui.button variant="warning" size="sm" icon="warning">Stock bajo</x-ui.button>
                        </span>
                        <span @click="$dispatch('show-toast', { type: 'warning', message: 'La requisición está pendiente de aprobación.' })">
                            <x-ui.button variant="warning" size="sm" icon="warning">Pendiente</x-ui.button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </x-ui.card>

    {{-- Forms Group --}}
    <x-ui.card title="Form Group (completo)" icon="dynamic_form">
        <div class="space-y-6 max-w-lg">
            <x-forms.group label="Nombre del cliente" icon="badge" required name="demo_name">
                <input type="text" placeholder="Nombre completo"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-900 text-sm focus:border-gray-400 focus:bg-white focus:outline-none transition"
                    value="Juan Pérez">
            </x-forms.group>

            <x-forms.group label="Con error" icon="warning" name="demo_error_group" error="Este campo es obligatorio">
                <input type="text" placeholder="Campo con error"
                    class="w-full px-4 py-2.5 rounded-lg border border-red-300 bg-red-50 text-red-900 text-sm focus:border-red-400 focus:bg-white focus:outline-none transition"
                    value="">
            </x-forms.group>
        </div>
    </x-ui.card>

    {{-- Card with footer --}}
    <x-ui.card title="Card con footer" subtitle="Ejemplo de card con botones de acción" icon="dashboard">
        <p class="text-sm text-gray-600">Contenido del body de la card. Aquí va el formulario, tabla o lo que sea.</p>
        <x-slot:footer>
            <x-ui.button variant="primary" icon="save">Guardar</x-ui.button>
            <x-ui.button variant="secondary" icon="close">Cancelar</x-ui.button>
            <x-ui.button variant="danger" icon="delete_sweep">Limpiar</x-ui.button>
        </x-slot:footer>
    </x-ui.card>
</div>
@endsection
