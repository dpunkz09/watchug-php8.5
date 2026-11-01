<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Community;
use App\Models\Game;
use App\Models\Tag;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run()
    {

        $save_data = [
            'tmdb_api' => '5ea02218764ccc1f0764e079169f8573',
            'tmdb_language' => 'en',
            'tmdb_people_limit' => '3',
            'import_season' => 'active',
            'import_episode' => 'active',


            'landing_title' => 'We let you watch movies online without',
            'landing_description' => 'We let you watch movies online without having to register or paying, with over 10000 Movies and TV Shows.',
            'landing_body' => 'We let you watch movies online without having to register or paying, with over 10000 Movies and TV Shows.',

            'facebook' => 'https://facebook.com/codelug',
            'twitter' => 'https://twitter.com/codelug',
            'instagram' => 'https://instagram.com/codelug',


            'paypal' => 'active',
            'paypal_mode' => 'sandbox',
            'paypal_client_id' => env('PAYPAL_CLIENT_ID', 'your_paypal_client_id_here'),
            'paypal_secret' => env('PAYPAL_SECRET', 'your_paypal_secret_here'),
            'paypal_webhook_id' => env('PAYPAL_WEBHOOK_ID', 'your_paypal_webhook_id_here'),

            'stripe' => 'active',
            'stripe_mode' => 'sandbox',
            'stripe_key' => env('STRIPE_KEY', 'your_stripe_key_here'),
            'stripe_secret' => env('STRIPE_SECRET', 'your_stripe_secret_here'),
            'stripe_signing_secret' => env('STRIPE_SIGNING_SECRET', 'your_stripe_signing_secret_here'),

        ];

        foreach ($save_data as $key => $value) {
            update_settings($key, $value);
        }


        Cache::forget('settings');
        Cache::flush();
//
//        // Blog
//        $data = json_decode(file_get_contents('https://dummyjson.com/posts'),true);
//        $ii=1;
//        foreach ($data['posts'] as $item) {
//            if($ii <= 4) {
//            $blog = new Article();
//            // Image upload
//            $imagename = Str::random(10);
//
//            if (! file_exists(public_path('uploads/temp'))) {
//                mkdir(public_path('uploads/temp'), 0777, true);
//            }
//
//            $temp_image = public_path('uploads/temp/'.Str::random(10).'.png');
//
//            $client = new \GuzzleHttp\Client();
//            $response = $client->request('GET', 'https://source.unsplash.com/random/1280x1024?movie', ['sink' => $temp_image, 'verify' => false, 'timeout' => 10]);
//
//            $input['image'] = fileUpload($temp_image, config('attr.article.path'), config('attr.article.large_x'), config('attr.article.large_y'), $imagename);
//            fileUpload($temp_image, config('attr.article.path'), config('attr.article.large_x'), config('attr.article.large_y'), $imagename, 'webp');
//            $input['thumb'] = fileUpload($temp_image, config('attr.article.path'), config('attr.article.thumb_x'), config('attr.article.thumb_y'), 'thumb-'.$imagename);
//            fileUpload($temp_image, config('attr.article.path'), config('attr.article.thumb_x'), config('attr.article.thumb_y'), 'thumb-'.$imagename, 'webp');
//
//
//
//            $blog->title = $item['title'];
//            $blog->slug = SlugService::createSlug(Article::class, 'slug', $item['title']);
//            $blog->description = $item['body'];
//            $blog->body = $item['body'];
//            $blog->image = $input['image'];
//            $blog->featured = 'active';
//            $blog->status = 'publish';
//            $blog->save();
//            }
//            $ii++;
//
//        }
    }
}
