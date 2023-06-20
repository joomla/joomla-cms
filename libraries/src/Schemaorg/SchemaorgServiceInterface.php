<?php

namespace Joomla\CMS\Schemaorg;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The schemaorg service.
 *
 * @since  _DEPLOY_VERSION__
 */
interface SchemaorgServiceInterface
{
    /**
     * Returns valid contexts.
     *
     * @return  array
     *
     * @since   _DEPLOY_VERSION__
     */
    public function getSchemaorgContexts(): array;
}
