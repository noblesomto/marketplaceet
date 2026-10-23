<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Admin;
use App\Models\Advert;
use App\Models\AdvertBoost;
use App\Models\Payment;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brands;
use App\Models\Blog;
use Carbon\Carbon;
use App\Models\Reports;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;


class AdminController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        $title = "Admin Dashboard - " . config('global.site_name');

        // Initialize stats array
        $stats = [];

        // User Management Stats (if admin has view_users permission)
        if ($admin->can('view_users')) {
            $stats['users'] = [
                'total' => User::count(),
                'active' => User::where('acc_status', 1)->count(),
                'inactive' => User::where('acc_status', 0)->count(),
                'verified' => User::where('verified', 1)->count(),
                'unverified' => User::where('verified', 0)->count(),
                'new_today' => User::whereDate('created_at', Carbon::today())->count(),
                'new_this_week' => User::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
                'new_last_week' => User::whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])->count(),
                'new_this_month' => User::whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->count(),
                'new_last_month' => User::whereMonth('created_at', Carbon::now()->subMonth()->month)->whereYear('created_at', Carbon::now()->subMonth()->year)->count(),
            ];
        }

        // Advert Management Stats (if admin has view_adverts permission)
        if ($admin->can('view_adverts')) {
            $stats['adverts'] = [
                'total' => Advert::count(),
                'active' => Advert::where('ad_status', 'active')->count(),
                'pending' => Advert::where('ad_status', 'pending')->count(),
                'sold' => Advert::where('sold', 'Yes')->count(),
                'disabled' => Advert::where('ad_status', 'disabled')->count(),
                'new_today' => Advert::whereDate('created_at', Carbon::today())->count(),
                'new_this_week' => Advert::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
                'new_last_week' => Advert::whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])->count(),
                'new_this_month' => Advert::whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->count(),
                'new_last_month' => Advert::whereMonth('created_at', Carbon::now()->subMonth()->month)->whereYear('created_at', Carbon::now()->subMonth()->year)->count(),
                'source_web' => Advert::where('source', 'web')->count(),
                'source_api' => Advert::where('source', 'api')->count(),
            ];
        }

        // Boost Management Stats (if admin has boost permissions)
        if ($admin->can('view_active_boosts') || $admin->can('view_completed_boosts') || $admin->can('view_unpaid_boosts')) {
            $stats['boosts'] = [
                'total' => AdvertBoost::count(),
                'active' => AdvertBoost::where('payment_status', 'paid')->where('boost_status', 'active')->count(),
                'completed' => AdvertBoost::where('boost_status', 'completed')->count(),
                'unpaid' => AdvertBoost::where('payment_status', 'pending')->count(),
                'revenue_today' => AdvertBoost::where('payment_status', 'paid')->whereDate('created_at', Carbon::today())->sum('amount'),
                'revenue_this_week' => AdvertBoost::where('payment_status', 'paid')->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('amount'),
                'revenue_last_week' => AdvertBoost::where('payment_status', 'paid')->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])->sum('amount'),
                'revenue_this_month' => AdvertBoost::where('payment_status', 'paid')->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->sum('amount'),
                'revenue_last_month' => AdvertBoost::where('payment_status', 'paid')->whereMonth('created_at', Carbon::now()->subMonth()->month)->whereYear('created_at', Carbon::now()->subMonth()->year)->sum('amount'),
            ];
        }

        // Payment & Settlement Stats (if admin has payment permissions)
        if ($admin->can('view_payments') || $admin->can('view_settlements')) {
            $stats['payments'] = [
                'total_transactions' => Payment::count(),
                'paid' => Payment::where('payment_status', 'paid')->count(),
                'pending' => Payment::where('payment_status', 'pending')->count(),
                'total_revenue' => Payment::where('payment_status', 'paid')->sum('amount'),
                'revenue_today' => Payment::where('payment_status', 'paid')->whereDate('created_at', Carbon::today())->sum('amount'),
                'revenue_this_month' => Payment::where('payment_status', 'paid')->whereMonth('created_at', Carbon::now()->month)->sum('amount'),
                'source_web' => Payment::where('payment_status', 'paid')->where('source', 'web')->count(),
                'source_api' => Payment::where('payment_status', 'paid')->where('source', 'api')->count(),
            ];

            $stats['settlements'] = [
                'pending' => Payment::where('payment_status', 'paid')->where('seller_settlement', 'no')->count(),
                'completed' => Payment::where('seller_settlement', 'yes')->count(),
                'pending_amount' => Payment::where('payment_status', 'paid')->where('seller_settlement', 'no')->sum('amount'),
            ];
        }

        // Shipping Stats (if admin has shipping permission)
        if ($admin->can('manage_shipping')) {
            $stats['shipping'] = [
                'pending' => Payment::where('payment_status', 'paid')->where('shipping_status', 'pending')->count(),
                'shipped' => Payment::where('payment_status', 'paid')->where('shipping_status', 'shipped')->count(),
                'delivered' => Payment::where('payment_status', 'paid')->where('shipping_status', 'delivered')->count(),
            ];
        }

        // Category Management Stats (if admin has category permissions)
        if ($admin->can('manage_categories')) {
            $stats['categories'] = [
                'total_categories' => Category::count(),
                'total_subcategories' => SubCategory::count(),
                'total_brands' => Brands::count(),
            ];
        }

        // Report Stats (if admin has report permissions)
        if ($admin->can('view_reports')) {
            $stats['reports'] = [
                'total' => Reports::count(),
                'pending' => Reports::where('status', 'pending')->count(),
                'resolved' => Reports::where('status', 'resolved')->count(),
                'new_today' => Reports::whereDate('created_at', Carbon::today())->count(),
            ];
        }

        // Blog Stats (if admin has blog permissions)
        if ($admin->can('view_blog')) {
            $stats['blog'] = [
                'total_posts' => Blog::count(),
                'published' => Blog::where('status', 'published')->count(),
                'draft' => Blog::where('status', 'draft')->count(),
            ];
        }

        // Recent Activities (limited based on permissions)
        $recentActivities = [];

        if ($admin->can('view_adverts')) {
            $recentAdverts = Advert::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            $recentActivities['adverts'] = $recentAdverts;
        }

        if ($admin->can('view_users')) {
            $recentUsers = User::orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            $recentActivities['users'] = $recentUsers;
        }

        if ($admin->can('view_reports')) {
            $recentReports = Reports::with(['user', 'adverts.owner', 'seller'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            $recentActivities['reports'] = $recentReports;
        }

        // Quick Actions based on permissions
        $quickActions = $this->getQuickActions($admin);

        return view('admin.index', compact('title', 'stats', 'recentActivities', 'quickActions', 'admin'));
    }

    /**
     * Get quick action buttons based on admin permissions
     */
    private function getQuickActions($admin)
    {
        $actions = [];

        if ($admin->can('create_advert')) {
            $actions[] = [
                'title' => 'Create Advert',
                'icon' => 'bi-plus-circle',
                'url' => '/admin/create-advert',
                'color' => 'primary'
            ];
        }

        if ($admin->can('view_users')) {
            $actions[] = [
                'title' => 'Manage Users',
                'icon' => 'bi-people',
                'url' => '/admin/users/active',
                'color' => 'info'
            ];
        }

        if ($admin->can('view_reports')) {
            $actions[] = [
                'title' => 'View Reports',
                'icon' => 'bi-flag',
                'url' => '/admin/view-reports',
                'color' => 'warning'
            ];
        }

        if ($admin->can('view_settlements')) {
            $actions[] = [
                'title' => 'Settlements',
                'icon' => 'bi-cash-coin',
                'url' => '/admin/settlements',
                'color' => 'success'
            ];
        }

        if ($admin->can('manage_categories')) {
            $actions[] = [
                'title' => 'Categories',
                'icon' => 'bi-grid',
                'url' => '/admin/manage-categories',
                'color' => 'secondary'
            ];
        }

        if ($admin->can('create_blog')) {
            $actions[] = [
                'title' => 'Write Blog Post',
                'icon' => 'bi-pencil-square',
                'url' => '/admin/blog/create',
                'color' => 'primary'
            ];
        }

        if ($admin->hasRole('super_admin')) {
            $actions[] = [
                'title' => 'System Settings',
                'icon' => 'bi-gear',
                'url' => '/admin/settings/roles',
                'color' => 'dark'
            ];
        }

        return $actions;
    }


    public function view_reports(Request $request)
    {
        $title = "Ad Reports | " . config('global.site_name');
        $page_title = "Ad Reports";
        $adverts = Reports::with(['user', 'adverts.owner', 'seller'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        
        return view('admin.reports', compact('title', 'page_title','adverts'));
    }

    public function report_status($id, $status)
    {
        DB::table('reports')
                ->where('id', $id)
                ->update([
                    'status'=> $status,
                    'updated_at' => Carbon::now(),
                ]);

        return redirect()->back()->with('status', ['text'=>'Report Status Changed','type'=>'success']);
    }

    public function deleteComplaint($id)
    {
        $deleted = Reports::where('id', $id)->delete();

        if (! $deleted) {
            return redirect()->back()->with('status', ['text' => 'Complaint not found', 'type' => 'danger']);
        }

        return redirect()->back()->with('status', ['text' => 'Complaint deleted', 'type' => 'success']);
    }




    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'You have been logged out successfully.');
    }
}
