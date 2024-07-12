<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Concern;

trait MethodSignatureDoesNotMatchType
{
    abstract protected function updateFoo(bool &$foo): void;
}
