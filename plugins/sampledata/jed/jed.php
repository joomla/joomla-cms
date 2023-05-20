<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Sampledata.JED
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Categories\Administrator\Model\CategoryModel;
use Joomla\Database\DatabaseDriver;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Sampledata - Jed Plugin
 *
 * @since  4.0.0
 */
class PlgSampledataJed extends CMSPlugin
{
    /**
     * Database object
     *
     * @var    DatabaseDriver
     *
     * @since  3.8.0
     */
    protected $db;

    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     *
     * @since  3.8.0
     */
    protected $autoloadLanguage = true;

    public function __construct(&$subject, $config = [])
    {
        parent::__construct($subject, $config);
        $this->setApplication(Factory::getApplication());
    }

    /**
     * Get an overview of the proposed sampledata.
     *
     * @return  object  Object containing the name, title, description, icon and steps.
     *
     * @since  3.8.0
     */
    public function onSampledataGetOverview()
    {
        $data              = new stdClass();
        $data->name        = $this->_name;
        $data->title       = Text::_('PLG_SAMPLEDATA_JED_OVERVIEW_TITLE');
        $data->description = Text::_('PLG_SAMPLEDATA_JED_OVERVIEW_DESC');
        $data->icon        = 'money';
        $data->steps       = 7;

        return $data;
    }

    /**
     * Make sure we don't overwrite current admin user, move them to user_id=5
     *
     * @return  array|void  Will be converted into the JSON response to the module.
     *
     * @since  3.8.0
     */
    public function onAjaxSampledataApplyStep1()
    {
        if ($this->getApplication()->getInput()->get('type') !== $this->_name) {
            return;
        }

        $this->importFile(__DIR__ . '/sql/step1.sql');

        $response            = [];
        $response['success'] = true;
        $response['message'] = Text::_('PLG_SAMPLEDATA_JED_STEP1_SUCCESS');

        return $response;
    }

    /**
     * First step to enter the sampledata. Tags
     *
     * @return  array|void  Will be converted into the JSON response to the module.
     *
     * @since  3.8.0
     */
    public function onAjaxSampledataApplyStep2()
    {
        if ($this->getApplication()->getInput()->get('type') !== $this->_name) {
            return;
        }

        $this->importFile(__DIR__ . '/sql/step2.sql');

        $response            = [];
        $response['success'] = true;
        $response['message'] = Text::_('PLG_SAMPLEDATA_JED_STEP2_SUCCESS');

        return $response;
    }

    /**
     * Second step to enter the sampledata. Banners
     *
     * @return  array|void  Will be converted into the JSON response to the module.
     *
     * @since  3.8.0
     */
    public function onAjaxSampledataApplyStep3()
    {
        if ($this->getApplication()->getInput()->get('type') !== $this->_name) {
            return;
        }

        $this->importFile(__DIR__ . '/sql/step3.sql');

        $response            = [];
        $response['success'] = true;
        $response['message'] = Text::_('PLG_SAMPLEDATA_JED_STEP3_SUCCESS');

        return $response;
    }

    /**
     * Third step to enter the sampledata. Content 1/2
     *
     * @return  array|void  Will be converted into the JSON response to the module.
     *
     * @since  3.8.0
     */
    public function onAjaxSampledataApplyStep4()
    {
        if ($this->getApplication()->getInput()->get('type') !== $this->_name) {
            return;
        }

        $this->importFile(__DIR__ . '/sql/step4.sql');

        $response            = [];
        $response['success'] = true;
        $response['message'] = Text::_('PLG_SAMPLEDATA_JED_STEP4_SUCCESS');

        return $response;
    }

    /**
     * Fourth step to enter the sampledata. Content 2/2
     *
     * @return  array|void  Will be converted into the JSON response to the module.
     *
     * @since  4.0.0
     */
    public function onAjaxSampledataApplyStep5()
    {
        if ($this->getApplication()->getInput()->get('type') !== $this->_name) {
            return;
        }

        $this->importFile(__DIR__ . '/sql/step5.sql');

        $response            = [];
        $response['success'] = true;
        $response['message'] = Text::_('PLG_SAMPLEDATA_JED_STEP5_SUCCESS');

        return $response;
    }

    /**
     * Fifth step to enter the sampledata. Contacts
     *
     * @return  array|void  Will be converted into the JSON response to the module.
     *
     * @since  3.8.0
     */
    public function onAjaxSampledataApplyStep6()
    {
        if ($this->getApplication()->getInput()->get('type') !== $this->_name) {
            return;
        }

        $this->importFile(__DIR__ . '/sql/step6.sql');

        $response            = [];
        $response['success'] = true;
        $response['message'] = Text::_('PLG_SAMPLEDATA_JED_STEP6_SUCCESS');

        return $response;
    }

    /**
     * Sixth step to enter the sampledata. Newsfeed.
     *
     * @return  array|void  Will be converted into the JSON response to the module.
     *
     * @since  3.8.0
     */
    public function onAjaxSampledataApplyStep7()
    {
        if ($this->getApplication()->getInput()->get('type') !== $this->_name) {
            return;
        }

        $this->importFile(__DIR__ . '/sql/step7.sql');

        $response            = [];
        $response['success'] = true;
        $response['message'] = Text::_('PLG_SAMPLEDATA_JED_STEP7_SUCCESS');

        return $response;
    }

    protected function importFile($file)
    {
        $return = true;

        // Get the contents of the schema file.
        if (!($buffer = file_get_contents($file))) {
            Factory::getApplication()->enqueueMessage(Text::_('INSTL_SAMPLE_DATA_NOT_FOUND'), 'error');

            return false;
        }

        // Get an array of queries from the schema and process them.
        $queries = $this->splitQueries($buffer);

        foreach ($queries as $query) {
            // Trim any whitespace.
            $query = trim($query);

            // If the query isn't empty and is not a MySQL or PostgreSQL comment, execute it.
            if (!empty($query) && ($query[0] != '#') && ($query[0] != '-')) {
                // Execute the query.
                $this->db->setQuery($query);

                try {
                    $this->db->execute();
                } catch (\RuntimeException $e) {
                    Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

                    $return = false;
                }
            }
        }

        return $return;
    }

    /**
     * Method to split up queries from a schema file into an array.
     *
     * @param   string  $query  SQL schema.
     *
     * @return  array  Queries to perform.
     *
     * @since   3.1
     */
    protected function splitQueries($query)
    {
        $buffer    = [];
        $queries   = [];
        $in_string = false;

        // Trim any whitespace.
        $query = trim($query);

        // Remove comment lines.
        $query = preg_replace("/\n\#[^\n]*/", '', "\n" . $query);

        // Remove PostgreSQL comment lines.
        $query = preg_replace("/\n\--[^\n]*/", '', "\n" . $query);

        // Find function.
        $funct = explode('CREATE OR REPLACE FUNCTION', $query);

        // Save sql before function and parse it.
        $query = $funct[0];

        // Parse the schema file to break up queries.
        for ($i = 0; $i < strlen($query) - 1; $i++) {
            if ($query[$i] == ';' && !$in_string) {
                $queries[] = substr($query, 0, $i);
                $query     = substr($query, $i + 1);
                $i         = 0;
            }

            if ($in_string && ($query[$i] == $in_string) && $buffer[1] != "\\") {
                $in_string = false;
            } elseif (!$in_string && ($query[$i] == '"' || $query[$i] == "'") && (!isset($buffer[0]) || $buffer[0] != "\\")) {
                $in_string = $query[$i];
            }

            if (isset($buffer[1])) {
                $buffer[0] = $buffer[1];
            }

            $buffer[1] = $query[$i];
        }

        // If the is anything left over, add it to the queries.
        if (!empty($query)) {
            $queries[] = $query;
        }

        // Add function part as is.
        for ($f = 1, $fMax = count($funct); $f < $fMax; $f++) {
            $queries[] = 'CREATE OR REPLACE FUNCTION ' . $funct[$f];
        }

        return $queries;
    }
}
