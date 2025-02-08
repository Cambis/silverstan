<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Type\Concern;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Accessory\HasMethodType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;

/**
 * This trait allows the use of `SilverStripe\Core\Extensible&SilverStripe\Core\Extension` which would normally resolve to NEVER.
 *
 * @requires-implements TypeWithClassName
 */
trait ExtensibleTypeTrait
{
    public function isSuperTypeOf(Type $type): TrinaryLogic
    {
        /** @phpstan-ignore-next-line phpstanApi.instanceofType */
        if ($type instanceof HasMethodType) {
            return TrinaryLogic::createMaybe();
        }
        foreach ($type->getObjectClassReflections() as $classReflection) {
            if ($this->getObjectClassNames() !== [] && $classReflection->isSubclassOf($this->getObjectClassNames()[0])) {
                return TrinaryLogic::createYes();
            }

            if ($classReflection->hasTraitUse('SilverStripe\Core\Extensible')) {
                return TrinaryLogic::createMaybe();
            }

            if ($classReflection->isSubclassOf('SilverStripe\Core\Extension')) {
                return TrinaryLogic::createMaybe();
            }
        }
        return parent::isSuperTypeOf($type);
    }
}
