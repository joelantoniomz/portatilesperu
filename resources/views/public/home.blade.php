@extends('layouts.public')

@section('title', 'Portátiles Perú - Laptops, Gaming, Ultrabooks y Workstations')

@section('content')
@php($heroProduct = $featuredProducts->first())

<div class="pb-0 home-page">
    <section class="home-hero relative isolate min-h-[510px] overflow-hidden bg-[#03120d]">
        @if($heroProduct)
            <img src="{{ $heroProduct->primary_image_url }}" alt="{{ $heroProduct->name }}" class="absolute inset-0 h-full w-full object-cover opacity-50">
        @endif
        <div class="absolute inset-0 bg-gradient-to-r from-[#020d09] via-[#041a12]/90 to-[#06160d]/25"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_58%_18%,rgba(34,197,94,0.45),transparent_23%)]"></div>

        <div class="relative mx-auto grid min-h-[510px] max-w-[1440px] items-center gap-8 px-5 py-10 sm:px-8 lg:grid-cols-[minmax(0,1fr)_280px] lg:px-10">
            <div class="max-w-2xl space-y-5">
                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-400/70 bg-black/30 px-4 py-1.5 text-[11px] font-bold text-white backdrop-blur">
                    <span class="text-emerald-400">ϟ</span> Tecnología que impulsa tus ideas
                </span>
                <h1 class="max-w-xl text-4xl font-black leading-[1.04] tracking-tight text-white sm:text-6xl lg:text-[58px]">
                    Encuentra la <span class="text-emerald-400">Laptop Ideal</span> para tu Potencial
                </h1>
                <p class="max-w-xl text-sm leading-6 text-slate-200 sm:text-[15px]">
                    Explora nuestra amplia gama de laptops, notebooks y ultrabooks de las mejores marcas. Rendimiento, calidad y tecnología en un solo lugar.
                </p>
                <div class="grid max-w-2xl grid-cols-2 gap-4 sm:grid-cols-4">
                    @foreach([['♢', 'Marcas', 'Autorizadas'], ['✿', 'Productos', '100% Originales'], ['♣', 'Envíos a', 'todo el Perú'], ['◉', 'Soporte', 'Especializado']] as [$icon, $title, $subtitle])
                    <div class="flex items-center gap-2 text-xs text-white">
                        <span class="text-2xl font-black text-emerald-400">{{ $icon }}</span>
                        <span><strong class="block">{{ $title }}</strong>{{ $subtitle }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="flex flex-wrap gap-3 pt-2">
                    <a href="{{ route('catalogo') }}" class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-7 py-3.5 text-xs font-black text-slate-950 shadow-xl shadow-emerald-500/25 transition hover:bg-emerald-400">
                        Explorar Catálogo Completo <span>→</span>
                    </a>
                    <a href="https://wa.me/51987654321?text=Hola%20Portatiles%20Peru,%20deseo%20asesoria%20para%20elegir%20una%20laptop" target="_blank" class="inline-flex items-center gap-2 rounded-full border border-white/80 bg-black/20 px-6 py-3.5 text-xs font-bold text-white transition hover:bg-white hover:text-black">
                        ◉ Consultar por WhatsApp
                    </a>
                </div>
            </div>

            @if($heroProduct)
            <article class="mx-auto w-full max-w-[280px] rounded-[1.5rem] bg-white p-5 shadow-2xl shadow-black/50">
                <div class="mb-4 flex items-center justify-between">
                    <span class="rounded-full bg-emerald-600 px-3 py-1.5 text-[10px] font-black tracking-wide text-white">● &nbsp;Laptop Recomendada</span>
                    <span class="text-xl text-slate-400">♡</span>
                </div>
                <div class="aspect-square overflow-hidden rounded-2xl bg-slate-100">
                    <img src="{{ $heroProduct->primary_image_url }}" alt="{{ $heroProduct->name }}" class="h-full w-full object-cover">
                </div>
                <div class="space-y-2 px-1 pt-4">
                    <p class="text-sm font-black leading-snug text-slate-950">{{ $heroProduct->name }}</p>
                    <p class="line-clamp-2 text-xs leading-5 text-slate-500">{{ $heroProduct->short_description ?: 'Equipo de alto rendimiento con garantía oficial en Perú.' }}</p>
                    <p class="pt-1 text-xl font-black text-emerald-600">{{ $heroProduct->formatted_price }}</p>
                    <a href="{{ route('catalogo.detalle', $heroProduct->slug) }}" class="mt-2 block rounded-full bg-emerald-600 px-4 py-3 text-center text-xs font-black text-white transition hover:bg-emerald-500">Ver producto &nbsp;→</a>
                    <a href="{{ route('catalogo.ficha-tecnica', $heroProduct->slug) }}" class="mt-2 block text-center text-[11px] font-bold text-emerald-700 hover:underline">↓ Descargar ficha técnica PDF</a>
                </div>
                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-slate-400"><span>‹</span><span class="text-emerald-500">●</span><span>● ●</span><span>›</span></div>
            </article>
            @endif
        </div>
    </section>

    <section class="mx-auto max-w-[1440px] px-5 py-6 sm:px-8 lg:px-10">
        <div class="mb-5 flex items-center justify-between gap-4">
            <h2 class="border-l-4 border-emerald-500 pl-3 text-xl font-black text-slate-950">Categorías principales</h2>
            <a href="{{ route('catalogo') }}" class="text-xs font-bold text-emerald-700 hover:underline">Ver todas las categorías →</a>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            @forelse($categories->take(6) as $category)
            <a href="{{ route('catalogo', ['categoria' => $category->slug]) }}" class="group relative min-h-36 overflow-hidden rounded-2xl border border-slate-100 bg-white p-3 text-center shadow-md shadow-slate-200/60 transition hover:-translate-y-1 hover:border-emerald-400 hover:shadow-lg">
                <div class="flex h-16 items-center justify-center overflow-hidden rounded-xl bg-emerald-50 text-3xl">
                    @if($featuredProducts->get($loop->index))
                        <img src="{{ $featuredProducts->get($loop->index)->primary_image_url }}" alt="{{ $category->name }}" class="h-full w-full object-contain mix-blend-multiply transition group-hover:scale-110">
                    @else
                        💻
                    @endif
                </div>
                <h3 class="mt-2 text-xs font-black text-slate-900">{{ $category->name }}</h3>
                <span class="mt-1 block text-[10px] font-bold text-emerald-700">Ver productos &nbsp;→</span>
            </a>
            @empty
            <p class="col-span-full rounded-2xl bg-slate-100 p-6 text-sm text-slate-500">Pronto tendremos categorías disponibles.</p>
            @endforelse
        </div>
    </section>

    <section class="relative overflow-hidden bg-[#041d15] py-6">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_0%_0%,rgba(22,163,74,0.28),transparent_26%)]"></div>
        <div class="relative mx-auto flex max-w-[1440px] flex-col items-center justify-between gap-5 px-5 text-center sm:flex-row sm:text-left lg:px-10">
            <div>
                <p class="text-sm font-black text-white">Marcas autorizadas</p>
                <p class="text-xs text-slate-400">Trabajamos con las mejores marcas del mercado.</p>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-x-9 gap-y-3 text-xl font-black uppercase tracking-tight text-white sm:text-2xl">
                @forelse($brands->take(7) as $brand)<span>{{ $brand->name }}</span>@empty<span>Lenovo</span><span>HP</span><span>ASUS</span><span>Acer</span>@endforelse
            </div>
            <a href="{{ route('catalogo') }}" class="rounded-full border border-emerald-400 px-4 py-2 text-xs font-bold text-white transition hover:bg-emerald-500 hover:text-black">Ver todas las marcas →</a>
        </div>
    </section>

    <section class="mx-auto max-w-[1440px] px-5 py-6 sm:px-8 lg:px-10">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([['🚚', 'Envíos rápidos', 'A todo el Perú'], ['✓', 'Compra segura', 'Por WhatsApp'], ['✹', 'Garantía oficial', 'En todos los productos'], ['◉', 'Asesoría personalizada', 'Te ayudamos a elegir']] as [$icon, $title, $description])
            <div class="flex items-center gap-4 rounded-2xl bg-white p-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-full bg-emerald-50 text-2xl text-emerald-600">{{ $icon }}</span>
                <span><strong class="block text-sm text-slate-900">{{ $title }}</strong><small class="text-xs text-slate-500">{{ $description }}</small></span>
            </div>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-12 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-end justify-between gap-4">
            <div><span class="text-xs font-black uppercase tracking-widest text-emerald-700">Selección especial</span><h2 class="mt-1 text-2xl font-black text-slate-950">Laptops Destacadas</h2></div>
            <a href="{{ route('catalogo') }}" class="text-xs font-bold text-emerald-700 hover:underline">Ver catálogo completo →</a>
        </div>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @forelse($featuredProducts as $product)
            <article class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-1 hover:border-emerald-400 hover:shadow-lg">
                <a href="{{ route('catalogo.detalle', $product->slug) }}" class="block aspect-video overflow-hidden rounded-2xl bg-slate-100"><img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-300 hover:scale-105"></a>
                <div class="mt-4 flex items-center justify-between text-[10px]"><span class="font-mono text-slate-400">{{ $product->sku }}</span><span class="font-bold text-emerald-700">{{ $product->brand?->name }}</span></div>
                <h3 class="mt-2 line-clamp-2 min-h-10 text-sm font-black leading-tight text-slate-900"><a href="{{ route('catalogo.detalle', $product->slug) }}">{{ $product->name }}</a></h3>
                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3"><span class="text-lg font-black text-emerald-600">{{ $product->formatted_price }}</span><a href="{{ route('catalogo.detalle', $product->slug) }}" class="rounded-xl bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700">Ver →</a></div>
            </article>
            @empty
            <p class="col-span-full rounded-2xl bg-slate-100 p-8 text-center text-sm text-slate-500">No hay productos destacados actualmente.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
