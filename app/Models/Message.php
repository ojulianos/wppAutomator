<?php

namespace App\Models;

use App\Traits\PhoneNumberTrait;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use PhoneNumberTrait;

    protected $fillable = [
        'reference_id', 'reference_phone', 'description', 'body'
    ];

    protected $hidden = [
        'message_id', 'id'
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

    public function rules(){
        return [
            'message_id' => 'nullable|exists:messages,id',
            'reference_id' => 'required|numeric',
            'reference_phone' => 'required|exists:phones,phone_number',
            'description' => 'required|min:5',
            'body' => 'required|min:5',
        ];
    }

    public function phone()
    {
        return $this->belongsTo(Phone::class);
    }

    public function createNew($phone)
    {
        $this->message_id = request()->message_id;
        $this->reference_id = request()->reference_id;
        $this->reference_phone = $phone;
        $this->description = request()->description;
        $this->body = request()->body;
        return $this->save();
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
