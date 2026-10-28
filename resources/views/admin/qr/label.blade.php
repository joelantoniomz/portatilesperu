<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiqueta QR - {{ $product->sku }} - Portátiles Perú</title>
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
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .controls {
            margin-bottom: 24px;
            display: flex;
            gap: 12px;
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
            transition: all 0.15s ease;
        }

        .btn-primary {
            background: #059669;
            color: white;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }

        .btn-primary:hover {
            background: #047857;
            transform: scale(1.02);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #334155;
        }

        .btn-secondary:hover {
            background: #cbd5e1;
        }

        /* Etiqueta Adhesiva Estándar 70x50mm aprox */
        .label-container {
            width: 320px;
            background: white;
            border: 2px dashed #94a3b8;
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .label-header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }

        .label-brand {
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 2px;
            color: #059669;
            text-transform: uppercase;
        }

        .label-title {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.25;
            margin-top: 2px;
            max-height: 34px;
            overflow: hidden;
        }

        .qr-wrapper {
            background: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 10px auto;
        }

        .qr-wrapper img {
            width: 170px;
            height: 170px;
            display: block;
        }

        .label-sku {
            font-family: monospace;
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 1px;
            margin-top: 4px;
        }

        .label-specs {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }

        .label-footer {
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .label-price {
            font-size: 18px;
            font-weight: 900;
            color: #059669;
        }

        .label-barcode-text {
            font-size: 9px;
            color: #64748b;
            font-family: monospace;
            text-align: right;
        }

        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
                min-height: auto;
            }

            .controls {
                display: none;
            }

            .label-container {
                border: 1px solid #000;
                box-shadow: none;
                border-radius: 0;
                page-break-inside: avoid;
                margin: 0;
                width: 70mm;
                height: 50mm;
                padding: 3mm;
            }

            .qr-wrapper img {
                width: 25mm;
                height: 25mm;
            }
        }
    </style>
</head>
<body>

    <div class="controls">
        <button onclick="window.print()" class="btn btn-primary">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Imprimir Etiqueta
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            Cerrar
        </button>
    </div>

    <div class="label-container">
        <div class="label-header">
            <div class="label-brand">PORTÁTILES PERÚ</div>
            <div class="label-title">{{ $product->name }}</div>
        </div>

        <div class="qr-wrapper">
            <img src="{{ route('admin.qr.svg', $product) }}" alt="QR {{ $product->sku }}">
        </div>

        <div class="label-sku">{{ $product->sku }}</div>
        <div class="label-specs">
            {{ $product->brand?->name ?? 'Laptop' }} &bull; Escanee para consultar stock
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

</body>
</html>
