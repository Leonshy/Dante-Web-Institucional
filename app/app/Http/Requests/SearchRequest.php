<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida el término del buscador interno (docs/05-backend-modelo-datos.md §8).
 * Longitud mínima para evitar consultas absurdas (`LIKE %%` contra toda la tabla).
 */
class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return [
            'q' => 'término de búsqueda',
        ];
    }

    public function term(): string
    {
        return (string) $this->validated('q');
    }
}
