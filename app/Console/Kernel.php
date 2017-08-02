<?php

namespace App\Console;

use Intervention\Image\Facades\Image;
use App\Track;
use App\Album;
use App\Mixtape;
use App\Video;
use App\Genre;
use App\ScrapeLink;
use cURL;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function() {
            /*
             * Import tracks
             */
            $page = 1;
            $ch = curl_init();
            $type = 'tracks';
            $url = 'http://www.leakedearly.com/api/'.$type.'?page='.$page;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer ' . env('LE_API_KEY')
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec ($ch);
            curl_close ($ch);
            $array = json_decode( $output, true );
            if( isset( $array['data'] ) && !empty( $array['data'] ) )
            {
                foreach( $array['data'] AS $track )
                {
                    $firstOrCreate = ScrapeLink::firstOrCreate(
                        [
                            'type' => 'track',
                            'href' => $track['api_url']
                        ]
                    );
                    if ($firstOrCreate->wasRecentlyCreated)
                    {
                        $firstOrCreate->status = 'processing';
                        $firstOrCreate->save();
                        $genre = Genre::firstOrCreate([
                            'status'=>'enabled',
                            'name'=> $track['genre_data']['name'],
                            'slug'=> str_slug( $track['genre_data']['name'] )
                        ]);
                        $filename = basename( $track['image_url'] );
                        Image::make( $track['image_url'] )->save(storage_path('app/public/images/track/'.$filename));
                        $insert = new Track;
                        $insert->status = 'enabled';
                        $insert->artist = $track['artist'];
                        $insert->name = $track['name'];
                        $insert->slug = str_slug( $track['artist'] . ' - ' . $track['name'] );
                        $insert->genre_id = $genre->id;
                        $insert->image = $filename;
                        $insert->download = $track['download'];
                        $insert->preview = $track['preview'];
                        $insert->keywords = '';
                        $insert->origin = $firstOrCreate->id;
                        $insert->created_at = $track['created_at'];
                        $insert->save();
                        $firstOrCreate->status = 'finished';
                        $firstOrCreate->save();
                    }
                }
            }

            /*
             * Import videos
             */
            $page = 1;
            $ch = curl_init();
            $type = 'videos';
            $url = 'http://www.leakedearly.com/api/'.$type.'?page='.$page;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer ' . env('LE_API_KEY')
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec ($ch);
            curl_close ($ch);
            $array = json_decode( $output, true );
            if( isset( $array['data'] ) && !empty( $array['data'] ) )
            {
                foreach( $array['data'] AS $video )
                {
                    $firstOrCreate = ScrapeLink::firstOrCreate(
                        [
                            'type' => 'video',
                            'href' => $video['api_url']
                        ]
                    );
                    if ($firstOrCreate->wasRecentlyCreated)
                    {
                        $firstOrCreate->status = 'processing';
                        $firstOrCreate->save();
                        $genre = Genre::firstOrCreate([
                            'status'=>'enabled',
                            'name'=> $video['genre_data']['name'],
                            'slug'=> str_slug( $video['genre_data']['name'] )
                        ]);
                        $filename = basename( $video['image_url'] );
                        Image::make( $video['image_url'] )->save(storage_path('app/public/images/video/'.$filename));
                        $insert = new Video;
                        $insert->status = 'enabled';
                        $insert->artist = $video['artist'];
                        $insert->name = $video['name'];
                        $insert->slug = str_slug( $video['artist'] . ' - ' . $video['name'] );
                        $insert->genre_id = $genre->id;
                        $insert->image = $filename;
                        $insert->preview = $video['video_url'];
                        $insert->origin = $firstOrCreate->id;
                        $insert->created_at = $video['created_at'];
                        $insert->save();
                        $firstOrCreate->status = 'finished';
                        $firstOrCreate->save();
                    }
                }
            }

            /*
             * Import albums
             */
            $page = 1;
            $ch = curl_init();
            $type = 'albums';
            $url = 'http://www.leakedearly.com/api/'.$type.'?page='.$page;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer ' . env('LE_API_KEY')
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec ($ch);
            curl_close ($ch);
            $array = json_decode( $output, true );
            if( isset( $array['data'] ) && !empty( $array['data'] ) )
            {
                foreach( $array['data'] AS $album )
                {
                    $firstOrCreate = ScrapeLink::firstOrCreate(
                        [
                            'type' => 'album',
                            'href' => $album['api_url']
                        ]
                    );
                    if ($firstOrCreate->wasRecentlyCreated)
                    {
                        $firstOrCreate->status = 'processing';
                        $firstOrCreate->save();
                        $genre = Genre::firstOrCreate([
                            'status'=>'enabled',
                            'name'=> $album['genre_data']['name'],
                            'slug'=> str_slug( $album['genre_data']['name'] )
                        ]);
                        $filename = basename( $album['image_url'] );
                        Image::make( $album['image_url'] )->save(storage_path('app/public/images/album/'.$filename));
                        $insert = new Album;
                        $insert->status = 'enabled';
                        $insert->artist = $album['artist'];
                        $insert->name = $album['name'];
                        $insert->slug = str_slug( $album['artist'] . ' - ' . $album['name'] );
                        $insert->genre_id = $genre->id;
                        $insert->image = $filename;
                        $insert->download = $album['download'];
                        $insert->keywords = '';
                        $insert->tracks = json_encode( $album['track_list'] );
                        $insert->origin = $firstOrCreate->id;
                        $insert->created_at = $album['created_at'];
                        $insert->save();
                        $firstOrCreate->status = 'finished';
                        $firstOrCreate->save();
                    }
                }
            }

            /*
             * Import mixtapes
             */
            $page = 1;
            $ch = curl_init();
            $type = 'mixtapes';
            $url = 'http://www.leakedearly.com/api/'.$type.'?page='.$page;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer ' . env('LE_API_KEY')
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec ($ch);
            curl_close ($ch);
            $array = json_decode( $output, true );
            if( isset( $array['data'] ) && !empty( $array['data'] ) )
            {
                foreach( $array['data'] AS $mixtape )
                {
                    $firstOrCreate = ScrapeLink::firstOrCreate(
                        [
                            'type' => 'mixtape',
                            'href' => $mixtape['api_url']
                        ]
                    );
                    if ($firstOrCreate->wasRecentlyCreated)
                    {
                        $firstOrCreate->status = 'processing';
                        $firstOrCreate->save();
                        $genre = Genre::firstOrCreate([
                            'status'=>'enabled',
                            'name'=> $mixtape['genre_data']['name'],
                            'slug'=> str_slug( $mixtape['genre_data']['name'] )
                        ]);
                        $filename = basename( $mixtape['image_url'] );
                        Image::make( $mixtape['image_url'] )->save(storage_path('app/public/images/mixtape/'.$filename));
                        $insert = new Mixtape;
                        $insert->status = 'enabled';
                        $insert->artist = $mixtape['artist'];
                        $insert->name = $mixtape['name'];
                        $insert->slug = str_slug( $mixtape['artist'] . ' - ' . $mixtape['name'] );
                        $insert->genre_id = $genre->id;
                        $insert->image = $filename;
                        $insert->download = $mixtape['download'];
                        $insert->keywords = '';
                        $insert->tracks = json_encode( $mixtape['track_list'] );
                        $insert->origin = $firstOrCreate->id;
                        $insert->created_at = $mixtape['created_at'];
                        $insert->save();
                        $firstOrCreate->status = 'finished';
                        $firstOrCreate->save();
                    }
                }
            }

        })->everyFiveMinutes();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
