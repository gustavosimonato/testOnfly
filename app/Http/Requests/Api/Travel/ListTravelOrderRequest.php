<?php

namespace App\Http\Requests\Api\Travel;

use App\Support\Traits\HandlesFailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class ListTravelOrderRequest extends FormRequest
{
    use HandlesFailedValidation;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'status' => ['nullable', 'string', 'in:requested,approved,cancelled'],
            'destination' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date-format:Y-m-d'],
            'end_date' => ['nullable', 'date-format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }
}
