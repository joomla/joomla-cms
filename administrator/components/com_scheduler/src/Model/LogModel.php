<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Model;

use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * MVC Model to interact with the Scheduler logs.
 *
 * @since  __DPELOY_VERSION__
 */
class LogModel extends AdminModel
{
    /**
     * There is no form for the log.
     *
     * @param   array  $data      Data that needs to go into the form
     * @param   bool   $loadData  Should the form load its data from the DB?
     *
     * @return Form|boolean  A Form object on success, false on failure.
     *
     * @since  5.3.0
     * @throws \Exception
     */
    public function getForm($data = [], $loadData = true)
    {
        return false;
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $name     The table name. Optional.
     * @param   string  $prefix   The class prefix. Optional.
     * @param   array   $options  Configuration array for model. Optional.
     *
     * @return Table
     *
     * @since  5.3.0
     * @throws \Exception
     */
    public function getTable($name = 'Log', $prefix = 'Administrator', $options = []): Table
    {
        return parent::getTable($name, $prefix, $options);
    }
}
