<?php

namespace Joomla\CMS\Schemaorg;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait for component schemaorg service.
 *
 * @since  _DEPLOY_VERSION__
 */
trait SchemaorgServiceTrait
{
    /**
     * Get a MVC factory
     *
     * @return  MVCFactoryInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    abstract public function getMVCFactory(): MVCFactoryInterface;
}
