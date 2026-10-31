<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogPdfController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PublicCatalogController;
use App\Http\Controllers\QrController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

// ─── Catálogo Público y Vitrina Comercial (Fase 4) ───────────────────────────
Route::get('/', [PublicCatalogController::class, 'home'])->name('home');
Route::get('/catalogo', [PublicCatalogController::class, 'index'])->name('catalogo');
Route::get('/catalogo/{slug}/ficha-tecnica.pdf', [PublicCatalogController::class, 'downloadTechnicalSheet'])->name('catalogo.ficha-tecnica');
Route::get('/catalogo/{slug}', [PublicCatalogController::class, 'show'])->name('catalogo.detalle');
Route::get('/nosotros', [PublicCatalogController::class, 'about'])->name('nosotros');
Route::get('/contacto', [PublicCatalogController::class, 'contact'])->name('contacto');

// ─── Carrito y Solicitud de Compra / WhatsApp (Fase 6) ────────────────────────
Route::get('/carrito', [CartController::class, 'index'])->name('carrito');
Route::post('/carrito/agregar', [CartController::class, 'add'])->name('carrito.agregar');
Route::post('/carrito/actualizar', [CartController::class, 'update'])->name('carrito.actualizar');
Route::post('/carrito/eliminar', [CartController::class, 'remove'])->name('carrito.eliminar');
Route::post('/carrito/vaciar', [CartController::class, 'clear'])->name('carrito.vaciar');
Route::post('/pedido/checkout', [CartController::class, 'checkout'])->name('pedido.checkout');
Route::get('/pedido/confirmacion/{orderNumber}', [CartController::class, 'confirmation'])->name('pedido.confirmacion');

// ─── Webhooks WhatsApp (Meta Cloud API & OpenWA REST) ──────────────────────
Route::get('/webhook/whatsapp', [WhatsAppWebhookController::class, 'verifyWebhook'])->name('webhook.whatsapp.verify');
Route::post('/webhook/whatsapp', [WhatsAppWebhookController::class, 'handleIncoming'])->name('webhook.whatsapp.handle');

Route::post('/webhook/openwa', [\App\Http\Controllers\OpenWaWebhookController::class, 'handleIncoming'])->name('webhook.openwa.handle');
Route::post('/api/webhook/openwa', [\App\Http\Controllers\OpenWaWebhookController::class, 'handleIncoming'])->name('api.webhook.openwa.handle');

// ─── Autenticación Administrativa ──────────────────────────────────────────
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─── Panel Administrativo (requiere autenticación) ────────────────────────────
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard y perfil
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/perfil', [AdminController::class, 'profile'])->name('profile');
    Route::put('/perfil', [AdminController::class, 'updateProfile'])->name('profile.update');

    // Gestión de usuarios (solo superadmin y admin)
    Route::middleware(['role:superadmin,admin'])->group(function () {
        Route::get('/usuarios', [AdminController::class, 'users'])->name('users');
        Route::post('/usuarios', [AdminController::class, 'storeUser'])->name('users.store');
        Route::put('/usuarios/{user}/contrasena', [AdminController::class, 'resetUserPassword'])->name('users.password');
        Route::put('/usuarios/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/usuarios/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        Route::get('/configuracion', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/configuracion', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('/configuracion/restablecer-fabrica', [SettingsController::class, 'factoryReset'])->name('settings.factory-reset');
        Route::get('/configuracion/backup/exportar', [SettingsController::class, 'exportBackup'])->name('settings.backup.export');
        Route::post('/configuracion/backup/importar', [SettingsController::class, 'importBackup'])->name('settings.backup.import');
    });

    // Auditoría
    Route::middleware(['permission:audit.view'])->group(function () {
        Route::get('/auditoria', [AdminController::class, 'auditLogs'])->name('audit');
    });

    // ──────────────────────────────────────────────────────────────────────────
    // FASE 2: CATÁLOGO, PRODUCTOS, CATEGORÍAS Y MARCAS
    // ──────────────────────────────────────────────────────────────────────────

    Route::middleware(['permission:catalog.view'])->group(function () {
        Route::get('/productos', [ProductController::class, 'index'])->name('products.index');
        Route::get('/productos/{product}', [ProductController::class, 'show'])->name('products.show')->whereNumber('product');
        Route::get('/categorias', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/marcas', [BrandController::class, 'index'])->name('brands.index');
    });

    Route::middleware(['permission:catalog.manage'])->group(function () {
        Route::get('/productos/plantilla', [ProductController::class, 'templateGuide'])->name('products.template-guide');
        Route::get('/productos/importar/plantilla', [ProductController::class, 'downloadImportTemplate'])->name('products.import-template');
        Route::post('/productos/gemini-autofill', [ProductController::class, 'geminiAutofill'])->name('products.gemini-autofill');
        Route::post('/productos/guardar-gemini-key', [ProductController::class, 'saveGeminiKey'])->name('products.save-gemini-key');
        Route::post('/productos/acciones-masivas', [ProductController::class, 'bulkAction'])->name('products.bulk-action');
        Route::post('/productos/importar', [ProductController::class, 'import'])->name('products.import');
        Route::get('/productos/crear', [ProductController::class, 'create'])->name('products.create');
        Route::post('/productos', [ProductController::class, 'store'])->name('products.store');
        Route::get('/productos/{product}/editar', [ProductController::class, 'edit'])->name('products.edit')->whereNumber('product');
        Route::put('/productos/{product}', [ProductController::class, 'update'])->name('products.update')->whereNumber('product');
        Route::delete('/productos/{product}', [ProductController::class, 'destroy'])->name('products.destroy')->whereNumber('product');
        Route::patch('/productos/{product}/estado', [ProductController::class, 'toggleStatus'])->name('products.toggleStatus')->whereNumber('product');
        Route::delete('/productos/{product}/imagenes/{image}', [ProductController::class, 'deleteImage'])->name('products.deleteImage')->whereNumber('product');

        Route::post('/categorias', [CategoryController::class, 'store'])->name('categories.store');
        Route::post('/categorias/{category}/subcategorias', [CategoryController::class, 'storeSubcategory'])->name('categories.storeSubcategory');
        Route::delete('/categorias/{category}/subcategorias/{subcategory}', [CategoryController::class, 'destroySubcategory'])->name('categories.subcategories.destroy');
        Route::put('/categorias/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categorias/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::post('/marcas', [BrandController::class, 'store'])->name('brands.store');
        Route::post('/marcas/{brand}/modelos', [BrandController::class, 'storeModel'])->name('brands.storeModel');
        Route::delete('/marcas/{brand}/modelos/{model}', [BrandController::class, 'destroyModel'])->name('brands.models.destroy');
        Route::delete('/marcas/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');
    });

    // ──────────────────────────────────────────────────────────────────────────
    // FASE 3: INVENTARIO, QR Y CÓDIGOS DE BARRAS
    // ──────────────────────────────────────────────────────────────────────────

    // Consulta de inventario y almacenes (permission: inventory.view)
    Route::middleware(['permission:inventory.view'])->group(function () {
        Route::get('/inventario', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventario/movimientos', [InventoryController::class, 'movements'])->name('inventory.movements');
        Route::get('/inventario/reservas', [InventoryController::class, 'reservations'])->name('inventory.reservations');
        Route::get('/inventario/producto/{product}/stock', [InventoryController::class, 'productStock'])->name('inventory.product')->whereNumber('product');
        Route::get('/inventario/producto/{product}/stock-legacy', [InventoryController::class, 'productStock'])->name('inventory.productStock')->whereNumber('product');
        Route::get('/inventario/escaner', [InventoryController::class, 'scannerView'])->name('inventory.scanner');
        // AJAX lookup — no modifica datos; escanear no reserva ni mueve stock
        Route::get('/inventario/buscar-codigo', [InventoryController::class, 'scanLookup'])->name('inventory.scan-lookup');
        Route::get('/inventario/buscar-codigo-legacy', [InventoryController::class, 'scanLookup'])->name('inventory.scanLookup');

        Route::get('/almacenes', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::get('/almacenes/{warehouse}', [WarehouseController::class, 'show'])->name('warehouses.show')->whereNumber('warehouse');
    });

    // Movimientos de stock — requiere permiso inventory.manage
    Route::middleware(['permission:inventory.manage'])->group(function () {
        Route::get('/inventario/entrada', [InventoryController::class, 'entryForm'])->name('inventory.entry');
        Route::post('/inventario/entrada', [InventoryController::class, 'storeEntry'])->name('inventory.entry.store');
        Route::post('/inventario/entrada-legacy', [InventoryController::class, 'storeEntry'])->name('inventory.storeEntry');

        Route::get('/inventario/salida', [InventoryController::class, 'exitForm'])->name('inventory.exit');
        Route::post('/inventario/salida', [InventoryController::class, 'storeExit'])->name('inventory.exit.store');
        Route::post('/inventario/salida-legacy', [InventoryController::class, 'storeExit'])->name('inventory.storeExit');

        Route::get('/inventario/ajuste', [InventoryController::class, 'adjustmentForm'])->name('inventory.adjustment');
        Route::post('/inventario/ajuste', [InventoryController::class, 'storeAdjustment'])->name('inventory.adjustment.store');
        Route::post('/inventario/ajuste-legacy', [InventoryController::class, 'storeAdjustment'])->name('inventory.storeAdjustment');

        Route::post('/inventario/reservas/{reservation}/cancelar', [InventoryController::class, 'cancelReservation'])
            ->name('inventory.reservations.cancel')->whereNumber('reservation');
        Route::patch('/inventario/reservas/{reservation}/cancelar', [InventoryController::class, 'cancelReservation'])
            ->name('inventory.cancelReservation')->whereNumber('reservation');

        // CRUD completo de almacenes
        Route::get('/almacenes/crear', [WarehouseController::class, 'create'])->name('warehouses.create');
        Route::post('/almacenes', [WarehouseController::class, 'store'])->name('warehouses.store');
        Route::get('/almacenes/{warehouse}/editar', [WarehouseController::class, 'edit'])->name('warehouses.edit')->whereNumber('warehouse');
        Route::put('/almacenes/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update')->whereNumber('warehouse');
        Route::delete('/almacenes/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy')->whereNumber('warehouse');
    });

    // Etiquetas QR (catalog.view para ver; catalog.manage para lote)
    Route::middleware(['permission:catalog.view'])->group(function () {
        Route::get('/qr/{product}/etiqueta', [QrController::class, 'label'])->name('qr.label')->whereNumber('product');
        Route::get('/qr/{product}/svg', [QrController::class, 'svg'])->name('qr.svg')->whereNumber('product');
        Route::match(['get', 'post'], '/qr/lote', [QrController::class, 'batchLabels'])->name('qr.batch');
    });

    // ──────────────────────────────────────────────────────────────────────────
    // FASE 6: PEDIDOS, SOLICITUDES Y WHATSAPP
    // ──────────────────────────────────────────────────────────────────────────
    Route::middleware(['permission:orders.view'])->group(function () {
        Route::get('/pedidos', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/pedidos/{order}', [OrderController::class, 'show'])->name('orders.show')->whereNumber('order');
    });

    Route::middleware(['permission:orders.manage'])->group(function () {
        Route::patch('/pedidos/{order}/estado', [OrderController::class, 'updateStatus'])->name('orders.updateStatus')->whereNumber('order');
        Route::post('/pedidos/{order}/confirmar-pago', [OrderController::class, 'confirmPayment'])->name('orders.confirmPayment')->whereNumber('order');
    });

    Route::middleware(['permission:catalog.pdf'])->group(function () {
        Route::get('/catalogos-pdf', [CatalogPdfController::class, 'index'])->name('catalog-pdf.index');
        Route::match(['get', 'post'], '/catalogos-pdf/previsualizar', [CatalogPdfController::class, 'previewPage'])->name('catalog-pdf.preview');
        Route::post('/catalogos-pdf', [CatalogPdfController::class, 'generate'])->name('catalog-pdf.generate');
    });

    // FASE 7: CLIENTES Y BANDEJA WHATSAPP (modo enlace siempre disponible)
    Route::middleware(['permission:customers.view'])->group(function () {
        Route::get('/clientes', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/clientes/{customer}', [CustomerController::class, 'show'])->name('customers.show')->whereNumber('customer');
    });
    Route::middleware(['permission:customers.manage'])->group(function () {
        Route::post('/clientes', [CustomerController::class, 'store'])->name('customers.store');
        Route::post('/clientes/{customer}/notas', [CustomerController::class, 'addNote'])->name('customers.notes.store')->whereNumber('customer');
        Route::patch('/clientes/{customer}', [CustomerController::class, 'update'])->name('customers.update')->whereNumber('customer');
        Route::post('/clientes/{customer}/recalcular', [CustomerController::class, 'recalculate'])->name('customers.recalculate')->whereNumber('customer');
    });
    Route::middleware(['permission:whatsapp.view'])->group(function () {
        Route::get('/conversaciones', [ConversationController::class, 'index'])->name('conversations.index');
        Route::get('/conversaciones/{conversation}', [ConversationController::class, 'show'])->name('conversations.show')->whereNumber('conversation');
        Route::get('/conversaciones/{conversation}/data', [ConversationController::class, 'data'])->name('conversations.data')->whereNumber('conversation');
        Route::get('/conversaciones/{conversation}/sync', [ConversationController::class, 'sync'])->name('conversations.sync')->whereNumber('conversation');
    });
    Route::middleware(['permission:whatsapp.manage'])->group(function () {
        Route::post('/conversaciones', [ConversationController::class, 'store'])->name('conversations.store');
        Route::post('/conversaciones/{conversation}/mensajes', [ConversationController::class, 'addMessage'])->name('conversations.messages.store')->whereNumber('conversation');
        Route::post('/conversaciones/{conversation}/chat/send', [ConversationController::class, 'sendChatMessage'])->name('conversations.chat.send')->whereNumber('conversation');
        Route::post('/conversaciones/{conversation}/chat/read', [ConversationController::class, 'markRead'])->name('conversations.chat.read')->whereNumber('conversation');
        Route::post('/conversaciones/{conversation}/chat/attachment', [ConversationController::class, 'attachMedia'])->name('conversations.chat.attachment')->whereNumber('conversation');
        Route::patch('/conversaciones/{conversation}', [ConversationController::class, 'updateStatus'])->name('conversations.update')->whereNumber('conversation');
    });

    // ─── Rutas de prueba de roles (usadas por PHPUnit) ────────────────────────
    Route::get('/almacen-test', fn () => response()->json(['message' => 'Acceso autorizado al módulo de almacén.']))
        ->middleware(['role:almacen,superadmin'])
        ->name('test.almacen');

    Route::get('/vendedor-test', fn () => response()->json(['message' => 'Acceso autorizado al módulo de ventas.']))
        ->middleware(['role:vendedor,superadmin'])
        ->name('test.vendedor');
});
