<?php

/**
 * Joomla! Content Management System
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\View;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla Platform CMS Interface
 *
 * @since  4.0.0
 */
interface ViewInterface
{
    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function display($tpl = null);

    /**
     * Method to get the model object
     *
     * @param   string  $name  The name of the model (optional)
     *
     * @return  BaseDatabaseModel  The model object
     *
     * @since   3.0
     */
    public function getModel($name = null);

    /**
     * Method to get the view name
     *
     * @return  string  The name of the view
     *
     * @since   5.0.0
     */
    public function getName();
}
