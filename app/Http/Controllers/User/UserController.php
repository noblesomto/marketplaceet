<?php

namespace App\Http\Controllers\User;
use App\Services\BuyDirectEscrow;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use App\Models\ArchivedMessage;
use App\Models\Advert;
use App\Models\AdvertBoost;
use App\Models\State;
use App\Models\Feedback;
use App\Models\Payment;
use App\Models\Wishlist;
use App\Models\Category;
use App\Models\Followers;
use App\Models\Notification;
use App\Models\ReportUser;
use Carbon\Carbon;
use App\Rules\ReCaptcha;
use Mail;
use App\Mail\ReportMail;
use App\Traits\HasUserSession;

class UserController extends Controller
{
    use HasUserSession;
        public function index(Request $request)
    {
        $title = "User Dashboard - " . config('global.site_name');
        $user = $this->getUserFromSession();
        $user_id = $user->user_id;
        $count_ads = Advert::where('user_id', $user_id)->count();
        $ads = Advert::with('firstImage')
                    ->orderBy('created_at', 'desc')
                    ->where('user_id', $user_id)
                    ->paginate(20);

        $hasMore = $ads->hasMorePages();

        return view('user.index', compact('title', 'user', 'ads', 'count_ads', 'hasMore'));
    }

    public function my_ads(Request $request)
    {
        $title = "My Ads | " . config('global.site_name');
        $user = $this->getUserFromSession();
        $user_id = $user->user_id;
        $ads = Advert::with(['firstImage', 'moderationLogs'])
                    ->withCount(['media as images_count' => fn ($q) => $q->where('collection_name', 'images')])
                    ->orderBy('created_at', 'desc')
                    ->where('user_id', $user_id)
                    ->paginate(20);
        $count_ads = Advert::where('user_id', $user_id)->count();

        $hasMore = $ads->hasMorePages();

        return view('user.my-ads', compact('title', 'user', 'ads', 'count_ads', 'hasMore'));
    }

    public function loadMoreUserAds(Request $request)
    {
        $user_id = $request->session()->get('user_id');

        if (!$user_id) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }

        $ads = Advert::with(['firstImage', 'moderationLogs'])
                    ->withCount(['media as images_count' => fn ($q) => $q->where('collection_name', 'images')])
                    ->orderBy('created_at', 'desc')
                    ->where('user_id', $user_id)
                    ->paginate(20);

        return response()->json([
            'html' => view('user.components.my-ads', ['ads' => $ads])->render(),
            'hasMore' => $ads->hasMorePages()
        ]);
    }

    public function category(Request $request)
    {   
        $title = "User Dashboard  - " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('users.user_id', $user_id)->first();
        $categories = Category::orderBy('category','asc')->get();
        
        return view('user.category', compact('title','user','categories'));
    }


    public function messages(Request $request)
    {
        $title = "User Dashboard  - " . config('global.site_name');
        $userId = $request->session()->get('user_id');
        $user = User::where('users.user_id', $userId)->first();

        if (!$user) {
            return redirect('/login')->with('error', 'Please login to view messages');
        }

        $count_ads = Advert::where('user_id', $userId)->count();

        // Get archived conversation keys for this user (using user_id string)
        $archivedConversations = ArchivedMessage::where('user_id', $userId)
            ->get()
            ->map(function($archive) {
                return $archive->advert_id . '_' . $archive->other_user_id;
            })
            ->toArray();

        // Fetch distinct conversations for the logged-in user as either sender or receiver
        $allConversations = Message::select(
                DB::raw("(CASE WHEN sender_id = '{$userId}' THEN receiver_id ELSE sender_id END) AS other_user_id"),
                'advert_id',
                DB::raw('MAX(created_at) as last_message_at'),
                DB::raw('MAX(id) as last_message_id')
            )
            ->where(function($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })
            ->groupBy('other_user_id', 'advert_id')
            ->get();

        // Filter out archived conversations
        $filteredConversations = $allConversations->filter(function($conversation) use ($archivedConversations) {
            $key = $conversation->advert_id . '_' . $conversation->other_user_id;
            return !in_array($key, $archivedConversations);
        });

        // Sort by last message date and reset keys
        $conversations = $filteredConversations->sortByDesc('last_message_at')->values();

        // Load additional details
        $conversations = $conversations->map(function ($conversation) use ($userId) {
            // Fetch advert and other user details
            $advert = Advert::find($conversation->advert_id);
            $otherUserId = $conversation->other_user_id;
            $otherUser = User::where('user_id', $otherUserId)->first();

            // Get the last message in this conversation
            $lastMessage = Message::where('advert_id', $conversation->advert_id)
                ->where(function($query) use ($userId, $otherUserId) {
                    $query->where(function($q) use ($userId, $otherUserId) {
                        $q->where('sender_id', $userId)
                          ->where('receiver_id', $otherUserId);
                    })->orWhere(function($q) use ($userId, $otherUserId) {
                        $q->where('sender_id', $otherUserId)
                          ->where('receiver_id', $userId);
                    });
                })
                ->orderBy('created_at', 'desc')
                ->first();

            // Count unread messages in this conversation
            $unreadCount = Message::where('advert_id', $conversation->advert_id)
                ->where('receiver_id', $userId)
                ->where('sender_id', $otherUserId)
                ->where('is_read', false)
                ->count();

            return [
                'advert' => $advert,
                'other_user' => $otherUser,
                'unread_count' => $unreadCount,
                'last_message' => $lastMessage,
                'last_message_at' => $conversation->last_message_at
            ];
        });

        return view('user.messages', compact('title', 'user', 'conversations', 'count_ads'));
    }

    public function archivedMessages(Request $request)
    {
        $title = "Archived Messages - " . config('global.site_name');
        $userId = $request->session()->get('user_id');
        $user = User::where('users.user_id', $userId)->first();

        if (!$user) {
            return redirect('/login')->with('error', 'Please login to view messages');
        }

        $count_ads = Advert::where('user_id', $userId)->count();

        // Get archived conversations
        $archivedConversations = ArchivedMessage::where('user_id', $userId)
            ->with(['advert', 'otherUser'])
            ->orderBy('archived_at', 'desc')
            ->get()
            ->map(function($archive) use ($userId) {
                // Get the last message in this conversation
                $lastMessage = Message::where('advert_id', $archive->advert_id)
                    ->where(function($query) use ($userId, $archive) {
                        $query->where(function($q) use ($userId, $archive) {
                            $q->where('sender_id', $userId)
                              ->where('receiver_id', $archive->other_user_id);
                        })->orWhere(function($q) use ($userId, $archive) {
                            $q->where('sender_id', $archive->other_user_id)
                              ->where('receiver_id', $userId);
                        });
                    })
                    ->orderBy('created_at', 'desc')
                    ->first();

                // Count unread messages in this conversation
                $unreadCount = Message::where('advert_id', $archive->advert_id)
                    ->where('receiver_id', $userId)
                    ->where('sender_id', $archive->other_user_id)
                    ->where('is_read', false)
                    ->count();

                return [
                    'advert' => $archive->advert,
                    'other_user' => $archive->otherUser,
                    'unread_count' => $unreadCount,
                    'last_message' => $lastMessage,
                    'archived_at' => $archive->archived_at,
                    'archive_id' => $archive->id,
                    'advert_id' => $archive->advert_id,
                    'other_user_id' => $archive->other_user_id
                ];
            });

        return view('user.archived-messages', compact('title', 'user', 'archivedConversations', 'count_ads'));
    }





    public function ad_status($status , $id)
    {
        // Sellers may only toggle their own ad between active/disabled themselves —
        // banned/pending_review are admin/resubmit-only transitions (see resubmitAd()).
        if (!in_array($status, ['active', 'disabled'], true)) {
            return redirect("user/my-ads")->with('error', 'Invalid status.');
        }

        $user = $this->getUserFromSession();

        $advert = Advert::where('id', $id)
            ->where('user_id', $user->user_id)
            ->whereIn('ad_status', ['active', 'disabled'])
            ->first();

        if (!$advert) {
            return redirect("user/my-ads")->with('error', 'Advert not found.');
        }

        $advert->update(['ad_status' => $status]);

        \App\Support\ActivityLog::record(
            'advert',
            ($status === 'disabled' ? 'Disabled' : 'Activated') . ' advert "' . $advert->ad_title . '"',
            $user,
            $advert
        );

        return redirect("user/my-ads")->with('success', 'Advert Status Updated');
    }

    public function resubmitAd($id)
    {
        $user = $this->getUserFromSession();

        $advert = Advert::where('id', $id)
            ->where('user_id', $user->user_id)
            ->where('ad_status', 'banned')
            ->first();

        if (!$advert) {
            return redirect("user/my-ads")->with('error', 'Advert not found or not eligible for resubmission.');
        }

        $advert->update([
            'ad_status' => 'pending_review',
            'resubmitted_at' => now(),
        ]);

        \App\Models\AdvertModerationLog::create([
            'advert_id' => $advert->id,
            'admin_id' => null,
            'action' => 'resubmitted',
        ]);

        \App\Support\ActivityLog::record('advert', 'Resubmitted advert "' . $advert->ad_title . '" for review', $user, $advert);

        return redirect("user/my-ads")->with('success', 'Advert resubmitted for review.');
    }

    public function add_wishlist(Request $request, $id)
    {   
        $user_id = $request->session()->get('user_id');

        if (!$user_id) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Please login'], 401);
            }
            return redirect()->back()->with('error', 'Please login to add to wishlist');
        }

        $advert = Advert::where('id', $id)->first();

        if (!$advert) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Advert not found'], 404);
            }
            return redirect()->back()->with('error', 'Advert not found');
        }

        $wishlist = Wishlist::where('advert_id', $id)
                            ->where('user_id', $user_id)
                            ->first();

        if ($wishlist) {
            $wishlist->delete();
            $message = 'Removed from Wishlist';
            $inWishlist = false;
        } else {
            $newpost = new Wishlist(['user_id' => $user_id]);
            $newpost->advert()->associate($advert);
            $newpost->save();
            $message = 'Added to Wishlist';
            $inWishlist = true;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'in_wishlist' => $inWishlist
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function favourites(Request $request)
    {
        $title = "Favourite Adverts | " . config('global.site_name');
        
        $userId = $request->session()->get('user_id');
        
        $user = User::find($userId);
        if (!$user) {
            abort(404, 'User not found');
        }
        
        $favoriteAds = Advert::with('firstImage')
            ->whereHas('wishlists', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $countAds = Advert::where('user_id', $userId)->count();
        
        return view('user.favourites', compact(
            'title',
            'user',
            'favoriteAds',
            'countAds'
        ));
    }

  public function payments(Request $request)
    {
        $userId = $request->session()->get('user_id');
        $user = User::findOrFail($userId);

        $buyAds = Payment::with(['advert.media', 'shipping'])
            ->where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Pending (abandoned checkout) — only show if the ad hasn't been sold yet
        $pendingOrders = Payment::with(['advert.media'])
            ->where('user_id', $userId)
            ->where('payment_status', 'pending')
            ->whereHas('advert', fn($q) => $q->where('sold', '!=', 'Yes'))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.payments', [
            'title'         => "My Orders | " . config('global.site_name'),
            'user'          => $user,
            'buyAds'        => $buyAds,
            'pendingOrders' => $pendingOrders,
        ]);
    }

    public function order_details(Request $request, $id)
    {
        $userId = $request->session()->get('user_id');
        $payment = Payment::with(['advert.media', 'shipping', 'cityLocation.state'])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        return view('user.order-details', [
            'title'   => "Order Details | " . config('global.site_name'),
            'payment' => $payment,
        ]);
    }

    public function confirmDelivery(Request $request, $orderId)
    {
        $userId = $request->session()->get('user_id');
        $order = Payment::find($orderId);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        [$ok, $message] = BuyDirectEscrow::make()->confirmReceipt($order, $userId);

        return response()->json(['success' => $ok, 'message' => $message, 'order' => $ok ? $order->fresh() : null], $ok ? 200 : 422);
    }

    public function reportProblem(Request $request, $orderId)
    {
        $request->validate(['reason' => 'required|string|min:10|max:2000']);

        $order = Payment::find($orderId);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        [$ok, $message] = BuyDirectEscrow::make()->openDispute($order, $request->session()->get('user_id'), $request->input('reason'));

        return response()->json(['success' => $ok, 'message' => $message], $ok ? 200 : 422);
    }

    public function cancelOrder(Request $request, $orderId)
    {
        $payment = Payment::find($orderId);

        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        [$ok, $message] = BuyDirectEscrow::make()->cancel($payment, 'buyer', 'Canceled by the buyer before shipping.', $request->session()->get('user_id'));

        return response()->json(['success' => $ok, 'message' => $message], $ok ? 200 : 422);
    }

    public function ad_shipping(Request $request, $id)
    {
        $userId = $request->session()->get('user_id');
        $user = User::findOrFail($userId);

        $ad = Payment::with(['advert', 'shipping', 'cityLocation.state'])
            ->where('advert_id', $id)
            ->where('payment_status', 'paid')
            ->latest()
            ->first();

        if (!$ad || !$ad->advert || $ad->advert->user_id !== $userId) {
            return redirect()->back()->with('error', 'No shipping record found for this ad.');
        }

        return view('user.ad-shipping', [
            'title' => "Ad Shipping | " . config('global.site_name'),
            'user' => $user,
            'ad' => $ad,
        ]);
    }

    public function update_shipping(Request $request, $id)
    {
        $userId = $request->session()->get('user_id');

        $request->validate([
            'seller_status' => 'required|in:shipped,canceled',
        ]);

        $payment = Payment::find($id);
        if (!$payment) {
            return redirect()->back()->with('error', 'Order not found.');
        }

        $escrow = BuyDirectEscrow::make();
        [$ok, $message] = $request->input('seller_status') === 'shipped'
            ? $escrow->markShipped($payment, $userId)
            : $escrow->cancel($payment, 'seller', 'Canceled by the seller before shipping.', $userId);

        if ($ok && $request->input('seller_status') === 'canceled') {
            $message = 'Order canceled. The buyer will be refunded and your advert is back on sale.';
        }

        return redirect()->back()->with($ok ? 'success' : 'error', $message);
    }

    public function advert_sold($id)
    {   
        DB::table('adverts')
            ->where('id', $id)
            ->update([
                'sold'=> "Yes",
                'sold_date'=> Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
 
        return redirect()->back()->with('success', 'Advert Marked Sold');
    
    }

    public function reviews_seller(Request $request, $id)
    {
        $title = "Feedbacks | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('users.user_id', $user_id)->first();
        $seller = User::where('user_id', $id)->first();
        $feedbacks = Feedback::with('user')->orderBy('created_at', 'asc')->where('seller_id', $id)->paginate(20);
        $count_feedbacks = Feedback::where('seller_id', $id)->count();
        $count_ads = Advert::where('user_id', $user_id)->count();
        return view('user.reviews-seller', compact('title','user', 'seller', 'feedbacks','count_ads','count_feedbacks'));
    }

    public function feedbacks(Request $request)
    {
        $title = "Feedbacks | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('users.user_id', $user_id)->first();
        $feedbacks = Feedback::with('user')->orderBy('created_at', 'asc')->where('seller_id', $user_id)->paginate(20);
        $count_feedbacks = Feedback::where('seller_id', $user_id)->count();
        $count_ads = Advert::where('user_id', $user_id)->count();
        return view('user.feedbacks', compact('title','user', 'feedbacks','count_ads','count_feedbacks'));
    }

    public function submit_feedback(Request $request, $seller)
    {
        $title = "Feedbacks | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('users.user_id', $user_id)->first();
        $seller = User::where('user_id', $seller)->first();
        $feedback = Feedback::with('user')->where('user_id', $user_id)->where('seller_id', $seller->user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        //dd($feedback);
        if ($request->isMethod('GET')) {
        return view('user.submit-feedbacks', compact('title','user', 'feedback','count_ads','seller'));
        }

        if ($request->isMethod('POST')) {

            $validated = $request->validate([
                'rating' => 'required|integer|between:1,5',
                'satisfaction' => 'required|integer|between:1,5',
                'reliable' => 'required|integer|between:1,5',
                'friendly' => 'required|integer|between:1,5',
                'message' => 'required|string|max:1000'
            ]);

            $validated['user_id'] = $user_id;
            $validated['seller_id'] = $seller->user_id;

            Feedback::updateOrCreate(
                [
                    'user_id' => $user_id,
                    'seller_id' => $seller->user_id
                ],
                $validated
            );

            return redirect()->back()->with('success', 'Thank you for your review!');
        }
    }


    public function boost_ad(Request $request, $id)
    {   
        $title = "Boost Ad | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        $price = 500;
        $advert = Advert::with(['images', 'car', 'phone'])
                        ->where('id', $id)
                        ->where('user_id', $user_id)
                        ->firstOrFail();

        return view('user.boost-ad', compact('title','user','advert','count_ads', 'price'));
    }

    public function post_boost_ad(Request $request, $id)
    {
        $title = "Boost Ad | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $promotion = $request->session()->get('promotion');
        $count_ads = Advert::where('user_id', $user_id)->count();
        $advert = Advert::with(['images', 'car', 'phone'])
                        ->where('id', $id)
                        ->where('user_id', $user_id)
                        ->firstOrFail();

        return view('user.post-boost-ad', compact('title','user','advert','count_ads','promotion'));
    }

    public function boosted_ad(Request $request, $id)
    {   
        $title = "Boosted Ad | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        $price = 500;
        $advert = AdvertBoost::with(['images', 'car', 'phone', 'boost'])
                ->where('id', $id)
                ->where('user_id', $user_id)
                ->firstOrFail();

        return view('user.boosted-ad', compact('title','user','advert','count_ads', 'price'));
    }

    public function boosted_ads(Request $request)
    {
        $title = "Boosted Ad | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        $price = 500;
        $ads = AdvertBoost::with(['user', 'advert.media'])
                ->where('user_id', $user_id)
                ->orderby('updated_at','desc')
                ->paginate(10);
        return view('user.boosted-adverts', compact('title','user','ads','count_ads', 'price'));
    }




    public function checkFollowing(Request $request, $userId)
    {
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

        $isFollowing = Followers::where([
            'user_id' => $user_id,
            'follow' => $userId
        ])->exists();

        return response()->json(['isFollowing' => $isFollowing]);
    }

    public function toggleFollow(Request $request)
    {
        $user_id = $request->session()->get('user_id');

        $request->validate([
            'followee_id' => 'required|exists:users,user_id'
        ]);

        $follow = Followers::where([
            'user_id' => $user_id,
            'follow' => $request->followee_id
        ])->first();

        if ($follow) {
            $follow->delete();
            return response()->json(['isFollowing' => false, 'message' => 'Unfollowed successfully']);
        } else {
            Followers::create([
                'user_id' => $user_id,
                'follow' => $request->followee_id
            ]);
            return response()->json(['isFollowing' => true, 'message' => 'Followed successfully']);
        }
    }

    public function notifications(Request $request) {
        $title = "My Notifications | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('users.user_id', $user_id)->first();

        // Stamp seen time (resets badge counter) and mark all unread as read.
        $user->notifications_seen_at = now();
        $user->save();

        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $notifications = Notification::with([
                'advert.owner',
                'advert.media' // Add this to eager load Spatie media
            ])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $count_ads = Advert::where('user_id', $user_id)->count();

        $groupedNotifications = $notifications->groupBy(function ($notification) {
            $date = $notification->created_at;
            if ($date->isToday()) {
                return 'Today';
            } elseif ($date->isYesterday()) {
                return 'Yesterday';
            } elseif ($date->isCurrentWeek()) {
                return $date->format('l');
            } else {
                return $date->format('M j, Y');
            }
        });

        return view('user.notifications', compact('title','user', 'groupedNotifications','count_ads'));
    }

    public function deleteNotification($id)
    {
        $user_id = request()->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $notification) {
            return response()->json(['status' => 'error', 'message' => 'Not found'], 404);
        }

        $notification->delete();

        return response()->json(['status' => 'success']);
    }


    public function report_user(Request $request, $id)
{
    $data['title'] = 'Report User | '.config('global.site_name');
    $data['reported']  = $reported = User::where('user_id', $id)->first();
    $user_id = $request->session()->get('user_id');
    $data['reporter'] = $user = User::where('user_id', $user_id)->first();

    if ($request->isMethod('GET')) {
        return view('public.report-user', $data);
    }

    if ($request->isMethod('POST')) {
        // Build validation rules
        $rules = [
            'name' => 'required',
            'subject' => 'required',
            'message' => 'required',
        ];

        // Add reCAPTCHA validation only if enabled
        if (config('services.recaptcha.enabled', false)) {
            $rules['g-recaptcha-response'] = ['required', new ReCaptcha];
        }

        $request->validate($rules);

        $message = ReportUser::updateOrCreate(
            [
                'reported' => $reported->id,
                'reporter' => $user->id,
            ],
            [
                'subject' => $request->subject,
                'message' => $request->message,
            ]
        );

        $details = [
            'advert' => $reported->name,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'subject' => $request->subject,
            'message' => $request->message,
        ];

        // Send email in background
        Mail::to(config('global.admin_email'))
            ->queue(new ReportMail($details));

        return redirect()->back()
            ->with('success', 'Your Report Has Been Received, We will Get back to Shortly');
    }
}

    public function followersListJson(Request $request)
    {
        $targetUserId = $request->query('of');
        if (!$targetUserId) return response()->json(['error' => 'Missing user ID'], 400);

        $loggedInUserId = $request->session()->get('user_id');
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = 20;

        $rows = Followers::where('follow', $targetUserId)
            ->with(['user:user_id,name,state,city'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $myFollowingIds = $loggedInUserId
            ? Followers::where('user_id', $loggedInUserId)->pluck('follow')->toArray()
            : [];

        $data = $rows->map(function ($f) use ($myFollowingIds, $loggedInUserId) {
            $u = $f->user;
            if (!$u) return null;
            $name    = $u->name ?? 'User';
            $words   = preg_split('/\s+/', trim($name));
            $initials = count($words) >= 2
                ? strtoupper(mb_substr($words[0], 0, 1) . mb_substr(end($words), 0, 1))
                : strtoupper(mb_substr($name, 0, 2));
            return [
                'user_id'      => $u->user_id,
                'name'         => $name,
                'initials'     => $initials,
                'state'        => trim(implode(', ', array_filter([$u->city, $u->state]))),
                'active_ads'   => Advert::where('user_id', $u->user_id)->activeNotRecentlySold()->count(),
                'is_following' => in_array($u->user_id, $myFollowingIds),
                'is_self'      => $loggedInUserId == $u->user_id,
                'profile_url'  => '/seller/' . Str::slug($name) . '/' . $u->user_id,
            ];
        })->filter()->values();

        return response()->json(['data' => $data, 'has_more' => $rows->hasMorePages(), 'total' => $rows->total()]);
    }

    public function followingListJson(Request $request)
    {
        $targetUserId = $request->query('of');
        if (!$targetUserId) return response()->json(['error' => 'Missing user ID'], 400);

        $loggedInUserId = $request->session()->get('user_id');
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = 20;

        $rows = Followers::where('user_id', $targetUserId)
            ->with(['seller:user_id,name,state,city'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $myFollowingIds = $loggedInUserId
            ? Followers::where('user_id', $loggedInUserId)->pluck('follow')->toArray()
            : [];

        $data = $rows->map(function ($f) use ($myFollowingIds, $loggedInUserId) {
            $u = $f->seller;
            if (!$u) return null;
            $name    = $u->name ?? 'User';
            $words   = preg_split('/\s+/', trim($name));
            $initials = count($words) >= 2
                ? strtoupper(mb_substr($words[0], 0, 1) . mb_substr(end($words), 0, 1))
                : strtoupper(mb_substr($name, 0, 2));
            return [
                'user_id'      => $u->user_id,
                'name'         => $name,
                'initials'     => $initials,
                'state'        => trim(implode(', ', array_filter([$u->city, $u->state]))),
                'active_ads'   => Advert::where('user_id', $u->user_id)->activeNotRecentlySold()->count(),
                'is_following' => in_array($u->user_id, $myFollowingIds),
                'is_self'      => $loggedInUserId == $u->user_id,
                'profile_url'  => '/seller/' . Str::slug($name) . '/' . $u->user_id,
            ];
        })->filter()->values();

        return response()->json(['data' => $data, 'has_more' => $rows->hasMorePages(), 'total' => $rows->total()]);
    }

    public function removeFollower(Request $request, $userId)
    {
        $loggedInUserId = $request->session()->get('user_id');
        $deleted = Followers::where('user_id', $userId)->where('follow', $loggedInUserId)->delete();
        if ($deleted) return response()->json(['success' => true, 'message' => 'Follower removed']);
        return response()->json(['success' => false, 'message' => 'Not found'], 404);
    }
}
