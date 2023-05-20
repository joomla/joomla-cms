<?php

/**
 * @package       JED
 *
 * @subpackage    VEL
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Site\Model;

// No direct access.
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Jed\Component\Jed\Site\Helper\JedHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

use function defined;

/**
 * VEL Report Model Class.
 *
 * @since  4.0
 */
class VelreportModel extends ItemModel
{
    /** Data Table
     * @since 4.0.0
     **/
    private string $dbtable = "#__jed_vel_report";

    /**
     * Method to check in an item.
     *
     * @param   int|null  $id  The id of the row to check out.
     *
     * @return  boolean True on success, false on failure.
     *
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function checkin(int $id = null): bool
    {
        // Get the id.
        $id = (!empty($id)) ? $id : (int) $this->getState('velreport.id');
        if ($id || JedHelper::userIDItem($id, $this->dbtable) || JedHelper::isAdminOrSuperUser()) {
            if ($id) {
                // Initialise the table
                $table = $this->getTable();

                // Attempt to check the row in.
                if (method_exists($table, 'checkin')) {
                    if (!$table->checkin($id)) {
                        return false;
                    }
                }
            }

            return true;
        } else {
            throw new Exception(Text::_("JERROR_ALERTNOAUTHOR"), 401);
        }
    }

    /**
     * Method to get a single record.
     *
     * @param   int|null  $pk  The id of the object to get.
     *
     * @return  object    Object on success, false on failure.
     *
     * @since 4.0.0
     * @throws Exception
     */
    public function getItem($pk = null)
    {
        $app = Factory::getApplication();
        if ($this->item === null) {
            $this->item = false;

            if (empty($pk)) {
                $pk = $this->getState('velreport.id');
            }

            // Get a level row instance.
            $table = $this->getTable();

            // Attempt to load the row.
            $keys = ["id" => $pk, "created_by" => JedHelper::getUser()->id];

            if ($table->load($keys)) {
                if (empty($result) || JedHelper::isAdminOrSuperUser()) {
                    // Check published state.
                    if ($published = $this->getState('filter.published')) {
                        if (isset($table->state) && $table->state != $published) {
                            $app->enqueueMessage("Item is not published", "message");

                            return null;
                        }
                    }

                    // Convert the JTable to a clean JObject.
                    $properties = $table->getProperties(1);

                    $this->item = ArrayHelper::toObject($properties, 'JObject');
                } else {
                    $app->enqueueMessage("Sorry you did not create that report item", "message");

                    return null;
                    //throw new Exception(Text::_("JERROR_ALERTNOAUTHOR"), 401);
                }
            }

            if (empty($this->item)) {
                $app->enqueueMessage(Text::_('COM_JED_SECURITY_CANT_LOAD'), "message");

                return null;
            }
        }


        if (!JedHelper::is_blank($this->item->pass_details_ok)) {
            $this->item->pass_details_ok = Text::_('COM_JED_VEL_REPORTS_FIELD_PASS_DETAILS_OK_OPTION_' . $this->item->pass_details_ok);
        }

        if (!JedHelper::is_blank($this->item->vulnerability_type)) {
            $this->item->vulnerability_type = Text::_('COM_JED_VEL_GENERAL_FIELD_VULNERABILITY_TYPE_OPTION_' . $this->item->vulnerability_type);
        }

        if (!JedHelper::is_blank($this->item->exploit_type)) {
            $this->item->exploit_type = Text::_('COM_JED_VEL_GENERAL_FIELD_EXPLOIT_TYPE_OPTION_' . $this->item->exploit_type);
        }

        if (!JedHelper::is_blank($this->item->vulnerability_actively_exploited)) {
            $this->item->vulnerability_actively_exploited = Text::_('COM_JED_VEL_REPORTS_FIELD_VULNERABILITY_ACTIVELY_EXPLOITED_OPTION_' . $this->item->vulnerability_actively_exploited);
        }

        if (!JedHelper::is_blank($this->item->vulnerability_publicly_available)) {
            $this->item->vulnerability_publicly_available = Text::_('COM_JED_VEL_REPORTS_FIELD_VULNERABILITY_PUBLICLY_AVAILABLE_OPTION_' . $this->item->vulnerability_publicly_available);
        }

        if (!JedHelper::is_blank($this->item->developer_communication_type)) {
            $this->item->developer_communication_type = Text::_('COM_JED_VEL_GENERAL_FIELD_DEVELOPER_COMMUNICATION_TYPE_OPTION_' . $this->item->developer_communication_type);
        }

        if (!JedHelper::is_blank($this->item->consent_to_process)) {
            $this->item->consent_to_process = Text::_('COM_JED_GENERAL_CONSENT_TO_PROCESS_OPTION_' . $this->item->consent_to_process);
        }

        if (!JedHelper::is_blank($this->item->passed_to_vel)) {
            $this->item->passed_to_vel = Text::_('COM_JED_VEL_GENERAL_FIELD_PASSED_TO_VEL_OPTION_' . $this->item->passed_to_vel);
        }

        if (!JedHelper::is_blank($this->item->data_source)) {
            $this->item->data_source = Text::_('COM_JED_VEL_GENERAL_FIELD_DATA_SOURCE_OPTION_' . $this->item->data_source);
        }

        if (isset($this->item->created_by)) {
            $this->item->created_by_name = JedHelper::getUserById($this->item->created_by)->name;
        }

        if (isset($this->item->modified_by)) {
            $this->item->modified_by_name = JedHelper::getUserById($this->item->modified_by)->name;
        }

        return $this->item;
    }

    /**
     * Get an instance of Table class
     *
     * @param   string  $name
     * @param   string  $prefix  Prefix for the table class name. Optional.
     * @param   array   $options
     *
     * @return  Table Table if success, throws exception on failure.
     * @since 4.0.0
     * @throws Exception
     */
    public function getTable($name = 'Velreport', $prefix = 'Administrator', $options = []): Table
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return void
     *
     * @since 4.0.0
     *
     * @throws Exception
     */
    protected function populateState()
    {
        $app  = Factory::getApplication();
        $user = JedHelper::getUser();

        // Check published state
        if ((!$user->authorise('core.edit.state', 'com_jed')) && (!$user->authorise('core.edit', 'com_jed'))) {
            $this->setState('filter.published', 1);
            $this->setState('filter.archived', 2);
        }

        // Load state from the request userState on edit or from the passed variable on default
        if (Factory::getApplication()->input->get('layout') == 'edit') {
            $id = Factory::getApplication()->getUserState('com_jed.edit.velreport.id');
        } else {
            $id = Factory::getApplication()->input->get('id');
            Factory::getApplication()->setUserState('com_jed.edit.velreport.id', $id);
        }

        $this->setState('velreport.id', $id);

        // Load the parameters.
        $params       = $app->getParams();
        $params_array = $params->toArray();

        if (isset($params_array['item_id'])) {
            $this->setState('velreport.id', $params_array['item_id']);
        }

        $this->setState('params', $params);
    }
}
