<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;

// load base model
jimport('joomla.application.component.modelform');

use Joomla\Utilities\ArrayHelper;

class JceModelConfig extends JModelForm
{    
    /**
     * Returns a Table object, always creating it.
     *
     * @param type   $type   The table type to instantiate
     * @param string $prefix A prefix for the table class name. Optional
     * @param array  $config Configuration array for model. Optional
     *
     * @return JTable A database object
     *
     * @since   1.6
     */
    public function getTable($type = 'Extension', $prefix = 'JTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get a form object.
     *
     * @param array $data     Data for the form
     * @param bool  $loadData True if the form is to load its own data (default case), false if not
     *
     * @return mixed A JForm object on success, false on failure
     *
     * @since	1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_jce.config', 'config', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return mixed The data for the form
     *
     * @since	1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_jce.config.data', array());

        if (empty($data)) {
            $data = $this->getData();
        }

        return $data;
    }

    /**
     * Method to get the configuration data.
     *
     * This method will load the global configuration data straight from
     * JConfig. If configuration data has been saved in the session, that
     * data will be merged into the original data, overwriting it.
     *
     * @return array An array containg all global config data
     *
     * @since	1.6
     */
    public function getData()
    {
        // Get the editor data
        $plugin = JPluginHelper::getPlugin('editors', 'jce');

        // json_decode
        $json = json_decode($plugin->params, true);

        if (empty($json)) {
            $json = array();
        }

        array_walk($json, function(&$value, $key) {
            if (is_numeric($value)) {
                $value = $value + 0;
            }
        });

        $data = new StdClass;
        $data->params = $json;

        return $data;
    }

    /**
     * Method to save the form data.
     *
     * @param   array  The form data
     *
     * @return bool True on success
     *
     * @since    2.7
     */
    public function save($data)
    {
        $table = $this->getTable();

        $id = $table->find(array(
            'type'      => 'plugin',
            'element'   => 'jce',
            'folder'    => 'editors'
        )); 

        if (!$id) {
            $this->setError('Invalid plugin');
            return false;
        }

        // Load the previous Data
		if (!$table->load($id))
		{
			$this->setError($table->getError());
            return false;
        }

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError($table->getError());
            return false;
		}

		// Check the data.
		if (!$table->check())
		{
			$this->setError($table->getError());
            return false;
        }
        
        // Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());
            return false;
        }

        return true;
    }
}