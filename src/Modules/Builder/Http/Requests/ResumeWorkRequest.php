<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResumeWorkRequest extends FormRequest
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
			'work' 					=> ['required', 'array', 'min:1'],
			'work.*.id' 			=> 'nullable|integer',
            'work.*.name' 			=> 'required|string',
			'work.*.position' 		=> 'required|string',
			'work.*.startDate' 		=> 'required|date',
			'work.*.endDate' 		=> 'nullable|date',
			'work.*.summary' 		=> 'nullable|string',
			'work.*.highlights' 	=> 'nullable|array',
        ];
    }

	public function messages()
	{
		return [
			'work.*.name' 			=> 'The Company Name field is required.',
			'work.*.position' 		=> 'The Position field is required.',
			'work.*.startDate'		=> 'The Start Date field is required.',
		];
	}
}
