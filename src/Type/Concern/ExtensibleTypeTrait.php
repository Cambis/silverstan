<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Type\Concern;

use Override;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Accessory\HasMethodType;
use PHPStan\Type\IsSuperTypeOfResult;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;

/**
 * This trait allows the use of `SilverStripe\Core\Extensible&SilverStripe\Core\Extension` which would normally resolve to NEVER.
 *
 * @requires-implements TypeWithClassName
 */
trait ExtensibleTypeTrait
{
    #[Override]
    public function isSuperTypeOf(Type $type): IsSuperTypeOfResult
    {
        /** @phpstan-ignore-next-line phpstanApi.instanceofType */
        if ($type instanceof HasMethodType) {
            return new IsSuperTypeOfResult(TrinaryLogic::createMaybe(), []);
        }

        foreach ($type->getObjectClassReflections() as $classReflection) {
            if ($this->getObjectClassNames() !== [] && $classReflection->isSubclassOf($this->getObjectClassNames()[0])) {
                return new IsSuperTypeOfResult(TrinaryLogic::createYes(), []);
            }

            if ($classReflection->hasTraitUse('SilverStripe\Core\Extensible')) {
                return new IsSuperTypeOfResult(TrinaryLogic::createMaybe(), []);
            }

            if ($classReflection->isSubclassOf('SilverStripe\Core\Extension')) {
                return new IsSuperTypeOfResult(TrinaryLogic::createMaybe(), []);
            }
        }

        return parent::isSuperTypeOf($type);
    }
}
