<?php


namespace App\Traits;


trait PhoneNumberTrait
{
    protected function clearPhoneNumber($value, $nonoDigito = true)
    {
        // Remove os caracteres de idenbtificação
        $data = str_replace('@c.us', '', $value);
        $total = strlen($data);

        // Verifica se está pedindo reconhecimento do nono dígito
        if(!$nonoDigito || $total <= 12) {
            return $data;
        }

        if(str_starts_with($data, '55')) {
            $inicio = substr($data, 0, 4);
            $final = substr($data, 5, $total);
            return "{$inicio}{$final}";
        }
    }
}
