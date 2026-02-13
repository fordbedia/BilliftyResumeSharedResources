<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests\V2;

trait DecodesJsonFormData
{
	/**
     * Override this in each FormRequest if you want custom keys.
     *
     * Example:
     *  protected function jsonFormDataKeys(): array
     *  {
     *      return ['basics', 'create'];
     *  }
     */
    protected function jsonFormDataKeys(): array
    {
        return ['create', 'basics', 'skills', 'work', 'education', 'references', 'finalize'];
    }

    /**
     * Decode JSON strings sent inside multipart/form-data (FormData) and merge them
     * back into the request input so validation rules like basics.name work.
     */
    protected function decodeJsonFormDataKeys(): void
    {
        foreach ($this->jsonFormDataKeys() as $key) {
            $value = $this->input($key);

            if (! is_string($value) || $value === '') {
                continue;
            }

            $decoded = json_decode($value, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                continue;
            }

            $this->merge([$key => $decoded]);
        }
    }
}