<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInscripcionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'evento_id' => ['required', 'integer', 'exists:eventos,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'evento_id.required' => 'Debes indicar el evento al que deseas inscribirte.',
            'evento_id.integer' => 'El identificador del evento no es válido.',
            'evento_id.exists' => 'El evento seleccionado no existe.',
        ];
    }
}
