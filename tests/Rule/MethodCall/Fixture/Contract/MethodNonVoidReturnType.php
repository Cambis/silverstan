<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Contract;

interface MethodNonVoidReturnType
{
    public function updateFoo(string &$foo): string;
}
