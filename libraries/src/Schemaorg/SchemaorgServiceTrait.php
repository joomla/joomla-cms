<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Schemaorg;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait for component schemaorg service.
 *
 * @since  5.0.0
 */
trait SchemaorgServiceTrait
{
    /**
     * Get a MVC factory
     *
     * @return  MVCFactoryInterface
     *
     * @since   5.0.0
     */
    abstract public function getMVCFactory(): MVCFactoryInterface;
}
