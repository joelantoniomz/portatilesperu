<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $options['cover_title'] ?? 'Catálogo de Productos' }} - {{ $options['company_name'] ?? 'Portátiles Perú' }}</title>
    <style>
        @page { margin: 15px 18px 25px 18px; }
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif; color: #1e293b; font-size: 8px; line-height: 1.25; }
        h1 { margin: 0 0 4px 0; font-size: 20px; font-weight: bold; }
        
        /* Portada / Cabecera */
        .cover { padding: 16px 20px; margin-bottom: 12px; color: #ffffff; background: #064e3b; border-bottom: 5px solid #10b981; border-radius: 6px; }
        .cover-title { font-size: 12px; font-weight: bold; margin: 2px 0 6px 0; color: #a7f3d0; }
        .cover-info { font-size: 7.5px; color: #d1fae5; line-height: 1.35; }
        
        /* Grid de Productos */
        .grid { width: 100%; border-collapse: separate; border-spacing: 6px; table-layout: fixed; }
        .card { vertical-align: top; width: {{ round(100 / ($options['columns'] ?? 4), 2) }}%; border: 1px solid #e2e8f0; padding: 7px; background: #ffffff; border-radius: 6px; page-break-inside: avoid; }
        
        /* Imagen del Producto */
        .img-container { width: 100%; height: 75px; text-align: center; margin-bottom: 5px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 3px; overflow: hidden; }
        .product-img { max-width: 100%; max-height: 69px; display: inline-block; vertical-align: middle; }
        .img-placeholder { height: 69px; line-height: 69px; color: #94a3b8; font-size: 8px; font-weight: bold; text-align: center; }
        
        /* Tipografía de Producto */
        .brand-cat { font-size: 6.5px; font-weight: bold; text-transform: uppercase; color: #047857; margin-bottom: 2px; letter-spacing: 0.3px; }
        .name { font-size: 8.5px; font-weight: bold; color: #0f172a; line-height: 1.2; margin-bottom: 2px; min-height: 21px; word-wrap: break-word; }
        .sku { font-size: 7px; color: #64748b; margin-bottom: 2px; font-weight: normal; }
        .price { font-size: 11px; font-weight: bold; color: #047857; margin: 2px 0; }
        .stock { font-size: 6.8px; color: #0284c7; font-weight: bold; margin-bottom: 2px; }
        .description { font-size: 6.8px; color: #475569; line-height: 1.2; margin-bottom: 3px; }
        
        /* Ficha Técnica */
        .specs { margin-top: 3px; border-top: 1px solid #f1f5f9; padding-top: 3px; font-size: 6.5px; color: #334155; line-height: 1.25; }
        .specs div { margin-bottom: 1px; }
        
        /* Temas */
        .corporativo .cover { background: #1e3a8a; border-bottom-color: #3b82f6; }
        .corporativo .brand-cat, .corporativo .price { color: #1d4ed8; }
        .premium .cover { background: #0f172a; border-bottom-color: #eab308; }
        .premium .brand-cat { color: #b45309; }
        .premium .price { color: #ca8a04; }
        .moderno .cover { background: #312e81; border-bottom-color: #8b5cf6; }
        .moderno .brand-cat, .moderno .price { color: #6d28d9; }
        .editorial .cover { background: #3f3f46; border-bottom-color: #71717a; }
        .mayorista .cover { background: #14532d; border-bottom-color: #16a34a; }
        .tecnico .cover { background: #0f172a; border-bottom-color: #0ea5e9; }
        .tecnico .brand-cat, .tecnico .price { color: #0284c7; }
        
        /* Footer fijo */
        .footer { position: fixed; bottom: -12px; left: 0; right: 0; color: #94a3b8; font-size: 7px; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 3px; }
    </style>
</head>
<body class="{{ $theme ?? 'minimalista' }}">

    <!-- Encabezado / Portada -->
    <div class="cover">
        <h1>{{ $options['company_name'] }}</h1>
        <div class="cover-title">{{ $options['cover_title'] }}</div>
        <div class="cover-info">
            <strong>Líneas:</strong> {{ $options['category_names'] ?: 'Todas las categorías' }}<br>
            @if(!empty($options['company_phone']))
                <strong>WhatsApp / Contacto:</strong> {{ $options['company_phone'] }} &bull;
            @endif
            @if(!empty($options['company_email']))
                <strong>Email:</strong> {{ $options['company_email'] }} &bull;
            @endif
            <strong>Medios de Pago:</strong> {{ $options['payment_methods'] }}
        </div>
    </div>

    <!-- Catálogo de Laptops -->
    <table class="grid">
        @foreach($products->chunk($options['columns'] ?? 4) as $row)
        <tr>
            @foreach($row as $product)
            <td class="card">
                <!-- Imagen del Producto -->
                <div class="img-container">
                    @if($product->pdf_image_url)
                        <img src="{{ $product->pdf_image_url }}" alt="" class="product-img">
                    @else
                        <div class="img-placeholder">PORTÁTILES PERÚ</div>
                    @endif
                </div>

                <div class="brand-cat">{{ $product->brand?->name }} &bull; {{ $product->category?->name }}</div>
                <div class="name">{{ Str::limit($product->name, 48) }}</div>

                @if($options['show_sku'])
                    <div class="sku">SKU: {{ $product->sku }}</div>
                @endif

                @if($options['show_prices'])
                    <div class="price">{{ $product->formatted_price }}</div>
                @endif

                @if($options['show_stock'])
                    <div class="stock">&bull; Stock: {{ $product->total_available_stock }} disp.</div>
                @endif

                @if($product->short_description)
                    <div class="description">{{ Str::limit($product->short_description, 60) }}</div>
                @endif

                @if($options['show_specs'] && !empty($product->technical_specs) && is_array($product->technical_specs))
                <div class="specs">
                    @php $specCount = 0; @endphp
                    @foreach($product->technical_specs as $key => $value)
                        @if($specCount < 3 && is_string($value))
                            <div><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ Str::limit($value, 28) }}</div>
                            @php $specCount++; @endphp
                        @endif
                    @endforeach
                </div>
                @endif
            </td>
            @endforeach
            
            {{-- Rellenar celdas vacías si la última fila tiene menos productos que columnas --}}
            @for($i = count($row); $i < ($options['columns'] ?? 4); $i++)
                <td style="width: {{ round(100 / ($options['columns'] ?? 4), 2) }}%; border: 0; background: transparent;"></td>
            @endfor
        </tr>
        @endforeach
    </table>

    <div class="footer">
        {{ $options['company_name'] }} &bull; Catálogo emitido el {{ now()->format('d/m/Y \a \l\a\s H:i') }} &bull; Precios y stock sujetos a disponibilidad.
    </div>

</body>
</html>
