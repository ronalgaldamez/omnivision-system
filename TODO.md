# TODO — Pendientes del sistema

> Mantenimiento de pendientes del flujo de soporte (SAC / NOC / OT / Contratos).
> Actualizado: 2026-08-05

## Flujos del sistema (referencia)

1. **SAC resuelve directo** — SAC crea ticket → resuelve → finaliza.
2. **Escalamiento a NOC / OT** — SAC crea ticket → no resuelve → escala a NOC o crea OT.
   - **2.1** NOC resuelve remoto → finaliza.
   - **2.2** NOC no resuelve → genera OT → supervisor asigna → técnico completa.
   - **2.3** SAC genera OT directo → supervisor asigna → técnico completa.
3. **Contrato + instalación** — SAC registra cliente → Contratos → contrato → OT → supervisor asigna → técnico instala.

## Pendientes resueltos

- [x] **INC-1 — NOC generaba OT a mano** → usa `WorkOrderService::createFromTicket`.
- [x] **INC-2 — SLA del ticket de instalación nunca se evalúa** → `WorkOrderShow::completeWorkOrder` llama `evaluateSla` al cerrar el ticket.
- [x] **INC-3 — Estado del ticket post-contrato inconsistente** → `ContractWorkflow` y `ContractForm` dejan el ticket en `in_progress` (en instalación) hasta que el técnico complete.
- [x] **INC-4 — `ContractForm` creaba OT sin guard anti-duplicado** → guard central en `WorkOrderService::createFromTicket/createFromContract`.
- [x] **INC-5 — OTs desde ticket sin `sla_started_at`** → el servicio lo setea por defecto (y la OT de campo en móvil).
- [x] **INC-6 — Doble submit en SAC podía duplicar OT** → cubierto por el guard central (SAC/NOC/Contratos/Dashboard pasan por el servicio).

## Notas de arquitectura

- **`WorkOrderService::createFromTicket` / `createFromContract`** son los únicos puntos de creación de OTs desde ticket/contrato e incluyen el guard anti-duplicado central.
- **`generateCode`** es el único punto de generación de códigos de OT.
- Las OTs puras (`WorkOrderForm`) y de campo (`Mobile/WorkOrderList`) se crean directas con `sla_started_at`.
