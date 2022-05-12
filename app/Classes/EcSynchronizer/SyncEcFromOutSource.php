<?php

namespace App\Classes\EcSynchronizer;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class SyncEcFromOutSource
{
    // DATA
    protected $type;
    protected $author;
    protected $author_id;
    protected $provider;
    protected $endpoint;
    protected $activity;
    protected $name_format;
    protected $app;

    /**
     * It sets all needed properties in order to perform the sync ec_tracks table from out_source_features
     * 
     *
     * @param string $type the of the feature (Track, Poi or Media)
     * @param string $author the email of the author to be associated with features
     * @param string $provider the class of the importer, can be only the class or whole namespace.
     * @param string $endpoint the url from which import begins (https://stelvio.wp.webmapp.it)
     * @param string $activity the activity to associate with the feature. it takes the Identifier (eg. hiking)
     * @param string $name_format the rule to construct the name field of the feature. (eg. “Ecooci {ref} - from {from}, to {to}”)
     * @param string $app the id of the app (eg. Parco Maremma = 1 )
     */
    public function __construct(string $type, string $author, string $provider = '', string $endpoint = '',string $activity = 'hiking' ,string $name_format, int $app = 0) 
    {
        $this->type = $type;
        $this->author = $author;
        $this->provider = $provider;            
        $this->endpoint = strtolower($endpoint);            
        $this->activity = strtolower($activity);            
        $this->name_format = $name_format;            
        $this->app = $app;            
    }

    /**
     * It checks the parameters of the command geohub:sync-ec-from-out-source to see if they are
     * available to use
     * 
     *
     * @return boolean 
     */
    public function checkParameters() 
    {
        // Check the author
        if (is_numeric($this->author)) {
            try {
                $user = User::find(intval($this->author));
                $this->author_id = $user->id;
            } catch (Exception $e) {
                throw new Exception('No User found with this ID '. $this->author); 
            }
        } else {
            try {
                $user = User::where('email',strtolower($this->author))->first();
                $this->author_id = $user->id;
            } catch (Exception $e) {
                throw new Exception('No User found with this email '. $this->author); 
            }
        }

        // Check the type
        if (strtolower($this->type) == 'track' ||
            strtolower($this->type) == 'poi' || 
            strtolower($this->type) == 'media' || 
            strtolower($this->type) == 'taxonomy' 
            ) {
                $this->type = strtolower($this->type);
            } else {
                throw new Exception('The value of parameter type: '.$this->type.' is not currect'); 
            }
        
        // Check the provider
        if (!empty($this->provider)) {
            $all_providers = DB::table('out_source_features')->select('provider')->distinct()->get();
            $mapped_providers = array_map(function($p){
                $provider = explode('\\',$p);
                if ($this->provider == end($provider) || $this->provider == $p) {
                    $this->provider = $p;
                    return true;
                } else {
                    return false;
                }
            },$all_providers->pluck('provider')->toArray());
            if (in_array(true , $mapped_providers )){
            } else {
                throw new Exception('The value of parameter provider '.$this->provider.' is not currect'); 
            }
        }

        // Check the endpoint
        if (!empty($this->endpoint)) {
            $all_endpoints = DB::table('out_source_features')->select('endpoint')->distinct()->get();
            $mapped_endpoints = array_map(function($e){
                if (!is_null($e)) {
                    if (strpos($e,$this->endpoint) || $e == $this->endpoint){
                        $this->endpoint = $e;
                        return true;
                    } else {
                        return false;
                    }
                }
            },$all_endpoints->pluck('endpoint')->toArray());
            if (in_array(true , $mapped_endpoints )){
                $this->endpoint = $this->endpoint;
            } else {
                throw new Exception('The value of parameter endpoint '.$this->endpoint.' is not currect'); 
            }
        }

        // Check the name_format
        if (!empty($this->name_format)) {
            $format = $this->name_format;
            preg_match_all('/\{{1}?(.*?)\}{1}?/', $format, $matches);
            $available_name_formats = array(
                '{name}',
                '{ref}',
            );
            if (is_array($matches[0])) {
                foreach($matches[0] as $m) {
                    if (!in_array($m, $available_name_formats)) {
                        throw new Exception('The value of parameter '.$m.' can not be found'); 
                    }
                }
            }
            // $sprintf_format = str_replace($available_name_formats, "%s", $format);            
        }

        // Check the avtivity
        if (!empty($this->activity)) {
            $all_activities = DB::table('taxonomy_activities')->select('identifier')->distinct()->get();
            $mapped_activities = array_map(function($a){
                if ($this->activity == $a){
                    return true;
                } else {
                    return false;
                }
            },$all_activities->pluck('identifier')->toArray());
            if (in_array(true , $mapped_activities )){
                $this->activity = $this->activity;
            } else {
                throw new Exception('The value of parameter activity '.$this->activity.' is not currect'); 
            }
        }

        // TODO: 
        // Check the app

        return true;
    }
}