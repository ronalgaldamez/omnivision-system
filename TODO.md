# TODO — Pendientes del sistema

> Mantenimiento de pendientes del flujo de soporte (SAC / NOC / OT / Contratos).
> Actualizado: 2026-08-01 — Rama de trabajo: `feature/crear-ot-instalacion-desde-contrato`

## Flujos del sistema (referencia)

1. **SAC resuelve directo** — SAC crea ticket → resuelve → finaliza.
2. **Escalamiento a NOC / OT** — SAC crea ticket → no resuelve → escala a NOC o crea OT.
   - **2.1** NOC resuelve remoto → finaliza.
   - **2.2** NOC no resuelve → genera OT → supervisor asigna → técnico completa.
   - **2.3** SAC genera OT directo → supervisor asigna → técnico completa.
3. **Contrato + instalación** — SAC registra cliente → Contratos → contrato → OT → supervisor asigna → técnico instala.

## Pendientes (inconsistencias de tiempos / estados)

- [ ] **INC-2 — SLA del ticket de instalación nunca se evalúa**
  Al completar la OT (`Mobile/WorkOrderShow::completeWorkOrder`) se cierra el ticket pero **no se llama `evaluateSla`**. El SLA de instalación queda sin marcar (cumplido/incumplido) y el ticket sigue contando como "en riesgo".
  → Llamar `evaluateSla` al cerrar el ticket desde la OT.

- [ ] **INC-3 — Estado del ticket post-contrato inconsistente**
  `ContractWorkflow` deja el ticket en `pending`; `ContractForm` lo deja en `in_progress`.
  → Unificar a un estado claro (ej. `in_progress` = "en instalación") hasta que el técnico complete.

- [ ] **INC-4 — `ContractForm` crea OT sin guard anti-duplicado**
  `ContractWorkflow` protege contra OT duplicada; `ContractForm` no.
  → Aplicar el mismo guard (reutilizar OT existente del ticket).

- [ ] **INC-5 — OTs creadas desde ticket sin `sla_started_at`**
  Las OT puras arrancan SLA al crearse (`WorkOrderForm`); las creadas desde ticket (SAC/NOC/contrato) no.
  → Unificar: iniciar `sla_started_at` al crear cuando corresponda.

- [ ] **INC-6 — Doble submit en SAC puede duplicar OT**
  `TicketForm::executeSolve` con `create_ot` no tiene guard anti-duplicado.
  → Validar que el ticket no tenga OT antes de crear.

## Resuelto en esta rama

- [x] **INC-1 — NOC generaba OT a mano** → ahora usa `WorkOrderService::createFromTicket` (código + zona/plan/coords + requires_noc).
- [x] Vehículo asignado por OT (`vehicle_id` en `work_orders`), selector en asignación con pre-sugerencia, columna en tabla y placa en el móvil.
- [x] Filtros y orden en `/admin/users` (estado, rol, sucursal, orden por columnas).
- [x] Toast de confirmación en edición de usuario (reemplaza flash que salía abajo).
- [x] Persistencia de draft en workflow de contratos (documentos, firma del agente, coordenadas, plan).
- [x] OT de instalación automática al crear contrato + ticket no se resuelve prematuramente.
- [x] Precarga de firma del cliente en paso 4 + botón "Volver a firmar" resetea cliente y BD.
- [x] Modales de confirmación para aprobar/rechazar (docs, coordenadas, firma).
