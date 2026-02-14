<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;

class ResumeBasicRequest extends FormRequest
{
	use DecodesJsonFormData;

    /**
     * Determine if the user is authorized to make this request.
     */
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
            'basics.name' => 'nullable|string',
			'basics.label' => 'nullable|string',
			'basics.url' => 'nullable|string',
			'basics_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:1120'],
			'basics.image_disk' => 'nullable|string',
			'basics.email' => 'nullable|email',
			'basics.phone' => ['nullable', 'string', 'regex:/^\+?[0-9\s().-]{7,20}$/'],
			'basics.address' => 'nullable|string',
			'basics.postalCode' => 'nullable|string',
			'basics.city' => 'nullable|string',
			'basics.countryCode' => 'nullable|string',
			'basics.region' => 'nullable|string',
			'basics.summary' => 'nullable|string',
			'basics.languages.is_active' => 'nullable|boolean',
			'basics.languages.language' => 'nullable|array',
			'basics.certifications.is_active' => 'nullable|boolean',
			'basics.certifications.body' => 'nullable|string',
			'basics.accomplishments.is_active' => 'nullable|boolean',
			'basics.accomplishments.body' => 'nullable|string',
			'basics.websites.is_active' => 'nullable|boolean',
			'basics.websites.url' => 'nullable|array',
			'basics.affiliations.is_active' => 'nullable|boolean',
			'basics.affiliations.body' => 'nullable|string',
			'basics.interests.is_active' => 'nullable|boolean',
			'basics.interests.body' => 'nullable|string',
			'basics.volunteering.is_active' => 'nullable|boolean',
			'basics.volunteering.body' => 'nullable|string',
			'basics.projects.is_active' => 'nullable|boolean',
			'basics.projects.body' => 'nullable|string',
        ];
    }

	public function attributes()
	{
		return [
			'basics.email' => 'Email',
		];
	}
}