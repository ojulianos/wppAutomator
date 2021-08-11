<?php

namespace App\Models;

use App\Traits\PhoneNumberTrait;
use Illuminate\Database\Eloquent\Model;

class DefaultPlatform extends Model
{
    use PhoneNumberTrait;

    protected $fillable = [
        'name',
        'email',
        'birth_date',
        'document',
        'phone',
        'cep', 'endereco', 'numero', 'complemento', 'bairro', 'cidade', 'estado', 'pais',
    ];

    protected $dates = [
        'birth_date'
    ];

    protected array $events = [];
    protected object $user;
    protected object $items;
    protected object $sale;

    public function __construct()
    {

    }

    public function getEvents()
    {
        return $this->events;
    }

    public function setUser($user): void
    {
        $this->fillable = $user;
    }

    public function getUser()
    {
        return $this->fillable;
    }

    public function setItems($items = []): void
    {
        $this->items = $items;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function setSale($sale): void
    {
        $this->sale = $sale;
    }

    public function getSale()
    {
        return $this->sale;
    }

}
