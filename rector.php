<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function(RectorConfig $rectorConfig): void {
    $rectorConfig->importNames(true);
    $rectorConfig->importShortClasses(false);

    $rectorConfig->paths([
        __DIR__,
    ]);

    $rectorConfig->skip([
        // Directories to skip
        __DIR__ . '/administrator/cache',
        __DIR__ . '/administrator/logs',
        __DIR__ . '/cache',
        __DIR__ . '/images',
        __DIR__ . '/installation/cache',
        __DIR__ . '/libraries/vendor',
        __DIR__ . '/node_modules',
        __DIR__ . '/tmp',
    ]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
    ]);
};
