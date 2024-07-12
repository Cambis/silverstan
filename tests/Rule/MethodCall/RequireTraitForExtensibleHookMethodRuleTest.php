<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\MethodCall;

use Cambis\Silverstan\NodeAnalyser\CallLikeAnalyser;
use Cambis\Silverstan\NodeAnalyser\ClassAnalyser;
use Cambis\Silverstan\Rule\MethodCall\RequireTraitForExtensibleHookMethodRule;
use Cambis\Silverstan\TypeComparator\CallLikeTypeComparator;
use Cambis\Silverstan\TypeResolver\CallLikeTypeResolver;
use Override;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\FileTypeMapper;

/**
 * @extends RuleTestCase<RequireTraitForExtensibleHookMethodRule>
 */
final class RequireTraitForExtensibleHookMethodRuleTest extends RuleTestCase
{
    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/ExtensibleComplete.php'], []);

        $this->analyse(
            [
                __DIR__ . '/Fixture/ExtensibleIncompleteAnnotation.php',
                __DIR__ . '/Fixture/ExtensibleMethodNonVoidReturnType.php',
                __DIR__ . '/Fixture/ExtensibleMethodNotAbstract.php',
                __DIR__ . '/Fixture/ExtensibleMethodNotProtected.php',
                __DIR__ . '/Fixture/ExtensibleMethodSignatureDoesNotMatchNumberOfParams.php',
                __DIR__ . '/Fixture/ExtensibleMethodSignatureDoesNotMatchType.php',
                __DIR__ . '/Fixture/ExtensibleMethodSignatureNotPassedByReference.php',
                __DIR__ . '/Fixture/ExtensibleMissingAnnotation.php',
                __DIR__ . '/Fixture/ExtensibleMultipleMethods.php',
                __DIR__ . '/Fixture/ExtensibleNotATrait.php',
                __DIR__ . '/Fixture/ExtensibleUnresolveableAnnotation.php',
            ],
            [
                [
                    '@phpstan-silverstripe-extend annotation does not specify anything.',
                    19,
                ],
                [
                    'Specified class method Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Concern\MethodNonVoidReturnType::updateFoo() must return void.',
                    20,
                ],
                [
                    'Specified class method Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Concern\MethodNotAbstract::updateFoo() must be abstract.',
                    20,
                ],
                [
                    'Specified class method Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Concern\MethodNotProtected::updateFoo() must be protected.',
                    20,
                ],
                [
                    'Specified class method Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Concern\MethodSignatureDoesNotMatchNumberOfParams::updateFoo() signature does not match.',
                    20,
                ],
                [
                    'Specified class method Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Concern\MethodSignatureDoesNotMatchType::updateFoo() signature does not match.',
                    20,
                ],
                [
                    'Specified class method Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Concern\MethodSignatureNotPassedByReference::updateFoo() literal parameters must be passed by reference.',
                    20,
                    'See: https://docs.silverstripe.org/en/5/developer_guides/extending/extensions/#modifying-existing-methods',
                ],
                [
                    'Cannot find trait definition for extensible hook updateFoo().',
                    16,
                    'Use the @phpstan-silverstripe-extend annotation to point an trait where the hook method is defined.',
                ],
                [
                    'Specified class Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Concern\MultipleMethods can only contain a single method definition.',
                    20,
                ],
                [
                    'Specified class Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Concern\NotATrait must be a trait.',
                    20,
                ],
                [
                    'Could not resolve specified class Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\UpdateFoo.',
                    19,
                ],
            ]
        );
    }

    #[Override]
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../../../extension.neon',
        ];
    }

    #[Override]
    protected function getRule(): Rule
    {
        return new RequireTraitForExtensibleHookMethodRule(
            self::getContainer()->getByType(CallLikeAnalyser::class),
            self::getContainer()->getByType(CallLikeTypeComparator::class),
            self::getContainer()->getByType(CallLikeTypeResolver::class),
            self::getContainer()->getByType(ClassAnalyser::class),
            self::getContainer()->getByType(FileTypeMapper::class),
            self::getContainer()->getByType(ReflectionProvider::class),
        );
    }
}
