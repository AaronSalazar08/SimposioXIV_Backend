<?php

namespace App\Http\Requests;

use App\Enums\EstadoInscripcion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexInscripcionRequest extends FormRequest
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
            'estado' => ['sometimes', 'string', Rule::enum(EstadoInscripcion::class)],
        ];
    }
}
