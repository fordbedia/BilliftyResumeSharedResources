<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
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
            'create.name'				=> 'required|string',
			'create.color_scheme_id' 	=> 'required|integer',
			'create.template' 			=> 'required|integer'
        ];
    }

	public function messages()
	{
		return [
			'create.name' 				=> 'Please add a name for your resume.',
			'create.color_scheme_id' 	=> 'Please select a color scheme for your resume.',
			'create.template' 			=> 'Please select a template for your resume.',
		];
	}
}
