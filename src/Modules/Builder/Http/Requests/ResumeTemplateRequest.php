<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResumeTemplateRequest extends FormRequest
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
			'name' => 'required|string',
            'template' => 'required|integer',
			'color_scheme_id' => 'required|integer',
        ];
    }

	public function messages(): array
	{
		return [
			'name' => 'Please add a name for your resume.',
			'template' => 'You forgot to select a template for your resume.',
			'color_scheme_id' => 'You forgot to select a color scheme for your resume.',
		];
	}
}
