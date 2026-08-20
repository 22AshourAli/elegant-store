<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Services\BarcodeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BarcodeController extends Controller
{
    public function show(ProductVariant $variant, BarcodeService $barcodeService)
    {
        $variant->load('product');

        return view('admin.products.barcode', [
            'variant' => $variant,
            'barcodeHtml' => $barcodeService->getHtml($variant->barcode, 60),
        ]);
    }

    public function print(ProductVariant $variant, BarcodeService $barcodeService)
    {
        $variant->load('product');

        return view('admin.products.barcode-print', [
            'variant' => $variant,
            'barcodeHtml' => $barcodeService->getHtml($variant->barcode, 40),
        ]);
    }

    public function download(ProductVariant $variant, BarcodeService $barcodeService): Response
    {
        $png = $barcodeService->getPng($variant->barcode, 80);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => "attachment; filename=\"barcode-{$variant->barcode}.png\"",
        ]);
    }

    public function search(Request $request, BarcodeService $barcodeService)
    {
        $request->validate(['q' => 'required|string|min:3']);

        $variant = $barcodeService->searchByBarcode($request->q);

        if (!$variant) {
            return back()->withErrors(['q' => 'لم يتم العثور على باركود بهذا الرقم.']);
        }

        return redirect()->route('admin.barcode.show', $variant);
    }

    public function batchPrint(Request $request, BarcodeService $barcodeService)
    {
        $variants = ProductVariant::whereIn('id', $request->input('variant_ids', []))
            ->with('product')
            ->get();

        $barcodes = $variants->map(fn($v) => [
            'variant' => $v,
            'html' => $barcodeService->getHtml($v->barcode, 30),
        ]);

        return view('admin.products.barcode-batch', compact('barcodes'));
    }
}
