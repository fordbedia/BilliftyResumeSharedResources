<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent;

interface Transactional
{
	/**
     * Run the callback atomically.
     * @template T
     * @param callable(): T $fn
     * @return T
     */
    public function run(callable $fn);
}