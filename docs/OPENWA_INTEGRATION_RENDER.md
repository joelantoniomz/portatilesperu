# GUÍA DE INTEGRACIÓN Y VARIABLES DE ENTORNO: OPENWA EN RENDER

Este documento detalla la configuración y despliegue del módulo integrado de **WhatsApp (OpenWA)** para **Portátiles Perú** en **Render**.

---

## 1. Arquitectura de Conexión: Render y OpenWA

### ⚠️ Consideración Crítica sobre OpenWA Local vs. Producción
- **En entorno local (PC de desarrollo):** OpenWA puede ejecutarse temporalmente en `http://localhost:2785`. Sin embargo, los servidores de Render en la nube **no tienen acceso a tu localhost ni a tu red privada**.
- **Para producción:**
  1. OpenWA debe estar alojado en un servidor accesible las 24 horas (VPS en Hetzner, DigitalOcean, AWS EC2 o un servicio Docker persistente como Railway).
  2. Debe contar con almacenamiento persistente para la carpeta de sesión de Chrome/Puppeteer (evita que se cierre la sesión de WhatsApp al reiniciar el contenedor).
  3. Debe contar con un dominio con HTTPS (ej. `https://whatsapp.tuempresa.pe` mediante proxy inverso con Nginx o Caddy).

---

## 2. Variables de Entorno para Render (Environment Settings)

En el panel de **Render** (`Dashboard > Tu Servicio Web > Environment`), agrega las siguientes variables de entorno:

| Variable | Descripción | Ejemplo de Valor |
|---|---|---|
| `OPENWA_BASE_URL` | URL pública con HTTPS de tu servidor OpenWA | `https://whatsapp.tu-servidor.com` |
| `OPENWA_API_KEY` | Clave secreta configurada en tu servidor OpenWA para autorizar las solicitudes HTTP | `clave_secreta_alfanumerica_32_bytes` |
| `OPENWA_SESSION_ID` | Identificador de la sesión en OpenWA | `default` |
| `OPENWA_WEBHOOK_SECRET` | Secreto compartido para validar la firma HMAC-SHA256 (`X-OpenWA-Signature`) | `secreto_hmac_sha256_super_seguro` |
| `OPENWA_TIMEOUT` | Tiempo máximo de espera en segundos para solicitudes a OpenWA | `15` |

*(Nota: En el repositorio nunca se incluyen estas claves en texto plano).*

---

## 3. Configuración del Webhook en OpenWA

Configura tu instancia de OpenWA para enviar los eventos a la ruta pública de tu aplicación en Render:

- **Webhook URL:** `https://portatilesperu.onrender.com/webhook/openwa`
  *(o `https://tu-dominio.com/webhook/openwa`)*
- **Encabezados esperados por el Webhook de Laravel:**
  - `X-OpenWA-Signature`: Firma HMAC-SHA256 del cuerpo original calculada con `OPENWA_WEBHOOK_SECRET`.
  - `X-OpenWA-Idempotency-Key`: Clave única para evitar duplicados ante reintentos.
- **Eventos a suscribir en OpenWA:**
  - `message.received` (mensajes de clientes)
  - `message.sent` (confirmación de envíos)
  - `message.ack` (estados de entrega y lectura)
  - `session.status` (estado de conexión de la sesión y batería)

---

## 4. Cómo Conectar o Cambiar la Sesión de WhatsApp

1. Ingresa al panel de administración de Portátiles Perú: `/admin/conversaciones`.
2. En la barra superior observarás el indicador de estado de **WhatsApp / OpenWA**.
3. Si la sesión no está conectada o requiere vincular un nuevo número, el panel mostrará el estado de la sesión (`SCAN_QR_CODE` o `STOPPED`).
4. Al escanear el código QR con el WhatsApp de la empresa desde tu smartphone, el evento `session.status` actualizará automáticamente la base de datos a `WORKING` / `CONNECTED`.
5. El chat interactivo quedará habilitado para enviar y recibir mensajes directamente desde el navegador sin abrir pestañas externas ni WhatsApp Web.

---

## 5. Resumen de Seguridad Implementada

1. **Protección de API Key:** La clave `OPENWA_API_KEY` se consume única y exclusivamente desde el servidor backend de Laravel (`App\Services\OpenWaService`). El cliente o frontend jamás tiene acceso a ella.
2. **Validación Criptográfica HMAC:** El webhook valida que el cuerpo del mensaje no haya sido alterado usando `hash_hmac` y `hash_equals`.
3. **Idempotencia Estricta:** Se registran las claves en `whatsapp_webhook_events` para descartar duplicados y reintentos.
4. **Control de Acceso:** El módulo requiere autenticación y permisos específicos (`whatsapp.view`, `whatsapp.manage`).

