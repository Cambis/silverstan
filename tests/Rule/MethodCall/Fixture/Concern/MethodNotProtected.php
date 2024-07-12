<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Concern;

trait MethodNotProtected
{
    abstract public function updateFoo(string &$foo): void;
}
