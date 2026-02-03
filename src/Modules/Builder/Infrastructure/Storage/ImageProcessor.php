<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Storage;

use Illuminate\Database\Eloquent\Model;

interface ImageProcessor
{
    public function store();

    public function deleteLastFile(string $column, Model $model);
}