<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       6.1.0
 */

namespace Joomla\Component\Workflow\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Model class for Graphical View of the workflow
 *
 * @since  6.1.0
 */
class GraphModel extends AdminModel
{
    /**
     * Auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since  6.1.0
     */
    public function populateState()
    {
        parent::populateState();

        $app       = Factory::getApplication();
        $context   = $this->option . '.' . $this->name;
        $extension = $app->getUserStateFromRequest($context . '.filter.extension', 'extension', null, 'cmd');

        $this->setState('filter.extension', $extension);
    }

    /**
     * Method to get the name of the model.
     *
     * @return  string  The name of the model.
     *
     * @since   6.1.0
     */
    public function getName()
    {
        return 'workflow'; // TODO: change it to to handle dynamically
    }

    /**
     * Method to get the form.
     *
     * @param   array  $data       An optional array of data for the form to interrogate.
     * @param   bool   $loadData   True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed  A \Joomla\CMS\Form\Form object on success, false on failure.
     *
     * @since   6.1.0
     */
    public function getForm($data = [], $loadData = true)
    {
        return false;
    }
}
