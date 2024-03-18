<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Contract;

interface UpdateTitle
{
    public function updateHook(string &$title): void;
}
