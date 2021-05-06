<?php

namespace Tests\Feature\generateduserdata;

use App\Models\UgcPoi;
use App\Models\UgcTrack;
use App\Models\User;
use App\Providers\HoquServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class storeApiTest extends TestCase {
    use RefreshDatabase;

    private function _getHoquServiceProviderMock() {
        $this->mock(HoquServiceProvider::class, function ($mock) {
            $mock->shouldReceive('store')
                ->andReturn(201);
        });
    }

    public function testWithoutAuthentication() {
        $response = $this->post('/api/usergenerateddata/store', []);
        $this->assertSame(401, $response->status());
    }

    public function testWithNoData() {
        $this->actingAs(User::where('email', '=', 'team@webmapp.it')->first(), 'api');
        $count = count(UgcPoi::get()) + count(UgcTrack::get());
        $response = $this->post('/api/usergenerateddata/store', []);
        $this->assertSame($response->status(), 422);
        $this->assertSame($count, count(UgcPoi::get()) + count(UgcTrack::get()));
    }

    public function testWithAPoi() {
        $this->_getHoquServiceProviderMock();
        $this->actingAs(User::where('email', '=', 'team@webmapp.it')->first(), 'api');
        $appId = 'it.webmapp.test';
        $formData = [
            "name" => "Test name"
        ];
        $data = [
            "type" => "FeatureCollection",
            "features" => [
                [
                    "type" => "Feature",
                    "geometry" => [
                        "type" => "Point",
                        "coordinates" => [10, 20]
                    ],
                    "properties" => [
                        "app" => [
                            "id" => $appId
                        ],
                        "form_data" => $formData
                    ]
                ]
            ]
        ];

        $response = $this->postJson('/api/usergenerateddata/store', $data);
        $this->assertSame($response->status(), 201);

        $this->assertCount(0, UgcTrack::get());
        $this->assertCount(1, UgcPoi::get());
        $newContent = UgcPoi::first();
        $this->assertSame($appId, $newContent->app_id);
        $this->assertSame($formData['name'], $newContent->name);
        unset($formData['name']); // This must have been moved in the name field
        $this->assertSame(json_encode($formData), json_encode(json_decode($newContent->raw_data, true)));
    }

    public function testWithATrack() {
        $this->_getHoquServiceProviderMock();
        $this->actingAs(User::where('email', '=', 'team@webmapp.it')->first(), 'api');
        $appId = 'it.webmapp.test';
        $formData = [
            "name" => "Test name"
        ];
        $data = [
            "type" => "FeatureCollection",
            "features" => [
                [
                    "type" => "Feature",
                    "geometry" => [
                        "type" => "LineString",
                        "coordinates" => [[10, 20], [10, 20], [10, 20]]
                    ],
                    "properties" => [
                        "app" => [
                            "id" => $appId
                        ],
                        "form_data" => $formData
                    ]
                ]
            ]
        ];

        $response = $this->postJson('/api/usergenerateddata/store', $data);
        $response->assertStatus(201);

        $this->assertCount(0, UgcPoi::get());
        $this->assertCount(1, UgcTrack::get());
        $newContent = UgcTrack::first();
        $this->assertSame($appId, $newContent->app_id);
        $this->assertSame($formData['name'], $newContent->name);
        unset($formData['name']); // This must have been moved in the name field
        $this->assertSame(json_encode($formData), json_encode(json_decode($newContent->raw_data, true)));
    }

    public function testSaveUgcPoiTriggerHoquStore() {
        $this->mock(HoquServiceProvider::class, function ($mock) {
            $mock->shouldReceive('store')
                ->once()
                ->with('update_ugc_taxonomy_wheres', Mockery::on(function ($option) {
                    return is_array($option) && isset($option['id']) && isset($option['type']) && $option['type'] === 'ugc_poi';
                }))
                ->andReturn(201);
        });

        $this->actingAs(User::where('email', '=', 'team@webmapp.it')->first(), 'api');
        $appId = 'it.webmapp.test';
        $formData = [
            "name" => "Test name"
        ];
        $data = [
            "type" => "FeatureCollection",
            "features" => [
                [
                    "type" => "Feature",
                    "geometry" => [
                        "type" => "Point",
                        "coordinates" => [10, 20]
                    ],
                    "properties" => [
                        "app" => [
                            "id" => $appId
                        ],
                        "form_data" => $formData
                    ]
                ]
            ]
        ];
        $response = $this->postJson('/api/usergenerateddata/store', $data);
    }

    public function testSaveUgcTrackTriggerHoquStore() {
        $this->mock(HoquServiceProvider::class, function ($mock) {
            $mock->shouldReceive('store')
                ->once()
                ->with('update_ugc_taxonomy_wheres', Mockery::on(function ($option) {
                    return is_array($option) && isset($option['id']) && isset($option['type']) && $option['type'] === 'ugc_track';
                }))
                ->andReturn(201);
        });

        $this->actingAs(User::where('email', '=', 'team@webmapp.it')->first(), 'api');
        $appId = 'it.webmapp.test';
        $formData = [
            "name" => "Test name"
        ];
        $data = [
            "type" => "FeatureCollection",
            "features" => [
                [
                    "type" => "Feature",
                    "geometry" => [
                        "type" => "LineString",
                        "coordinates" => [[10, 20], [10, 20], [10, 20]]
                    ],
                    "properties" => [
                        "app" => [
                            "id" => $appId
                        ],
                        "form_data" => $formData
                    ]
                ]
            ]
        ];
        $response = $this->postJson('/api/usergenerateddata/store', $data);
    }
}
