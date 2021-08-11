<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\StoreUpdatePhoneRequest;

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
            ->paginate();
        $phones->getCollection()
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
        if(!$phone = $this->phone->create($request->all()))
            return $this->sendError('Erro ao cadastrar o telefone');

        return $this->sendResponse($phone, 'Telefone Cadastrado');
    }

    public function show($id)
    {
        $phone = $this->phone->find($id);

        return $this->sendResponse($phone, 'Detalhes do telefone');
    }

    public function update($id, StoreUpdatePhoneRequest $request)
    {
        if(!$phone = $this->phone->findOrFail($id))
            return $this->sendError('Telefone não encontrado.');

        if(!$phone->update($request->all()))
            return $this->sendError('Telefone não atualizado.');

        return $this->sendResponse($phone, 'Telefone Atualizado');
    }

    public function destroy($id)
    {
        if(!$phone = $this->phone->findOrFail($id))
            return $this->sendError('Telefone não encontrado.');

        if(!$phone->destroy($id))
            return $this->sendError('Telefone não excluído.');

        return $this->sendResponse($phone, 'Telefone Excluído');
    }

}
