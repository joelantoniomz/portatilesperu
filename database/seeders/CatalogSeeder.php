<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductModel;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Categorías y Subcategorías
        $categoriesData = [
            [
                'name' => 'Laptops Gamer',
                'slug' => 'laptops-gamer',
                'description' => 'Portátiles de alto rendimiento para gaming competitivo, streaming y esports con tarjetas gráficas dedicadas.',
                'subcategories' => ['Gama Alta (RTX 4080/4090)', 'Gama Media Competitiva (RTX 4060/4070)', 'Entrada Esports (RTX 4050/3050)'],
            ],
            [
                'name' => 'Laptops Corporativas y Trabajo',
                'slug' => 'laptops-corporativas-trabajo',
                'description' => 'Equipos diseñados para productividad empresarial, seguridad de datos, durabilidad militar y alta autonomía.',
                'subcategories' => ['Workstation Móvil', 'Ultraligera Ejecutiva', 'Productividad de Oficina'],
            ],
            [
                'name' => 'Laptops Ultraligeras y Estudiantes',
                'slug' => 'laptops-ultraligeras-estudiantes',
                'description' => 'Portabilidad extrema, batería de larga duración y pantallas nítidas para clases y viajes.',
                'subcategories' => ['Económica Estudiantil', 'Premium Liviana'],
            ],
            [
                'name' => 'Laptops para Creadores y Diseño',
                'slug' => 'laptops-creadores-diseno',
                'description' => 'Pantallas OLED con precisión de color 100% DCI-P3 para edición de video 4K, arquitectura y renderizado 3D.',
                'subcategories' => ['Edición Audiovisual', 'Arquitectura y Render 3D'],
            ],
        ];

        $categories = [];
        $subcategories = [];

        foreach ($categoriesData as $index => $cat) {
            $category = Category::firstOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                    'sort_order' => $index,
                    'is_active' => true,
                ]
            );
            $categories[$cat['slug']] = $category;

            foreach ($cat['subcategories'] as $subIndex => $subName) {
                $subSlug = Str::slug($subName.'-'.$category->slug);
                $sub = Subcategory::firstOrCreate(
                    ['slug' => $subSlug],
                    [
                        'category_id' => $category->id,
                        'name' => $subName,
                        'sort_order' => $subIndex,
                        'is_active' => true,
                    ]
                );
                $subcategories[$subSlug] = $sub;
            }
        }

        // 2. Marcas y Modelos
        $brandsData = [
            'Lenovo' => ['Legion Pro', 'ThinkPad E Series', 'ThinkPad X1', 'Yoga Slim', 'IdeaPad Gaming'],
            'ASUS' => ['ROG Zephyrus', 'ROG Strix', 'TUF Gaming', 'ZenBook Pro'],
            'HP' => ['Victus', 'Omen Transcend', 'ProBook', 'Spectre x360'],
            'Dell' => ['XPS 13', 'XPS 15', 'Latitude 5000', 'Alienware m16'],
            'Apple' => ['MacBook Pro 14', 'MacBook Pro 16', 'MacBook Air M2', 'MacBook Air M3'],
            'MSI' => ['Katana 15', 'Stealth 16 Studio', 'Raider GE78'],
        ];

        $brands = [];
        $models = [];

        foreach ($brandsData as $brandName => $modelNames) {
            $brand = Brand::firstOrCreate(
                ['slug' => Str::slug($brandName)],
                [
                    'name' => $brandName,
                    'is_active' => true,
                ]
            );
            $brands[$brandName] = $brand;

            foreach ($modelNames as $mName) {
                $mSlug = Str::slug($mName);
                $model = ProductModel::firstOrCreate(
                    ['brand_id' => $brand->id, 'slug' => $mSlug],
                    ['name' => $mName, 'is_active' => true]
                );
                $models["{$brandName}_{$mName}"] = $model;
            }
        }

        // 3. Productos Reales con Fichas Técnicas Estructuradas
        $productsData = [
            [
                'sku' => 'LEN-LEG-PRO5-4060',
                'name' => 'Lenovo Legion Pro 5 16IRX9 Intel Core i7 32GB SSD 1TB RTX 4060',
                'brand' => 'Lenovo',
                'model' => 'Legion Pro',
                'category_slug' => 'laptops-gamer',
                'price' => 6499.00,
                'status' => 'published',
                'is_featured' => true,
                'is_new' => true,
                'barcode' => '7701234567890',
                'short_description' => 'Portátil gamer de élite con chasis térmico Legion Coldfront 5.0, pantalla 16" 240Hz y tarjeta gráfica NVIDIA RTX 4060 8GB GDDR6.',
                'description' => 'La Lenovo Legion Pro 5 está diseñada para jugadores competitivos y creadores exigentes. Su procesador Intel Core i7 de 14.ª generación y la GPU NVIDIA GeForce RTX 4060 proporcionan un rendimiento sobresaliente con DLSS 3.5. Incluye pantalla PureSight Gaming de 16 pulgadas con certificación X-Rite Pantone y teclado Legion TrueStrike con retroiluminación RGB de 4 zonas.',
                'weight' => 2.50,
                'dimensions' => '36.3 x 26.0 x 2.2 cm',
                'min_stock_alert' => 3,
                'specs' => [
                    'procesador' => 'Intel Core i7-14700HX (20 núcleos, hasta 5.5 GHz)',
                    'ram' => '32 GB DDR5 5600MHz (2x 16GB ampliable a 64GB)',
                    'almacenamiento' => '1 TB SSD M.2 2280 PCIe 4.0 NVMe',
                    'pantalla' => '16" WQXGA (2560x1600) IPS 240Hz, 500 nits, 100% sRGB, DisplayHDR 400',
                    'grafica' => 'NVIDIA GeForce RTX 4060 8GB GDDR6 (TGP 140W)',
                    'sistema_operativo' => 'Windows 11 Home 64 bits en español',
                    'conectividad' => 'Wi-Fi 6E (802.11ax), Bluetooth 5.2, RJ-45 Gigabit Ethernet',
                    'bateria' => '80 Wh con tecnología Super Rapid Charge Pro',
                    'garantia' => '12 meses de garantía oficial en Perú',
                ],
                'image_svg_color' => '#10B981',
            ],
            [
                'sku' => 'ASUS-ROG-G16-4070',
                'name' => 'ASUS ROG Zephyrus G16 OLED Intel Core Ultra 9 32GB SSD 1TB RTX 4070',
                'brand' => 'ASUS',
                'model' => 'ROG Zephyrus',
                'category_slug' => 'laptops-creadores-diseno',
                'price' => 8899.00,
                'status' => 'published',
                'is_featured' => true,
                'is_new' => true,
                'barcode' => '7701234567891',
                'short_description' => 'Chasis ultradelgado de aluminio CNC, pantalla ROG Nebula OLED 2.5K 240Hz y aceleración IA con NPU dedicada.',
                'description' => 'El ROG Zephyrus G16 redefine la potencia portátil con un grosor de solo 1.49 cm. La pantalla OLED ofrece negros perfectos, 0.2 ms de tiempo de respuesta y 100% DCI-P3 para edición profesional.',
                'weight' => 1.85,
                'dimensions' => '35.4 x 24.6 x 1.49 cm',
                'min_stock_alert' => 2,
                'specs' => [
                    'procesador' => 'Intel Core Ultra 9 185H con Intel AI Boost NPU',
                    'ram' => '32 GB LPDDR5X 7467MHz Dual Channel',
                    'almacenamiento' => '1 TB SSD PCIe 4.0 NVMe',
                    'pantalla' => '16" 2.5K (2560x1600) ROG Nebula OLED 240Hz 0.2ms G-Sync',
                    'grafica' => 'NVIDIA GeForce RTX 4070 8GB GDDR6 (TGP 105W)',
                    'sistema_operativo' => 'Windows 11 Pro',
                    'conectividad' => 'Thunderbolt 4, HDMI 2.1, Wi-Fi 7, Bluetooth 5.4',
                    'garantia' => '12 meses de garantía directa ASUS Perú',
                ],
                'image_svg_color' => '#059669',
            ],
            [
                'sku' => 'LEN-THINK-E14G5',
                'name' => 'Lenovo ThinkPad E14 Gen 5 AMD Ryzen 7 16GB SSD 512GB 14" FHD',
                'brand' => 'Lenovo',
                'model' => 'ThinkPad E Series',
                'category_slug' => 'laptops-corporativas-trabajo',
                'price' => 3199.00,
                'status' => 'published',
                'is_featured' => false,
                'is_new' => false,
                'barcode' => '7701234567892',
                'short_description' => 'Portátil empresarial probado bajo estándares militares MIL-STD-810H con lector de huellas y teclado resistente a derrames.',
                'description' => 'Confiable, segura y veloz para jornadas de trabajo corporativas continuas. Con módulo TPM 2.0 y cámara web FHD con obturador de privacidad.',
                'weight' => 1.41,
                'dimensions' => '31.3 x 21.9 x 1.79 cm',
                'min_stock_alert' => 5,
                'specs' => [
                    'procesador' => 'AMD Ryzen 7 7730U (8 núcleos, 16 hilos, hasta 4.5 GHz)',
                    'ram' => '16 GB DDR4 3200MHz',
                    'almacenamiento' => '512 GB SSD M.2 PCIe NVMe',
                    'pantalla' => '14" Full HD (1920x1080) IPS Antirreflejo 300 nits',
                    'grafica' => 'AMD Radeon Graphics integrada',
                    'sistema_operativo' => 'Windows 11 Pro 64 bits en español',
                    'seguridad' => 'Chip TPM 2.0 discreto, lector de huellas dactilares',
                    'garantia' => '12 meses de garantía corporativa Lenovo Premier',
                ],
                'image_svg_color' => '#0F766E',
            ],
            [
                'sku' => 'APP-MBP-14-M3P',
                'name' => 'Apple MacBook Pro 14" Chip M3 Pro 18GB 512GB SSD Gris Espacial',
                'brand' => 'Apple',
                'model' => 'MacBook Pro 14',
                'category_slug' => 'laptops-creadores-diseno',
                'price' => 8499.00,
                'status' => 'published',
                'is_featured' => true,
                'is_new' => true,
                'barcode' => '7701234567893',
                'short_description' => 'Arquitectura Apple Silicon con chip M3 Pro, pantalla Liquid Retina XDR de 1000 nits continuos y hasta 18 horas de batería.',
                'description' => 'Ideal para desarrolladores de software, ingenieros de audio y diseñadores profesionales que requieren máxima eficiencia energética sin caídas de velocidad.',
                'weight' => 1.61,
                'dimensions' => '31.26 x 22.12 x 1.55 cm',
                'min_stock_alert' => 2,
                'specs' => [
                    'procesador' => 'Chip Apple M3 Pro con CPU de 11 núcleos y GPU de 14 núcleos',
                    'ram' => '18 GB memoria unificada',
                    'almacenamiento' => '512 GB SSD ultrarrápido',
                    'pantalla' => '14.2" Liquid Retina XDR (3024x1964) ProMotion 120Hz',
                    'grafica' => 'GPU de 14 núcleos con trazado de rayos por hardware',
                    'sistema_operativo' => 'macOS Sonoma',
                    'conectividad' => '3x Thunderbolt 4, HDMI, ranura SDXC, MagSafe 3',
                    'garantia' => '12 meses de garantía oficial Apple en Perú',
                ],
                'image_svg_color' => '#1E293B',
            ],
            [
                'sku' => 'ASUS-TUF-A15-4050',
                'name' => 'ASUS TUF Gaming A15 AMD Ryzen 5 16GB SSD 512GB RTX 4050 144Hz',
                'brand' => 'ASUS',
                'model' => 'TUF Gaming',
                'category_slug' => 'laptops-gamer',
                'price' => 3799.00,
                'status' => 'published',
                'is_featured' => false,
                'is_new' => false,
                'barcode' => '7701234567894',
                'short_description' => 'Portátil gamer de alta resistencia con certificación MIL-STD-810H y tarjeta gráfica RTX 4050 con MUX Switch.',
                'description' => 'Excelente balance costo-rendimiento para jugadores de shooters y MOBA en 1080p con altas tasas de refresco.',
                'weight' => 2.20,
                'dimensions' => '35.4 x 25.1 x 2.24 cm',
                'min_stock_alert' => 4,
                'specs' => [
                    'procesador' => 'AMD Ryzen 5 7535HS (6 núcleos, 12 hilos, hasta 4.55 GHz)',
                    'ram' => '16 GB DDR5 4800MHz',
                    'almacenamiento' => '512 GB SSD PCIe 4.0 NVMe',
                    'pantalla' => '15.6" FHD (1920x1080) IPS 144Hz Adaptive-Sync',
                    'grafica' => 'NVIDIA GeForce RTX 4050 6GB GDDR6 (TGP 140W)',
                    'sistema_operativo' => 'Windows 11 Home',
                    'garantia' => '12 meses ASUS Perú',
                ],
                'image_svg_color' => '#B45309',
            ],
            [
                'sku' => 'DELL-XPS-13P-9320',
                'name' => 'Dell XPS 13 Plus 9320 Intel Core i7 16GB SSD 512GB Touch',
                'brand' => 'Dell',
                'model' => 'XPS 13',
                'category_slug' => 'laptops-ultraligeras-estudiantes',
                'price' => 5899.00,
                'status' => 'published',
                'is_featured' => false,
                'is_new' => false,
                'barcode' => '7701234567895',
                'short_description' => 'Touchpad invisible háptico en vidrio, teclado de borde a borde y pantalla táctil InfinityEdge.',
                'description' => 'La laptop ultraportátil premium más icónica de Dell con acabados en aluminio mecanizado CNC.',
                'weight' => 1.26,
                'dimensions' => '29.5 x 19.9 x 1.52 cm',
                'min_stock_alert' => 3,
                'specs' => [
                    'procesador' => 'Intel Core i7-1360P (12 núcleos, 16 hilos, hasta 5.0 GHz)',
                    'ram' => '16 GB LPDDR5 6000MHz',
                    'almacenamiento' => '512 GB SSD PCIe NVMe',
                    'pantalla' => '13.4" 3.5K (3456x2160) OLED Táctil InfinityEdge',
                    'grafica' => 'Intel Iris Xe Graphics',
                    'sistema_operativo' => 'Windows 11 Pro',
                    'garantia' => '12 meses con servicio en sitio Dell',
                ],
                'image_svg_color' => '#2563EB',
            ],
            [
                'sku' => 'HP-VIC-15-3050',
                'name' => 'HP Victus 15-fa1093dx Intel Core i5 16GB SSD 512GB RTX 3050',
                'brand' => 'HP',
                'model' => 'Victus',
                'category_slug' => 'laptops-gamer',
                'price' => 3299.00,
                'status' => 'draft', // Estado borrador para probar filtros
                'is_featured' => false,
                'is_new' => false,
                'barcode' => '7701234567896',
                'short_description' => 'Equipo en borrador pendiente de revisión de precio final.',
                'description' => 'Laptop de entrada para juegos y tareas universitarias exigentes.',
                'weight' => 2.29,
                'dimensions' => '35.7 x 25.5 x 2.35 cm',
                'min_stock_alert' => 5,
                'specs' => [
                    'procesador' => 'Intel Core i5-13420H',
                    'ram' => '16 GB DDR4',
                    'almacenamiento' => '512 GB SSD NVMe',
                    'grafica' => 'NVIDIA GeForce RTX 3050 6GB',
                    'pantalla' => '15.6" FHD 144Hz',
                ],
                'image_svg_color' => '#64748B',
            ],
            [
                'sku' => 'DELL-LAT-5440-ARC',
                'name' => 'Dell Latitude 5440 Intel Core i5 16GB SSD 256GB (Modelo Archivado)',
                'brand' => 'Dell',
                'model' => 'Latitude 5000',
                'category_slug' => 'laptops-corporativas-trabajo',
                'price' => 2999.00,
                'status' => 'archived', // Archivado: NO debe mostrarse en catálogo público
                'is_featured' => false,
                'is_new' => false,
                'barcode' => '7701234567897',
                'short_description' => 'Modelo de generación anterior archivado por fin de ciclo comercial.',
                'description' => 'Producto archivado para trazabilidad histórica de inventario.',
                'weight' => 1.36,
                'dimensions' => '32.1 x 21.2 x 1.9 cm',
                'min_stock_alert' => 0,
                'specs' => [
                    'procesador' => 'Intel Core i5-1335U',
                    'ram' => '16 GB DDR4',
                    'almacenamiento' => '256 GB SSD NVMe',
                ],
                'image_svg_color' => '#475569',
            ],
        ];

        foreach ($productsData as $pData) {
            $brand = $brands[$pData['brand']];
            $model = $models["{$pData['brand']}_{$pData['model']}"] ?? null;
            $category = $categories[$pData['category_slug']];

            $product = Product::firstOrCreate(
                ['sku' => $pData['sku']],
                [
                    'name' => $pData['name'],
                    'slug' => Str::slug($pData['name'].'-'.$pData['sku']),
                    'category_id' => $category->id,
                    'subcategory_id' => $category->subcategories->first()?->id,
                    'brand_id' => $brand->id,
                    'product_model_id' => $model?->id,
                    'short_description' => $pData['short_description'],
                    'description' => $pData['description'],
                    'price' => $pData['price'],
                    'currency' => 'PEN',
                    'barcode' => $pData['barcode'],
                    'status' => $pData['status'],
                    'is_featured' => $pData['is_featured'],
                    'is_new' => $pData['is_new'],
                    'min_stock_alert' => $pData['min_stock_alert'],
                    'weight' => $pData['weight'],
                    'dimensions' => $pData['dimensions'],
                    'technical_specs' => $pData['specs'],
                ]
            );

            // Generar 2 imágenes de galería representativas para cada producto si no tiene
            if ($product->images()->count() === 0) {
                // Imagen 1: Vista Frontal
                ProductImage::create([
                    'product_id' => $product->id,
                    'file_path' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=800&auto=format&fit=crop&q=80',
                    'is_primary' => true,
                    'sort_order' => 0,
                    'alt_text' => "{$product->name} - Vista Frontal",
                ]);

                // Imagen 2: Vista Lateral / Teclado
                ProductImage::create([
                    'product_id' => $product->id,
                    'file_path' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=800&auto=format&fit=crop&q=80',
                    'is_primary' => false,
                    'sort_order' => 1,
                    'alt_text' => "{$product->name} - Teclado y Pantalla",
                ]);
            }
        }
    }
}
