<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Versioning\VersionableModelTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * User note model.
 *
 * @since  2.5
 */
class NoteModel extends AdminModel
{
    use VersionableModelTrait;

    /**
     * The type alias for this content type.
     *
     * @var      string
     * @since    3.2
     */
    public $typeAlias = 'com_users.note';

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  \Joomla\CMS\Form\Form|bool  A Form object on success, false on failure
     *
     * @since   2.5
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_users.note', 'note', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get a single record.
     *
     * @param   integer  $pk  The id of the primary key.
     *
     * @return  mixed  Object on success, false on failure.
     *
     * @since   2.5
     * @throws  \Exception
     */
    public function getItem($pk = null)
    {
        $result = parent::getItem($pk);

        // Get the dispatcher and load the content plugins.
        PluginHelper::importPlugin('content');

        // Load the user plugins for backward compatibility (v3.3.3 and earlier).
        PluginHelper::importPlugin('user');

        // Trigger the data preparation event.
        Factory::getApplication()->triggerEvent('onContentPrepareData', ['com_users.note', $result]);

        return $result;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  object  The data for the form.
     *
     * @since   1.6
     * @throws  \Exception
     */
    protected function loadFormData()
    {
        // Get the application
        $app = Factory::getApplication();

        // Check the session for previously entered form data.
        $data = $app->getUserState('com_users.edit.note.data');

        if (empty($data)) {
            $data = $this->getItem();

            // Prime some default values.
            if ($this->getState('note.id') == 0) {
                $data->catid = $app->getInput()->get('catid', $app->getUserState('com_users.notes.filter.category_id'), 'int');
            }

            $userId = $app->getInput()->get('u_id', 0, 'int');

            if ($userId != 0) {
                $data->user_id = $userId;
            }
        }

        $this->preprocessData('com_users.note', $data);

        return $data;
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  \Exception
     */
    protected function populateState()
    {
        parent::populateState();

        $userId = Factory::getApplication()->getInput()->get('u_id', 0, 'int');
        $this->setState('note.user_id', $userId);
    }
}
