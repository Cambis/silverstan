# 3 Rules Overview

## ConfigurablePropertiesExtension

Allows configurable read-write properties.

- class: [`Cambis\Silverstan\Rules\ClassPropertiesNode\ConfigurablePropertiesExtension`](../src/Rules/ClassPropertiesNode/ConfigurablePropertiesExtension.php)

```php
final class Foo extends \SilverStripe\ORM\DataObject
{
    private array $bar = [];
}
```

:x:

<br>

```php
final class Foo extends \SilverStripe\ORM\DataObject
{
    private static array $bar = [];
}
```

:+1:

<br>

## ForbidStaticPropertyFetchOnConfigurablePropertyRule

Forbid static property fetch on configurable properties.

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rules\StaticPropertyFetch\ForbidStaticPropertyFetchOnConfigurablePropertyRule`](../src/Rules/StaticPropertyFetch/ForbidStaticPropertyFetchOnConfigurablePropertyRule.php)

```yaml
parameters:
    silverstanRules:
        forbidStaticPropertyFetchOnConfigurableProperty:
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

## ForbidSuperglobalsRule

Forbid the use of superglobals ($_GET, $_REQUEST etc.).

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rules\Variable\ForbidSuperglobalsRule`](../src/Rules/Variable/ForbidSuperglobalsRule.php)

```yaml
parameters:
    silverstanRules:
        forbidSuperglobals:
            enabled: true
            forbiddenSuperglobals:
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
