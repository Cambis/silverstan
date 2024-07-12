<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Concern;

trait MethodNotAbstract
{
    protected function updateFoo(string &$foo): void
    {
    }
}
