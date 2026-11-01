<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Models\Genre;
use App\Models\Post;

class OptimizeCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:optimize-site {--clear : Clear all caches without warming}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear and intelligently rebuild site caches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('clear')) {
            $this->info('🧹 Clearing all caches...');
            $this->call('cache:clear');
            $this->call('view:clear');
            $this->call('config:clear');
            $this->call('route:clear');
            $this->info('✅ All caches cleared!');
            return 0;
        }

        $this->info('🚀 Starting cache optimization...');
        
        // Clear old caches
        $this->info('🧹 Clearing old caches...');
        $this->call('cache:clear');
        $this->call('view:clear');
        
        // Warm up critical caches
        $this->info('🔥 Warming up critical caches...');
        
        // Cache browse genres
        $this->info('  → Caching browse genres...');
        Cache::remember('browse-genre', 7200, function () {
            return Genre::select('id', 'title', 'slug')
                ->withCount(['posts'])
                ->where('featured', 'active')
                ->limit(5)
                ->get()
                ->toArray();
        });
        
        // Cache recommends
        $this->info('  → Caching recommendations...');
        Cache::remember('browse-recommends', 3600, function () {
            return Post::select('id', 'title', 'slug', 'type', 'image', 'vote_average', 'created_at')
                ->orderby('vote_average','desc')
                ->limit(9)
                ->get()
                ->toArray();
        });
        
        $this->info('⚙️  Optimizing config...');
        $this->call('config:cache');
        
        $this->newLine();
        $this->info('✅ Cache optimization complete!');
        $this->newLine();
        $this->info('💡 Tip: Run this command after:');
        $this->info('   • Adding new posts/movies/shows');
        $this->info('   • Updating genres or collections');
        $this->info('   • Modifying settings');
        $this->newLine();
        $this->info('⚠️  Note: Run "php artisan route:cache" manually after fixing duplicate route names');
        
        return 0;
    }
}
