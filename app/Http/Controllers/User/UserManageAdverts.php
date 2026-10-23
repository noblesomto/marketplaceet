<?php

namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\NewAdMail;
use App\Models\User;
use App\Models\Category;
use App\Models\Message;
use App\Models\SubCategory;
use App\Models\Brands;
use App\Models\Advert;
use App\Models\AdvertBoost;
use App\Models\BoostType;
use App\Models\BoostDuration;
use App\Models\VehicleModel;
use App\Models\State;
use App\Models\Followers;
use App\Models\AdvertImage;
use App\Models\CarDetail;
use App\Models\PhoneDetail;
use App\Models\Shipping;
use App\Models\Notification;
use App\Helpers\ContentHelper;
use App\Helpers\FileUploadHelper;
use App\Jobs\PostAdvertJob;
use App\Models\AdSetting;
use App\Services\AdvertValidationService;
use App\Services\ImageQualityService;
use App\Traits\ManagesImages;
use App\Traits\HasUserSession;
use App\Support\ActivityLog;

class UserManageAdverts extends Controller
{
    use HasUserSession;
    use ManagesImages;

      public function fetch_subcat($cat_id)
    {
        $subcat = SubCategory::where('cat_id', $cat_id)->get();
        return response()->json($subcat);
    }

    public function fetch_brand($cat_id)
    {
        $brand = Brands::where('subcat_id', $cat_id)->get();
        return response()->json($brand);
    }

    public function post_ad(Request $request)
    {
        $title = "Post New Advert - " . config('global.site_name');
        $seller_id = $user_id = $request->session()->get('user_id');

        // Check if user session exists
        if (!$user_id) {
            return redirect('/login')->with('error', 'Please login to post an ad.');
        }

        $user = User::where('user_id', $user_id)->first();

        // Check if user exists
        if (!$user) {
            $request->session()->forget('user_id');
            return redirect('/login')->with('error', 'User not found. Please login again.');
        }

        $categories = Category::orderBy('category', 'asc')->get();
        $states = State::all();
        $shippings = Shipping::where('status', 'Active')->orderBy('company', 'asc')->get();
        $followers = Followers::with(['user:user_id,id,email,name'])
            ->where('follow', $user_id)
            ->get();

        if ($user->disable_account=='yes') {
            $request->session()->forget('user_id');
            return redirect("login")->with('success', 'Logged Out successfully!');
        }

        if (empty($user->phone)) {
            return redirect('/user/profile-info')->with('error', 'Please Update your Phone number');
        }

        //dd($followers);

        if ($request->isMethod('POST')) {
            // ✅ HANDLE IMAGE UPLOADS EARLY - Store temporarily in case of validation errors
            $tempImages = [];
            if ($request->hasFile('images')) {
                $tempImages = $this->storeTemporaryImages($request->file('images'));
            }

            // ✅ TIER 1: IMAGE QUALITY VALIDATION
            if ($request->hasFile('images')) {
                $imageQualityService = new ImageQualityService();
                $imageQualityErrors = [];
                $imageQualityWarnings = [];
                $totalQualityScore = 0;
                $imageCount = 0;
                $strictness = (int) AdSetting::getValue('image_strictness', 7);

                foreach ($request->file('images') as $image) {
                    $result = $imageQualityService->validateImage($image, $strictness);
                    $imageCount++;

                    // Collect errors
                    if (!$result['valid']) {
                        $imageQualityErrors = array_merge($imageQualityErrors, $result['errors']);
                    }

                    // Collect warnings (but don't block upload)
                    if (!empty($result['warnings'])) {
                        $imageQualityWarnings = array_merge($imageQualityWarnings, $result['warnings']);
                    }

                    $totalQualityScore += $result['score'];
                }

                // If there are quality errors, reject the upload
                if (!empty($imageQualityErrors)) {
                    if (!empty($tempImages)) {
                        $request->session()->flash('temp_images', $tempImages);
                    }

                    // Get recommendations
                    $recommendations = $imageQualityService->getRecommendations([
                        'valid' => false,
                        'details' => [
                            'width' => 0,
                            'sharpness' => 0,
                            'brightness' => 0
                        ]
                    ]);

                    return back()->withErrors([
                        'images' => array_merge(
                            ['Image quality validation failed:'],
                            array_slice($imageQualityErrors, 0, 3), // Show first 3 errors
                            [''],
                            ['Recommendations:'],
                            $recommendations
                        )
                    ])->withInput();
                }

                // Log quality metrics
                $avgScore = $imageCount > 0 ? round($totalQualityScore / $imageCount) : 0;
                \Log::info('Image quality validation passed', [
                    'image_count' => $imageCount,
                    'average_score' => $avgScore,
                    'quality_rating' => $imageQualityService->getQualityRating($avgScore),
                    'warnings_count' => count($imageQualityWarnings)
                ]);
            }

            // ✅ EARLY VALIDATION: Validate only CRITICAL required fields
            // Only validate fields that are ALWAYS required, not conditional fields
            // This prevents 500 errors and avoids validating hidden fields
            try {
                $request->validate([
                    'ad_title'    => 'required|max:75',
                    'description' => ['required', 'max:3500'],
                ], [
                    'ad_title.required'       => 'Ad title is required.',
                    'description.required'    => 'Description is required.',
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                // Validation failed - flash temp images to session
                if (!empty($tempImages)) {
                    $request->session()->flash('temp_images', $tempImages);
                }
                throw $e; // Re-throw to show validation errors
            }

            $subcat = (int) $request->input('subcategory');
            $category = (int) $request->input('category');
            $description = $this->removeEmojis($request->input('description'));
            $request->merge(['description' => $description]);

            // ✅ Check if temp images exist (from previous validation error)
            $hasTempImages = !empty($request->input('temp_image_paths', []));

            // Use dynamic validation service based on Category UI Config
            $validationService = new AdvertValidationService();
            $rules = $validationService->getRules($category, $subcat, false, $hasTempImages, $user->acc_type);
            $imageRequirements = \App\Services\AdvertImageRequirements::get();

            // Custom validation messages
            $messages = [
                'images.required'             => "Please upload at least {$imageRequirements['min_images']} images.",
                'images.min'                  => "Please upload at least {$imageRequirements['min_images']} images.",
                'images.max'                  => "You may upload a maximum of {$imageRequirements['max_images']} images.",
                'images.*.image'              => 'All files must be images.',
                'images.*.mimes'              => 'Images must be ' . implode(', ', $imageRequirements['allowed_formats']) . ' format.',
                'images.*.max'                => 'Each image must not exceed ' . round($imageRequirements['max_file_size_bytes'] / (1024 * 1024)) . 'MB.',
                // Price messages
                'price.required_unless'       => 'Please enter a price, or select "Contact for Price".',
                'price.gt'                    => 'Please enter a price greater than Br 0, or select "Contact for Price".',
                'price_type.required_unless'  => 'Please select a price type.',
                // Car-specific messages
                'condition.required'          => 'Please select the vehicle condition.',
                'registration.required'       => 'Please select the vehicle registration status.',
                'fuel.required'               => 'Please select the fuel type.',
                'transmission.required'       => 'Please select the transmission type.',
                'vehicle_type.required'       => 'Please select the body/vehicle type.',
                'exterior_color.required'     => 'Please select the exterior color.',
                'model.required'              => 'Please select the vehicle model.',
                'model.exists'                => 'The selected model is invalid. Please select a valid model.',
                'model.min'                   => 'Please select a valid vehicle model.',
                // Phone-specific messages
                'phone_color.required'        => 'Please select the phone color.',
                'phone_condition.required'    => 'Please select the phone condition.',
                'device.required'             => 'Please select the device type.',
            ];

            try {
                $validatedData = $request->validate($rules, $messages);
            } catch (\Illuminate\Validation\ValidationException $e) {
                // Validation failed - flash temp images to session
                if (!empty($tempImages)) {
                    $request->session()->flash('temp_images', $tempImages);
                }
                throw $e; // Re-throw to show validation errors
            }

            // Validate shipping requirements
            $shippingError = $validationService->validateShipping($request, $user);
            if ($shippingError) {
                // Flash temp images to session before redirecting
                if (!empty($tempImages)) {
                    $request->session()->flash('temp_images', $tempImages);
                }
                return back()->withErrors($shippingError)->withInput();
            }

            if ($reason = ContentHelper::detectBannedContact($request->input('ad_title'))) {
                if (!empty($tempImages)) {
                    $request->session()->flash('temp_images', $tempImages);
                }
                return back()->withErrors([
                    'ad_title' => "Your ad title appears to contain {$reason}. For your safety, please don't share phone numbers or contact details in the ad text — buyers can reach you directly through in-app messaging once your ad is live.",
                ])->withInput();
            }

            $adTitle = ContentHelper::sanitizeTitle($request->ad_title);

            if (empty($adTitle)) {
                if (!empty($tempImages)) {
                    $request->session()->flash('temp_images', $tempImages);
                }
                return back()->withErrors([
                    'ad_title' => 'Your ad title contains only invalid characters (e.g. phone numbers or special symbols). Please use plain descriptive text.',
                ])->withInput();
            }

            if ($reason = ContentHelper::detectBannedContact($request->input('description'))) {
                if (!empty($tempImages)) {
                    $request->session()->flash('temp_images', $tempImages);
                }
                return back()->withErrors([
                    'description' => "Your description appears to contain {$reason}. For your safety, please don't share phone numbers or contact details in the ad text — buyers can reach you directly through in-app messaging once your ad is live.",
                ])->withInput();
            }

            $adDescription = ContentHelper::sanitizeDescription($request->input('description'));
            $metaDescription = Str::limit($adDescription, 150, '');
            $rawWords = explode(' ', Str::slug($adTitle . ' ' . $metaDescription, ' '));
            $filteredWords = array_filter($rawWords, function ($word) {
            return strlen($word) > 3;
            });

            $uniqueWords = array_unique($filteredWords);

            $keywords = implode(', ', array_slice($uniqueWords, 0, 10));


            $lga     = $request->lga;

            // Duplicate check: same user + same title + same category/subcategory
            // Only blocks active ads OR ads posted within the last 24 hours (anti-abuse window).
            // This allows legitimate re-listing after a sale/deletion without permanently blocking sellers.
            $exists = Advert::where('user_id', $user_id)
                ->where('ad_title', $adTitle)
                ->where('category', $request->input('category'))
                ->where('sub_category', $request->input('subcategory'))
                ->where(function ($q) {
                    $q->where('ad_status', 'active')
                      ->orWhere('created_at', '>=', now()->subHours(24));
                })
                ->exists();

            if ($exists) {
                return redirect('/user/my-ads')->with('error', "You already have an active listing with this title in the same category. Please edit the existing ad or wait before re-listing.");
            }

            DB::beginTransaction();
            try {

            $advert = Advert::create([
                'ad_title'         => $adTitle,
                'title_slug'       => Str::slug($adTitle),
                'ad_type'          => $request->input('ad_type', 'Private'), // ✅ Default to 'Private' if not provided
                'category'         => $request->input('category'),
                'sub_category'     => $request->input('subcategory'),
                'brand'            => $request->input('brand'),
                'price'            => $request->input('price'),
                'contact_price'    => $request->input('contact_price'),
                'salary'           => $request->input('salary'),
                'expected_salary'  => $request->input('expected_salary'),
                'item_condition'   => $request->input('item_condition'),
                'price_type'       => $request->input('price_type'),
                'buy_direct'       => strtolower($request->input('buy_direct', 'No')) === 'yes' ? 'Yes' : 'No',
                'state'            => $request->input('state'),
                'lga'              => $request->input('lga'),
                'state_slug'       => Str::slug($request->input('lga')),
                'description'      => $adDescription,
                'keyword'          => $keywords,
                'meta_description' => $metaDescription,
                'featured'         => "No",
                'shipment'         => $request->input('shipment', 'Pickup'), // ✅ Default to 'Pickup' if not provided
                'show_contact'     => $request->input('show_contact', 'No'), // ✅ Default to 'No' if not provided
                'quantity'         => $request->input('quantity') ?? 1,
                'views'            => "0",
                'ad_status'        => 'active',
                'user_id'          => $user_id,
                'source'           => 'web',
            ]);

            $advert->update(['ad_id' => (string) $advert->id]);

            ActivityLog::record('advert', 'Posted advert "' . $advert->ad_title . '"', $user, $advert, [
                'source' => 'web',
            ]);

            $advert->shippings()->sync($request->input('shipping', []));

            // ✅ Handle image uploads - Priority: temp images > new uploads > default
            $tempImagePaths = $request->input('temp_image_paths', []);
            $hasNewImages = $request->hasFile('images');
            $hasTempImages = !empty($tempImagePaths);

            \Log::debug("Image Processing Debug", [
                'temp_paths' => $tempImagePaths,
                'has_new_images' => $hasNewImages,
                'has_temp_images' => $hasTempImages,
            ]);

            if ($hasTempImages || $hasNewImages) {
                $imagesToProcess = [];

                // First, collect temp images
                if ($hasTempImages) {
                    foreach ($tempImagePaths as $tempPath) {
                        \Log::debug("Checking temp image", ['path' => $tempPath, 'exists' => \Storage::disk('public')->exists($tempPath)]);
                        if (\Storage::disk('public')->exists($tempPath)) {
                            $imagesToProcess[] = [
                                'type' => 'temp',
                                'path' => $tempPath,
                            ];
                        }
                    }
                }

                // Then, add new uploads
                if ($hasNewImages) {
                    foreach ($request->file('images') as $image) {
                        if ($image && $image->isValid()) {
                            $imagesToProcess[] = [
                                'type' => 'new',
                                'file' => $image,
                            ];
                        }
                    }
                }

                \Log::debug("Images to process", ['count' => count($imagesToProcess)]);

                // Process all images
                $this->processAdvertImages($imagesToProcess, $advert);
            } elseif (in_array($request->input('category'), [3, 18])) {
                // Add default image for jobs/CV categories
                $advert->addDefaultImage('jobs.png');
            }

            // ✅ Validate final image count - prevents ads going live with missing images
            // (e.g. client-submitted temp_image_paths that expired/no longer exist on disk)
            if (!in_array($request->input('category'), [3, 18])) {
                $minImages = (int) AdSetting::getValue('min_images', 3);
                $finalImageCount = $advert->getMedia('images')->count();

                if ($finalImageCount < $minImages) {
                    DB::rollBack();
                    if (!empty($tempImages)) {
                        $request->session()->flash('temp_images', $tempImages);
                    }
                    return back()->withErrors([
                        'images' => "Advert must have at least {$minImages} image" . ($minImages === 1 ? '' : 's') . ". Please upload more images."
                    ])->withInput();
                }
            }

            // Store car-specific info
            if (in_array($subcat, [2, 21, 23])) {
                $car = new CarDetail([
                    'car_id'            => rand(10000, 99999),
                    'cat_id'            => $request->input('category'),
                    'brand_id'          => $request->input('brand'),
                    'model'             => $request->input('model'),
                    'mileage'           => $request->input('mileage'),
                    'condition'         => $request->input('condition'),
                    'registration'      => $request->input('registration'),
                    'fuel'              => $request->input('fuel'),
                    'transmission'      => $request->input('transmission'),
                    'vehicle_type'      => $request->input('vehicle_type'),
                    'doors'             => $request->input('doors'),
                    'exterior_color'    => $request->input('exterior_color'),
                    'material_interior' => $request->input('material_interior'),
                    'exterior_equipment'=> json_encode($request->input('exterior_equipment')),
                    'interior'          => json_encode($request->input('interior')),
                    'security'          => json_encode($request->input('security')),
                ]);
                $car->advert()->associate($advert);
                $car->save();
            }

            // Store phone-specific info
            if ($subcat === 6) {
                $phone = new PhoneDetail([
                    'phone_id'  => rand(10000, 99999),
                    'cat_id'    => $request->input('category'),
                    'brand_id'  => $request->input('brand'),
                    'model'     => $request->input('model'),
                    'color'     => $request->input('phone_color'),
                    'device'    => $request->input('device'),
                    'condition' => $request->input('phone_condition'),
                ]);
                $phone->advert()->associate($advert);
                $phone->save();
            }

            DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Advert creation failed: ' . $e->getMessage());
                return back()->withErrors(['error' => 'Something went wrong while posting your advert. Please try again.'])->withInput();
            }

            $user_id = $request->session()->get('user_id');
            $seller  = User::where('user_id', $user_id)->first();

            // Notify followers after response is sent (no queue worker required)
            if ($seller) {
                PostAdvertJob::dispatchAfterResponse(
                    $advert,
                    $seller,
                    'New Ad',
                    $seller->name . ' has placed the ad "' . $advert->ad_title . '"',
                );
            }


            // Handle promotion
            if ($request->has('promotion')) {
                $request->session()->put('promotion', $request->promotion);
                return redirect('/user/post-boost-ad/' . $advert->id);
            }

            return redirect('/user/my-ads')->with('success', 'Your Advert Has successfully been Posted');
        }

        if ($request->isMethod('GET')) {
            // Get active boost types and durations for optional boost during post
            $boostTypes = BoostType::active()->ordered()->get();
            $boostDurations = BoostDuration::active()->ordered()->get();
            $minImages = (int) AdSetting::getValue('min_images', 3);
            $maxImages = (int) AdSetting::getValue('max_images', 8);

            return view('user.post-ad', compact('title', 'categories', 'user', 'shippings', 'states', 'boostTypes', 'boostDurations', 'minImages', 'maxImages'));
        }
    }


    public function edit_ad(Request $request, $ad_id)
    {
        $title = "Edit Advert - " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::orderBy('category','asc')->get();
        $states = State::all();
        $shippings = Shipping::where('status', 'Active')->orderBy('company', 'asc')->get();
        $advert = Advert::with(['images', 'car', 'phone'])
            ->where('id', $ad_id)
            ->where('user_id', $user_id)
            ->firstOrFail();

        // Load dependent data
        $subcategories = SubCategory::where('cat_id', $advert->category)->get();
        $brands = Brands::where('subcat_id', $advert->sub_category)->get();

        $models = collect();
        if ($advert->brand) {
            $models = VehicleModel::where('brand_id', $advert->brand)->get();
        }
        //dd($models);

        // Parse car registration if exists
        $registration = [];
        if ($advert->car_details) {
            $regParts = explode(' ', $advert->car_details->registration);
            $registration = [
                'month' => $regParts[0] ?? '',
                'year' => $regParts[1] ?? ''
            ];
        }

        if ($request->isMethod('POST')) {
            return $this->update_ad($request, $advert, $user);
        }

        $minImages = (int) AdSetting::getValue('min_images', 3);
        $maxImages = (int) AdSetting::getValue('max_images', 8);

        return view('user.edit-ad', compact(
            'title',
            'categories',
            'user',
            'advert',
            'subcategories',
            'brands',
            'models',
            'registration',
            'states',
            'shippings',
            'minImages',
            'maxImages'
        ));
    }

    protected function update_ad(Request $request, $advert, $user)
    {
        $subcat = (int) $request->input('subcategory');
        $category = (int) $request->input('category');
        $oldPrice = $advert->getOriginal('price');

        $minImages  = (int) AdSetting::getValue('min_images', 3);
        $maxImages  = (int) AdSetting::getValue('max_images', 8);
        $strictness = (int) AdSetting::getValue('image_strictness', 7);

        $description = $this->removeEmojis($request->input('description'));
        $request->merge(['description' => $description]);

        // Use dynamic validation service based on Category UI Config
        $validationService = new AdvertValidationService();
        $rules = $validationService->getRules($category, $subcat, true, false, $user->acc_type); // true = isUpdate

        $validatedData = $request->validate($rules, [
            'phone_color.required'       => 'Please select the phone color.',
            'phone_condition.required'   => 'Please select the phone condition.',
            'device.required'            => 'Please select the device type.',
            'price.required_unless'      => 'Please enter a price, or select "Contact for Price".',
            'price.gt'                   => 'Please enter a price greater than Br 0, or select "Contact for Price".',
            'price_type.required_unless' => 'Please select a price type.',
        ]);
        if ($request->has('buy_direct') && ($buyDirectError = $validationService->validateShipping($request, $user))) {
            return back()->withErrors($buyDirectError)->withInput();
        }

        if ($reason = ContentHelper::detectBannedContact($request->input('ad_title'))) {
            return back()->withErrors([
                'ad_title' => "Your ad title appears to contain {$reason}. For your safety, please don't share phone numbers or contact details in the ad text — buyers can reach you directly through in-app messaging once your ad is live.",
            ])->withInput();
        }

        $adTitle = ContentHelper::sanitizeTitle($request->input('ad_title'));

        if ($reason = ContentHelper::detectBannedContact($request->input('description'))) {
            return back()->withErrors([
                'description' => "Your description appears to contain {$reason}. For your safety, please don't share phone numbers or contact details in the ad text — buyers can reach you directly through in-app messaging once your ad is live.",
            ])->withInput();
        }

        $adDescrition = ContentHelper::sanitizeDescription($request->input('description'));
        $metaDescription = Str::limit($adDescrition, 150, '');
        $rawWords = explode(' ', Str::slug($adTitle . ' ' . $adDescrition, ' '));
        $filteredWords = array_filter($rawWords, function ($word) {
        return strlen($word) > 3;
        });

        $uniqueWords = array_unique($filteredWords);

        $keywords = implode(', ', array_slice($uniqueWords, 0, 10));

        //dd($keywords);
        // Update main advert
        $advert->update([
            'ad_title'        => $adTitle,
            'title_slug'      => Str::slug($adTitle),
            'ad_type'         => $request->input('ad_type'),
            'category'        => $request->input('category'),
            'sub_category'    => $request->input('subcategory'),
            'brand'           => $request->input('brand'),
            'price'           => $request->input('price'),
            'price_type'      => $request->input('price_type'),
            'contact_price'    => $request->input('contact_price'),
            'salary'           => $request->input('salary'),
            'expected_salary'  => $request->input('expected_salary'),
            'item_condition'  => $request->input('item_condition'),
            'buy_direct'      => $request->has('buy_direct') ? (strtolower($request->input('buy_direct')) === 'yes' ? 'Yes' : 'No') : $advert->buy_direct,
            'state'           => $request->input('state'),
            'lga'             => $request->input('lga'),
            'state_slug'      => Str::slug($request->input('lga')),
            'description'       => $adDescrition,
            'featured'         => $advert->featured,
            'keyword'         => $keywords,
            'meta_description'=> $metaDescription,
            'shipment'        => $request->input('shipment'),
            'show_contact'    => $request->input('show_contact'),
            'quantity'        => $request->input('quantity') ?? 1,
        ]);

        ActivityLog::record('advert', 'Edited advert "' . $advert->ad_title . '"', $user, $advert);

        $messages = [];


        // Handle deleted images first
        if ($request->has('deleted_images') && !empty($request->input('deleted_images'))) {
            $deletedImages = $request->input('deleted_images');
            // Ensure it's an array
            if (!is_array($deletedImages)) {
                $deletedImages = explode(',', $deletedImages);
            }
            $deletedImages = array_filter(array_map('intval', $deletedImages));

            if (!empty($deletedImages)) {
                $effectiveMinImages = in_array($category, [3, 18]) ? 0 : $minImages;

                $result = $this->getImageService()->validateAndDeleteImages(
                    $advert,
                    $deletedImages,
                    $effectiveMinImages,
                    'images'
                );

                if (!$result['success']) {
                    return redirect()->back()->withErrors([
                        'deleted_images' => $result['error']
                    ]);
                }

                if ($result['deleted'] > 0) {
                    $messages[] = "{$result['deleted']} image(s) deleted";
                }
            }
        }


        // Upload new images BEFORE reordering existing ones
        $newImagesUploaded = 0;
        if ($request->hasFile('images')) {
            // Quality validation for newly added images
            $imageQualityService = new ImageQualityService();
            $qualityErrors = [];
            foreach ($request->file('images') as $image) {
                $result = $imageQualityService->validateImage($image, $strictness);
                if (!$result['valid']) {
                    $qualityErrors = array_merge($qualityErrors, $result['errors']);
                }
            }
            if (!empty($qualityErrors)) {
                return redirect()->back()->withErrors([
                    'images' => array_merge(['Image quality validation failed:'], array_slice($qualityErrors, 0, 3)),
                ])->withInput();
            }

            // Max images guard: current (after deletions) + new must not exceed maxImages
            $currentAfterDeletion = $advert->fresh()->getMedia('images')->count();
            $incomingCount = count($request->file('images'));
            if (!in_array($category, [3, 18]) && ($currentAfterDeletion + $incomingCount) > $maxImages) {
                return redirect()->back()->withErrors([
                    'images' => "Too many images. Maximum allowed is {$maxImages}. You currently have {$currentAfterDeletion} and are uploading {$incomingCount} more.",
                ])->withInput();
            }

            \Log::info('Files received for upload:', [
                'count' => count($request->file('images')),
                'files' => array_map(function($file) {
                    return $file ? $file->getClientOriginalName() : 'null';
                }, $request->file('images'))
            ]);

            // Get current image count to set proper positions for new images
            $currentImageCount = $advert->getMedia('images')->count();

            $uploadedMedia = $this->handleImageUploads($request, $advert, 'images', false);
            $newImagesUploaded = count($uploadedMedia);

            if ($newImagesUploaded > 0) {
                $messages[] = "{$newImagesUploaded} new image(s) uploaded";

                // Set positions for new images starting after existing ones
                foreach ($uploadedMedia as $index => $media) {
                    $media->order_column = $currentImageCount + $index + 1;
                    $media->setCustomProperty('position', $currentImageCount + $index + 1);
                    $media->save();
                }
            } else {
                \Log::warning('No images were uploaded despite files being present');
            }
        }

        // Handle image reordering AFTER uploads
        if ($request->filled('existing_image_order')) {
            $orderedIds = explode(',', $request->input('existing_image_order'));
            $orderedIds = array_filter(array_map('intval', $orderedIds));

            if (!empty($orderedIds)) {
                // Get all current media IDs (including newly uploaded ones)
                $allCurrentMediaIds = $advert->getMedia('images')->pluck('id')->toArray();

                // Add any new media IDs that aren't in the existing order
                $newMediaIds = array_diff($allCurrentMediaIds, $orderedIds);
                $finalOrder = array_merge($orderedIds, $newMediaIds);

                $reordered = $this->getImageService()->reorderImages($advert, $finalOrder, 'images');
                if ($reordered) {
                    $messages[] = "Images reordered";
                }
            }
        } else if ($newImagesUploaded > 0) {
            // If no specific order given but new images uploaded, reorder all to ensure consistent numbering
            $allMediaIds = $advert->getMedia('images')->sortBy('order_column')->pluck('id')->toArray();
            $this->getImageService()->reorderImages($advert, $allMediaIds, 'images');
        }

        // Validate final image count
        $finalImageCount = $advert->getMedia('images')->count();

        if ($finalImageCount < $minImages) {
            if (in_array($category, [3, 18])) {
                // Add default image for jobs/CV categories if no images exist
                if ($finalImageCount < 1) {
                    $advert->addDefaultImage('jobs.png');
                    $messages[] = "Default job image added";
                }
            } else {
                return redirect()->back()->withErrors([
                    'images' => "Advert must have at least {$minImages} image" . ($minImages === 1 ? '' : 's') . ". Please upload more images."
                ])->withInput();
            }
        }

        // Update Car details (upsert: update if exists, create if missing)
        if (in_array($subcat, [2, 21, 23])) {
            $carData = [
                'cat_id'            => $request->input('category'),
                'brand_id'          => $request->input('brand'),
                'model'             => $request->input('model'),
                'mileage'           => $request->input('mileage'),
                'condition'         => $request->input('condition'),
                'registration'      => $request->input('registration'),
                'fuel'              => $request->input('fuel'),
                'transmission'      => $request->input('transmission'),
                'vehicle_type'      => $request->input('vehicle_type'),
                'doors'             => $request->input('doors'),
                'exterior_color'    => $request->input('exterior_color'),
                'material_interior' => $request->input('material_interior'),
                'exterior_equipment'=> json_encode($request->input('exterior_equipment')),
                'interior'          => json_encode($request->input('interior')),
                'security'          => json_encode($request->input('security')),
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

        $user_id = $request->session()->get('user_id');
        $seller  = User::where('user_id', $user_id)->first();


        // Notify followers of price change after response is sent (no queue worker required)
        if ($advert->price != $oldPrice) {
            PostAdvertJob::dispatchAfterResponse(
                $advert,
                $seller,
                'Price Update',
                $seller->name . ' updated the price of ' . $advert->ad_title
            );
        }

        $message = 'Advert updated successfully';
        if (!empty($messages)) {
            $message .= ': ' . implode(', ', $messages);
        }
        return redirect('/user/my-ads')->with('success', $message);
    }



       public function delete_ad(Request $request, $id)
    {
        try {
            \DB::beginTransaction();

            $user_id = $request->session()->get('user_id');
            // Scoped to the owner — without this any logged-in user could delete
            // any other user's advert by guessing/incrementing the id.
            $advert = Advert::where('id', $id)->where('user_id', $user_id)->first();
            if (!$advert) {
                \DB::rollBack();
                return redirect()->back()->with('error', 'Advert not found');
            }

            $adTitle = $advert->ad_title;
            $user = User::where('user_id', $user_id)->first();

            // Delete the advert - the model event will handle image deletion
            // based on category (Job category preserves default images)
            $advert->delete();

            ActivityLog::record('advert', 'Deleted advert "' . $adTitle . '"', $user, $advert);

            \DB::commit();
            return redirect()->back()->with('success', 'Advert deleted successfully');

        } catch (\Exception $e) {
            \DB::rollBack();

            \Log::error('Failed to delete advert', [
                'advert_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Failed to delete advert');
        }
    }

    private function removeEmojis($text)
    {
        // Return empty string if text is null or empty
        if ($text === null || $text === '') {
            return '';
        }

        // Remove emojis using regex
        return preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{1F004}\x{1F0CF}\x{1F18E}\x{1F191}-\x{1F19A}\x{1F201}\x{1F21A}\x{1F22F}\x{1F232}-\x{1F236}\x{1F238}-\x{1F23A}\x{1F250}\x{1F251}]/u', '', $text);
    }

    /**
     * Store uploaded images temporarily in case of validation errors
     * This allows images to be retained when form validation fails
     *
     * @param array $images Array of uploaded files
     * @return array Array of temp image data
     */
    private function storeTemporaryImages($images)
    {
        $tempImages = [];
        $tempDir = 'temp/post-ad-images';

        // Create temp directory if it doesn't exist
        if (!\Storage::disk('public')->exists($tempDir)) {
            \Storage::disk('public')->makeDirectory($tempDir);
        }

        foreach ($images as $index => $image) {
            if ($image && $image->isValid()) {
                // Generate unique filename
                $filename = uniqid('temp_') . '_' . time() . '_' . $index . '.' . $image->getClientOriginalExtension();

                // Store in temp directory
                $path = $image->storeAs($tempDir, $filename, 'public');

                if ($path) {
                    $tempImages[] = [
                        'path' => $path,
                        'url' => \Storage::disk('public')->url($path),
                        'original_name' => $image->getClientOriginalName(),
                        'size' => $image->getSize(),
                    ];
                }
            }
        }

        return $tempImages;
    }

    /**
     * Move temporary images to permanent advert images
     *
     * @param array $tempImages Array of temp image data
     * @param Advert $advert The advert model
     * @return void
     */
    /**
     * Process both temp and new images for an advert using Spatie Media Library
     *
     * @param array $images Array of images to process (temp and new)
     * @param Advert $advert The advert model
     * @return void
     */
    private function processAdvertImages($images, $advert)
    {
        \Log::debug("processAdvertImages called", [
            'advert_id' => $advert->ad_id,
            'images_count' => count($images)
        ]);

        foreach ($images as $index => $imageData) {
            try {
                $position = $index + 1;

                if ($imageData['type'] === 'temp') {
                    $tempPath = $imageData['path'];
                    $fullPath = \Storage::disk('public')->path($tempPath);

                    \Log::debug("Processing temp image", ['index' => $index, 'exists' => file_exists($fullPath)]);

                    if (file_exists($fullPath)) {
                        $media = $advert
                            ->addMedia($fullPath)
                            ->withCustomProperties([
                                'position' => $position,
                                'source' => 'temp'
                            ])
                            ->usingFileName(uniqid() . '.webp')
                            ->toMediaCollection('images');

                        $media->order_column = $position;
                        $media->save();

                        @unlink($fullPath);
                    } else {
                        \Log::warning("Temp image not found, skipping", [
                            'advert_id' => $advert->ad_id,
                            'index' => $index,
                        ]);
                    }
                } elseif ($imageData['type'] === 'new') {
                    $file = $imageData['file'];

                    \Log::debug("Processing new upload", ['index' => $index]);

                    $media = $advert
                        ->addMedia($file)
                        ->withCustomProperties([
                            'position' => $position,
                            'original_name' => $file->getClientOriginalName()
                        ])
                        ->usingFileName(uniqid() . '.webp')
                        ->toMediaCollection('images');

                    $media->order_column = $position;
                    $media->save();
                }

            } catch (\Exception $e) {
                \Log::error("Failed to process image {$index} for advert {$advert->ad_id}: " . $e->getMessage());
                continue;
            }
        }

        $mediaCount = $advert->getMedia('images')->count();
        \Log::info("Advert images processed", [
            'advert_id' => $advert->ad_id,
            'count' => $mediaCount,
        ]);
    }



}
