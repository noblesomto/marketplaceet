<?php

namespace App\Http\Controllers\Pages;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\User;
use App\Models\Admin;
use App\Models\Advert;
use App\Models\AdvertImage;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Blog;
use Mail;
use Hash;
use App\Mail\RegisterMail;
use App\Mail\NotifyMail;
use App\Mail\ContactMail;

class PageController extends Controller
{

    public function about()
    {   
        $title = "About Us  | " . config('global.site_name');
        return view('public.pages.about', compact('title'));
    }

    public function career()
    {
        $title = "Career  | " . config('global.site_name');
        return view('public.pages.career', compact('title'));
    }

    public function privacy()
    {
        $title = "Privacy Policy  | " . config('global.site_name');
        return view('public.pages.privacy', compact('title'));
    }

    public function cookie()
    {
        $title = "Cookie Policy  | " . config('global.site_name');
        return view('public.pages.cookie', compact('title'));
    }

    public function billing()
    {
        $title = "Billing Policy  | " . config('global.site_name');
        return view('public.pages.billing', compact('title'));
    }

    public function copyright()
    {
        $title = "Copyright Policy  | " . config('global.site_name');
        return view('public.pages.copyright', compact('title'));
    }

    public function dmca()
    {
        $title = "Intellectual Property & Copyright Policy  | " . config('global.site_name');
        return view('public.pages.dmca', compact('title'));
    }

    public function safety()
    {   
        $title = "Tips for your safety  | " . config('global.site_name');
        return view('public.pages.safety', compact('title'));
    }

    public function terms()
    {
        $title = "Terms of Use  | " . config('global.site_name');
        return view('public.pages.terms', compact('title'));
    }

    public function payments_refunds()
    {
        $title = "Payment and Refunds  | " . config('global.site_name');
        return view('public.pages.payments-refunds', compact('title'));
    }

    public function faq()
    {
        $title = "FAQ  | " . config('global.site_name');
        return view('public.pages.faq', compact('title'));
    }

    public function how_it_works()
    {
        $title = "How It Works  | " . config('global.site_name');
        return view('public.pages.how-it-works', compact('title'));
    }

    public function advertise()
    {
        $title = "Advertise With Us  | " . config('global.site_name');
        return view('public.pages.advertise', compact('title'));
    }

    public function sell_online()
    {
        $title = "Sell Online  | " . config('global.site_name');
        return view('public.pages.sell-online', compact('title'));
    }

    public function blog()
    {
        $title = "Our Blog  | " . config('global.site_name');
        $blogs = Blog::where('status', 'published')->latest()->paginate(12);
        return view('public.blog.index', compact('title','blogs'));
    }

    public function blog_details($slug)
    {
        $blog = Blog::where('slug',$slug)->first();

        // Check if blog exists
        if (!$blog) {
            abort(404);
        }
         $title = $blog->title . " | " . config('global.site_name');
        //dd($slug);
        $blog->increment('views');

        $similar = Blog::where('category', $blog->category)
            ->where('slug', '!=', $slug)
            ->where('status','published')
            ->inRandomOrder()
            ->limit(2)
            ->get();
        //dd($similar);
        return view('public.blog.blog-details', compact('title','blog','similar'));
    }

    public function page()
    {
        $title = "FAQ  | " . config('global.site_name');
        return view('public.page', compact('title'));
    }

    public function contact(Request $request)
    {
        $title = 'Contact Us | '.config('global.site_name');

        if ($request->isMethod('GET')) {
            return view('public.pages.contact-us', compact('title'));
        }

        if ($request->isMethod('POST')) {
            // Build validation rules
            $rules = [
                'name' => 'required',
                'phone' => 'required',
                'subject' => 'required',
                'email' => 'required|email',
                'message' => 'required',
            ];

            $messages = [];

            // Add reCAPTCHA validation only if enabled
            if (config('services.recaptcha.enabled', false)) {
                $rules['g-recaptcha-response'] = 'required';
                $messages['g-recaptcha-response.required'] = 'Security verification is required.';
            }

            $request->validate($rules, $messages);

            // Verify reCAPTCHA v3 only if enabled
            if (config('services.recaptcha.enabled', false)) {
                $recaptchaResponse = $request->input('g-recaptcha-response');
                $recaptchaSecret = config('services.recaptcha.secret_key');

                $verifyResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $recaptchaSecret,
                    'response' => $recaptchaResponse,
                    'remoteip' => $request->ip()
                ]);

                $recaptchaData = $verifyResponse->json();

                // Check if verification was successful and score is acceptable
                if (!$recaptchaData['success'] || $recaptchaData['score'] < 0.5) {
                    Log::warning('Contact form reCAPTCHA verification failed', [
                        'email' => $request->input('email'),
                        'score' => $recaptchaData['score'] ?? 'N/A',
                        'ip' => $request->ip(),
                        'error_codes' => $recaptchaData['error-codes'] ?? []
                    ]);

                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['g-recaptcha-response' => 'Security verification failed. Please try again.']);
                }
            }

            $details = [
                'name' => $request->input('name'),
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
                'subject' => $request->input('subject'),
                'message' => $request->input('message'),
            ];

            $admin_email = config('global.site_email');

            try {
                Mail::to($admin_email)->send(new ContactMail($details));
                return redirect()->back()->with('success', 'Great! Your message was successfully sent, We will get back to you ASAP');
            } catch (Throwable $e) {
                Log::error('Contact form email failed', [
                    'error' => $e->getMessage(),
                    'email' => $request->input('email')
                ]);
                return redirect()->back()->with('error', 'Error!, Your Email could not be sent, please contact admin: '.config('global.site_email'));
            }
        }
    }

    public function shipping()
    {
        $title = "Shipping  | " . config('global.site_name');
        return view('public.shipping.shipping', compact('title'));
    }



    public function adminlogin(Request $request)
    {
        $title = "Admin Login" . config('global.site_title');

        if ($request->isMethod('POST')) {
            $request->validate([
                'username' => 'required',
                'password' => 'required|min:4',
            ]);

            $username = $request->username;
            $password = $request->password;

            $admin = Admin::where('username', $username)->first();

            if ($admin) {
                // Check if password is bcrypt hashed (recommended)
                if (Hash::check($password, $admin->password)) {
                    $request->session()->put('admin_id', $admin->admin_id);
                    return redirect()->action([AdminController::class, 'index']);
                }

                // Legacy MD5 check - migrate to bcrypt on successful login
                if ($admin->password === md5($password)) {
                    // Migrate to bcrypt
                    $admin->password = Hash::make($password);
                    $admin->save();

                    $request->session()->put('admin_id', $admin->admin_id);
                    return redirect()->action([AdminController::class, 'index']);
                }
            }

            return redirect("admin/login")->with('status',['text'=>'Sorry! you enter wrong credentials ','type'=>'danger']);
        }

        if ($request->isMethod('GET')) {
            return view('public.admin', compact('title'));
        }
    }

    public function getIp(){
        if(!empty($_SERVER['HTTP_CLIENT_IP'])){
            //ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            //ip pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    public function email()
    {   
        $title = "Email" . config('global.site_title');
        $details = [
                'amount' => '93244',
                'email'=>'noblesomto@gmail.com',
                'time' => '03:20',
                'ip' => 'hqn9223',
                'phone' => '090434353',
                'postcode' => '093033',
                'city' => 'Bole',
                'state' => 'Addis Ababa',
                'shipping' => '2 bedrooms',
                'shipped_date' => '2025-02-14',
                'checkout' => '2025-03-01',
                'title' => 'Stunning Modern 2-Floor House with 3 Ensuite Rooms',
                'subject' => 'Stunning Modern 2-Floor House with 3 Ensuite Rooms',
                'advert' => 'Stunning Modern 2-Floor House with 3 Ensuite Rooms',
                'address' => 'Entire home in Greater London, United Kingdom',
                'token' => '093033',
                'name' => "noble",
                'buyer' => "noble",
                'seller' => "noble",
                'user_id' => "5244",
                'tracking_id' => "GO5Ka244",
                'date' => "5-2-44",
                'otp'=>"049403",
                'ship_code'=>"049403",
                'currency'=>"USD",
                'email_subject'=>"049403",
                'message' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis mattis vitae quam vel viverra. Etiam vitae orci sit amet quam euismod tincidunt.',
                'email_body'=>"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis mattis vitae quam vel viverra. Etiam vitae orci sit amet quam euismod tincidunt. Cras eu porttitor nunc. Integer lacinia augue nibh, ac pretium nunc venenatis vitae. Fusce sapien elit, commodo vitae purus id, ultricies placerat lectus. Sed non sagittis augue. Donec in consequat turpis. Donec vel lectus tempor, fringilla dolor at, varius mi. Praesent a nulla maximus, blandit ex non, rutrum augue. Pellentesque sit amet turpis luctus, porta purus vitae, lobortis tellus. Aliquam erat volutpat. Ut eget quam euismod, feugiat eros in, venenatis quam. Nam vitae nibh in augue venenatis porta non in nunc. Vivamus iaculis ut enim nec egestas. Morbi tristique lectus in orci ultricies, id imperdiet massa consectetur. Donec semper diam in laoreet hendrerit.<br><br>

Morbi faucibus pulvinar lectus. Sed vehicula elit ac cursus porttitor. Aenean augue quam, vehicula iaculis vulputate sit amet, pretium et mauris. Maecenas ut scelerisque sem. Morbi in purus non eros ullamcorper placerat. Etiam semper sit amet ex non dictum. Phasellus facilisis mauris vitae tellus finibus, a semper justo bibendum.

Nunc justo velit, dictum sed est ut, porttitor semper odio. Fusce sit amet diam vitae lectus pretium mattis a a quam. Morbi dictum viverra metus. Donec sed lectus nec risus laoreet gravida ut non leo. Mauris sed tellus lorem. Pellentesque sit amet dolor a tortor viverra imperdiet ut sed metus. Vivamus venenatis sem risus, sed aliquet sem lobortis in. Nam quis erat vel tortor aliquam cursus eget id justo. Sed sollicitudin ex pellentesque libero feugiat mollis. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Aenean rutrum volutpat placerat. Vestibulum sed congue lorem, sit amet posuere dui. Proin sagittis mi odio, id congue mi viverra at. Pellentesque sit amet tellus eget quam fringilla tincidunt. Morbi aliquam dolor ut nisl semper ultricies.",
            ];
        return view('email.registerMail', compact('title','details'));
    }
}
