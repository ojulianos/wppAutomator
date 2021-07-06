<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\StoreUpdatePhoneRequest;
use App\Models\Phone;
use Illuminate\Http\Request;

class PhoneController extends BaseController
{
    public $phone;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->phone = auth('api')->user()->phones();
    }

    public function index()
    {
        $phones = $this->phone
            ->paginate()
            ->getCollection()
            ->transform(function ($value) {
            return [
                'id' => $value->id,
                'name' => $value->name,
                'phone_number' => $value->phone_number,
                'platform' => $value->platform,
                'platform_api_url' => $value->platform_api_url,
                'total_messages' => $value->messages->count(),
                'status' => $value->status,
            ];
        });

        return $this->sendResponse($phones, 'phones list');
    }

    public function store(StoreUpdatePhoneRequest $request)
    {
        $phone = $this->phone->createNew();
        dd($phone);

        return $this->sendResponse($phone, 'Telefone Cadastrado');
    }

    public function show($id)
    {
        $phone = $this->phone->find($id);

        return $this->sendResponse($phone, 'Detalhes do telefone');
    }

    public function update($id, Request $request)
    {
        $request->validate($this->phone->rules());
        $phone = $this->phone->find($id);

        return $this->sendResponse($phone->save($request->all()), 'Telefone Atualizado');
    }

    public function destroy($id)
    {
        $phone = $this->phone->find($id);

        return $this->sendResponse($phone->destroy($id), 'Telefone Excluído');
    }

}
