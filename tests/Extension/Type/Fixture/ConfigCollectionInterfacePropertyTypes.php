<?php

namespace Cambis\Silverstan\Tests\Extension\Type\Fixture;

use Cambis\Silverstan\Tests\Extension\Type\Source\ConfigurableClass;
use SilverStripe\Core\Config\Config;
use function PHPStan\Testing\assertType;

assertType('array', Config::inst()->get(ConfigurableClass::class, 'native_array'));
assertType('array', Config::inst()->get(ConfigurableClass::class, 'phpdoc_array'));
assertType('array<string>', Config::inst()->get(ConfigurableClass::class, 'iterable_typed_array'));

assertType('bool', Config::inst()->get(ConfigurableClass::class, 'native_boolean'));
assertType('bool', Config::inst()->get(ConfigurableClass::class, 'phpdoc_boolean'));

assertType('int', Config::inst()->get(ConfigurableClass::class, 'native_integer'));
assertType('int', Config::inst()->get(ConfigurableClass::class, 'phpdoc_integer'));

assertType('string', Config::inst()->get(ConfigurableClass::class, 'native_string'));
assertType('string', Config::inst()->get(ConfigurableClass::class, 'phpdoc_string'));

assertType('mixed', Config::inst()->get(ConfigurableClass::class, 'mixed'));
