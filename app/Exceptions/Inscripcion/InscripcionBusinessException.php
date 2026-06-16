<?php

namespace App\Exceptions\Inscripcion;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class InscripcionBusinessException extends Exception
{
    abstract public function field(): string;

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => [
                $this->field() => [$this->getMessage()],
            ],
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }
}
