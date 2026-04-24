<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'Sistema Kardex')</title>
    <!-- Tailwind CSS v3 CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts + Material Icons -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0,1"
        rel="stylesheet">
    <!-- Leaflet CSS y JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
            font-size: 1.25rem;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
    @livewireStyles
</head>

<body class="bg-gray-50 text-gray-800 text-sm">
    <livewire:global-notification />
    <div x-data="{ open: false }" class="min-h-screen">
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-14">
                    <div class="flex">
                        <div class="flex items-center">
                            <span class="material-symbols-outlined text-blue-600 mr-1">inventory</span>
                            <span class="font-semibold text-gray-700">Kardex System</span>
                        </div>
                        <!-- Menú escritorio -->
                        <div class="hidden md:flex md:items-center md:space-x-4 ml-6">
                            @auth
                                @if(module_active('inventory'))
                                    <div class="relative group">
                                        <button
                                            class="text-gray-600 hover:text-gray-900 px-2 py-1 rounded-md text-sm flex items-center gap-1">
                                            <span class="material-symbols-outlined text-base">list_alt</span> Movimientos
                                            <span class="material-symbols-outlined text-base">expand_more</span>
                                        </button>
                                        <div class="absolute left-0 top-full pt-1 hidden group-hover:block z-20">
                                            <div class="bg-white border border-gray-200 rounded-md shadow-md min-w-[180px]">
                                                @can('view movements')
                                                    <a href="{{ route('movements.index') }}"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-base">list_alt</span> Listado
                                                    </a>
                                                @endcan
                                                @can('create movements')
                                                    <a href="{{ route('movements.create') }}"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-base">add_circle</span> Nuevo
                                                        Movimiento
                                                    </a>
                                                @endcan
                                                @can('view kardex')
                                                    <a href="{{ route('kardex.index') }}"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-base">receipt</span> Kardex
                                                    </a>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>

                                    @can('view products')
                                        <a href="{{ route('products.index') }}"
                                            class="text-gray-600 hover:text-gray-900 px-2 py-1 rounded-md text-sm flex items-center gap-1">
                                            <span class="material-symbols-outlined text-base">box</span> Productos
                                        </a>
                                    @endcan
                                @endif

                                @if(module_active('suppliers'))
                                    <div class="relative group">
                                        <button
                                            class="text-gray-600 hover:text-gray-900 px-2 py-1 rounded-md text-sm flex items-center gap-1">
                                            <span class="material-symbols-outlined text-base">shopping_cart</span> Compras
                                            <span class="material-symbols-outlined text-base">expand_more</span>
                                        </button>
                                        <div class="absolute left-0 top-full pt-1 hidden group-hover:block z-20">
                                            <div class="bg-white border border-gray-200 rounded-md shadow-md min-w-[180px]">
                                                @can('view purchases')
                                                    <a href="{{ route('purchases.index') }}"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-base">history</span> Historial
                                                    </a>
                                                @endcan
                                                @can('create purchases')
                                                    <a href="{{ route('purchases.create') }}"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-base">add_shopping_cart</span>
                                                        Nueva Compra
                                                    </a>
                                                @endcan
                                                @can('view suppliers')
                                                    <a href="{{ route('suppliers.index') }}"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-base">business</span>
                                                        Proveedores
                                                    </a>
                                                @endcan
                                                @can('create purchases')
                                                    <a href="{{ route('returns.create') }}"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-base">assignment_return</span>
                                                        Devolución
                                                    </a>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if(module_active('technicians'))
                                    @if(auth()->user()->hasRole('technician'))
                                        @can('create technician_requests')
                                            <a href="{{ route('mobile.technician.requests') }}"
                                                class="text-gray-600 hover:text-gray-900 px-2 py-1 rounded-md text-sm flex items-center gap-1">
                                                <span class="material-symbols-outlined text-base">list_alt</span> Mis Solicitudes
                                            </a>
                                            <a href="{{ route('mobile.technician.requests.create') }}"
                                                class="text-gray-600 hover:text-gray-900 px-2 py-1 rounded-md text-sm flex items-center gap-1">
                                                <span class="material-symbols-outlined text-base">add_task</span> Nueva Solicitud
                                            </a>
                                            <a href="{{ route('mobile.work-orders.list') }}"
                                                class="text-gray-600 hover:text-gray-900 px-2 py-1 rounded-md text-sm flex items-center gap-1">
                                                <span class="material-symbols-outlined text-base">work</span> Mis Órdenes
                                            </a>
                                            <a href="{{ route('mobile.work-orders.map') }}"
                                                class="text-gray-600 hover:text-gray-900 px-2 py-1 rounded-md text-sm flex items-center gap-1">
                                                <span class="material-symbols-outlined text-base">map</span> Mapa de Órdenes
                                            </a>
                                        @endcan
                                    @elseif(auth()->user()->hasRole('warehouse') || auth()->user()->hasRole('admin'))
                                        <div class="relative group">
                                            <button
                                                class="text-gray-600 hover:text-gray-900 px-2 py-1 rounded-md text-sm flex items-center gap-1">
                                                <span class="material-symbols-outlined text-base">handyman</span> Técnicos
                                                <span class="material-symbols-outlined text-base">expand_more</span>
                                            </button>
                                            <div class="absolute left-0 top-full pt-1 hidden group-hover:block z-20">
                                                <div class="bg-white border border-gray-200 rounded-md shadow-md min-w-[180px]">
                                                    @can('view technician_requests')
                                                        <a href="{{ route('technician-requests.index') }}"
                                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                            <span class="material-symbols-outlined text-base">inbox</span> Gestionar
                                                            Solicitudes
                                                        </a>
                                                    @endcan
                                                    @can('approve technician_requests')
                                                        <a href="{{ route('code-delivery.index') }}"
                                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                            <span class="material-symbols-outlined text-base">qr_code_scanner</span>
                                                            Escáner QR
                                                        </a>
                                                    @endcan
                                                    @if(module_active('technician_returns'))
                                                        @can('view technician_returns')
                                                            <a href="{{ route('technician-returns.index') }}"
                                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                                <span class="material-symbols-outlined text-base">assignment_return</span>
                                                                Devoluciones
                                                            </a>
                                                        @endcan
                                                        @can('create technician_returns')
                                                            <a href="{{ route('technician-returns.create') }}"
                                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                                <span class="material-symbols-outlined text-base">add_circle</span>
                                                                Registrar Devolución
                                                            </a>
                                                        @endcan
                                                    @endif
                                                    @if(module_active('work_orders'))
                                                        @can('view work_orders')
                                                            <a href="{{ route('work-orders.index') }}"
                                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                                <span class="material-symbols-outlined text-base">work</span> Órdenes de
                                                                Trabajo
                                                            </a>
                                                            <a href="{{ route('work-orders.map') }}"
                                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                                <span class="material-symbols-outlined text-base">map</span> Mapa de OT
                                                            </a>
                                                        @endcan
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                @can('view dashboard')
                                    <a href="{{ route('dashboard') }}"
                                        class="text-gray-600 hover:text-gray-900 px-2 py-1 rounded-md text-sm flex items-center gap-1">
                                        <span class="material-symbols-outlined text-base">dashboard</span> Dashboard
                                    </a>
                                @endcan

                                @if(module_active('reports'))
                                    @can('view reports')
                                        <div class="relative group">
                                            <button
                                                class="text-gray-600 hover:text-gray-900 px-2 py-1 rounded-md text-sm flex items-center gap-1">
                                                <span class="material-symbols-outlined text-base">assessment</span> Reportes
                                                <span class="material-symbols-outlined text-base">expand_more</span>
                                            </button>
                                            <div class="absolute left-0 top-full pt-1 hidden group-hover:block z-20">
                                                <div class="bg-white border border-gray-200 rounded-md shadow-md min-w-[160px]">
                                                    <a href="{{ route('reports.stock') }}"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-base">inventory</span> Stock
                                                        bajo
                                                    </a>
                                                    <a href="{{ route('reports.movements') }}"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-base">swap_vert</span>
                                                        Movimientos
                                                    </a>
                                                    <a href="{{ route('reports.technicians') }}"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-base">handyman</span>
                                                        Rendimiento técnicos
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endcan
                                @endif

                                <!-- ========== DROPDOWN: SOPORTE TÉCNICO (basado en permisos) ========== -->
                                @php
                                    $showSupport = false;
                                    if (module_active('work_orders')) {
                                        if (auth()->user()->can('create tickets') || auth()->user()->can('edit tickets')) {
                                            $showSupport = true;
                                        }
                                    }
                                @endphp

                                @if($showSupport)
                                    <div class="relative group">
                                        <button
                                            class="text-gray-600 hover:text-gray-900 px-2 py-1 rounded-md text-sm flex items-center gap-1">
                                            <span class="material-symbols-outlined text-base">support_agent</span> Soporte
                                            técnico
                                            <span class="material-symbols-outlined text-base">expand_more</span>
                                        </button>
                                        <div class="absolute left-0 top-full pt-1 hidden group-hover:block z-20">
                                            <div class="bg-white border border-gray-200 rounded-md shadow-md min-w-[200px]">
                                                @can('create tickets')
                                                    <a href="{{ route('tickets.create') }}"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-base">add_comment</span> Nuevo
                                                        Ticket
                                                    </a>
                                                    <a href="{{ route('tickets.index') }}"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-base">chat</span> Mis Tickets
                                                    </a>
                                                @endcan
                                                @can('edit tickets')
                                                    <a href="{{ route('noc.panel') }}"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-base">settings_overscan</span>
                                                        Panel NOC
                                                    </a>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if(auth()->user()->hasRole('admin'))
                                    <div class="relative group">
                                        <button
                                            class="text-gray-600 hover:text-gray-900 px-2 py-1 rounded-md text-sm flex items-center gap-1">
                                            <span class="material-symbols-outlined text-base">admin_panel_settings</span> Admin
                                            <span class="material-symbols-outlined text-base">expand_more</span>
                                        </button>
                                        <div class="absolute left-0 top-full pt-1 hidden group-hover:block z-20">
                                            <div class="bg-white border border-gray-200 rounded-md shadow-md min-w-[180px]">
                                                <a href="{{ route('admin.users.index') }}"
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                    <span class="material-symbols-outlined text-base">people</span> Usuarios
                                                </a>
                                                <a href="{{ route('admin.roles.index') }}"
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                    <span class="material-symbols-outlined text-base">security</span> Roles y
                                                    Permisos
                                                </a>
                                                <hr class="my-1">
                                                @can('manage catalog')
                                                    <a href="{{ route('admin.catalog') }}"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-base">inventory_2</span>
                                                        Catálogo
                                                    </a>
                                                @endcan
                                                <a href="{{ route('admin.settings') }}"
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                    <span class="material-symbols-outlined text-base">settings</span>
                                                    Configuración
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endauth
                        </div>
                    </div>

                    @auth
                        <div class="hidden md:flex items-center space-x-3">
                            <span class="material-symbols-outlined text-base text-gray-500">account_circle</span>
                            <span class="text-xs text-gray-500">{{ auth()->user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit"
                                    class="text-gray-500 hover:text-gray-700 text-sm flex items-center gap-1">
                                    <span class="material-symbols-outlined text-base">logout</span> Salir
                                </button>
                            </form>
                        </div>
                    @endauth

                    <!-- Botón hamburguesa (móvil) -->
                    <div class="flex items-center md:hidden">
                        <button @click="open = !open" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                            <span class="material-symbols-outlined">menu</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Menú móvil -->
            <div x-show="open" @click.away="open = false" x-transition.duration.300ms
                class="md:hidden bg-white border-t border-gray-200">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    @auth
                        @if(module_active('inventory'))
                            <div class="space-y-1">
                                <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Movimientos
                                </div>
                                @can('view movements')
                                    <a href="{{ route('movements.index') }}"
                                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2 ml-2">
                                        <span class="material-symbols-outlined text-base">list_alt</span> Listado
                                    </a>
                                @endcan
                                @can('create movements')
                                    <a href="{{ route('movements.create') }}"
                                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2 ml-2">
                                        <span class="material-symbols-outlined text-base">add_circle</span> Nuevo Movimiento
                                    </a>
                                @endcan
                                @can('view kardex')
                                    <a href="{{ route('kardex.index') }}"
                                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2 ml-2">
                                        <span class="material-symbols-outlined text-base">receipt</span> Kardex
                                    </a>
                                @endcan
                            </div>

                            @can('view products')
                                <a href="{{ route('products.index') }}"
                                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-base">box</span> Productos
                                </a>
                            @endcan
                        @endif

                        @if(module_active('suppliers'))
                            <div class="space-y-1">
                                <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Compras
                                </div>
                                @can('view purchases')
                                    <a href="{{ route('purchases.index') }}"
                                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2 ml-2">
                                        <span class="material-symbols-outlined text-base">history</span> Historial
                                    </a>
                                @endcan
                                @can('create purchases')
                                    <a href="{{ route('purchases.create') }}"
                                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2 ml-2">
                                        <span class="material-symbols-outlined text-base">add_shopping_cart</span> Nueva Compra
                                    </a>
                                @endcan
                                @can('view suppliers')
                                    <a href="{{ route('suppliers.index') }}"
                                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2 ml-2">
                                        <span class="material-symbols-outlined text-base">business</span> Proveedores
                                    </a>
                                @endcan
                                @can('create purchases')
                                    <a href="{{ route('returns.create') }}"
                                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2 ml-2">
                                        <span class="material-symbols-outlined text-base">assignment_return</span> Devolución
                                    </a>
                                @endcan
                            </div>
                        @endif

                        @if(module_active('technicians'))
                            @if(auth()->user()->hasRole('technician'))
                                @can('create technician_requests')
                                    <a href="{{ route('mobile.technician.requests') }}"
                                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                        <span class="material-symbols-outlined text-base">list_alt</span> Mis Solicitudes
                                    </a>
                                    <a href="{{ route('mobile.technician.requests.create') }}"
                                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                        <span class="material-symbols-outlined text-base">add_task</span> Nueva Solicitud
                                    </a>
                                    <a href="{{ route('mobile.work-orders.list') }}"
                                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                        <span class="material-symbols-outlined text-base">work</span> Mis Órdenes
                                    </a>
                                    <a href="{{ route('mobile.work-orders.map') }}"
                                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                        <span class="material-symbols-outlined text-base">map</span> Mapa de Órdenes
                                    </a>
                                @endcan
                            @elseif(auth()->user()->hasRole('warehouse') || auth()->user()->hasRole('admin'))
                                <div class="space-y-1">
                                    <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Técnicos
                                    </div>
                                    @can('view technician_requests')
                                        <a href="{{ route('technician-requests.index') }}"
                                            class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2 ml-2">
                                            <span class="material-symbols-outlined text-base">inbox</span> Gestionar Solicitudes
                                        </a>
                                    @endcan
                                    @can('approve technician_requests')
                                        <a href="{{ route('code-delivery.index') }}"
                                            class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2 ml-2">
                                            <span class="material-symbols-outlined text-base">qr_code_scanner</span> Escáner QR
                                        </a>
                                    @endcan
                                    @if(module_active('technician_returns'))
                                        @can('view technician_returns')
                                            <a href="{{ route('technician-returns.index') }}"
                                                class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2 ml-2">
                                                <span class="material-symbols-outlined text-base">assignment_return</span> Devoluciones
                                            </a>
                                        @endcan
                                        @can('create technician_returns')
                                            <a href="{{ route('technician-returns.create') }}"
                                                class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2 ml-2">
                                                <span class="material-symbols-outlined text-base">add_circle</span> Registrar Devolución
                                            </a>
                                        @endcan
                                    @endif
                                    @if(module_active('work_orders'))
                                        @can('view work_orders')
                                            <a href="{{ route('work-orders.index') }}"
                                                class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2 ml-2">
                                                <span class="material-symbols-outlined text-base">work</span> Órdenes de Trabajo
                                            </a>
                                            <a href="{{ route('work-orders.map') }}"
                                                class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2 ml-2">
                                                <span class="material-symbols-outlined text-base">map</span> Mapa de OT
                                            </a>
                                        @endcan
                                    @endif
                                </div>
                            @endif
                        @endif

                        @can('view dashboard')
                            <a href="{{ route('dashboard') }}"
                                class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                <span class="material-symbols-outlined text-base">dashboard</span> Dashboard
                            </a>
                        @endcan

                        @if(module_active('reports'))
                            @can('view reports')
                                <div class="space-y-1">
                                    <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Reportes
                                    </div>
                                    <a href="{{ route('reports.stock') }}"
                                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2 ml-2">
                                        <span class="material-symbols-outlined text-base">inventory</span> Stock bajo
                                    </a>
                                    <a href="{{ route('reports.movements') }}"
                                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2 ml-2">
                                        <span class="material-symbols-outlined text-base">swap_vert</span> Movimientos
                                    </a>
                                    <a href="{{ route('reports.technicians') }}"
                                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2 ml-2">
                                        <span class="material-symbols-outlined text-base">handyman</span> Rendimiento técnicos
                                    </a>
                                </div>
                            @endcan
                        @endif

                        <!-- ========== MENÚ MÓVIL: SOPORTE TÉCNICO (sección) ========== -->
                        @php
                            $showSupportMobile = false;
                            if (module_active('work_orders')) {
                                if (auth()->user()->can('create tickets') || auth()->user()->can('edit tickets')) {
                                    $showSupportMobile = true;
                                }
                            }
                        @endphp

                        @if($showSupportMobile)
                            <div class="space-y-1">
                                <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Soporte
                                    técnico</div>
                                @can('create tickets')
                                    <a href="{{ route('tickets.create') }}"
                                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2 ml-2">
                                        <span class="material-symbols-outlined text-base">add_comment</span> Nuevo Ticket
                                    </a>
                                    <a href="{{ route('tickets.index') }}"
                                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2 ml-2">
                                        <span class="material-symbols-outlined text-base">chat</span> Mis Tickets
                                    </a>
                                @endcan
                                @can('edit tickets')
                                    <a href="{{ route('noc.panel') }}"
                                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2 ml-2">
                                        <span class="material-symbols-outlined text-base">settings_overscan</span> Panel NOC
                                    </a>
                                @endcan
                            </div>
                        @endif

                        @if(auth()->user()->hasRole('admin'))
                            <div class="space-y-1">
                                <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Admin</div>
                                <a href="{{ route('admin.users.index') }}"
                                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2 ml-2">
                                    <span class="material-symbols-outlined text-base">people</span> Usuarios
                                </a>
                                <a href="{{ route('admin.roles.index') }}"
                                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2 ml-2">
                                    <span class="material-symbols-outlined text-base">security</span> Roles y Permisos
                                </a>
                                @can('manage catalog')
                                    <a href="{{ route('admin.catalog') }}"
                                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 flex items-center gap-2 ml-2">
                                        <span class="material-symbols-outlined text-base">inventory_2</span> Catálogo
                                    </a>
                                @endcan
                                <a href="{{ route('admin.settings') }}"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2 ml-2">
                                    <span class="material-symbols-outlined text-base">settings</span> Configuración
                                </a>
                            </div>
                        @endif

                        <hr class="my-2">
                        <div class="flex items-center gap-2 px-3 py-2 text-sm text-gray-500">
                            <span class="material-symbols-outlined text-base">account_circle</span>
                            <span>{{ auth()->user()->name }}</span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit"
                                class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-red-600 hover:bg-gray-100 flex items-center gap-2">
                                <span class="material-symbols-outlined text-base">logout</span> Cerrar sesión
                            </button>
                        </form>
                    @endauth
                </div>
            </div>
        </nav>

        <main class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>

    <!-- Toast global -->
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 5000)"
        x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300" style="display: none;">
        <div x-show="toastType === 'info'"
            class="bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
            <span class="material-symbols-outlined">info</span>
            <span x-text="toastMessage"></span>
        </div>
    </div>

    @livewireScripts
    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>

</html>