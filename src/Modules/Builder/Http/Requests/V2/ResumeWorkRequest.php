<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;

class ResumeWorkRequest extends FormRequest
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
            'work.*.name'  		=> 'nullable|string',
			'work.*.position'		=> 'nullable|string',
			'work.*.startDate'		=> 'nullable|date',
			'work.*.endDate'		=> 'nullable|date',
			'work.*.summary'		=> 'nullable|string',
        ];
    }
}