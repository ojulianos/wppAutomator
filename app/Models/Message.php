<?php

namespace App\Models;

use App\Traits\PhoneNumberTrait;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use PhoneNumberTrait;

    protected $fillable = [
        'message_id',
        'reference_id',
        'reference_phone',
        'description',
        'body',
        'tags',
        'type',
    ];

    protected $search = [
        'phone' => '',
        'body' => '',
    ];

    public function __construct()
    {
        $this->search = [
            'phone' => $this->clearPhoneNumber(request()->_message_reference),
            'body' => request()->_mesage_search,
        ];
    }

    public function phone()
    {
        return $this->belongsTo(Phone::class);
    }

    public function messageByReference()
    {
        return $this
            ->where('reference_phone', $this->search['phone'])
            ->where('reference_id', $this->search['body'])
            ->first();
    }

    public function searchByReference()
    {
        return $this
            ->where('reference_phone', $this->search['phone'])
            ->where(function($query) {
                $query->where('body', 'LIKE', "%{$this->search['body']}%")
                    ->orWhere('description', 'LIKE', "%{$this->search['body']}%");
            })
            ->get();
    }


}
