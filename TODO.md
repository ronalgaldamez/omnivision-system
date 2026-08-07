# TODO — Vista de Rendimiento Espectacular (`/reports/performance`)

> Panel de rendimiento comercial y operativo con las métricas del Trello + todo lo medible del sistema.
> Creación: 2026-08-07 — Rama sugerida: `feature/vista-rendimiento-performance`

## 📌 Objetivo

Crear una vista de rendimiento de nivel "espectacular" (nada básico) que centralice:

1. **Ventas por mes** — rendimiento de agentes de ventas por ventas realizadas.
2. **Instalaciones realizadas** — cuántas instalaciones se completaron.
3. **Fallos solucionados** — cuántos fallos se resolvieron.

Y sumar TODO lo que el sistema ya puede medir (comercial, técnico, soporte y operativo).

## 🗺️ Mapeo a datos reales

| Métrica | Modelo | Cómo se mide |
|---------|--------|--------------|
| Ventas por mes | `Contract` | `created_by` (agente) + `contract_date` + `price`/`installation_cost` ($) |
| Instalaciones realizadas | `WorkOrder` | `service_type='instalacion'` + `status='completed'` + `completed_date` |
| Fallos solucionados | `Ticket` | `status='resolved'` + `resolved_at` + `resolved_by` + `priority` + `sla_met` |

## 📋 FASES DE TRABAJO

---

### FASE 1 — Permisos y rutas

- [x] **1.1** Agregar `ViewPerformanceReports = 'view performance reports'` a `app/Enums/PermissionEnum.php`.
- [x] **1.2** Asignar el permiso en `database/seeders/RolesAndPermissionsSeeder.php` a:
  - `admin` (ya recibe todos), `branch_admin`, `accountant`, `field_supervisor`, `sales_rep`, `noc`.
- [x] **1.3** Re-ejecutar: `php artisan db:seed --class=RolesAndPermissionsSeeder`.
- [x] **1.4** ⚠️ Usuarios con permisos personalizados (sistema híbrido `User::hasPermissionTo`) NO heredan el nuevo permiso del rol → asignarlos manualmente o documentarlo.
- [x] **1.5** Ruta en `routes/web.php`: `GET /reports/performance` → `App\Livewire\Reports\PerformanceReport` con middleware `can:view performance reports`, dentro del grupo `reports`.
- [x] **1.6** Link en el sidebar (`resources/views/components/layouts/app.blade.php`) dentro del menú Reportes, con icono y gate por permiso.

---

### FASE 2 — Servicio de datos (lógica en Servicios, no en el componente)

Crear `app/Services/PerformanceReportService.php` — una pasada por métrica, índices, **sin N+1**.

- [x] **2.1** Métodos base y filtros comunes (rango de fechas, departamento, sucursal, plan).
- [x] **2.2** `heroKpis($filters)`: 8 KPIs con variación % vs período anterior:
  - Ingresos por ventas ($), Contratos nuevos, Ticket promedio por contrato,
  - Instalaciones completadas, Tasa de éxito de instalación,
  - Fallos resueltos, Tiempo promedio de resolución, Cumplimiento SLA.
- [x] **2.3** Ventas / comercial:
  - `salesMonthly($filters)` — ingresos ($) y cantidad de contratos por mes.
  - `salesByAgent($filters)` — ranking de agentes por $ y por cantidad (top N).
  - `salesByPlan($filters)` — contratos e ingresos por plan.
  - `salesByZone($filters)` — por departamento (departamento/municipio/distrito de El Salvador).
  - `salesByServiceType($filters)` — por tipo de servicio contratado.
  - `salesByStatus($filters)` — contratos completos (isFullySigned + docs) vs en proceso vs cancelados.
- [x] **2.4** Instalaciones / técnico:
  - `installationsMonthly($filters)` — completadas vs asignadas por mes.
  - `installationSuccessRate($filters)` — completadas / asignadas.
  - `installationsByTechnician($filters)` — ranking por instalaciones completadas.
  - `averageInstallationTime($filters)` — `assigned_at` → `completed_date` (promedio por mes).
  - `installationsByZone($filters)` — por departamento.
  - `installationsPending($filters)` — cola de asignadas sin completar.
- [x] **2.5** Fallos / soporte:
  - `failuresMonthly($filters)` — tickets resueltos por mes, desglosados L1 (SAC) vs NOC (`requires_noc`).
  - `failuresByPriority($filters)` — P1-P4.
  - `failuresByResolver($filters)` — ranking de quien resuelve.
  - `averageResolutionTime($filters)` — `created_at` → `resolved_at` (promedio, y por prioridad).
  - `slaComplianceReport($filters)` — % cumplimiento global y por prioridad (`sla_met`).
  - `failuresByServiceType($filters)` — top tipos de fallo.
  - `escalations($filters)` — tickets escalados a NOC vs resueltos en L1.
- [x] **2.6** Funnel comercial:
  - `commercialFunnel($filters)` — clientes nuevos → contratos firmados → instalaciones completadas → ingresos ($).
  - `conversionRates($filters)` — ticket→contrato y contrato→instalación.
- [x] **2.7** Eficiencia técnica (bonus):
  - `technicalEfficiency($filters)` — requisiciones aprobadas, devoluciones **sobrantes vs dañados** por técnico (pérdida material).
  - `inventorySnapshot($filters)` — valor de inventario, stock bajo, movimientos por mes.
  - `shipmentsByStatus($filters)` — distribución por estado (`pending→in_transit→delivered→confirmed`).

---

### FASE 3 — Componente Livewire

Crear `app/Livewire/Reports/PerformanceReport.php`

- [x] **3.1** Props de filtros: `fechaInicio`, `fechaFin`, `departamento`, `sucursalId`, `planId`.
- [x] **3.2** Presets de rango: 7d / 30d / 90d / 12m (botones).
- [x] **3.3** `mount()` — defaults: mes actual vs mes anterior (para comparación de KPIs).
- [x] **3.4** `updated{Filter}()` — recalculado reactivo al cambiar filtros.
- [x] **3.5** Listas para selects: departamentos (de El Salvador), sucursales activas, planes activos.
- [x] **3.6** `render()` — pasa datos del servicio a la vista con `->layout('components.layouts.app')`.

---

### FASE 4 — Vista

Crear `resources/views/livewire/reports/performance-report.blade.php`

- [x] **4.1** Barra de filtros (fechas + presets + departamento + sucursal + plan).
- [x] **4.2** Hero de KPIs — tarjetas con valor + variación % vs período anterior (verde/rojo).
- [x] **4.3** Sección VENTAS:
  - Área: ingresos por mes ($).
  - Barra: contratos por mes.
  - Ranking de agentes de ventas (top).
  - Donut: por plan / por departamento.
- [x] **4.4** Sección INSTALACIONES:
  - Barra: completadas vs asignadas por mes.
  - Ranking de técnicos.
  - Tiempo promedio de instalación (línea/área).
  - Desglose por departamento.
- [x] **4.5** Sección FALLOS:
  - Área: resueltos por mes (L1 vs NOC).
  - Donut: por prioridad.
  - Cumplimiento SLA (radial/donut).
  - Ranking de resueltores.
- [x] **4.6** Funnel comercial (visual de embudo).
- [x] **4.7** Eficiencia técnica (sobrantes vs dañados, stock bajo).
- [x] **4.8** Charts con `wire:key` estable y `x-charts.apex` (reusar el fix de actualización ya aplicado).
- [x] **4.9** Estados vacíos, loading y responsive (Tailwind).
- [x] **4.10** UI 100% con componentes de `omnivision-design` — nada genérico.

---

### FASE 5 — Verificación

- [x] **5.1** `php -l` en los archivos nuevos.
- [x] **5.2** `php artisan view:cache` (vistas compilan).
- [x] **5.3** Probar con `TestDataSeeder` + datos reales. *(Se creó `PerformanceDemoSeeder`: idempotente, dev-only, con `contract_date`/`completed_date`/`resolved_at` correctos. Ejecutado — la vista quedó poblada.)*
- [x] **5.4** Validar permisos: cada rol ve la vista solo si corresponde (admin sí, SAC no, etc.).
- [x] **5.5** Revisar queries: sin N+1, sin consultas por técnico en loop.
- [x] **5.6** Validar filtros combinados (rango + departamento + sucursal + plan).
- [x] **5.7** Comparación de KPIs vs período anterior correcta (bordes de mes).
- [x] **5.8** Responsive en móvil/tablet/desktop.

---

## ⚠️ RIESGOS Y DEPENDENCIAS

- **Permisos**: re-ejecutar `RolesAndPermissionsSeeder` puede afectar roles personalizados. El sistema híbrido hace que usuarios con permisos directos no hereden el nuevo permiso.
- **Rendimiento**: muchas agregaciones por render → hacerlas en una sola pasada por métrica y agregar índices si hace falta. Evitar N+1 a toda costa.
- **Datos localizados**: desgloses geográficos SOLO con departamento/municipio/distrito de El Salvador (regla del AGENTS.md).
- **SLA**: reutilizar `SlaService` para métricas de cumplimiento y at-risk (no duplicar lógica).
- **Fechas**: decidir semántica de "completado" (`completed_date`) y "resuelto" (`resolved_at`) — ya corregidas en técnicos, aplicarlas acá igual.

## 🏁 Criterios de terminado (Definition of Done)

- [x] Vista `/reports/performance` funcional con TODAS las secciones.
- [x] Permiso `view performance reports` creado, asignado y documentado.
- [x] Lógica 100% en `PerformanceReportService` (cero lógica de negocio en el componente).
- [x] Cero N+1 verificable (log de queries).
- [x] UI con componentes de `omnivision-design`.
- [x] Desgloses geográficos con datos de El Salvador.
- [x] Sin errores de sintaxis ni de compilación de vistas.

---

## 📋 PRÓXIMAS MEJORAS (pendientes del sistema)

> Detección: 2026-08-07 — prioridades sugeridas A → B → C → D → E

### A. ApexCharts local (quitar dependencia de CDN)

- [x] **A.1** Descargar `apexcharts.min.js` (versión actual 3.49.1) a `public/js/apexcharts.min.js`.
- [x] **A.2** Reemplazar el `<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/...">` en `resources/views/components/layouts/app.blade.php` por el asset local.
- [x] **A.3** Verificar que los charts de Dashboard, Técnicos, SLA y Rendimiento funcionen con internet cortada.

### B. Consistencia de estados de requisición

- [x] **B.1** Auditar dónde se usa `open` y `heredada` en requisiciones (RequisitionIndex, RequisitionDetail, WorkOrderShow, Mobile, Tickets).
- [x] **B.2** Definir el flujo objetivo: `pending → approved → closed` | `rejected` (sin `open`).
- [x] **B.3** Mapear datos legados: `open` → `approved` (los que están activos) y `heredada` → definir.
- [x] **B.4** Actualizar queries, validaciones y detalle de requisición a los estados reales.
- [x] **B.5** Verificar que los KPIs del dashboard y el reporte de técnicos sigan correctos.

### C. Semántica de instalaciones en el dashboard

- [x] **C.1** Decidir: "instalaciones este mes" = creadas (`created_at`) o completadas (`completed_date`).
- [x] **C.2** Aplicar la decisión al `monthlyComparison` del dashboard (hoy usa `created_at`).
- [x] **C.3** Alinear con el resto del sistema (técnicos y rendimiento ya usan `completed_date`).

### D. Reverb como servicio persistente (dev)

- [x] **D.1** Investigar el mecanismo según SO (Windows: NSSM/tarea programada; Linux: systemd/supervisor).
- [x] **D.2** Documentar el comando `php artisan reverb:start` y cómo dejarlo corriendo.
- [x] **D.3** (Opcional) script de arranque automático.

### E. Limpieza de seeders

- [x] **E.1** Decidir el destino de `TestDataSeeder` (legacy, sin guard anti-duplicado): eliminar o marcar dev-only.
- [x] **E.2** Confirmar que `PerformanceDemoSeeder` es el único seeder de demo y está documentado como dev-only.
