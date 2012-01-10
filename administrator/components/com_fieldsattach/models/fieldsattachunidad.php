<?php
/**
 * @version		$Id: fieldattachunidad.php 15 2011-09-02 18:37:15Z cristian $
 * @package		fieldsattach
 * @subpackage		Components
 * @copyright		Copyright (C) 2011 - 2020 Open Source Cristian Grañó, Inc. All rights reserved.
 * @author		Cristian Grañó
 * @link		http://joomlacode.org/gf/project/fieldsattach_1_6/
 * @license		License GNU General Public License version 2 or later
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
jimport('joomla.form.form');

/**
 * fieldsattach Model
 */
class fieldsattachModelfieldsattachunidad extends JModelAdmin
{
        public $id;
	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param	array	$data	An array of input data.
	 * @param	string	$key	The name of the key for the primary key.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Check specific edit permission then general edit permission.
		return JFactory::getUser()->authorise('core.edit', 'com_fieldsattach.message.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
	}
	/**
	 * Returns a Table object, always creating it
	 *
	 * @param	type	$type	The table type to instantiate
	 * @param	string	$prefix	A prefix for the table class name. Optional.
	 * @param	array	$config	Configuration array for model. Optional.
	 *
	 * @return	JTable	A database object
	 * @since	1.6
	 */

        
	public function getTable($type = 'fieldsattachunidad', $prefix = 'fieldsattachTable', $config = array())
	{
                
		return JTable::getInstance($type, $prefix, $config);
	}


	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_fieldsattach.fieldsattachunidad', 'fieldsattachunidad', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{

			return false;
		}
              
		return $form;
	}
        /**
	 * Method to get a single record.
	 *
	 * @param	integer	$pk	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) {
			// Convert the params field to an array.
			$registry = new JRegistry;
			//$registry->loadJSON($item->metadata);

			//$item->metadata = $registry->toArray();
		}
               // echo "item ".$item->id;
		return $item;
	}
	/**
	 * Method to get the script that have to be included on the form
	 *
	 * @return string	Script files
	 */
	public function getScript() 
	{
		return 'administrator/components/com_fieldsattach/models/forms/fieldsattach.js';
	}
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_fieldsattachunidad.edit.fieldsattachunidad.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
		}
		return $data;
	}

        /**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	public function store()
	{
		// Check the session for previously entered form data.  
                $data = JRequest::getVar('jform', array(), 'post', 'array'); 
                
                $row = $this->getTable();
                if (!$row->bind( $data ))
                    {
                    return JError::raiseWarning( 500, $row->getError() );
                    }
                if(!$row->store())
                {
                    return JError::raiseWarning( 500, $row->getError() );
                }

                $this->id = $row->id;
                
                return true;

	}

        /**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	public function delete()
        {
             $row = $this->getTable();
             $data           = JRequest::getVar('cid', array(), 'post', 'array');

                foreach ($data  as $id)
                { 
                    if(!$row->delete($id))
                    {
                        return JError::raiseWarning( 500, $row->getError() );
                    }
                }
             return true;
        }

         
}
