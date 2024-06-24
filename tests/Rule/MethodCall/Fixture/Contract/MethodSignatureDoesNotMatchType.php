<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Contract;

interface MethodSignatureDoesNotMatchType
{
    public function updateFoo(bool &$foo): void;
}
