<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Normaliser;

use function explode;
use function ltrim;
use function strtok;
use function trim;

final class Normaliser
{
    /**
     * Remove trailing bracket notation.
     *
     * Foo('') => Foo
     */
    public function normaliseBracketNotation(string $bracketNotation): string
    {
        $result = strtok($bracketNotation, '(');

        if ($result === false) {
            return $bracketNotation;
        }

        return trim($result);
    }

    /**
     * Remove trailing dot notation.
     *
     * Foo.dotNotated => Foo
     *
     * @see https://docs.silverstripe.org/en/5/developer_guides/model/relations/#dot-notation
     */
    public function normaliseDotNotation(string $dotNotated): string
    {
        if (strpos($dotNotated, '.') === false) {
            return $dotNotated;
        }

        [$class] = explode('.', $dotNotated, 2);

        return $class;
    }

    /**
     * Remove leading prefix notation.
     *
     * %$Foo => Foo
     *
     * @see https://docs.silverstripe.org/en/5/developer_guides/extending/injector/#special-yaml-syntax
     */
    public function normalisePrefixNotation(string $prefixNotated): string
    {
        if (strpos($prefixNotated, '%$') === false) {
            return $prefixNotated;
        }

        [$_, $class] = explode('%$', $prefixNotated, 2);

        return $class;
    }

    /**
     * Strip leading backslashes from namespace.
     *
     * \\Foo => Foo
     */
    public function normaliseNamespace(string $namespace): string
    {
        return ltrim($namespace, '\\');
    }
}
