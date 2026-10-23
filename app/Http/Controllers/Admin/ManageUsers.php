<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\UserVerification;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Advert;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use App\Support\ActivityLog;
use Spatie\Activitylog\Models\Activity;

class ManageUsers extends Controller
{
    public function active_users(Request $request)
    {
        $title = "Active Users | " . config('global.site_name');
        $page_title = "Active Users";
        $users = User::where('acc_status', 1)->where('disable_account', 'no')->orderBy('created_at', 'desc')->paginate(20);

        $stats = $this->globalUserStats();

        return view('admin.users.active-users', compact('title', 'users', 'page_title', 'stats'));
    }

    public function unverified_users(Request $request)
    {
        $title = "Unverified Users | " . config('global.site_name');
        $page_title = "Unverified Users";
        $users = User::where('acc_status', 0)->where('disable_account', 'no')->orderBy('created_at', 'desc')->paginate(20);

        $stats = $this->globalUserStats();

        return view('admin.users.unverified-users', compact('title', 'users', 'page_title', 'stats'));
    }

    public function disabled_users(Request $request)
    {
        $title = "Disabled Users | " . config('global.site_name');
        $page_title = "Disabled Users";
        // No acc_status filter — show ALL disabled users regardless of verification state
        $users = User::where('disable_account', 'yes')->orderBy('disable_account_date', 'desc')->paginate(20);

        $stats = $this->globalUserStats();

        return view('admin.users.disabled-users', compact('title', 'users', 'page_title', 'stats'));
    }

    public function disable_status(Request $request, $id)
    {
        $request->validate([
            'action'  => 'required|in:disable,enable',
            'reason'  => 'nullable|string|max:500',
        ]);

        $action = $request->input('action');
        $isDisabling = $action === 'disable';

        $admin = Auth::guard('admin')->user();
        $user = User::where('user_id', $id)->first();

        DB::table('users')
            ->where('user_id', $id)
            ->update([
                'disable_account'      => $isDisabling ? 'yes' : 'no',
                'disable_account_date' => $isDisabling ? Carbon::now() : null,
                'disabled_by'          => $isDisabling ? ($admin->name ?? 'Admin') : null,
                'disable_reason'       => $isDisabling ? $request->input('reason') : null,
                'updated_at'           => Carbon::now(),
            ]);

        if ($user) {
            ActivityLog::record(
                'account',
                $isDisabling ? 'Account disabled by admin' : 'Account re-enabled by admin',
                $admin,
                $user,
                $isDisabling ? ['reason' => $request->input('reason')] : []
            );
        }

        $text = $isDisabling ? 'User account has been disabled.' : 'User account has been re-enabled.';

        return redirect()->back()->with('status', ['text' => $text, 'type' => 'success']);
    }

    public function user_status($id, $status)
    {
        $user = User::where('user_id', $id)->first();

        DB::table('users')
                ->where('user_id', $id)
                ->update([
                    'acc_status'=> $status,
                    'updated_at' => Carbon::now(),
                ]);

        if ($user) {
            ActivityLog::record(
                'account',
                $status == 1 ? 'Account activated by admin' : 'Account deactivated by admin',
                Auth::guard('admin')->user(),
                $user
            );
        }

        return redirect()->back()->with('status', ['text'=>'User Status Changed','type'=>'success']);
    }

    public function search(Request $request)
    {
        try {
            $query = User::query();

            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('user_id', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('phone', 'LIKE', "%{$searchTerm}%");
                });
            }

            if ($request->filled('account_type')) {
                $query->where('acc_type', $request->account_type);
            }

            if ($request->filled('verification')) {
                $query->where('verified', $request->verification);
            }

            // Scope to the current admin page context
            $context = $request->input('context', 'active');
            if ($context === 'disabled') {
                $query->where('disable_account', 'yes');
            } elseif ($context === 'unverified') {
                $query->where('acc_status', 0)->where('disable_account', 'no');
            } else {
                // active
                $query->where('acc_status', 1)->where('disable_account', 'no');
            }

            $users = $query->orderBy('created_at', 'desc')->paginate(10);

            return response()->json([
                'success' => true,
                'users' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page'    => $users->lastPage(),
                    'per_page'     => $users->perPage(),
                    'total'        => $users->total(),
                    'from'         => $users->firstItem(),
                    'to'           => $users->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching users: ' . $e->getMessage()
            ], 500);
        }
    }

    public function view_user(Request $request, $id)
    {
        $title = "User Details | " . config('global.site_name');
        $user = User::with('adverts')->where('user_id', $id)->first();
        $active_adverts = Advert::where('ad_status', 1)->where('sold', "No")->where('user_id', $id)->count();
        $sold_adverts = Advert::where('sold', "Yes")->where('user_id', $id)->count();
        $totalRevenue = Payment::where('payment_status', "paid")
                ->where('seller_settlement', "yes")
                ->where('user_id', $id)
                ->sum('amount_paid');
        $pendingRevenue = Payment::where('payment_status', "paid")
            ->where('seller_settlement', "no")
            ->where('user_id', $id)
            ->sum('amount_paid');
        return view('admin.users.view-user', compact('title', 'user', 'active_adverts', 'sold_adverts', 'totalRevenue','pendingRevenue'));
    }

    public function delete_user($user_id)
    {
        $user = User::where('user_id', $user_id)->first();
        if ($user) {
            ActivityLog::record(
                'account',
                'Account deleted by admin',
                Auth::guard('admin')->user(),
                $user,
                ['name' => $user->name, 'email' => $user->email]
            );

            Advert::where('user_id', $user_id)->delete();
            $user->delete();
        }

        return redirect()->route('admin.active.users')->with('status', ['text' => 'User deleted successfully.', 'type' => 'success']);
    }

    public function user_verification(Request $request)
    {
        $title = "User Verification | " . config('global.site_name');
        $page_title = "User Verification";
        $users = UserVerification::with('user')->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.users.user-verification', compact('title', 'users', 'page_title'));
    }

    public function verify_status($id, $status, $verify)
    {
        $user = User::where('user_id', $id)->first();

        DB::table('user_verifications')
            ->where('user_id', $id)
            ->update([
                'verify_status'=> $status,
                'updated_at' => Carbon::now(),
            ]);

        DB::table('users')
                ->where('user_id', $id)
                ->update([
                    'verified'=> $verify,
                    'updated_at' => Carbon::now(),
                ]);

        if ($user) {
            ActivityLog::record(
                'account',
                'Identity verification ' . ($verify === 'yes' ? 'approved' : 'rejected') . ' by admin',
                Auth::guard('admin')->user(),
                $user
            );
        }

        return redirect()->back()->with('status', ['text'=>'Verification Status Changed','type'=>'success']);
    }

    public function user_activity(Request $request, $id)
    {
        $user = User::where('user_id', $id)->firstOrFail();
        $title = "Activity - " . $user->name . " | " . config('global.site_name');

        $advertIds = Advert::where('user_id', $user->user_id)->pluck('id');

        $query = Activity::query()
            ->where(function ($q) use ($user) {
                $q->where('causer_type', User::class)->where('causer_id', $user->id);
            })
            ->orWhere(function ($q) use ($user) {
                $q->where('subject_type', User::class)->where('subject_id', $user->id);
            })
            ->orWhere(function ($q) use ($advertIds) {
                $q->where('subject_type', Advert::class)->whereIn('subject_id', $advertIds);
            });

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->input('log_name'));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        $activities = $query->orderByDesc('created_at')->paginate(30)->withQueryString();

        // Spatie's default causer/subject morphTo resolution assumes the related
        // model's Eloquent primary key matches the morph id column — that's true
        // for Advert/Admin, but User's declared key is 'user_id', not the 'id'
        // this table stores (see App\Support\ActivityLog). Resolve manually.
        $userCache = [$user->id => $user];
        $advertCache = [];

        $activities->getCollection()->transform(function (Activity $activity) use (&$userCache, &$advertCache) {
            foreach (['causer', 'subject'] as $relation) {
                $type = $activity->{$relation . '_type'};
                $modelId = $activity->{$relation . '_id'};

                if ($type === User::class && $modelId) {
                    $userCache[$modelId] ??= User::where('id', $modelId)->first();
                    $activity->setRelation($relation, $userCache[$modelId]);
                } elseif ($type === Advert::class && $modelId) {
                    // Advert isn't soft-deleted — a hard-deleted advert simply
                    // won't resolve here; the log description already carries
                    // the title so the timeline entry stays meaningful.
                    $advertCache[$modelId] ??= Advert::find($modelId);
                    $activity->setRelation($relation, $advertCache[$modelId]);
                }
            }

            return $activity;
        });

        $logNames = Activity::query()
            ->where(function ($q) use ($user) {
                $q->where('causer_type', User::class)->where('causer_id', $user->id);
            })
            ->orWhere(function ($q) use ($user) {
                $q->where('subject_type', User::class)->where('subject_id', $user->id);
            })
            ->orWhere(function ($q) use ($advertIds) {
                $q->where('subject_type', Advert::class)->whereIn('subject_id', $advertIds);
            })
            ->distinct()
            ->pluck('log_name');

        return view('admin.users.user-activity', compact('title', 'user', 'activities', 'logNames'));
    }

    private function globalUserStats(): array
    {
        return [
            'total'    => User::count(),
            'active'   => User::where('acc_status', 1)->where('disable_account', 'no')->count(),
            'disabled' => User::where('disable_account', 'yes')->count(),
        ];
    }
}
