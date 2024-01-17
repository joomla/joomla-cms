<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Factory;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\MVC\Model\ModelInterface;
use Joomla\Input\Input;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Factory to create MVC objects.
 *
 * @since  3.10.0
 */
interface MVCFactoryInterface
{
    /**
     * Method to load and return a controller object.
     *
     * @param   string                   $name    The name of the controller
     * @param   string                   $prefix  The controller prefix
     * @param   array                    $config  The configuration array for the controller
     * @param   CMSApplicationInterface  $app     The app
     * @param   Input                    $input   The input
     *
     * @return  \Joomla\CMS\MVC\Controller\ControllerInterface
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function createController($name, $prefix, array $config, CMSApplicationInterface $app, Input $input);

    /**
     * Method to load and return a model object.
     *
     * @param   string  $name    The name of the model.
     * @param   string  $prefix  Optional model prefix.
     * @param   array   $config  Optional configuration array for the model.
     *
     * @return  ModelInterface  The model object
     *
     * @since   3.10.0
     * @throws  \Exception
     */
    public function createModel($name, $prefix = '', array $config = []);

    /**
     * Method to load and return a view object.
     *
     * @param   string  $name    The name of the view.
     * @param   string  $prefix  Optional view prefix.
     * @param   string  $type    Optional type of view.
     * @param   array   $config  Optional configuration array for the view.
     *
     * @return  \Joomla\CMS\MVC\View\ViewInterface  The view object
     *
     * @since   3.10.0
     * @throws  \Exception
     */
    public function createView($name, $prefix = '', $type = '', array $config = []);

    /**
     * Method to load and return a table object.
     *
     * @param   string  $name    The name of the table.
     * @param   string  $prefix  Optional table prefix.
     * @param   array   $config  Optional configuration array for the table.
     *
     * @return  \Joomla\CMS\Table\Table  The table object
     *
     * @since   3.10.0
     * @throws  \Exception
     */
    public function createTable($name, $prefix = '', array $config = []);
}
