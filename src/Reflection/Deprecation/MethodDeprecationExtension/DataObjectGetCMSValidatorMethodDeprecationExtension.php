<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Reflection\Deprecation\MethodDeprecationExtension;

use PHPStan\BetterReflection\Reflection\Adapter\ReflectionMethod;
use PHPStan\Reflection\Deprecation\Deprecation;
use PHPStan\Reflection\Deprecation\MethodDeprecationExtension;
use function sprintf;
use function strtolower;

/**
 * This extension reports usage of the deprecated `DataObject::getCMSValidator()` method.
 *
 * @see \Cambis\Silverstan\Tests\Reflection\Deprecation\MethodDeprecationExtension\DataObjectGetCMSValidatorMethodDeprecationExtensionTest
 */
final class DataObjectGetCMSValidatorMethodDeprecationExtension implements MethodDeprecationExtension
{
    public function getMethodDeprecation(ReflectionMethod $reflectionMethod): ?Deprecation
    {
        if (!$reflectionMethod->getDeclaringClass()->isSubclassOf('SilverStripe\ORM\DataObject')) {
            return null;
        }

        if (strtolower($reflectionMethod->getName()) !== 'getcmsvalidator') {
            return null;
        }

        // Don't report if `getCMSCompositeValidator()` does not exist, as we are probably on an older version of Silverstripe
        if (!$reflectionMethod->getDeclaringClass()->hasMethod('getCMSCompositeValidator')) {
            return null;
        }

        return Deprecation::createWithDescription(sprintf(
            'use %s::getCMSCompositeValidator() instead. See https://docs.silverstripe.org/en/developer_guides/forms/validation/#validation-in-the-cms.',
            $reflectionMethod->getDeclaringClass()->getName()
        ));
    }
}
