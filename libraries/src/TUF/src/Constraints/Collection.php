<?php

namespace Tuf\Constraints;

use Symfony\Component\Validator\Constraints\Collection as SymfonyCollection;

/**
 * Custom constraint that extends Symfony's Collection constraint to add 'excludedFields'.
 */
class Collection extends SymfonyCollection
{

    /**
     * @var array
     */
    public $unsupportedFields = [];
}
