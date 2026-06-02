<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
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
    ]);
