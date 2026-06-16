<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'identifier.required' => 'Debes ingresar tu carnet o correo electrónico.',
            'password.required' => 'Debes ingresar tu contraseña.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $identifier = $this->string('identifier')->toString();

            if (str_contains($identifier, '@')) {
                if (! preg_match('/@ucr\.ac\.cr$/', $identifier)) {
                    $validator->errors()->add(
                        'identifier',
                        'Solo se permiten correos institucionales (@ucr.ac.cr).'
                    );
                }

                return;
            }

            if (! preg_match('/^[a-zA-Z0-9]{6}$/', $identifier)) {
                $validator->errors()->add(
                    'identifier',
                    'El carnet debe tener exactamente 6 caracteres alfanuméricos.'
                );
            }
        });
    }

    public function isEmailIdentifier(): bool
    {
        return str_contains($this->string('identifier')->toString(), '@');
    }

    public function identifier(): string
    {
        return $this->string('identifier')->toString();
    }
}
