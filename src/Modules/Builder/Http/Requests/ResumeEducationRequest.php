<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResumeEducationRequest extends FormRequest
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
			'education' 				=> ['required', 'array', 'min:1'],
            'education.*.institution' 	=> 'required|string',
			'education.*.area'			=> 'required|string',
			'education.*.studyType'		=> 'required|string',
			'education.*.startDate' 	=> 'nullable|date',
			'education.*.endDate' 		=> 'nullable|date',
			'education.*.score'			=> 'nullable|string',
        ];
    }

	public function messages()
	{
		return [
			'education.*.institution' => 'The Institution field is required.',
			'education.*.area'		=> 'The Field of Study field is required.',
			'education.*.studyType'	=> 'The Degree Type field is required.',
		];
	}
}
