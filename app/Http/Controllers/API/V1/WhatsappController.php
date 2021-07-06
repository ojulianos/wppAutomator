<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;

class WhatsappController extends BaseController
{
    public function __construct()
    {
    }

    public function index(Request $request)
    {

        try {
            if(!isset($_POST) && !isset($_POST['_mesage_search'])) {
                throw new DomainException('Código ou mensagem não informada');
            }

            $reference = clearDataReference($_POST['_message_reference']);
            $sql = "SELECT *
            FROM messages
            WHERE reference_phone = '{$reference}'
            AND reference_id = '{$_POST['_mesage_search']}'
            ORDER BY id DESC LIMIT 1";
            $query = $conn->query($sql);
            if(!$messages = $query->fetch_object()) {
                $sql = "SELECT *
                FROM messages
                WHERE reference_phone = '{$reference}'
                AND (body LIKE '%{$_POST['_mesage_search']}%' OR description = '{$_POST['_mesage_search']}')
                ORDER BY id DESC";
                $query = $conn->query($sql);

                if(!$messages = $query->fetch_all()) {
                    throw new DomainException('Código ou mensagem não encontrada');
                }

                var_dump($messages);
                return;
            }

            echo json_encode([
                'response' => $messages->body
            ]);
        } catch (Throwable $th) {
            echo json_encode([
                'response' => $th->getMessage()
            ]);
        }

    }
}
