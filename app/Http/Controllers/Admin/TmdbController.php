<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostEpisode;
use App\Models\PostSeason;
use App\Models\PostVideo;
use App\Models\Tag;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Traits\PostTrait;

class TmdbController extends Controller
{

    use PostTrait;

    public function show(Request $request)
    {
        $config = [
            'title' => __('Tool'),
            'nav' => 'tool',
        ];
        if (!config('settings.tmdb_api') || !config('settings.tmdb_language')) {
            return redirect()->route('admin.tmdb.settings');
        }
        return view('admin.tmdb.show', compact('config', 'request'));
    }

    public function fetch(Request $request)
    {

        if ($request->isMethod('post')) {
            return redirect()->route('admin.tmdb.fetch', ['type' => $request->input('type'), 'q' => $request->input('q'), 'sortable' => $request->input('sortable')]);
        }
        $config = [
            'title' => __('Tool'),
            'nav' => 'tool',
        ];
        $listings = array();
        if ($request->has('type')) {
            $tool_filter = null;
            if ($request->has('page')) {
                $tool_filter .= '&page=' . $request->get('page');
            }
            if ($request->has('q')) {
                $result = Http::get('https://api.themoviedb.org/3/search/' . $request->type . '?query=' . $request->q . $tool_filter . '&api_key=' . config('settings.tmdb_api') . '&language=' . config('settings.tmdb_language'));
            } elseif ($request->has('sortable')) {
                $result = Http::get('https://api.themoviedb.org/3/discover/' . $request->type . '?sort_by=' . $request->sortable . $tool_filter . '&api_key=' . config('settings.tmdb_api') . '&language=' . config('settings.tmdb_language'));
            }
            $result = json_decode($result->getBody(), true);
            if (isset($result['results'])) {
                foreach ($result['results'] as $item) {

                    $check = Post::where('tmdb_id', $item['id'])->first();
                    if (isset($item['poster_path']) and empty($check->id)) {
                        $listings[] = $this->tmdbFetchTrait($item, $request->type);
                    }
                }
            }
        }
        return view('admin.tmdb.show', compact('config', 'request', 'listings', 'result'));
    }

    public function settings(Request $request)
    {
        $config = [
            'title' => __('Tool'),
            'nav' => 'tool',
        ];
        return view('admin.tmdb.settings', compact('config', 'request'));
    }

    public function update(Request $request)
    {
        $save_data = [
            'tmdb_api',
            'tmdb_language',
            'tmdb_people_limit',
            'tmdb_image',
            'draft_post',
            'import_season',
            'import_episode',
            'vidsrc',
        ];
        foreach ($save_data as $item) {
            update_settings($item, $request->$item);
        }
        Cache::forget('settings');
        Cache::flush();

        return redirect()->route('admin.tmdb.settings')->with('success', __(':title has been updated', ['title' => 'Tool']));
    }


    public function store(Request $request)
    {

        $postArray = $this->tmdbApiTrait($request->type, $request->tmdb_id);
        $model = new Post();

        $folderDate = date('m-Y') . '/';

        if(config('settings.tmdb_image') != 'active') {

            if ($postArray['image']) {
                $imagename = Str::random(10);
                $imageFile = $postArray['image'];
                $uploaded_image = fileUpload($imageFile, config('attr.poster.path').$folderDate.'/',
                    config('attr.poster.size_x'), config('attr.poster.size_y'), $imagename);
                fileUpload($imageFile, config('attr.poster.path').$folderDate.'/', config('attr.poster.size_x'),
                    config('attr.poster.size_y'), $imagename, 'webp');
                $model->image = $uploaded_image;
            }

            if ($postArray['cover']) {
                $imagename = Str::random(10);
                $coverFile = $postArray['cover'];
                $uploaded_cover = fileUpload($coverFile, config('attr.poster.path').$folderDate.'/',
                    config('attr.poster.cover_size_x'), config('attr.poster.cover_size_y'), 'cover-'.$imagename);
                fileUpload($coverFile, config('attr.poster.path').$folderDate.'/', config('attr.poster.cover_size_x'),
                    config('attr.poster.cover_size_y'), 'cover-'.$imagename, 'webp');
                $model->cover = $uploaded_cover;
            }

            if ($postArray['slide']) {
                $imagename = Str::random(10);
                $slideFile = $postArray['slide'];
                $uploaded_slide = fileUpload($slideFile, config('attr.poster.path').$folderDate.'/',
                    config('attr.poster.slide_x'), config('attr.poster.slide_y'), 'slide-'.$imagename);
                fileUpload($slideFile, config('attr.poster.path').$folderDate.'/', config('attr.poster.slide_x'),
                    config('attr.poster.slide_y'), 'slide-'.$imagename, 'webp');
                $model->slide = $uploaded_slide;
            }
            if ($postArray['story']) {
                $imagename = Str::random(10);
                $storyFile = $postArray['story'];
                $uploaded_story = fileUpload($storyFile, config('attr.poster.path').$folderDate.'/',
                    config('attr.poster.story_x'), config('attr.poster.story_y'), 'story-'.$imagename);
                fileUpload($storyFile, config('attr.poster.path').$folderDate.'/', config('attr.poster.story_x'),
                    config('attr.poster.story_y'), 'story-'.$imagename, 'webp');
                $model->story = $uploaded_story;
            }
        }

        $model->type = $postArray['type'];
        $model->title = $postArray['title'];
        $model->title_sub = $postArray['title_sub'];
        $model->slug = SlugService::createSlug(Post::class, 'slug', $postArray['title']);
        $model->tagline = $postArray['tagline'];
        $model->overview = $postArray['overview'];
        $model->release_date = $postArray['release_date'];
        $model->runtime = $postArray['runtime'];
        $model->vote_average = $postArray['vote_average'];
        $model->country_id = $postArray['country_id'];
        $model->trailer = $postArray['trailer'];
        $model->tmdb_image = $postArray['tmdb_image'];
        $model->imdb_id = $postArray['imdb_id'];
        $model->tmdb_id = $postArray['tmdb_id'];
        $model->meta_title = $request->input('meta_title');
        $model->meta_description = $request->input('meta_description');
        $model->status = config('settings.draft_post') == 'active' ? 'draft' : 'publish';
        $model->save();

        // Category
        if (isset($postArray['genres'])) {
            foreach ($postArray['genres'] as $key) {
                $syncCategories[] = $key['current_id'];
            }
            $model->genres()->sync($syncCategories);
        }
        // Tag
        if (isset($postArray['tags'])) {
            $tagArray = array();
            foreach ($postArray['tags'] as $tag) {
                if ($tag) {
                    $tagComponent = Tag::where('type', 'post')->firstorcreate(array('tag' => $tag, 'type' => 'post'));
                    $tagArray[$tagComponent->id] = ['post_id' => $model->id, 'tagged_id' => $tagComponent->id];
                }
            }
            $model->tags()->sync($tagArray);
        }

        // Video
        if (isset($postArray['videos'])) {
            foreach ($postArray['videos'] as $key) {
                if ($key['link']) {
                    $video = new PostVideo();
                    $video->type = $key['type'];
                    $video->link = $key['link'];
                    $model->videos()->save($video);
                }
            }
        }

        // People
        if (isset($postArray['peoples'])) {
            foreach ($postArray['peoples'] as $key) {
                $traitPeople = $this->PeopleTmdb($key);
                if (!empty($traitPeople->id)) {
                    $model->peoples()->attach($traitPeople->id);
                }
            }
        }

        // Season
        if (isset($postArray['seasons'])) {
            foreach ($postArray['seasons'] as $key) {
                if ($key['season_number']) {
                    $season = new PostSeason();
                    $season->name = $key['name'];
                    $season->season_number = $key['season_number'];
                    $model->seasons()->save($season);

                    $episodes = json_decode($key['episode'], true);
                    foreach ($episodes as $episodeKey) {
                        $episode = new PostEpisode();
                        if (config('settings.tmdb_image') != 'active') {
                            if (isset($episodeKey['image'])) {
                                $imagename = Str::random(10);
                                $imageFile = $episodeKey['image'];
                                $uploaded_image = fileUpload($imageFile, config('attr.poster.episode_path') . $folderDate, config('attr.poster.episode_size_x'), config('attr.poster.episode_size_y'), $imagename);
                                fileUpload($imageFile, config('attr.poster.episode_path') . $folderDate, config('attr.poster.episode_size_x'), config('attr.poster.episode_size_y'), $imagename, 'webp');

                                $episode->image = $uploaded_image;
                            }
                        }
                        $episode->post_id = $model->id;
                        $episode->name = $episodeKey['name'];
                        $episode->season_number = $season->season_number;
                        $episode->episode_number = $episodeKey['episode_number'];
                        $episode->overview = $episodeKey['overview'];
                        $episode->tmdb_image = $episodeKey['tmdb_image'];
                        $episode->runtime = isset($episodeKey['runtime']) ? $episodeKey['runtime'] : null;
                        $episode->status = config('settings.draft_post') == 'active' ? 'draft' : 'publish';
                        $season->episodes()->save($episode);
                    }
                }
            }
        }

    }

    public function tmdbSingleFetch(Request $request)
    {
        $postArray = $this->tmdbApiTrait($request->type, $request->tmdb_id);
        return json_encode($postArray);
    }

    public function tmdbEpisodeFetch(Request $request)
    {
        $postArray = $this->tmdbEpisodeApiTrait($request);
        return json_encode($postArray);
    }

    public function bulkImport(Request $request)
    {
        $config = [
            'title' => __('Bulk Import'),
            'nav' => 'tool',
        ];
        
        if (!config('settings.tmdb_api') || !config('settings.tmdb_language')) {
            return redirect()->route('admin.tmdb.settings')->with('error', 'Please configure TMDB API settings first');
        }

        return view('admin.tmdb.bulk-import', compact('config'));
    }

    public function bulkImportStart(Request $request)
    {
        $request->validate([
            'type' => 'required|in:movie,tv',
            'source' => 'required|in:vidsrc'
        ]);

        $type = $request->input('type');
        $importId = Str::random(16);

        // Fetch the TMDB IDs from vidsrc
        $url = $type === 'movie' 
            ? 'https://vidsrc.sh/ids/movie_tmdb.txt' 
            : 'https://vidsrc.sh/ids/tv_tmdb.txt';

        try {
            $response = Http::timeout(30)->get($url);
            
            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch TMDB IDs from source'
                ], 400);
            }

            $content = $response->body();
            $tmdbIds = array_filter(array_map('trim', explode("\n", $content)));

            // Remove duplicates and already imported
            $tmdbIds = array_unique($tmdbIds);
            $existingIds = Post::where('type', $type)->pluck('tmdb_id')->toArray();
            $tmdbIds = array_diff($tmdbIds, $existingIds);
            $tmdbIds = array_values($tmdbIds);

            if (empty($tmdbIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No new items to import. All items already exist in database.'
                ], 400);
            }

            // Initialize progress cache
            Cache::put("bulk_import_{$importId}", [
                'total' => count($tmdbIds),
                'processed' => 0,
                'completed' => 0,
                'failed' => 0,
                'skipped' => 0,
                'status' => 'processing',
                'type' => $type,
                'started_at' => now()->toDateTimeString(),
                'items' => []
            ], now()->addHours(24));

            // Dispatch jobs
            foreach ($tmdbIds as $tmdbId) {
                \App\Jobs\BulkImportJob::dispatch($type, $tmdbId, $importId);
            }

            return response()->json([
                'success' => true,
                'import_id' => $importId,
                'total' => count($tmdbIds),
                'message' => 'Import started successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk import start failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to start import: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkImportProgress(Request $request)
    {
        $importId = $request->input('import_id');
        
        if (!$importId) {
            return response()->json([
                'success' => false,
                'message' => 'Import ID is required'
            ], 400);
        }

        $progress = Cache::get("bulk_import_{$importId}");

        if (!$progress) {
            return response()->json([
                'success' => false,
                'message' => 'Import session not found'
            ], 404);
        }

        // Calculate progress percentage
        $percentage = $progress['total'] > 0 
            ? round(($progress['processed'] / $progress['total']) * 100, 2) 
            : 0;

        $progress['percentage'] = $percentage;
        $progress['is_complete'] = $progress['processed'] >= $progress['total'];

        return response()->json([
            'success' => true,
            'progress' => $progress
        ]);
    }

    public function bulkImportCancel(Request $request)
    {
        $importId = $request->input('import_id');
        
        if (!$importId) {
            return response()->json([
                'success' => false,
                'message' => 'Import ID is required'
            ], 400);
        }

        // Update cache to mark as cancelled
        $progress = Cache::get("bulk_import_{$importId}");
        if ($progress) {
            $progress['status'] = 'cancelled';
            Cache::put("bulk_import_{$importId}", $progress, now()->addHours(24));
        }

        return response()->json([
            'success' => true,
            'message' => 'Import cancelled successfully'
        ]);
    }
}
