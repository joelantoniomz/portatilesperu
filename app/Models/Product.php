<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'slug',
        'category_id',
        'subcategory_id',
        'brand_id',
        'product_model_id',
        'short_description',
        'description',
        'price',
        'currency',
        'barcode',
        'qr_code',
        'status',
        'is_featured',
        'is_new',
        'min_stock_alert',
        'weight',
        'dimensions',
        'technical_specs',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'min_stock_alert' => 'integer',
        'weight' => 'decimal:2',
        'technical_specs' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name.'-'.$product->sku);
            }
            if (empty($product->qr_code)) {
                $product->qr_code = 'PORT-'.strtoupper($product->sku);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_model_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * URL de la imagen principal o primera imagen disponible.
     */
    public function getPrimaryImageUrlAttribute(): string
    {
        $primary = $this->images->firstWhere('is_primary', true) ?? $this->images->first();

        if ($primary) {
            return $primary->url;
        }

        // SVG Placeholder por defecto limpio y estilizado
        return "data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300' viewBox='0 0 400 300' fill='none'%3E%3Crect width='400' height='300' fill='%230B3322'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='sans-serif' font-size='16' font-weight='bold' fill='%2334D399'%3EPortátiles Perú%3C/text%3E%3C/svg%3E";
    }

    /**
     * Ruta o URL optimizada para renderizado en DomPDF (convierte siempre a JPEG Base64 nativo).
     */
    public function getPdfImageUrlAttribute(): ?string
    {
        $primary = $this->images->firstWhere('is_primary', true) ?? $this->images->first();

        if (! $primary || empty($primary->file_path)) {
            return self::generatePlaceholderBase64($this->brand?->name);
        }

        // Si es archivo local en public o storage
        $localPaths = [
            public_path('storage/'.$primary->file_path),
            storage_path('app/public/'.$primary->file_path),
        ];

        foreach ($localPaths as $localPath) {
            if (file_exists($localPath) && is_file($localPath)) {
                $ext = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg'], true)) {
                    $c = @file_get_contents($localPath);
                    if ($c !== false) {
                        return 'data:image/jpeg;base64,'.base64_encode($c);
                    }
                } elseif ($ext === 'png') {
                    $c = @file_get_contents($localPath);
                    if ($c !== false) {
                        return 'data:image/png;base64,'.base64_encode($c);
                    }
                } elseif (function_exists('imagecreatefromstring')) {
                    $c = @file_get_contents($localPath);
                    if ($c !== false) {
                        $gd = @imagecreatefromstring($c);
                        if ($gd !== false) {
                            ob_start();
                            imagejpeg($gd, null, 82);
                            $jpegBinary = ob_get_clean();
                            imagedestroy($gd);

                            return 'data:image/jpeg;base64,'.base64_encode($jpegBinary);
                        }
                    }
                }
            }
        }

        // Si es URL remota (http o https), convertir siempre a JPEG Base64 compatible con DomPDF
        if (str_starts_with($primary->file_path, 'http://') || str_starts_with($primary->file_path, 'https://')) {
            $cacheKey = 'pdf_img_jpeg_b64_'.md5($primary->file_path);
            $base64 = cache()->remember($cacheKey, 86400, function () use ($primary) {
                try {
                    $response = Http::withoutVerifying()
                        ->timeout(4)
                        ->withHeaders([
                            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                            'Accept' => 'image/jpeg,image/png,image/*;q=0.8',
                        ])
                        ->get($primary->file_path);

                    if ($response->successful() && function_exists('imagecreatefromstring')) {
                        $gd = @imagecreatefromstring($response->body());
                        if ($gd !== false) {
                            ob_start();
                            imagejpeg($gd, null, 82);
                            $jpegBinary = ob_get_clean();
                            imagedestroy($gd);

                            return 'data:image/jpeg;base64,'.base64_encode($jpegBinary);
                        }
                    }
                } catch (\Throwable $e) {
                    // Silenciar y continuar al placeholder
                }

                return null;
            });

            if ($base64) {
                return $base64;
            }
        }

        return self::generatePlaceholderBase64($this->brand?->name);
    }

    /**
     * Genera un gráfico PNG nítido y ligero con el logotipo/marca para DomPDF.
     */
    public static function generatePlaceholderBase64(?string $brand = null): string
    {
        $brandText = strtoupper(trim($brand ?: 'PORTÁTILES PERÚ'));
        $cacheKey = 'pdf_placeholder_b64_'.md5($brandText);

        return cache()->rememberForever($cacheKey, function () use ($brandText) {
            if (! function_exists('imagecreatetruecolor')) {
                return "data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='200'%3E%3Crect width='300' height='200' fill='%23f8fafc'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='sans-serif' font-size='12' fill='%23059669'%3EPORTÁTILES PERÚ%3C/text%3E%3C/svg%3E";
            }

            $w = 300;
            $h = 180;
            $im = imagecreatetruecolor($w, $h);
            $bg = imagecolorallocate($im, 248, 250, 252);
            imagefilledrectangle($im, 0, 0, $w, $h, $bg);
            $border = imagecolorallocate($im, 226, 232, 240);
            imagerectangle($im, 0, 0, $w - 1, $h - 1, $border);

            // Silueta de laptop estilizada
            $screenBorder = imagecolorallocate($im, 148, 163, 184);
            $screenFill = imagecolorallocate($im, 6, 78, 59);
            imagefilledrectangle($im, 70, 35, 230, 125, $screenBorder);
            imagefilledrectangle($im, 74, 39, 226, 121, $screenFill);

            $base = imagecolorallocate($im, 30, 41, 59);
            imagefilledrectangle($im, 50, 126, 250, 136, $base);

            // Marca en pantalla
            $textColor = imagecolorallocate($im, 52, 211, 153);
            $dispText = substr($brandText, 0, 18);
            $x = max(10, (int) (($w - (strlen($dispText) * 7.5)) / 2));
            imagestring($im, 3, $x, 75, $dispText, $textColor);

            ob_start();
            imagepng($im);
            $png = ob_get_clean();
            imagedestroy($im);

            return 'data:image/png;base64,'.base64_encode($png);
        });
    }

    /**
     * Precio formateado con moneda.
     */
    public function getFormattedPriceAttribute(): string
    {
        $symbol = $this->currency === 'USD' ? '$' : 'S/';

        return "{$symbol} ".number_format($this->price, 2);
    }

    /**
     * Scope: Solo productos publicados para catálogo público.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope: Productos destacados.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: Búsqueda flexible por texto.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%")
                ->orWhere('barcode', 'like', "%{$term}%")
                ->orWhere('short_description', 'like', "%{$term}%");
        });
    }

    /**
     * Scope: Filtros administrativos y de catálogo.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        return $query;
    }

    public function inventoryBalances(): HasMany
    {
        return $this->hasMany(InventoryBalance::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockReservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }

    public function getTotalPhysicalStockAttribute(): int
    {
        return (int) $this->inventoryBalances->sum('qty_physical');
    }

    public function getTotalAvailableStockAttribute(): int
    {
        return (int) $this->inventoryBalances->sum('qty_available');
    }
}
