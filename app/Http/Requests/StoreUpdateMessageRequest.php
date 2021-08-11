<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'message_id' => 'nullable|exists:messages,id',
            'reference_id' => 'required|numeric',
            'reference_phone' => 'nullable|exists:phones,phone_number',
            'description' => 'required|min:5',
            'body' => 'required|min:5',
            'tags' => 'nullable',
            'type' => 'required|in:primeiro-contato,mensagem,resultados,nao-encontrato,finalizacao',
        ];
    }
}
