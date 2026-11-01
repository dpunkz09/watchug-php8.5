<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Broadcast;
use App\Models\Collection;
use App\Models\Community;
use App\Models\Genre;
use App\Models\Module;
use App\Models\PostEpisode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Post;

class IndexController extends Controller
{
    public function index()
    {
        // Seo
        $config['title'] = config('settings.title');
        $config['description'] = config('settings.description');


        $modules = Module::where('status', 'active')->orderby('sortable', 'asc')->get();

        $listings = [];
        foreach ($modules as $module) {
            $limit = $module->arguments->limit ?? 10;
            if($module->slug == 'slider') {
                $listings['slider'] = Cache::remember('home-slider', 3600, function () use ($limit) {
                    return Post::with('genres')
                        ->where('slider', 'active')
                        ->where('status','publish')
                        ->orderBy('id', 'desc')
                        ->limit($limit)
                        ->get()
                        ->map(function ($post) {
                            return [
                                'id' => $post->id,
                                'title' => $post->title,
                                'slug' => $post->slug,
                                'type' => $post->type,
                                'imageurl' => $post->imageurl,
                                'coverurl' => $post->coverurl,
                                'slideurl' => $post->slideurl, // Add slideurl accessor
                                'vote_average' => $post->vote_average,
                                'runtime' => $post->runtime,
                                'release_date' => $post->release_date?->format('Y-m-d'),
                                'genres' => $post->genres->toArray(),
                            ];
                        })
                        ->toArray();
                });
            } elseif($module->slug == 'movie') {
                $listings['movie'] = Cache::remember('home-movie', 3600, function () use ($limit) {
                    return Post::with('genres')
                        ->where('type', 'movie')
                        ->where('status', 'publish')
                        ->orderBy('id', 'desc')
                        ->limit($limit ?? 16)
                        ->get()
                        ->map(function ($post) {
                            return [
                                'id' => $post->id,
                                'title' => $post->title,
                                'slug' => $post->slug,
                                'type' => $post->type,
                                'imageurl' => $post->imageurl,
                                'vote_average' => $post->vote_average,
                                'runtime' => $post->runtime,
                                'release_date' => $post->release_date?->format('Y-m-d'),
                                'genres' => $post->genres->toArray(),
                            ];
                        })
                        ->toArray();
                });
            } elseif($module->slug == 'tv') {
                $listings['tv'] = Cache::remember('home-tv', 3600, function () use ($limit) {
                    return Post::with('genres')
                        ->where('type', 'tv')
                        ->where('status','publish')
                        ->orderby('id','desc')
                        ->limit($limit ?? 16)
                        ->get()
                        ->map(function ($post) {
                            return [
                                'id' => $post->id,
                                'title' => $post->title,
                                'slug' => $post->slug,
                                'type' => $post->type,
                                'imageurl' => $post->imageurl,
                                'vote_average' => $post->vote_average,
                                'runtime' => $post->runtime,
                                'release_date' => $post->release_date?->format('Y-m-d'),
                                'genres' => $post->genres->toArray(),
                            ];
                        })
                        ->toArray();
                });
            } elseif($module->slug == 'episode') {
                $listings['episode'] = Cache::remember('home-episode', 1800, function () use ($limit) {
                    return PostEpisode::with(['post.genres', 'season'])
                        ->where('status','publish')
                        ->orderby('id','desc')
                        ->limit($limit ?? 16)
                        ->get()
                        ->map(function ($episode) {
                            return [
                                'id' => $episode->id,
                                'name' => $episode->name,
                                'episode_number' => $episode->episode_number,
                                'imageurl' => $episode->imageurl, // This calls the accessor
                                'status' => $episode->status,
                                'post' => $episode->post ? [
                                    'id' => $episode->post->id,
                                    'title' => $episode->post->title,
                                    'slug' => $episode->post->slug,
                                    'type' => $episode->post->type,
                                    'imageurl' => $episode->post->imageurl, // Fallback image
                                ] : null,
                                'season' => $episode->season ? [
                                    'id' => $episode->season->id,
                                    'season_number' => $episode->season->season_number,
                                    'name' => $episode->season->name,
                                ] : null,
                            ];
                        })
                        ->toArray();
                });
            } elseif($module->slug == 'featured') {
                $listings['featured'] = Cache::remember('home-featured', 3600, function () use ($limit) {
                    return Post::with('genres')
                        ->where('featured', 'active')
                        ->where('status','publish')
                        ->orderby('id','desc')
                        ->limit($limit ?? 16)
                        ->get()
                        ->map(function ($post) {
                            return [
                                'id' => $post->id,
                                'title' => $post->title,
                                'slug' => $post->slug,
                                'type' => $post->type,
                                'storyurl' => $post->storyurl,
                                'genres' => $post->genres->toArray(),
                            ];
                        })
                        ->toArray();
                });
            }elseif($module->slug == 'broadcast') {
                $listings['broadcast'] = Cache::remember('home-broadcast', 1800, function () use ($limit) {
                    return Broadcast::where('status','publish')->orderby('id','desc')->limit($limit ?? 16)->get()->toArray();
                });
            } elseif($module->slug == 'genre') {
                $listings['genres'] = Cache::remember('home-genre', 7200, function () use ($limit) {
                    return Genre::withCount(['posts'])
                        ->where('featured', 'active')
                        ->limit(5)
                        ->get()
                        ->map(function ($genre) {
                            return [
                                'id' => $genre->id,
                                'title' => $genre->title,
                                'slug' => $genre->slug,
                                'color' => $genre->color,
                                'posts_count' => $genre->posts_count,
                                'image' => $genre->getImage(), // Call the getImage() method
                            ];
                        })
                        ->toArray();
                });
            } elseif($module->slug == 'collection') {
                $listings['collection'] = Cache::remember('home-collection', 7200, function () use ($limit) {
                    return Collection::withCount('posts')
                        ->with('postsLimited')
                        ->where('featured', 'active')
                        ->orderBy('id','desc')
                        ->limit(4)
                        ->get()
                        ->map(function ($collection) {
                            return [
                                'id' => $collection->id,
                                'title' => $collection->title,
                                'slug' => $collection->slug,
                                'posts_count' => $collection->posts_count,
                                'postsLimited' => $collection->postsLimited->map(function ($post) {
                                    return [
                                        'id' => $post->id,
                                        'title' => $post->title,
                                        'imageurl' => $post->imageurl,
                                    ];
                                })->toArray(),
                            ];
                        })
                        ->toArray();
                });
            } elseif($module->slug == 'blog') {
                $listings['blog'] = Cache::remember('home-blog', 3600, function () use ($limit) {
                    return Article::where('featured', 'active')
                        ->orderBy('id','desc')
                        ->limit($limit)
                        ->get()
                        ->map(function ($article) {
                            return [
                                'id' => $article->id,
                                'title' => $article->title,
                                'slug' => $article->slug,
                                'imageurl' => $article->imageurl ?? null,
                                'created_at' => $article->created_at->format('Y-m-d H:i:s'),
                            ];
                        })
                        ->toArray();
                });
            }
        }


        return view('home.index', compact('config', 'listings', 'modules'));
    }
    public function landing()
    {
        // Seo
        $config['title'] = config('settings.title');
        $config['description'] = config('settings.description');
        return view('home.landing', compact('config'));
    }
    public function search(Request $request) {
        return redirect()->route('search',$request->q);
    }
}
