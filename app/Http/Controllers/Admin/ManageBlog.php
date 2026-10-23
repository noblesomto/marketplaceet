<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use Illuminate\Support\Facades\Storage;


class ManageBlog extends Controller
{
    public function index()
    {
        $title = "Blog Posts | " . config('global.site_name');
        $page_title = "Blog Posts | " . config('global.site_name');
        $blogs = Blog::latest()->paginate(10);
        return view('admin.blogs.index', compact('blogs','title','page_title'));
    }

    public function create()
    {
        $title = "Blog Posts | " . config('global.site_name');
        $page_title = "Blog Posts | " . config('global.site_name');
        return view('admin.blogs.create',  compact('title','page_title'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
        'title' => 'required|string|max:255',
        'category' => 'required|string',
        'content' => 'required|string',
        'keywords' => 'nullable|string',
        'meta_description' => 'nullable|string',
        'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
    ]);

    $blog = Blog::create([
        'title' => $validated['title'],
        'content' => $validated['content'],
        'category' => $validated['category'],
        'keywords' => $validated['keywords'] ?? null,
        'meta_description' => $validated['meta_description'] ?? null,
    ]);
        // Add featured image to the blog-specific collection
        if ($request->hasFile('featured_image')) {
            $blog->addMediaFromRequest('featured_image')
                ->toMediaCollection('blog_featured_image');
        }

        return redirect()->route('blogs.show', $blog)
            ->with('success', 'Blog created successfully!');
    }


     public function edit(Blog $blog)
    {
        $title = "Blog Posts | " . config('global.site_name');
        $page_title = "Blog Posts | " . config('global.site_name');
        return view('admin.blogs.edit', compact('blog','title','page_title'));
    }

    public function update(Request $request, Blog $blog)
    {
        //dd($request);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'content' => 'required|string',
            'keywords' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);
        //dd($validated);
        $blog->update([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'content' => $validated['content'],
            'keywords' => $validated['keywords'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
        ]);

        // Update featured image (singleFile will auto-delete the old one)
        if ($request->hasFile('featured_image')) {
            $blog->addMediaFromRequest('featured_image')
                ->toMediaCollection('blog_featured_image');
        }

        return redirect()->route('blogs.show', $blog)
            ->with('success', 'Blog updated successfully!');
    }

    public function destroy(Blog $blog)
    {
        $blog->delete();

        return redirect()->route('blogs.index')
            ->with('success', 'Blog deleted successfully!');
    }

    /**
     * Remove featured image only
     */
    public function removeFeaturedImage(Blog $blog)
    {
        $blog->clearMediaCollection('blog_featured_image');

        return back()->with('success', 'Featured image removed successfully!');
    }

    public function show(Blog $blog)
    {
        $title = "Blog Posts | " . config('global.site_name');
        $page_title = "Blog Posts | " . config('global.site_name');
        return view('admin.blogs.show', compact('blog','title','page_title'));
    }

    public function upload(Request $request)
    {
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('tinymce', 'public');
            return response()->json(['location' => asset('uploads/' . $path)]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }

    public function toggleStatus(Blog $blog)
    {
        $blog->status = $blog->status === 'published' ? 'draft' : 'published';
        $blog->save();

        return response()->json([
            'status' => $blog->status,
            'badgeClass' => $blog->status === 'published' ? 'bg-success' : 'bg-secondary',
            'label' => ucfirst($blog->status),
        ]);
    }

}
