<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Type\Concern;

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
    public function isSuperTypeOf(Type $type): IsSuperTypeOfResult
    {
        /** @phpstan-ignore-next-line phpstanApi.instanceofType */
        if ($type instanceof HasMethodType) {
            return new IsSuperTypeOfResult(TrinaryLogic::createMaybe(), []);
        }
        foreach ($type->getObjectClassReflections() as $classReflection) {
            foreach ($this->getObjectClassReflections() as $selfClassReflection) {
                if (!$classReflection->isSubclassOfClass($selfClassReflection)) {
                    continue;
                }

                return new IsSuperTypeOfResult(TrinaryLogic::createYes(), []);
            }

            if ($classReflection->hasTraitUse('SilverStripe\Core\Extensible')) {
                return new IsSuperTypeOfResult(TrinaryLogic::createMaybe(), []);
            }

            if ($classReflection->is('SilverStripe\Core\Extension')) {
                return new IsSuperTypeOfResult(TrinaryLogic::createMaybe(), []);
            }
        }
        return parent::isSuperTypeOf($type);
    }
}
