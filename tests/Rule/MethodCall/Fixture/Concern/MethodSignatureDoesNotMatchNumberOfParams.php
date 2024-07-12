<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Concern;

trait MethodSignatureDoesNotMatchNumberOfParams
{
    abstract protected function updateFoo(string &$foo, bool &$bar): void;
}
