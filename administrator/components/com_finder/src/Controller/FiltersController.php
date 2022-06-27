<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Controller;

use Joomla\CMS\MVC\Controller\AdminController;

/**
 * Filters controller class for Finder.
 *
 * @since  2.5
 */
class FiltersController extends AdminController
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $text_prefix = 'COM_FINDER_FILTERS';

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model.
     *
     * @since   2.5
     */
    public function getModel($name = 'Filter', $prefix = 'Administrator', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }
}
