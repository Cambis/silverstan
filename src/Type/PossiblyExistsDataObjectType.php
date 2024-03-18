<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Type;

use PHPStan\Type\ObjectType;

/**
 * Represents a `\SilverStripe\ORM\DataObject` accessed via a magic method.
 */
final class PossiblyExistsDataObjectType extends ObjectType
{
}
