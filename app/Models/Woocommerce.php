<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class Woocommerce extends Model
{

    protected $guarded = [];

    protected $hidden = [];
    /**
     * @var Http
     */
    private Http $http;

    public function __construct(Http $http)
    {
        $this->http = $http;
    }

    public function getUser()
    {
        return $this->belongsTo(Phone::class);
    }
}
