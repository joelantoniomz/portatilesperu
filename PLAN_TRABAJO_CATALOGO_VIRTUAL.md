# Plan de trabajo del proyecto
## Sistema de catálogo virtual, inventario y ventas por WhatsApp

**Documento para:** Antigravity  
**Versión:** 1.0  
**Idioma de la aplicación:** español

## 1. Objetivo

Construir una aplicación web comercial con un catálogo público y un panel de administración que permita gestionar productos, categorías, marcas, inventario, pedidos, clientes y conversaciones de WhatsApp, además de generar catálogos PDF configurables.

## 2. Dirección visual elegida

Tomar como referencia principal la **primera imagen proporcionada por el usuario**: dashboard oscuro, moderno, con verde esmeralda como color de acento, tarjetas compactas, gráficos visibles y navegación lateral con iconos. Es la referencia preferida por el usuario.

No copiar literalmente marcas, nombres, cifras, mapas, textos ni datos ficticios que aparecen en las referencias. Usar únicamente la composición visual como inspiración y adaptarla a un sistema de catálogo e inventario.

### Criterios de diseño
- Dashboard oscuro o verde muy oscuro, con acentos esmeralda y superficies de alto contraste.
- Tarjetas de indicadores compactas, bordes redondeados y sombras discretas.
- Gráficos legibles, tablas ordenadas y jerarquía visual clara.
- Menú lateral con iconos y etiquetas en español.
- Botones de acción rápida para agregar producto, registrar entrada, consultar pedidos y generar PDF.
- Diseño responsive: en móvil, el menú lateral debe convertirse en navegación compacta.
- Accesibilidad: contraste adecuado, foco visible, etiquetas claras y estados no comunicados solo por color.
- La referencia visual es una guía, no una obligación de usar gráficos irrelevantes. Cada widget debe corresponder a datos reales del sistema.

## 3. Usuarios y roles

- **Superadministrador:** acceso completo, configuración e integraciones.
- **Administrador:** catálogo, inventario, pedidos, clientes y reportes según permisos.
- **Vendedor:** pedidos, clientes y conversaciones asignadas.
- **Almacén:** entradas, salidas, conteos y movimientos de inventario.
- **Editor de catálogo:** productos, imágenes, categorías y generación de PDF.
- **Auditor/solo lectura:** consulta de información autorizada y registros.

Las autorizaciones deben validarse en el servidor, no solo ocultando elementos de la interfaz.

## 4. Catálogo público

- Página de inicio con logo, búsqueda, categorías, destacados y novedades.
- Listado con paginación, búsqueda y filtros.
- Detalle del producto con galería de 6–8 imágenes, descripción, ficha técnica, SKU, marca/modelo, precio y disponibilidad.
- Carrito o lista de solicitud de compra.
- Botón para solicitar por WhatsApp.
- Páginas de contacto, preguntas frecuentes, privacidad y términos.
- Diseño adaptable a celulares, tabletas y escritorio.

Solo se deben mostrar productos publicados y datos aprobados para exposición pública.

## 5. Panel de administración

Menú lateral recomendado:

1. Dashboard.
2. Productos.
3. Categorías y subcategorías.
4. Marcas y modelos.
5. Inventario y almacenes.
6. QR y códigos de barras.
7. Pedidos y ventas.
8. Conversaciones de WhatsApp.
9. Clientes.
10. Generador de catálogos PDF.
11. Importación y exportación.
12. Reportes.
13. Usuarios, roles y permisos.
14. Apariencia del catálogo.
15. Integraciones.
16. Configuración general.
17. Auditoría y seguridad.
18. Copias de seguridad y mantenimiento.

### Dashboard y accesos directos

Los indicadores deben calcularse desde la base de datos:
- Productos activos y publicados.
- Productos con stock bajo.
- Pedidos pendientes de confirmación.
- Pedidos en preparación.
- Solicitudes recientes.
- Conversaciones abiertas, si la API está integrada.
- Resumen de movimientos de inventario.
- Actividad reciente de los usuarios.

Accesos directos:
- Agregar producto.
- Registrar entrada de stock.
- Ajustar inventario.
- Ver pedidos pendientes.
- Abrir conversaciones.
- Importar productos.
- Generar catálogo PDF.

No utilizar cifras estáticas de ejemplo en producción. Mostrar estados vacíos claros cuando aún no haya datos.

## 6. Productos e inventario

Los productos deben incluir, según corresponda:
- SKU único, nombre, slug, descripción y estado.
- Categoría, subcategoría, marca y modelo.
- Precio y moneda.
- Código de barras y QR.
- Galería de 6–8 imágenes.
- Especificaciones técnicas estructuradas.
- Peso/dimensiones opcionales.
- Fechas de creación y actualización.

El inventario debe registrar entradas, salidas, ajustes, reservas, devoluciones, usuario responsable, fecha, motivo y referencia. Debe haber alertas de stock mínimo e historial auditable.

**Reglas de stock:** escanear QR no descuenta existencias. Una solicitud no debe descontar stock por defecto. Definir cuándo se reserva y cuándo se registra la salida definitiva. Proteger las operaciones concurrentes y evitar sobreventa.

## 7. Flujo de compra por WhatsApp

1. El cliente selecciona productos y cantidades.
2. El servidor valida los productos, precios y disponibilidad.
3. Se guarda una solicitud/pedido con un código de referencia.
4. Se prepara el mensaje con la referencia y resumen.
5. Se abre WhatsApp.
6. El vendedor confirma disponibilidad, precio final, entrega y condiciones.
7. El pedido avanza por estados controlados.
8. Las reservas se crean o liberan según la política de inventario.

Estados sugeridos: pendiente de confirmación, confirmado, en preparación, listo para entrega/envío, completado y cancelado.

Un enlace de WhatsApp solo abre un mensaje preparado; no sincroniza la conversación con el panel. Para bandeja integrada se requiere WhatsApp Business Platform, webhooks, credenciales autorizadas y manejo de errores. La aplicación debe funcionar en modo enlace aunque la API no esté configurada.

## 8. Generador de catálogos PDF

Permitir generar catálogos:
- General.
- Por categoría/subcategoría.
- Por marca/modelo.
- Por selección manual.
- De novedades o destacados, si existen esos datos.
- Técnico con especificaciones ampliadas.

Opciones:
- Elegir si se muestran precios, SKU, códigos, stock y especificaciones.
- Elegir tamaño A4 o carta, orientación y márgenes.
- Configurar portada, logo, colores, contacto, índice y numeración.
- Previsualizar y descargar.
- Usar solo productos permitidos según filtros y permisos.

### Ocho diseños
1. **Minimalista:** limpio y con espacio en blanco.
2. **Corporativo:** formal y alineado con la marca.
3. **Premium:** imágenes grandes y composición elegante.
4. **Comercial:** precios y llamadas a la acción destacados.
5. **Técnico:** fichas y tablas de especificaciones.
6. **Editorial:** portada, índice y jerarquía tipográfica.
7. **Mayorista:** formato compacto para códigos y precios por volumen cuando existan.
8. **Moderno:** tarjetas visuales y composición contemporánea.

Los ocho diseños deben ser plantillas distintas que utilicen el mismo origen de datos; no ocho nombres para una misma plantilla.

## 9. Importación, reportes y configuración

- Plantillas CSV/XLSX documentadas, vista previa y validación por fila.
- Actualización por SKU con política explícita para duplicados.
- Informe de errores de importación y exportaciones según permisos.
- Reportes de stock, pedidos, productos y actividad.
- Configuración del nombre comercial, logo, colores, banners, moneda, número de WhatsApp y mensaje inicial.
- Historial de cambios críticos y copias de seguridad restaurables.

## 10. Seguridad y calidad

- Validar permisos en servidor.
- Usar ORM o consultas parametrizadas.
- Proteger sesiones, formularios y autenticación.
- Validar tipo y tamaño de archivos subidos.
- Proteger contra XSS, CSRF, inyección SQL y acceso no autorizado.
- No exponer tokens ni datos sensibles en logs o repositorio.
- Validar las URLs externas de imágenes y prevenir SSRF si se permite importarlas.
- Probar copias de seguridad y restauración.
- Mantener dependencias actualizadas.
- No afirmar seguridad absoluta; documentar riesgos y mitigaciones.

## 11. Entregables de trabajo

- Repositorio organizado y código funcional.
- Documentación de instalación y configuración.
- Migraciones y modelo de datos documentados.
- Pruebas automatizadas y manuales.
- `.env.example` sin secretos.
- `docs/PROGRESS.md` con avances por fase.
- Lista de decisiones pendientes y riesgos.
- Guía de despliegue, respaldo y restauración.

## 12. Definición de éxito

El sistema estará listo para una prueba piloto cuando un administrador pueda gestionar productos e inventario, el público pueda consultar productos, una solicitud de compra genere una referencia antes de abrir WhatsApp, los vendedores puedan gestionar estados, los permisos se respeten, el PDF se genere correctamente y las pruebas críticas pasen.
