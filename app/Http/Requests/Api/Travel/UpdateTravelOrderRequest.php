<?php

namespace App\Http\Requests\Api\Travel;

use App\Support\Traits\HandlesFailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTravelOrderRequest extends FormRequest
{
    use HandlesFailedValidation;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'status' => ['required', 'string', 'in:approved,cancelled'],
        ];
    }
}
