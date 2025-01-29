<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Type\ObjectType;

use PHPStan\Type\ObjectType;

/**
 * Internal type used to represent a `SilverStripe\ORM\DataObject` that may not exist in the database.
 */
final class UnsafeObjectType extends ObjectType
{
}
