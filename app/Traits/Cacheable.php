<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait Cacheable
{
    /**
     * Clear all related cache keys when model is saved
     */
    protected static function bootCacheable()
    {
        static::saved(function ($model) {
            $model->clearRelatedCache();
        });

        static::deleted(function ($model) {
            $model->clearRelatedCache();
        });
    }

    /**
     * Clear cache keys related to this model
     */
    public function clearRelatedCache()
    {
        $cacheKeys = $this->getCacheKeys();
        
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Define which cache keys should be cleared for this model
     * Override this method in your models
     */
    protected function getCacheKeys(): array
    {
        return [];
    }
}
