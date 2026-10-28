<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiquetas QR y Códigos de Barra - {{ $selectedCategory ? $selectedCategory->name . ' - ' : '' }}Portátiles Perú</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background-color: #f1f5f9;
            color: #0f172a;
            padding: 20px;
        }

        .controls {
            max-width: 1000px;
            margin: 0 auto 24px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: white;
            padding: 16px 24px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }

        .controls h1 {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
        }

        .controls p {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: #0c3823;
            color: white;
            box-shadow: 0 4px 12px rgba(12, 56, 35, 0.3);
        }

        .btn-primary:hover {
            background: #047857;
            transform: scale(1.02);
        }

        .grid-labels {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Etiqueta Adhesiva lista para pegar en cajas */
        .label-item {
            background: white;
            border: 1.5px dashed #94a3b8;
            border-radius: 14px;
            padding: 14px;
            text-align: center;
            page-break-inside: avoid;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .label-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1.5px solid #0c3823;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }

        .label-brand {
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 1px;
            color: #0c3823;
            text-transform: uppercase;
        }

        .label-category {
            font-size: 9px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }

        .label-name {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.25;
            margin-bottom: 6px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 30px;
        }

        .qr-wrapper {
            background: white;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 6px auto;
        }

        .qr-img {
            width: 130px;
            height: 130px;
            display: block;
        }

        .label-sku {
            font-family: monospace;
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        .label-specs {
            font-size: 10px;
            color: #64748b;
            line-height: 1.3;
            margin: 4px 0;
            max-height: 26px;
            overflow: hidden;
        }

        .label-footer {
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .label-price {
            font-size: 16px;
            font-weight: 900;
            color: #0c3823;
        }

        .label-barcode-text {
            font-size: 9px;
            font-family: monospace;
            color: #64748b;
            text-align: right;
        }

        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }

            .controls {
                display: none;
            }

            .grid-labels {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 5mm;
                max-width: 100%;
            }

            .label-item {
                border: 1px solid #000;
                border-radius: 4px;
                padding: 3mm;
                box-shadow: none;
                page-break-inside: avoid;
            }

            .qr-img {
                width: 25mm;
                height: 25mm;
            }
        }
    </style>
</head>
<body>

    <div class="controls">
        <div>
            <h1>
                @if($selectedCategory)
                    Etiquetas QR para Categoría: {{ $selectedCategory->name }} ({{ count($products) }} laptops)
                @else
                    Etiquetas QR y Códigos de Barras en Lote ({{ count($products) }} productos)
                @endif
            </h1>
            <p>Formato de impresión optimizado para pegar en las cajas de los productos con SKU, precio y especificaciones.</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn btn-primary">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir Lote Completo
            </button>
            <button onclick="window.close()" class="btn" style="background: #e2e8f0; color: #334155;">
                Cerrar
            </button>
        </div>
    </div>

    @if(count($products) === 0)
    <div style="max-width: 600px; margin: 40px auto; text-align: center; background: white; padding: 40px; border-radius: 20px;">
        <p style="color: #64748b; font-size: 14px; font-weight: 600;">No se encontraron productos activos para los filtros seleccionados.</p>
    </div>
    @else
    <div class="grid-labels">
        @foreach($products as $product)
        <div class="label-item">
            <div class="label-header">
                <span class="label-brand">{{ $product->brand?->name ?? 'PORTÁTILES PERÚ' }}</span>
                <span class="label-category">{{ $product->category?->name ?? 'LAPTOPS' }}</span>
            </div>

            <div class="label-name" title="{{ $product->name }}">{{ $product->name }}</div>

            <div class="qr-wrapper">
                <img class="qr-img" src="{{ route('admin.qr.svg', $product) }}" alt="QR {{ $product->sku }}">
            </div>

            <div class="label-sku">{{ $product->sku }}</div>

            @php
                $specsList = [];
                if (!empty($product->technical_specs) && is_array($product->technical_specs)) {
                    foreach (['procesador', 'ram', 'almacenamiento', 'grafica'] as $specKey) {
                        if (!empty($product->technical_specs[$specKey])) {
                            $specsList[] = Str::limit($product->technical_specs[$specKey], 35);
                        }
                    }
                    if (empty($specsList)) {
                        foreach (array_slice($product->technical_specs, 0, 3) as $k => $v) {
                            if (is_string($v)) {
                                $specsList[] = Str::limit($v, 35);
                            }
                        }
                    }
                }
            @endphp
            <div class="label-specs">
                @if(!empty($specsList))
                    {{ implode(' • ', array_slice($specsList, 0, 3)) }}
                @else
                    Laptop Alta Gama • Escanea para consultar stock
                @endif
            </div>

            <div class="label-footer">
                <div class="label-price">{{ $product->formatted_price }}</div>
                <div class="label-barcode-text">
                    @if($product->barcode)
                        BARCODE: {{ $product->barcode }}<br>
                    @endif
                    {{ date('d/m/Y') }}
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</body>
</html>
