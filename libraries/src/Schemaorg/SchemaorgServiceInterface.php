<?php

namespace Joomla\CMS\Schemaorg;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The schemaorg service.
 *
 * @since  4.0.0
 */
interface SchemaorgServiceInterface
{
    /**
     * Returns valid contexts.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public function getSchemaorgContexts(): array;
}
