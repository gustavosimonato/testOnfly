<?php

namespace App\Http\Requests\Api\Travel;

use App\Support\Traits\HandlesFailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class StoreTravelOrderRequest extends FormRequest
{
    use HandlesFailedValidation;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'destination' => ['required', 'string', 'max:255'],
            'departure_date' => [
                'required',
                'date-format:Y-m-d',
                'after_or_equal:today',
            ],
            'return_date' => [
                'required',
                'date-format:Y-m-d',
                'after:departure_date',
            ],
        ];
    }
}
