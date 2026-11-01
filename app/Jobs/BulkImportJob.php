<?php

namespace App\Jobs;

use App\Models\Genre;
use App\Models\Country;
use App\Models\Post;
use App\Models\PostEpisode;
use App\Models\PostSeason;
use App\Models\PostVideo;
use App\Models\Tag;
use App\Traits\PostTrait;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BulkImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, PostTrait;

    public $timeout = 600; // 10 minutes per item
    public $tries = 3;

    protected $type;
    protected $tmdbId;
    protected $importId;

    public function __construct($type, $tmdbId, $importId)
    {
        $this->type = $type;
        $this->tmdbId = $tmdbId;
        $this->importId = $importId;
    }

    public function handle()
    {
        try {
            // Check if already exists
            $exists = Post::where('tmdb_id', $this->tmdbId)->first();
            if ($exists) {
                $this->updateProgress('skipped', 'Already exists');
                return;
            }

            // Fetch data from TMDB API
            $postArray = $this->tmdbApiTrait($this->type, $this->tmdbId);
            
            if (!$postArray) {
                $this->updateProgress('failed', 'Failed to fetch from TMDB');
                return;
            }

            $model = new Post();
            $folderDate = date('m-Y') . '/';

            // Handle images
            if (config('settings.tmdb_image') != 'active') {
                if ($postArray['image']) {
                    $imagename = Str::random(10);
                    $imageFile = $postArray['image'];
                    $uploaded_image = fileUpload($imageFile, config('attr.poster.path') . $folderDate . '/',
                        config('attr.poster.size_x'), config('attr.poster.size_y'), $imagename);
                    fileUpload($imageFile, config('attr.poster.path') . $folderDate . '/', config('attr.poster.size_x'),
                        config('attr.poster.size_y'), $imagename, 'webp');
                    $model->image = $uploaded_image;
                }

                if ($postArray['cover']) {
                    $imagename = Str::random(10);
                    $coverFile = $postArray['cover'];
                    $uploaded_cover = fileUpload($coverFile, config('attr.poster.path') . $folderDate . '/',
                        config('attr.poster.cover_size_x'), config('attr.poster.cover_size_y'), 'cover-' . $imagename);
                    fileUpload($coverFile, config('attr.poster.path') . $folderDate . '/', config('attr.poster.cover_size_x'),
                        config('attr.poster.cover_size_y'), 'cover-' . $imagename, 'webp');
                    $model->cover = $uploaded_cover;
                }

                if ($postArray['slide']) {
                    $imagename = Str::random(10);
                    $slideFile = $postArray['slide'];
                    $uploaded_slide = fileUpload($slideFile, config('attr.poster.path') . $folderDate . '/',
                        config('attr.poster.slide_x'), config('attr.poster.slide_y'), 'slide-' . $imagename);
                    fileUpload($slideFile, config('attr.poster.path') . $folderDate . '/', config('attr.poster.slide_x'),
                        config('attr.poster.slide_y'), 'slide-' . $imagename, 'webp');
                    $model->slide = $uploaded_slide;
                }

                if ($postArray['story']) {
                    $imagename = Str::random(10);
                    $storyFile = $postArray['story'];
                    $uploaded_story = fileUpload($storyFile, config('attr.poster.path') . $folderDate . '/',
                        config('attr.poster.story_x'), config('attr.poster.story_y'), 'story-' . $imagename);
                    fileUpload($storyFile, config('attr.poster.path') . $folderDate . '/', config('attr.poster.story_x'),
                        config('attr.poster.story_y'), 'story-' . $imagename, 'webp');
                    $model->story = $uploaded_story;
                }
            }

            // Save post
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
            $model->status = config('settings.draft_post') == 'active' ? 'draft' : 'publish';
            $model->save();

            // Sync categories
            if (isset($postArray['genres'])) {
                $syncCategories = [];
                foreach ($postArray['genres'] as $key) {
                    $syncCategories[] = $key['current_id'];
                }
                $model->genres()->sync($syncCategories);
            }

            // Sync tags
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

            // Attach people
            if (isset($postArray['peoples'])) {
                foreach ($postArray['peoples'] as $key) {
                    $traitPeople = $this->PeopleTmdb($key);
                    if (!empty($traitPeople->id)) {
                        $model->peoples()->attach($traitPeople->id);
                    }
                }
            }

            // Import seasons and episodes for TV shows
            if (isset($postArray['seasons'])) {
                foreach ($postArray['seasons'] as $key) {
                    if ($key['season_number']) {
                        $season = new PostSeason();
                        $season->name = $key['name'];
                        $season->season_number = $key['season_number'];
                        $model->seasons()->save($season);

                        $episodes = json_decode($key['episode'], true);
                        if ($episodes) {
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

            $this->updateProgress('completed', 'Successfully imported');

        } catch (\Exception $e) {
            Log::error('Bulk import failed for TMDB ID: ' . $this->tmdbId, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->updateProgress('failed', $e->getMessage());
            throw $e;
        }
    }

    protected function updateProgress($status, $message = '')
    {
        $cacheKey = "bulk_import_{$this->importId}";
        $progress = Cache::get($cacheKey, [
            'total' => 0,
            'processed' => 0,
            'completed' => 0,
            'failed' => 0,
            'skipped' => 0,
            'status' => 'processing',
            'items' => []
        ]);

        $progress['processed']++;
        $progress[$status]++;
        $progress['items'][$this->tmdbId] = [
            'type' => $this->type,
            'tmdb_id' => $this->tmdbId,
            'status' => $status,
            'message' => $message,
            'timestamp' => now()->toDateTimeString()
        ];

        Cache::put($cacheKey, $progress, now()->addHours(24));
    }
}
