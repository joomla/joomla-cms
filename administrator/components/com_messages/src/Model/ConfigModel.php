<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Messages\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Message configuration model.
 *
 * @since  1.6
 */
class ConfigModel extends FormModel
{
    /**
     * Method to auto-populate the model state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the model
     * configuration flag to ignore the request is set.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState()
    {
        $user = $this->getCurrentUser();

        $this->setState('user.id', $user->get('id'));

        // Load the parameters.
        $params = ComponentHelper::getParams('com_messages');
        $this->setState('params', $params);
    }

    /**
     * Method to get a single record.
     *
     * @return  mixed  Object on success, false on failure.
     *
     * @since   1.6
     */
    public function &getItem()
    {
        $item   = new CMSObject();
        $userid = (int) $this->getState('user.id');

        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $query->select(
            [
                $db->quoteName('cfg_name'),
                $db->quoteName('cfg_value'),
            ]
        )
            ->from($db->quoteName('#__messages_cfg'))
            ->where($db->quoteName('user_id') . ' = :userid')
            ->bind(':userid', $userid, ParameterType::INTEGER);

        $db->setQuery($query);

        try {
            $rows = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        foreach ($rows as $row) {
            $item->set($row->cfg_name, $row->cfg_value);
        }

        $this->preprocessData('com_messages.config', $item);

        return $item;
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  \Joomla\CMS\Form\Form|bool  A Form object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_messages.config', 'config', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function save($data)
    {
        $db = $this->getDatabase();

        if ($userId = (int) $this->getState('user.id')) {
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__messages_cfg'))
                ->where($db->quoteName('user_id') . ' = :userid')
                ->bind(':userid', $userId, ParameterType::INTEGER);
            $db->setQuery($query);

            try {
                $db->execute();
            } catch (\RuntimeException $e) {
                $this->setError($e->getMessage());

                return false;
            }

            if (count($data)) {
                $query = $db->getQuery(true)
                    ->insert($db->quoteName('#__messages_cfg'))
                    ->columns(
                        [
                            $db->quoteName('user_id'),
                            $db->quoteName('cfg_name'),
                            $db->quoteName('cfg_value'),
                        ]
                    );

                foreach ($data as $k => $v) {
                    $query->values(
                        implode(
                            ',',
                            $query->bindArray(
                                [$userId , $k, $v],
                                [ParameterType::INTEGER, ParameterType::STRING, ParameterType::STRING]
                            )
                        )
                    );
                }

                $db->setQuery($query);

                try {
                    $db->execute();
                } catch (\RuntimeException $e) {
                    $this->setError($e->getMessage());

                    return false;
                }
            }

            return true;
        } else {
            $this->setError('COM_MESSAGES_ERR_INVALID_USER');

            return false;
        }
    }
}
