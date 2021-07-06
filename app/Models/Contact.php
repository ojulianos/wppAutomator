<?php

namespace App\Models;

use App\Traits\PhoneNumberTrait;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use PhoneNumberTrait;

    protected $fillable = [
        'number', 'document', 'order_id'
    ];

    public function firstContact()
    {
        $number = $this->clearPhoneNumber(request()->from);

        $contact = $this->where(
            'number', $number
        );

        if($contact->count() <= 0) {
            $this->number = $number;
            return $this->save();
        }

        return $this->get()->first();
    }

    public function setContactDocument()
    {
        $this->order_id = request()->body;
        $this->document = request()->body;
        return $this->save();
    }
}
