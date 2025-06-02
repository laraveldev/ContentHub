<?php

namespace App\Http\Controllers\Web;

use App\Models\Author;
use App\Models\Category;
use App\Models\Content;
use App\Models\Genre;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contents = Content::with('category', 'authors', 'genres')->get();
        return view('content.index', ['contents' => $contents]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $autohrs    = Author::all();
        $gernes     = Genre::all();
        return view('content.create',
            [
                'categories' => $categories,
                'authors'    => $autohrs,
                'genres'     => $gernes
            ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fileName  = pathinfo($request->file('thumbnail')->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = pathinfo($request->file('thumbnail')->getClientOriginalName(), PATHINFO_EXTENSION);
        $fileName  = $fileName.'_'.time().'.'.$extension;

        $filePath = $request
            ->file('thumbnail')
            ->storeAs('images', $fileName, 'public');


        $content              = new Content();
        $content->title       = $request->input('title');
        $content->description = $request->input('description');
        $content->url         = $request->input('url') ?? $filePath;
        $content->category_id = $request->input('category_id');
        $content->save();

        if ($request->has('author_ids')) {
            $content->authors()->attach($request->input('author_ids'));
        }

        if ($request->has('genre_ids')) {
            $content->genres()->attach($request->input('genre_ids'));
        }
        return redirect('/contents');
    }

    /**
     * Display the specified resource.
     */
    public function show(Content $content)
    {
        $content->load('category', 'authors', 'genres');

        $releatedContents = Content::where('category_id', $content->category_id)
                                   ->where('id', '!=', $content->id)
                                   ->inRandomOrder()
                                   ->take(3)
                                   ->get();
        return view('content.show', compact('content', 'releatedContents'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Content $content)
    {
        $categories = Category::all();
        return view('content.edit', ['content' => $content, 'categories' => $categories]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Content $content)
    {
        $content->update($request->all());
        return redirect('/contents');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Content $content)
    {
        $content->delete();
        return redirect('/contents');
    }
}
