<?php

namespace App\Http\Controllers;

use App\Application\UseCases\Product\CreateProductUseCase;
use App\Application\UseCases\Product\DeleteProductUseCase;
use App\Application\UseCases\Product\ListProductsUseCase;
use App\Application\UseCases\Product\ShowProductUseCase;
use App\Application\UseCases\Product\UpdateProductUseCase;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class ProductController extends Controller
{
    public function index(
        Request              $request,
        ListProductsUseCase  $useCase,
    ): JsonResponse {
        $result = $useCase->execute(
            page:    (int) $request->query('page', 1),
            perPage: (int) $request->query('per_page', 15),
            search:  $request->query('search'),
        );

        $data = array_map(
            fn($entity) => (new ProductResource($entity))->resolve(),
            $result['data']
        );

        return response()->json([
            'data' => $data,
            'meta' => $result['meta'],
        ]);
    }

    public function show(
        string             $uuid,
        ShowProductUseCase $useCase,
    ): ProductResource {
        return new ProductResource($useCase->execute($uuid));
    }

    public function store(
        StoreProductRequest   $request,
        CreateProductUseCase  $useCase,
    ): JsonResponse {
        $product = $useCase->execute(
            name:         $request->validated('name'),
            price:        (float) $request->validated('price'),
            categoryUuid: $request->validated('category_uuid'),
            slug:         $request->validated('slug'),
            description:  $request->validated('description'),
            tagUuids:     $request->validated('tag_uuids', []),
        );

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    public function update(
        UpdateProductRequest $request,
        string               $uuid,
        UpdateProductUseCase $useCase,
    ): ProductResource {
        $data = $request->validated();

        $product = $useCase->execute(
            uuid:         $uuid,
            name:         $data['name'] ?? null,
            price:        isset($data['price']) ? (float) $data['price'] : null,
            categoryUuid: $data['category_uuid'] ?? null,
            slug:         $data['slug'] ?? null,
            description:  $data['description'] ?? null,
            tagUuids:     $data['tag_uuids'] ?? null,
        );

        return new ProductResource($product);
    }

    public function destroy(
        string               $uuid,
        DeleteProductUseCase $useCase,
    ): JsonResponse {
        $useCase->execute($uuid);

        return response()->json(null, 204);
    }
}
