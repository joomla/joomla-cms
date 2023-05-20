<?php

/**
 * @package       JED
 *
 * @subpackage    VEL
 *
 * @copyright     (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Administrator\Model;

// No direct access.
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;

/**
 * VEL Vulnerable Item model.
 *
 * @since  4.0.0
 */
class VelvulnerableitemModel extends AdminModel
{
    /**
     * @var    string    Alias to manage history control
     *
     * @since  4.0.0
     */
    public $typeAlias = 'com_jed.velvulnerableitem';
    /**
     * @var      string    The prefix to use with controller messages.
     *
     * @since  4.0.0
     */
    protected $text_prefix = 'COM_JED';
    /**
     * @var    mixed  Item data
     *
     * @since  4.0.0
     */
    protected mixed $item = null;

    /**
     * @var int ID Of VEL Report
     * @since 4.0.0
     */
    protected int $idVelReport = -1;

    /**
     * @var int ID Of VEL linked item (report, abandoned report or developer update
     * @since 4.0.0
     */
    protected int $linked_item_id = -1;

    /**
     * Method to get the record form.
     *
     * @param   array    $data      An optional array of data for the form to interogate.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form|bool  A Form object on success, false on failure
     *
     * @since  4.0.0
     *
     * @throws Exception
     */
    public function getForm($data = [], $loadData = true, $formname = 'jform'): Form|bool
    {

        // Get the form.
        $form = $this->loadForm(
            'com_jed.velvulnerableitem',
            'velvulnerableitem',
            ['control'        => $formname,
                  'load_data' => $loadData,
            ]
        );


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
     * @return  object|bool    Object on success, false on failure.
     *
     * @since  4.0.0
     * @throws Exception
     */
    public function getItem($pk = null): CMSObject|bool
    {

        return parent::getItem($pk);
    }

    /**
     * Returns a reference to the Table object, always creating it.
     *
     * @param   string  $name     The table type to instantiate
     * @param   string  $prefix   A prefix for the table class name. Optional.
     * @param   array   $options  Configuration array for model. Optional.
     *
     * @return    Table    A database object
     *
     * @since  4.0.0
     * @throws Exception
     */
    public function getTable($name = 'Velvulnerableitem', $prefix = 'Administrator', $options = []): Table
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Gets VEL Linked Reports
     * @return array
     *
     * @since version
     * @throws Exception
     */
    public function getVELLinkedReports(): array
    {

        $input = Factory::getApplication()->input;

        $vel_item_id = $input->get('id');


        $output['velreport']          = null;
        $output['veldeveloperupdate'] = null;
        $output['velabandonware']     = null;

        $velReportData  = $this->getVelReportData($vel_item_id);
        //$velReportModel = BaseDatabaseModel::getInstance('Velreport', 'JedModel', ['ignore_request' => true]);
        $velReportModel = new VelreportModel();
        $velReportForm  = $velReportModel->getForm($velReportData, false);
        $velReportForm->bind($velReportData);

        $output['velreport']['data']  = $velReportData;
        $output['velreport']['model'] = $velReportModel;
        $output['velreport']['form']  = $velReportForm;

        $velDeveloperUpdateData  = $this->getvelDeveloperUpdateData($vel_item_id);
        //$velDeveloperUpdateModel = BaseDatabaseModel::getInstance('Veldeveloperupdate', 'JedModel', ['ignore_request' => true]);
        $velDeveloperUpdateModel = new VeldeveloperupdateModel();
        $velDeveloperUpdateForm  = $velDeveloperUpdateModel->getForm($velDeveloperUpdateData, false);
        $velDeveloperUpdateForm->bind($velDeveloperUpdateData);

        $output['veldeveloperupdate']['data']  = $velDeveloperUpdateData;
        $output['veldeveloperupdate']['model'] = $velDeveloperUpdateModel;
        $output['veldeveloperupdate']['form']  = $velDeveloperUpdateForm;

        $velAbandonwareDataData  = $this->getvelAbandonwareData($vel_item_id);
        //$velAbandonwareDataModel = BaseDatabaseModel::getInstance('Velabandonedreport', 'JedModel', ['ignore_request' => true]);
        $velAbandonwareDataModel = new VelabandonedreportModel();
        $velAbandonwareDataForm  = $velAbandonwareDataModel->getForm($velAbandonwareDataData, false);
        $velAbandonwareDataForm->bind($velAbandonwareDataData);

        $output['velabandonware']['data']  = $velAbandonwareDataData;
        $output['velabandonware']['model'] = $velAbandonwareDataModel;
        $output['velabandonware']['form']  = $velAbandonwareDataForm;

        return $output;
    }

    /**
     * Get VEL Abandoned Report Data
     *
     * @param   int  $vel_item_id
     *
     * @return array
     *
     * @since 4.0.0
     */
    public function getVelAbandonwareData(int $vel_item_id): array
    {
        // Create a new query object.
        $db = $this->getDatabase();

        $query = $db->getQuery(true);

        // Select some fields
        $query->select('a.*');

        // From the vel_report table
        $query->from($db->quoteName('#__jed_vel_abandoned_report', 'a'));

        $query->where('a.vel_item_id = ' . $vel_item_id);


        // Load the items
        $db->setQuery($query);
        $db->execute();
        if ($db->getNumRows()) {
            return $db->loadObjectList();
        }

        return [];
    }

    /**
     * getVelDeveloperUpdateData - gets the entry for a specific developer update
     *
     * @param   int  $vel_item_id
     *
     * @return array
     *
     * @since version
     */
    public function getVelDeveloperUpdateData(int $vel_item_id)
    {
        // Create a new query object.
        $db = $this->getDatabase();

        $query = $db->getQuery(true);

        // Select some fields
        $query->select('a.*');

        // From the vel_report table
        $query->from($db->quoteName('#__jed_vel_developer_update', 'a'));


        $query->where('a.vel_item_id = ' . $vel_item_id);


        // Load the items
        $db->setQuery($query);
        $db->execute();
        if ($db->getNumRows()) {
            return $db->loadObjectList();
        }

        return [];
    }

    /**
     * getVelReportData - gets the entry for a specific vulnerable item report
     *
     * @param   int  $vel_item_id
     *
     * @return array
     *
     * @since version
     */
    public function getVelReportData(int $vel_item_id): array
    {
        // Create a new query object.
        $db = $this->getDatabase();

        $query = $db->getQuery(true);

        // Select some fields
        $query->select('a.*');

        // From the vel_report table
        $query->from($db->quoteName('#__jed_vel_report', 'a'));


        $query->where('a.vel_item_id = ' . $vel_item_id);


        // Load the items
        $db->setQuery($query);
        $db->execute();
        if ($db->getNumRows()) {
            return $db->loadObjectList();
        }

        return [];
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return   mixed  The data for the form.
     *
     * @since  4.0.0
     *
     * @throws Exception
     */
    protected function loadFormData(): mixed
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_jed.edit.velvulnerableitem.data', []);

        if (empty($data)) {
            if ($this->item === null) {
                $this->item = $this->getItem();
            }

            $data = $this->item;


            // Support for multiple or not foreign key field: status
            $array = [];

            foreach ((array) $data->status as $value) {
                if (!is_array($value)) {
                    $array[] = $value;
                }
            }
            if (!empty($array)) {
                $data->status = $array;
            }

            // Support for multiple or not foreign key field: risk_level
            $array = [];

            foreach ((array) $data->risk_level as $value) {
                if (!is_array($value)) {
                    $array[] = $value;
                }
            }
            if (!empty($array)) {
                $data->risk_level = $array;
            }

            // Support for multiple or not foreign key field: exploit_type
            $array = [];

            foreach ((array) $data->exploit_type as $value) {
                if (!is_array($value)) {
                    $array[] = $value;
                }
            }
            if (!empty($array)) {
                $data->exploit_type = $array;
            }

            // Support for multiple or not foreign key field: discoverer_public
            $array = [];

            foreach ((array) $data->discoverer_public as $value) {
                if (!is_array($value)) {
                    $array[] = $value;
                }
            }
            if (!empty($array)) {
                $data->discoverer_public = $array;
            }
        }

        return $data;
    }
}
