# 11 Rules Overview

## DisallowNewInstanceOnInjectableRule

Disallow instantiating a `\SilverStripe\Core\Injectable` class using `new`. Use `create()` instead.

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\New_\DisallowNewInstanceOnInjectableRule`](../src/Rule/New_/DisallowNewInstanceOnInjectableRule.php)

```yaml
parameters:
    silverstanRules:
        disallowNewInstanceOnInjectable:
            enabled: true
```

↓

```php
final class Foo
{
    use \SilverStripe\Core\Injectable;
}

$foo = new Foo();
```

:x:

<br>

```php
final class Foo
{
    use \SilverStripe\Core\Injectable;
}

$foo = Foo::create();
```

:+1:

<br>

## DisallowNonInterfacePublicMethodsOnExtensionRule

Disallow non interface public methods on `\SilverStripe\Core\Extension`, an interface should be used to define public methods added to an owner class.

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\ClassMethod\DisallowNonInterfacePublicMethodsOnExtensionRule`](../src/Rule/ClassMethod/DisallowNonInterfacePublicMethodsOnExtensionRule.php)

```yaml
parameters:
    silverstanRules:
        disallowNonInterfacePublicMethodsOnExtension:
            enabled: true
```

↓

```php
final class FooExtension extends \SilverStripe\Core\Extension
{
    public function foo(): string
    {
        return 'foo';
    }
}

/**
 * @mixin FooExtension
 */
final class Foo extends \SilverStripe\ORM\DataObject
{
    public function bar(): void
    {
        $this->foo(); // Visible
        $this->getOwner(); // Visible
    }
}
```

:x:

<br>

```php
interface FooExtensionInterface
{
    public function foo(): string;
}

final class FooExtension extends \SilverStripe\Core\Extension implements FooExtensionInterface
{
    public function foo(): string
    {
        return 'foo';
    }
}

/**
 * @mixin FooExtensionInterface
 */
final class Foo extends \SilverStripe\ORM\DataObject
{
    public function bar(): void
    {
        $this->foo(); // Visible
        $this->getOwner(); // Not visible
    }
}
```

:+1:

<br>

## DisallowOverridingOfConfigurationPropertyTypeRule

Disallow overriding types of configuration properties.

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\ClassPropertyNode\DisallowOverridingOfConfigurationPropertyTypeRule`](../src/Rule/ClassPropertyNode/DisallowOverridingOfConfigurationPropertyTypeRule.php)

```yaml
parameters:
    silverstanRules:
        disallowOverridingOfConfigurationPropertyType:
            enabled: true
```

↓

```php
class Foo extends \SilverStripe\ORM\DataObject
{
    private static string $foo = 'foo';
}

final class Bar extends Foo
{
    private static string|bool $foo = false;
}
```

:x:

<br>

```php
class Foo extends \SilverStripe\ORM\DataObject
{
    private static string $foo = 'foo';
}

final class Bar extends Foo
{
    private static string $foo = 'bar';
}
```

:+1:

<br>

## DisallowStaticPropertyFetchOnConfigurationPropertyRule

Disallow static property fetch on configuration properties.

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\StaticPropertyFetch\DisallowStaticPropertyFetchOnConfigurationPropertyRule`](../src/Rule/StaticPropertyFetch/DisallowStaticPropertyFetchOnConfigurationPropertyRule.php)

```yaml
parameters:
    silverstanRules:
        disallowStaticPropertyFetchOnConfigurationProperty:
            enabled: true
```

↓

```php
final class Foo extends \SilverStripe\ORM\DataObject
{
    private static string $singular_name = 'Foo';

    public function getType(): string
    {
        return self::$singular_name;
    }
}
```

:x:

<br>

```php
final class Foo extends \SilverStripe\ORM\DataObject
{
    private static string $singular_name = 'Foo';

    public function getType(): string
    {
        return $this->config()->get('singular_name');
    }
}
```

:+1:

<br>

## DisallowSuperglobalsRule

Disallow the use of superglobals ($_GET, $_REQUEST etc.).

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\Variable\DisallowSuperglobalsRule`](../src/Rule/Variable/DisallowSuperglobalsRule.php)

```yaml
parameters:
    silverstanRules:
        disallowSuperglobals:
            enabled: true
            disallowedSuperglobals:
                - _GET
                - _POST
                - _FILES
                - _COOKIE
                - _SESSION
                - _REQUEST
                - _ENV
                - GLOBALS
```

↓

```php
final class CustomMiddleware implements \SilverStripe\Control\Middleware\HTTPMiddleware
{
    /**
     * @return void
     */
    public function process(\SilverStripe\Control\HTTPRequest $request, callable $delegate)
    {
        $foo =  $_GET['foo'];
    }
}
```

:x:

<br>

```php
final class CustomMiddleware implements \SilverStripe\Control\Middleware\HTTPMiddleware
{
    /**
     * @return void
     */
    public function process(\SilverStripe\Control\HTTPRequest $request, callable $delegate)
    {
        $foo =  $request->getVar('foo');
    }
}
```

:+1:

<br>

## DisallowUnsafeAccessOfMagicDataObjectRule

Use `instanceof` and `exists()` first before accessing any magic `\SilverStripe\ORM\DataObject` methods or properties as the object may not be present in the database. Enabling this rule will change the return type of `$has_one` and `$belongs_to` relationships from `\SilverStripe\ORM\DataObject` to `\SilverStripe\ORM\DataObject|null` in order to encourage the use of the `instanceof` check.

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\CollectedDataNode\DisallowUnsafeAccessOfMagicDataObjectRule`](../src/Rule/CollectedDataNode/DisallowUnsafeAccessOfMagicDataObjectRule.php)

```yaml
parameters:
    silverstanRules:
        disallowUnsafeAccessOfMagicDataObject:
            enabled: true
```

↓

```php
/**
 * @method \SilverStripe\ORM\DataObject Bar()
 */
final class Foo extends \SilverStripe\ORM\DataObject
{
    public function doSomething(): string
    {
        return $this->Bar()->Title;
    }
}
```

:x:

<br>

```php
/**
 * @method \SilverStripe\ORM\DataObject Bar()
 */
final class Foo extends \SilverStripe\ORM\DataObject
{
    public function doSomething(): string
    {
        if ($this->Bar() instanceof \SilverStripe\ORM\DataObject && $this->Bar()->exists()) {
            return $this->Bar()->Title;
        }

        return '';
    }
}
```

:+1:

<br>

## DisallowUseOfReservedConfigurationPropertyNameRule

Disallow declaring a non configuration property that shares the same name with an existing configuration property.

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\ClassPropertyNode\DisallowUseOfReservedConfigurationPropertyNameRule`](../src/Rule/ClassPropertyNode/DisallowUseOfReservedConfigurationPropertyNameRule.php)

```yaml
parameters:
    silverstanRules:
        disallowUseOfReservedConfigurationPropertyName:
            enabled: true
```

↓

```php
final class Foo extends \SilverStripe\ORM\DataObject
{
    public static string $table_name = 'Foo';
}
```

:x:

<br>

```php
final class Foo extends \SilverStripe\ORM\DataObject
{
    private static string $table_name = 'Foo';
}
```

:+1:

<br>

## RequireConfigurationPropertyOverrideRule

Require a class to override a set of configuration properties.

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\InClassNode\RequireConfigurationPropertyOverrideRule`](../src/Rule/InClassNode/RequireConfigurationPropertyOverrideRule.php)

```yaml
parameters:
    silverstanRules:
        requireConfigurationPropertyOverride:
            enabled: true
            requiredProperties:
                -
                    class: SilverStripe\ORM\DataObject
                    properties:
                        - table_name
```

↓

```php
final class Foo extends \SilverStripe\ORM\DataObject
{
}
```

:x:

<br>

```php
final class Foo extends \SilverStripe\ORM\DataObject
{
    private static string $table_name = 'Foo';
}
```

:+1:

<br>

## RequireConfigurationPropertySnakeCaseNameRule

Configuration properties must be in snake_case.

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\ClassPropertyNode\RequireConfigurationPropertySnakeCaseNameRule`](../src/Rule/ClassPropertyNode/RequireConfigurationPropertySnakeCaseNameRule.php)

```yaml
parameters:
    silverstanRules:
        requireConfigurationPropertySnakeCaseName:
            enabled: true
```

↓

```php
final class Foo extends \SilverStripe\ORM\DataObject
{
    private static string $fooBar = 'foo bar';
}
```

:x:

<br>

```php
final class Foo extends \SilverStripe\ORM\DataObject
{
    private static string $foo_bar = 'foo bar';
}
```

:+1:

<br>

## RequireInterfaceForExtensibleHookMethodRule

Require extensible hook methods to be defined via an interface. Use the `@phpstan-silverstripe-extend` annotation resolve the interface location.

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\MethodCall\RequireInterfaceForExtensibleHookMethodRule`](../src/Rule/MethodCall/RequireInterfaceForExtensibleHookMethodRule.php)

```yaml
parameters:
    silverstanRules:
        requireInterfaceForExtensibleHookMethod:
            enabled: true
```

↓

```php
final class Foo extends \SilverStripe\ORM\DataObject
{
    public function bar(): string
    {
        $bar = 'bar';

        $this->extend('updateBar', $bar);

        return $bar;
    }
}
```

:x:

<br>

```php
final class Foo extends \SilverStripe\ORM\DataObject
{
    /**
     * @phpstan-silverstripe-extend UpdateBar
     */
    public function bar(): string
    {
        $bar = 'bar';

        $this->extend('updateBar', $bar);

        return $bar;
    }
}

interface UpdateBar
{
    public function updateBar(string &$bar): void;
}
```

:+1:

<br>

## RequireParentCallInOverridenMethodRule

Require parent call in an overriden method.

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\ClassMethod\RequireParentCallInOverridenMethodRule`](../src/Rule/ClassMethod/RequireParentCallInOverridenMethodRule.php)

```yaml
parameters:
    silverstanRules:
        requireParentCallInOverridenMethod:
            enabled: true
            requiredParentCalls:
                -
                    class: SilverStripe\ORM\DataObject
                    method: onBeforeWrite
                    isFirst: false
```

↓

```php
final class Foo extends \SilverStripe\ORM\DataObject
{
    protected function onBeforeWrite(): void
    {
    }
}
```

:x:

<br>

```php
final class Foo extends \SilverStripe\ORM\DataObject
{
    protected function onBeforeWrite(): void
    {
        parent::onBeforeWrite();
    }
}
```

:+1:

<br>
