<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Concern;

abstract class NotATrait
{
    abstract protected function updateFoo(string &$title): void;
}
