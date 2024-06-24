# 14 Rules Overview

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
namespace App\Extension;

/**
 * @extends \SilverStripe\Core\Extension<\App\Model\Foo & static>
 */
final class FooExtension extends \SilverStripe\Core\Extension
{
    public function foo(): string
    {
        return 'foo';
    }
}

namespace App\Model;

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
namespace App\Contract;

interface FooExtensionInterface
{
    public function foo(): string;
}

namespace App\Extension;

/**
 * @extends \SilverStripe\Core\Extension<\App\Model\Foo & static>
 */
final class FooExtension extends \SilverStripe\Core\Extension implements FooExtensionInterface
{
    public function foo(): string
    {
        return 'foo';
    }
}

namespace App\Model;

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
    public function process(\SilverStripe\Control\HTTPRequest $request, callable $delegate): void
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
    public function process(\SilverStripe\Control\HTTPRequest $request, callable $delegate): void
    {
        $foo =  $request->getVar('foo');
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

## RequireClassInAllowedNamespaceRule

Require a class to be in an allowed namespace.

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\InClassNode\RequireClassInAllowedNamespaceRule`](../src/Rule/InClassNode/RequireClassInAllowedNamespaceRule.php)

```yaml
parameters:
    silverstanRules:
        requireClassInAllowedNamespace:
            enabled: true
            classes:
                -
                    class: SilverStripe\ORM\DataObject
                    allowedNamespaces:
                        - Model
                -
                    class: SilverStripe\Control\Controller
                    allowedNamespaces:
                        - Controller
                -
                    class: SilverStripe\Core\Extension
                    allowedNamespaces:
                        - Extension
                -
                    class: SilverStripe\Dev\BuildTask
                    allowedNamespaces:
                        - Task
                -
                    class: Symbiote\QueuedJobs\Services\AbstractQueuedJob
                    allowedNamespaces:
                        - Job
```

↓

```php
namespace App;

final class Foo extends \SilverStripe\ORM\DataObject
{
}

namespace App;

final class FooController extends \SilverStripe\Control\Controller
{
}

namespace App;

final class FooExtension extends \SilverStripe\Core\Extension
{
}

namespace App;

final class FooTask extends \SilverStripe\Dev\BuildTask
{
}

namespace App;

final class FooJob extends \Symbiote\QueuedJobs\Services\AbstractQueuedJob
{
}
```

:x:

<br>

```php
namespace App\Model;

final class Foo extends \SilverStripe\ORM\DataObject
{
}

namespace App\Controller;

final class FooController extends \SilverStripe\Control\Controller
{
}

namespace App\Extension;

final class FooExtension extends \SilverStripe\Core\Extension
{
}

namespace App\Task;

final class FooTask extends \SilverStripe\Dev\BuildTask
{
}

namespace App\Job;

final class FooJob extends \Symbiote\QueuedJobs\Services\AbstractQueuedJob
{
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
                        - singular_name
                        - plural_name
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
namespace App\Model;

final class Foo extends \SilverStripe\ORM\DataObject
{
    public function bar(): string
    {
        $bar = 'bar';

        $this->extend('updateBar', $bar);

        return $bar;
    }
}

namespace App\Extension;

/**
 * @extends \SilverStripe\Core\Extension<Foo & static>
 */
final class FooExtension extends \SilverStripe\Core\Extension
{
    public function updateBar(string &$bar): void
    {
        $bar = 'foobar';
    }
}
```

:x:

<br>

```php
namespace App\Model;

final class Foo extends \SilverStripe\ORM\DataObject
{
    /**
     * @phpstan-silverstripe-extend \App\Contract\UpdateBar
     */
    public function bar(): string
    {
        $bar = 'bar';

        $this->extend('updateBar', $bar);

        return $bar;
    }
}

namespace App\Contract;

interface UpdateBar
{
    public function updateBar(string &$bar): void;
}

namespace App\Extension;

/**
 * @extends \SilverStripe\Core\Extension<Foo & static>
 */
final class FooExtension extends \SilverStripe\Core\Extension implements \App\Contract\UpdateBar
{
    public function updateBar(string &$bar): void
    {
        $bar = 'foobar';
    }
}
```

:+1:

<br>

## RequireInterfaceInAllowedNamespaceRule

Require an interface to be in an allowed namespace.

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\Interface_\RequireInterfaceInAllowedNamespaceRule`](../src/Rule/Interface_/RequireInterfaceInAllowedNamespaceRule.php)

```yaml
parameters:
    silverstanRules:
        requireInterfaceInAllowedNamespace:
            enabled: true
            allowedNamespaces:
                - Contract
```

↓

```php
namespace App;

interface FooInterface
{
}
```

:x:

<br>

```php
namespace App\Contract;

interface FooInterface
{
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

## RequireTraitInAllowedNamespaceRule

Require a trait to be in an allowed namespace.

:wrench: **configure it!**

- class: [`Cambis\Silverstan\Rule\Trait_\RequireTraitInAllowedNamespaceRule`](../src/Rule/Trait_/RequireTraitInAllowedNamespaceRule.php)

```yaml
parameters:
    silverstanRules:
        requireTraitInAllowedNamespace:
            enabled: true
            allowedNamespaces:
                - Concern
```

↓

```php
namespace App;

trait FooTrait
{
}
```

:x:

<br>

```php
namespace App\Concern;

trait FooTrait
{
}
```

:+1:

<br>
