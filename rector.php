<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $configurator): void {
    // here we can define, what sets of rules will be applied
    // tip: use "SetList" class to autocomplete sets

    $configurator->import( SetList::CODE_QUALITY );
    $configurator->import( SetList::PHP_74 );
    $configurator->import( SetList::TYPE_DECLARATION );
    $configurator->import( SetList::TYPE_DECLARATION_STRICT );
    $configurator->import( SetList::EARLY_RETURN );
    $configurator->import( SetList::NAMING );
    $configurator->import( SetList::CODING_STYLE );
    $configurator->import( LevelSetList::UP_TO_PHP_74 );
    $configurator->fileExtensions( [ 'php' ] );
    $configurator->phpVersion( PhpVersion::PHP_74 );
    $configurator->importNames();
    $configurator->importShortClasses( false );
    $configurator->parallel();
    $configurator->cacheDirectory( __DIR__ . '/cache/rector' );
    $configurator->paths( [
        __DIR__ . '/src',
    ] );

    $configurator->skip(
        [
            // or fnmatch
            __DIR__ . '/vendor',
            __DIR__ . '/cache',
        ]
    );
};
