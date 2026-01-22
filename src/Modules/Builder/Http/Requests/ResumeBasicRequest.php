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
			'basics' 						=> ['required', 'array'],
            'basics.name' 					=> 'required|string',
			'basics.email' 					=> 'required|email',
			'basics.label' 					=> 'required|string',
			'basics.phone' 					=> 'nullable|phone',
			'basics.location' 				=> ['nullable', 'array'],
			'basics.location.address' 		=> 'nullable|string',
			'basics.location.postalCode' 	=> 'nullable|string',
			'basics.location.city' 			=> 'nullable|string',
			'basics.location.countryCode' 	=> 'nullable|string',
			'basics.location.region' 		=> 'nullable|string',
			'basics.summary' 				=> 'nullable|string',
			'basics.profiles'				=> 'nullable|array',
        ];
    }

	public function messages()
	{
		return [
			'basics.label' => 'The Professional Title field is required.',
			'basics.email' => 'The Email field is required.',
			'basics.name' => 'The Company Name field is required.',
		];
	}
}
