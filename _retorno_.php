<?php
header("Content-Type: application/json;charset=utf-8");
date_default_timezone_set("America/New_york");

$servidor = $_SERVER['DOCUMENT_ROOT'];
require_once($servidor . "/js_config.php");
require_once(URL_ROOT . "/sistema/classes/classe.database.php");
require_once($servidor . "/sistema/classes/zapi/zapiEnv.php");

$conn = new JS_Consulta();

//recuperacao de dados posts
$ttdd = file_get_contents('php://input');

$ttee = json_decode($ttdd);
$ttee = ($ttee->data);

$micro = time();
$horario = date('y/m/d H:i:s');

$arquivo = fopen('debbug.txt','a+');
if ($arquivo == false) die('Não foi possível criar o arquivo.');
$texto = $ttdd ."\n\n";
fwrite($arquivo, $texto);
fclose($arquivo);

if (!empty($ttdd)) {

    $tipoMsg = $ttee->type;

    if ($ttee->isGroupMsg == "true") {
        return;
    }

    // obriga a gravação do registro quando for arquivo
    $tipo = $ttee->type;
    $midiaType = $ttee->mimetype ?
        str_replace("\/", "/", $ttee->mimetype) :
        "";
    /* definimos o tipo de midia e a extenção da referencia */
    if ($tipo == "chat") {

        $midia = "chat";
        $ext = "";

    } elseif ($tipo == "location") {

        $midia = "location";
        $ext = "";

    } else {

        switch ($midiaType) {
            case "":
                $ext = "";
                $midia = "chat";
                break;
            case "video/mp4":
                $ext = ".mp4";
                $midia = "video";
                break;
            case "image/jpeg":
                $ext = ".jpeg";
                $midia = "image";
                break;
            case "image/jpg":
                $ext = ".jpg";
                $midia = "image";
                break;
            case "image/png":
                $ext = ".png";
                $midia = "image";
                break;
            case "audio/ogg; codecs=opus":
                $ext = ".ogg";
                $midia = "ptt";
                break;
            case "audio/ogg;":
                $ext = ".ogg";
                $midia = "ptt";
                break;
            case "application/pdf":
                $ext = ".pdf";
                $midia = "document";
                break;
            case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
                $ext = ".xlsx";
                $midia = "document";
                break;
            case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
                $ext = ".docx";
                $midia = "document";
                break;
            case "application/vnd.openxmlformats-officedocument.presentationml.presentation":
                $ext = ".pptx";
                $midia = "document";
                break;
            case "application/msword":
                $ext = ".doc";
                $midia = "document";
                break;
            case "application/vnd.ms-excel":
                $ext = ".xls";
                $midia = "document";
                break;
            case "application/octet-stream":
                $ext = ".txt";
                $midia = "document";
                break;
            case "text/plain":
                $ext = ".txt";
                $midia = "document";
                break;
        }
    }

    /* geramos a referencia a partir do id da mensagem caso seja arquivo */
    $referencia = md5($ttee->id) . $ext;

    if (($tipo == "chat") or ($tipo == "image") or ($tipo == "location") or ($tipo == "video") or ($tipo == "ptt") or ($tipo == "document")) {

        $sql_bgeral = "SELECT * FROM js_wh_geral WHERE idl='1'";
        $bgeral = $conn->consulta($sql_bgeral);
        if ($bgeral) {
            if (mysqli_num_rows($bgeral) > 0) {
                $row6 = mysqli_fetch_array($bgeral);
                $bot_init = $row6['bot_init'];
            }
        }

        $unix= time();
        $date = new DateTime();
        $whatsapp = explode("@", $ttee->from);
        $whatsapp = $whatsapp[0];

        $fromContact = explode("@", $ttee->to);
        $fromContact = $fromContact[0];


        $queryClient = $conn->consulta("SELECT unix_foto,imagem,unix_foto FROM js_wh_clientes WHERE whatsapp= '{$whatsapp}'");
        $photoClientUpdate = mysqli_fetch_object($queryClient);

        $updatePhoto = $photoClientUpdate->unix_foto + 43200;

        if(time() > $updatePhoto ){

            $photoContact = ((($ttee->chat)->contact)->profilePicThumbObj)->eurl ?
                ((($ttee->chat)->contact)->profilePicThumbObj)->eurl :
                "./midias/imagens/foto_padrao.jpg";

            $unixPhoto = time();
            $updateClient = $conn->consulta("UPDATE js_wh_clientes SET imagem='{$photoContact}', unix_foto='{$unixPhoto}' WHERE whatsapp='{$whatsapp}'");

        }


        $tmp = $date->format('U = Y-m-d H:i:s');
        $msg = $ttee->body;
        $idchat = $ttee->id;

        $nome_cliente = (($ttee->chat)->contact)->name ?
            (($ttee->chat)->contact)->name :
            (($ttee->chat)->contact)->pushname;

        $nome_agenda  = (($ttee->chat)->contact)->pushname ?
            (($ttee->chat)->contact)->pushname :
            $whatsapp;

        $mimetype     = $midiaType;

        $lpna = str_replace(" ", "", $nome_agenda);
        $lpna = str_replace("+", "", $lpna);
        $lpna = str_replace("-", "", $lpna);

        if (is_numeric($lpna)) {
            $nmag = $nome_cliente;
        } else {
            $nmag = $nome_agenda;
        }

        if ($nmag == "") {
            $nmag = $whatsapp;
        }

        if (($midia == "image") or ($midia == "video")) {

            $width = $ttee->width;
            $height = $ttee->height;
            $size = $ttee->size;
            $legenda = $ttee->caption ? $ttee->caption : "";
            $referencia = $referencia;
            $url_upp = "";

        } else if ($midia == "ogg") {
            /** aqui so muda se for diferente não precisa mexer pronto */

            $referencia = $referencia;

        } else if ($midia == "document") {

            $referencia = $referencia;
            $mimetype = $midiaType;

        } elseif ($midia == "location") {

            $tipo_loc = "";
            $lat = $ttee->lat;
            $lng = $ttee->lng;
        }

        if ($whatsapp == "") {

            /*sem ação*/

        } else {

            if (substr($whatsapp, 0, 2) == 55) {

                $exnum = strlen($whatsapp);

                if ($exnum == 13) {
                    $nono = "sim";
                } elseif ($exnum == 12) {
                    $nono = "nao";
                }

                if ($nono == "sim") {

                    $pt1nono = substr($whatsapp, 0, 4);
                    $pt2nono = substr($whatsapp, 5, 12);
                    $semnono = $pt1nono . $pt2nono;
                    $comnono = $whatsapp;

                } elseif ($nono == "nao") {

                    $pt1sono = substr($whatsapp, 0, 4);
                    $pt2sono = substr($whatsapp, 4, 12);
                    $comnono = $pt1sono . "9" . $pt2sono;
                    $semnono = $whatsapp;
                }

            } else {
                $semnono = $whatsapp;
            }

            if ($midia == "ptt") {
                $mdenv = "audio";
            } elseif ($midia == "image") {
                $mdenv = "imagem";
            } else {
                $mdenv = "texto";
            }

            $sel = $conn->consulta("SELECT * FROM js_wh_conversacao WHERE idchat='{$semnono}' OR idchat='{$comnono}' OR idchat='{$whatsapp}' ");

            if ($sel) {

                if (mysqli_num_rows($sel) > 0) {

                    $row = mysqli_fetch_array($sel);
                    $idap = $row['aplicacao'];
                    $whatsr = $row['idchat'];

                    if ($whatsr == $semnono) {

                        if ($nono == "sim") {

                            $sql_up="UPDATE js_wh_conversacao SET idchat='{$whatsapp}' WHERE idchat='{$whatsr}'";
                            $up= $conn->consulta($sql_up);

                            $sql_up2="UPDATE js_wh_clientes SET nome='{$nmag}', whatsapp='{$whatsapp}' WHERE whatsapp='{$whatsr}'";
                            $up2= $conn->consulta($sql_up2);

                        } else {

                        }
                    }

                    if ($whatsr == $comnono) {

                        if ($nono == "nao") {
                            $sql_up = "UPDATE js_wh_conversacao SET idchat='{$whatsapp}' WHERE idchat='{$whatsr}'";
                            $up = $conn->consulta($sql_up);

                            $sql_up2 = "UPDATE js_wh_clientes SET nome='{$nmag}', whatsapp='{$whatsapp}' WHERE whatsapp='{$whatsr}'";
                            $up2 = $conn->consulta($sql_up2);

                        } else {

                        }
                    }

                    if ($idap == "7") {

                        $sql_bcm = "SELECT * FROM js_wh_clientes WHERE whatsapp='{$whatsr}'";
                        $bcm = $conn->consulta($sql_bcm);

                        if ($bcm) {

                            if (mysqli_num_rows($bcm) > 0) {
                                $row7 = mysqli_fetch_array($bcm);
                                $nms = $row7['msgag'];
                                $newag = $nms + 1;

                            }
                        }
                    }

                    if (($idap != "4") and ($idap != "3")) {


                        $sql_updb = "UPDATE js_wh_clientes SET chatbot='sim' , novo='true' , msgag='1' ,  encerrach='nao' , chat_unix='{$micro}' WHERE whatsapp='{$whatsr}'";
                        $updb = $conn->consulta($sql_updb);

                        $sql_bsel = "SELECT * FROM js_wh_clientes WHERE whatsapp='{$whatsr}'";
                        $bsel = $conn->consulta($sql_bsel);

                        if ($bsel) {

                            if (mysqli_num_rows($bsel) > 0) {
                                $row5 = mysqli_fetch_array($bsel);
                                $idlc = $row5['idl'];
                                $protocolo = $row5['protocolo'];
                            }
                        }

                        $msglp = $conn->limpar($msg);

                        $numero = $whatsapp;
                        $datai = $horario;
                        $idw = $idlc;
                        $unix = $micro;
                        $lpn = $msglp;

                        if ($midia == "image") {

                            $sql_upd3 = "INSERT INTO js_wh_mensagens (data_msg , autoria , atendente , midia , cliente , whatsapp , mimetype , iwidth , iheight , isize , referencia  , legenda , tipo , protocolo) VALUES ('{$datai}','cliente','{$atendente}','imagem','{$idw}','{$numero}','{$mimetype}','{$width}','{$height}','{$size}','{$referencia}','{$legenda}','humano','{$protocolo}')";
                            $upd3 = $conn->consulta($sql_upd3);

                            if ($legenda != "") {

                                $legenda = $conn->limpar($legenda);
                                $sql_upd3 = "INSERT INTO js_wh_mensagens (mensagem , data_msg , autoria , atendente , midia , cliente , whatsapp, tipo , protocolo) VALUES ('{$legenda}','{$datai}','cliente','{$atendente}','texto','{$idw}','{$numero}','humano','{$protocolo}')";
                                $upd3 = $conn->consulta($sql_upd3);

                            }
                            if ($upd3) {

                                $sql_upc = "UPDATE js_wh_clientes SET novo='true' , msgag='{$newag}' , unixmsg='{$unix}' WHERE whatsapp='{$numero}'";
                                $upc = $conn->consulta($sql_upc);
                            }

                        } elseif ($midia == "chat") {


                            $sql_upd3 = "INSERT INTO js_wh_mensagens (mensagem , data_msg , autoria , atendente , midia , cliente , whatsapp , tipo , protocolo) VALUES ('{$lpn}','{$datai}','cliente','{$atendente}','texto','{$idw}','{$numero}','humano','{$protocolo}')";
                            $upd3 = $conn->consulta($sql_upd3);

                            if ($upd3) {
                                $sql_upc = "UPDATE js_wh_clientes SET novo='true' , msgag='{$newag}' , unixmsg='{$unix}' WHERE whatsapp='{$numero}'";
                                $upc = $conn->consulta($sql_upc);
                            }

                            if ($bot_init == "true") {


                                $sql_bcb = "SELECT * FROM js_wh_autoresponders WHERE pergunta='{$lpn}' and ordem='1' LIMIT 0,1";
                                $bcb = $conn->consulta($sql_bcb);

                                if ($bcb) {
                                    if (mysqli_num_rows($bcb) > 0) {

                                        $ttbcb = mysqli_num_rows($bcb);
                                        $row2 = mysqli_fetch_array($bcb);
                                        $resposta = $row2['resposta'];

                                        $sql_upd3 = "INSERT INTO js_wh_mensagens (mensagem , data_msg , autoria , atendente , midia , cliente , whatsapp , tipo , protocolo) VALUES ('{$resposta}','{$datai}','bot','{$atendente}','texto','{$idw}','{$numero}','humano','{$protocolo}')";
                                        $upd3 = $conn->consulta($sql_upd3);

                                        if ($upd3) {

                                            require_once(JS_CAMINHO . "/sistema/classes/classe.disparo.php");
                                            $env = new Disparo();
                                            $retorno = $env->disparar($numero, $resposta, "texto", "", "sim", "nao");

                                            $sql_upc = "UPDATE js_wh_clientes SET novo='true' , unixmsg='{$unix}' WHERE whatsapp='{$numero}'";
                                            $upc = $conn->consulta($sql_upc);
                                        }

                                    } else {
                                        $sql_bcb = "SELECT * FROM js_wh_autoresponders WHERE ordem='4'";
                                        $bcb = $conn->consulta($sql_bcb);
                                        if ($bcb) {
                                            if (mysqli_num_rows($bcb) > 0) {
                                                $ttbcb = mysqli_num_rows($bcb);
                                                while ($row3 = mysqli_fetch_array($bcb)) {
                                                    $pergunta[] = $row3['pergunta'];
                                                    $resposta[] = $row3['resposta'];
                                                }

                                                function tirarAcentos($string)
                                                {
                                                    return preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/"), explode(" ", "a A e E i I o O u U n N"), $string);
                                                }

                                                $cont = 0;
                                                while ($cont < $ttbcb) {

                                                    $perguntalo = mb_strtolower($pergunta[$cont]);
                                                    $perguntalo = tirarAcentos($perguntalo);
                                                    $perguntalo = preg_replace('/( )+/', ' ', $perguntalo);
                                                    $perguntalo = str_replace('Ç', 'c', $perguntalo);
                                                    $perguntalo = str_replace('ç', 'c', $perguntalo);

                                                    $pattern = '/' . $perguntalo . '/';//Padrão a ser encontrado na string $tags
                                                    $strto = mb_strtolower($lpn);
                                                    $strto = tirarAcentos($strto);
                                                    $strto = preg_replace('/( )+/', ' ', $strto);
                                                    $strto = str_replace('Ç', 'c', $strto);
                                                    $strto = str_replace('ç', 'c', $strto);

                                                    if (preg_match($pattern, $strto)) {
                                                        $sql_upd3 = "INSERT INTO js_wh_mensagens (mensagem , data_msg , autoria , atendente , midia , cliente , whatsapp , tipo , protocolo) VALUES ('{$resposta[$cont]}','{$datai}','bot','{$atendente}','texto','{$idw}','{$numero}','humano','{$protocolo}')";
                                                        $upd3 = $conn->consulta($sql_upd3);

                                                        if ($upd3) {
                                                            require_once(URL_ROOT . "/sistema/classes/classe.disparo.php");
                                                            $env = new Disparo();
                                                            $retorno = $env->disparar($numero, $resposta[$cont], "texto", "", "sim", "nao");

                                                            $sql_upc = "UPDATE js_wh_clientes SET novo='true' , unixmsg='{$unix}' WHERE whatsapp='{$numero}'";
                                                            $upc = $conn->consulta($sql_upc);

                                                            exit;

                                                            $cont += 10000;
                                                        }
                                                    } else {
                                                        $cont++;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                        } elseif ($midia == "ptt") {

                            $sql_upd3 = "INSERT INTO js_wh_mensagens (mensagem , data_msg , autoria , atendente , midia , cliente , whatsapp ,  referencia , tipo , protocolo) VALUES ('{$lpn}','{$datai}','cliente','{$atendente}','ptt','{$idw}','{$numero}','{$referencia}','humano','{$protocolo}')";
                            $upd3 = $conn->consulta($sql_upd3);

                            if ($upd3) {
                                $sql_upc = "UPDATE js_wh_clientes SET novo='true' , msgag='{$newag}' , unixmsg='{$unix}' WHERE whatsapp='{$numero}'";
                                $upc = $conn->consulta($sql_upc);
                            }
                        } elseif ($midia == 'document') {

                            $sql_upd3 = "INSERT INTO js_wh_mensagens (mensagem , data_msg , autoria , atendente , midia , cliente , whatsapp ,  referencia , mimetype , tipo , protocolo) VALUES ('{$lpn}','{$datai}','cliente','{$atendente}','pdf','{$idw}','{$numero}','{$referencia}','{$mimetype}','humano','{$protocolo}')";
                            $upd3 = $conn->consulta($sql_upd3);


                            if ($upd3) {
                                $sql_upc = "UPDATE js_wh_clientes SET novo='true' , msgag='{$newag}' , unixmsg='{$unix}' WHERE whatsapp='{$numero}'";
                                $upc = $conn->consulta($sql_upc);
                            }
                        } elseif ($midia == "vcard") {

                            $numer = explode("waid=", $lpn);
                            $numer2 = explode(":", $numer[1]);
                            $numer3 = $numer2[0];

                            $nomee = explode("FN:", $lpn);
                            $nomee2 = explode("TEL;", $nomee[1]);
                            $nome3 = $nomee2[0];

                            $msgsv = "O cliente enviou um contato! Whatsapp: " . $numer3 . " Nome do contato: " . $nome3;

                            $sql_upd3 = "INSERT INTO js_wh_mensagens (mensagem , data_msg , autoria , atendente , midia , cliente , whatsapp , tipo , protocolo) VALUES ('{$msgsv}','{$datai}','cliente','{$atendente}','texto','{$idw}','{$numero}','humano','{$protocolo}')";
                            $upd3 = $conn->consulta($sql_upd3);

                            if ($upd3) {
                                $sql_upc = "UPDATE js_wh_clientes SET novo='true' , msgag='{$newag}' , chat_unix='{$unix}' , unixmsg='{$unix}' WHERE whatsapp='{$numero}'";
                                $upc = $conn->consulta($sql_upc);
                            }

                        } elseif ($midia == "location") {

                            if ($tipo_loc == "tempo_real") {
                                $msgsv = "O cliente enviou a localização em tempo real:<br>Google Maps: <a href=\"https://www.google.com/maps/search/?api=1&query=" . $lat . "," . $lng . "\" target=\"_blank\">Visualizar Compartilhamento</a>";


                            } else {
                                $msgsv = "O cliente enviou uma localização fixa:<br>Google Maps: <a href=\"https://www.google.com/maps/search/?api=1&query=" . $lat . "," . $lng . "\" target=\"_blank\">Visualizar Compartilhamento</a>";
                            }

                            $sql_upd3 = "INSERT INTO js_wh_mensagens (mensagem , data_msg , autoria , atendente , midia , cliente , whatsapp , tipo , protocolo) VALUES ('{$msgsv}','{$datai}','cliente','{$atendente}','texto','{$idw}','{$numero}','humano','{$protocolo}')";
                            $upd3 = $conn->consulta($sql_upd3);

                            if ($upd3) {
                                $sql_upc = "UPDATE js_wh_clientes SET novo='true' , msgag='{$newag}' , chat_unix='{$unix}' , unixmsg='{$unix}' WHERE whatsapp='{$numero}'";
                                $upc = $conn->consulta($sql_upc);
                            }
                        } elseif ($midia == "video") {

                            $sql_upd3 = "INSERT INTO js_wh_mensagens (data_msg , autoria , atendente , midia , cliente , whatsapp , mimetype , iwidth , iheight , isize , referencia  , legenda , tipo , protocolo) VALUES ('{$datai}','cliente','{$atendente}','video','{$idw}','{$numero}','{$mimetype}','{$width}','{$height}','{$size}','{$referencia}','{$legenda}','humano','{$protocolo}')";
                            $upd3 = $conn->consulta($sql_upd3);

                            if ($legenda != "") {

                                $legenda = $conn->limpar($legenda);
                                $sql_upd3 = "INSERT INTO js_wh_mensagens (mensagem , data_msg , autoria , atendente , midia , cliente , whatsapp, tipo , protocolo) VALUES ('{$legenda}','{$datai}','cliente','{$atendente}','texto','{$idw}','{$numero}','humano','{$protocolo}')";
                                $upd3 = $conn->consulta($sql_upd3);
                            }

                            if ($upd3) {
                                $sql_upc = "UPDATE js_wh_clientes SET novo='true' , msgag='{$newag}' , unixmsg='{$unix}' WHERE whatsapp='{$numero}'";
                                $upc = $conn->consulta($sql_upc);
                            }


                        } else {

                            $msgdc = "Este tipo de arquivo não é suportado, por favor digite sua dúvida!";

                            require_once(URL_ROOT . "/sistema/classes/classe.disparo.php");
                            $env = new Disparo();
                            $retorno = $env->disparar($numero, $msgdc, "texto", "", "sim", "nao");

                            $msgcl = "O cliente enviou um arquivo não suportado, pede para digitar a dúvida";
                            $sql_upd3 = "INSERT INTO js_wh_mensagens (mensagem , data_msg , autoria , atendente , midia , cliente , whatsapp , tipo , protocolo) VALUES ('{$msgcl}','{$datai}','cliente','{$atendente}','texto','{$idw}','{$numero}','humano','{$protocolo}')";
                            $upd3 = $conn->consulta($sql_upd3);

                            $sql_upd3 = "INSERT INTO js_wh_mensagens (mensagem , data_msg , autoria , atendente , midia , cliente , whatsapp , tipo , protocolo) VALUES ('{$msgdc}','{$datai}','bot','{$atendente}','texto','{$idw}','{$numero}','humano','{$protocolo}')";
                            $upd3 = $conn->consulta($sql_upd3);

                            if ($upd3) {
                                $sql_upc = "UPDATE js_wh_clientes SET novo='true' , msgag='{$newag}' , unixmsg='{$unix}' WHERE whatsapp='{$numero}'";
                                $upc = $conn->consulta($sql_upc);
                            }
                        }
                    }


                    $sql_sl = "SELECT * FROM js_wh_aplicacoes WHERE idapl='{$idap}'";
                    $sl = $conn->consulta($sql_sl);
                    if ($sl) {
                        if (mysqli_num_rows($sl) > 0) {

                            $row2 = mysqli_fetch_array($sl);
                            $req = $row2['req'];

                            require_once('' . $servidor . $req);
                            $exe = executar($whatsr, $idchat, $msg, $midia, $mimetype, $width, $height, $size, $url_upp, $legenda, $referencia, $tipo_loc, $lat, $lng);

                        } else {

                            $req = "/sistema/funcoes/chat_bot/padrao_contato.php";
                            require_once('' . $servidor . $req);
                            $exe = executar($whatsr, $idchat, $msg, $nome_cliente);
                        }
                    }


                } else {


                    $sql_insert = "INSERT INTO js_wh_conversacao (ultmsg , aplicacao , idchat) VALUES ('{$msg}','0','{$whatsapp}')";
                    $insert = $conn->consulta($sql_insert);

                    $sql_insert2 = "INSERT INTO js_wh_clientes (nome , imagem , whatsapp , ativo , inicia , statusat , msgag , chatbot , encerrach , acesso , unixmsg,validado) VALUES ('{$nmag}','{$photoContact}','{$whatsapp}','false','false','aberto','0','sim','nao','{\"0\":\"sim\"}','{$micro}','true')";

                    $insert2 = $conn->consulta($sql_insert2);

                    $sql_bsel = "SELECT * FROM js_wh_clientes WHERE whatsapp='{$whatsapp}'";
                    $bsel = $conn->consulta($sql_bsel);
                    if ($bsel) {
                        if (mysqli_num_rows($bsel) > 0) {
                            $row5 = mysqli_fetch_array($bsel);
                            $idlc = $row5['idl'];
                        }
                    }

                    $msglp = $conn->limpar($msg);

                    $numero = $whatsapp;
                    $datai = $horario;
                    $idw = $idlc;
                    $unix = $micro;
                    $lpn = $msglp;

                    if ($midia == "image") {

                        $sql_upd3 = "INSERT INTO js_wh_mensagens (data_msg , autoria , atendente , midia , cliente , whatsapp , mimetype , iwidth , iheight , isize , referencia  , legenda , tipo , protocolo) VALUES ('{$datai}','cliente','{$atendente}','imagem','{$idw}','{$numero}','{$mimetype}','{$width}','{$height}','{$size}','{$referencia}','{$legenda}','humano','{$protocolo}')";
                        $upd3 = $conn->consulta($sql_upd3);

                        if ($legenda != "") {
                            $legenda = $conn->limpar($legenda);
                            $sql_upd3 = "INSERT INTO js_wh_mensagens (mensagem , data_msg , autoria , atendente , midia , cliente , whatsapp, tipo , protocolo) VALUES ('{$legenda}','{$datai}','cliente','{$atendente}','texto','{$idw}','{$numero}','humano','{$protocolo}')";
                            $upd3 = $conn->consulta($sql_upd3);
                        }

                        if ($upd3) {
                            $sql_upc = "UPDATE js_wh_clientes SET novo='true' , msgag='{$newag}' , unixmsg='{$unix}' WHERE whatsapp='{$numero}'";
                            $upc = $conn->consulta($sql_upc);
                        }
                    } elseif ($midia == "chat") {


                        $sql_upd3 = "INSERT INTO js_wh_mensagens (mensagem , data_msg , autoria , atendente , midia , cliente , whatsapp , tipo , protocolo) VALUES ('{$lpn}','{$datai}','cliente','{$atendente}','texto','{$idw}','{$numero}','humano','{$protocolo}')";
                        $upd3 = $conn->consulta($sql_upd3);

                        if ($upd3) {
                            $sql_upc = "UPDATE js_wh_clientes SET novo='true' , msgag='{$newag}' , unixmsg='{$unix}' WHERE whatsapp='{$numero}'";
                            $upc = $conn->consulta($sql_upc);
                        }

                        if ($bot_init == "true") {


                            $sql_bcb = "SELECT * FROM js_wh_autoresponders WHERE pergunta='{$lpn}' and ordem='1' LIMIT 0,1";

                            $bcb = $conn->consulta($sql_bcb);

                            if ($bcb) {

                                if (mysqli_num_rows($bcb) > 0) {

                                    $ttbcb = mysqli_num_rows($bcb);
                                    $row2 = mysqli_fetch_array($bcb);
                                    $resposta = $row2['resposta'];

                                    $sql_upd3 = "INSERT INTO js_wh_mensagens (mensagem , data_msg , autoria , atendente , midia , cliente , whatsapp , tipo , protocolo) VALUES ('{$resposta}','{$datai}','bot','{$atendente}','texto','{$idw}','{$numero}','humano','{$protocolo}')";
                                    $upd3 = $conn->consulta($sql_upd3);

                                    if ($upd3) {

                                        require_once(URL_ROOT . "/sistema/classes/classe.disparo.php");
                                        $env = new Disparo();
                                        $retorno = $env->disparar($numero, $resposta, "texto", "", "sim", "nao");

                                        $sql_upc = "UPDATE js_wh_clientes SET novo='true' , unixmsg='{$unix}' WHERE whatsapp='{$numero}'";
                                        $upc = $conn->consulta($sql_upc);
                                    }

                                } else {


                                    $sql_bcb = "SELECT * FROM js_wh_autoresponders WHERE ordem='4'";
                                    $bcb = $conn->consulta($sql_bcb);
                                    if ($bcb) {
                                        if (mysqli_num_rows($bcb) > 0) {
                                            $ttbcb = mysqli_num_rows($bcb);
                                            while ($row3 = mysqli_fetch_array($bcb)) {
                                                $pergunta[] = $row3['pergunta'];
                                                $resposta[] = $row3['resposta'];
                                            }

                                            function tirarAcentos($string)
                                            {
                                                return preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/"), explode(" ", "a A e E i I o O u U n N"), $string);
                                            }

                                            $cont = 0;
                                            while ($cont < $ttbcb) {
                                                $perguntalo = mb_strtolower($pergunta[$cont]);
                                                $perguntalo = tirarAcentos($perguntalo);
                                                $perguntalo = preg_replace('/( )+/', ' ', $perguntalo);
                                                $perguntalo = str_replace('Ç', 'c', $perguntalo);
                                                $perguntalo = str_replace('ç', 'c', $perguntalo);

                                                $pattern = '/' . $perguntalo . '/';//Padrão a ser encontrado na string $tags
                                                $strto = mb_strtolower($lpn);
                                                $strto = tirarAcentos($strto);
                                                $strto = preg_replace('/( )+/', ' ', $strto);
                                                $strto = str_replace('Ç', 'c', $strto);
                                                $strto = str_replace('ç', 'c', $strto);

                                                if (preg_match($pattern, $strto)) {
                                                    $sql_upd3 = "INSERT INTO js_wh_mensagens (mensagem , data_msg , autoria , atendente , midia , cliente , whatsapp , tipo , protocolo) VALUES ('{$resposta[$cont]}','{$datai}','bot','{$atendente}','texto','{$idw}','{$numero}','humano','{$protocolo}')";
                                                    $upd3 = $conn->consulta($sql_upd3);

                                                    if ($upd3) {
                                                        require_once(URL_ROOT . "/sistema/classes/classe.disparo.php");
                                                        $env = new Disparo();
                                                        $retorno = $env->disparar($numero, $resposta[$cont], "texto", "", "sim", "nao");

                                                        $sql_upc = "UPDATE js_wh_clientes SET novo='true' , unixmsg='{$unix}' WHERE whatsapp='{$numero}'";
                                                        $upc = $conn->consulta($sql_upc);

                                                        exit;
                                                        $cont += 10000;
                                                    }
                                                } else {
                                                    $cont++;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                    } elseif ($midia == "ptt") {

                        $sql_upd3 = "INSERT INTO js_wh_mensagens (mensagem , data_msg , autoria , atendente , midia , cliente , whatsapp ,  referencia , tipo , protocolo) VALUES ('{$lpn}','{$datai}','cliente','{$atendente}','ptt','{$idw}','{$numero}','{$referencia}','humano','{$protocolo}')";
                        $upd3 = $conn->consulta($sql_upd3);

                        if ($upd3) {
                            $sql_upc = "UPDATE js_wh_clientes SET novo='true' , msgag='{$newag}' , unixmsg='{$unix}' WHERE whatsapp='{$numero}'";
                            $upc = $conn->consulta($sql_upc);
                        }

                    } elseif ($midia == "document") {

                        $sql_upd3 = "INSERT INTO js_wh_mensagens (mensagem , data_msg , autoria , atendente , midia , cliente , whatsapp ,  referencia , mimetype , tipo , protocolo) VALUES ('{$lpn}','{$datai}','cliente','{$atendente}','pdf','{$idw}','{$numero}','{$referencia}','{$mimetype}','humano','{$protocolo}')";
                        $upd3 = $conn->consulta($sql_upd3);

                        if ($upd3) {
                            $sql_upc = "UPDATE js_wh_clientes SET novo='true' , msgag='{$newag}' , unixmsg='{$unix}' WHERE whatsapp='{$numero}'";
                            $upc = $conn->consulta($sql_upc);
                        }

                    } elseif ($midia == "vcard") {

                        $numer = explode("waid=", $lpn);
                        $numer2 = explode(":", $numer[1]);
                        $numer3 = $numer2[0];

                        $nomee = explode("FN:", $lpn);
                        $nomee2 = explode("TEL;", $nomee[1]);
                        $nome3 = $nomee2[0];

                        $msgsv = "O cliente enviou um contato! Whatsapp: " . $numer3 . " Nome do contato: " . $nome3;

                        $sql_upd3 = "INSERT INTO js_wh_mensagens (mensagem , data_msg , autoria , atendente , midia , cliente , whatsapp , tipo , protocolo) VALUES ('{$msgsv}','{$datai}','cliente','{$atendente}','texto','{$idw}','{$numero}','humano','{$protocolo}')";
                        $upd3 = $conn->consulta($sql_upd3);

                        if ($upd3) {
                            $sql_upc = "UPDATE js_wh_clientes SET novo='true' , msgag='{$newag}' , chat_unix='{$unix}' , unixmsg='{$unix}' WHERE whatsapp='{$numero}'";
                            $upc = $conn->consulta($sql_upc);
                        }

                    } elseif ($midia == "location") {

                        if ($tipo_loc == "tempo_real") {
                            $msgsv = "O cliente enviou a localização em tempo real:<br>Google Maps: <a href=\"https://www.google.com/maps/search/?api=1&query=" . $lat . "," . $lng . "\" target=\"_blank\">Visualisar Localização</a>";
                        } else {
                            $msgsv = "O cliente enviou uma localização fixa:<br>Google Maps: <a href=\"https://www.google.com/maps/search/?api=1&query=" . $lat . "," . $lng . "\" target=\"_blank\">Visualisar Localização</a>";
                        }

                        $sql_upd3 = "INSERT INTO js_wh_mensagens (mensagem , data_msg , autoria , atendente , midia , cliente , whatsapp , tipo , protocolo) VALUES ('{$msgsv}','{$datai}','cliente','{$atendente}','texto','{$idw}','{$numero}','humano','{$protocolo}')";
                        $upd3 = $conn->consulta($sql_upd3);
                        if ($upd3) {
                            $sql_upc = "UPDATE js_wh_clientes SET novo='true' , msgag='{$newag}' , chat_unix='{$unix}' , unixmsg='{$unix}' WHERE whatsapp='{$numero}'";
                            $upc = $conn->consulta($sql_upc);
                        }
                    } elseif ($midia == "video") {

                        $sql_upd3 = "INSERT INTO js_wh_mensagens (data_msg , autoria , atendente , midia , cliente , whatsapp , mimetype , iwidth , iheight , isize , referencia  , legenda , tipo , protocolo) VALUES ('{$datai}','cliente','{$atendente}','video','{$idw}','{$numero}','{$mimetype}','{$width}','{$height}','{$size}','{$referencia}','{$legenda}','humano','{$protocolo}')";
                        $upd3 = $conn->consulta($sql_upd3);

                        if ($legenda != "") {
                            $legenda = $conn->limpar($legenda);
                            $sql_upd3 = "INSERT INTO js_wh_mensagens (mensagem , data_msg , autoria , atendente , midia , cliente , whatsapp, tipo , protocolo) VALUES ('{$legenda}','{$datai}','cliente','{$atendente}','texto','{$idw}','{$numero}','humano','{$protocolo}')";
                            $upd3 = $conn->consulta($sql_upd3);
                        }

                        if ($upd3) {
                            $sql_upc = "UPDATE js_wh_clientes SET novo='true' , msgag='{$newag}' , unixmsg='{$unix}' WHERE whatsapp='{$numero}'";
                            $upc = $conn->consulta($sql_upc);
                        }

                    } else {

                        $msgdc = "Arquivo não suportado! Digite 0 para retornar!";

                        require_once(URL_ROOT . "/sistema/classes/classe.disparo.php");
                        $env = new Disparo();
                        $retorno = $env->disparar($numero, $msgdc, "texto", "", "sim", "nao");

                        $msgcl = "O cliente enviou um arquivo não suportado, pede para digitar a dúvida";
                        $sql_upd3 = "INSERT INTO js_wh_mensagens (mensagem , data_msg , autoria , atendente , midia , cliente , whatsapp , tipo , protocolo) VALUES ('{$msgcl}','{$datai}','cliente','{$atendente}','texto','{$idw}','{$numero}','humano','{$protocolo}')";
                        $upd3 = $conn->consulta($sql_upd3);

                        $sql_upd3 = "INSERT INTO js_wh_mensagens (mensagem , data_msg , autoria , atendente , midia , cliente , whatsapp , tipo , protocolo) VALUES ('{$msgdc}','{$datai}','bot','{$atendente}','texto','{$idw}','{$numero}','humano','{$protocolo}')";
                        $upd3 = $conn->consulta($sql_upd3);

                        if ($upd3) {
                            $sql_upc = "UPDATE js_wh_clientes SET novo='true' , msgag='{$newag}' , unixmsg='{$unix}' WHERE whatsapp='{$numero}'";
                            $upc = $conn->consulta($sql_upc);
                        }
                    }

                    $req = "/sistema/funcoes/chat_bot/padrao_contato.php";
                    require_once('' . $servidor . $req);
                    $exe = executar($whatsapp, $idchat, $msg, $nome_cliente);

                }
            }
        }
    }


    if (($tipoMsg == 'document') or ($tipoMsg == 'image') or ($tipo == "ptt") or ($tipo == "video") ) {

        $referencia = $referencia;
        $base64 = $ttee->base64;
        $base64 = explode(",",$base64);
        $base64 = $base64[1];

        $extensao = $midiaType;
        $dcogg = base64_decode($base64);

        $ext = explode("/", $extensao);
        $expref = explode(".", $referencia);

        $nmar = $expref[0];
        $exar = substr($ext[1], 0, 3);

        if (($extensao == "video/mp4") or ($extensao == "image/png") or ($extensao == "image/jpeg") or ($extensao == "audio/ogg; codecs=opus") or ($extensao == "application/pdf") or ($exar == "pla") or ($exar == "vnd") or ($exar == "msw")) {

            if ($extensao == "audio/ogg; codecs=opus") {

                $ins = file_put_contents($servidor . '/midias/imagens/uploads/' . $referencia, $dcogg);
                $cma = '/midias/imagens/uploads/' . $referencia;

            }elseif ($extensao == "application/pdf") {

                $ins = file_put_contents($servidor . '/midias/imagens/uploads/' . $referencia, $dcogg);
                $cma = '/midias/imagens/uploads/' . $referencia;

            } elseif ($exar == "pla") {

                $ins = file_put_contents($servidor . '/midias/imagens/uploads/' . $nmar . ".txt", $dcogg);
                $cma = '/midias/imagens/uploads/' . $nmar . ".txt";

            } elseif ($exar == "vnd") {

                if ($extensao == "application/vnd.ms-excel") {

                    $ins = file_put_contents($servidor . '/midias/imagens/uploads/' . $nmar . ".xls", $dcogg);
                    $cma = '/midias/imagens/uploads/' . $nmar . ".xls";

                } elseif ($extensao == "application/vnd.openxmlformats-officedocument.wordprocessingml.document") {

                    $ins = file_put_contents($servidor . '/midias/imagens/uploads/' . $nmar . ".docx", $dcogg);
                    $cma = '/midias/imagens/uploads/' . $nmar . ".docx";

                } else {

                    $ins = file_put_contents($servidor . '/midias/imagens/uploads/' . $nmar . ".xlsx", $dcogg);
                    $cma = '/midias/imagens/uploads/' . $nmar . ".xlsx";
                }

            } elseif ($exar == "msw") {

                $ins = file_put_contents($servidor . '/midias/imagens/uploads/' . $nmar . ".doc", $dcogg);
                $cma = '/midias/imagens/uploads/' . $nmar . ".doc";

            } elseif ($extensao == "video/mp4") {

                $ins = file_put_contents($servidor . '/midias/imagens/uploads/' . $nmar . ".mp4", $dcogg);
                $cma = '/midias/imagens/uploads/' . $nmar . ".mp4";

            } else {

                $ins = file_put_contents($servidor . '/midias/imagens/uploads/' . $referencia, $dcogg);
                $cma = '/midias/imagens/uploads/' . $referencia;
            }

            $inslo64 = "INSERT INTO js_wh_arquivos (referencia , arquivo) VALUES ('{$referencia}','{$cma}')";
            $inse64 = $conn->consulta($inslo64);

        } else {

            $inslo64 = "INSERT INTO js_wh_arquivos (referencia , arquivo) VALUES ('{$referencia}','{$base64}')";
            $inse64 = $conn->consulta($inslo64);
        }

        $sql_refe = "SELECT * FROM js_wh_mensagens WHERE referencia='{$referencia}' LIMIT 0,1";
        $refe = $conn->consulta($sql_refe);
        if ($refe) {
            if (mysqli_num_rows($refe) > 0) {
                $row3 = mysqli_fetch_array($refe);
                $cliente = $row3['cliente'];
                $sql_clie = "SELECT * FROM js_wh_clientes WHERE idl='{$cliente}'";
                $clie = $conn->consulta($sql_clie);
                if ($clie) {
                    if (mysqli_num_rows($clie) > 0) {
                        $row4 = mysqli_fetch_array($clie);
                        $msgag = $row4['msgag'];
                        $newag = $msgag + 1;

                        $sql_upc = "UPDATE js_wh_clientes SET novo='true' , msgag='{$newag}' WHERE idl='{$cliente}'";
                        $upc = $conn->consulta($sql_upc);

                        if ($upc) {

                        }
                    }
                }
            }
        }
    } else {
        /**
         * Não identificado
         */
    }

}
