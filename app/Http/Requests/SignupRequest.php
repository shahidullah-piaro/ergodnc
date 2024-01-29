<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class SignupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'name' => ['required', 'string'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email'), // Assuming $this->user holds the existing user
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->symbols()
                    ->numbers()
            ],
            'fathers_name' => ['nullable','string','max:255'],
            'mothers_name' => ['nullable','string','max:255'],
            'nid' => ['nullable','string','max:10'], // Assuming 10-digit NID
            'file' => ['nullable','string'],
            'image' => ['nullable','string'],
            'audio' => ['nullable','string'],
            'video' => ['nullable','string'],
            'start_date' => ['nullable','date'],
            'end_date' => ['nullable','date','after_or_equal:start_date']
        ];
    }
}
