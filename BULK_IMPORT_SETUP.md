# TMDB Bulk Import Setup Guide

## Overview
The TMDB Bulk Import feature allows you to import thousands of movies or TV shows from VidSrc's TMDB ID lists with real-time progress tracking and background processing.

## Features
- ✅ Server-side background processing
- ✅ Real-time progress monitoring
- ✅ Automatic duplicate detection and skipping
- ✅ Detailed statistics (completed, failed, skipped)
- ✅ Activity log with recent import details
- ✅ Page can be closed - import continues in background
- ✅ Cancellation support
- ✅ Session persistence (resume on page refresh)

## Requirements

### 1. Queue Worker Setup
The bulk import uses Laravel queues to process items in the background. You MUST have a queue worker running.

#### Option A: Using Laravel Horizon (Recommended for Production)
```bash
composer require laravel/horizon
php artisan horizon:install
php artisan horizon
```

#### Option B: Using Basic Queue Worker
```bash
php artisan queue:work --tries=3 --timeout=600
```

#### Option C: Using Supervisor (Production)
Create a supervisor configuration file at `/etc/supervisor/conf.d/watchug-worker.conf`:

```ini
[program:watchug-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/flixworld.xyz/watchug-php8.5/artisan queue:work --sleep=3 --tries=3 --timeout=600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/flixworld.xyz/watchug-php8.5/storage/logs/worker.log
stopwaitsecs=3600
```

Then reload supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start watchug-worker:*
```

### 2. Cache Configuration
The progress tracking uses Laravel's cache. Make sure you have a proper cache driver configured in `.env`:

```env
CACHE_DRIVER=redis  # Recommended for production
# or
CACHE_DRIVER=file   # Works for development
```

For Redis:
```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 3. TMDB API Configuration
Ensure your TMDB API settings are configured:
- Go to: Admin Dashboard → Tools → Themoviedb → Config
- Add your TMDB API key
- Configure import settings (seasons, episodes, people limit, etc.)

## Usage

### Step 1: Access Bulk Import
1. Login to Admin Dashboard
2. Navigate to **Tools** menu
3. Click on **TMDB Bulk Import** card
4. Or go directly to: `/admin/tmdb-bulk-import`

### Step 2: Select Type
- Choose **Movies** to import movie collection
- Choose **TV Shows** to import TV series collection

### Step 3: Start Import
- Click **Start Import** button
- The system will:
  1. Fetch TMDB IDs from VidSrc lists
  2. Filter out already imported items
  3. Queue all items for background processing
  4. Show real-time progress

### Step 4: Monitor Progress
The progress screen shows:
- **Progress Bar**: Visual representation of completion
- **Statistics**: Processed, Completed, Skipped, Failed counts
- **Activity Log**: Recent import activity with status
- **Status**: Current state of the import

### Step 5: Background Processing
- You can close the page - import continues in background
- Return anytime to check progress
- Use the URL with `?import_id=XXX` to resume monitoring

## Data Sources

### Movies
Source: `https://vidsrc.sh/ids/movie_tmdb.txt`
- Contains TMDB IDs for movies
- One ID per line
- Automatically filtered for existing entries

### TV Shows
Source: `https://vidsrc.sh/ids/tv_tmdb.txt`
- Contains TMDB IDs for TV series
- One ID per line
- Includes seasons and episodes based on your settings

## Import Process

For each TMDB ID, the system will:
1. Check if already exists (skip if found)
2. Fetch data from TMDB API
3. Download and process images (if configured)
4. Create post entry
5. Attach genres, tags, and people
6. Import seasons and episodes (for TV shows)
7. Update progress in real-time

## Troubleshooting

### Queue Worker Not Running
**Problem**: Items stuck at 0% processed
**Solution**: 
```bash
# Check if queue worker is running
ps aux | grep "queue:work"

# Start queue worker
php artisan queue:work --tries=3 --timeout=600
```

### Import Stuck or Slow
**Problem**: Progress not updating
**Solutions**:
1. Check queue worker is running
2. Check TMDB API rate limits
3. Increase number of queue workers:
```bash
php artisan queue:work --tries=3 --timeout=600 &
php artisan queue:work --tries=3 --timeout=600 &
php artisan queue:work --tries=3 --timeout=600 &
```

### High Failure Rate
**Problem**: Many items showing as "failed"
**Possible Causes**:
1. TMDB API key invalid or rate limited
2. Network connectivity issues
3. Disk space full (for image uploads)
4. Database connection issues

**Check Logs**:
```bash
tail -f storage/logs/laravel.log
```

### Memory Issues
**Problem**: Queue worker crashes
**Solution**: Increase PHP memory limit in `php.ini`:
```ini
memory_limit = 512M
```

Or use queue worker with memory limit:
```bash
php artisan queue:work --memory=512 --tries=3 --timeout=600
```

### Cache Issues
**Problem**: Progress not updating correctly
**Solution**: Clear cache and restart:
```bash
php artisan cache:clear
php artisan queue:restart
```

## Performance Tips

### 1. Use Redis for Cache
Redis provides much faster cache operations for real-time progress:
```bash
sudo apt install redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

Update `.env`:
```env
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 2. Multiple Queue Workers
Run multiple workers for faster processing:
```bash
php artisan queue:work --tries=3 --timeout=600 --queue=default &
php artisan queue:work --tries=3 --timeout=600 --queue=default &
php artisan queue:work --tries=3 --timeout=600 --queue=default &
php artisan queue:work --tries=3 --timeout=600 --queue=default &
```

### 3. Optimize Database
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. Disable Image Downloads (Faster Import)
In TMDB Settings, enable "Use TMDB Image URLs" to skip downloading images locally. This significantly speeds up imports.

## Monitoring

### Check Queue Status
```bash
# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

### Check Logs
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Queue worker logs (if using supervisor)
tail -f storage/logs/worker.log
```

### Database Checks
```sql
-- Count imported posts
SELECT type, COUNT(*) FROM posts GROUP BY type;

-- Check recent imports
SELECT * FROM posts ORDER BY created_at DESC LIMIT 10;
```

## API Rate Limits

TMDB API has rate limits:
- **40 requests every 10 seconds**
- If you hit the limit, imports will fail temporarily

To handle this:
1. Use queue delays between jobs
2. Implement retry logic (already built-in with `--tries=3`)
3. Monitor error logs for rate limit messages

## Security Notes

1. **Demo Mode**: The routes use `middleware('demo')` - remove this in production
2. **Authentication**: Ensure only admins can access bulk import
3. **Rate Limiting**: Consider adding rate limiting to prevent abuse
4. **API Keys**: Keep your TMDB API key secure in `.env` file

## Maintenance

### Clear Old Import Sessions
Import progress data is cached for 24 hours. To manually clear:
```bash
php artisan cache:forget bulk_import_XXXXXXXXXXXXXXXX
```

Or clear all cache:
```bash
php artisan cache:clear
```

## Support

If you encounter issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify queue worker is running
3. Test TMDB API connectivity
4. Check database connectivity
5. Verify file permissions for storage directories

## Changelog

### Version 1.0.0
- Initial release
- Movies and TV shows bulk import
- Real-time progress tracking
- Background processing with Laravel queues
- Activity logging
- Duplicate detection and skipping
- Cancellation support
