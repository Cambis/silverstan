<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Concern;

trait MethodNonVoidReturnType
{
    abstract protected function updateFoo(string &$foo): string;
}
