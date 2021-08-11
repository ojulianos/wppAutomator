<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\StoreUpdateMessageRequest;
use App\Models\Message;

class MessageController extends BaseController
{
    private $phone;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->phone = auth('api')->user()->phones();
    }

    public function index($phone)
    {
        $messages = $this->phone->findOrFail($phone)->messages()->paginate();

        return $this->sendResponse($messages, 'messages list');
    }

    public function store($phone, StoreUpdateMessageRequest $request)
    {
        $phone = $this->phone->findOrFail($phone);
        $messages = new Message;

        $messages->reference_id = $request->reference_id;
        $messages->reference_phone = $phone->phone_number;
        $messages->description = $request->description;
        $messages->body = $request->body;
        $messages->tags = collect($request->tags)->implode('text', ',');
        $messages->type = $request->type;

        if(!$messages->save())
            return $this->sendError('Erro ao cadastrar o mensagem');

        return $this->sendResponse($messages, 'Mensagem Cadastrado');
    }

    public function update($id, StoreUpdateMessageRequest $request)
    {
        if(!$messages = Message::find($id))
            return $this->sendError('Mensagem não encontrada');

        $messages->reference_id = $request->reference_id;
        $messages->description = $request->description;
        $messages->body = $request->body;
        $messages->tags = collect($request->tags)->implode('text', ',');
        $messages->type = $request->type;

        if(!$messages->save())
            return $this->sendError('Mensagem não atualizada');

        return $this->sendResponse($messages, 'Mensagem Cadastrado');
    }

    public function destroy($id)
    {
        if(!$messages = Message::find($id))
            return $this->sendError('Mensagem não encontrada');

        return $messages->destroy($id);
    }

}
