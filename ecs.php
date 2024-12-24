<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedTraitsFixer;
use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Whitespace\StatementIndentationFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withSkip(
        [
            StatementIndentationFixer::class => [
                __DIR__ . '/src/Middleware/MiddlewareDispatcher.php'
            ],
        ]
    )
    ->withConfiguredRule(
        ClassDefinitionFixer::class,
        [
            'space_before_parenthesis' => true,
        ],
    )
    ->withConfiguredRule(
        VisibilityRequiredFixer::class,
        [
            'elements' => [], // Esto deshabilitará la regla de visibilidad
        ]
    )
    ->withFileExtensions(['php'])
    ->withPaths(
        [
            __DIR__ . '/src',
            __DIR__ . '/tests',
        ],
    )
    ->withPhpCsFixerSets(perCS20: true)
    ->withPreparedSets(
        cleanCode: true,
        comments:true,
        docblocks: true,
        namespaces: true,
        psr12: true,
        strict: true,
    )
    ->withRules(
        [
            NoUnusedImportsFixer::class,
            OrderedClassElementsFixer::class,
            OrderedTraitsFixer::class,
        ]
    );
