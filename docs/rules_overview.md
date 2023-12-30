# 2 Rules Overview

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
