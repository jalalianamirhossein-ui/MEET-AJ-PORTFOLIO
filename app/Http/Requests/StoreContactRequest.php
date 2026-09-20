<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->replace($this->except(['status', 'internal_notes', 'service_id', 'id']));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:50'],
            'email' => ['required', 'email', 'max:100'],
            'subject' => ['required', 'string', 'min:5', 'max:100'],
            'message' => ['required', 'string', 'min:10', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:40'],
            'website' => ['nullable', 'string', 'max:255'],
            'service' => ['nullable', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Invalid name (2-50 characters required)',
            'name.min' => 'Invalid name (2-50 characters required)',
            'name.max' => 'Invalid name (2-50 characters required)',
            'email.required' => 'Invalid email address',
            'email.email' => 'Invalid email address',
            'email.max' => 'Invalid email address',
            'subject.required' => 'Invalid subject (5-100 characters required)',
            'subject.min' => 'Invalid subject (5-100 characters required)',
            'subject.max' => 'Invalid subject (5-100 characters required)',
            'message.required' => 'Invalid message (10-1000 characters required)',
            'message.min' => 'Invalid message (10-1000 characters required)',
            'message.max' => 'Invalid message (10-1000 characters required)',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response($validator->errors()->first(), 400)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Cache-Control', 'no-store'));
    }
}
