<?php

namespace App\Services;

use Picqer\Barcode\BarcodeGeneratorHTML;
use Picqer\Barcode\BarcodeGeneratorPNG;
use App\Models\ProductVariant;

class BarcodeService
{
    private BarcodeGeneratorHTML $htmlGenerator;
    private BarcodeGeneratorPNG $pngGenerator;

    public function __construct()
    {
        $this->htmlGenerator = new BarcodeGeneratorHTML();
        $this->pngGenerator = new BarcodeGeneratorPNG();
    }

    public function generateUniqueCode(): string
    {
        do {
            $code = str_pad(mt_rand(100000000, 999999999), 9, '0', STR_PAD_LEFT);
        } while (ProductVariant::where('barcode', $code)->exists());

        return $code;
    }

    public function assignBarcode(ProductVariant $variant): string
    {
        if ($variant->barcode) {
            return $variant->barcode;
        }

        $code = $this->generateUniqueCode();
        $variant->updateQuietly(['barcode' => $code]);

        return $code;
    }

    public function getHtml(string $code, int $height = 50): string
    {
        return $this->htmlGenerator->getBarcode($code, BarcodeGeneratorHTML::TYPE_CODE_128, 1, $height);
    }

    public function getPng(string $code, int $height = 50): string
    {
        return $this->pngGenerator->getBarcode($code, BarcodeGeneratorPNG::TYPE_CODE_128, 1, $height);
    }

    public function getPngBase64(string $code, int $height = 50): string
    {
        $png = $this->getPng($code, $height);
        return base64_encode($png);
    }

    public function searchByBarcode(string $barcode): ?ProductVariant
    {
        return ProductVariant::where('barcode', $barcode)
            ->with('product')
            ->first();
    }
}
