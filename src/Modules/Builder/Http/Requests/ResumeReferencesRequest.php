<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResumeReferencesRequest extends FormRequest
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
			'references' 				=> ['required', 'array', 'min:1'],
            'references.*.name'			=> 'required|string',
			'references.*.reference'	=> 'required|string'
        ];
    }

	public function messages()
	{
		return [
			'references.*.name' 		=> 'The Name field is required.',
			'references.*.reference' 	=> 'The Reference field is required.',
		];
	}
}
