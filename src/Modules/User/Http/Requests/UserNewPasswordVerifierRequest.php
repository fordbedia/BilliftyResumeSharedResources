<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserNewPasswordVerifierRequest extends FormRequest
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
			'currentPassword' 	=> 'required|string',
			'newPassword' 		=> 'required|string',
			'confirmPassword'	=> 'required|string|confirmed:newPassword'
        ];
    }

	public function messages()
	{
		return [
			'confirmPassword' => 'Confirm password does not match with your new password.'
		];
	}
}
