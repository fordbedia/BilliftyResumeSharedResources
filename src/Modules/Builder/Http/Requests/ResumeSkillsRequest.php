<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResumeSkillsRequest extends FormRequest
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
			'skills' => ['required', 'array', 'min:1'],
			'skills.*.id' => 'nullable|integer',
			'skills.*.name' => 'required|string',
			'skills.*.level' => 'nullable|string',
        ];
    }

	public function messages()
	{
		return [
			'skills.*.name' => 'Skill Name is required.',
		];
	}
}
