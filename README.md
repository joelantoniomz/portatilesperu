# Portátiles Perú — Catálogo Virtual, Inventario y Ventas por WhatsApp

Sistema web integral de comercio y gestión para venta de equipos portátiles y cómputo, compuesto por un catálogo público moderno, panel de administración con estética verde esmeralda inspirada en el diseño de referencia (`diseño.png`), control de inventario con trazabilidad y protección de sobreventa, pedidos con referencia previa a WhatsApp y generador de catálogos PDF en ocho diseños visuales diferenciados.

---

## 1. Diagnóstico del Repositorio (Fase 0)

| Parámetro | Estado / Valor Detectado |
|---|---|
| **Ruta de Trabajo** | `C:\laragon\www\portatiles-peru` |
| **PHP Runtime** | PHP 8.3.33 (CLI x64) con extensiones: `pdo_mysql`, `mbstring`, `gd`, `intl`, `curl`, `xml`, `bcmath`, `fileinfo` activas. |
| **Gestor de Paquetes PHP** | Composer 2.10.3 |
| **Node.js / npm** | Node.js v24.21.0 / npm 11.19.0 |
| **Motor de Base de Datos** | MySQL 8.4.3 en ejecución local en puerto `3306` (entorno Laragon). |
| **Control de Versiones** | Git 2.56.0 inicializado. |
| **Estado Inicial de Código** | Repositorio limpio; contiene únicamente documentos de especificación (`PROMPT_ANTIGRAVITY.md`, `PLAN_TRABAJO_CATALOGO_VIRTUAL.md`, `PLAN_DESARROLLO_POR_FASES_ANTIGRAVITY.md`) y la imagen de referencia visual (`diseño.png`). No se ha eliminado código funcional. |
| **Seguridad de Secretos** | Sin secretos expuestos en repositorio. |

---

## 2. Stack Tecnológico Seleccionado

- **Backend:** [Laravel 11.x](https://laravel.com/) (PHP 8.3). Robusto ORM Eloquent, soporte nativo de transacciones ACID para evitar sobreventa, gestión de roles y políticas en servidor, colas de trabajo en segundo plano para exportaciones y generación de PDFs.
- **Base de Datos:** MySQL 8.4 (motor InnoDB con claves foráneas e integridad referencial).
- **Frontend & UI:** Blade, Vanilla CSS / Tailwind CSS con sistema de diseño a medida basado en `diseño.png` (fondo oscuro/verde profundo `#062316`, acentos esmeralda `#10B981`, dock lateral flotante, tarjetas compactas de alto contraste y gráficos limpios).
- **Componentes Reactivos:** Alpine.js / Vanilla JS para galería de imágenes, lectura de QR mediante cámara web/móvil, calculadora de pedidos y modales.
- **Generación de PDFs:** Motor DomPDF con 8 plantillas Blade completamente diferenciadas en diseño y diagramación.
- **Integración WhatsApp:**
  - *Modo Enlace (Siempre disponible):* Generación obligatoria de pedido en base de datos con referencia única (`PED-YYYY-XXXX`) y validación de precios antes de abrir WhatsApp.
  - *Modo Bandeja Integrada (Opcional):* WhatsApp Cloud API (Meta) con verificación de webhooks por firma criptográfica HMAC SHA-256 y deduplicación de eventos.

---

## 3. Modelo de Datos Principal

```text
users ───────────────┬─ roles & permissions
                     ├─ audit_logs
                     └─ stock_movements

products ────────────┬─ product_images (6-8 imágenes)
                     ├─ product_specifications (ficha técnica JSON)
                     ├─ categories & subcategories
                     ├─ brands & product_models
                     ├─ inventory_balances (por almacén)
                     └─ stock_reservations

orders ──────────────┬─ customers
                     ├─ order_items (snapshot de SKU, nombre y precio unitario)
                     ├─ order_status_history (trazabilidad de estados)
                     └─ stock_reservations
```

---

## 4. Requisitos del Sistema e Instalación

### Requisitos:
- PHP >= 8.2 (detectado: 8.3.33)
- Composer >= 2.0 (detectado: 2.10.3)
- MySQL >= 8.0 (detectado: 8.4.3)
- Node.js >= 18.0 (detectado: 24.21.0) y npm

### Pasos de Instalación (a ejecutar en Fase 1):
1. **Configurar el entorno:**
   ```bash
   cp .env.example .env
   ```
2. **Crear la base de datos MySQL en Laragon:**
   ```sql
   CREATE DATABASE IF NOT EXISTS portatiles_peru CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. **Instalar dependencias de backend y frontend:**
   ```bash
   composer install
   npm install
   ```
4. **Generar la clave de aplicación y ejecutar migraciones:**
   ```bash
   php artisan key:generate
   php artisan migrate --seed
   ```
5. **Compilar recursos de diseño y arrancar servidor:**
   ```bash
   npm run build
   php artisan serve
   ```

---

## 5. Reglas Críticas de Negocio

1. **Pedido con Referencia Obligatoria:** Ningún enlace de WhatsApp se construye sin antes haber validado stock y precio en el servidor y haber persistido el pedido en base de datos con su código único de seguimiento.
2. **Protección Contra Sobreventa:** Las operaciones de inventario emplean transacciones con bloqueo pesimista en base de datos (`lockForUpdate`).
3. **Escaneo de Códigos:** Escanear códigos de barras o QR nunca altera automáticamente las existencias; actúa únicamente como selector o buscador de producto.
4. **Validación en Servidor:** Todos los totales, impuestos y cálculos monetarios se procesan en el backend; el cliente no determina precios.
5. **Generador PDF Multidiseño:** Dispone de 8 maquetaciones visualmente independientes (Minimalista, Corporativo, Premium, Comercial, Técnico, Editorial, Mayorista y Moderno) con el mismo origen de datos.
6. **Métricas Reales:** El dashboard no expone datos estáticos o ficticios en producción; calcula estadísticas vivas desde las entidades del sistema y muestra estados vacíos limpios cuando no hay registros.

---

## 6. Documentación Adicional

- [Plan de Trabajo General](file:///c:/laragon/www/portatiles-peru/PLAN_TRABAJO_CATALOGO_VIRTUAL.md)
- [Plan de Desarrollo por Fases](file:///c:/laragon/www/portatiles-peru/PLAN_DESARROLLO_POR_FASES_ANTIGRAVITY.md)
- [Registro de Progreso y Estado de Fases](file:///c:/laragon/www/portatiles-peru/docs/PROGRESS.md)
