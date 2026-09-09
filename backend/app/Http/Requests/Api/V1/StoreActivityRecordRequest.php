<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreActivityRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(['class', 'agenda', 'archive', 'group', 'explore', 'profile', 'tenant', 'action'])],
            'label' => ['required', 'string', 'max:160'],
            'metadata' => ['sometimes', 'array'],
        ];
    }
}
