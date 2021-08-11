<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Contact;
use App\Models\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Throwable;
use WPPConnectTeam\Wppconnect\Facades\Wppconnect;

class WhatsappController extends BaseController
{
    private Phone $phone;
    private Contact $contact;

    protected $url;
    protected $key;
    protected $session;

    /**
     * __construct function
     */
    public function __construct(Phone $phone, Contact $contact)
    {
        $this->url = config('wppconnect.defaults.base_uri');
        $this->key = config('wppconnect.defaults.secret_key');
        $this->session = "51982346281";

        $this->phone = $phone;
        $this->contact = $contact;
    }

    public function index(Request $request)
    {
        try {
            if($request->type != 'chat')
                return false;

            if(isset($request->id['fromMe']))
                return false;

            if(!$phone = $this->phone->where('phone_number', $request->session)->first())
                throw new \DomainException('Telefone não cadastrado');

            $fromContact = explode("@", $request->from);
            $fromContact = $fromContact[0];
            $nome_cliente = $request->sender['name'] ? $request->sender['name'] : $request->sender['pushname'];

            $msg = $request->body;

            $message = $phone->messages()->where('body', 'like', "%{$msg}%")->orWhere('description', $msg)->first();

//            $this->send($phone->phone_number, $phone->platform, $fromContact, $message->body);
        } catch (Throwable $th) {
            return response()->json([
                'response' => $th->getMessage()
            ]);
        }

    }

    /*
     * TODO: EXIBIR PARA USUÁRIO A URL wpp/{session}/{platform}
     *
     * 1 - Localizar número do telefone pela sessão
     * 2 - Localizar mensagem na base de dados com o código de evento
     * 3 - Enviar mensagem para o consumidor com o texto configurado
     * */
    public function send($session, $platform) //, $to, $message)
    {
        // Monetizze -> 1=AGUARDANDO_PAGAMENTO, 2=FINALIZADA_APROVADA, 3=CANCELADA, 4=DEVOLVIDA, 5=BLOQUEADA, 6=COMPLETA, 7=ABANDONO_DE_CHECKOUT, 8=OCULTO, 98=CARTAO, 99=BOLETO, 101=ASSINATURA_ATIVA, 102=ASSINATURA_INADIMPLENTE, 103=ASSINATURA_CANCELADA, 104=ASSINATURA_AGUARDANDO_PAGAMENTO
        // Woocommerce -> payment_complete - order_status_pending - order_status_failed - order_status_on-hold - order_status_processing - order_status_completed - order_status_refunded - order_status_cancelled


        if(!$phone = $this->phone->where('phone_number', $session)->first())
            throw new \DomainException('Telefone não cadastrado');

        $message = $phone->messages()->where('type', request()->tipoPostback['codigo'])->first();

        $phone_number = 55 . str_replace([' ', ',', '-', '(', ')'], '', request()->comprador['telefone']);
        Wppconnect::make($this->url);

        $token = Wppconnect::to("/api/{$session}/{$this->key}/generate-token")->asJson()->post();
        $token = json_decode($token->getBody()->getContents(),true);

        $response = Wppconnect::to("/api/{$session}/send-message")
            ->withBody([
                'phone' => $phone_number,
                'message' => $message
            ])->withHeaders([
                'Authorization' => "Bearer {$token['token']}"
            ])->asJson()->post();

dd(json_decode($response->getBody()->getContents()));
        return response()->json(json_decode($response->getBody()->getContents()));
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
//        try {
//            if(!$contact = $this->contact->firstContact()) {
//
//            }
//
//            $this->validate($request, $this->message->rules());
//
//            if(!$message = $this->message->messageByReference()) {
//                $message = $this->searchByReference();
//            }
//
//            return response()->json([
//                'response' => $message->body ?? $message
//            ]);
//        } catch (\Throwable $th) {
//            return response()->json([
//                'response' => $th->getMessage()
//            ]);
//        }
    }

    public function newQuestion(Request $request)
    {
//        $whatsapp = new Whatsapp;
//
//        return $whatsapp->sendMessage($request->number, $request->message);
    }

    /**
     * @return string
     */
    private function searchByReference(): string
    {
//        if (!$messages = $this->message->searchByReference()) {
//            throw new \DomainException('Não encontramos respostas, estamos direcionando voc6e para um atendente.');
//        }
//
//        $message = "Não encontramos o que você busca, mas talvez um dos tópicos abaixo ajudem: \n \n";
//        foreach ($messages as $me) {
//            $message .= "Para *$me->description* digite $me->reference_id\n";
//        }
//        return $message;
    }
}
