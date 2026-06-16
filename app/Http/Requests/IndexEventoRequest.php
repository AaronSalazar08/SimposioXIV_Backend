<?php

namespace App\Http\Requests;

use App\Enums\TipoEvento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexEventoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'dia' => ['sometimes', 'integer', 'in:1,2,3'],
            'tipo' => ['sometimes', 'string', Rule::enum(TipoEvento::class)],
            'area_id' => ['sometimes', 'integer', 'exists:areas,id'],
            'solo_disponibles' => ['sometimes', 'boolean'],
        ];
    }
}
