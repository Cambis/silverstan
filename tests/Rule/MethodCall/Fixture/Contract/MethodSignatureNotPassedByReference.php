<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Contract;

interface MethodSignatureNotPassedByReference
{
    public function updateFoo(string $foo): void;
}
