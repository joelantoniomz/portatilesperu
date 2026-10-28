<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — Portátiles Perú</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        forest: {
                            950: '#03140C',
                            900: '#051A11',
                            850: '#072418',
                            800: '#0B3322',
                            700: '#104930',
                        },
                        emerald: {
                            400: '#34D399',
                            500: '#10B981',
                            600: '#059669',
                            700: '#047857',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #0F452C 0%, #062316 45%, #03120A 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4 sm:p-6 text-slate-800">

    <div class="w-full max-w-4xl grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        <!-- Columna Izquierda: Branding y Contexto -->
        <div class="lg:col-span-5 text-white space-y-6">
            <div class="space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-600 to-emerald-400 flex items-center justify-center shadow-xl shadow-emerald-500/30">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-extrabold tracking-tight">PORTÁTILES PERÚ</h1>
                <p class="text-emerald-200/80 text-sm leading-relaxed">
                    Plataforma de catálogo virtual, control de inventario con protección de sobreventa y ventas directas con trazabilidad.
                </p>
            </div>

            <div class="pt-4 border-t border-emerald-500/20 space-y-3 text-xs text-emerald-200/90">
                <div class="flex items-center gap-2.5">
                    <span class="w-5 h-5 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs font-bold">✓</span>
                    <span>Control de stock en tiempo real y ventas seguras</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="w-5 h-5 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs font-bold">✓</span>
                    <span>Generador de catálogos en PDF con imágenes</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="w-5 h-5 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs font-bold">✓</span>
                    <span>Asistente inteligente con Gemini AI</span>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Tarjeta de Login -->
        <div class="lg:col-span-7">
            <div class="bg-white rounded-3xl p-8 sm:p-10 shadow-2xl border border-white/20">
                <div class="mb-6">
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">Acceso Administrativo</h2>
                    <p class="text-slate-500 text-sm mt-1">Ingrese sus credenciales autorizadas por el servidor.</p>
                </div>

                @if(session('status'))
                <div class="mb-5 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
                @endif

                @if($errors->any())
                <div class="mb-5 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
                @endif

                <form action="{{ route('login.attempt') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Correo Electrónico</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </span>
                            <input type="email" name="email" id="email" required value="{{ old('email') }}" placeholder="ejemplo@portatilesperu.com" class="w-full pl-10 pr-4 py-3 rounded-2xl border border-slate-200 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Contraseña</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <input type="password" name="password" id="password" required placeholder="••••••••" class="w-full pl-10 pr-4 py-3 rounded-2xl border border-slate-200 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-medium text-slate-600">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                            <span>Recordar sesión</span>
                        </label>
                        <span class="text-xs text-slate-400 font-medium">Validación en servidor activa</span>
                    </div>

                    <button type="submit" class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-bold text-sm shadow-xl shadow-emerald-500/30 transition-all hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-2">
                        <span>Iniciar Sesión</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
