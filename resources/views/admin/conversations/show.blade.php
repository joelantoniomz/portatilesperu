@extends('layouts.admin')

@section('title', 'Chat WhatsApp Comercial - Portátiles Perú')

@section('content')
@php
    $activeId = $activeConversation?->id ?? $conversation?->id ?? null;
    $catalogUrl = route('catalogo');
    $csrfToken = csrf_token();
@endphp

<div class="space-y-4" id="whatsappChatApp">

    <!-- Encabezado Superior y Monitor de Conexión -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-1">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                    Centro de Atención WhatsApp
                </span>
                @if(isset($openwaStatus) && $openwaStatus['connected'])
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        OpenWA Sesión Activa ({{ $openwaStatus['phone'] ?? 'Conectado' }})
                    </span>
                @elseif(isset($whatsappStatus) && $whatsappStatus['configured'])
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-300 border border-emerald-500/30">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Meta Cloud API Conectado
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-400/30" title="Configura OPENWA_BASE_URL y OPENWA_API_KEY">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        OpenWA / WhatsApp (Modo Configuración)
                    </span>
                @endif
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight mt-0.5">
                Chat Comercial Integrado
            </h1>
        </div>

        <!-- Acciones Globales -->
        <div class="flex items-center gap-2">
            <button
                type="button"
                onclick="document.getElementById('modalNewConversation').classList.remove('hidden')"
                class="px-4 py-2.5 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-md transition-all flex items-center gap-2 hover:scale-[1.02]"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nueva Conversación</span>
            </button>
            <button
                type="button"
                onclick="document.getElementById('modalConfigInfo').classList.remove('hidden')"
                class="px-3.5 py-2.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition-colors flex items-center gap-1.5"
                title="Estado de conexión WhatsApp API"
            >
                <svg class="w-4 h-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Meta API</span>
            </button>
        </div>
    </div>

    <!-- Banner Informativo de Modo Configuración (Si faltan credenciales) -->
    @if(! $whatsappStatus['configured'])
    <div class="card-surface rounded-2xl border border-amber-300/40 bg-gradient-to-r from-amber-50 to-orange-50 p-4 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-amber-900">
        <div class="flex items-start gap-3">
            <div class="p-2 bg-amber-500 text-white rounded-xl flex-shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="font-black text-sm text-amber-950">Chat en Modo de Configuración</p>
                <p class="text-amber-800 text-[11px] mt-0.5 leading-relaxed">
                    Las credenciales oficiales de <strong>Meta WhatsApp Business Cloud API</strong> aún no están definidas en tu archivo <code>.env</code>.
                    Actualmente puedes <strong>gestionar clientes, redactar y guardar notas internas privadas</strong>.
                    Para activar el envío y recepción en vivo hacia teléfonos de clientes, completa las credenciales indicadas.
                </p>
            </div>
        </div>
        <button
            type="button"
            onclick="document.getElementById('modalConfigInfo').classList.remove('hidden')"
            class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs whitespace-nowrap shadow-xs transition-colors self-start sm:self-auto"
        >
            Ver variables pendientes
        </button>
    </div>
    @endif

    <!-- Selector de Vistas Móviles (Visible solo en pantallas pequeñas) -->
    <div class="flex lg:hidden bg-white/10 p-1 rounded-2xl gap-1 text-xs font-bold text-white">
        <button type="button" onclick="switchMobileTab('chats')" id="tabBtnChats" class="flex-1 py-2 rounded-xl bg-emerald-500 text-slate-950 text-center shadow-xs">
            💬 Chats ({{ $conversations->total() }})
        </button>
        <button type="button" onclick="switchMobileTab('thread')" id="tabBtnThread" class="flex-1 py-2 rounded-xl text-emerald-100 hover:bg-white/10 text-center">
            ✉️ Conversación
        </button>
        <button type="button" onclick="switchMobileTab('info')" id="tabBtnInfo" class="flex-1 py-2 rounded-xl text-emerald-100 hover:bg-white/10 text-center">
            📋 Ficha Cliente
        </button>
    </div>

    <!-- CONTENEDOR PRINCIPAL: 3 COLUMNAS -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start min-h-[720px]">

        <!-- ====================================================================== -->
        <!-- COLUMNA 1: BANDEJA Y LISTA DE CONVERSACIONES (3 Columnas)              -->
        <!-- ====================================================================== -->
        <div id="colChats" class="lg:col-span-3 card-surface rounded-3xl border border-slate-100 shadow-sm overflow-hidden flex flex-col h-[740px]">
            
            <!-- Barra de Búsqueda y Filtros -->
            <div class="p-3.5 border-b border-slate-100 bg-slate-50/70 space-y-2.5">
                <div class="relative">
                    <input
                        type="text"
                        id="chatSearchInput"
                        oninput="filterConversationList(this.value)"
                        placeholder="Buscar cliente, fono o pedido..."
                        class="w-full pl-8 pr-3 py-2 rounded-xl border border-slate-200 bg-white text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                    >
                    <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <!-- Pestañas de Filtros Rápidos -->
                <div class="flex items-center gap-1 overflow-x-auto custom-scrollbar pb-1 text-[11px]">
                    <button type="button" onclick="filterByTab('all')" id="filterTabAll" class="filter-tab px-2.5 py-1 rounded-lg font-bold bg-slate-900 text-white whitespace-nowrap">
                        Todos
                    </button>
                    <button type="button" onclick="filterByTab('unread')" id="filterTabUnread" class="filter-tab px-2.5 py-1 rounded-lg font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 whitespace-nowrap">
                        No leídos @if($stats['unread'] > 0) <span class="ml-0.5 px-1.5 py-0.2 bg-emerald-500 text-slate-950 rounded-full text-[9px]">{{ $stats['unread'] }}</span> @endif
                    </button>
                    <button type="button" onclick="filterByTab('in_progress')" id="filterTabProgress" class="filter-tab px-2.5 py-1 rounded-lg font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 whitespace-nowrap">
                        En atención
                    </button>
                    <button type="button" onclick="filterByTab('open')" id="filterTabOpen" class="filter-tab px-2.5 py-1 rounded-lg font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 whitespace-nowrap">
                        Abiertas
                    </button>
                    <button type="button" onclick="filterByTab('resolved')" id="filterTabResolved" class="filter-tab px-2.5 py-1 rounded-lg font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 whitespace-nowrap">
                        Cerradas
                    </button>
                </div>
            </div>

            <!-- Listado de Conversaciones -->
            <div class="flex-1 overflow-y-auto divide-y divide-slate-100 custom-scrollbar" id="conversationList">
                @forelse($conversations as $c)
                @php
                    $isActive = ($activeId == $c->id);
                    $initials = strtoupper(substr($c->customer_name ?: 'C', 0, 2));
                @endphp
                <div
                    id="convItem-{{ $c->id }}"
                    onclick="selectConversation({{ $c->id }})"
                    data-status="{{ $c->status }}"
                    data-unread="{{ $c->unread_count }}"
                    data-search="{{ strtolower(($c->customer_name ?: '').' '.($c->customer_phone ?: '').' '.($c->order_reference ?: '')) }}"
                    class="conv-list-item p-3.5 cursor-pointer transition-all hover:bg-slate-50 flex items-start gap-3 {{ $isActive ? 'bg-emerald-50/70 border-l-4 border-emerald-500' : '' }}"
                >
                    <!-- Avatar con Iniciales y Badge de Conexión -->
                    <div class="relative flex-shrink-0">
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-slate-800 to-slate-900 text-emerald-300 font-black text-xs flex items-center justify-center shadow-xs">
                            {{ $initials }}
                        </div>
                        @if($c->unread_count > 0)
                        <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-emerald-500 text-slate-950 font-black text-[10px] flex items-center justify-center shadow-xs animate-bounce">
                            {{ $c->unread_count }}
                        </span>
                        @endif
                    </div>

                    <!-- Datos del Chat -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1">
                            <h3 class="text-xs font-black text-slate-900 truncate">
                                {{ $c->customer_name ?: 'Cliente WhatsApp' }}
                            </h3>
                            <span class="text-[10px] text-slate-400 font-mono whitespace-nowrap">
                                {{ $c->last_activity_at?->diffForHumans(null, true, true) ?? '' }}
                            </span>
                        </div>

                        <p class="text-[11px] text-slate-500 font-mono truncate">
                            {{ $c->customer_phone }}
                            @if($c->order_reference)
                                &bull; <span class="text-emerald-700 font-bold">{{ $c->order_reference }}</span>
                            @endif
                        </p>

                        <p class="text-[11px] text-slate-600 truncate mt-0.5">
                            {{ $c->last_message_preview ?: 'Sin mensajes aún.' }}
                        </p>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-xs text-slate-400">
                    No se encontraron conversaciones.
                </div>
                @endforelse
            </div>

            <!-- Resumen Inferior -->
            <div class="p-2.5 bg-slate-50 border-t border-slate-100 text-center text-[10.5px] text-slate-500 font-medium">
                Total: <strong class="text-slate-800">{{ $conversations->total() }}</strong> conversaciones registradas
            </div>
        </div>

        <!-- ====================================================================== -->
        <!-- COLUMNA 2: HILO DE CONVERSACIÓN Y CHAT INTEGRADO (6 Columnas)          -->
        <!-- ====================================================================== -->
        <div id="colThread" class="hidden lg:flex lg:col-span-6 card-surface rounded-3xl border border-slate-100 shadow-sm overflow-hidden flex-col h-[740px]">
            
            @if($activeConversation)
            <!-- Cabecera del Chat Activo -->
            <div class="p-4 border-b border-slate-200 bg-slate-50/90 flex items-center justify-between gap-3 flex-shrink-0" id="threadHeader">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white font-black text-xs flex items-center justify-center shadow-xs flex-shrink-0" id="chatHeaderAvatar">
                        {{ strtoupper(substr($activeConversation->customer_name ?: 'C', 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-black text-slate-900 truncate" id="chatHeaderName">
                                {{ $activeConversation->customer_name ?: 'Cliente WhatsApp' }}
                            </h2>
                            <span class="px-2 py-0.2 rounded-md text-[10px] font-bold bg-slate-200 text-slate-700" id="chatHeaderStatus">
                                {{ $activeConversation->status_label }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 font-mono truncate" id="chatHeaderPhone">
                            {{ $activeConversation->customer_phone }}
                            <span id="chat24hBadge" class="ml-2 inline-flex items-center gap-1 font-sans text-[10px] font-semibold {{ $activeConversation->isWithin24HourWindow() ? 'text-emerald-700' : 'text-amber-700' }}">
                                @if($activeConversation->isWithin24HourWindow())
                                    ⚡ Ventana 24h activa
                                @else
                                    ⏳ Ventana expirada (requiere plantilla)
                                @endif
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Botones Superiores de la Cabecera -->
                <div class="flex items-center gap-2 flex-shrink-0">
                    <button
                        type="button"
                        onclick="refreshCurrentChat()"
                        title="Refrescar mensajes"
                        class="p-2 rounded-xl bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 transition-colors shadow-xs"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                    @if($activeConversation->order)
                    <a
                        href="{{ route('admin.orders.show', $activeConversation->order) }}"
                        class="px-2.5 py-1.5 rounded-xl bg-slate-900 text-white font-mono font-bold text-[11px] hover:bg-slate-800 transition-colors shadow-xs"
                    >
                        {{ $activeConversation->order->order_number }}
                    </a>
                    @endif
                </div>
            </div>

            <!-- Historial de Mensajes Cronológico -->
            <div class="flex-1 p-4 sm:p-5 overflow-y-auto space-y-3.5 bg-slate-100/60 custom-scrollbar" id="messagesContainer">
                <div class="flex justify-center">
                    <span class="px-3 py-1 rounded-full bg-slate-200 text-slate-600 text-[10.5px] font-medium text-center">
                        🔒 Chat comercial integrado de Portátiles Perú &bull; Todos los mensajes se guardan en el sistema
                    </span>
                </div>

                @forelse($activeConversation->messages as $msg)
                @php
                    $isInbound = ($msg->direction === 'inbound');
                    $isNote = ($msg->type === 'note');
                @endphp
                <div class="flex {{ $isInbound ? 'justify-start' : 'justify-end' }}" id="msg-item-{{ $msg->id }}">
                    <div class="max-w-[82%] rounded-2xl p-3.5 shadow-xs transition-all {{ $isNote ? 'bg-amber-50 border border-amber-200 text-amber-950' : ($isInbound ? 'bg-white border border-slate-200 text-slate-900' : 'bg-emerald-600 text-white') }}">
                        
                        @if($isNote)
                        <div class="flex items-center gap-1.5 text-[10px] font-black text-amber-700 uppercase tracking-wider mb-1">
                            <span>🔒 Nota Interna Confidencial</span>
                        </div>
                        @endif

                        <!-- Contenido Texto -->
                        <p class="text-xs whitespace-pre-wrap leading-relaxed {{ $isInbound ? 'text-slate-800' : ($isNote ? 'text-amber-950' : 'text-white') }}">{{ $msg->content }}</p>

                        <!-- Adjunto Multimedia si existe -->
                        @if($msg->attachment_url)
                        <div class="mt-2 pt-2 border-t {{ $isInbound ? 'border-slate-100' : 'border-white/20' }}">
                            @if($msg->attachment_type === 'image')
                                <a href="{{ $msg->attachment_url }}" target="_blank" class="block rounded-xl overflow-hidden max-h-48 border border-white/20">
                                    <img src="{{ $msg->attachment_url }}" alt="Adjunto" class="w-full object-cover">
                                </a>
                            @else
                                <a href="{{ $msg->attachment_url }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl {{ $isInbound ? 'bg-slate-100 text-slate-800' : 'bg-white/20 text-white' }} text-xs font-bold hover:underline">
                                    <span>📄 {{ $msg->attachment_filename ?: 'Ver documento adjunto' }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                        @endif

                        <!-- Pie del Mensaje: Hora y Ticks de Estado -->
                        <div class="mt-1.5 flex items-center justify-between gap-3 text-[10px] {{ $isNote ? 'text-amber-700/80' : ($isInbound ? 'text-slate-400' : 'text-emerald-100/90') }}">
                            <span>{{ $msg->user?->name ?? ($isInbound ? ($activeConversation->customer_name ?: 'Cliente') : 'Asesor') }}</span>
                            <div class="flex items-center gap-1 font-mono">
                                <span>{{ $msg->sent_at?->format('H:i') }}</span>
                                @if(! $isInbound && ! $isNote)
                                    @if($msg->status === 'pending')
                                        <span title="Pendiente de envío">⏳</span>
                                    @elseif($msg->status === 'sent')
                                        <span title="Enviado a WhatsApp">✓</span>
                                    @elseif($msg->status === 'delivered')
                                        <span title="Entregado al teléfono">✓✓</span>
                                    @elseif($msg->status === 'read')
                                        <span class="text-cyan-300 font-bold" title="Leído por el cliente">✓✓</span>
                                    @elseif($msg->status === 'failed')
                                        <span class="text-red-300 font-bold cursor-help" title="Error: {{ $msg->error_message }}">⚠️</span>
                                    @endif
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
                @empty
                <div class="py-16 text-center text-xs text-slate-400 font-medium">
                    Aún no hay mensajes en esta conversación. Puedes redactar un mensaje, adjuntar una ficha técnica o guardar notas internas.
                </div>
                @endforelse
            </div>

            <!-- Acciones Rápidas (Catálogo, Ficha Técnica de Laptop, Medios de Pago) -->
            <div class="px-4 py-2 bg-slate-50 border-t border-slate-200/80 flex items-center gap-1.5 overflow-x-auto custom-scrollbar flex-shrink-0">
                <span class="text-[10px] uppercase font-bold text-slate-400 mr-1 flex-shrink-0">Rápidas:</span>

                <button
                    type="button"
                    onclick="document.getElementById('modalProductSpecs').classList.remove('hidden')"
                    class="px-2.5 py-1 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[11px] shadow-xs transition-all flex items-center gap-1 whitespace-nowrap"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Ficha Técnica Laptop</span>
                </button>

                <button
                    type="button"
                    onclick="insertQuickText('catalog')"
                    class="px-2.5 py-1 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-[11px] shadow-xs transition-all whitespace-nowrap"
                >
                    📚 Catálogo Web
                </button>

                <button
                    type="button"
                    onclick="insertQuickText('payment')"
                    class="px-2.5 py-1 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 font-bold text-[11px] shadow-xs transition-all whitespace-nowrap"
                >
                    💳 Cuentas y Pagos
                </button>

                <button
                    type="button"
                    onclick="insertQuickText('warranty')"
                    class="px-2.5 py-1 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 font-bold text-[11px] shadow-xs transition-all whitespace-nowrap"
                >
                    🛡️ Garantía 12 Meses
                </button>
            </div>

            <!-- Vista Previa de Archivo Adjunto (si se selecciona uno) -->
            <div id="attachmentPreviewBar" class="hidden px-4 py-2 bg-emerald-50 border-t border-emerald-200 flex items-center justify-between text-xs text-emerald-900">
                <div class="flex items-center gap-2 truncate">
                    <span class="text-base" id="attachPreviewIcon">📎</span>
                    <span class="font-bold truncate" id="attachPreviewFilename">archivo.pdf</span>
                    <span class="text-[10px] text-emerald-700" id="attachPreviewSize">(0 KB)</span>
                </div>
                <button type="button" onclick="clearAttachment()" class="p-1 rounded-lg hover:bg-emerald-200 text-emerald-800" title="Quitar archivo">
                    ✕
                </button>
            </div>

            <!-- Caja de Redacción y Envío -->
            <div class="p-3 bg-white border-t border-slate-200 flex-shrink-0">
                <form id="chatSendForm" onsubmit="handleChatSubmit(event)" class="space-y-2">
                    <div class="relative flex items-end gap-2">
                        
                        <!-- Botón Adjuntar Archivo -->
                        <div class="relative flex-shrink-0">
                            <input
                                type="file"
                                id="chatFileInput"
                                onchange="handleFileSelected(this)"
                                class="hidden"
                                accept=".pdf,.jpg,.jpeg,.png,.webp"
                            >
                            <button
                                type="button"
                                onclick="document.getElementById('chatFileInput').click()"
                                class="p-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors"
                                title="Adjuntar imagen o PDF (Ficha técnica o proforma)"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                            </button>
                        </div>

                        <!-- Selector Modo: WhatsApp o Nota Interna -->
                        <div class="flex-shrink-0">
                            <select
                                id="messageSendType"
                                class="rounded-xl border border-slate-200 bg-slate-50 px-2 py-2 text-[11px] font-bold text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            >
                                @if($whatsappStatus['configured'])
                                    <option value="whatsapp" selected>📱 WhatsApp</option>
                                    <option value="note">🔒 Nota Interna</option>
                                @else
                                    <option value="note" selected>🔒 Nota Interna</option>
                                    <option value="whatsapp">📱 WhatsApp (Requiere API)</option>
                                @endif
                            </select>
                        </div>

                        <!-- Textarea de Redacción -->
                        <div class="flex-1 min-w-0">
                            <textarea
                                id="chatTextInput"
                                rows="2"
                                onkeydown="handleTextKeydown(event)"
                                placeholder="Escribe un mensaje... (Enter para enviar, Shift+Enter para salto de línea)"
                                class="w-full rounded-2xl border border-slate-300 bg-white p-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all resize-none"
                            ></textarea>
                        </div>

                        <!-- Botón Enviar -->
                        <button
                            type="submit"
                            id="btnSendChat"
                            class="p-2.5 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black shadow-md transition-all flex items-center justify-center hover:scale-105 flex-shrink-0"
                            title="Enviar mensaje (Enter)"
                        >
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                            </svg>
                        </button>
                    </div>

                    <div class="flex items-center justify-between text-[10px] text-slate-400 px-1">
                        <span>Presiona <strong>Enter</strong> para enviar &bull; <strong>Shift + Enter</strong> para nueva línea</span>
                        <span id="chatSendingIndicator" class="hidden text-emerald-600 font-bold animate-pulse">Enviando mensaje...</span>
                    </div>
                </form>
            </div>
            @else
            <!-- Estado Vacío cuando no hay chat seleccionado -->
            <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-slate-400">
                <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-300 flex items-center justify-center mb-3">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <h3 class="text-sm font-black text-slate-700">Selecciona una conversación</h3>
                <p class="text-xs text-slate-400 mt-1 max-w-sm">
                    Elige una conversación en la columna izquierda o inicia una nueva para ver los datos del cliente y conversar en vivo.
                </p>
            </div>
            @endif

        </div>

        <!-- ====================================================================== -->
        <!-- COLUMNA 3: INFORMACIÓN COMERCIAL Y GESTIÓN (3 Columnas)                 -->
        <!-- ====================================================================== -->
        <div id="colInfo" class="hidden lg:flex lg:col-span-3 card-surface rounded-3xl border border-slate-100 shadow-sm p-4 overflow-y-auto flex-col h-[740px] space-y-4 custom-scrollbar">
            
            @if($activeConversation)
            <!-- 1. Estado y Asesor Asignado -->
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 space-y-3">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Gestión de Conversación</span>
                
                <form method="POST" action="{{ route('admin.conversations.update', $activeConversation) }}" class="space-y-2">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="text-[10.5px] font-bold text-slate-600 block mb-0.5">Estado:</label>
                        <select name="status" class="w-full rounded-xl border border-slate-200 bg-white p-2 text-xs text-slate-800 font-medium focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            @foreach(['open'=>'Abierta','in_progress'=>'En atención','resolved'=>'Resuelta','archived'=>'Archivada'] as $val => $lbl)
                                <option value="{{ $val }}" @selected($activeConversation->status === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-[10.5px] font-bold text-slate-600 block mb-0.5">Asesor Responsable:</label>
                        <select name="user_id" class="w-full rounded-xl border border-slate-200 bg-white p-2 text-xs text-slate-800 font-medium focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <option value="">Sin asignar</option>
                            @foreach($advisors as $adv)
                                <option value="{{ $adv->id }}" @selected($activeConversation->user_id === $adv->id)>{{ $adv->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button class="w-full py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition-colors">
                        Actualizar Atención
                    </button>
                </form>
            </div>

            <!-- 2. Pedido Asociado -->
            @if($activeConversation->order)
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 space-y-2 text-xs" id="commercialOrderBlock">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Pedido Solicitado</span>
                    <a href="{{ route('admin.orders.show', $activeConversation->order) }}" class="text-[11px] font-bold text-emerald-700 hover:underline">
                        Ver Ficha Completa &rarr;
                    </a>
                </div>
                <div class="space-y-1">
                    <p class="font-mono font-bold text-slate-900 text-sm">{{ $activeConversation->order->order_number }}</p>
                    <p class="text-slate-500">Total: <strong class="text-emerald-700 font-mono text-xs">{{ $activeConversation->order->formatted_total }}</strong></p>
                    <p class="text-slate-500">Estado: <span class="font-semibold text-slate-800">{{ ucfirst($activeConversation->order->status) }}</span></p>
                </div>

                @if($activeConversation->order->items->isNotEmpty())
                <div class="pt-2 border-t border-slate-200/60 space-y-1.5">
                    <span class="text-[10px] font-bold text-slate-500 uppercase block">Productos:</span>
                    @foreach($activeConversation->order->items as $item)
                    <div class="text-[11px] text-slate-700 flex justify-between gap-2">
                        <span class="truncate">{{ $item->quantity }}x {{ $item->product_name }}</span>
                        <span class="font-mono font-bold text-slate-900 whitespace-nowrap">{{ $item->formatted_subtotal }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            <!-- 3. Ficha y Datos del Cliente -->
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 space-y-2 text-xs" id="commercialCustomerBlock">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Datos del Cliente</span>
                
                @if($activeConversation->customer)
                <div class="space-y-1 text-slate-600">
                    <p><strong class="text-slate-800">Nombre:</strong> {{ $activeConversation->customer->name }}</p>
                    <p><strong class="text-slate-800">Teléfono:</strong> {{ $activeConversation->customer->phone }}</p>
                    <p><strong class="text-slate-800">Email:</strong> {{ $activeConversation->customer->email ?: 'Sin registrar' }}</p>
                    <p><strong class="text-slate-800">Ciudad:</strong> {{ $activeConversation->customer->city ?: 'No especificada' }}</p>
                    <p><strong class="text-slate-800">Compras acumuladas:</strong> {{ $activeConversation->customer->orders_count }} pedidos ({{ $activeConversation->customer->formatted_total_spent }})</p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('admin.customers.show', $activeConversation->customer) }}" class="block text-center py-2 px-3 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-bold border border-slate-200 transition-colors shadow-xs">
                        Abrir Perfil de Cliente
                    </a>
                </div>
                @else
                <div class="text-slate-500 text-[11px] space-y-2">
                    <p>Cliente no registrado como perfil comercial.</p>
                    <p><strong class="text-slate-700">Nombre:</strong> {{ $activeConversation->customer_name }}</p>
                    <p><strong class="text-slate-700">Teléfono:</strong> {{ $activeConversation->customer_phone }}</p>
                </div>
                @endif
            </div>

            <!-- 4. Notas Internas Privadas del Cliente -->
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 space-y-2 text-xs" id="commercialNotesBlock">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Notas Internas Confidenciales</span>
                
                @if($activeConversation->customer && $activeConversation->customer->notes->isNotEmpty())
                <div class="space-y-2 max-h-40 overflow-y-auto custom-scrollbar">
                    @foreach($activeConversation->customer->notes as $note)
                    <div class="p-2.5 bg-white rounded-xl border border-slate-200/70 text-[11px] text-slate-700">
                        <p class="leading-relaxed">{{ $note->note }}</p>
                        <p class="mt-1 text-[9px] text-slate-400 font-mono">{{ $note->user?->name ?? 'Personal' }} &bull; {{ $note->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-[11px] text-slate-400">Sin notas internas registradas aún para este cliente.</p>
                @endif
            </div>
            @else
            <div class="p-8 text-center text-xs text-slate-400">
                Selecciona una conversación para inspeccionar sus datos comerciales.
            </div>
            @endif

        </div>

    </div>

</div>

<!-- ========================================================================== -->
<!-- MODALES: NUEVA CONVERSACIÓN, FICHA TÉCNICA Y ESTADO DE META                -->
<!-- ========================================================================== -->

<!-- Modal: Iniciar Nueva Conversación -->
<div id="modalNewConversation" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card-surface rounded-3xl max-w-md w-full p-6 border border-slate-100 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-base font-black text-slate-900">Nueva Conversación WhatsApp</h3>
                <p class="text-xs text-slate-500">Inicia un chat con un cliente en el centro de atención.</p>
            </div>
            <button type="button" onclick="document.getElementById('modalNewConversation').classList.add('hidden')" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl">
                ✕
            </button>
        </div>

        <form action="{{ route('admin.conversations.store') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-bold text-slate-700 block mb-1">Nombre del Cliente *</label>
                <input name="customer_name" required placeholder="Ej. Carlos Mendoza" class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-700 block mb-1">Teléfono WhatsApp *</label>
                <input name="customer_phone" required placeholder="Ej. 987654321" class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-700 block mb-1">N° Pedido (opcional)</label>
                <input name="order_reference" placeholder="PED-2026-00001" class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-700 block mb-1">Resumen o Asunto</label>
                <textarea name="summary" rows="2" placeholder="Consulta sobre disponibilidad de laptop gamer..." class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modalNewConversation').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-500 text-slate-950 font-black text-xs shadow-sm">
                    Crear y Abrir Chat
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Selector de Ficha Técnica de Laptop -->
<div id="modalProductSpecs" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card-surface rounded-3xl max-w-lg w-full p-6 border border-slate-100 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-base font-black text-slate-900">Adjuntar Ficha Técnica de Laptop</h3>
                <p class="text-xs text-slate-500">Selecciona el equipo para insertar especificaciones, precio y enlaces.</p>
            </div>
            <button type="button" onclick="document.getElementById('modalProductSpecs').classList.add('hidden')" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl">
                ✕
            </button>
        </div>

        <div class="space-y-3">
            <label class="text-xs font-bold text-slate-700 block">Seleccionar Laptop:</label>
            <select id="selectProductForSpecs" class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-800 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                <option value="">-- Elige una laptop del catálogo --</option>
                @foreach($products as $prod)
                    <option value="{{ $prod->id }}">
                        {{ $prod->brand?->name }} &bull; {{ $prod->name }} — {{ $prod->formatted_price }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" onclick="document.getElementById('modalProductSpecs').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs">
                Cancelar
            </button>
            <button type="button" onclick="insertSelectedProductSheet()" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-sm">
                Insertar en Mensaje
            </button>
        </div>
    </div>
</div>

<!-- Modal: Estado de Configuración Meta WhatsApp API -->
<div id="modalConfigInfo" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card-surface rounded-3xl max-w-lg w-full p-6 border border-slate-100 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full {{ $whatsappStatus['configured'] ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                <h3 class="text-base font-black text-slate-900">Estado de Meta WhatsApp API</h3>
            </div>
            <button type="button" onclick="document.getElementById('modalConfigInfo').classList.add('hidden')" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl">
                ✕
            </button>
        </div>

        <div class="space-y-3 text-xs text-slate-700">
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/70 space-y-1 font-mono text-[11px]">
                <p><strong>URL del Webhook oficial:</strong></p>
                <div class="flex items-center gap-2 bg-white p-2 rounded-xl border border-slate-200">
                    <span class="truncate flex-1 text-emerald-800">{{ $whatsappStatus['webhook_url'] }}</span>
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $whatsappStatus['webhook_url'] }}'); alert('URL copiada.');" class="px-2 py-1 rounded bg-slate-100 text-slate-700 font-bold hover:bg-slate-200">
                        Copiar
                    </button>
                </div>
            </div>

            @if(! empty($whatsappStatus['missing_keys']))
            <div class="p-3 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 space-y-1.5">
                <p class="font-bold">Variables faltantes en tu archivo <code>.env</code>:</p>
                <ul class="list-disc list-inside space-y-0.5 font-mono text-[11px]">
                    @foreach($whatsappStatus['missing_keys'] as $key)
                        <li>{{ $key }}</li>
                    @endforeach
                </ul>
            </div>
            @else
            <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 space-y-1">
                <p class="font-bold">✅ Configuración Completa y Operativa</p>
                <p class="text-[11px]">Phone ID: {{ $whatsappStatus['phone_number_id'] }}</p>
            </div>
            @endif

            <p class="text-[11px] text-slate-500 leading-relaxed">
                El endpoint de webhook admite verificación mediante <code>GET /webhook/whatsapp</code> con <code>hub.verify_token</code> y recibe mensajes y confirmaciones de lectura vía <code>POST /webhook/whatsapp</code>.
            </p>
        </div>

        <div class="flex justify-end pt-2 border-t border-slate-100">
            <button type="button" onclick="document.getElementById('modalConfigInfo').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs">
                Cerrar
            </button>
        </div>
    </div>
</div>

<!-- Modal: Aviso de WhatsApp no configurado al presionar enviar -->
<div id="modalNotConfiguredAlert" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card-surface rounded-3xl max-w-md w-full p-6 border border-amber-200 shadow-2xl space-y-4">
        <div class="flex items-start gap-3">
            <div class="p-2.5 bg-amber-500 text-white rounded-2xl flex-shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-black text-slate-900">WhatsApp Cloud API no configurada</h3>
                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                    Para enviar mensajes directos al teléfono del cliente se requieren las credenciales de <strong>Meta WhatsApp Cloud API</strong> en tu archivo <code>.env</code>.
                </p>
            </div>
        </div>

        <div class="p-3 bg-amber-50 rounded-2xl border border-amber-200 text-xs text-amber-900 space-y-1">
            <p class="font-bold">¿Cómo deseas proceder con tu mensaje?</p>
            <p class="text-[11px] text-amber-800 leading-relaxed">
                Puedes guardarlo de inmediato como una <strong>Nota Interna</strong> en el chat para registrar la atención, o abrir el asistente de configuración de Meta.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-2 pt-2 border-t border-slate-100">
            <button
                type="button"
                onclick="convertPendingMessageToNoteAndSend()"
                class="flex-1 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-xs text-center transition-colors"
            >
                Guardar como Nota Interna ahora
            </button>
            <button
                type="button"
                onclick="document.getElementById('modalNotConfiguredAlert').classList.add('hidden'); document.getElementById('modalConfigInfo').classList.remove('hidden');"
                class="py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs text-center transition-colors"
            >
                Ver variables en .env
            </button>
        </div>
    </div>
</div>

<!-- Toast flotante de notificaciones -->
<div id="chatToastNotification" class="fixed top-6 right-6 z-50 transform transition-all duration-300 translate-y-[-100px] opacity-0 pointer-events-none">
    <div id="chatToastInner" class="p-3.5 rounded-2xl bg-slate-900 text-white text-xs font-semibold shadow-2xl flex items-center gap-3 border border-slate-700">
        <span id="chatToastIcon">ℹ️</span>
        <span id="chatToastMessage">Mensaje de notificación</span>
    </div>
</div>

@php
    $productsPayload = $productsData ?? [];
@endphp

<!-- ========================================================================== -->
<!-- SCRIPT REACTIVO DE GESTIÓN DE CHAT, POLLING Y ACCIONES RÁPIDAS             -->
<!-- ========================================================================== -->
<script>
    const CSRF_TOKEN = '{{ $csrfToken }}';
    const CATALOG_URL = '{{ $catalogUrl }}';
    const PRODUCTS_DATA = @json($productsPayload);

    let currentConversationId = {{ $activeId ?: 'null' }};
    let lastMessageId = 0;
    let syncIntervalTimer = null;
    let currentSelectedFile = null;

    document.addEventListener('DOMContentLoaded', () => {
        // Inicializar último id de mensaje
        initLastMessageId();
        scrollToBottom();

        // Sondeo asíncrono cada 3.5 segundos si hay conversación activa
        if (currentConversationId) {
            startSyncPolling();
        }
    });

    function initLastMessageId() {
        const msgs = document.querySelectorAll('[id^="msg-item-"]');
        if (msgs.length > 0) {
            const last = msgs[msgs.length - 1];
            const id = parseInt(last.id.replace('msg-item-', ''), 10);
            if (!isNaN(id)) lastMessageId = id;
        }
    }

    function scrollToBottom() {
        const c = document.getElementById('messagesContainer');
        if (c) c.scrollTop = c.scrollHeight;
    }

    // ── Selección asíncrona de conversación sin recarga total ───────────────────
    async function selectConversation(id) {
        if (!id) return;
        currentConversationId = id;

        // Actualizar URL en el navegador sin recargar
        window.history.pushState(null, '', `/admin/conversaciones/${id}`);

        // Actualizar clases activas en la lista izquierda
        document.querySelectorAll('.conv-list-item').forEach(el => {
            el.classList.remove('bg-emerald-50/70', 'border-l-4', 'border-emerald-500');
        });
        const activeItem = document.getElementById(`convItem-${id}`);
        if (activeItem) {
            activeItem.classList.add('bg-emerald-50/70', 'border-l-4', 'border-emerald-500');
        }

        // Si estamos en móvil, mostrar la conversación
        switchMobileTab('thread');

        try {
            const res = await fetch(`/admin/conversaciones/${id}/data`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('Error al cargar conversación');
            const data = await res.json();

            renderConversationThread(data);
            renderCommercialInfo(data);

            // Iniciar sondeo para la conversación activa
            startSyncPolling();
        } catch (err) {
            console.error(err);
        }
    }

    function renderConversationThread(data) {
        const c = data.conversation;
        document.getElementById('chatHeaderName').textContent = c.customer_name;
        document.getElementById('chatHeaderPhone').textContent = c.customer_phone;
        document.getElementById('chatHeaderStatus').textContent = c.status_label;
        document.getElementById('chatHeaderAvatar').textContent = (c.customer_name || 'C').substring(0, 2).toUpperCase();

        const badge24h = document.getElementById('chat24hBadge');
        if (badge24h) {
            badge24h.textContent = c.is_within_24h ? '⚡ Ventana 24h activa' : '⏳ Ventana expirada (requiere plantilla)';
            badge24h.className = `ml-2 inline-flex items-center gap-1 font-sans text-[10px] font-semibold ${c.is_within_24h ? 'text-emerald-700' : 'text-amber-700'}`;
        }

        // Renderizar mensajes
        const container = document.getElementById('messagesContainer');
        container.innerHTML = `
            <div class="flex justify-center">
                <span class="px-3 py-1 rounded-full bg-slate-200 text-slate-600 text-[10.5px] font-medium text-center">
                    🔒 Chat comercial integrado de Portátiles Perú &bull; Todos los mensajes se guardan en el sistema
                </span>
            </div>
        `;

        lastMessageId = 0;
        data.messages.forEach(msg => {
            appendMessageBubble(msg);
            if (msg.id > lastMessageId) lastMessageId = msg.id;
        });

        scrollToBottom();
    }

    function renderCommercialInfo(data) {
        // Actualizar bloque de cliente
        const custBlock = document.getElementById('commercialCustomerBlock');
        if (custBlock && data.customer) {
            const cust = data.customer;
            custBlock.innerHTML = `
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Datos del Cliente</span>
                <div class="space-y-1 text-slate-600 text-xs">
                    <p><strong class="text-slate-800">Nombre:</strong> ${cust.name}</p>
                    <p><strong class="text-slate-800">Teléfono:</strong> ${cust.phone || 'N/A'}</p>
                    <p><strong class="text-slate-800">Email:</strong> ${cust.email || 'Sin registrar'}</p>
                    <p><strong class="text-slate-800">Ciudad:</strong> ${cust.city || 'No especificada'}</p>
                    <p><strong class="text-slate-800">Compras acumuladas:</strong> ${cust.orders_count} pedidos (${cust.total_spent})</p>
                </div>
                <div class="pt-2">
                    <a href="${cust.show_url}" class="block text-center py-2 px-3 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-bold border border-slate-200 transition-colors shadow-xs">
                        Abrir Perfil de Cliente
                    </a>
                </div>
            `;
        }

        // Actualizar bloque de pedido
        const ordBlock = document.getElementById('commercialOrderBlock');
        if (ordBlock && data.order) {
            const ord = data.order;
            let itemsHtml = '';
            if (ord.items && ord.items.length) {
                itemsHtml = `<div class="pt-2 border-t border-slate-200/60 space-y-1.5"><span class="text-[10px] font-bold text-slate-500 uppercase block">Productos:</span>` +
                    ord.items.map(it => `<div class="text-[11px] text-slate-700 flex justify-between gap-2"><span class="truncate">${it.quantity}x ${it.product_name}</span><span class="font-mono font-bold text-slate-900 whitespace-nowrap">${it.formatted_subtotal}</span></div>`).join('') +
                    `</div>`;
            }
            ordBlock.innerHTML = `
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Pedido Solicitado</span>
                    <a href="${ord.show_url}" class="text-[11px] font-bold text-emerald-700 hover:underline">Ver Ficha Completa &rarr;</a>
                </div>
                <div class="space-y-1">
                    <p class="font-mono font-bold text-slate-900 text-sm">${ord.order_number}</p>
                    <p class="text-slate-500">Total: <strong class="text-emerald-700 font-mono text-xs">${ord.total}</strong></p>
                    <p class="text-slate-500">Estado: <span class="font-semibold text-slate-800">${ord.status}</span></p>
                </div>
                ${itemsHtml}
            `;
        }
    }

    function appendMessageBubble(msg) {
        const container = document.getElementById('messagesContainer');
        const isInbound = (msg.direction === 'inbound');
        const isNote = (msg.type === 'note');

        let tickHtml = '';
        if (!isInbound && !isNote) {
            if (msg.status === 'pending') tickHtml = '<span title="Pendiente">⏳</span>';
            else if (msg.status === 'sent') tickHtml = '<span title="Enviado">✓</span>';
            else if (msg.status === 'delivered') tickHtml = '<span title="Entregado">✓✓</span>';
            else if (msg.status === 'read') tickHtml = '<span class="text-cyan-300 font-bold" title="Leído">✓✓</span>';
            else if (msg.status === 'failed') tickHtml = `<span class="text-red-300 font-bold cursor-help" title="${msg.error_message || 'Error'}">⚠️</span>`;
        }

        let mediaHtml = '';
        if (msg.attachment_url) {
            if (msg.attachment_type === 'image') {
                mediaHtml = `<div class="mt-2 pt-2 border-t ${isInbound ? 'border-slate-100' : 'border-white/20'}"><a href="${msg.attachment_url}" target="_blank" class="block rounded-xl overflow-hidden max-h-48 border border-white/20"><img src="${msg.attachment_url}" alt="Adjunto" class="w-full object-cover"></a></div>`;
            } else {
                mediaHtml = `<div class="mt-2 pt-2 border-t ${isInbound ? 'border-slate-100' : 'border-white/20'}"><a href="${msg.attachment_url}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl ${isInbound ? 'bg-slate-100 text-slate-800' : 'bg-white/20 text-white'} text-xs font-bold hover:underline"><span>📄 ${msg.attachment_filename || 'Ver documento'}</span></a></div>`;
            }
        }

        const bubble = document.createElement('div');
        bubble.id = `msg-item-${msg.id}`;
        bubble.className = `flex ${isInbound ? 'justify-start' : 'justify-end'}`;
        bubble.innerHTML = `
            <div class="max-w-[82%] rounded-2xl p-3.5 shadow-xs transition-all ${isNote ? 'bg-amber-50 border border-amber-200 text-amber-950' : (isInbound ? 'bg-white border border-slate-200 text-slate-900' : 'bg-emerald-600 text-white')}">
                ${isNote ? '<div class="flex items-center gap-1.5 text-[10px] font-black text-amber-700 uppercase tracking-wider mb-1"><span>🔒 Nota Interna Confidencial</span></div>' : ''}
                <p class="text-xs whitespace-pre-wrap leading-relaxed ${isInbound ? 'text-slate-800' : (isNote ? 'text-amber-950' : 'text-white')}">${escapeHtml(msg.content)}</p>
                ${mediaHtml}
                <div class="mt-1.5 flex items-center justify-between gap-3 text-[10px] ${isNote ? 'text-amber-700/80' : (isInbound ? 'text-slate-400' : 'text-emerald-100/90')}">
                    <span>${msg.user_name || (isInbound ? 'Cliente' : 'Asesor')}</span>
                    <div class="flex items-center gap-1 font-mono">
                        <span>${msg.time_only || ''}</span>
                        <span id="msg-tick-${msg.id}">${tickHtml}</span>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(bubble);
    }

    // ── Sondeo asíncrono en segundo plano (Polling liviano) ────────────────────
    function startSyncPolling() {
        if (syncIntervalTimer) clearInterval(syncIntervalTimer);
        syncIntervalTimer = setInterval(syncMessages, 3500);
    }

    async function syncMessages() {
        if (!currentConversationId) return;

        try {
            const res = await fetch(`/admin/conversaciones/${currentConversationId}/sync?after_id=${lastMessageId}`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const data = await res.json();

            // Mensajes nuevos
            if (data.new_messages && data.new_messages.length > 0) {
                data.new_messages.forEach(msg => {
                    appendMessageBubble(msg);
                    if (msg.id > lastMessageId) lastMessageId = msg.id;
                });
                scrollToBottom();

                // Marcar como leído automáticamente ya que el asesor tiene abierta la conversación
                fetch(`/admin/conversaciones/${currentConversationId}/chat/read`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }
                });
            }

            // Actualizar ticks de estado (delivered, read, failed)
            if (data.statuses && data.statuses.length > 0) {
                data.statuses.forEach(s => {
                    const tickEl = document.getElementById(`msg-tick-${s.id}`);
                    if (tickEl) {
                        if (s.status === 'read') tickEl.innerHTML = '<span class="text-cyan-300 font-bold" title="Leído">✓✓</span>';
                        else if (s.status === 'delivered') tickEl.innerHTML = '<span title="Entregado">✓✓</span>';
                        else if (s.status === 'sent') tickEl.innerHTML = '<span title="Enviado">✓</span>';
                        else if (s.status === 'failed') tickEl.innerHTML = `<span class="text-red-300 font-bold cursor-help" title="${s.error_message || 'Error'}">⚠️</span>`;
                    }
                });
            }
        } catch (e) {
            // Silencioso en caso de pérdida de red temporal
        }
    }

    // ── Envío de mensaje reactivo sin recargar ────────────────────────────────
    function handleTextKeydown(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            handleChatSubmit(e);
        }
    }

    let pendingMessageContent = '';

    function showToast(message, type = 'info') {
        const toast = document.getElementById('chatToastNotification');
        const msgEl = document.getElementById('chatToastMessage');
        const iconEl = document.getElementById('chatToastIcon');
        const inner = document.getElementById('chatToastInner');

        if (!toast || !msgEl) return;

        msgEl.textContent = message;
        if (type === 'error') {
            iconEl.textContent = '⚠️';
            inner.className = 'p-3.5 rounded-2xl bg-red-950 text-white text-xs font-semibold shadow-2xl flex items-center gap-3 border border-red-700';
        } else if (type === 'success') {
            iconEl.textContent = '✅';
            inner.className = 'p-3.5 rounded-2xl bg-emerald-950 text-emerald-200 text-xs font-semibold shadow-2xl flex items-center gap-3 border border-emerald-700';
        } else {
            iconEl.textContent = 'ℹ️';
            inner.className = 'p-3.5 rounded-2xl bg-slate-900 text-white text-xs font-semibold shadow-2xl flex items-center gap-3 border border-slate-700';
        }

        toast.classList.remove('translate-y-[-100px]', 'opacity-0', 'pointer-events-none');
        setTimeout(() => {
            toast.classList.add('translate-y-[-100px]', 'opacity-0', 'pointer-events-none');
        }, 4000);
    }

    function showNotConfiguredModal(content) {
        pendingMessageContent = content;
        const modal = document.getElementById('modalNotConfiguredAlert');
        if (modal) modal.classList.remove('hidden');
    }

    async function convertPendingMessageToNoteAndSend() {
        const modal = document.getElementById('modalNotConfiguredAlert');
        if (modal) modal.classList.add('hidden');

        const typeSelect = document.getElementById('messageSendType');
        if (typeSelect) typeSelect.value = 'note';

        const textInput = document.getElementById('chatTextInput');
        if (textInput && pendingMessageContent) {
            textInput.value = pendingMessageContent;
        }

        await handleChatSubmit();
    }

    async function handleChatSubmit(e) {
        if (e) e.preventDefault();
        const textInput = document.getElementById('chatTextInput');
        const content = textInput.value.trim();
        const typeSelect = document.getElementById('messageSendType');
        const sendType = typeSelect.value;
        const fileInput = document.getElementById('chatFileInput');
        const file = fileInput.files[0] || null;

        if (!content && !file) {
            return;
        }

        const indicator = document.getElementById('chatSendingIndicator');
        const btnSend = document.getElementById('btnSendChat');
        btnSend.disabled = true;
        if (indicator) indicator.classList.remove('hidden');

        try {
            // Si hay archivo seleccionado, enviamos vía endpoint de adjuntos
            if (file) {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('caption', content);
                formData.append('_token', CSRF_TOKEN);

                const res = await fetch(`/admin/conversaciones/${currentConversationId}/chat/attachment`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData
                });
                const data = await res.json();
                if (!res.ok) {
                    if (data.error_code === 'NOT_CONFIGURED') {
                        showNotConfiguredModal(content);
                    } else {
                        showToast(data.error || 'Error al enviar archivo.', 'error');
                    }
                } else {
                    appendMessageBubble(data.message);
                    if (data.message.id > lastMessageId) lastMessageId = data.message.id;
                    clearAttachment();
                    textInput.value = '';
                    scrollToBottom();
                    showToast('Archivo enviado con éxito.', 'success');
                }
            } else {
                // Envío de texto normal o nota
                const res = await fetch(`/admin/conversaciones/${currentConversationId}/chat/send`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        content: content,
                        type: sendType
                    })
                });
                const data = await res.json();
                if (!res.ok) {
                    if (data.error_code === 'NOT_CONFIGURED') {
                        showNotConfiguredModal(content);
                    } else {
                        showToast(data.error || 'Error al enviar mensaje.', 'error');
                    }
                } else {
                    appendMessageBubble(data.message);
                    if (data.message.id > lastMessageId) lastMessageId = data.message.id;
                    textInput.value = '';
                    scrollToBottom();
                }
            }
        } catch (err) {
            showToast('Fallo de conexión al enviar el mensaje.', 'error');
        } finally {
            btnSend.disabled = false;
            if (indicator) indicator.classList.add('hidden');
            textInput.focus();
        }
    }

    // ── Manejo de Archivos Adjuntos ──────────────────────────────────────────
    function handleFileSelected(input) {
        if (!input.files || !input.files[0]) return;
        currentSelectedFile = input.files[0];

        const bar = document.getElementById('attachmentPreviewBar');
        const nameEl = document.getElementById('attachPreviewFilename');
        const sizeEl = document.getElementById('attachPreviewSize');
        const iconEl = document.getElementById('attachPreviewIcon');

        nameEl.textContent = currentSelectedFile.name;
        sizeEl.textContent = `(${(currentSelectedFile.size / 1024).toFixed(1)} KB)`;
        iconEl.textContent = currentSelectedFile.type.includes('image') ? '📷' : '📄';

        bar.classList.remove('hidden');
    }

    function clearAttachment() {
        const input = document.getElementById('chatFileInput');
        input.value = '';
        currentSelectedFile = null;
        document.getElementById('attachmentPreviewBar').classList.add('hidden');
    }

    // ── Acciones Rápidas (Plantillas) ────────────────────────────────────────
    function insertQuickText(type) {
        const input = document.getElementById('chatTextInput');
        let text = '';

        if (type === 'catalog') {
            text = `📚 *CATÁLOGO OFICIAL DE PORTÁTILES PERÚ*\n` +
                `Hola! Te comparto nuestro catálogo online actualizado con stock y precios en tiempo real:\n` +
                `🔗 ${CATALOG_URL}\n\n` +
                `✅ Garantía oficial de 12 meses\n` +
                `✅ Envíos a todo el Perú`;
        } else if (type === 'payment') {
            text = `💳 *MEDIOS DE PAGO DISPONIBLES - PORTÁTILES PERÚ*\n` +
                `Aceptamos los siguientes métodos de pago seguro:\n` +
                `📱 *Billeteras:* Yape / Plin\n` +
                `🏦 *Transferencias:* BCP, BBVA, Interbank (Soles y Dólares)\n` +
                `💳 *Tarjetas:* Débito y Crédito vía link seguro\n\n` +
                `Una vez realizada la operación, envíanos la captura por aquí para confirmar tu despacho de inmediato.`;
        } else if (type === 'warranty') {
            text = `🛡️ *GARANTÍA Y COMPROMISO PORTÁTILES PERÚ*\n` +
                `Todos nuestros equipos cuentan con 12 meses de garantía oficial y soporte técnico directo en Lima y provincias. Todos los productos se entregan sellados y con comprobante (Boleta o Factura).`;
        }

        input.value = text;
        input.focus();
    }

    function insertSelectedProductSheet() {
        const select = document.getElementById('selectProductForSpecs');
        const prodId = select.value;
        if (!prodId) {
            alert('Por favor selecciona una laptop de la lista.');
            return;
        }

        const product = PRODUCTS_DATA.find(p => p.id == prodId);
        if (!product) return;

        let specsText = '';
        if (product.specs && typeof product.specs === 'object') {
            for (const [k, v] of Object.entries(product.specs)) {
                if (typeof v === 'string') {
                    const formattedKey = k.replace(/_/g, ' ').toUpperCase();
                    specsText += `• *${formattedKey}:* ${v}\n`;
                }
            }
        }

        const message = `💻 *PORTÁTILES PERÚ - FICHA TÉCNICA OFICIAL*\n` +
            `*Equipo:* ${product.name}\n` +
            `*Marca:* ${product.brand}\n` +
            `*SKU:* ${product.sku}\n` +
            `*Precio:* ${product.price}\n\n` +
            (specsText ? `⚙️ *ESPECIFICACIONES PRINCIPALES:*\n${specsText}\n` : '') +
            `🔗 *Ver fotos y detalles en la web:*\n${product.url}\n\n` +
            `📄 *Descargar Ficha Técnica en PDF:*\n${product.pdf_url}\n\n` +
            `¿Te gustaría coordinar la entrega o facturación de este equipo? Quedo atento a tus dudas.`;

        document.getElementById('chatTextInput').value = message;
        document.getElementById('modalProductSpecs').classList.add('hidden');
        document.getElementById('chatTextInput').focus();
    }

    function refreshCurrentChat() {
        if (currentConversationId) {
            selectConversation(currentConversationId);
        }
    }

    // ── Filtrado de la lista izquierda ───────────────────────────────────────
    function filterConversationList(query) {
        const q = query.toLowerCase().trim();
        document.querySelectorAll('.conv-list-item').forEach(item => {
            const data = item.getAttribute('data-search') || '';
            item.style.display = data.includes(q) ? 'flex' : 'none';
        });
    }

    function filterByTab(tab) {
        document.querySelectorAll('.filter-tab').forEach(btn => {
            btn.className = 'filter-tab px-2.5 py-1 rounded-lg font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 whitespace-nowrap';
        });

        if (tab === 'all') document.getElementById('filterTabAll').className = 'filter-tab px-2.5 py-1 rounded-lg font-bold bg-slate-900 text-white whitespace-nowrap';
        else if (tab === 'unread') document.getElementById('filterTabUnread').className = 'filter-tab px-2.5 py-1 rounded-lg font-bold bg-slate-900 text-white whitespace-nowrap';
        else if (tab === 'in_progress') document.getElementById('filterTabProgress').className = 'filter-tab px-2.5 py-1 rounded-lg font-bold bg-slate-900 text-white whitespace-nowrap';
        else if (tab === 'open') document.getElementById('filterTabOpen').className = 'filter-tab px-2.5 py-1 rounded-lg font-bold bg-slate-900 text-white whitespace-nowrap';
        else if (tab === 'resolved') document.getElementById('filterTabResolved').className = 'filter-tab px-2.5 py-1 rounded-lg font-bold bg-slate-900 text-white whitespace-nowrap';

        document.querySelectorAll('.conv-list-item').forEach(item => {
            const status = item.getAttribute('data-status');
            const unread = parseInt(item.getAttribute('data-unread') || '0', 10);

            if (tab === 'all') {
                item.style.display = 'flex';
            } else if (tab === 'unread') {
                item.style.display = unread > 0 ? 'flex' : 'none';
            } else if (tab === 'in_progress') {
                item.style.display = (status === 'in_progress') ? 'flex' : 'none';
            } else if (tab === 'open') {
                item.style.display = (status === 'open') ? 'flex' : 'none';
            } else if (tab === 'resolved') {
                item.style.display = (status === 'resolved' || status === 'archived') ? 'flex' : 'none';
            }
        });
    }

    // ── Vistas Móviles (Toggle entre Chats, Hilo e Información) ───────────────
    function switchMobileTab(tab) {
        const colChats = document.getElementById('colChats');
        const colThread = document.getElementById('colThread');
        const colInfo = document.getElementById('colInfo');

        const btnChats = document.getElementById('tabBtnChats');
        const btnThread = document.getElementById('tabBtnThread');
        const btnInfo = document.getElementById('tabBtnInfo');

        // Reset
        colChats.classList.add('hidden');
        colThread.classList.add('hidden');
        colInfo.classList.add('hidden');

        btnChats.className = 'flex-1 py-2 rounded-xl text-emerald-100 hover:bg-white/10 text-center';
        btnThread.className = 'flex-1 py-2 rounded-xl text-emerald-100 hover:bg-white/10 text-center';
        btnInfo.className = 'flex-1 py-2 rounded-xl text-emerald-100 hover:bg-white/10 text-center';

        if (tab === 'chats') {
            colChats.classList.remove('hidden');
            btnChats.className = 'flex-1 py-2 rounded-xl bg-emerald-500 text-slate-950 text-center shadow-xs';
        } else if (tab === 'thread') {
            colThread.classList.remove('hidden');
            colThread.classList.add('flex');
            btnThread.className = 'flex-1 py-2 rounded-xl bg-emerald-500 text-slate-950 text-center shadow-xs';
        } else if (tab === 'info') {
            colInfo.classList.remove('hidden');
            colInfo.classList.add('flex');
            btnInfo.className = 'flex-1 py-2 rounded-xl bg-emerald-500 text-slate-950 text-center shadow-xs';
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
</script>
@endsection
