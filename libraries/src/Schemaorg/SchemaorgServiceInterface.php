<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Schemaorg;

\defined('_JEXEC') or die;

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
