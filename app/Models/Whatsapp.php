<?php

namespace App\Models;

use App\Traits\PhoneNumberTrait;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Http;

class Whatsapp extends Model
{
    use PhoneNumberTrait;

    /**
     * @var Http
     */
    private Http $http;
    private $_URL;

    public function __construct(Http $http)
    {
        $this->_URL = env('WPP_SERVER_URL');
        $this->http = $http;
    }

    public function rules(){
        return [];
    }

    public function sendMessage($number, $message)
    {
        return $this->http->post("{$this->_URL}send-message", [
            'number' => $number,
            'message' => $message,
        ]);
    }




}
