<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\PostSeason;
use App\Models\PostEpisode;
use App\Traits\PostTrait;
use App\Traits\PeopleTrait;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class TmdbMassImporter extends Component
{
    use PostTrait, PeopleTrait;

    public $type = 'movie';
    public $sortBy = 'popularity.desc';
    public $totalPages = 5;
    public $startPage = 1;
    public $importPeople = false; // Disabled by default for performance
    public $importSeasons = true; // Enabled by default for TV shows
    
    public $isImporting = false;
    public $progress = 0;
    public $currentPage = 0;
    public $totalItems = 0;
    public $importedCount = 0;
    public $skippedCount = 0;
    public $errorCount = 0;
    public $currentItem = '';
    public $logs = [];
    public $completed = false;

    protected $rules = [
        'type' => 'required|in:movie,tv',
        'sortBy' => 'required',
        'totalPages' => 'required|integer|min:1|max:100',
        'startPage' => 'required|integer|min:1',
        'importPeople' => 'boolean',
    ];

    public function mount()
    {
        // Initialize component
        $this->addLog('Component loaded successfully', 'info');
    }

    public function startImport()
    {
        \Log::info('TmdbMassImporter: startImport called');
        
        $this->validate();

        // Check if TMDB image setting is enabled
        if (config('settings.tmdb_image') != 'active') {
            $this->addLog('⚠️ Warning: "Use themoviedb image" setting is disabled. Images may not display correctly.', 'warning');
            $this->addLog('Go to Tools → Themoviedb → Config and enable "Use themoviedb image" for better performance.', 'warning');
        }

        $this->isImporting = true;
        $this->progress = 0;
        $this->currentPage = $this->startPage;
        $this->totalItems = 0;
        $this->importedCount = 0;
        $this->skippedCount = 0;
        $this->errorCount = 0;
        $this->logs = [];
        $this->completed = false;

        $this->addLog('Starting mass import...', 'info');
        $this->addLog("Type: {$this->type}, Sort: {$this->sortBy}, Pages: {$this->totalPages}", 'info');
        
        \Log::info('TmdbMassImporter: Import initialized', [
            'type' => $this->type,
            'pages' => $this->totalPages,
            'startPage' => $this->startPage
        ]);
    }

    public function processNextPage()
    {
        if (!$this->isImporting) {
            return;
        }

        $endPage = $this->startPage + $this->totalPages - 1;

        if ($this->currentPage <= $endPage) {
            $this->processPage($this->currentPage);
            
            // Update progress
            $this->progress = round((($this->currentPage - $this->startPage + 1) / $this->totalPages) * 100);
            
            $this->currentPage++;

            if ($this->currentPage > $endPage) {
                $this->completed = true;
                $this->isImporting = false;
                $this->addLog('Import completed!', 'success');
                $this->addLog("Imported: {$this->importedCount}, Skipped: {$this->skippedCount}, Errors: {$this->errorCount}", 'success');
            }
        }
    }

    protected function processPage($page)
    {
        // Increase execution time for this page
        set_time_limit(120); // 2 minutes per page
        
        try {
            $this->addLog("Fetching page {$page}...", 'info');

            $response = Http::timeout(30)->get('https://api.themoviedb.org/3/discover/' . $this->type, [
                'api_key' => config('settings.tmdb_api'),
                'language' => config('settings.tmdb_language', 'en'),
                'sort_by' => $this->sortBy,
                'page' => $page,
            ]);

            if (!$response->successful()) {
                $this->addLog("Failed to fetch page {$page}: HTTP {$response->status()}", 'error');
                $this->errorCount++;
                return;
            }

            $result = $response->json();

            if (!isset($result['results'])) {
                $this->addLog("No results found on page {$page}", 'warning');
                return;
            }

            $pageItems = count($result['results']);
            $this->totalItems += $pageItems;
            $this->addLog("Found {$pageItems} items on page {$page}", 'info');

            foreach ($result['results'] as $item) {
                $this->processItem($item);
            }

        } catch (\Exception $e) {
            $this->addLog("Error processing page {$page}: " . $e->getMessage(), 'error');
            $this->errorCount++;
        }
    }

    protected function processItem($item)
    {
        // Reset execution time for each item
        set_time_limit(60); // 1 minute per item
        
        try {
            if (!isset($item['id']) || !isset($item['poster_path'])) {
                $this->skippedCount++;
                return;
            }

            $title = $item['title'] ?? $item['name'] ?? 'Unknown';
            $this->currentItem = $title;

            // Check if already exists
            $existingPost = Post::where('tmdb_id', $item['id'])->first();
            if ($existingPost) {
                $this->addLog("Skipped: \"{$title}\" (already exists)", 'warning');
                $this->skippedCount++;
                return;
            }

            // Fetch full details from TMDB
            $postArray = $this->tmdbApiTrait($this->type, $item['id']);

            if (!$postArray) {
                $this->addLog("Error: Failed to fetch details for \"{$title}\"", 'error');
                $this->errorCount++;
                return;
            }

            // Extract relationships and additional data before creating post
            $genres = $postArray['genres'] ?? [];
            $peoples = $postArray['peoples'] ?? [];
            $seasons = $postArray['seasons'] ?? [];
            $tags = $postArray['tags'] ?? [];
            $videos = $postArray['videos'] ?? [];
            unset($postArray['genres'], $postArray['peoples'], $postArray['tags'], $postArray['seasons'], $postArray['videos']);

            // Handle images based on tmdb_image setting (matching TmdbController)
            if (config('settings.tmdb_image') == 'active') {
                // Use TMDB images directly - clear the full URL fields and keep only tmdb_image path
                $postArray['image'] = null;
                $postArray['cover'] = null;
                $postArray['slide'] = null;
                $postArray['story'] = null;
            }

            // Create post
            $model = new Post();
            $model->fill($postArray);
            // Set status based on draft_post setting (matching TmdbController)
            $model->status = config('settings.draft_post') == 'active' ? 'draft' : 'publish';
            $model->save();

            // Handle genre relationship - extract current_id from genre arrays (matching TmdbController)
            if (!empty($genres) && is_array($genres)) {
                $genreIds = [];
                foreach ($genres as $genre) {
                    if (isset($genre['current_id'])) {
                        $genreIds[] = $genre['current_id'];
                    }
                }
                if (!empty($genreIds)) {
                    $model->genres()->sync($genreIds);
                }
            }

            // Handle tags (matching TmdbController)
            if (!empty($tags) && is_array($tags)) {
                $tagArray = [];
                foreach ($tags as $tag) {
                    if ($tag) {
                        $tagComponent = \App\Models\Tag::where('type', 'post')->firstOrCreate([
                            'tag' => $tag, 
                            'type' => 'post'
                        ]);
                        $tagArray[$tagComponent->id] = [
                            'post_id' => $model->id, 
                            'tagged_id' => $tagComponent->id
                        ];
                    }
                }
                if (!empty($tagArray)) {
                    $model->tags()->sync($tagArray);
                }
            }

            // Handle videos (matching TmdbController)
            if (!empty($videos) && is_array($videos)) {
                foreach ($videos as $videoData) {
                    if (!empty($videoData['link'])) {
                        $video = new \App\Models\PostVideo();
                        $video->type = $videoData['type'] ?? 'video';
                        $video->link = $videoData['link'];
                        $model->videos()->save($video);
                    }
                }
            }

            // Handle people relationship (optional, can be slow)
            if ($this->importPeople && !empty($peoples) && is_array($peoples)) {
                foreach ($peoples as $person) {
                    if (isset($person['id']) && $person['api'] === 'active') {
                        try {
                            // Use PeopleTrait to create/get people (matching TmdbController)
                            $traitPeople = $this->PeopleTmdb($person);
                            if (!empty($traitPeople->id)) {
                                $model->peoples()->attach($traitPeople->id);
                            }
                        } catch (\Exception $e) {
                            // Skip people errors, continue with import
                            \Log::warning("Failed to import person: " . $e->getMessage());
                        }
                    }
                }
            }

            // Handle seasons and episodes for TV shows (matching TmdbController)
            if ($this->type === 'tv' && $this->importSeasons && !empty($seasons) && is_array($seasons)) {
                $this->importSeasons($model, $seasons);
            }

            $statusText = $model->status === 'draft' ? ' (draft)' : '';
            $this->addLog("Imported: \"{$title}\"{$statusText} (ID: {$item['id']})", 'success');
            $this->importedCount++;

        } catch (\Exception $e) {
            $title = $item['title'] ?? $item['name'] ?? 'Unknown';
            $this->addLog("Error importing \"{$title}\": " . $e->getMessage(), 'error');
            $this->errorCount++;
        }
    }

    protected function addLog($message, $type = 'info')
    {
        $this->logs[] = [
            'message' => $message,
            'type' => $type,
            'time' => now()->format('H:i:s')
        ];

        // Keep only last 50 logs to prevent memory issues
        if (count($this->logs) > 50) {
            $this->logs = array_slice($this->logs, -50);
        }
    }

    protected function importSeasons($post, $seasons)
    {
        try {
            $seasonCount = 0;
            $episodeCount = 0;
            $folderDate = now()->format('m-Y') . '/';

            foreach ($seasons as $seasonData) {
                if (!isset($seasonData['season_number']) || $seasonData['season_number'] <= 0) {
                    continue;
                }

                // Create season (matching TmdbController structure)
                $season = new \App\Models\PostSeason();
                $season->name = $seasonData['name'] ?? 'Season ' . $seasonData['season_number'];
                $season->season_number = $seasonData['season_number'];
                $season->tmdb_id = $seasonData['tmdb_id'] ?? null;
                $post->seasons()->save($season);
                $seasonCount++;

                // Episodes are stored as JSON string in seasons array (like TmdbController)
                if (!empty($seasonData['episode'])) {
                    $episodes = is_string($seasonData['episode']) 
                        ? json_decode($seasonData['episode'], true) 
                        : $seasonData['episode'];
                    
                    if (is_array($episodes)) {
                        foreach ($episodes as $episodeData) {
                            $episode = new \App\Models\PostEpisode();
                            
                            // Handle episode image (matching TmdbController)
                            if (config('settings.tmdb_image') != 'active' && isset($episodeData['image'])) {
                                try {
                                    $imagename = \Str::random(10);
                                    $imageFile = $episodeData['image'];
                                    $uploaded_image = fileUpload(
                                        $imageFile, 
                                        config('attr.poster.episode_path') . $folderDate,
                                        config('attr.poster.episode_size_x'), 
                                        config('attr.poster.episode_size_y'), 
                                        $imagename
                                    );
                                    fileUpload(
                                        $imageFile, 
                                        config('attr.poster.episode_path') . $folderDate,
                                        config('attr.poster.episode_size_x'), 
                                        config('attr.poster.episode_size_y'), 
                                        $imagename, 
                                        'webp'
                                    );
                                    $episode->image = $uploaded_image;
                                } catch (\Exception $e) {
                                    \Log::warning("Failed to upload episode image: " . $e->getMessage());
                                }
                            }
                            
                            $episode->post_id = $post->id;
                            $episode->tmdb_id = $episodeData['tmdb_id'] ?? null;
                            $episode->name = $episodeData['name'] ?? 'Episode ' . $episodeData['episode_number'];
                            $episode->season_number = $seasonData['season_number'];
                            $episode->episode_number = $episodeData['episode_number'];
                            $episode->overview = $episodeData['overview'] ?? null;
                            $episode->tmdb_image = $episodeData['tmdb_image'] ?? null;
                            $episode->runtime = $episodeData['runtime'] ?? null;
                            $episode->status = config('settings.draft_post') == 'active' ? 'draft' : 'publish';
                            $season->episodes()->save($episode);
                            $episodeCount++;
                        }
                    }
                }
            }

            if ($seasonCount > 0) {
                $this->addLog("  → Added {$seasonCount} season(s) with {$episodeCount} episode(s)", 'info');
            }

        } catch (\Exception $e) {
            $this->addLog("  → Failed to import seasons: " . $e->getMessage(), 'warning');
            \Log::error("Season import error: " . $e->getMessage());
        }
    }

    public function resetImport()
    {
        $this->isImporting = false;
        $this->progress = 0;
        $this->currentPage = 0;
        $this->totalItems = 0;
        $this->importedCount = 0;
        $this->skippedCount = 0;
        $this->errorCount = 0;
        $this->currentItem = '';
        $this->logs = [];
        $this->completed = false;
    }

    public function render()
    {
        return view('livewire.tmdb-mass-importer');
    }
}
