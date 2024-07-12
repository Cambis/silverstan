<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Concern;

trait UpdateFoo
{
    abstract protected function updateFoo(string &$title): void;
}
