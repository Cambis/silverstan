<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ValueObject;

use Nette\Utils\Strings;

use function sprintf;

final readonly class ClassAllowedNamespace
{
    /**
     * @var string
     */
    private const ALLOWED_NAMESPACE_REGEX = '#\b%s\b#';

    public function __construct(
        /**
         * @var class-string
         */
        public string $className,
        /**
         * @var string[]
         */
        public array $allowedNamespaces
    ) {
    }

    public function isNamespaceAllowed(string $namespace): bool
    {
        foreach ($this->allowedNamespaces as $allowedNamespace) {
            if (Strings::match($namespace, sprintf(self::ALLOWED_NAMESPACE_REGEX, $allowedNamespace)) !== null) {
                return true;
            }
        }

        return false;
    }
}
