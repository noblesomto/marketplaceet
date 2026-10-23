<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

/**
 * Admin Controller for Managing Category UI Configurations
 *
 * Allows administrators to manage show/hide rules for categories
 * and subcategories without touching code.
 */
class CategoryUIAdminController extends Controller
{
    /**
     * Show list of all categories with UI config status
     */
    public function index()
    {
        $title = "Category - " . config('global.site_name');
        $categories = Category::select('id', 'category', 'category_slug', 'ui_config')
            ->orderBy('category')
            ->get()
            ->map(function ($cat) {
                $cat->has_config = !is_null($cat->ui_config);
                $cat->config_count = $cat->has_config ? count(json_decode($cat->ui_config, true)) : 0;
                return $cat;
            });

        $subcategories = SubCategory::select('id', 'cat_id', 'sub_category', 'sub_cat_slug', 'ui_config')
            ->with('category:id,category')
            ->orderBy('cat_id')
            ->orderBy('sub_category')
            ->get()
            ->map(function ($sub) {
                $sub->has_config = !is_null($sub->ui_config);
                $sub->config_count = $sub->has_config ? count(json_decode($sub->ui_config, true)) : 0;
                return $sub;
            });

        return view('admin.category-ui.index', compact('categories', 'subcategories','title'));
    }

    /**
     * Show edit form for category UI config
     */
    public function editCategory($id)
    {
        $title = "Edit Category - " . config('global.site_name');
        $category = Category::findOrFail($id);

        $config = $category->ui_config
            ? json_decode($category->ui_config, true)
            : $this->getDefaultCategoryConfig();

        $availableElements = $this->getAvailableElements();

        return view('admin.category-ui.edit-category', compact('category', 'config', 'availableElements','title'));
    }

    /**
     * Update category UI config
     */
    public function updateCategory(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'show' => 'nullable|array',
            'show.*' => 'string',
            'hide' => 'nullable|array',
            'hide.*' => 'string',
            'label_brand' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Build config
        $config = [
            'show' => $request->input('show', []),
            'hide' => $request->input('hide', []),
            'labels' => []
        ];

        // Add label if provided
        if ($request->filled('label_brand')) {
            $config['labels']['brand'] = $request->input('label_brand');
        }

        // Remove empty arrays
        if (empty($config['show'])) unset($config['show']);
        if (empty($config['hide'])) unset($config['hide']);
        if (empty($config['labels'])) unset($config['labels']);

        // Save to database
        $category->ui_config = !empty($config) ? json_encode($config) : null;
        $category->save();

        // Clear cache
        Cache::forget('category_ui_config_v1');

        return redirect()->route('admin.category-ui.index')
            ->with('status', [
                'text' => "UI configuration updated for category: {$category->category}",
                'type' => 'success'
            ]);
    }

    /**
     * Show edit form for subcategory UI config
     */
    public function editSubcategory($id)
    {
        $title = "Edit Sub Category - " . config('global.site_name');
        $subcategory = SubCategory::with('category')->findOrFail($id);

        $config = $subcategory->ui_config
            ? json_decode($subcategory->ui_config, true)
            : $this->getDefaultSubcategoryConfig();

        $availableElements = $this->getAvailableElements();

        return view('admin.category-ui.edit-subcategory', compact('subcategory', 'config', 'availableElements','title'));
    }

    /**
     * Update subcategory UI config
     */
    public function updateSubcategory(Request $request, $id)
    {
        $subcategory = SubCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'show' => 'nullable|array',
            'show.*' => 'string',
            'hide' => 'nullable|array',
            'hide.*' => 'string',
            'label_brand' => 'nullable|string|max:100',
            'required' => 'nullable|array',
            'required.*' => 'string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Build config
        $config = [
            'show' => $request->input('show', []),
            'hide' => $request->input('hide', []),
            'labels' => [],
            'required' => $request->input('required', [])
        ];

        // Add label if provided
        if ($request->filled('label_brand')) {
            $config['labels']['brand'] = $request->input('label_brand');
        }

        // Remove empty arrays
        if (empty($config['show'])) unset($config['show']);
        if (empty($config['hide'])) unset($config['hide']);
        if (empty($config['labels'])) unset($config['labels']);
        if (empty($config['required'])) unset($config['required']);

        // Save to database
        $subcategory->ui_config = !empty($config) ? json_encode($config) : null;
        $subcategory->save();

        // Clear cache
        Cache::forget('category_ui_config_v1');

        return redirect()->route('admin.category-ui.index')
            ->with('status', [
                'text' => "UI configuration updated for subcategory: {$subcategory->sub_category}",
                'type' => 'success'
            ]);
    }

    /**
     * Delete category UI config
     */
    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
        $category->ui_config = null;
        $category->save();

        Cache::forget('category_ui_config_v1');

        return redirect()->route('admin.category-ui.index')
            ->with('status', [
                'text' => "UI configuration removed for category: {$category->category}. Default config will be used.",
                'type' => 'success'
            ]);
    }

    /**
     * Delete subcategory UI config
     */
    public function deleteSubcategory($id)
    {
        $subcategory = SubCategory::findOrFail($id);
        $subcategory->ui_config = null;
        $subcategory->save();

        Cache::forget('category_ui_config_v1');

        return redirect()->route('admin.category-ui.index')
            ->with('status', [
                'text' => "UI configuration removed for subcategory: {$subcategory->sub_category}. Default config will be used.",
                'type' => 'success'
            ]);
    }

    /**
     * Get available form elements
     */
    private function getAvailableElements()
    {
        return [
            'Form Fields' => [
                'price' => 'Price Field',
                'salary' => 'Salary Field',
                'expectedSalary' => 'Expected Salary Field',
                'services' => 'Services Field',
                'quantity' => 'Quantity Field',
            ],
            'Sections' => [
                'divCar' => 'Car Details Section',
                'divPhone' => 'Phone Details Section',
                'divModel' => 'Model Dropdown',
            ],
            'Options' => [
                'shipment' => 'Shipment Options',
                'shipping' => 'Shipping Methods',
                'itemCondition' => 'Item Condition',
                'buyDirect' => 'Buy Direct Option',
            ]
        ];
    }

    /**
     * Get default category configuration
     */
    private function getDefaultCategoryConfig()
    {
        return [
            'show' => ['price', 'quantity', 'shipment', 'itemCondition', 'buyDirect'],
            'hide' => ['services', 'salary', 'expectedSalary'],
            'labels' => ['brand' => 'Select Option:']
        ];
    }

    /**
     * Get default subcategory configuration
     */
    private function getDefaultSubcategoryConfig()
    {
        return [
            'show' => [],
            'hide' => ['divCar', 'divPhone', 'divModel'],
            'labels' => ['brand' => 'Select Option:'],
            'required' => []
        ];
    }
}
