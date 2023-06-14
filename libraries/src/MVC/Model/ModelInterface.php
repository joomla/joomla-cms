<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface for a base model.
 *
 * @since  4.0.0
 */
interface ModelInterface
{
    /**
     * Method to get the model name.
     *
     * @return  string  The name of the model
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function getName();
}
