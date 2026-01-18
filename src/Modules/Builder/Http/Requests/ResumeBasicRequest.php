<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResumeBasicRequest extends FormRequest
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
            'name' 					=> 'required|string',
			'email' 				=> 'required|email',
			'label' 				=> 'required|string',
			'phone' 				=> 'nullable|phone',
			'location' 				=> ['nullable', 'array'],
			'location.address' 		=> 'nullable|string',
			'location.postalCode' 	=> 'nullable|string',
			'location.city' 		=> 'nullable|string',
			'location.countryCode' 	=> 'nullable|string',
			'location.region' 		=> 'nullable|string',
			'summary' 				=> 'nullable|string',
			'profiles'				=> 'nullable|array',
        ];
    }

	public function messages()
	{
		return [
			'label' => 'The Professional Title field is required.',
		];
	}
}
