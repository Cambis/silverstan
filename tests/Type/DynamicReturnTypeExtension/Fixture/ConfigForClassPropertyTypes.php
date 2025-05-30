<?php

namespace Cambis\Silverstan\Tests\Type\DynamicReturnTypeExtension\Fixture;

use Cambis\Silverstan\Tests\Type\DynamicReturnTypeExtension\Source\ConfigurableClass;
use function PHPStan\Testing\assertType;

assertType('array', ConfigurableClass::config()->get('native_array'));
assertType('array', ConfigurableClass::config()->get('phpdoc_array'));
assertType('array<string>', ConfigurableClass::config()->get('iterable_typed_array'));

assertType('bool', ConfigurableClass::config()->get('native_boolean'));
assertType('bool', ConfigurableClass::config()->get('phpdoc_boolean'));

assertType('int', ConfigurableClass::config()->get('native_integer'));
assertType('int', ConfigurableClass::config()->get('phpdoc_integer'));

assertType('string', ConfigurableClass::config()->get('native_string'));
assertType('string', ConfigurableClass::config()->get('phpdoc_string'));

assertType('mixed', ConfigurableClass::config()->get('mixed'));

new class extends ConfigurableClass {
    public function doSomething(): void
    {
        assertType('array<string>', self::config()->get('iterable_typed_array'));
        assertType('array<string>', $this->config()->get('iterable_typed_array'));
    }
};
