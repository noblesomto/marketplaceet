<?php

namespace App\Services;

use App\Models\Category;
use App\Models\SubCategory;
use App\Services\AdvertImageRequirements;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

/**
 * Dynamic Validation Service for Adverts
 *
 * Builds validation rules based on Category UI Config
 * Ensures fields hidden by UI are not required in validation
 */
class AdvertValidationService
{
    /** Allowed values of adverts.item_condition (DB enum). */
    public const ITEM_CONDITIONS = ['New', 'Foreign Used', 'Locally Used'];

    /**
     * Get validation rules for creating/updating an advert
     *
     * @param int $categoryId
     * @param int|null $subcategoryId
     * @param bool $isUpdate - If true, images are optional
     * @param bool $hasTempImages - If true, images are optional (already uploaded)
     * @param string|null $accType - The submitting user's account type (e.g. "Commercial"), used to gate account-restricted fields like quantity
     * @return array
     */
    public function getRules(int $categoryId, ?int $subcategoryId = null, bool $isUpdate = false, bool $hasTempImages = false, ?string $accType = null): array
    {
        // Get UI config
        $uiConfig = $this->getUIConfig($categoryId, $subcategoryId);

        $requirements = AdvertImageRequirements::get();
        $mimes = implode(',', $requirements['allowed_formats']);

        // Base rules (always required)
        $rules = [
            'ad_title'    => 'required|max:75',
            'category'    => 'required|integer|min:1|exists:categories,id',
            'subcategory' => 'required|integer|min:1|exists:sub_categories,id',
            'brand'       => 'required',
            'state' => 'required|exists:states,name',
            'lga'   => 'required|exists:lgas,name',
            'description' => ['required', 'max:3500'],
            'images.*' => "image|mimes:{$mimes}|max:{$requirements['max_file_size_kb']}",
            // These map to DB enums — reject unknown values with a clear message instead of a failed insert.
            'item_condition' => 'nullable|in:' . implode(',', self::ITEM_CONDITIONS),
            'contact_price'  => 'nullable|in:yes,no,Yes,No,YES,NO',
            'show_contact'   => 'nullable|in:Yes,No,yes,no,YES,NO',
        ];

        // ✅ Images required only for create (not for jobs category)
        // Skip image requirement if temp images exist (from previous validation error)
        if (!$isUpdate && !in_array($categoryId, [3, 18]) && !$hasTempImages) {
            $rules['images'] = "required|array|min:{$requirements['min_images']}|max:{$requirements['max_images']}";
        }

        // Add conditional rules based on UI config
        $rules = array_merge($rules, $this->buildConditionalRules($uiConfig, $categoryId, $subcategoryId, $accType));

        return $rules;
    }

    /**
     * Build conditional validation rules based on UI config
     *
     * @param array $uiConfig
     * @param int $categoryId
     * @param int|null $subcategoryId
     * @param string|null $accType
     * @return array
     */
    protected function buildConditionalRules(array $uiConfig, int $categoryId, ?int $subcategoryId, ?string $accType = null): array
    {
        $rules = [];

        // Check if field is visible (not hidden by UI config)
        $isVisible = function($field) use ($uiConfig) {
            // If field is in hide array, it's not visible
            if (in_array($field, $uiConfig['hide'] ?? [])) {
                return false;
            }

            // ✅ FIX: Financial fields must be EXPLICITLY shown
            // salary/expectedSalary/price are mutually exclusive and category-specific
            // They should ONLY be visible if explicitly in the "show" array
            $financialFields = ['salary', 'expectedSalary', 'price'];
            if (in_array($field, $financialFields)) {
                return in_array($field, $uiConfig['show'] ?? []);
            }

            // For other fields, if not explicitly hidden, consider visible
            return true;
        };

        // Check if field is required by subcategory config
        $isRequired = function($field) use ($uiConfig) {
            return in_array($field, $uiConfig['required'] ?? []);
        };

        // Determine which financial field should be required
        $salaryVisible = $isVisible('salary');
        $expectedSalaryVisible = $isVisible('expectedSalary');
        $priceVisible = $isVisible('price');

        // Salary field (for jobs category)
        if ($salaryVisible) {
            $rules['salary'] = 'required';
        }

        // Expected salary field (for CVs category)
        if ($expectedSalaryVisible) {
            $rules['expected_salary'] = 'required';
        }

        // Price field - required by default unless salary/expectedSalary visible OR contact_price=yes
        if ($priceVisible) {
            // If salary or expectedSalary are shown, price is not required
            if ($salaryVisible || $expectedSalaryVisible) {
                $rules['price'] = 'nullable|numeric';
            } else {
                // Price is required unless user selected "Contact for Price".
                // `gt:0` matters because Laravel's required/required_unless treat 0 as
                // "present" (only null/''/[] count as empty), so a bare `required_unless`
                // was letting price=0 through despite price_type being "Fixed Price".
                $rules['price']      = 'required_unless:contact_price,yes|numeric|gt:0';
                $rules['price_type'] = 'required_unless:contact_price,yes';
            }
        }

        // Quantity field — category must show it AND the account must be Commercial
        // (matches the @if($user->acc_type=="Commercial") gate in post-ad/edit-ad Blade views).
        // Reject outright rather than silently ignore, since callers save quantity
        // straight from raw request input, bypassing these rules otherwise.
        if ($isVisible('quantity') && $accType === 'Commercial') {
            $rules['quantity'] = 'nullable|numeric|min:1';
        } else {
            $rules['quantity'] = 'prohibited';
        }

        // Item condition (only if visible and not excluded by category/subcat)
        if ($isVisible('itemCondition')) {
            // Must match category-ui-manager.js hide rules exactly.
            // Categories that hide itemCondition: Services(1), Jobs(3), Real Estate(7), Local Biz(11), CVs(18)
            // Subcategories that hide itemCondition: Cars(2), Phones(6), Real-estate subcats(16-19), Trucks(21), Bikes(23)
            // NOTE: subcats 22 (Motorcycles), 24 (Parts), 25 (Accessories) SHOW itemCondition — do NOT skip them.
            $skipItemConditionCategories = [1, 3, 7, 11, 18];
            $skipItemConditionSubcats    = [2, 6, 16, 17, 18, 19, 21, 23];

            if (!in_array($categoryId, $skipItemConditionCategories) &&
                !in_array($subcategoryId, $skipItemConditionSubcats)) {
                $rules['item_condition'] = 'required|in:' . implode(',', self::ITEM_CONDITIONS);
            }
        }

        // Car-specific fields — always required for car subcategories regardless of UI config.
        // Subcats: 2=Cars, 21=Buses & Minibuses, 23=Trucks & Trailers
        if (in_array($subcategoryId, [2, 21, 23])) {
            $rules['condition']      = ['required', Rule::in(['Local used', 'Foreign used', 'Brand new'])];
            $rules['registration']   = ['required', Rule::in(['Registered', 'Unregistered'])];
            $rules['fuel']           = ['required', Rule::in(['Petrol', 'Diesel', 'Electric', 'Hybrid', 'Natural gas CNG', 'LPG'])];
            $rules['transmission']   = ['required', Rule::in(['Automatic', 'Manual', 'AMT', 'CVT'])];
            $rules['vehicle_type']   = ['required', Rule::in(['Small Car', 'SUV/Off Road Vehicle', 'Station Wagon', 'Truck', 'Coupe', 'Limousine', 'Pickup', 'Van/Bus', 'Others'])];
            $rules['exterior_color'] = 'required|string|max:50';
            // model is an integer FK into the models table — must exist and be non-zero
            $rules['model']          = 'required|numeric|min:1|exists:models,id';
        }

        // Phone-specific fields — always required for phone subcategory
        if ($subcategoryId == 6) {
            $rules['phone_color']     = 'required';
            $rules['phone_condition'] = 'required';
            $rules['device']          = 'required';
            // model dropdown is not shown for phones — no model required
        }

        // Generic model requirement (for other subcategories that declare model required)
        if ($isRequired('model') && !isset($rules['model'])) {
            $rules['model'] = 'required|numeric|min:1|exists:models,id';
        }

        return $rules;
    }

    /**
     * Get merged UI config for category and subcategory
     *
     * @param int $categoryId
     * @param int|null $subcategoryId
     * @return array
     */
    protected function getUIConfig(int $categoryId, ?int $subcategoryId): array
    {
        $config = Cache::remember('category_ui_config_v1', 60 * 60 * 24, function () {
            $categories = Category::select('id', 'ui_config')->get();
            $subcategories = SubCategory::select('id', 'ui_config')->get();

            $catConfig = [];
            foreach ($categories as $cat) {
                if ($cat->ui_config) {
                    $catConfig[$cat->id] = json_decode($cat->ui_config, true);
                }
            }

            $subConfig = [];
            foreach ($subcategories as $sub) {
                if ($sub->ui_config) {
                    $subConfig[$sub->id] = json_decode($sub->ui_config, true);
                }
            }

            return [
                'categories' => $catConfig,
                'subcategories' => $subConfig,
            ];
        });

        // Get category config
        $categoryConfig = $config['categories'][$categoryId] ?? $this->getDefaultCategoryConfig($categoryId);

        // Get subcategory config (if provided)
        $subcategoryConfig = [];
        if ($subcategoryId) {
            $subcategoryConfig = $config['subcategories'][$subcategoryId] ?? [];
        }

        // Merge configs (subcategory rules are additive)
        return $this->mergeConfigs($categoryConfig, $subcategoryConfig);
    }

    /**
     * Merge category and subcategory configs
     * Subcategory rules are ADDITIVE to category rules
     *
     * @param array $categoryConfig
     * @param array $subcategoryConfig
     * @return array
     */
    protected function mergeConfigs(array $categoryConfig, array $subcategoryConfig): array
    {
        $merged = [
            'show' => array_merge(
                $categoryConfig['show'] ?? [],
                $subcategoryConfig['show'] ?? []
            ),
            'hide' => array_merge(
                $categoryConfig['hide'] ?? [],
                $subcategoryConfig['hide'] ?? []
            ),
            'required' => $subcategoryConfig['required'] ?? [],
            'labels' => array_merge(
                $categoryConfig['labels'] ?? [],
                $subcategoryConfig['labels'] ?? []
            ),
        ];

        // Remove duplicates
        $merged['show'] = array_unique($merged['show']);
        $merged['hide'] = array_unique($merged['hide']);

        // If a field is in both show and hide, hide takes precedence
        $merged['show'] = array_diff($merged['show'], $merged['hide']);

        return $merged;
    }

    /**
     * Get default category config (fallback if no custom config)
     *
     * @param int $categoryId
     * @return array
     */
    protected function getDefaultCategoryConfig(int $categoryId): array
    {
        // Default configs for common categories
        $defaults = [
            1 => [ // Jobs
                'show' => ['salary'],
                'hide' => ['price', 'shipment', 'itemCondition', 'buyDirect', 'expectedSalary', 'quantity'],
                'labels' => ['brand' => 'Select Job Type:'],
            ],
            2 => [ // Services
                'show' => ['services'],
                'hide' => ['price', 'salary', 'expectedSalary', 'quantity', 'shipment'],
                'labels' => ['brand' => 'Select Service Type:'],
            ],
            3 => [ // Jobs (duplicate ID? check actual category IDs)
                'show' => ['salary'],
                'hide' => ['price', 'shipment', 'itemCondition', 'buyDirect', 'expectedSalary', 'quantity'],
                'labels' => ['brand' => 'Select Job Type:'],
            ],
            18 => [ // CVs
                'show' => ['expectedSalary'],
                'hide' => ['price', 'salary', 'shipment', 'itemCondition', 'buyDirect', 'quantity'],
                'labels' => ['brand' => 'Select Position:'],
            ],
        ];

        return $defaults[$categoryId] ?? [
            'show' => ['price', 'quantity', 'shipment', 'itemCondition', 'buyDirect'],
            'hide' => ['services', 'salary', 'expectedSalary'],
            'labels' => ['brand' => 'Select Option:'],
        ];
    }

    /**
     * Validate shipping / Buy Direct requirements.
     *
     * Courier selection is only required while shipping carriers are enabled
     * (config global.shipping_carriers_enabled). Buy Direct requires the seller
     * to have bank details on file, otherwise escrowed funds can't be paid out.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User|null $seller
     * @return array|null Returns error array if validation fails, null if passes
     */
    public function validateShipping($request, $seller = null): ?array
    {
        $wantsBuyDirect = strtolower((string) $request->buy_direct) === 'yes';

        if (config('global.shipping_carriers_enabled')) {
            $needsShipping = $request->shipment === 'Ship' || $wantsBuyDirect;

            if ($needsShipping && empty($request->input('shipping'))) {
                return ['shipping' => ['Please select at least one shipping method.']];
            }
        }

        if ($wantsBuyDirect && $seller && (empty($seller->account_number) || empty($seller->bank_code))) {
            return ['buy_direct' => ['Add your bank details in your profile before enabling Buy Direct, so we can pay you when the item sells.']];
        }

        return null;
    }
}
