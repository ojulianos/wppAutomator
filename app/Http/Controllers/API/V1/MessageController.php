<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Contact;
use App\Models\Message;
use App\Models\Phone;
use App\Models\Whatsapp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MessageController extends BaseController
{
    /**
     * @var Message
     */
    private $message;
    /**
     * @var Contact
     */
    private Contact $contact;

    /**
     * Create a new controller instance.
     *
     * @param Message $message
     */
    public function __construct()
    {
//        $this->message = $message;
//        $this->contact = $contact;
    }


    public function index($phone)
    {
        $phone = Phone::find($phone);
        $messages = $phone->messages->paginate();

        return response()->json($messages);
    }

    public function store($phone, Request $request)
    {
        $this->validate($request, $this->message->rules());

        return $this->message->createNew($phone);
    }

    public function show($phone, $id)
    {
        $message = $this->message->where('reference_phone', $phone)->find($id);

        return $message;
    }

    public function update($phone, $id, Request $request)
    {
        $this->validate($request, $this->message->rules());
        $message = $this->message->where('reference_phone', $phone)->find($id);

        return $message->save($request->all());
    }

    public function destroy($phone, $id)
    {
        $message = $this->message->where('reference_phone', $phone)->find($id);

        return $message->destroy($id);
    }


    /*
 {
    "event":"onack",
    "session":"oooooo",
    "id":{
        "fromMe":false,
        "remote":"554891345850@c.us",
        "id":"3EB024BA372599959E38",
        "_serialized":"false_554891345850@c.us_3EB024BA372599959E38"
    },
    "body":"oi",
    "type":"chat",
    "t":1622466548,
    "notifyName":"",
    "from":"554891345850@c.us",
    "to":"555182346281@c.us",
    "self":"in",
    "ack":3,
    "invis":false,
    "isNewMsg":true,
    "star":false,
    "recvFresh":true,
    "isFromTemplate":false,
    "broadcast":false,
    "mentionedJidList":[],
    "isVcardOverMmsDocument":false,
    "isForwarded":false,
    "labels":[],
    "isDynamicReplyButtonsMsg":false
}

         * 1 - IDENTIFICA SE É O PRIMEIRO CONTATO
         *  1.1 - MANDA MENSAGEM DE BOAS VINDAS
         *  1.2 - MANDAR MENSAGEM DE BOAS VINDAS PERSONALIZADA
         *
         * 2 - IDENTIFICA A PLATAFORMA
         *  2.1 - CASO A PLATAFORMA NECESSITE DO DOCUMENTO OU NUMERO DO PEDIDO PEDIR O DADO
         *  2.2 - COMUNICAR COM A PLATAFORMA
         *  2.3 - ENVIAR RESUDO DO PEDIDO PARA USUÁRIO
         *
         * 3 - BUSCAR MENSAGEM
         *  3.1 - EXIBIR MENSAGEM DE UM RESULTADO
         *  3.2 - EXIBIR MENSAGEM DE RESULTADOS
         *  3.3 - EXIBIR MENSAGEM DE RESULTADO NÃO ENCONTRADO E CHAMAR ATENDENTE HUMANO
         *
         * 4 - MENSAGEM DE ADEUS/FINALIZAÇÃO
         *
         * */
    public function getQuestions(Request $request)
    {
        try {
            if(!$contact = $this->contact->firstContact()) {

            }

            $this->validate($request, $this->message->rules());

            if(!$message = $this->message->messageByReference()) {
                $message = $this->searchByReference();
            }

            return response()->json([
                'response' => $message->body ?? $message
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'response' => $th->getMessage()
            ]);
        }
    }

    public function newQuestion(Request $request)
    {
        $whatsapp = new Whatsapp;

        return $whatsapp->sendMessage($request->number, $request->message);
    }

    /**
     * @return string
     */
    private function searchByReference(): string
    {
        if (!$messages = $this->message->searchByReference()) {
            throw new \DomainException('Não encontramos respostas, estamos direcionando voc6e para um atendente.');
        }

        $message = "Não encontramos o que você busca, mas talvez um dos tópicos abaixo ajudem: \n \n";
        foreach ($messages as $me) {
            $message .= "Para *$me->description* digite $me->reference_id\n";
        }
        return $message;
    }
}
