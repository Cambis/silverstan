<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Concern;

trait MethodSignatureNotPassedByReference
{
    abstract protected function updateFoo(string $foo): void;
}
