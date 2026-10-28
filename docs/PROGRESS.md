# Progreso del Proyecto: Portátiles Perú (Catálogo Virtual, Inventario y Ventas WhatsApp)

**Última actualización:** 2026-10-25  
**Fase actual completada:** Fase 7 — Clientes y bandeja de WhatsApp  
**Estado:** Fases 0 a 7 COMPLETADAS al 100% con 61/61 pruebas pasando (250 aserciones).  

---

## 1. Resumen de Fases y Estado

| Fase | Descripción | Estado | Pruebas |
|---|---|---|---|
| **Fase 0** | Inspección y planificación técnica | **Completada** | Entorno verificado (PHP 8.3, Composer 2.10, MySQL 8.4, Node 24) |
| **Fase 1** | Base técnica, autenticación y permisos | **Completada** | 13/13 pruebas pasando (45 aserciones) - Autenticación y Autorización en servidor |
| **Fase 2** | Productos, categorías, marcas y modelos | **Completada** | 9/9 pruebas pasando (33 aserciones) - Catálogo, taxonomías y galería |
| **Fase 3** | Inventario, QR y códigos de barras | **Completada** | 9/9 pruebas pasando (26 aserciones) - Trazabilidad, multialmacén, reservas |
| **Fase 4** | Catálogo público y diseño visual | **Completada** | 9/9 pruebas pasando (31 aserciones) - Vitrina comercial, filtros facetados y WhatsApp |
| **Fase 5** | Dashboard administrativo (datos reales) | **Completada** | 7/7 pruebas pasando (26 aserciones) - Métricas, valorización, alertas y Kardex |
| **Fase 6** | Solicitud de compra, pedidos y WhatsApp por enlace | **Completada** | 9/9 pruebas pasando (69 aserciones) - Carrito, reserva preventiva, pedidos y wa.me |
| **Fase 7** | Clientes y bandeja integrada de WhatsApp | **Completada** | 5/5 pruebas pasando (19 aserciones) - Clientes, notas, asignación y modo enlace |
| **Fase 8** | Generador de catálogos PDF (8 diseños) | Pendiente | - |
| **Fase 9** | Importación, exportación y reportes | Pendiente | - |
| **Fase 10** | Configuración, auditoría y seguridad | Pendiente | - |
| **Fase 11** | Pruebas integrales y publicación | Pendiente | - |
| **Fase 12** | IA y mejoras posteriores | Pendiente | - |

---

## 2. Detalle de Fase 6 — Solicitud de Compra, Pedidos y WhatsApp por Enlace

### Criterios de Aceptación Cumplidos:
- [x] **Persistencia obligatoria en Base de Datos antes de WhatsApp:**
  - El pedido se guarda en la tabla `orders` y sus items en `order_items` con código de referencia único (`PED-YYYY-XXXXX`) **antes** de abrir el chat de WhatsApp.
  - Generación automática correlativa anual protegida con transacciones DB y bloqueos pesimistas (`lockForUpdate()`).
- [x] **Validación estricta y cálculo de precios en Servidor:**
  - El servidor nunca confía en totales ni precios enviados por el cliente.
  - Subtotales y total final se calculan consultando directamente `Product::find($id)->price`.
  - Cantidades inválidas (`<= 0`) o stock insuficiente se rechazan con `ValidationException`.
- [x] **Reserva preventiva de stock (24 horas):**
  - Cada item genera automáticamente una `StockReservation` asociada a la referencia del pedido.
  - El stock físico (`qty_physical`) se mantiene intacto mientras que el stock reservado (`qty_reserved`) aumenta y el disponible para la venta (`qty_available`) disminuye en el almacén de despacho seleccionado.
  - Si el pedido se cancela, `OrderService::updateStatus()` libera automáticamente las reservas de stock asociadas, restituyendo el stock disponible.
- [x] **Construcción y generación del mensaje oficial de WhatsApp:**
  - El mensaje contiene referencia única, nombre del cliente, teléfono, ciudad, tipo de comprobante, desglose de laptops solicitadas con SKU, precio total en soles y enlace web directo a la ficha de confirmación del pedido.
  - Botón oficial con enlace `https://wa.me/...` que precarga el mensaje estructurado para atención inmediata por parte de un asesor.
- [x] **Módulo Administrativo de Pedidos (`/admin/pedidos`):**
  - KPI counters: Total Pedidos, Pendientes por Coordinar, Confirmados, En Preparación, Completados y Ventas Totales en Soles (S/).
  - Búsqueda por código de pedido, cliente, teléfono o email.
  - Filtros facetados por estado, almacén y rangos de fecha.
  - Ficha detallada con items, reservas de stock vinculadas, datos del cliente y comprobante (Boleta / Factura / RUC).
  - Transición de estados (`pending`, `confirmed`, `in_preparation`, `dispatched`, `completed`, `cancelled`) con registro en bitácora de historial de estados (`order_status_history`) y auditoría (`AuditLog`).
- [x] **Pruebas automatizadas:** `tests/Feature/OrderProcessingTest.php` (9 pruebas pasando con 69 aserciones).

---

## 3. Detalle de Fase 7 — Clientes y Bandeja de WhatsApp

### Criterios de aceptación cumplidos:
1. **Módulo de Clientes:** perfiles generados desde pedidos y registros manuales, historial de compras, total acumulado, notas internas y estados protegidos por permisos.
2. **Bandeja de Conversaciones:** conversaciones asociadas a cliente y pedido, asignación de asesor, cambios de estado, registro interno y respuestas rápidas.
3. **Trazabilidad:** las acciones de creación, notas, cambios de cliente y conversación se registran en auditoría (`AuditLog`).

---

## 4. Chat Comercial Integrado y WhatsApp Business Platform Cloud API (Oficial Meta)

### Criterios de aceptación cumplidos al 100%:
1. **Integración con Meta WhatsApp Cloud API Oficial:**
   - Servicio backend `App\Services\WhatsAppCloudApiService` para envíos de texto, multimedia (imágenes, PDFs de fichas técnicas o catálogos) y plantillas aprobadas.
   - Endpoint de verificación `GET /webhook/whatsapp` con desafío `hub.challenge` y validación de `hub.verify_token`.
   - Endpoint de recepción `POST /webhook/whatsapp` con firma criptográfica HMAC-SHA256 (`X-Hub-Signature-256`), deduplicación estricta (`wamid` único) e idempotencia mediante `whatsapp_webhook_events`.
   - Actualización de estados reales de entrega: enviado (`sent`), entregado (`delivered`), leído (`read`) y fallido (`failed` con código y descripción de error).
2. **Centro de Atención Comercial (3 Columnas Reactivas y Adaptables):**
   - **Columna 1 (Izquierda):** Lista de chats, buscador por cliente/teléfono/pedido, filtros por pestañas (*Todos*, *No leídos*, *En atención*, *Abiertas*, *Cerradas*), avatares con iniciales, badge de mensajes no leídos y estado de conexión de Meta.
   - **Columna 2 (Centro):** Chat en vivo sin aperturas externas ni redirecciones a WhatsApp Web. Burbujas diferenciadas para cliente, asesor y notas privadas. Envío reactivo con `Enter`, salto de línea con `Shift + Enter`, subida de adjuntos (imágenes/PDFs) con preview pre-envío, botones rápidos (Ficha técnica de laptop, Catálogo web, Medios de pago, Garantía), e indicador de ventana de atención de 24 horas.
   - **Columna 3 (Derecha):** Panel comercial con ficha del cliente, historial de compras acumulado, pedido vinculado con desglose de items/totales, notas internas privadas y selector de estado/asesor.
3. **Modo de Configuración Transparente:**
   - Si no se configuran credenciales en `.env`, el sistema se presenta en "Modo de Configuración", permitiendo redactar y guardar notas privadas e indicando exactamente qué variables faltan sin simular envíos exitosos falsos.
4. **Pruebas Automatizadas:**
   - [x] `tests/Unit/WhatsAppDatabaseSchemaTest.php`: 3 pruebas (15 aserciones).
   - [x] `tests/Unit/WhatsAppCloudApiServiceTest.php`: 7 pruebas (27 aserciones).
   - [x] `tests/Feature/WhatsAppChatIntegrationTest.php`: 9 pruebas (39 aserciones).
   - [x] `tests/Feature/CustomerConversationTest.php`: 6 pruebas (25 aserciones).
   - [x] **Suite Integral del Proyecto:** 88 pruebas y 366 aserciones pasando al 100% (código 0).

