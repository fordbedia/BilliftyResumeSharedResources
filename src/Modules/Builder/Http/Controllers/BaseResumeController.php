<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

abstract class BaseResumeController extends Controller
{
	abstract protected function requestClass(): string;

    protected function validated(Request $request): array
    {
        $class = $this->requestClass();

        /** @var FormRequest $form */
        $form = app($class);

        // Feed incoming data to the request instance
        $form->merge($request->all());

        // Optional: if you want authorize() to apply
        if (method_exists($form, 'authorize') && $form->authorize() === false) {
            abort(403, 'This action is unauthorized.');
        }

       // apply custom messages + custom attribute names
        return validator(
            $form->all(),
            $form->rules(),
            method_exists($form, 'messages') ? $form->messages() : [],
            method_exists($form, 'attributes') ? $form->attributes() : []
        )->validate();
    }
}