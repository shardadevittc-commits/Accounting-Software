<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:191'],
            'username' => ['nullable', 'string', 'max:191', Rule::unique('users', 'username')->ignore($userId)],
            'email' => ['required', 'string', 'email', 'max:191', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'role_id' => ['required', 'exists:roles,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ];
    }
}
