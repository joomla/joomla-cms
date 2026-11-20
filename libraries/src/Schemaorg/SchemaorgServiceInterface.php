<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Schemaorg;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The schemaorg service.
 *
 * @since  5.0.0
 */
interface SchemaorgServiceInterface
{
    /**
     * Returns valid contexts.
     *
     * @return  array
     *
     * @since   5.0.0
     */
    public function getSchemaorgContexts(): array;
}
