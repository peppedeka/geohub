<?php

namespace App\Observers;

use App\Models\EcTrack;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EcTrackElasticObserver
{
    /**
     * Handle the EcTrack "created" event.
     *
     * @param  \App\Models\EcTrack  $ecTrack
     * @return void
     */
    public function save(EcTrack $ecTrack)
    {

    }

    /**
     * Handle the EcTrack "updated" event.
     *
     * @param  \App\Models\EcTrack  $ecTrack
     * @return void
     */
    public function updated(EcTrack $ecTrack)
    {

        #REF: https://github.com/elastic/elasticsearch-php/
        #REF: https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/index.html

        $hosts = ['https://forge:1b0VUJxRFxeOupkjPeie@elastic.sis-te.com'];
        $client = ClientBuilder::create()->setHosts($hosts)->build();



        return;



        Log::info('Indexing track '.$ecTrack->id);
        $geom = $ecTrack::where('id', '=', $ecTrack->id)
            ->select(
                DB::raw("ST_AsGeoJSON(geometry) as geom")
            )
            ->first()
            ->geom;

        $curl = curl_init();
// https://elastic.sis-te.com/geohub
//             CURLOPT_URL => 'https://elastic.geniuslocianalytics.com/geohub/_doc/'.$ecTrack->id,
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://elastic.sis-te.com/geohub/_doc/'.$ecTrack->id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
  "geometry" : '.$geom.',
  "id": '.$ecTrack->id.',
  "ref": "100",
  "cai_scale": "E",
   "piccioli": "fava"
}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Basic Zm9yZ2U6MWIwVlVKeFJGeGVPdXBralBlaWU='
            ),
        ));

        $response = curl_exec($curl);


        curl_close($curl);
        Log::info('Index OK');
     }

    /**
     * Handle the EcTrack "deleted" event.
     *
     * @param  \App\Models\EcTrack  $ecTrack
     * @return void
     */
    public function deleted(EcTrack $ecTrack)
    {
        //
    }

    /**
     * Handle the EcTrack "restored" event.
     *
     * @param  \App\Models\EcTrack  $ecTrack
     * @return void
     */
    public function restored(EcTrack $ecTrack)
    {
        //
    }

    /**
     * Handle the EcTrack "force deleted" event.
     *
     * @param  \App\Models\EcTrack  $ecTrack
     * @return void
     */
    public function forceDeleted(EcTrack $ecTrack)
    {
        //
    }
}
