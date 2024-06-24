<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Contract;

interface UpdateFoo
{
    public function updateFoo(string &$title): void;
}
