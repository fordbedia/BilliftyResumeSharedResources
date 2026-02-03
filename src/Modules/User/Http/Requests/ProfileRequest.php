<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

	protected function prepareForValidation(): void
	{
		// Decode JSON "profile" into request input
		if ($this->filled('profile') && is_string($this->input('profile'))) {
			$decoded = json_decode($this->input('profile'), true);

			if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
				// prevent JSON from ever injecting an "avatar" root key
				unset($decoded['avatar']);
				if (isset($decoded['info']) && is_array($decoded['info'])) {
					unset($decoded['info']['avatarFile']); // frontend-only
				}

				$this->merge($decoded);
			}
		}

		/**
		 * CRITICAL FIX:
		 * If "avatar" is present but not an uploaded file, remove it
		 * so file/image rules won't run on a string.
		 */
		if ($this->has('avatar') && !$this->hasFile('avatar')) {
			$this->request->remove('avatar'); // or $this->offsetUnset('avatar');
		}
	}


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
	{
		return [
			'id' => 'required|exists:users,id',
			'name' => 'required|string',

			// validate only if a file is actually uploaded
			'avatar' => 'sometimes|file|image|max:2048',

			'info' => 'nullable|array',
			'info.avatar' => 'nullable|string', // stored path/url
			'info.phone' => 'nullable|string',
			'info.location' => 'nullable|string',
			'info.website' => 'nullable|string',
			'info.address_1' => 'nullable|string',
			'info.address_2' => 'nullable|string',
			'info.bio' => 'nullable|string',
		];
	}

}
