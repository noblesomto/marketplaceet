<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

/**
 * Category UI Configuration Controller
 *
 * Provides database-driven UI configuration for category-based
 * show/hide logic on Post Ad and Edit Ad forms.
 *
 * SAFE FOR PRODUCTION:
 * - Read-only operations
 * - Heavy caching (24 hours)
 * - Fallback defaults included
 * - No breaking changes
 */
class CategoryUIController extends Controller
{
    /**
     * Get UI configuration for all categories and subcategories
     *
     * @OA\Get(
     *     path="/api/ui-config/all",
     *     summary="Get all category and subcategory UI configurations",
     *     tags={"UI Configuration"},
     *     @OA\Response(
     *         response=200,
     *         description="UI configuration data",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     *
     * Cached for 24 hours for performance
     */
    public function getUIConfig(): JsonResponse
    {
        $config = Cache::remember('category_ui_config_v1', 86400, function () {
            return [
                'categories' => $this->getCategoriesConfig(),
                'subcategories' => $this->getSubcategoriesConfig(),
                'defaults' => $this->getDefaultConfigs(),
                // Explicit, ready-to-use list so clients don't have to resolve
                // each category's hide/show arrays (with default-config fallback)
                // themselves just to answer "does this category take a quantity?".
                'quantity_eligible_category_ids' => $this->getQuantityEligibleCategoryIds(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $config,
            'cached' => true,
            'version' => '1.0'
        ]);
    }

    /**
     * Get UI config for a specific category
     *
     * @param int $categoryId
     * @return JsonResponse
     */
    public function getCategoryConfig($categoryId): JsonResponse
    {
        $category = Category::find($categoryId);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $config = $category->ui_config
            ? json_decode($category->ui_config, true)
            : $this->getDefaultCategoryConfig();

        return response()->json([
            'success' => true,
            'data' => $config
        ]);
    }

    /**
     * Get UI config for a specific subcategory
     *
     * @param int $subcategoryId
     * @return JsonResponse
     */
    public function getSubcategoryConfig($subcategoryId): JsonResponse
    {
        $subcategory = SubCategory::find($subcategoryId);

        if (!$subcategory) {
            return response()->json([
                'success' => false,
                'message' => 'Subcategory not found'
            ], 404);
        }

        $config = $subcategory->ui_config
            ? json_decode($subcategory->ui_config, true)
            : $this->getDefaultSubcategoryConfig();

        return response()->json([
            'success' => true,
            'data' => $config
        ]);
    }

    /**
     * Clear UI configuration cache
     *
     * Call this after updating category/subcategory UI configs
     *
     * @return JsonResponse
     */
    public function clearCache(): JsonResponse
    {
        Cache::forget('category_ui_config_v1');

        return response()->json([
            'success' => true,
            'message' => 'UI configuration cache cleared'
        ]);
    }

    /**
     * Get all categories with their UI configurations
     *
     * @return array
     */
    private function getCategoriesConfig(): array
    {
        return Category::select('id', 'category', 'ui_config')
            ->whereNotNull('ui_config')
            ->get()
            ->mapWithKeys(function ($category) {
                return [
                    $category->id => json_decode($category->ui_config, true)
                ];
            })
            ->toArray();
    }

    /**
     * Get all subcategories with their UI configurations
     *
     * @return array
     */
    private function getSubcategoriesConfig(): array
    {
        return SubCategory::select('id', 'sub_category', 'ui_config')
            ->whereNotNull('ui_config')
            ->get()
            ->mapWithKeys(function ($subcategory) {
                return [
                    $subcategory->id => json_decode($subcategory->ui_config, true)
                ];
            })
            ->toArray();
    }

    /**
     * IDs of every category that shows the quantity field — resolved across
     * ALL categories (not just ones with a custom ui_config row), falling
     * back to the default category config the same way the web UI does.
     *
     * @return array
     */
    private function getQuantityEligibleCategoryIds(): array
    {
        $default = $this->getDefaultCategoryConfig();

        return Category::select('id', 'ui_config')
            ->get()
            ->filter(function ($category) use ($default) {
                $config = $category->ui_config
                    ? json_decode($category->ui_config, true)
                    : $default;

                return !in_array('quantity', $config['hide'] ?? []);
            })
            ->pluck('id')
            ->values()
            ->toArray();
    }

    /**
     * Get default configurations for categories and subcategories
     *
     * Used as fallback when no specific config exists
     *
     * @return array
     */
    private function getDefaultConfigs(): array
    {
        return [
            'category' => $this->getDefaultCategoryConfig(),
            'subcategory' => $this->getDefaultSubcategoryConfig()
        ];
    }

    /**
     * Default category configuration
     *
     * @return array
     */
    private function getDefaultCategoryConfig(): array
    {
        return [
            'show' => ['price', 'quantity', 'shipment', 'itemCondition', 'buyDirect'],
            'hide' => ['services', 'salary', 'expectedSalary'],
            'labels' => [
                'brand' => 'Select Option:'
            ]
        ];
    }

    /**
     * Default subcategory configuration
     *
     * @return array
     */
    private function getDefaultSubcategoryConfig(): array
    {
        return [
            'show' => [],
            'hide' => ['divCar', 'divPhone', 'divModel'],
            'labels' => [
                'brand' => 'Select Option:'
            ],
            'required' => []
        ];
    }
}
