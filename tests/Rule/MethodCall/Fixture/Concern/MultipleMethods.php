<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Concern;

trait MultipleMethods
{
    abstract protected function updateFoo(): void;

    abstract protected function updateBar(): void;
}
