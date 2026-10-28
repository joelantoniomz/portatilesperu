<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 28px; }
        body { color: #172033; font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        .header { background: #062217; border-bottom: 5px solid #22c55e; color: #fff; padding: 20px 22px; }
        .brand { color: #5eea9a; font-size: 11px; font-weight: bold; letter-spacing: 1px; }
        h1 { font-size: 22px; line-height: 1.2; margin: 8px 0 0; }
        .subtitle { color: #d6e5de; margin: 7px 0 0; }
        .layout { margin-top: 20px; width: 100%; }
        .image { background: #f1f5f9; height: 245px; text-align: center; vertical-align: middle; width: 48%; }
        .image img { max-height: 230px; max-width: 100%; }
        .summary { padding-left: 22px; vertical-align: top; }
        .price { color: #059669; font-size: 24px; font-weight: bold; margin: 14px 0; }
        .label { color: #64748b; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .value { color: #172033; font-size: 11px; font-weight: bold; margin: 4px 0 12px; }
        .section { border-bottom: 2px solid #10b981; color: #063d2a; font-size: 14px; font-weight: bold; margin: 24px 0 9px; padding-bottom: 6px; }
        .specs { border-collapse: collapse; width: 100%; }
        .specs td { border-bottom: 1px solid #e2e8f0; padding: 8px 6px; }
        .specs td:first-child { color: #64748b; font-weight: bold; text-transform: capitalize; width: 34%; }
        .footer { bottom: 20px; color: #64748b; font-size: 8px; position: fixed; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">PORTÁTILES PERÚ · E.I.R.L. | MOYO TECH</div>
        <h1>Ficha técnica del producto</h1>
        <p class="subtitle">Información comercial y especificaciones del equipo seleccionado.</p>
    </div>

    <table class="layout"><tr>
        <td class="image"><img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}"></td>
        <td class="summary">
            <div class="label">Producto</div><div class="value">{{ $product->name }}</div>
            <div class="label">Marca y categoría</div><div class="value">{{ $product->brand?->name }} · {{ $product->category?->name }}</div>
            <div class="label">Código SKU</div><div class="value">{{ $product->sku }}</div>
            <div class="label">Precio contado</div><div class="price">{{ $product->formatted_price }}</div>
            @if($product->short_description)<div class="label">Descripción corta</div><div>{{ $product->short_description }}</div>@endif
        </td>
    </tr></table>

    <div class="section">Especificaciones técnicas</div>
    <table class="specs">
        @forelse($product->technical_specs ?? [] as $key => $value)
        <tr><td>{{ str_replace('_', ' ', $key) }}</td><td>{{ is_array($value) ? implode(', ', $value) : $value }}</td></tr>
        @empty
        <tr><td colspan="2">Consulte con un asesor para conocer las especificaciones completas.</td></tr>
        @endforelse
        @if($product->weight)<tr><td>Peso</td><td>{{ $product->weight }} kg</td></tr>@endif
        @if($product->dimensions)<tr><td>Dimensiones</td><td>{{ $product->dimensions }}</td></tr>@endif
    </table>

    <div class="footer">Portátiles Perú · Ficha generada el {{ now()->format('d/m/Y') }} · Compra y asesoría por WhatsApp.</div>
</body>
</html>
