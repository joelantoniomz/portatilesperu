<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GeminiService
{
    /**
     * Obtiene la clave de API configurada en base de datos, config o entorno.
     */
    public function getApiKey(): ?string
    {
        $key = Setting::getValue('gemini_api_key');
        if (! empty($key)) {
            return trim($key);
        }

        $configKey = config('services.gemini.api_key');
        if (! empty($configKey)) {
            return trim($configKey);
        }

        $envKey = env('GEMINI_API_KEY');
        if (! empty($envKey)) {
            return trim($envKey);
        }

        return null;
    }

    /**
     * Guarda la clave de API en la tabla de configuración.
     */
    public function saveApiKey(string $apiKey): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'gemini_api_key'],
            ['value' => trim($apiKey)]
        );
    }

    /**
     * Comprueba si la API key está configurada.
     */
    public function hasApiKey(): bool
    {
        return ! empty($this->getApiKey());
    }

    /**
     * Genera datos comerciales, taxonómicos y ficha técnica mediante Gemini AI.
     *
     * @param  string  $input  Texto con el nombre comercial, especificaciones o prompt
     * @param  array  $options  Opciones como 'sections' (identification, descriptions, specs)
     * @return array Datos estructurados del producto
     */
    public function generateProductData(string $input, array $options = []): array
    {
        $apiKey = $this->getApiKey();

        if (empty($apiKey)) {
            // Si no hay API Key, generamos mediante un extractor heurístico local inteligente
            return $this->fallbackLocalExtraction($input, 'No se ha configurado la API Key de Gemini. Se aplicó autocompletado inteligente local.');
        }

        $categories = Category::where('is_active', true)->pluck('name', 'id')->toArray();
        $brands = Brand::where('is_active', true)->pluck('name', 'id')->toArray();

        $prompt = $this->buildPrompt($input, $categories, $brands);

        // 1. Intentar descubrir dinámicamente los modelos disponibles para esta API Key
        $availableModels = [];
        try {
            $listResponse = Http::withoutVerifying()
                ->timeout(10)
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                ])
                ->get("https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}");

            if ($listResponse->successful()) {
                $modelsData = $listResponse->json('models') ?? [];
                foreach ($modelsData as $m) {
                    $methods = $m['supportedGenerationMethods'] ?? [];
                    if (in_array('generateContent', $methods, true)) {
                        // Limpiar 'models/' prefix
                        $modelName = str_replace('models/', '', $m['name'] ?? '');
                        if (! empty($modelName)) {
                            $availableModels[] = $modelName;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignorar y continuar con candidatos estáticos
        }

        // 2. Armar lista priorizada de modelos
        $preferredConfig = config('services.gemini.model');
        $staticCandidates = [
            $preferredConfig,
            'gemini-2.5-flash',
            'gemini-2.0-flash',
            'gemini-2.0-flash-001',
            'gemini-2.5-flash-lite',
            'gemini-1.5-flash',
            'gemini-1.5-flash-8b',
            'gemini-1.5-pro',
        ];

        // Unir los descubiertos de la API con los candidatos estáticos
        $candidateModels = array_unique(array_filter(array_merge($availableModels, $staticCandidates)));

        $lastError = 'Error desconocido en Gemini';

        foreach ($candidateModels as $model) {
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            try {
                $response = Http::withoutVerifying()
                    ->timeout(25)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'x-goog-api-key' => $apiKey,
                    ])
                    ->post($endpoint, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt],
                                ],
                            ],
                        ],
                        'generationConfig' => [
                            'responseMimeType' => 'application/json',
                            'temperature' => 0.2,
                        ],
                    ]);

                if ($response->successful()) {
                    $body = $response->json();
                    $rawText = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    $cleanJson = $this->cleanJsonString($rawText);
                    $decoded = json_decode($cleanJson, true);

                    if (is_array($decoded)) {
                        $matchedCategoryId = $this->matchEntityId($decoded['suggested_category'] ?? '', $categories);
                        $matchedBrandId = $this->matchEntityId($decoded['suggested_brand'] ?? '', $brands);

                        $decoded['category_id'] = $matchedCategoryId;
                        $decoded['brand_id'] = $matchedBrandId;
                        $decoded['source'] = 'gemini';
                        $decoded['model_used'] = $model;

                        // Asegurar SKU único si fue sugerido
                        if (! empty($decoded['sku'])) {
                            $decoded['sku'] = $this->ensureUniqueSku($decoded['sku']);
                        }

                        return $decoded;
                    }
                }

                $lastError = $response->json('error.message') ?? "Error HTTP {$response->status()} con modelo {$model}";

                // Si el modelo específico no fue encontrado (404), continuar con el siguiente candidato
                if ($response->status() === 404 || str_contains(strtolower($lastError), 'not found')) {
                    continue;
                }

                // Si fue error de cuota o clave inválida (400, 403), continuar si hay más candidatos o salir
                if ($response->status() === 429) {
                    continue; // Intentar otro modelo en caso de cuota por modelo
                }

            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
            }
        }

        return $this->fallbackLocalExtraction($input, $lastError);
    }

    /**
     * Construye el prompt detallado para Gemini solicitando formato JSON estricto.
     */
    private function buildPrompt(string $input, array $categories, array $brands): string
    {
        $categoriesList = implode(', ', $categories);
        $brandsList = implode(', ', $brands);

        return <<<PROMPT
Eres un experto catalogador técnico de tecnología y laptops para Portátiles Perú (tienda especializada en Lima, Perú).
Analiza el siguiente texto de entrada sobre un producto (puede ser una laptop, auriculares/audífonos, monitor, accesorios u otro equipo de tecnología):

"""
{$input}
"""

Debes generar una ficha en formato JSON estricto con las siguientes claves:
1. "name": Nombre comercial completo y bien estructurado (Ej: "Audífonos Inalámbricos JBL Tune 520BT Morado On-Ear con Bluetooth 5.3" o "Lenovo Legion Pro 5 16IRX9 Intel Core i7 32GB").
2. "sku": Código SKU conciso, limpio y en mayúsculas (Ej: "JBL-T520BT-PUR" o "LEN-LEG-PRO5").
3. "suggested_category": Elige la categoría más adecuada exclusivamente de esta lista existente: [{$categoriesList}].
4. "suggested_brand": Elige la marca más adecuada exclusivamente de esta lista existente: [{$brandsList}].
5. "short_description": Resumen comercial conciso de 1 a 2 oraciones para compartir por WhatsApp y catálogo.
6. "description": Presentación comercial detallada en 2 o 3 párrafos destacando características, calidad de sonido o potencia, batería y uso recomendado.
7. "technical_specs": Objeto con especificaciones técnicas reales del producto analizado.
   IMPORTANTE: Si el producto NO es una computadora/laptop (por ejemplo si son audífonos, mouse, mochila, etc.):
   - Deja en blanco ("") los campos que NO apliquen como "procesador", "ram", "almacenamiento", "pantalla", "grafica", "sistema_operativo". ¡NO inventes procesador ni RAM si son audífonos!
   - Rellena con precisión los campos que SÍ apliquen, como "conectividad" (ej: "Bluetooth 5.3, USB-C, Conexión multipunto"), "garantia" (ej: "12 meses de garantía oficial").
   - Si es una laptop, rellena todos los campos técnicos habituales (procesador, ram, almacenamiento, pantalla, grafica, sistema_operativo, conectividad, garantia).
8. "weight": Peso numérico aproximado en kg (ej: 0.16 para audífonos, 2.3 para laptop).
9. "dimensions": Dimensiones aproximadas en formato "Largo x Ancho x Grosor cm" si se conocen, o cadena vacía "".

Responde ÚNICAMENTE el objeto JSON sin texto antes ni después.
PROMPT;
    }

    /**
     * Limpia delimitadores markdown del JSON devuelto si los hubiere.
     */
    private function cleanJsonString(string $raw): string
    {
        $raw = trim($raw);
        if (str_starts_with($raw, '```json')) {
            $raw = substr($raw, 7);
        } elseif (str_starts_with($raw, '```')) {
            $raw = substr($raw, 3);
        }
        if (str_ends_with($raw, '```')) {
            $raw = substr($raw, 0, -3);
        }

        return trim($raw);
    }

    /**
     * Busca el ID más cercano de categoría o marca.
     */
    private function matchEntityId(string $name, array $entities): ?int
    {
        if (empty($name)) {
            return null;
        }

        $lower = Str::lower(trim($name));
        foreach ($entities as $id => $entityName) {
            $entityLower = Str::lower($entityName);
            if ($entityLower === $lower || str_contains($entityLower, $lower) || str_contains($lower, $entityLower)) {
                return (int) $id;
            }
        }

        return null;
    }

    /**
     * Asegura que el SKU sugerido no exista en la base de datos.
     */
    private function ensureUniqueSku(string $sku): string
    {
        $baseSku = Str::upper(preg_replace('/[^A-Z0-9\-]/', '', str_replace(' ', '-', trim($sku))));
        if (empty($baseSku)) {
            $baseSku = 'LAPTOP-'.strtoupper(Str::random(6));
        }

        $candidate = $baseSku;
        $counter = 1;

        while (Product::where('sku', $candidate)->exists()) {
            $candidate = "{$baseSku}-{$counter}";
            $counter++;
        }

        return $candidate;
    }

    /**
     * Extracción heurística local cuando no hay API Key o hay problemas de conexión.
     */
    public function fallbackLocalExtraction(string $input, ?string $warningNotice = null): array
    {
        $categories = Category::where('is_active', true)->pluck('name', 'id')->toArray();
        $brands = Brand::where('is_active', true)->pluck('name', 'id')->toArray();

        $name = trim($input);
        if (empty($name)) {
            $name = 'Producto de Tecnología y Cómputo';
        }

        // Detección de si es accesorio o producto que NO es laptop (audífonos, mouse, etc.)
        $isAudioOrAccessory = (bool) preg_match('/(aud[ií]fono|auricular|headphone|earphone|headset|parlante|speaker|mouse|teclado|funda|mochila|cable|cargador|adaptador)/i', $input);

        // Detección de marca
        $detectedBrandId = null;
        $detectedBrandName = '';
        foreach ($brands as $id => $brandName) {
            if (stripos($input, $brandName) !== false) {
                $detectedBrandId = $id;
                $detectedBrandName = $brandName;
                break;
            }
        }

        // Detección de procesador (solo si no es accesorio)
        $cpu = '';
        if (! $isAudioOrAccessory) {
            if (preg_match('/(i[3579][\-\s]?\d{4,5}[A-Z]{0,2}|Ryzen[\s\d][\-\s]?\d{4}[A-Z]{0,2}|Apple\sM[1234][\s\w]*|Ultra\s\d[\s\w]*)/i', $input, $m)) {
                $cpu = trim($m[0]);
            }
        }

        // Detección de RAM (solo si no es accesorio)
        $ram = '';
        if (! $isAudioOrAccessory) {
            if (preg_match('/(\d{1,3}\s?GB(\s?DDR[45])?(\s?\d{4}MHz)?)/i', $input, $m)) {
                $ram = trim($m[0]);
            }
        }

        // Detección de Almacenamiento (solo si no es accesorio)
        $storage = '';
        if (! $isAudioOrAccessory) {
            if (preg_match('/(\d{1,2}\s?TB|\d{3,4}\s?GB)\s?(SSD|NVMe|PCIe)?/i', $input, $m)) {
                $storage = trim($m[0]);
            }
        }

        // Detección de Gráfica (solo si no es accesorio)
        $gpu = '';
        if (! $isAudioOrAccessory) {
            if (preg_match('/(RTX\s?\d{4}[A-Z]{0,3}|GeForce\sRTX\s?\d{4}|Radeon\s[\w\d]+|Intel\sIris\sXe|Apple\sGPU)/i', $input, $m)) {
                $gpu = 'NVIDIA GeForce '.trim($m[0]);
            }
        }

        // Detección de Pantalla (solo si no es accesorio)
        $display = '';
        if (! $isAudioOrAccessory) {
            if (preg_match('/(\d{2}(\.\d)?["\']?)\s?(WQXGA|QHD|FHD|OLED|IPS|4K|240Hz|165Hz|144Hz)?/i', $input, $m)) {
                $display = trim($m[0]);
            }
        }

        // Detección de Sistema Operativo
        $os = $isAudioOrAccessory ? '' : 'Windows 11 Home';

        // Detección de Conectividad
        $connectivity = 'Bluetooth, USB-C';
        if (preg_match('/(Bluetooth[\s\d\.]*|Wi-Fi[\s\d\w\.]*|USB-C|Jack\s3\.5mm|Inal[aá]mbrico)/i', $input, $m)) {
            $connectivity = trim($m[0]);
        }

        // Detección de categoría sugerida
        $suggestedCategoryId = null;
        if ($isAudioOrAccessory) {
            foreach ($categories as $catId => $catName) {
                if (stripos($catName, 'accesorio') !== false || stripos($catName, 'audio') !== false || stripos($catName, 'perif') !== false) {
                    $suggestedCategoryId = $catId;
                    break;
                }
            }
        } elseif (stripos($input, 'gamer') !== false || stripos($input, 'rtx') !== false || stripos($input, 'legion') !== false || stripos($input, 'rog') !== false) {
            $suggestedCategoryId = array_search('Laptops Gamer', $categories) ?: null;
        } elseif (stripos($input, 'thinkpad') !== false || stripos($input, 'corporativa') !== false || stripos($input, 'latitude') !== false) {
            $suggestedCategoryId = array_search('Laptops Corporativas y Trabajo', $categories) ?: null;
        }

        $cleanSku = $this->ensureUniqueSku(
            ($detectedBrandName ? substr($detectedBrandName, 0, 3) : 'PROD').'-'.strtoupper(Str::slug(substr($name, 0, 18)))
        );

        return [
            'name' => $name,
            'sku' => $cleanSku,
            'suggested_category' => $suggestedCategoryId ? ($categories[$suggestedCategoryId] ?? '') : '',
            'category_id' => $suggestedCategoryId,
            'suggested_brand' => $detectedBrandName,
            'brand_id' => $detectedBrandId,
            'short_description' => "Excelente equipo {$name} de alto rendimiento y garantía en Perú.",
            'description' => "El modelo {$name} destaca por sus especificaciones equilibradas, gran autonomía y excelente acabado para satisfacer las máximas exigencias de los usuarios en Perú.",
            'technical_specs' => [
                'procesador' => $cpu,
                'ram' => $ram,
                'almacenamiento' => $storage,
                'pantalla' => $display,
                'grafica' => $gpu,
                'sistema_operativo' => $os,
                'conectividad' => $connectivity,
                'garantia' => '12 meses de garantía oficial en Perú',
            ],
            'weight' => $isAudioOrAccessory ? 0.22 : 2.10,
            'dimensions' => '',
            'source' => 'local_fallback',
            'warning' => $warningNotice,
        ];
    }
}
