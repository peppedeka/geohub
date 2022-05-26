<?php

namespace Tests\Feature;

use App\Classes\OutSourceImporter\OutSourceImporterFeatureWP;
use App\Models\OutSourceFeature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\MockInterface;
use Tests\TestCase;

class OutSourceImporterFeatureWPImportMediaTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function when_poi_has_featured_media_tags_feature_image_should_have_osf_id()
    {
        // WHEN
        $type = 'poi';
        $endpoint = 'https://stelvio.wp.webmapp.it';
        $source_id = 2654;
        $stelvio_poi = file_get_contents(base_path('tests/Feature/Stubs/stelvio_poi.json'));
        $url_poi = $endpoint.'/wp-json/wp/v2/poi/'.$source_id;
        
        $source_id_media = 2481;
        $stelvio_media = file_get_contents(base_path('tests/Feature/Stubs/stelvio_media.json'));
        $url_media = $endpoint.'/wp-json/wp/v2/media/'.$source_id_media;

        // PREPARE MOCK
        $this->mock(CurlServiceProvider::class,function (MockInterface $mock) use ($stelvio_poi,$url_poi,$stelvio_media,$url_media){
            $mock->shouldReceive('exec')
            ->atLeast(1)
            ->with($url_poi)
            ->andReturn($stelvio_poi);
            
            $mock->shouldReceive('exec')
            ->atLeast(1)
            ->with($url_media)
            ->andReturn($stelvio_media);
        });

        // FIRE
        $poi = new OutSourceImporterFeatureWP($type,$endpoint,$source_id);
        $poi_id = $poi->importFeature();

        // VERIFY
        $out_source_poi = OutSourceFeature::find($poi_id);
        $out_source_media = OutSourceFeature::find($out_source_poi->tags['feature_image']);
        $this->assertEquals($out_source_media->id,$out_source_poi->tags['feature_image']);
        $this->assertEquals($out_source_media->provider,'App\Classes\OutSourceImporter\OutSourceImporterFeatureWP');
    }
}
