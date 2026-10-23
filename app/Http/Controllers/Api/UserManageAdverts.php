<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advert;
use App\Models\AdSetting;
use App\Models\AdvertImage;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brands;
use App\Models\VehicleModel;
use App\Models\State;
use App\Models\Shipping;
use App\Models\CarDetail;
use App\Models\PhoneDetail;
use App\Models\AdvertBoost;
use App\Helpers\ContentHelper;
use App\Helpers\FileUploadHelper;
use App\Services\AdvertImageRequirements;
use App\Services\AdvertValidationService;
use App\Services\ImageQualityService;
use App\Traits\ManagesImages;
use App\Jobs\PostAdvertJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Support\ActivityLog;

/**
 * @group Advert Management
 *
 * APIs for creating, updating, and managing user adverts
 */
class UserManageAdverts extends Controller
{
    use ManagesImages;
    /**
     * @OA\Get(
     *     path="/api/adverts/categories/{categoryId}/subcategories",
     *     summary="Get subcategories by category",
     *     tags={"Advert Management"},
     *     @OA\Parameter(
     *         name="categoryId",
     *         in="path",
     *         required=true,
     *         description="Category ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subcategories list",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SubCategory"))
     *         )
     *     )
     * )
     */
    public function fetchSubcategories($categoryId)
    {
        $subcategories = SubCategory::where('cat_id', $categoryId)->get();

        return response()->json([
            'success' => true,
            'data' => $subcategories
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/adverts/subcategories/{subcategoryId}/brands",
     *     summary="Get brands by subcategory",
     *     tags={"Advert Management"},
     *     @OA\Parameter(
     *         name="subcategoryId",
     *         in="path",
     *         required=true,
     *         description="Subcategory ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Brands list",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Brand"))
     *         )
     *     )
     * )
     */
    public function fetchBrands($subcategoryId)
    {
        $brands = Brands::where('subcat_id', $subcategoryId)->get();

        return response()->json([
            'success' => true,
            'data' => $brands
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/adverts/brands/{brandId}/models",
     *     summary="Get models by brand",
     *     tags={"Advert Management"},
     *     @OA\Parameter(
     *         name="brandId",
     *         in="path",
     *         required=true,
     *         description="Brand ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Models list",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Model"))
     *         )
     *     )
     * )
     */
    public function fetchModels($brandId)
    {
        $models = VehicleModel::where('brand_id', $brandId)->get();

        return response()->json([
            'success' => true,
            'data' => $models
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/shipping-companies",
     *     summary="Get active shipping companies",
     *     tags={"Advert Management"},
     *     @OA\Response(
     *         response=200,
     *         description="Shipping companies list",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Shipping"))
     *         )
     *     )
     * )
     */
    public function getShippingCompanies()
    {
        $shippings = Shipping::where('status', 'Active')->orderBy('company', 'asc')->get();
        $shippings->each(function ($shipping) {
            $shipping->logo = $shipping->logo
                ? (Str::startsWith($shipping->logo, 'http') ? $shipping->logo : asset('uploads/shipping/'.$shipping->logo))
                : null;
        });

        return response()->json([
            'success' => true,
            'data' => $shippings
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/adverts/create/data",
     *     summary="Get data needed for creating adverts",
     *     tags={"Advert Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Create advert data",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="categories", type="array", @OA\Items(ref="#/components/schemas/Category")),
     *                 @OA\Property(property="states", type="array", @OA\Items(ref="#/components/schemas/State")),
     *                 @OA\Property(property="shippings", type="array", @OA\Items(ref="#/components/schemas/Shipping"))
     *             )
     *         )
     *     )
     * )
     */
    public function getCreateData()
    {
        $categories = Category::orderBy('category', 'asc')->get();
        $states = State::all();
        $shippings = Shipping::where('status', 'Active')->orderBy('company', 'asc')->get();
        $shippings->each(function ($shipping) {
            $shipping->logo = $shipping->logo
                ? (Str::startsWith($shipping->logo, 'http') ? $shipping->logo : asset('uploads/shipping/'.$shipping->logo))
                : null;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'states'     => $states,
                'shippings'  => $shippings,
                'image_requirements' => AdvertImageRequirements::get(),
                'car_options' => [
                    'conditions'    => ['Local used', 'Foreign used', 'Brand new'],
                    'fuels'         => ['Petrol', 'Diesel', 'Electric', 'Hybrid', 'Natural gas CNG', 'LPG'],
                    'transmissions' => ['Automatic', 'Manual', 'AMT', 'CVT'],
                    'registrations' => ['Registered', 'Unregistered'],
                    'vehicle_types' => ['Small Car', 'SUV/Off Road Vehicle', 'Station Wagon', 'Truck', 'Coupe', 'Limousine', 'Pickup', 'Van/Bus', 'Others'],
                ],
                'phone_options' => [
                    'conditions'   => ['New - Unboxed', 'New - No Packaging', 'Used - Very Good', 'Used - Good', 'Used - Defect', 'Foreign Used - No Packaging'],
                    'device_types' => ['Smartphone', 'Feature Phone', 'Tablet'],
                ],
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/adverts/image-requirements",
     *     summary="Get the current admin-configured advert image requirements",
     *     description="Returns the same image count/dimension/size/format rules the server enforces, so the client can validate locally before upload. Cached ~1hr server-side (per AdSetting key); safe to poll or cache on the client too.",
     *     tags={"Advert Management"},
     *     @OA\Response(
     *         response=200,
     *         description="Current image requirements",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function imageRequirements()
    {
        return response()->json([
            'success' => true,
            'data'    => AdvertImageRequirements::get(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/adverts/upload-images",
     *     summary="Pre-upload images before submitting an advert",
     *     description="Upload images independently and receive tokens. Pass the tokens in temp_image_paths[] when calling POST /api/adverts. Tokens expire after 1 hour. The old images[] approach on the create endpoint still works — this is an optional optimisation.",
     *     tags={"Advert Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"images"},
     *                 @OA\Property(property="images[]", type="array", @OA\Items(type="string", format="binary"), description="1–8 images, max 20MB each")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Images uploaded — tokens returned"),
     *     @OA\Response(response=422, description="Validation or quality error")
     * )
     */
    public function uploadImages(Request $request)
    {
        $user = auth()->user();
        $requirements = AdvertImageRequirements::get();
        $mimes = implode(',', $requirements['allowed_formats']);

        $validator = Validator::make($request->all(), [
            'images'   => "required|array|min:1|max:{$requirements['max_images']}",
            'images.*' => "required|image|mimes:{$mimes}|max:{$requirements['max_file_size_kb']}",
        ], [
            'images.required' => 'Please provide at least one image.',
            'images.min'      => 'Please provide at least one image.',
            'images.max'      => "You may upload a maximum of {$requirements['max_images']} images at a time.",
            'images.*.image'  => 'All files must be images.',
            'images.*.mimes'  => 'Images must be ' . implode(', ', $requirements['allowed_formats']) . ' format.',
            'images.*.max'    => 'Each image must not exceed ' . round($requirements['max_file_size_bytes'] / (1024 * 1024)) . 'MB.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Run quality validation early so errors surface before the main submit
        $imageQualityService = new ImageQualityService();
        $qualityErrors = [];

        foreach ($request->file('images') as $image) {
            $result = $imageQualityService->validateImage($image, $requirements['strictness']);
            if (!$result['valid']) {
                $qualityErrors = array_merge($qualityErrors, $result['errors']);
            }
        }

        if (!empty($qualityErrors)) {
            return response()->json([
                'success'         => false,
                'errors'          => ['images' => array_slice($qualityErrors, 0, 3)],
                'recommendations' => $imageQualityService->getRecommendations(['valid' => false, 'details' => []]),
            ], 422);
        }

        // Store in a user-scoped temp directory — prevents token theft across accounts
        $tempDir  = 'temp/post-ad-images/' . $user->user_id;
        $uploaded = [];

        foreach ($request->file('images') as $index => $image) {
            if (!$image->isValid()) continue;

            $filename = uniqid('tmp_') . '_' . time() . '_' . $index . '.' . $image->getClientOriginalExtension();
            $path     = $image->storeAs($tempDir, $filename, 'public');

            if ($path) {
                $uploaded[] = [
                    'token' => $path,
                    'url'   => Storage::disk('public')->url($path),
                    'index' => $index,
                ];
            }
        }

        Log::info('API: Pre-upload images stored', [
            'user_id'     => $user->user_id,
            'image_count' => count($uploaded),
        ]);

        return response()->json([
            'success'    => true,
            'message'    => count($uploaded) . ' image(s) uploaded. Pass the tokens in temp_image_paths[] when submitting your advert.',
            'images'     => $uploaded,
            'expires_in' => 3600,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/adverts",
     *     summary="Create a new advert",
     *     tags={"Advert Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"ad_title", "category", "subcategory", "brand", "state", "lga", "description"},
     *                 @OA\Property(property="ad_title", type="string", maxLength=75),
     *                 @OA\Property(property="ad_type", type="string"),
     *                 @OA\Property(property="category", type="integer"),
     *                 @OA\Property(property="subcategory", type="integer"),
     *                 @OA\Property(property="brand", type="integer"),
     *                 @OA\Property(property="price", type="number"),
     *                 @OA\Property(property="contact_price", type="string"),
     *                 @OA\Property(property="salary", type="number"),
     *                 @OA\Property(property="expected_salary", type="number"),
     *                 @OA\Property(property="item_condition", type="string"),
     *                 @OA\Property(property="price_type", type="string"),
     *                 @OA\Property(property="buy_direct", type="string"),
     *                 @OA\Property(property="state", type="string"),
     *                 @OA\Property(property="lga", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="shipment", type="string"),
     *                 @OA\Property(property="show_contact", type="string"),
     *                 @OA\Property(property="quantity", type="integer"),
     *                 @OA\Property(property="shipping", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="images[]", type="array", @OA\Items(type="string", format="binary")),
     *                 @OA\Property(property="image_order", type="string"),
     *                 @OA\Property(property="promotion", type="string"),
     *                 // Car specific fields
     *                 @OA\Property(property="model", type="integer"),
     *                 @OA\Property(property="registration", type="string"),
     *                 @OA\Property(property="mileage", type="number"),
     *                 @OA\Property(property="condition", type="string"),
     *                 @OA\Property(property="fuel", type="string"),
     *                 @OA\Property(property="transmission", type="string"),
     *                 @OA\Property(property="vehicle_type", type="string"),
     *                 @OA\Property(property="doors", type="integer"),
     *                 @OA\Property(property="exterior_color", type="string"),
     *                 @OA\Property(property="material_interior", type="string"),
     *                 @OA\Property(property="exterior_equipment", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="interior", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="security", type="array", @OA\Items(type="string")),
     *                 // Phone specific fields
     *                 @OA\Property(property="phone_color", type="string"),
     *                 @OA\Property(property="phone_condition", type="string"),
     *                 @OA\Property(property="device", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Advert created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/Advert")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function createAdvert(Request $request)
    {
        $user = auth()->user();

        // Idempotency key: the app generates one UUID per "Post Ad" tap and
        // resends the same one on any automatic retry (e.g. after a network
        // error where it can't tell whether the original request succeeded).
        // A repeat with the same key returns the advert already created
        // instead of creating a duplicate. Optional — omit it and behavior
        // falls back to the content-based duplicate check below.
        $idempotencyKey = $request->header('Idempotency-Key') ?: $request->input('idempotency_key');
        $idempotencyKey = is_string($idempotencyKey) ? trim($idempotencyKey) : null;

        if ($idempotencyKey === '') {
            $idempotencyKey = null;
        }

        if ($idempotencyKey !== null && !preg_match('/^[A-Za-z0-9\-_.]{8,64}$/', $idempotencyKey)) {
            return response()->json([
                'success' => false,
                'errors' => ['idempotency_key' => ['Idempotency key must be 8-64 characters: letters, numbers, - _ or .']],
            ], 422);
        }

        if ($idempotencyKey !== null) {
            $existing = Advert::where('user_id', $user->user_id)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing) {
                $existing->load(['media', 'car', 'phone', 'shippings']);
                return response()->json([
                    'success' => true,
                    'message' => 'Advert already created for this request.',
                    'replayed' => true,
                    'data' => $existing,
                ], 200);
            }
        }

        $subcat = (int) $request->input('subcategory');
        $category = (int) $request->input('category');

        $this->normalizeStateInput($request);

        // Use dynamic validation service based on Category UI Config
        $validationService = new AdvertValidationService();
        $hasTempImages = !empty($request->input('temp_image_paths', []));
        $rules = $validationService->getRules($category, $subcat, false, $hasTempImages, $user->acc_type);
        $imageRequirements = AdvertImageRequirements::get();

        $messages = [
            'images.required'             => "Please upload at least {$imageRequirements['min_images']} images.",
            'images.min'                  => "Please upload at least {$imageRequirements['min_images']} images.",
            'images.max'                  => "You may upload a maximum of {$imageRequirements['max_images']} images.",
            'state.exists'                => 'Please select a valid state.',
            'lga.exists'                  => 'Please select a valid LGA.',
            'price.required_unless'       => 'Please enter a price, or select "Contact for Price".',
            'price.gt'                    => 'Please enter a price greater than 0, or select "Contact for Price".',
            'price_type.required_unless'  => 'Please select a price type.',
            'condition.required'          => 'Please select the vehicle condition.',
            'registration.required'       => 'Please select the vehicle registration status.',
            'fuel.required'               => 'Please select the fuel type.',
            'transmission.required'       => 'Please select the transmission type.',
            'vehicle_type.required'       => 'Please select the body type.',
            'exterior_color.required'     => 'Please select the exterior color.',
            'model.required'              => 'Please select the vehicle model.',
            'model.exists'                => 'The selected model is invalid.',
            'model.min'                   => 'Please select a valid vehicle model.',
            'phone_color.required'        => 'Please select the phone color.',
            'phone_condition.required'    => 'Please select the phone condition.',
            'device.required'             => 'Please select the device type.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            Log::warning('createAdvert validation failed', [
                'user_id' => $user->user_id,
                'errors'  => $validator->errors()->toArray(),
                'input'   => $request->only(['ad_title','category','subcategory','brand','state','lga','price','item_condition']),
            ]);
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate shipping requirements
        $shippingError = $validationService->validateShipping($request, $user);
        if ($shippingError) {
            return response()->json([
                'success' => false,
                'errors' => $shippingError
            ], 422);
        }

        if ($reason = ContentHelper::detectBannedContact($request->input('ad_title'))) {
            return response()->json([
                'success' => false,
                'errors'  => ['ad_title' => ["Your ad title appears to contain {$reason}. For your safety, please don't share phone numbers or contact details in the ad text — buyers can reach you directly through in-app messaging once your ad is live."]],
            ], 422);
        }

        // Duplicate check: same user + title + category/subcategory, active or posted in last 24h
        $adTitle = ContentHelper::sanitizeTitle($request->input('ad_title', ''));

        if (empty($adTitle)) {
            return response()->json([
                'success' => false,
                'errors'  => ['ad_title' => ['Your ad title contains only invalid characters (e.g. phone numbers or special symbols). Please use plain descriptive text.']],
            ], 422);
        }

        if ($reason = ContentHelper::detectBannedContact($request->input('description'))) {
            return response()->json([
                'success' => false,
                'errors'  => ['description' => ["Your description appears to contain {$reason}. For your safety, please don't share phone numbers or contact details in the ad text — buyers can reach you directly through in-app messaging once your ad is live."]],
            ], 422);
        }

        $duplicateExists = Advert::where('user_id', $user->user_id)
            ->where('ad_title', $adTitle)
            ->where('category', $category)
            ->where('sub_category', $subcat)
            ->where(function ($q) {
                $q->where('ad_status', 'active')
                  ->orWhere('created_at', '>=', now()->subHours(24));
            })
            ->exists();

        if ($duplicateExists) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active listing with this title in the same category.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $metaDescription = Str::limit(strip_tags($request->input('description')), 150, '');
            $rawWords = explode(' ', Str::slug($request->input('ad_title') . ' ' . $request->input('description'), ' '));
            $filteredWords = array_filter($rawWords, function ($word) {
                return strlen($word) > 3;
            });
            $uniqueWords = array_unique($filteredWords);
            $keywords = implode(', ', array_slice($uniqueWords, 0, 10));

            $advert = Advert::create([
                'idempotency_key' => $idempotencyKey,
                'ad_title'   => $adTitle,
                'title_slug' => Str::slug($adTitle),
                'ad_type'    => $request->input('ad_type', 'Private'),
                'category' => $request->input('category'),
                'sub_category' => $request->input('subcategory'),
                'brand' => $request->input('brand'),
                'price' => $request->input('price'),
                'contact_price' => $request->input('contact_price'),
                'salary' => $request->input('salary'),
                'expected_salary' => $request->input('expected_salary'),
                'item_condition' => $request->input('item_condition'),
                'price_type' => $request->input('price_type'),
                'buy_direct' => strtolower($request->input('buy_direct', 'No')) === 'yes' ? 'Yes' : 'No',
                'state' => $request->input('state'),
                'lga' => $request->input('lga'),
                'state_slug' => Str::slug($request->input('lga')),
                'description' => ContentHelper::sanitizeDescription($request->input('description')),
                'keyword' => $keywords,
                'meta_description' => $metaDescription,
                'featured' => "No",
                'shipment' => $request->input('shipment', 'Pickup'),
                'show_contact' => $request->input('show_contact', 'No'),
                'quantity' => $request->input('quantity') ?? 1,
                'views' => "0",
                'ad_status' => 'active',
                'user_id' => $user->user_id,
                'source' => 'api',
            ]);

            $advert->update(['ad_id' => (string) $advert->id]);

            ActivityLog::record('advert', 'Posted advert "' . $advert->ad_title . '"', $user, $advert, ['source' => 'app']);

            $advert->shippings()->sync($request->input('shipping', []));

            // ── Image handling ───────────────────────────────────────────────────
            // Path A: pre-uploaded tokens (new two-step flow)
            // Path B: direct file upload in this request (original flow — unchanged)
            // Path C: jobs/CV default image
            $tempImagePaths = $request->input('temp_image_paths', []);

            if (!empty($tempImagePaths)) {
                // Path A — quality already validated at upload time, just attach
                if (!in_array($category, [3, 18]) && count($tempImagePaths) < $imageRequirements['min_images']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'errors'  => ['images' => ["Please upload at least {$imageRequirements['min_images']} images."]],
                    ], 422);
                }

                $expectedPrefix = 'temp/post-ad-images/' . $user->user_id . '/';

                foreach ($tempImagePaths as $position => $tempPath) {
                    // Security: token must be owned by this user
                    if (!str_starts_with($tempPath, $expectedPrefix)) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'errors'  => ['images' => ['Invalid image token. Please re-upload your images.']],
                        ], 403);
                    }

                    $fullPath = Storage::disk('public')->path($tempPath);
                    if (!file_exists($fullPath)) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'errors'  => ['images' => ['One or more images have expired. Please upload them again.']],
                        ], 422);
                    }

                    $media = $advert
                        ->addMedia($fullPath)
                        ->withCustomProperties(['position' => $position + 1, 'source' => 'temp'])
                        ->usingFileName(uniqid() . '.webp')
                        ->toMediaCollection('images');

                    $media->order_column = $position + 1;
                    $media->save();
                }

                Log::info('API: Pre-uploaded images attached', [
                    'advert_id'   => $advert->ad_id,
                    'image_count' => count($tempImagePaths),
                ]);

            } elseif ($request->hasFile('images')) {
                // Path B — ✅ TIER 1: Image Quality Validation (original direct-upload, unchanged)
                $imageQualityService = new ImageQualityService();
                $imageQualityErrors = [];
                $totalQualityScore = 0;
                $imageCount = 0;

                foreach ($request->file('images') as $image) {
                    $result = $imageQualityService->validateImage($image, $imageRequirements['strictness']);
                    $imageCount++;

                    if (!$result['valid']) {
                        $imageQualityErrors = array_merge($imageQualityErrors, $result['errors']);
                    }

                    $totalQualityScore += $result['score'];
                }

                if (!empty($imageQualityErrors)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'errors'  => ['images' => array_slice($imageQualityErrors, 0, 3)],
                        'recommendations' => $imageQualityService->getRecommendations([
                            'valid' => false, 'details' => []
                        ]),
                    ], 422);
                }

                if (!in_array($category, [3, 18]) && $imageCount < $imageRequirements['min_images']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'errors'  => ['images' => ["Please upload at least {$imageRequirements['min_images']} images."]],
                    ], 422);
                }

                $images = $request->file('images');
                $order  = explode(',', $request->input('image_order', ''));

                if (empty($order) || $order[0] === '') {
                    $order = array_keys($images);
                }

                foreach ($order as $position => $index) {
                    if (!isset($images[$index]) || !$images[$index]->isValid()) continue;

                    $media = $advert
                        ->addMedia($images[$index])
                        ->withCustomProperties([
                            'position'      => $position + 1,
                            'original_name' => $images[$index]->getClientOriginalName(),
                        ])
                        ->usingFileName(uniqid() . '.webp')
                        ->toMediaCollection('images');

                    $media->order_column = $position + 1;
                    $media->save();
                }

                $avgScore = $imageCount > 0 ? round($totalQualityScore / $imageCount) : 0;
                Log::info('API: Image quality validation passed', [
                    'advert_id'      => $advert->ad_id,
                    'image_count'    => $imageCount,
                    'average_score'  => $avgScore,
                    'quality_rating' => $imageQualityService->getQualityRating($avgScore),
                ]);

            } elseif (in_array($category, [3, 18])) {
                // Path C — default image for jobs/CV categories
                $advert->addDefaultImage('jobs.png');
            }

            // Store car-specific info — subcats: 2=Cars, 21=Buses & Minibuses, 23=Trucks & Trailers
            if (in_array($subcat, [2, 21, 23])) {
                $car = new CarDetail([
                    'car_id' => rand(10000, 99999),
                    'cat_id' => $request->input('category'),
                    'brand_id' => $request->input('brand'),
                    'model' => $request->input('model'),
                    'mileage' => $request->input('mileage'),
                    'condition' => $request->input('condition'),
                    'registration' => $request->input('registration'),
                    'fuel' => $request->input('fuel'),
                    'transmission' => $request->input('transmission'),
                    'vehicle_type' => $request->input('vehicle_type'),
                    'doors' => $request->input('doors'),
                    'exterior_color' => $request->input('exterior_color'),
                    'material_interior' => $request->input('material_interior'),
                    'exterior_equipment' => json_encode($request->input('exterior_equipment', [])),
                    'interior' => json_encode($request->input('interior', [])),
                    'security' => json_encode($request->input('security', [])),
                ]);
                $car->advert()->associate($advert);
                $car->save();
            }

            // Store phone-specific info
            if ($subcat === 6) {
                $phone = new PhoneDetail([
                    'phone_id' => rand(10000, 99999),
                    'cat_id' => $request->input('category'),
                    'brand_id' => $request->input('brand'),
                    'model' => $request->input('model'),
                    'color' => $request->input('phone_color'),
                    'device' => $request->input('device'),
                    'condition' => $request->input('phone_condition'),
                ]);
                $phone->advert()->associate($advert);
                $phone->save();
            }

            DB::commit();

            // Notify followers after response is sent (no queue worker required)
            $seller = auth()->user();
            if ($seller) {
                PostAdvertJob::dispatchAfterResponse(
                    $advert,
                    $seller,
                    'New Ad',
                    $seller->name . ' has placed the ad "' . $advert->ad_title . '"'
                );

                Log::info('API: Follower notification dispatched after response', [
                    'advert_id' => $advert->ad_id,
                    'seller_id' => $seller->id
                ]);
            }

            // Load relationships (media instead of old images)
            $advert->load(['media', 'car', 'phone', 'shippings']);

            return response()->json([
                'success' => true,
                'message' => 'Advert created successfully',
                'data' => $advert
            ], 201);

        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            DB::rollBack();

            // A concurrent request with the same idempotency key won the race and
            // committed first — return the advert it created instead of erroring
            // or leaving the client with a duplicate.
            if ($idempotencyKey !== null) {
                $existing = Advert::where('user_id', $user->user_id)
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();

                if ($existing) {
                    $existing->load(['media', 'car', 'phone', 'shippings']);
                    return response()->json([
                        'success' => true,
                        'message' => 'Advert already created for this request.',
                        'replayed' => true,
                        'data' => $existing,
                    ], 200);
                }
            }

            Log::error('Advert creation failed: unique constraint violation', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while creating your advert. Please try again.',
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Advert creation failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while creating your advert. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/adverts/{advertId}/edit",
     *     summary="Get advert data for editing",
     *     tags={"Advert Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="advertId",
     *         in="path",
     *         required=true,
     *         description="Advert ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Advert data",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="advert", ref="#/components/schemas/Advert"),
     *                 @OA\Property(property="subcategories", type="array", @OA\Items(ref="#/components/schemas/SubCategory")),
     *                 @OA\Property(property="brands", type="array", @OA\Items(ref="#/components/schemas/Brand")),
     *                 @OA\Property(property="models", type="array", @OA\Items(ref="#/components/schemas/Model"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Advert not found"
     *     )
     * )
     */
    public function getAdvertForEdit($advertId)
    {
        $user = auth()->user();

        $advert = Advert::with(['media', 'car', 'phone', 'shippings'])
            ->where('id', $advertId)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found'
            ], 404);
        }

        $subcategories = SubCategory::where('cat_id', $advert->category)->get();
        $brands = Brands::where('subcat_id', $advert->sub_category)->get();
        $models = VehicleModel::where('brand_id', $advert->brand)->get();

        // Return Spatie media items as 'images' for the edit screen.
        // Each item includes 'id' (use for deleted_images[] / existing_image_order),
        // 'url', 'thumbnail_url', and 'order_column' for the Android client.
        $images = $advert->getMedia('images')->map(fn ($m) => [
            'id'            => $m->id,
            'url'           => $m->getUrl(),
            'thumbnail_url' => $m->getUrl('thumbnail'),
            'order_column'  => $m->order_column,
        ])->values();

        return response()->json([
            'success' => true,
            'data' => [
                'advert'        => $advert,
                'images'        => $images,
                'subcategories' => $subcategories,
                'brands'        => $brands,
                'models'        => $models,
                'user'          => $user,
            ]
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/adverts/{advertId}",
     *     summary="Update an advert",
     *     tags={"Advert Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="advertId",
     *         in="path",
     *         required=true,
     *         description="Advert ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"ad_title", "category", "subcategory", "brand", "state", "lga", "description"},
     *                 @OA\Property(property="ad_title", type="string", maxLength=75),
     *                 @OA\Property(property="ad_type", type="string"),
     *                 @OA\Property(property="category", type="integer"),
     *                 @OA\Property(property="subcategory", type="integer"),
     *                 @OA\Property(property="brand", type="integer"),
     *                 @OA\Property(property="price", type="number"),
     *                 @OA\Property(property="contact_price", type="string"),
     *                 @OA\Property(property="salary", type="number"),
     *                 @OA\Property(property="expected_salary", type="number"),
     *                 @OA\Property(property="item_condition", type="string"),
     *                 @OA\Property(property="price_type", type="string"),
     *                 @OA\Property(property="buy_direct", type="string"),
     *                 @OA\Property(property="state", type="string"),
     *                 @OA\Property(property="lga", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="shipment", type="string"),
     *                 @OA\Property(property="show_contact", type="string"),
     *                 @OA\Property(property="quantity", type="integer"),
     *                 @OA\Property(property="shipping", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="images[]", type="array", @OA\Items(type="string", format="binary")),
     *                 @OA\Property(property="image_order", type="string"),
     *                 @OA\Property(property="deleted_images", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="existing_image_order", type="string"),
     *                 // Car specific fields
     *                 @OA\Property(property="model", type="integer"),
     *                 @OA\Property(property="registration", type="string"),
     *                 @OA\Property(property="mileage", type="number"),
     *                 @OA\Property(property="condition", type="string"),
     *                 @OA\Property(property="fuel", type="string"),
     *                 @OA\Property(property="transmission", type="string"),
     *                 @OA\Property(property="vehicle_type", type="string"),
     *                 @OA\Property(property="doors", type="integer"),
     *                 @OA\Property(property="exterior_color", type="string"),
     *                 @OA\Property(property="material_interior", type="string"),
     *                 @OA\Property(property="exterior_equipment", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="interior", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="security", type="array", @OA\Items(type="string")),
     *                 // Phone specific fields
     *                 @OA\Property(property="phone_color", type="string"),
     *                 @OA\Property(property="phone_condition", type="string"),
     *                 @OA\Property(property="device", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Advert updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/Advert")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updateAdvert(Request $request, $advertId)
    {
        $user = auth()->user();
        $subcat = (int) $request->input('subcategory');
        $category = (int) $request->input('category');

        $advert = Advert::where('id', $advertId)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found'
            ], 404);
        }

        $this->normalizeStateInput($request);

        // Use dynamic validation service based on Category UI Config
        $validationService = new AdvertValidationService();
        $rules = $validationService->getRules($category, $subcat, true, false, $user->acc_type); // true = isUpdate

        $messages = [
            'state.exists'                => 'Please select a valid state.',
            'lga.exists'                  => 'Please select a valid LGA.',
            'price.required_unless'       => 'Please enter a price, or select "Contact for Price".',
            'price.gt'                    => 'Please enter a price greater than 0, or select "Contact for Price".',
            'price_type.required_unless'  => 'Please select a price type.',
            'condition.required'          => 'Please select the vehicle condition.',
            'registration.required'       => 'Please select the vehicle registration status.',
            'fuel.required'               => 'Please select the fuel type.',
            'transmission.required'       => 'Please select the transmission type.',
            'vehicle_type.required'       => 'Please select the body/vehicle type.',
            'exterior_color.required'     => 'Please select the exterior color.',
            'model.required'              => 'Please select the vehicle model.',
            'model.exists'                => 'The selected model is invalid.',
            'model.min'                   => 'Please select a valid vehicle model.',
            'phone_color.required'        => 'Please select the phone color.',
            'phone_condition.required'    => 'Please select the phone condition.',
            'device.required'             => 'Please select the device type.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->has('buy_direct') && ($buyDirectError = $validationService->validateShipping($request, $user))) {
            return response()->json(['success' => false, 'errors' => $buyDirectError], 422);
        }

        if ($reason = ContentHelper::detectBannedContact($request->input('ad_title'))) {
            return response()->json([
                'success' => false,
                'errors'  => ['ad_title' => ["Your ad title appears to contain {$reason}. For your safety, please don't share phone numbers or contact details in the ad text — buyers can reach you directly through in-app messaging once your ad is live."]],
            ], 422);
        }

        if ($reason = ContentHelper::detectBannedContact($request->input('description'))) {
            return response()->json([
                'success' => false,
                'errors'  => ['description' => ["Your description appears to contain {$reason}. For your safety, please don't share phone numbers or contact details in the ad text — buyers can reach you directly through in-app messaging once your ad is live."]],
            ], 422);
        }

        try {
            DB::beginTransaction();

            // ✅ Capture old price BEFORE update for price change notification
            $oldPrice = $advert->getOriginal('price');

            $metaDescription = Str::limit(strip_tags($request->input('description')), 150, '');
            $rawWords = explode(' ', Str::slug($request->input('ad_title') . ' ' . $request->input('description'), ' '));
            $filteredWords = array_filter($rawWords, function ($word) {
                return strlen($word) > 3;
            });
            $uniqueWords = array_unique($filteredWords);
            $keywords = implode(', ', array_slice($uniqueWords, 0, 10));

            $adTitleUpdate = ContentHelper::sanitizeTitle($request->input('ad_title', ''));

            // Update main advert
            $advert->update([
                'ad_title'   => $adTitleUpdate ?: $advert->ad_title,
                'title_slug' => Str::slug($adTitleUpdate ?: $advert->ad_title),
                'ad_type'    => $request->input('ad_type', $advert->ad_type),
                'category' => $request->input('category'),
                'sub_category' => $request->input('subcategory'),
                'brand' => $request->input('brand'),
                'price' => $request->input('price'),
                'price_type' => $request->input('price_type'),
                'contact_price' => $request->input('contact_price'),
                'salary' => $request->input('salary'),
                'expected_salary' => $request->input('expected_salary'),
                'item_condition' => $request->input('item_condition'),
                'buy_direct' => $request->has('buy_direct') ? (strtolower($request->input('buy_direct')) === 'yes' ? 'Yes' : 'No') : $advert->buy_direct,
                'state' => $request->input('state'),
                'lga' => $request->input('lga'),
                'state_slug' => Str::slug($request->input('lga')),
                'description' => ContentHelper::sanitizeDescription($request->input('description')),
                'featured' => $advert->featured,
                'keyword' => $keywords,
                'meta_description' => $metaDescription,
                'shipment' => $request->input('shipment', $advert->shipment),
                'show_contact' => $request->input('show_contact', $advert->show_contact ?? 'No'),
                'quantity' => $request->input('quantity') ?? 1,
            ]);

            ActivityLog::record('advert', 'Edited advert "' . $advert->ad_title . '"', $user, $advert, ['source' => 'app']);

            $advert->shippings()->sync($request->input('shipping', []));

            // Handle deleted images
            if ($request->has('deleted_images')) {
                $deletedImages = $request->input('deleted_images');
                // Ensure it's an array
                if (!is_array($deletedImages)) {
                    $deletedImages = explode(',', $deletedImages);
                }
                $deletedImages = array_filter(array_map('intval', $deletedImages));

                if (!empty($deletedImages)) {
                    $effectiveMinImages = in_array($category, [3, 18]) ? 0 : (int) AdSetting::getValue('min_images', 3);

                    $result = $this->getImageService()->validateAndDeleteImages(
                        $advert,
                        $deletedImages,
                        $effectiveMinImages,
                        'images'
                    );

                    if (!$result['success']) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'errors' => ['deleted_images' => [$result['error']]]
                        ], 422);
                    }
                }
            }

            // Handle image reordering (Spatie media order_column)
            $orderedIds = [];
            if ($request->filled('existing_image_order')) {
                $orderedIds = explode(',', $request->input('existing_image_order'));
                foreach ($orderedIds as $index => $mediaId) {
                    \Spatie\MediaLibrary\MediaCollections\Models\Media::where('id', (int) $mediaId)
                        ->where('model_id', $advert->id)
                        ->where('model_type', get_class($advert))
                        ->where('collection_name', 'images')
                        ->update(['order_column' => $index + 1]);
                }
            }

            $newPositionStart = count($orderedIds) > 0
                ? count($orderedIds) + 1
                : ($advert->getMedia('images')->count() + 1);

            // Upload new images via Spatie MediaLibrary
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    if ($image->isValid()) {
                        $position = $newPositionStart + $index;
                        $media = $advert
                            ->addMedia($image)
                            ->withCustomProperties(['position' => $position])
                            ->usingFileName(uniqid() . '.webp')
                            ->toMediaCollection('images');
                        $media->order_column = $position;
                        $media->save();
                    }
                }
            }

            // Defense-in-depth: the deletion guard above already prevents a request
            // from dropping below the minimum, so this should only trip for adverts
            // that were already under the minimum before this request.
            $finalMinImages = in_array($category, [3, 18]) ? 0 : (int) AdSetting::getValue('min_images', 3);
            $finalImageCount = $advert->getMedia('images')->count();

            if ($finalImageCount < $finalMinImages) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'errors' => ['images' => ["Advert must have at least {$finalMinImages} image" . ($finalMinImages === 1 ? '' : 's') . ". Please upload more images."]]
                ], 422);
            }

            // Update Car details — subcats: 2=Cars, 21=Buses & Minibuses, 23=Trucks & Trailers
            if (in_array($subcat, [2, 21, 23])) {
                $carData = [
                    'cat_id'             => $request->input('category'),
                    'brand_id'           => $request->input('brand'),
                    'model'              => $request->input('model'),
                    'mileage'            => $request->input('mileage'),
                    'condition'          => $request->input('condition'),
                    'registration'       => $request->input('registration'),
                    'fuel'               => $request->input('fuel'),
                    'transmission'       => $request->input('transmission'),
                    'vehicle_type'       => $request->input('vehicle_type'),
                    'doors'              => $request->input('doors'),
                    'exterior_color'     => $request->input('exterior_color'),
                    'material_interior'  => $request->input('material_interior'),
                    'exterior_equipment' => json_encode($request->input('exterior_equipment', [])),
                    'interior'           => json_encode($request->input('interior', [])),
                    'security'           => json_encode($request->input('security', [])),
                ];
                if ($advert->car) {
                    $advert->car->update($carData);
                } else {
                    $car = new CarDetail(array_merge($carData, ['car_id' => rand(10000, 99999)]));
                    $car->advert()->associate($advert);
                    $car->save();
                }
            }

            // Update Phone details (upsert: update if exists, create if missing)
            if ($subcat === 6) {
                $phoneData = [
                    'cat_id'    => $request->input('category'),
                    'brand_id'  => $request->input('brand'),
                    'model'     => $request->input('model'),
                    'color'     => $request->input('phone_color'),
                    'device'    => $request->input('device'),
                    'condition' => $request->input('phone_condition'),
                ];
                if ($advert->phone) {
                    $advert->phone->update($phoneData);
                } else {
                    $phone = new PhoneDetail(array_merge($phoneData, ['phone_id' => rand(10000, 99999)]));
                    $phone->advert()->associate($advert);
                    $phone->save();
                }
            }

            DB::commit();

            // Notify followers of price change after response is sent (no queue worker required)
            $seller = auth()->user();
            if ($advert->price != $oldPrice && $seller) {
                PostAdvertJob::dispatchAfterResponse(
                    $advert,
                    $seller,
                    'Price Update',
                    $seller->name . ' updated the price of ' . $advert->ad_title
                );

                Log::info('API: Price update notification dispatched after response', [
                    'advert_id' => $advert->ad_id,
                    'seller_id' => $seller->id,
                    'old_price' => $oldPrice,
                    'new_price' => $advert->price
                ]);
            }

            $advert->load(['images', 'car', 'phone', 'shippings']);

            return response()->json([
                'success' => true,
                'message' => 'Advert updated successfully',
                'data' => $advert
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Advert update failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while updating your advert. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/adverts/{advertId}",
     *     summary="Delete an advert",
     *     tags={"Advert Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="advertId",
     *         in="path",
     *         required=true,
     *         description="Advert ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Advert deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Advert not found"
     *     )
     * )
     */
    public function deleteAdvert($advertId)
    {
        $user = auth()->user();

        try {
            DB::beginTransaction();

            $advert = Advert::where('id', $advertId)
                ->where('user_id', $user->user_id)
                ->first();

            if (!$advert) {
                return response()->json([
                    'success' => false,
                    'message' => 'Advert not found'
                ], 404);
            }

            // Delete media images except jobs/CV categories
            if (!in_array($advert->category, [3, 18])) {
                $advert->clearMediaCollection('images');
            }

            $adTitle = $advert->ad_title;
            $advert->delete();

            ActivityLog::record('advert', 'Deleted advert "' . $adTitle . '"', $user, $advert, ['source' => 'app']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Advert deleted successfully'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Advert deletion failed', [
                'advert_id' => $advertId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete advert'
            ], 500);
        }
    }



    /**
     * @OA\Get(
     *     path="/api/adverts/{advertId}/boost",
     *     summary="Get boost information for an advert",
     *     tags={"Advert Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="advertId",
     *         in="path",
     *         required=true,
     *         description="Advert ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Boost information",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="advert", ref="#/components/schemas/Advert"),
     *                 @OA\Property(property="price", type="number")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Advert not found"
     *     )
     * )
     */
    public function getBoostInfo($advertId)
    {
        $user = auth()->user();

        $advert = Advert::with(['images', 'car', 'phone'])
            ->where('id', $advertId)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'advert' => $advert,
                'price' => 500 // Fixed boost price
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/adverts/{advertId}/boosted",
     *     summary="Get boosted advert information",
     *     tags={"Advert Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="advertId",
     *         in="path",
     *         required=true,
     *         description="Advert ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Boosted advert information",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="advert", ref="#/components/schemas/Advert"),
     *                 @OA\Property(property="price", type="number")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Advert not found"
     *     )
     * )
     */
    public function getBoostedAdvert($advertId)
    {
        $user = auth()->user();

        $advert = Advert::with(['images', 'car', 'phone', 'boost'])
            ->where('id', $advertId)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'advert' => $advert,
                'price' => 500
            ]
        ]);
    }

    // If the app sends a numeric state ID instead of the state name, resolve it
    // to the canonical name so it passes validation and stores consistently.
    private function normalizeStateInput(Request $request): void
    {
        $value = $request->input('state');
        if ($value !== null && is_numeric($value)) {
            $name = State::where('id', $value)->value('name');
            if ($name) {
                $request->merge(['state' => $name]);
            }
        }
    }
}
