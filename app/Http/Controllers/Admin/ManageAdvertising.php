<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Advertising;
use Carbon\Carbon;
use App\Helpers\FileUploadHelper;

class ManageAdvertising extends Controller
{
    public function create_advert(Request $request)
    {

        $title = "New Adverts - " . config('global.site_name');
        $adverts = Advertising::orderBy('created_at', 'desc')->paginate(20);

            if ($request->isMethod('POST')) {
                //dd($request);
            $request->validate([
                'company' => 'required',
                'advert_image' => 'nullable|image|mimes:jpg,png,jpeg,gif|max:3048',
                'advert_mobile_image' => 'nullable|image|mimes:jpg,png,jpeg,gif|max:3048',
                'url' => 'required|url',
                'duration' => 'required',
            ], [
                'advert_image.required_without' => 'Please upload at least a desktop or mobile image.',
            ]);

            if (!$request->hasFile('advert_image') && !$request->hasFile('advert_mobile_image')) {
                return redirect()->back()->withErrors(['advert_image' => 'Please upload at least a desktop or mobile image.'])->withInput();
            }

            $imageName = null;
            if ($request->hasFile('advert_image')) {
                $image = $request->file('advert_image');
                $imageName = time().'.'.$image->extension();
                $imageName = str_replace(' ', '-', $imageName);
                $image->move('uploads/advertising', $imageName);
            }

            $mobileImageName = null;
            if ($request->hasFile('advert_mobile_image')) {
                $mobileImage = $request->file('advert_mobile_image');
                $mobileImageName = time().'_mobile.'.$mobileImage->extension();
                $mobileImageName = str_replace(' ', '-', $mobileImageName);
                $mobileImage->move('uploads/advertising', $mobileImageName);
            }

            $post = Advertising::create([
                'advert_id'=> rand(11111,99999),
                'company'=> $request->input('company'),
                'url'=> $request->input('url'),
                'duration'=> $request->input('duration'),
                'start_date'=> Carbon::now(),
                'type'=> $request->input('type'),
                'image'=> $imageName,
                'mobile_image'=> $mobileImageName,
                'status'=> "active",
            ]);

            return redirect()->back()->with('status', ['text'=>'Advert  Successfully published','type'=>'success']);
        }
        if ($request->isMethod('GET')) {
            return view('admin.advertising.create-advert', compact('title', 'adverts'));
        }

    }

    public function updateAdvert(Request $request, $id)
    {
        //dd($id);
        // Validation rules
        $request->validate([
            'company' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'duration' => 'required|integer',
            'type' => 'required|string|in:banner,sidebar',
            'status' => 'required|string|in:active,inactive,pending',
            'advert_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'advert_mobile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Find the advertisement
            $advert = Advertising::where('advert_id', $id)->firstOrFail();
            //dd($advert);
            // Update basic fields
            $advert->company = $request->company;
            $advert->url = $request->url;
            $advert->duration = $request->duration;
            $advert->type = $request->type;
            $advert->status = $request->status;

            // Reset start_date when reactivating so the duration window starts fresh
            if ($request->status === 'active') {
                $advert->start_date = Carbon::now();
            }

            // Handle desktop image upload if provided
            if ($request->hasFile('advert_image')) {
                if ($advert->image && file_exists(public_path('uploads/advertising/' . $advert->image))) {
                    unlink(public_path('uploads/advertising/' . $advert->image));
                }

                $image = $request->file('advert_image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/advertising'), $imageName);
                $advert->image = $imageName;
            }

            // Handle mobile image upload if provided
            if ($request->hasFile('advert_mobile_image')) {
                if ($advert->mobile_image && file_exists(public_path('uploads/advertising/' . $advert->mobile_image))) {
                    unlink(public_path('uploads/advertising/' . $advert->mobile_image));
                }

                $mobileImage = $request->file('advert_mobile_image');
                $mobileImageName = time() . '_mobile_' . uniqid() . '.' . $mobileImage->getClientOriginalExtension();
                $mobileImage->move(public_path('uploads/advertising'), $mobileImageName);
                $advert->mobile_image = $mobileImageName;
            }

            // Save the updated advertisement
            $advert->save();

            return redirect()->back()->with('status', [
                'type' => 'success',
                'text' => 'Advertisement updated successfully!'
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with('status', [
                'type' => 'danger',
                'text' => 'Error updating advertisement: ' . $e->getMessage()
            ])->withInput();
        }
    }


    public function delete_advert(Request $request, $id)
{
    $advert = Advertising::where('advert_id', $id)->first();
    
    if ($advert) {
        // Check if the advert has an image and delete it
        if ($advert->image) {
            // Get the image path - adjust based on your storage structure
            $imagePath = public_path('uploads/advertising/' . $advert->image);

            // Check if file exists and delete it
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        // Delete the advert record
        $advert->delete();
        
        return back()->with('success', 'Advert deleted successfully.');
    }
    
    return back()->with('error', 'Advert not found.');
}
}
