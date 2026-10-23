<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\StoreSubCategoryRequest;
use App\Http\Requests\Admin\StoreBrandRequest;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brands;
use App\Models\VehicleModel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManageCategories extends Controller
{

    public function category(StoreCategoryRequest $request)
{
    $title = "Category - " . config('global.site_name');
    $category = Category::orderBy('category','asc')->get();
    $sub_category = SubCategory::orderBy('sub_category','asc')->get();

    if ($request->isMethod('POST')) {

        $fields = [
            'category'         => $request->input('category'),
            'seo_group'        => $request->input('seo_group', 'product'),
            'meta_title'       => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'keywords'         => $request->input('keywords'),
        ];

        // Check if it's an update or create
        if ($request->has('category_id') && $request->input('category_id')) {
            $category = Category::find($request->input('category_id'));
            if ($category) {
                $category->update($fields);
                $message = 'Category successfully updated';
            }
        } else {
            Category::create($fields);
            $message = 'Category successfully published';
        }

        return redirect('/admin/category')->with('status', ['text' => $message, 'type' => 'success']);
    }

    if ($request->isMethod('GET')) {
        return view('admin.category.category', compact('title', 'category', 'sub_category'));
    }
}

    public function delete_category($id)
    {
        $cat = Category::where('cat_id', $id)->first();
        $cat->delete();
        $subcat = SubCategory::where('cat_id', $id)->first();
        if($subcat !=null){
          $subcat->delete();
        }
        $brand = Brands::where('cat_id', $id)->first();
        if($brand !=null){
          $brand->delete();
        }

        return redirect("admin/category")->with('status', ['text'=>'Category was deleted','type'=>'success']);
    }

    public function edit_category($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return redirect('/admin/category')->with('status', ['text'=>'Category not found','type'=>'danger']);
        }

        // Return JSON for AJAX or redirect with data
        return response()->json($category);
    }

    public function sub_category(StoreSubCategoryRequest $request, $id)
{
    $title = "Sub Category - " . config('global.site_name');
    $cat = Category::where('id', $id)->first();
    $subcat = SubCategory::where('cat_id', $id)->orderBy('sub_category','asc')->get();

    if ($request->isMethod('POST')) {

        // Handle icon upload
        $iconFilename = null;
        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            $iconFilename = time() . '_' . preg_replace('/\s+/', '-', $file->getClientOriginalName());
            $file->move(self::iconUploadPath(), $iconFilename);
        }

        // Check if it's an update or create
        if ($request->has('subcategory_id') && $request->input('subcategory_id')) {
            // Update existing subcategory
            $subcategory = SubCategory::find($request->input('subcategory_id'));
            if ($subcategory) {
                $updateData = [
                    'cat_id'           => $request->input('category'),
                    'sub_category'     => $request->input('sub_category'),
                    'meta_title'       => $request->input('meta_title'),
                    'meta_description' => $request->input('meta_description'),
                    'keywords'         => $request->input('keywords'),
                ];

                if ($iconFilename) {
                    // Delete old icon if it exists
                    if ($subcategory->icon) {
                        $oldPath = self::iconUploadPath($subcategory->icon);
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                    $updateData['icon'] = $iconFilename;
                } elseif ($request->boolean('remove_icon') && $subcategory->icon) {
                    $oldPath = self::iconUploadPath($subcategory->icon);
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                    $updateData['icon'] = null;
                }

                $subcategory->update($updateData);
                $message = 'Sub Category successfully updated';
            }
        } else {
            // Create new subcategory
            $subcategory = SubCategory::create([
                'cat_id'           => $request->input('category'),
                'sub_category'     => $request->input('sub_category'),
                'icon'             => $iconFilename,
                'meta_title'       => $request->input('meta_title'),
                'meta_description' => $request->input('meta_description'),
                'keywords'         => $request->input('keywords'),
            ]);

            $message = 'Sub Category successfully published';
        }

        return redirect('/admin/sub-category/'.$id)->with('status', ['text' => $message, 'type' => 'success']);
    }

    if ($request->isMethod('GET')) {
        return view('admin.category.sub-category', compact('title', 'cat','subcat'));
    }
}

    public function delete_subcategory($id, $cat)
    {
        $subcat = SubCategory::where('id', $id)->first();

        // Delete icon file from disk
        if ($subcat && $subcat->icon) {
            $iconPath = self::iconUploadPath($subcat->icon);
            if (file_exists($iconPath)) {
                unlink($iconPath);
            }
        }

        $subcat->delete();
        $brand = Brands::where('subcat_id', $id)->first();
        if($brand !=null){
          $brand->delete();
        }

        return redirect("admin/sub-category/".$cat)->with('status', ['text'=>'Sub Category was deleted','type'=>'success']);
    }

    public function delete_subcategory_icon($id)
    {
        $subcat = SubCategory::findOrFail($id);

        if ($subcat->icon) {
            $iconPath = self::iconUploadPath($subcat->icon);
            if (file_exists($iconPath)) {
                unlink($iconPath);
            }
            $subcat->update(['icon' => null]);
        }

        return redirect()->back()->with('status', ['text' => 'Icon removed successfully', 'type' => 'success']);
    }

    public function brand(StoreBrandRequest $request, $id)
{
    $title = "Brands - " . config('global.site_name');
    $cat = SubCategory::where('id', $id)->first();
    $brand = Brands::where('subcat_id', $id)->orderBy('brand','asc')->get();

    if ($request->isMethod('POST')) {

        // Check if it's an update or create
        if ($request->has('brand_id') && $request->input('brand_id')) {
            // Update existing brand
            $brand = Brands::find($request->input('brand_id'));
            if ($brand) {
                $brand->update([
                    'subcat_id' => $request->input('sub_category'),
                    'brand' => $request->input('brand'),
                    'meta_title' => $request->input('meta_title'),
                    'meta_description' => $request->input('meta_description'),
                    'keywords' => $request->input('keywords'),
                ]);

                $message = 'Brand successfully updated';
            }
        } else {
            // Create new brand
            $brand = Brands::create([
                'subcat_id' => $request->input('sub_category'),
                'brand' => $request->input('brand'),
                'meta_title' => $request->input('meta_title'),
                'meta_description' => $request->input('meta_description'),
                'keywords' => $request->input('keywords'),
            ]);

            $message = 'Brand successfully published';
        }

        return redirect('/admin/brand/'.$request->input('sub_category'))->with('status', ['text' => $message, 'type' => 'success']);
    }

    if ($request->isMethod('GET')) {
        return view('admin.category.brand', compact('title', 'brand','cat'));
    }
}

    public function model(Request $request, $id)
    {
        $title = "Models - " . config('global.site_name');
        $brand = Brands::where('id', $id)->first();
        $model = VehicleModel::where('brand_id', $id)->orderBy('model','asc')->get();

        if ($request->isMethod('POST')) {
            $request->validate([
                'brand' => 'required',
                'model' => 'required',
                'meta_title' => 'nullable|max:255',
                'meta_description' => 'nullable|max:500',
                'year_start' => 'nullable|integer|min:1900|max:' . date('Y'),
                'year_end' => 'nullable|integer|min:1900|max:' . date('Y'),
            ]);

            // Check if it's an update or create
            if ($request->has('model_id') && $request->input('model_id')) {
                // Update existing model
                $model = VehicleModel::find($request->input('model_id'));
                if ($model) {
                    $model->update([
                        'brand_id' => $request->input('brand'),
                        'model' => $request->input('model'),
                        'meta_title' => $request->input('meta_title'),
                        'meta_description' => $request->input('meta_description'),
                        'year_start' => $request->input('year_start'),
                        'year_end' => $request->input('year_end'),
                    ]);

                    $message = 'Model successfully updated';
                }
            } else {
                // Create new model
                $model = VehicleModel::create([
                    'brand_id' => $request->input('brand'),
                    'model' => $request->input('model'),
                    'meta_title' => $request->input('meta_title'),
                    'meta_description' => $request->input('meta_description'),
                    'year_start' => $request->input('year_start'),
                    'year_end' => $request->input('year_end'),
                ]);

                $message = 'Model successfully published';
            }

            return redirect('/admin/model/'.$request->input('brand'))->with('status', ['text' => $message, 'type' => 'success']);
        }

        if ($request->isMethod('GET')) {
            return view('admin.category.model', compact('title', 'model','brand'));
        }
    }

    public function delete_model($id, $cat)
    {
        $model = VehicleModel::where('id', $id)->first();
        $model->delete();

        return redirect("admin/model/".$cat)->with('status', ['text'=>'Model was deleted','type'=>'success']);
    }

    public function delete_brand($id, $cat)
    {
        $brand = Brands::where('id', $id)->first();
        $brand->delete();

        return redirect("admin/brand/".$cat)->with('status', ['text'=>'Brand was deleted','type'=>'success']);
    }

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

    public function fetch_model($cat_id)
    {
        $model = VehicleModel::where('brand_id', $cat_id)->get();
        //dd($model);
        return response()->json($model);
    }

    /**
     * Resolve the correct filesystem path for subcategory icon storage.
     *
     * On the server, the webroot is public_html/ (a sibling of marketplace/),
     * not marketplace/public/. Set PUBLIC_ASSETS_PATH in .env to the absolute
     * path of public_html/ on the server. Locally, falls back to public_path().
     *
     * Server .env example:
     *   PUBLIC_ASSETS_PATH=/home/admin/domains/marketplace.et/public_html
     */
    private static function iconUploadPath(string $filename = ''): string
    {
        $base = rtrim(env('PUBLIC_ASSETS_PATH', public_path()), '/');
        $dir  = $base . '/frontend/images/subcategory-icons';
        return $filename ? $dir . '/' . $filename : $dir;
    }

}
