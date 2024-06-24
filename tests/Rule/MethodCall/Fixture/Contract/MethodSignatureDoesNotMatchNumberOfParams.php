<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Contract;

interface MethodSignatureDoesNotMatchNumberOfParams
{
    public function updateFoo(string &$foo, bool &$bar): void;
}
