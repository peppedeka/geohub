<?php

namespace App\Models;

use App\Providers\HoquServiceProvider;
use App\Traits\GeometryFeatureTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EcPoi extends Model
{
    use HasFactory, GeometryFeatureTrait;

    private HoquServiceProvider $hoquServiceProvider;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->hoquServiceProvider = app(HoquServiceProvider::class);
    }

    public function save(array $options = [])
    {
        static::creating(function ($ecPoi) {
            $user = User::getEmulatedUser();
            if (is_null($user)) {
                $user = User::where('email', '=', 'team@webmapp.it')->first();
            }
            $ecPoi->author()->associate($user);

            // try {
            //     $this->hoquServiceProvider->store('enrich_ec_media', ['id' => $this->id]);
            // } catch (\Exception $e) {
            //     Log::error('An error occurred during a store operation: ' . $e->getMessage());
            // }
        });

        $geometry = $this->attributes["geometry"];
        static::updating(function ($ecPoi) use ($geometry) {
            $this->attributes["geometry"] = DB::raw($geometry);
        });

        parent::save($options);
    }

    public function author()
    {
        return $this->belongsTo("\App\Models\User", "user_id", "id");
    }

    public function taxonomyWheres()
    {
        return $this->morphToMany(TaxonomyWhere::class, 'taxonomy_whereable');
    }
}
