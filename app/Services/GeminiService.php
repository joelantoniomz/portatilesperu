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

        $candidateModels = [
            config('services.gemini.model', 'gemini-2.5-flash'),
            'gemini-2.5-flash',
            'gemini-2.0-flash',
            'gemini-1.5-flash-latest',
            'gemini-1.5-pro-latest',
        ];

        // Eliminar duplicados manteniendo orden
        $candidateModels = array_unique(array_filter($candidateModels));

        $lastError = 'Error desconocido en Gemini';

        foreach ($candidateModels as $model) {
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            try {
                $response = Http::withoutVerifying()
                    ->timeout(25)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
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

                // Si fue error de cuota o clave inválida (400, 403), salir al fallback
                break;

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
Eres un experto catalogador técnico de laptops comerciales y gaming para Portátiles Perú (tienda especializada en Lima, Perú).
Analiza el siguiente texto de entrada sobre un producto (puede ser el nombre comercial, un código de modelo o una lista de especificaciones técnicas desordenadas):

"""
{$input}
"""

Debes generar una ficha completa y profesional en formato JSON estricto con las siguientes claves:
1. "name": Nombre comercial completo y bien estructurado (Ej: "Lenovo Legion Pro 5 16IRX9 Intel Core i7-14700HX 32GB RAM 1TB SSD RTX 4060 16\" WQXGA 240Hz").
2. "sku": Código SKU conciso, limpio y en mayúsculas (Ej: "LEN-LEG-PRO5-4060" o "ASUS-ROG-G16-4070").
3. "suggested_category": Elige la categoría más adecuada exclusivamente de esta lista existente: [{$categoriesList}].
4. "suggested_brand": Elige la marca más adecuada exclusivamente de esta lista existente: [{$brandsList}].
5. "short_description": Resumen comercial atractivo y conciso de 1 a 2 oraciones para compartir por WhatsApp y catálogos rápidos.
6. "description": Presentación comercial detallada en 2 o 3 párrafos, destacando pantalla, capacidad para gaming o trabajo pesado, sistema de refrigeración y calidad de construcción en soles peruanos.
7. "technical_specs": Objeto con los siguientes campos técnicos precisos:
   - "procesador": Modelo de CPU, núcleos y frecuencia turbo máxima (Ej: "Intel Core i7-14700HX (20 núcleos, hasta 5.5GHz)").
   - "ram": Capacidad, tipo y velocidad (Ej: "32 GB DDR5 5600MHz").
   - "almacenamiento": Capacidad, factor de forma y bus (Ej: "1 TB SSD M.2 PCIe 4.0 NVMe").
   - "pantalla": Tamaño, resolución, panel, tasa de refresco y brillo (Ej: "16\" WQXGA (2560x1600) IPS 240Hz 500 nits, 100% sRGB").
   - "grafica": Modelo de GPU y VRAM dedicada (Ej: "NVIDIA GeForce RTX 4060 8GB GDDR6 (140W TGP)").
   - "sistema_operativo": "Windows 11 Home" o "Windows 11 Pro".
   - "conectividad": Puertos principales (Ej: "Wi-Fi 6E, Bluetooth 5.3, Thunderbolt 4 / USB4, HDMI 2.1, RJ-45 Gigabit").
   - "garantia": Texto de garantía (Ej: "12 meses de garantía oficial en Perú").
8. "weight": Peso numérico aproximado en kg (Ej: 2.35).
9. "dimensions": Dimensiones aproximadas en formato "Largo x Ancho x Grosor cm" (Ej: "36.3 x 26.0 x 2.2 cm").

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
            $name = 'Laptop Corporativa / Gamer de Alto Rendimiento';
        }

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

        // Detección de procesador
        $cpu = 'Intel Core i7 / AMD Ryzen 7';
        if (preg_match('/(i[3579][\-\s]?\d{4,5}[A-Z]{0,2}|Ryzen[\s\d][\-\s]?\d{4}[A-Z]{0,2}|Apple\sM[1234][\s\w]*|Ultra\s\d[\s\w]*)/i', $input, $m)) {
            $cpu = trim($m[0]);
        }

        // Detección de RAM
        $ram = '16 GB DDR5';
        if (preg_match('/(\d{1,3}\s?GB(\s?DDR[45])?(\s?\d{4}MHz)?)/i', $input, $m)) {
            $ram = trim($m[0]);
        }

        // Detección de Almacenamiento
        $storage = '1 TB SSD M.2 NVMe';
        if (preg_match('/(\d{1,2}\s?TB|\d{3,4}\s?GB)\s?(SSD|NVMe|PCIe)?/i', $input, $m)) {
            $storage = trim($m[0]);
        }

        // Detección de Gráfica
        $gpu = 'NVIDIA GeForce RTX';
        if (preg_match('/(RTX\s?\d{4}[A-Z]{0,3}|GeForce\sRTX\s?\d{4}|Radeon\s[\w\d]+|Intel\sIris\sXe|Apple\sGPU)/i', $input, $m)) {
            $gpu = 'NVIDIA GeForce '.trim($m[0]);
        }

        // Detección de Pantalla
        $display = '15.6" Full HD (1920x1080) IPS 144Hz';
        if (preg_match('/(\d{2}(\.\d)?["\']?)\s?(WQXGA|QHD|FHD|OLED|IPS|4K|240Hz|165Hz|144Hz)?/i', $input, $m)) {
            $display = trim($m[0]);
        }

        // Detección de categoría sugerida
        $suggestedCategoryId = null;
        if (stripos($input, 'gamer') !== false || stripos($input, 'rtx') !== false || stripos($input, 'legion') !== false || stripos($input, 'rog') !== false) {
            $suggestedCategoryId = array_search('Laptops Gamer', $categories) ?: null;
        } elseif (stripos($input, 'thinkpad') !== false || stripos($input, 'corporativa') !== false || stripos($input, 'latitude') !== false) {
            $suggestedCategoryId = array_search('Laptops Corporativas y Trabajo', $categories) ?: null;
        }

        $cleanSku = $this->ensureUniqueSku(
            ($detectedBrandName ? substr($detectedBrandName, 0, 3) : 'LAP').'-'.strtoupper(Str::slug(substr($name, 0, 18)))
        );

        return [
            'name' => $name,
            'sku' => $cleanSku,
            'suggested_category' => $suggestedCategoryId ? ($categories[$suggestedCategoryId] ?? '') : '',
            'category_id' => $suggestedCategoryId,
            'suggested_brand' => $detectedBrandName,
            'brand_id' => $detectedBrandId,
            'short_description' => "Potente equipo {$name} optimizado para alta productividad y desempeño en Perú.",
            'description' => "La nueva {$name} integra componentes de vanguardia para brindar una experiencia de usuario fluida, diseño ergonómico y máxima fiabilidad tanto para profesionales como para usuarios avanzados.",
            'technical_specs' => [
                'procesador' => $cpu,
                'ram' => $ram,
                'almacenamiento' => $storage,
                'pantalla' => $display,
                'grafica' => $gpu,
                'sistema_operativo' => 'Windows 11 Home',
                'conectividad' => 'Wi-Fi 6, Bluetooth 5.2, USB-C, HDMI',
                'garantia' => '12 meses de garantía oficial en Perú',
            ],
            'weight' => 2.10,
            'dimensions' => '35.9 x 25.1 x 2.1 cm',
            'source' => 'local_fallback',
            'warning' => $warningNotice,
        ];
    }
}
