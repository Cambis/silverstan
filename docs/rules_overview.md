# 9 rules overview

## DisallowMethodCallOnUnsafeDataObjectRule

Call `exists()` first before accessing any magic `SilverStripe\ORM\DataObject` methods as the object may not be present in the database. Database manipulation methods such as `write()` and `delete()` are allowed by default. If you think a method is safe to call by default add it to the `allowedMethodCalls` configuration.

:mag_right: **silverstan.dataObject.unsafe**

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\MethodCall\DisallowMethodCallOnUnsafeDataObjectRule`](../src/Rule/MethodCall/DisallowMethodCallOnUnsafeDataObjectRule.php)

```yaml
parameters:
    silverstan:
        disallowMethodCallOnUnsafeDataObject:
            enabled: true
            allowedMethodCalls:
                My\Object\Class:
                    - doSomethingSafe
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
        return $this->Bar()->doSomething();
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
        if (!$this->Bar()->exists()) {
            return '';
        }

        return $this->Bar()->doSomething();
    }
}
```

:+1:

<br>

## DisallowNewInstanceOnInjectableRule

Disallow instantiating a `SilverStripe\Core\Injectable` class using `new`. Use `create()` instead.

:mag_right: **silverstan.injectable.useCreate**

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\New_\DisallowNewInstanceOnInjectableRule`](../src/Rule/New_/DisallowNewInstanceOnInjectableRule.php)

```yaml
parameters:
    silverstan:
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

## DisallowOverridingOfConfigurationPropertyTypeRule

Disallow overriding types of configuration properties.

:mag_right: **silverstan.configurationProperty.invalid**

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\ClassPropertyNode\DisallowOverridingOfConfigurationPropertyTypeRule`](../src/Rule/ClassPropertyNode/DisallowOverridingOfConfigurationPropertyTypeRule.php)

```yaml
parameters:
    silverstan:
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

## DisallowUsageOfDeprecatedConfigurationPropertyRule

Disallow usage of depercated configuration properties.

Automatically enabled if [PHPStan deprecation rules](https://github.com/phpstan/phpstan-deprecation-rules) is installed.

:mag_right: **silverstan.configurationProperty.deprecated**

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\ClassPropertyNode\DisallowUsageOfDeprecatedConfigurationPropertyRule`](../src/Rule/ClassPropertyNode/DisallowUsageOfDeprecatedConfigurationPropertyRule.php)

```yaml
parameters:
    silverstan:
        disallowUsageOfDeprecatedConfigurationProperty:
            enabled: true
```

↓

```php
class Foo extends \SilverStripe\ORM\DataObject
{
    /**
     * @deprecated use new_property instead.
     */
    private static string $deprecated_property = '';
}

final class Bar extends Foo
{
    private static string $deprecated_property = '';
}
```

:x:

<br>

```php
class Foo extends \SilverStripe\ORM\DataObject
{
    /**
     * @deprecated use new_property instead.
     */
    private static string $deprecated_property = '';
}

final class Bar extends Foo
{
    private static string $new_property = '';
}
```

:+1:

<br>

## DisallowPropertyFetchOnConfigForClassRule

Disallow property fetch on `SilverStripe\Core\Config\Config_ForClass`. PHPStan cannot resolve the type of the property, use `self::config()->get('property_name')` instead.

:mag_right: **silverstan.configurationProperty.unresolveableType**

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\PropertyFetch\DisallowPropertyFetchOnConfigForClassRule`](../src/Rule/PropertyFetch/DisallowPropertyFetchOnConfigForClassRule.php)

```yaml
parameters:
    silverstan:
        disallowPropertyFetchOnConfigForClass:
            enabled: true
```

↓

```php
final class Foo extends \SilverStripe\ORM\DataObject
{
    private static string $singular_name = 'Foo';

    public function getType(): string
    {
        return self::config()->singular_name;
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
        return self::config()->get('singular_name');
    }
}
```

:+1:

<br>

## DisallowPropertyFetchOnUnsafeDataObjectRule

Call `exists()` first before accessing any magic `SilverStripe\ORM\DataObject` properties as the object may not be present in the database. Property assignment is allowed.

:mag_right: **silverstan.dataObject.unsafe**

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\PropertyFetch\DisallowPropertyFetchOnUnsafeDataObjectRule`](../src/Rule/PropertyFetch/DisallowPropertyFetchOnUnsafeDataObjectRule.php)

```yaml
parameters:
    silverstan:
        disallowPropertyFetchOnUnsafeDataObject:
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
        if (!$this->Bar()->exists()) {
            return '';
        }

        return $this->Bar()->Title;
    }
}
```

:+1:

<br>

## DisallowStaticPropertyFetchOnConfigurationPropertyRule

Disallow static property fetch on configuration properties.

:mag_right: **silverstan.configurationProperty.unsafe**

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\StaticPropertyFetch\DisallowStaticPropertyFetchOnConfigurationPropertyRule`](../src/Rule/StaticPropertyFetch/DisallowStaticPropertyFetchOnConfigurationPropertyRule.php)

```yaml
parameters:
    silverstan:
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
        return self::config()->get('singular_name');
    }
}
```

:+1:

<br>

## RequireConfigurationPropertyOverrideRule

Require a class to override a set of configuration properties.

:mag_right: **silverstan.configurationProperty.required**

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\InClassNode\RequireConfigurationPropertyOverrideRule`](../src/Rule/InClassNode/RequireConfigurationPropertyOverrideRule.php)

```yaml
parameters:
    silverstan:
        requireConfigurationPropertyOverride:
            enabled: true
            classes:
                SilverStripe\ORM\DataObject:
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

## RequireParentCallInOverridenMethodRule

Require parent call in an overriden method.

:mag_right: **silverstan.requiredParentCall**

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\ClassMethod\RequireParentCallInOverridenMethodRule`](../src/Rule/ClassMethod/RequireParentCallInOverridenMethodRule.php)

```yaml
parameters:
    silverstan:
        requireParentCallInOverridenMethod:
            enabled: true
            classes:
                -
                    class: SilverStripe\ORM\DataObject
                    method: onBeforeWrite
```

↓

```php
final class Foo extends \SilverStripe\ORM\DataObject
{
    protected function onBeforeWrite(): void
    {
        // Custom code...
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
        // Custom code...

        parent::onBeforeWrite();
    }
}
```

:+1:

<br>