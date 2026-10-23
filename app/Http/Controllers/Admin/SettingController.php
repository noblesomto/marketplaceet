<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\AdSetting;
use App\Models\Shipping;
use App\Models\User;
use App\Models\Admin;
use Hash;
use App\Models\State;
use App\Models\GigLogistic;

class SettingController extends Controller
{
    public function shipping(Request $request)
    {   
        $title = "Setup Shipping | " . config('global.site_name');
        $shippings = Shipping::get();

        //dd($shippings);
        if ($request->isMethod('GET')) {
            return view('admin.settings.shipping', compact('title','shippings'));
        }

         if ($request->isMethod('POST')) {

            $request->validate([
                'company' => 'required',
                'weight' => 'required|numeric',
                'price' => 'required|numeric',
                'description' => 'required',
                'logo' => 'required|image|mimes:jpg,png,jpeg,gif|max:10288',

            ]);


                $image = $request->file('logo');
                $imageName = time() . '.jpg';
                $imageName = str_replace(' ', '-', $imageName);
                $request->file('logo')->move('uploads/shipping', $imageName);
     
                Shipping::create([
                    'ship_id'=> rand(11111,99999),
                    'company'=> $request->input('company'),
                    'weight'=> $request->input('weight'),
                    'price'=> $request->input('price'),
                    'description'=> $request->input('description'),
                    'logo'=> $imageName,
                ]);
  

            return redirect()->back()->with('status', ['text'=>'Company  Successfully published','type'=>'success']);
        }
    }

    public function gig_locations(Request $request)
    {   
        $title = "New Location | " . config('global.site_name');
        $state = State::get();
        $gig = GigLogistic::with('state')->paginate(10);

        //dd($shippings);
        if ($request->isMethod('GET')) {
            return view('admin.settings.gig.locations', compact('title','state','gig'));
        }

         if ($request->isMethod('POST')) {

            $request->validate([
                'state' => 'required',
                'city' => 'required',
                'address' => 'required',

            ]);

                GigLogistic::create([
                    'state_id'=> $request->input('state'),
                    'city'=> $request->input('city'),
                    'address'=> $request->input('address'),
                    'slug' => Str::slug($request->input('city')),
                ]);
  

            return redirect()->back()->with('status', ['text'=>'Location  Successfully published','type'=>'success']);
        }
    }

    public function delete_gig_location($id) 
    {
        $gig = GigLogistic::where('id', $id)->first();
        $gig->delete();

        return redirect()->back()->with('status', ['text'=>'Location was deleted','type'=>'success']);

    }

    public function updateGigLocation(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:gig_logistics,id',
            'state' => 'required|exists:states,id',
            'city' => 'required|string|max:255',
            'address' => 'required|string|max:255'
        ]);

        $gig = GigLogistic::findOrFail($request->id);

        //dd($gig);
        $gig->update([
            'state_id' => $request->state,
            'city' => $request->city,
            'address' => $request->address
        ]);

        return response()->json(['success' => true]);
    }

    public function imageSettings(Request $request)
    {
        $title = "Ad Image Settings | " . config('global.site_name');

        if ($request->isMethod('POST')) {
            $request->validate([
                'max_images'              => 'required|integer|min:1|max:20',
                'min_images'              => 'required|integer|min:1',
                'image_strictness'        => 'required|integer|min:1|max:10',
                'image_min_width'         => 'required|integer|min:100|max:10000',
                'image_min_height'        => 'required|integer|min:100|max:10000',
                'image_recommended_width' => 'required|integer|min:100|max:10000',
                'image_recommended_height'=> 'required|integer|min:100|max:10000',
                'image_min_file_size_kb'  => 'required|integer|min:1',
                'image_max_file_size_mb'  => 'required|integer|min:1|max:50',
            ]);

            // min_images cannot exceed max_images
            if ($request->min_images > $request->max_images) {
                return back()->withInput()->with('status', [
                    'type' => 'danger',
                    'text' => 'Minimum images cannot be greater than maximum images.',
                ]);
            }

            // Minimum dimensions cannot exceed recommended dimensions
            if ($request->image_min_width > $request->image_recommended_width
                || $request->image_min_height > $request->image_recommended_height) {
                return back()->withInput()->with('status', [
                    'type' => 'danger',
                    'text' => 'Minimum dimensions cannot be greater than recommended dimensions.',
                ]);
            }

            // Minimum file size (KB) cannot exceed maximum file size (MB)
            if ($request->image_min_file_size_kb > $request->image_max_file_size_mb * 1024) {
                return back()->withInput()->with('status', [
                    'type' => 'danger',
                    'text' => 'Minimum file size cannot be greater than maximum file size.',
                ]);
            }

            AdSetting::setValue('max_images',               $request->max_images);
            AdSetting::setValue('min_images',                $request->min_images);
            AdSetting::setValue('image_strictness',          $request->image_strictness);
            AdSetting::setValue('image_min_width',            $request->image_min_width);
            AdSetting::setValue('image_min_height',           $request->image_min_height);
            AdSetting::setValue('image_recommended_width',    $request->image_recommended_width);
            AdSetting::setValue('image_recommended_height',   $request->image_recommended_height);
            AdSetting::setValue('image_min_file_size_kb',     $request->image_min_file_size_kb);
            AdSetting::setValue('image_max_file_size_mb',     $request->image_max_file_size_mb);

            return back()->with('status', ['type' => 'success', 'text' => 'Image settings updated successfully.']);
        }

        $settings = [
            'max_images'               => (int) AdSetting::getValue('max_images',               8),
            'min_images'               => (int) AdSetting::getValue('min_images',               3),
            'image_strictness'         => (int) AdSetting::getValue('image_strictness',         7),
            'image_min_width'          => (int) AdSetting::getValue('image_min_width',          800),
            'image_min_height'         => (int) AdSetting::getValue('image_min_height',          600),
            'image_recommended_width'  => (int) AdSetting::getValue('image_recommended_width',  1200),
            'image_recommended_height' => (int) AdSetting::getValue('image_recommended_height',  900),
            'image_min_file_size_kb'   => (int) AdSetting::getValue('image_min_file_size_kb',     50),
            'image_max_file_size_mb'   => (int) AdSetting::getValue('image_max_file_size_mb',     20),
        ];

        return view('admin.settings.ad-image-settings', compact('title', 'settings'));
    }

    public function manageAdmin(Request $request)
    {
        $title = "Manage Admins - " . config('global.site_name');
        $admins = Admin::get();

        if ($request->isMethod('POST')) {
            $request->validate([
                'username' => 'required|string|min:3|max:50|unique:admins,username',
                'email' => 'required|email|unique:admins,email',
                'password' => 'required|string|min:8|confirmed',
            ]);
            //dd($request);
            try {
                $admin = Admin::create([
                    'admin_id' => rand(11111,99999),
                    'username' => $request->username,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                return redirect()->back()->with('status', [
                    'text' => 'Admin user created successfully!',
                    'type' => 'success'
                ]);

            } catch (\Exception $e) {
                return redirect()->back()->with('status', [
                    'text' => 'Error creating admin user. Please try again.',
                    'type' => 'danger'
                ])->withInput($request->except('password', 'password_confirmation'));
            }
        }

        if ($request->isMethod('GET')) {
            return view('admin.settings.admins.users', compact('title','admins'));
        }
    }
}
