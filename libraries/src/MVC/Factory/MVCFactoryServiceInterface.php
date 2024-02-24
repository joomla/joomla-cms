<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface to be implemented by classes depending on a MVC factory.
 *
 * @since  4.0.0
 */
interface MVCFactoryServiceInterface
{
    /**
     * Get the factory.
     *
     * @return  MVCFactoryInterface
     *
     * @since   4.0.0
     * @throws  \UnexpectedValueException May be thrown if the factory has not been set.
     */
    public function getMVCFactory(): MVCFactoryInterface;
}
