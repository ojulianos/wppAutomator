<?php

namespace App\Models;

use App\Traits\PhoneNumberTrait;
use Illuminate\Database\Eloquent\Model;


class Phone extends Model
{
    use PhoneNumberTrait;

    protected $fillable = [
        'phone_number', 'name', 'platform', 'platform_api_url', 'platform_api_key'
    ];

    protected $search = [
        'from' => '',
        'to' => '',
    ];

    public function __contruct()
    {
        $this->search = [
            'from' => $this->clearPhoneNumber(request()->from),
            'to' => $this->clearPhoneNumber(request()->to),
        ];
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'reference_phone', 'phone_number');
    }

    public function getStatusAttribute()
    {
        return false;
    }

}
