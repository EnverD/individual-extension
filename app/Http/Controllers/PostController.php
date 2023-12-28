<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Subpage;
use Illuminate\Support\Facades\Auth;
use App\Models\Like;
use Illuminate\Support\Str;


class PostController extends Controller
{
     // Show a form for creating a new post in a subpage
     public function create(Subpage $subpage)
     {
         return view('posts.create', compact('subpage'));
     }
 
    public function store(Request $request, $slug)
    {
        $subpage = Subpage::where('slug', $slug)->firstOrFail();

        $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        $postSlug = Str::slug($request->title);

        // Ensure uniqueness of the post slug within the subpage
        $postSlugCount = Post::where('subpage_id', $subpage->id)
                             ->where('slug', 'LIKE', $postSlug . '%')
                             ->count();


        $post = new Post();
        $post->title = $request->title;
        $post->content = $request->content;
        $post->user_id = Auth::id();
        $post->subpage_id = $subpage->id;
        $post->slug = $postSlugCount > 0 ? "{$postSlug}-{$postSlugCount}" : $postSlug;

        $post->save();

        return redirect()->route('subpages.showSubpage', $subpage->slug);
    }


    public function toggleLike($slug, $post_slug)
    {
        // First, get the correct subpage using its slug
        // - this is necessary because the post_slug only is unique for the subpage
        $subpage = Subpage::where('slug', $slug)->firstOrFail();

        // Then, get the post by its slug and make sure it belongs to the correct subpage
        $post = Post::where('slug', $post_slug)->where('subpage_id', $subpage->id)->firstOrFail();

        // Now toggle the like for the correct post
        $like = $post->likes()->where('user_id', auth()->id())->first();

        if ($like) {
            $like->delete();
        } else {
            $post->likes()->create(['user_id' => auth()->id()]);
        }

        return back();
    }

    public function toggleDislike($slug, $post_slug)
    {
        // Get the post and toggle the dislike
        // Check if the user already disliked this post
        $dislike = $post->dislikes()->where('user_id', auth()->id())->first();
    
        if ($dislike) {
            $dislike->delete(); // Remove the dislike if it exists
        } else {
            // Add a dislike if it doesn't exist
            $post->dislikes()->create(['user_id' => auth()->id()]);
    
            // Remove the like (upvote) if it exists
            $like = $post->likes()->where('user_id', auth()->id())->first();
            if ($like) {
                $like->delete();
            }
        }
    
        return back(); // Redirect back to the previous page
    }

    public function loadMore(Request $request) {
        $offset = $request->get('offset'); // Get the offset parameter
        $posts = Post::skip($offset)->take(5)->get(); // Fetch posts
        
        return response()->json(['posts' => $posts]);
    }
    
    
    public function destroy($slug, $postSlug)
    {
         // First, get the correct subpage using its slug
        // - this is necessary because the post_slug only is unique for the subpage
        $subpage = Subpage::where('slug', $slug)->firstOrFail();

        // Then, get the post by its slug and make sure it belongs to the correct subpage
        $post = Post::where('slug', $postSlug)->where('subpage_id', $subpage->id)->firstOrFail();
    
        if ($post->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
    
        $post->delete();
    
        return back()->with('status', 'Post deleted successfully.');
    }




}