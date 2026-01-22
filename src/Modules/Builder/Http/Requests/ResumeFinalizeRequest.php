<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResumeFinalizeRequest extends FormRequest
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
        return array_merge(
            (new ResumeBasicRequest())->rules(),
            (new ResumeEducationRequest())->rules(),
            (new ResumeWorkRequest())->rules(),
            (new ResumeSkillsRequest())->rules(),
            (new ResumeReferencesRequest())->rules(),
			(new ResumeTemplateRequest())->rules(),
        );
    }

	public function messages(): array
	{
		return array_merge(
			(new ResumeBasicRequest())->messages(),
			(new ResumeEducationRequest())->messages(),
			(new ResumeWorkRequest())->messages(),
			(new ResumeSkillsRequest())->messages(),
			(new ResumeReferencesRequest())->messages(),
			(new ResumeTemplateRequest())->messages(),
		);
	}

	public function attributes(): array
	{
		return array_merge(
			method_exists(new ResumeBasicRequest(), 'attributes') ? (new ResumeBasicRequest())->attributes() : [],
			method_exists(new ResumeEducationRequest(), 'attributes') ? (new ResumeEducationRequest())->attributes() : [],
			method_exists(new ResumeWorkRequest(), 'attributes') ? (new ResumeWorkRequest())->attributes() : [],
			method_exists(new ResumeSkillsRequest(), 'attributes') ? (new ResumeSkillsRequest())->attributes() : [],
			method_exists(new ResumeReferencesRequest(), 'attributes') ? (new ResumeReferencesRequest())->attributes() : [],
		);
	}
}
