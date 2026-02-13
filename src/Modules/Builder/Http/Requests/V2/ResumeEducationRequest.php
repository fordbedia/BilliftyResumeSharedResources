<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;

class ResumeEducationRequest extends FormRequest
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
           	'education.*.resume_id' 	=> 'nullable|string',
			'education.*.institution' 	=> 'nullable|string',
			'education.*.area' 			=> 'nullable|string',
			'education.*.studyType' 	=> 'nullable|string',
			'education.*.startDate' 	=> 'nullable|string',
			'education.*.endDate' 		=> 'nullable|string',
			'education.*.sort_order' 	=> 'nullable|string',
        ];
    }
}