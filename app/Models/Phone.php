<?php

namespace App\Models;

use App\Traits\PhoneNumberTrait;
use Illuminate\Database\Eloquent\Model;


class Phone extends Model
{
    use PhoneNumberTrait;

    protected $fillable = [
        'phone_number', 'name', 'platform', 'platform_api_url'
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

    public function createNew()
    {
        $this->phone_number = request()->phone_number;
        $this->name = request()->name;
        $this->platform = request()->platform;
        $this->platform_api_url = request()->platform_api_url;
        return $this->save();
    }

    public function getStatusAttribute()
    {
        return false;
    }

}
