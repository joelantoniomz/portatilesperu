# Plan de desarrollo por fases para Antigravity
## Catálogo virtual, panel administrativo, inventario, WhatsApp y PDF

## Instrucción de ejecución
Desarrolla una fase por vez. Inspecciona primero el repositorio y conserva el código funcional existente. No avances a la fase siguiente hasta cumplir los criterios de aceptación. Las pantallas deben estar conectadas a lógica real y persistencia; no entregues solo maquetas visuales.

Al finalizar cada fase:
1. Ejecuta las pruebas relevantes y corrige fallos.
2. Actualiza `README.md` y `docs/PROGRESS.md`.
3. Resume archivos creados/modificados, pruebas ejecutadas, resultados y pendientes.
4. Documenta decisiones técnicas, riesgos y variables necesarias.
5. No uses secretos reales ni datos comerciales inventados.

## Fase 0 — Inspección y planificación técnica
**Tareas**
- Inspeccionar repositorio, stack, dependencias, rutas, base de datos y pruebas.
- Detectar funcionalidades ya existentes y posibles riesgos.
- Si está vacío, proponer una base compatible y mantenible.
- Documentar arquitectura, entidades, fases y decisiones bloqueantes.
- Crear/actualizar `README.md`, `.env.example` y `docs/PROGRESS.md`.

**Aceptación**
- Diagnóstico documentado.
- Instrucciones reproducibles de instalación.
- No se ha eliminado código funcional sin razón.
- No hay secretos en el repositorio.

## Fase 1 — Base técnica, autenticación y permisos
**Tareas**
- Configurar entorno, base de datos y migraciones.
- Implementar autenticación y cierre de sesión.
- Crear roles: superadministrador, administrador, vendedor, almacén, editor de catálogo y auditor.
- Aplicar políticas de autorización en el servidor.
- Crear layout administrativo y navegación adaptable.
- Añadir auditoría mínima para acciones sensibles.

**Aceptación**
- Rutas protegidas rechazan usuarios no autorizados.
- Los permisos se verifican en servidor.
- Existen pruebas de autenticación y autorización.

## Fase 2 — Productos, categorías, marcas y modelos
**Tareas**
- CRUD de productos, categorías, subcategorías, marcas y modelos.
- Validar SKU único, campos obligatorios, precios y estados.
- Galería de imágenes con orden, tipo y tamaño validados.
- Fichas técnicas estructuradas.
- Publicar, despublicar, archivar y buscar productos.

**Aceptación**
- Se puede crear y editar un producto con varias imágenes.
- SKU duplicados se rechazan o gestionan según una regla documentada.
- Productos archivados no aparecen públicamente.
- Validaciones ejecutadas en servidor.

## Fase 3 — Inventario, QR y códigos de barras
**Tareas**
- Registrar entradas, salidas, ajustes, reservas y devoluciones.
- Diferenciar stock físico, reservado y disponible.
- Alertas por stock mínimo e historial de movimientos.
- Generar QR por producto y etiquetas imprimibles.
- Búsqueda por escaneo con cámara en navegadores compatibles y lector USB/Bluetooth.
- Proteger operaciones concurrentes mediante transacciones y controles adecuados.

**Aceptación**
- Cada movimiento tiene usuario, fecha, motivo y referencia.
- Escanear un código no cambia el stock automáticamente.
- No hay sobreventa en operaciones simultáneas dentro de la política definida.
- Cancelar una reserva la libera correctamente sin borrar historial.

## Fase 4 — Catálogo público y diseño
**Tareas**
- Aplicar la referencia visual principal: primera imagen proporcionada, dashboard oscuro/verde esmeralda, tarjetas compactas y gráficos claros.
- Adaptar la identidad al negocio; no copiar marcas o datos ficticios de la imagen.
- Crear inicio, búsqueda, filtros, categorías, listado y detalle.
- Mostrar galería de 6–8 imágenes, precio, disponibilidad y ficha técnica.
- Crear páginas informativas y diseño responsive.
- Cargar datos reales desde la base de datos.

**Aceptación**
- Solo se muestran productos publicados.
- Búsqueda, filtros y paginación funcionan.
- Interfaz usable en móvil y escritorio.
- Contraste, foco y etiquetas de controles adecuados.

## Fase 5 — Dashboard administrativo
**Tareas**
- Crear tarjetas de métricas con datos reales: productos, stock bajo, pedidos pendientes, pedidos en preparación y actividad reciente.
- Añadir gráficos útiles solo cuando existan datos suficientes.
- Accesos directos: agregar producto, entrada de stock, pedidos, conversaciones, importación y generación de PDF.
- Mostrar estados vacíos cuando no existan datos.

**Aceptación**
- Métricas coinciden con consultas verificables.
- Accesos directos llevan a acciones funcionales.
- No hay cifras estáticas de demostración en producción.
- El dashboard respeta permisos por rol.

## Fase 6 — Solicitud de compra, pedidos y WhatsApp por enlace
**Tareas**
- Crear carrito/lista de solicitud con cantidades editables.
- Validar productos, cantidades, precio y disponibilidad en el servidor.
- Guardar pedido con referencia antes de abrir WhatsApp.
- Generar mensaje con referencia y resumen de productos.
- Implementar estados, notas e historial de cambios.
- Definir reserva y salida de stock; gestionar cancelaciones correctamente.

**Aceptación**
- Pedido guardado antes de abrir WhatsApp.
- El mensaje contiene referencia y resumen correcto.
- El servidor no confía en totales enviados por el navegador.
- Cantidades inválidas y stock insuficiente se rechazan.
- Cancelaciones liberan reservas correctamente.

## Fase 7 — Clientes y bandeja integrada de WhatsApp
**Tareas**
- Crear perfiles de clientes e historial de pedidos.
- Mantener disponible el modo de enlace aunque la API no esté configurada.
- Si hay credenciales autorizadas, integrar WhatsApp Business Platform mediante API oficial y webhooks.
- Añadir bandeja, asignación a vendedores, notas internas y vinculación con pedidos.
- Validar webhooks, deduplicar eventos, gestionar reintentos y errores.
- Mantener tokens en configuración segura, nunca en el navegador o logs.

**Aceptación**
- La falta de credenciales no rompe la compra por enlace.
- La integración solo se considera completa tras pruebas reales autorizadas.
- Los eventos duplicados no duplican mensajes/pedidos.
- Se documenta la configuración requerida.

## Fase 8 — Generador de catálogos PDF
**Tareas**
- Generar catálogo general, por categoría, por marca y por selección manual.
- Implementar ocho plantillas diferenciadas: minimalista, corporativo, premium, comercial, técnico, editorial, mayorista y moderno.
- Permitir portada, logo, colores, datos de contacto, índice, numeración, tamaño A4/carta y orientación.
- Elegir inclusión de precios, SKU, stock y especificaciones.
- Añadir vista previa, descarga y manejo de errores.
- Usar cola de trabajo si la generación es pesada.

**Aceptación**
- Cada diseño tiene identidad visual realmente distinta.
- Los filtros seleccionan productos correctos.
- Los datos del PDF coinciden con la base de datos.
- Los PDF se abren correctamente y muestran portada/numeración según configuración.

## Fase 9 — Importación, exportación y reportes
**Tareas**
- Plantillas CSV/XLSX y documentación de columnas.
- Vista previa, validación por fila y reporte de errores.
- Política de actualización por SKU.
- Exportaciones filtradas según permisos.
- Reportes de productos, inventario, movimientos y pedidos.
- Monitoreo de trabajos largos.

**Aceptación**
- Errores de importación se informan por fila.
- No se crean duplicados accidentalmente.
- Exportaciones y reportes respetan permisos.
- Los indicadores coinciden con datos verificables.

## Fase 10 — Configuración, auditoría y seguridad
**Tareas**
- Configurar logo, nombre comercial, colores, banners, moneda y datos de contacto.
- Configurar número de WhatsApp y mensaje inicial.
- Revisar autorización, CSRF, XSS, inyección SQL, sesiones, limitación de intentos y archivos.
- Proteger URLs externas contra SSRF si se importan imágenes por URL.
- Crear logs sin secretos, auditoría y política de retención.
- Implementar copias de seguridad y probar restauración.

**Aceptación**
- Solo roles autorizados cambian configuración.
- No se exponen secretos ni archivos privados.
- Pruebas de seguridad críticas pasan.
- Existe una copia de seguridad restaurable.

## Fase 11 — Pruebas integrales y publicación
**Tareas**
- Probar el recorrido completo desde catálogo hasta pedido y gestión del vendedor.
- Probar inventario con solicitudes simultáneas, cancelaciones y ajustes.
- Revisar rendimiento, accesibilidad y errores.
- Preparar dominio, HTTPS, variables de entorno y despliegue.
- Documentar migraciones, reversión y restauración.
- Capacitar a los usuarios mediante una guía breve.

**Aceptación**
- Pruebas críticas pasan.
- El despliegue se puede repetir con instrucciones documentadas.
- Se ha probado restauración en un entorno de prueba.
- No se publica hasta corregir errores críticos.

## Fase 12 — IA y mejoras posteriores
**Tareas**
- Proponer descripciones y títulos basados en información existente.
- Sugerir categorías y etiquetas.
- Proponer respuestas para vendedores.
- Recomendar productos relacionados.
- Permitir revisión y aprobación humana.

**Aceptación**
- La IA no publica automáticamente datos comerciales sin permiso.
- No inventa especificaciones, precios ni stock.
- Las sugerencias se pueden editar y descartar.

## Modelo de datos a considerar
`users`, `roles`, `permissions`, `products`, `product_images`, `categories`, `brands`, `models`, `product_specifications`, `warehouses`, `inventory_balances`, `stock_movements`, `stock_reservations`, `customers`, `orders`, `order_items`, `order_status_history`, `conversations`, `messages`, `catalog_jobs`, `imports`, `store_settings`, `audit_logs`, `integration_events`.

El modelo final debe documentar relaciones, índices, restricciones y políticas de retención. Los pedidos deben conservar una instantánea de los nombres, precios y cantidades al momento de la solicitud.

## Arquitectura sugerida
Si el repositorio está vacío, evaluar Laravel con PHP soportado y MySQL o PostgreSQL. Confirmar versiones compatibles antes de instalar. Usar servicios de dominio separados para productos, inventario, pedidos, WhatsApp y PDF; colas para tareas largas; pruebas unitarias y de integración. Si el repositorio existente usa un stack funcional, evaluar antes de reescribir.

## Pruebas mínimas
- Permisos por rol.
- SKU duplicado.
- Subida de imágenes inválidas.
- Búsqueda y filtros.
- Pedido guardado antes de abrir WhatsApp.
- Precio calculado en servidor.
- Stock insuficiente y concurrencia.
- Cancelación y liberación de reserva.
- Los ocho PDF y sus filtros.
- Importación válida y con errores.
- Fallos y duplicados de webhook.
- Restauración de copia de seguridad.

## Archivo de progreso obligatorio
Mantener `docs/PROGRESS.md` con:
- Fase actual.
- Completado.
- Pruebas ejecutadas y resultado.
- Pendientes.
- Riesgos/decisiones.
- Próxima fase.

## Regla final
Priorizar datos consistentes, permisos, inventario correcto y pruebas por encima de adornos visuales. El dashboard debe inspirarse en la primera imagen del usuario, pero cada indicador debe representar información real y útil para este negocio.
