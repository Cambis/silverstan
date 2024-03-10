# 6 Rules Overview

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

## DisallowOverridingOfConfigurablePropertyTypeRule

Disallow overriding types of configurable properties.

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\ClassPropertyNode\DisallowOverridingOfConfigurablePropertyTypeRule`](../src/Rule/ClassPropertyNode/DisallowOverridingOfConfigurablePropertyTypeRule.php)

```yaml
parameters:
    silverstanRules:
        disallowOverridingOfConfigurablePropertyType:
            enabled: true
```

↓

```php
class Foo extends \SilverStripe\ORM\DataObject
{
    private static string $foo = 'foo';
}
```

:x:

<br>

```php
final class Bar extends Foo
{
    private static string|bool $foo = false;
}
```

:+1:

<br>

## DisallowStaticPropertyFetchOnConfigurablePropertyRule

Disallow static property fetch on configurable properties.

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\StaticPropertyFetch\DisallowStaticPropertyFetchOnConfigurablePropertyRule`](../src/Rule/StaticPropertyFetch/DisallowStaticPropertyFetchOnConfigurablePropertyRule.php)

```yaml
parameters:
    silverstanRules:
        disallowStaticPropertyFetchOnConfigurableProperty:
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

## DisallowUseOfReservedConfigurablePropertyNameRule

Disallow declaring a non configurable property that shares the same name with an existing configurable property.

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\ClassPropertyNode\DisallowUseOfReservedConfigurablePropertyNameRule`](../src/Rule/ClassPropertyNode/DisallowUseOfReservedConfigurablePropertyNameRule.php)

```yaml
parameters:
    silverstanRules:
        disallowUseOfReservedConfigurablePropertyName:
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

## RequireConfigurablePropertyOverrideRule

Require a class to override a set of configurable properties.

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\InClassNode\RequireConfigurablePropertyOverrideRule`](../src/Rule/InClassNode/RequireConfigurablePropertyOverrideRule.php)

```yaml
parameters:
    silverstanRules:
        requireConfigurablePropertyOverride:
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
