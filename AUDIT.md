# Auditoría del Sistema Kardex

## Estado actual — Julio 2026

Rama activa: `feature/dashboard-kpis`

---

## ✅ Completo
- **Dashboard KPIs** — Valor inventario, entradas/salidas hoy, compras mes, top productos, dispositivos por estado, clientes nuevos, OTs pendientes/completadas
- **68 componentes Livewire** funcionales en todos los módulos
- **183 tests** (328 assertions) — cobertura en módulos core
- Inventario, Compras, Técnicos, Bodega, Tickets, OTs, Dispositivos, NOC, Mobile, Admin, Reports, SLA

## 🔴 Bugs críticos (prioridad alta)

| Bug | Archivo | Impacto |
|-----|---------|---------|
| TechnicianPerformance view/component mismatch | `app/Livewire/Reports/TechnicianPerformance.php` + `dashboard.blade.php` | Renderiza todo en cero |
| Ruta `admin.settings` mal anidada | `routes/web.php:199` | URL real es `/admin/roles/admin/settings` |
| WorkOrderShow desktop: 3 TODO stubs | `app/Livewire/WorkOrders/WorkOrderShow.php` | Consumo, vinculación OTs y flags no funcionales |
| WorkOrderShow: toasts invisibles | `app/Livewire/WorkOrders/WorkOrderShow.php` | 6 dispatch usan `showToast` (debe ser `show-toast`) |
| CatalogManager: toasts invisibles | `app/Livewire/Admin/Catalog/CatalogManager.php` | 9 dispatch usan `showToast` |
| Importaciones: módulo ausente | `app/Livewire/Imports/` | No existe en esta rama (está en `feature/excel-imports`) |

## 🟠 Pendientes importantes

- Tests para Dashboard (`tests/Feature/Livewire/Reports/DashboardTest.php`)
- Vista huérfana `code-delivery-form` sin componente Livewire
- `NocPanel.php` es código muerto (ruta apunta a `NocInbox`)
- `README.md` vacío, `.env.example` desactualizado
- `welcome.blade.php` página stale de Laravel

## 📁 Archivos creados/modificados en esta sesión

- `app/Services/DashboardKpiService.php` — nuevo
- `app/Livewire/Reports/Dashboard.php` — modificado (nuevos KPIs)
- `resources/views/livewire/reports/dashboard.blade.php` — modificado (nuevas secciones)
