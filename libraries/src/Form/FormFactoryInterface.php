<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface defining a factory which can create Form objects
 *
 * @since  4.0.0
 */
interface FormFactoryInterface
{
    /**
     * Method to get an instance of a form.
     *
     * @param   string  $name     The name of the form.
     * @param   array   $options  An array of form options.
     *
     * @return  Form
     *
     * @since   4.0.0
     */
    public function createForm(string $name, array $options = []): Form;
}
