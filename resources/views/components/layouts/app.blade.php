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
        body { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
            font-size: 1.25rem;
        }
        [x-cloak] { display: none !important; }
        .nav-dropdown {
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            transition: all 0.2s ease;
        }
        .nav-group:hover .nav-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
    </style>
    @livewireStyles
</head>

<body class="bg-gray-50 text-gray-800 text-sm">
    <livewire:global-notification />
    <div x-data="{ open: false }" class="min-h-screen">
        <nav class="bg-white border-b border-gray-200/80 shadow-sm sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-14 items-center">
                    <div class="flex items-center gap-x-6">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center shadow-sm">
                                <span class="material-symbols-outlined text-white text-xl">inventory</span>
                            </div>
                            <span class="font-semibold text-gray-700 text-sm">Kardex System</span>
                        </a>

                        <!-- Menú escritorio -->
                        <div class="hidden md:flex md:items-center md:gap-x-1">
                            @auth
                                {{-- INVENTARIO --}}
                                @if(module_active('inventory') && auth()->user()->canAny(['view movements', 'view products', 'create movements', 'view kardex']))
                                <div class="nav-group relative">
                                    <button class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50/80 transition text-sm font-medium">
                                        <span class="material-symbols-outlined text-base">inventory</span> Inventario
                                        <span class="material-symbols-outlined text-base">expand_more</span>
                                    </button>
                                    <div class="nav-dropdown absolute left-0 top-full pt-1 z-20">
                                        <div class="bg-white rounded-xl border border-gray-200/80 shadow-lg min-w-[200px] py-1.5">
                                            @can('view movements')
                                                <a href="{{ route('movements.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                    <span class="material-symbols-outlined text-base">list_alt</span> Movimientos
                                                </a>
                                            @endcan
                                            @can('create movements')
                                                <a href="{{ route('movements.create') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                    <span class="material-symbols-outlined text-base">add_circle</span> Nuevo Movimiento
                                                </a>
                                            @endcan
                                            @can('view products')
                                                <a href="{{ route('products.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                    <span class="material-symbols-outlined text-base">box</span> Productos
                                                </a>
                                            @endcan
                                            @can('view kardex')
                                                <a href="{{ route('kardex.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                    <span class="material-symbols-outlined text-base">receipt</span> Kardex
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                                @endif

                                {{-- COMPRAS --}}
                                @if(module_active('suppliers') && auth()->user()->canAny(['view suppliers', 'view purchases', 'create purchases']))
                                <div class="nav-group relative">
                                    <button class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50/80 transition text-sm font-medium">
                                        <span class="material-symbols-outlined text-base">shopping_cart</span> Compras
                                        <span class="material-symbols-outlined text-base">expand_more</span>
                                    </button>
                                    <div class="nav-dropdown absolute left-0 top-full pt-1 z-20">
                                        <div class="bg-white rounded-xl border border-gray-200/80 shadow-lg min-w-[200px] py-1.5">
                                            @can('view suppliers')
                                                <a href="{{ route('suppliers.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                    <span class="material-symbols-outlined text-base">business</span> Proveedores
                                                </a>
                                            @endcan
                                            @can('view purchases')
                                                <a href="{{ route('purchases.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                    <span class="material-symbols-outlined text-base">history</span> Historial de Compras
                                                </a>
                                            @endcan
                                            @can('create purchases')
                                                <a href="{{ route('purchases.create') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                    <span class="material-symbols-outlined text-base">add_shopping_cart</span> Nueva Compra
                                                </a>
                                            @endcan
                                            @can('create purchases')
                                                <a href="{{ route('returns.create') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                    <span class="material-symbols-outlined text-base">assignment_return</span> Devolución
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                                @endif

                                {{-- TÉCNICOS (para técnico: enlaces directos; para warehouse/admin: dropdown) --}}
                                @if(module_active('technicians'))
                                    @if(auth()->user()->hasRole('technician'))
                                        @php
                                            $hasTechAny = auth()->user()->canAny(['view technician_requests', 'create technician_requests', 'view work_orders']);
                                        @endphp
                                        @if($hasTechAny)
                                            <div class="flex items-center gap-1">
                                                @can('view technician_requests')
                                                    <a href="{{ route('mobile.technician.requests') }}" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50/80 transition text-sm font-medium">
                                                        <span class="material-symbols-outlined text-base">list_alt</span> Mis Solicitudes
                                                    </a>
                                                @endcan
                                                @can('create technician_requests')
                                                    <a href="{{ route('mobile.technician.requests.create') }}" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50/80 transition text-sm font-medium">
                                                        <span class="material-symbols-outlined text-base">add_task</span> Nueva Solicitud
                                                    </a>
                                                @endcan
                                                @can('view work_orders')
                                                    <a href="{{ route('mobile.work-orders.list') }}" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50/80 transition text-sm font-medium">
                                                        <span class="material-symbols-outlined text-base">work</span> Mis Órdenes
                                                    </a>
                                                    <a href="{{ route('mobile.work-orders.map') }}" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50/80 transition text-sm font-medium">
                                                        <span class="material-symbols-outlined text-base">map</span> Mapa de Órdenes
                                                    </a>
                                                @endcan
                                            </div>
                                        @endif
                                    @elseif(auth()->user()->hasAnyRole(['warehouse', 'admin']))
                                        @php
                                            $hasTechWarehouseAny = auth()->user()->canAny(['view technician_requests', 'approve technician_requests', 'view technician_returns', 'create technician_returns', 'view work_orders']);
                                        @endphp
                                        @if($hasTechWarehouseAny)
                                        <div class="nav-group relative">
                                            <button class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50/80 transition text-sm font-medium">
                                                <span class="material-symbols-outlined text-base">handyman</span> Técnicos
                                                <span class="material-symbols-outlined text-base">expand_more</span>
                                            </button>
                                            <div class="nav-dropdown absolute left-0 top-full pt-1 z-20">
                                                <div class="bg-white rounded-xl border border-gray-200/80 shadow-lg min-w-[200px] py-1.5">
                                                    @can('view technician_requests')
                                                        <a href="{{ route('technician-requests.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                            <span class="material-symbols-outlined text-base">inbox</span> Gestionar Solicitudes
                                                        </a>
                                                    @endcan
                                                    @can('approve technician_requests')
                                                        <a href="{{ route('code-delivery.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                            <span class="material-symbols-outlined text-base">qr_code_scanner</span> Escáner QR
                                                        </a>
                                                    @endcan
                                                    @if(module_active('technician_returns'))
                                                        @can('view technician_returns')
                                                            <a href="{{ route('technician-returns.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                                <span class="material-symbols-outlined text-base">assignment_return</span> Devoluciones
                                                            </a>
                                                        @endcan
                                                        @can('create technician_returns')
                                                            <a href="{{ route('technician-returns.create') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                                <span class="material-symbols-outlined text-base">add_circle</span> Registrar Devolución
                                                            </a>
                                                        @endcan
                                                    @endif
                                                    @if(module_active('work_orders'))
                                                        @can('view work_orders')
                                                            <a href="{{ route('work-orders.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                                <span class="material-symbols-outlined text-base">work</span> Órdenes de Trabajo
                                                            </a>
                                                            <a href="{{ route('work-orders.map') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                                <span class="material-symbols-outlined text-base">map</span> Mapa de OT
                                                            </a>
                                                        @endcan
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    @endif
                                @endif

                                {{-- REPORTES --}}
                                @if(module_active('reports') && auth()->user()->can('view reports'))
                                <div class="nav-group relative">
                                    <button class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50/80 transition text-sm font-medium">
                                        <span class="material-symbols-outlined text-base">assessment</span> Reportes
                                        <span class="material-symbols-outlined text-base">expand_more</span>
                                    </button>
                                    <div class="nav-dropdown absolute left-0 top-full pt-1 z-20">
                                        <div class="bg-white rounded-xl border border-gray-200/80 shadow-lg min-w-[200px] py-1.5">
                                            <a href="{{ route('reports.stock') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                <span class="material-symbols-outlined text-base">inventory</span> Stock bajo
                                            </a>
                                            <a href="{{ route('reports.movements') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                <span class="material-symbols-outlined text-base">swap_vert</span> Movimientos
                                            </a>
                                            <a href="{{ route('reports.technicians') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                <span class="material-symbols-outlined text-base">handyman</span> Rendimiento técnicos
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                {{-- SOPORTE (escritorio) --}}
                                @php
                                    $hasSupportAccess = auth()->user()->can('create tickets') ||
                                                        auth()->user()->can('view any tickets') ||
                                                        auth()->user()->can('view own tickets') ||
                                                        auth()->user()->can('access noc panel');
                                @endphp
                                @if($hasSupportAccess)
                                <div class="nav-group relative">
                                    <button class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50/80 transition text-sm font-medium">
                                        <span class="material-symbols-outlined text-base">support_agent</span> Soporte
                                        <span class="material-symbols-outlined text-base">expand_more</span>
                                    </button>
                                    <div class="nav-dropdown absolute left-0 top-full pt-1 z-20">
                                        <div class="bg-white rounded-xl border border-gray-200/80 shadow-lg min-w-[200px] py-1.5">
                                            @can('create tickets')
                                                <a href="{{ route('tickets.create') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                    <span class="material-symbols-outlined text-base">add_comment</span> Nuevo Ticket
                                                </a>
                                            @endcan
                                            @can('view any tickets')
                                                <a href="{{ route('tickets.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                    <span class="material-symbols-outlined text-base">list_alt</span> Todos los Tickets
                                                </a>
                                            @elsecan('view own tickets')
                                                <a href="{{ route('tickets.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                    <span class="material-symbols-outlined text-base">chat</span> Mis Tickets
                                                </a>
                                            @endcan
                                            @can('access noc panel')
                                                <a href="{{ route('noc.panel') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                    <span class="material-symbols-outlined text-base">settings_overscan</span> Panel NOC
                                                </a>
                                            @endcan
                                            @can('view own work_orders')
                                                @if(auth()->user()->hasAnyRole(['noc', 'secretary']))
                                                    <a href="{{ route('work-orders.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                        <span class="material-symbols-outlined text-base">work</span> Mis Órdenes Relacionadas
                                                    </a>
                                                @endif
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                                @endif

                                {{-- ADMIN --}}
                                @if(auth()->user()->hasRole('admin'))
                                <div class="nav-group relative">
                                    <button class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50/80 transition text-sm font-medium">
                                        <span class="material-symbols-outlined text-base">admin_panel_settings</span> Admin
                                        <span class="material-symbols-outlined text-base">expand_more</span>
                                    </button>
                                    <div class="nav-dropdown absolute left-0 top-full pt-1 z-20">
                                        <div class="bg-white rounded-xl border border-gray-200/80 shadow-lg min-w-[200px] py-1.5">
                                            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                <span class="material-symbols-outlined text-base">people</span> Usuarios
                                            </a>
                                            <a href="{{ route('admin.roles.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                <span class="material-symbols-outlined text-base">security</span> Roles y Permisos
                                            </a>
                                            <hr class="my-1.5 border-gray-100">
                                            @can('manage catalog')
                                                <a href="{{ route('admin.catalog') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                    <span class="material-symbols-outlined text-base">inventory_2</span> Catálogo
                                                </a>
                                            @endcan
                                            <a href="{{ route('admin.settings') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50/80 transition">
                                                <span class="material-symbols-outlined text-base">settings</span> Configuración
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endauth
                        </div>
                    </div>

                    <!-- Perfil y logout -->
                    @auth
                        <div class="hidden md:flex items-center gap-3">
                            {{-- Notificaciones NOC (solo se ve si tiene acceso al panel NOC) --}}
                            @if(Auth::user()->can('access noc panel'))
                                <livewire:notifications-badge />
                            @endif

                            <div class="flex items-center gap-2 text-xs text-gray-500 bg-gray-50/80 px-3 py-1.5 rounded-lg">
                                <span class="material-symbols-outlined text-base">account_circle</span>
                                <span>{{ auth()->user()->name }}</span>
                            </div>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="flex items-center gap-1.5 text-gray-500 hover:text-gray-700 text-sm font-medium transition">
                                    <span class="material-symbols-outlined text-base">logout</span> Salir
                                </button>
                            </form>
                        </div>
                    @endauth

                    <!-- Botón hamburguesa (móvil) -->
                    <div class="flex items-center md:hidden">
                        <button @click="open = !open" class="text-gray-500 hover:text-gray-700 focus:outline-none p-1.5 rounded-lg hover:bg-gray-100 transition">
                            <span class="material-symbols-outlined">menu</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Menú móvil -->
            <div x-show="open" @click.away="open = false" x-transition.duration.200ms
                class="md:hidden bg-white border-t border-gray-200 shadow-lg">
                <div class="px-4 pt-3 pb-4 space-y-2 max-h-[calc(100vh-3.5rem)] overflow-y-auto">
                    @auth
                        @if(module_active('inventory') && auth()->user()->canAny(['view movements', 'view products', 'create movements', 'view kardex']))
                            <div class="space-y-1">
                                <h3 class="px-3 py-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Inventario</h3>
                                @can('view movements')<a href="{{ route('movements.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Movimientos</a>@endcan
                                @can('create movements')<a href="{{ route('movements.create') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Nuevo Movimiento</a>@endcan
                                @can('view products')<a href="{{ route('products.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Productos</a>@endcan
                                @can('view kardex')<a href="{{ route('kardex.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Kardex</a>@endcan
                            </div>
                        @endif

                        @if(module_active('suppliers') && auth()->user()->canAny(['view suppliers', 'view purchases', 'create purchases']))
                            <div class="space-y-1">
                                <h3 class="px-3 py-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Compras</h3>
                                @can('view suppliers')<a href="{{ route('suppliers.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Proveedores</a>@endcan
                                @can('view purchases')<a href="{{ route('purchases.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Historial de Compras</a>@endcan
                                @can('create purchases')<a href="{{ route('purchases.create') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Nueva Compra</a>@endcan
                                @can('create purchases')<a href="{{ route('returns.create') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Devolución</a>@endcan
                            </div>
                        @endif

                        @if(module_active('technicians'))
                            @if(auth()->user()->hasRole('technician'))
                                @php $hasTechAnyMobile = auth()->user()->canAny(['view technician_requests', 'create technician_requests', 'view work_orders']); @endphp
                                @if($hasTechAnyMobile)
                                    @can('view technician_requests')<a href="{{ route('mobile.technician.requests') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50/80">Mis Solicitudes</a>@endcan
                                    @can('create technician_requests')<a href="{{ route('mobile.technician.requests.create') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50/80">Nueva Solicitud</a>@endcan
                                    @can('view work_orders')<a href="{{ route('mobile.work-orders.list') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50/80">Mis Órdenes</a>@endcan
                                    @can('view work_orders')<a href="{{ route('mobile.work-orders.map') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50/80">Mapa de Órdenes</a>@endcan
                                @endif
                            @elseif(auth()->user()->hasAnyRole(['warehouse', 'admin']))
                                @php $hasTechWarehouseAnyMobile = auth()->user()->canAny(['view technician_requests', 'approve technician_requests', 'view technician_returns', 'create technician_returns', 'view work_orders']); @endphp
                                @if($hasTechWarehouseAnyMobile)
                                    <div class="space-y-1">
                                        <h3 class="px-3 py-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Técnicos</h3>
                                        @can('view technician_requests')<a href="{{ route('technician-requests.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Gestionar Solicitudes</a>@endcan
                                        @can('approve technician_requests')<a href="{{ route('code-delivery.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Escáner QR</a>@endcan
                                        @if(module_active('technician_returns'))
                                            @can('view technician_returns')<a href="{{ route('technician-returns.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Devoluciones</a>@endcan
                                            @can('create technician_returns')<a href="{{ route('technician-returns.create') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Registrar Devolución</a>@endcan
                                        @endif
                                    </div>
                                @endif
                            @endif
                        @endif

                        @if(module_active('reports') && auth()->user()->can('view reports'))
                            <div class="space-y-1">
                                <h3 class="px-3 py-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Reportes</h3>
                                <a href="{{ route('reports.stock') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Stock bajo</a>
                                <a href="{{ route('reports.movements') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Movimientos</a>
                                <a href="{{ route('reports.technicians') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Rendimiento técnicos</a>
                            </div>
                        @endif

                        {{-- SOPORTE MÓVIL --}}
                        @php $showSupportMobile = module_active('work_orders') && (auth()->user()->can('create tickets') || auth()->user()->can('view any tickets') || auth()->user()->can('view own tickets') || auth()->user()->can('access noc panel')); @endphp
                        @if($showSupportMobile)
                            <div class="space-y-1">
                                <h3 class="px-3 py-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Soporte</h3>
                                @can('create tickets')
                                    <a href="{{ route('tickets.create') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Nuevo Ticket</a>
                                @endcan
                                @can('view any tickets')
                                    <a href="{{ route('tickets.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Todos los Tickets</a>
                                @elsecan('view own tickets')
                                    <a href="{{ route('tickets.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Mis Tickets</a>
                                @endcan
                                @can('access noc panel')
                                    <a href="{{ route('noc.panel') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Panel NOC</a>
                                @endcan
                                @can('view own work_orders')
                                    @if(auth()->user()->hasAnyRole(['noc', 'secretary']))
                                        <a href="{{ route('work-orders.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Mis Órdenes Relacionadas</a>
                                    @endif
                                @endcan
                            </div>
                        @endif

                        @if(auth()->user()->hasRole('admin'))
                            <div class="space-y-1">
                                <h3 class="px-3 py-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Admin</h3>
                                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Usuarios</a>
                                <a href="{{ route('admin.roles.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Roles y Permisos</a>
                                @can('manage catalog')<a href="{{ route('admin.catalog') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Catálogo</a>@endcan
                                <a href="{{ route('admin.settings') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50/80 ml-2">Configuración</a>
                            </div>
                        @endif

                        <hr class="my-2 border-gray-100">
                        <div class="flex items-center gap-2 px-3 py-2 text-sm text-gray-500">
                            <span class="material-symbols-outlined text-base">account_circle</span>
                            <span>{{ auth()->user()->name }}</span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="flex items-center gap-2 w-full text-left px-3 py-2 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 transition">
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

    <!-- Toast -->
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 5000)"
        x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300"
        x-transition:enter="transform ease-out duration-300" x-transition:enter-start="translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transform ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-2 opacity-0"
        style="display: none;">
        <div x-show="toastType === 'success'" class="bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">check_circle</span>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
        <div x-show="toastType === 'error'" class="bg-red-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">error</span>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
        <div x-show="toastType === 'info'" class="bg-blue-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">info</span>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
    </div>

    @livewireScripts
    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>

</html>