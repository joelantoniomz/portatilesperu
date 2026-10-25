# Prompt principal para Antigravity

Lee los archivos `PLAN_TRABAJO_CATALOGO_VIRTUAL.md` y `PLAN_DESARROLLO_POR_FASES_ANTIGRAVITY.md` completos antes de modificar el proyecto.

La primera imagen de referencia proporcionada por el usuario es la preferida para el estilo visual. Úsala como inspiración principal: dashboard moderno, fondo oscuro o verde profundo, acentos verde esmeralda, tarjetas compactas, gráficos claros y navegación lateral con iconos. Adapta esa estética al negocio de catálogo e inventario. No copies nombres, marcas, cifras, mapas ni contenido ficticio de la imagen. No sacrifiques legibilidad, accesibilidad ni funcionalidad por imitar la referencia.

Desarrolla el proyecto por fases según el plan. No construyas todo de una vez y no avances de fase hasta cumplir sus criterios de aceptación. Inspecciona primero el repositorio; conserva el código funcional y no reescribas tecnologías sin una razón documentada.

**Comienza únicamente con la Fase 0.** Entrega un diagnóstico del repositorio, stack detectado, funcionalidades existentes, riesgos, decisiones técnicas y plan concreto de implementación. Si el repositorio está vacío, propone la estructura inicial y versiones compatibles. No comiences la Fase 1 hasta presentar el diagnóstico.

En cada fase:
- Implementa funciones completas, no solo pantallas estáticas.
- Conecta interfaz, validaciones del servidor y base de datos.
- Ejecuta pruebas y corrige errores.
- Actualiza `README.md` y `docs/PROGRESS.md`.
- Informa archivos modificados, pruebas, resultados y pendientes.
- No inventes precios, stock, especificaciones ni datos comerciales.
- No incluyas secretos en código, logs o documentación.
- Pregunta solo cuando una decisión de negocio sea imprescindible para continuar.

Requisitos críticos:
- El pedido debe guardarse con una referencia antes de abrir WhatsApp.
- El enlace de WhatsApp no debe presentarse como una bandeja sincronizada. La bandeja integrada requiere la API oficial, credenciales autorizadas y webhooks.
- Escanear QR/barcode no descuenta stock automáticamente.
- El inventario debe registrar movimientos y protegerse contra sobreventa y operaciones concurrentes.
- Los precios y totales se calculan/validan en el servidor.
- Las autorizaciones se aplican en servidor por rol y acción.
- El generador PDF debe ofrecer ocho diseños visualmente diferentes.
- El dashboard debe mostrar datos reales, no métricas de demostración.
- Debe existir una estrategia de copias de seguridad y una restauración probada.

Prioriza la Fase 0 y espera la revisión del diagnóstico antes de continuar. Usa los dos documentos como especificación funcional y técnica de referencia.
