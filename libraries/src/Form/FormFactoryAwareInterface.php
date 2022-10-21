<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface to be implemented by classes depending on a form factory.
 *
 * @since  4.0.0
 */
interface FormFactoryAwareInterface
{
    /**
     * Set the form factory to use.
     *
     * @param   FormFactoryInterface  $factory  The form factory to use.
     *
     * @return  FormFactoryAwareInterface  This method is chainable.
     *
     * @since   4.0.0
     */
    public function setFormFactory(FormFactoryInterface $factory);
}
