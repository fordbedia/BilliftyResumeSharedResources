<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;

class ResumeSkillsRequest extends FormRequest
{
	use DecodesJsonFormData;

	 public function authorize(): bool
    {
        return true;
    }

	protected function prepareForValidation(): void
    {
        $this->decodeJsonFormDataKeys();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           	'skills.*.level'			=> 'nullable|string',
			'skills.*.resume_id'		=> 'nullable|integer',
			'skills.*.name'				=> 'nullable|string',
			'skills.*.sort_order'		=> 'nullable|integer',
        ];
    }
}