# 9 Rules Overview

## DisallowMethodCallOnUnsafeDataObjectRule

Call `exists()` first before accessing any magic `\SilverStripe\ORM\DataObject` methods as the object may not be present in the database. Database manipulation methods such as `write()` and `delete()` are allowed by default. If you think a method is safe to call by default add it to the `allowedMethodCalls` configuration.

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\MethodCall\DisallowMethodCallOnUnsafeDataObjectRule`](../src/Rule/MethodCall/DisallowMethodCallOnUnsafeDataObjectRule.php)

```yaml
parameters:
    silverstanRules:
        disallowMethodCallOnUnsafeDataObject:
            enabled: true
            allowedMethodCalls:
                - mySafeMethod
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
namespace App\Model;

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
namespace App\Model;

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

## DisallowPropertyFetchOnConfigForClassRule

Disallow property fetch on `\SilverStripe\Core\Config\Config_ForClass`. PHPStan cannot resolve the type of the property, use `self::config()->get('property_name')` instead.

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\PropertyFetch\DisallowPropertyFetchOnConfigForClassRule`](../src/Rule/PropertyFetch/DisallowPropertyFetchOnConfigForClassRule.php)

```yaml
parameters:
    silverstanRules:
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

Call `exists()` first before accessing any magic `\SilverStripe\ORM\DataObject` properties as the object may not be present in the database. Property assignment is allowed.

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\PropertyFetch\DisallowPropertyFetchOnUnsafeDataObjectRule`](../src/Rule/PropertyFetch/DisallowPropertyFetchOnUnsafeDataObjectRule.php)

```yaml
parameters:
    silverstanRules:
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
        return self::config()->get('singular_name');
    }
}
```

:+1:

<br>

## DisallowUnsafeAccessOfMagicDataObjectRule

Call `exists()` first before accessing any magic `\SilverStripe\ORM\DataObject` methods or properties as the object may not be present in the database.

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
        if (!$this->Bar()->exists()) {
            return '';
        }

        return $this->Bar()->Title;
    }
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
            classes:
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

    private static string $singlular_name = 'Foo';

    private static string $plural_name = 'Foos';
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
            classes:
                -
                    class: SilverStripe\ORM\DataObject
                    method: onBeforeWrite
                -
                    class: SilverStripe\ORM\DataObject
                    method: onAfterWrite
                -
                    class: SilverStripe\ORM\DataObject
                    method: requireDefaultRecords
                -
                    class: SilverStripe\Dev\SapphireTest
                    method: setUp
                    isFirst: true
                -
                    class: SilverStripe\Dev\SapphireTest
                    method: setUpBeforeClass
                    isFirst: true
                -
                    class: SilverStripe\Dev\SapphireTest
                    method: tearDown
                -
                    class: SilverStripe\Dev\SapphireTest
                    method: tearDownAfterClass
```

↓

```php
namespace App\Model;

final class Foo extends \SilverStripe\ORM\DataObject
{
    protected function onBeforeWrite(): void
    {
        // Custom code...
    }

    protected function onAfterWrite(): void
    {
        // Custom code...
    }

    public function requireDefaultRecords(): void
    {
        // Custom code...
    }
}

namespace App\Tests\Model;

final class FooTest extends \SilverStripe\Dev\SapphireTest
{
    protected function setUp(): void
    {
        // Custom code...
    }

    protected function setUpBeforeClass(): void
    {
        // Custom code...
    }

    protected function tearDown(): void
    {
        // Custom code...
    }

    protected function tearDownAfterClass(): void
    {
        // Custom code...
    }
}
```

:x:

<br>

```php
namespace App\Model;

final class Foo extends \SilverStripe\ORM\DataObject
{
    protected function onBeforeWrite(): void
    {
        // Custom code...

        parent::onBeforeWrite();
    }

    protected function onAfterWrite(): void
    {
        // Custom code...

        parent::onAfterWrite();
    }

    public function requireDefaultRecords(): void
    {
        // Custom code...

        parent::requireDefaultRecords();
    }
}

namespace App\Tests\Model;

final class FooTest extends \SilverStripe\Dev\SapphireTest
{
    protected function setUp(): void
    {
        parent::setUp();

        // Custom code...
    }

    protected function setUpBeforeClass(): void
    {
        parent::setupBeforeClass();

        // Custom code...
    }

    protected function tearDown(): void
    {
        // Custom code...

        parent::tearDown();
    }

    protected function tearDownAfterClass(): void
    {
        // Custom code...

        parent::tearDownAfterClass();
    }
}
```

:+1:

<br>
