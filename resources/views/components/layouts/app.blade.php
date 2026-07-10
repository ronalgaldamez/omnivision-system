<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>
        @hasSection('title')
            @yield('title')
        @else
            {{ page_title_from_route() }}
        @endif
    </title>

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('android-chrome-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('android-chrome-512x512.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0,1"
        rel="stylesheet">
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

        .sidebar-scroll {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 2px;
        }
    </style>
    @livewireStyles
</head>

<body class="bg-gray-50 text-gray-800 text-sm">

    @auth
        {{-- ========== LAYOUT CON SIDEBAR PARA USUARIOS AUTENTICADOS ========== --}}
        <div x-data="{ sidebarOpen: window.innerWidth >= 768 }" class="flex h-screen overflow-hidden">

            {{-- Sidebar --}}
            <aside :class="sidebarOpen ? 'w-64' : 'w-0'"
                class="bg-white border-r border-gray-200/80 flex flex-col transition-all duration-300 overflow-hidden z-40 h-full sticky top-0">

                <div class="h-14 flex items-center gap-2 px-4 border-b border-gray-100 flex-shrink-0">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center shadow-sm">
                            <span class="material-symbols-outlined text-white text-xl">inventory</span>
                        </div>
                        <span x-show="sidebarOpen" class="font-semibold text-gray-700 text-sm whitespace-nowrap">Kardex
                            System</span>
                    </a>
                </div>

                <nav class="flex-1 overflow-y-auto sidebar-scroll px-3 py-4 space-y-1">
                    {{-- INVENTARIO --}}
                    @if(module_active('inventory') && auth()->user()->can('access_inventory'))
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50/80 transition text-sm font-medium">
                                <span class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-base">inventory</span>
                                    <span x-show="sidebarOpen">Inventario</span>
                                </span>
                                <span x-show="sidebarOpen" class="material-symbols-outlined text-sm transition-transform"
                                    :class="open ? 'rotate-180' : ''">expand_more</span>
                            </button>
                            <div x-show="open" x-collapse class="ml-4 space-y-1 mt-1">
                                @can('view_movements_menu')<a href="{{ route('movements.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">list_alt</span> Movimientos</a>@endcan
                                @can('view_new_movement_menu')<a href="{{ route('movements.create') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                        class="material-symbols-outlined text-base">add_circle</span> Nuevo
                                Movimiento</a>@endcan
                                @can('view_products_menu')<a href="{{ route('products.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">box</span> Productos</a>@endcan
                                @can('view_kardex_menu')<a href="{{ route('kardex.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">receipt</span> Kardex</a>@endcan
                                @can('access_inventory')<a href="{{ route('bodega.shipments.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">local_shipping</span> Envíos</a>@endcan
                                @can('access_inventory')<a href="{{ route('devices.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">settings_ethernet</span> Dispositivos</a>@endcan
                                @can('access_inventory')<a href="{{ route('bodega.requisitions.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">inventory</span> Bodega</a>@endcan
                            </div>
                        </div>
                    @endif

                    {{-- COMPRAS --}}
                    @if(module_active('suppliers') && auth()->user()->can('access_suppliers'))
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50/80 transition text-sm font-medium">
                                <span class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-base">shopping_cart</span>
                                    <span x-show="sidebarOpen">Compras</span>
                                </span>
                                <span x-show="sidebarOpen" class="material-symbols-outlined text-sm transition-transform"
                                    :class="open ? 'rotate-180' : ''">expand_more</span>
                            </button>
                            <div x-show="open" x-collapse class="ml-4 space-y-1 mt-1">
                                @can('view_suppliers_menu')<a href="{{ route('suppliers.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">business</span> Proveedores</a>@endcan
                                @can('view_purchase_history_menu')<a href="{{ route('purchases.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                        class="material-symbols-outlined text-base">history</span> Historial de
                                Compras</a>@endcan
                                @can('view_new_purchase_menu')<a href="{{ route('purchases.create') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                        class="material-symbols-outlined text-base">add_shopping_cart</span> Nueva
                                Compra</a>@endcan
                                @can('view_returns_menu')<a href="{{ route('returns.create') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                        class="material-symbols-outlined text-base">assignment_return</span>
                                Devolución</a>@endcan
                            </div>
                        </div>
                    @endif

                    {{-- TÉCNICOS --}}
                    @if(module_active('technicians') && auth()->user()->can('access_technicians'))
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50/80 transition text-sm font-medium">
                                <span class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-base">handyman</span>
                                    <span x-show="sidebarOpen">Técnicos</span>
                                </span>
                                <span x-show="sidebarOpen" class="material-symbols-outlined text-sm transition-transform"
                                    :class="open ? 'rotate-180' : ''">expand_more</span>
                            </button>
                            <div x-show="open" x-collapse class="ml-4 space-y-1 mt-1">
                                @can('view_returns_menu')<a href="{{ route('technician-returns.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                        class="material-symbols-outlined text-base">assignment_return</span>
                                Devoluciones</a>@endcan
                                @can('view_register_return_menu')<a href="{{ route('technician-returns.create') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                        class="material-symbols-outlined text-base">add_circle</span> Registrar
                                Devolución</a>@endcan
                                @can('view_work_orders_menu')<a href="{{ route('work-orders.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">work</span> Órdenes de Trabajo</a>@endcan
                                @can('access my daily jobs')<a href="{{ route('mobile.work-orders.list') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">work</span> Mis Trabajos</a>@endcan
                                @can('view_map_ot_menu')<a href="{{ route('work-orders.map') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">map</span> Mapa de OT</a>@endcan
                                @can('view_requisitions_menu')<a href="{{ route('technician.requisitions.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">inventory_2</span> Requisiciones</a>@endcan
                            </div>
                        </div>
                    @endif

                    {{-- REPORTES --}}
                    @if(module_active('reports') && auth()->user()->can('access_reports'))
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50/80 transition text-sm font-medium">
                                <span class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-base">assessment</span>
                                    <span x-show="sidebarOpen">Reportes</span>
                                </span>
                                <span x-show="sidebarOpen" class="material-symbols-outlined text-sm transition-transform"
                                    :class="open ? 'rotate-180' : ''">expand_more</span>
                            </button>
                            <div x-show="open" x-collapse class="ml-4 space-y-1 mt-1">
                                @can('view_low_stock_menu')<a href="{{ route('reports.stock') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">inventory</span> Stock bajo</a>@endcan
                                @can('view_movements_report_menu')<a href="{{ route('reports.movements') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">swap_vert</span> Movimientos</a>@endcan
                                @can('view_technician_performance_menu')<a href="{{ route('reports.technicians') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                        class="material-symbols-outlined text-base">handyman</span> Rendimiento
                                técnicos</a>@endcan
                            </div>
                        </div>
                    @endif

                    {{-- SOPORTE --}}
                    @if(auth()->user()->can('access_support'))
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50/80 transition text-sm font-medium">
                                <span class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-base">support_agent</span>
                                    <span x-show="sidebarOpen">Soporte</span>
                                </span>
                                <span x-show="sidebarOpen" class="material-symbols-outlined text-sm transition-transform"
                                    :class="open ? 'rotate-180' : ''">expand_more</span>
                            </button>
                            <div x-show="open" x-collapse class="ml-4 space-y-1 mt-1">
                                @can('view_new_ticket_menu')<a href="{{ route('tickets.create') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">add_comment</span> Nuevo Ticket</a>@endcan
                                @can('view_all_tickets_menu')<a href="{{ route('tickets.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">list_alt</span> Todos los Tickets</a>@endcan
                                @can('view_noc_panel_menu')<a href="{{ route('noc.panel') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                        class="material-symbols-outlined text-base">settings_overscan</span> Bandeja
                                NOC</a>@endcan
                                @can('view sla dashboard')<a href="{{ route('sla.dashboard') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                        class="material-symbols-outlined text-base">monitoring</span> Dashboard SLA</a>@endcan

                            </div>
                        </div>
                    @endif

                    {{-- CLIENTES --}}
                    @if(auth()->user()->can('view clients'))
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50/80 transition text-sm font-medium">
                                <span class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-base">people</span>
                                    <span x-show="sidebarOpen">Clientes</span>
                                </span>
                                <span x-show="sidebarOpen" class="material-symbols-outlined text-sm transition-transform"
                                    :class="open ? 'rotate-180' : ''">expand_more</span>
                            </button>
                            <div x-show="open" x-collapse class="ml-4 space-y-1 mt-1">
                                @can('view clients')<a href="{{ route('admin.clients.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">list_alt</span> Ver Clientes</a>@endcan
                                @can('create clients')<a href="{{ route('admin.clients.create') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">add_circle</span> Nuevo Cliente</a>@endcan
                            </div>
                        </div>
                    @endif

                    {{-- ADMIN --}}
                    @if(auth()->user()->can('access_admin'))
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50/80 transition text-sm font-medium">
                                <span class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-base">admin_panel_settings</span>
                                    <span x-show="sidebarOpen">Admin</span>
                                </span>
                                <span x-show="sidebarOpen" class="material-symbols-outlined text-sm transition-transform"
                                    :class="open ? 'rotate-180' : ''">expand_more</span>
                            </button>
                            <div x-show="open" x-collapse class="ml-4 space-y-1 mt-1">
                                @can('view_users_menu')<a href="{{ route('admin.users.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">people</span> Usuarios</a>@endcan
                                @can('view_roles_menu')<a href="{{ route('admin.roles.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">security</span> Roles y Permisos</a>@endcan
                                @can('view_catalog_menu')<a href="{{ route('admin.catalog') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">inventory_2</span> Catálogo</a>@endcan
                                @can('manage catalog')<a href="{{ route('admin.plans') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">assignment</span> Planes y Zonas</a>@endcan
                                @can('assign supervisors to zones')<a href="{{ route('admin.supervisor-zones') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">supervisor_account</span> Supervisores x Zona</a>@endcan
                                @can('access_admin')<a href="{{ route('admin.shelves') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">shelves</span> Estanterías</a>@endcan
                                @can('access_admin')<a href="{{ route('admin.branches.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                class="material-symbols-outlined text-base">store</span> Sucursales</a>@endcan
                                @can('view_settings_menu')<a href="{{ route('admin.settings') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                        class="material-symbols-outlined text-base">settings</span> Configuración</a>@endcan
                                @can('view sla goals')<a href="{{ route('admin.sla.goals.index') }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80"><span
                                        class="material-symbols-outlined text-base">timer</span> Metas SLA</a>@endcan
                            </div>
                        </div>
                    @endif
                </nav>

                <div class="border-t border-gray-100 p-3 flex-shrink-0" x-show="sidebarOpen">
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <span class="material-symbols-outlined text-sm">circle</span>
                        <span>{{ auth()->user()->name }}</span>
                    </div>
                </div>
            </aside>

            {{-- Contenido principal --}}
            <div class="flex-1 flex flex-col overflow-hidden">
                <nav
                    class="bg-white border-b border-gray-200/80 h-14 flex items-center justify-between px-4 z-30 flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <button @click="sidebarOpen = !sidebarOpen"
                            class="text-gray-500 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-100 transition">
                            <span class="material-symbols-outlined">menu</span>
                        </button>
                        <span class="font-semibold text-gray-700 text-sm hidden sm:block">Kardex System</span>
                    </div>

                    <div class="flex items-center gap-3">
                        @if(is_null(auth()->user()->branch_id))
                            <livewire:admin.branch-switcher />
                        @endif

                        @if(Auth::user()->can('access noc panel'))
                            <livewire:notifications-badge />
                        @endif

                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open"
                                class="flex items-center gap-2 text-xs text-gray-500 bg-gray-50/80 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition">
                                <img src="https://api.dicebear.com/7.x/{{ auth()->user()->avatar_style ?? 'initials' }}/svg?seed={{ urlencode(auth()->user()->name) }}&size=24"
                                    alt="Avatar" class="w-6 h-6 rounded-full">
                                <span>{{ auth()->user()->name }}</span>
                                <span class="material-symbols-outlined text-sm transition-transform"
                                    :class="open ? 'rotate-180' : ''">arrow_drop_down</span>
                            </button>

                            <div x-show="open" @click.away="open = false" x-cloak
                                class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-200/80 overflow-hidden z-50">
                                <a href="{{ route('profile') }}"
                                    class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80">
                                    <span class="material-symbols-outlined text-base">person</span> Mi Perfil
                                </a>
                                @if(auth()->user()->can('access_admin'))
                                    <a href="{{ route('admin.settings') }}"
                                        class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80">
                                        <span class="material-symbols-outlined text-base">settings</span> Configuraciones
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                                        <span class="material-symbols-outlined text-base">logout</span> Salir
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </nav>

                <main class="flex-1 overflow-y-auto p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
    @else
        {{-- ========== LAYOUT MÍNIMO PARA INVITADOS (login, register) ========== --}}
        <main class="min-h-screen flex items-center justify-center p-4">
            {{ $slot }}
        </main>
    @endauth

    {{-- Toast (múltiples, se apilan) --}}
    <div x-data="{ toasts: [] }"
        x-on:show-toast.window="toasts.push({ id: Date.now() + Math.random(), type: $event.detail.type, message: $event.detail.message }); setTimeout(() => toasts.shift(), 5000)"
        x-on:show-toasts.window="
            $event.detail.errors.forEach(msg => {
                toasts.push({ id: Date.now() + Math.random(), type: 'error', message: msg });
                setTimeout(() => toasts.shift(), 5000);
            });
        "
        class="fixed bottom-5 right-5 z-50 flex flex-col gap-2">
        <template x-for="t in toasts" :key="t.id">
            <div :class="{
                'bg-green-600': t.type === 'success',
                'bg-red-600': t.type === 'error',
                'bg-blue-600': t.type === 'info'
            }" class="text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3 transition-all duration-300"
                x-transition:enter="transform ease-out duration-300" x-transition:enter-start="translate-y-2 opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transform ease-in duration-200"
                x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-2 opacity-0">
                <span x-show="t.type === 'success'" class="material-symbols-outlined">check_circle</span>
                <span x-show="t.type === 'error'" class="material-symbols-outlined">error</span>
                <span x-show="t.type === 'info'" class="material-symbols-outlined">info</span>
                <span x-text="t.message" class="text-sm font-medium"></span>
            </div>
        </template>
    </div>

    @livewireScripts
    @vite(['resources/js/app.js'])
    @stack('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            const audio = new Audio('{{ asset('sounds/notification.mp3') }}');
            function unlockAudio() { audio.play().then(() => { audio.pause(); audio.currentTime = 0; }).catch(() => { }); document.removeEventListener('click', unlockAudio); }
            document.addEventListener('click', unlockAudio, { once: true });
            Livewire.on('new-noc-ticket', () => { audio.play().catch(err => console.log('Audio bloqueado:', err)); });
        });
    </script>
</body>

</html>