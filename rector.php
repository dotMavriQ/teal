<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\Config\RectorConfig;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;
use RectorLaravel\Rector\ClassMethod\MakeModelAttributesAndScopesProtectedRector;
use RectorLaravel\Rector\FuncCall\AppToResolveRector;
use RectorLaravel\Rector\StaticCall\CarbonToDateFacadeRector;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/database',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    // Keep code compatible with the composer PHP constraint (^8.2),
    // even though the runtime is newer.
    ->withPhpSets()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        earlyReturn: true,
        typeDeclarations: true,
    )
    ->withSets([
        LaravelSetList::LARAVEL_CODE_QUALITY,
    ])
    ->withImportNames(removeUnusedImports: true)
    ->withSkip([
        // Carbon is used deliberately; don't rewrite it to the Date facade.
        CarbonToDateFacadeRector::class,
        // Project convention (CLAUDE.md): resolve services via app(Service::class),
        // not resolve().
        AppToResolveRector::class,
        // empty() is used idiomatically throughout; rewriting it to
        // in_array($x, [null, '', '0'], true) is noisier, not clearer.
        DisallowedEmptyRuleFixerRector::class,
        // Eloquent accessors/scopes are kept public by convention (view/Livewire
        // access); don't force them to protected.
        MakeModelAttributesAndScopesProtectedRector::class,
        // Prefer the concise truthy checks already used across the codebase over
        // explicit `!== null` / `!== ''` comparisons.
        ExplicitBoolCompareRector::class,
    ]);
