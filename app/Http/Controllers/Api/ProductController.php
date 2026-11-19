<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    /**
     * Menampilkan semua produk.
     */
    public function index(): JsonResponse
    {
        $rows = Product::query()
            ->latest('id')
            ->get();

        return response()->json([
            'data' => $rows->map(
                fn ($row) => $this->data($row)
            )->values(),
        ]);
    }

    /**
     * Menambahkan produk baru.
     */
    public function store(Request $request): JsonResponse
    {
        $product = Product::create(
            $this->validated($request, false)
        );

        return response()->json([
            'data' => $this->data($product),
        ], 201)->header(
            'Location',
            route('products.show', $product, false)
        );
    }

    /**
     * Menampilkan detail produk.
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'data' => $this->data($product),
        ]);
    }

    /**
     * Mengubah data produk.
     */
    public function update(
        Request $request,
        Product $product
    ): JsonResponse {
        $product->update(
            $this->validated(
                $request,
                $request->isMethod('PATCH')
            )
        );

        $product->refresh();

        return response()->json([
            'data' => $this->data($product),
        ]);
    }

    /**
     * Menghapus produk.
     */
    public function destroy(Product $product): Response
    {
        $product->delete();

        return response()->noContent();
    }

    /**
     * Validasi data produk.
     */
    private function validated(
        Request $request,
        bool $partial
    ): array {
        $presence = $partial
            ? 'sometimes|required'
            : 'required';

        return $request->validate([
            'name' => $presence . '|string|max:100',
            'price' => $presence . '|integer|min:0|max:2147483647',
            'stock' => $presence . '|integer|min:0|max:2147483647',
        ]);
    }

    /**
     * Format data produk untuk JSON.
     */
    private function data(Product $product): array
    {
        return [
            'id' => (int) $product->id,
            'name' => $product->name,
            'price' => (int) $product->price,
            'stock' => (int) $product->stock,
        ];
    }
}
