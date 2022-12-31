<?php

namespace Joomla\CMS\Schemaorg;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait for component schemaorg service.
 *
 * @since  4.0.0
 */
trait SchemaorgServiceTrait
{
    /**
     * Get a MVC factory
     *
     * @return  MVCFactoryInterface
     *
     * @since   4.0.0
     */
    abstract public function getMVCFactory(): MVCFactoryInterface;
}
