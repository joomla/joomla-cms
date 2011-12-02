<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-2.0/JG/trunk/administrator/components/com_joomgallery/models/mini.php $
// $Id: mini.php 3378 2011-10-07 18:37:56Z aha $
/****************************************************************************************\
**   JoomGallery 2                                                                      **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2011  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

jimport('joomla.application.component.modeladmin');

/**
 * Mini Joom model
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class LanguagesModelOverride extends JModelAdmin
{
	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 *
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_languages.override', 'override', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
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
		$data = JFactory::getApplication()->getUserState('com_languages.edit.override.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 * @since   11.1
	 */
	public function getItem($pk = null)
	{
		require_once JPATH_COMPONENT.'/helpers/languages.php';

		$pk	= (!empty($pk)) ? $pk : JRequest::getCmd('id');
    $filename = constant('JPATH_'.strtoupper($this->getState('filter.client'))).DS.'language'.DS.'overrides'.DS.$this->getState('filter.language', 'en-GB').'.override.ini';
		$strings = LanguagesHelper::parseFile($filename);

		$result = new stdClass();
		$result->key = '';
		$result->override = '';
		if(isset($strings[$pk]))
		{
			$result->key = $pk;
			$result->override =  $strings[$pk];
		}
		
		return $result;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 * @since   11.1
	 */
	public function save($data)
	{
    $app = JFactory::getApplication();
		require_once JPATH_COMPONENT.'/helpers/languages.php';

		$client = $app->getUserState('com_languages.overrides.filter.client', 0) ? 'administrator' : 'site';
		$language = $app->getUserState('com_languages.overrides.filter.language', 'en-GB');

    $filename = constant('JPATH_'.strtoupper($client)).DS.'language'.DS.'overrides'.DS.$language.'.override.ini';
		$strings = LanguagesHelper::parseFile($filename);

		if(isset($strings[$data['id']]))
		{
			if($data['key'] == $data['id'])
			{
				$strings[$data['key']] = $data['override'];
			}
			else
			{
				unset($strings[$data['id']]);
				$strings = array($data['key'] => $data['override']) + $strings;
			}
		}
		else
		{
			$strings = array($data['key'] => $data['override']) + $strings;
		}

		$registry = new JRegistry();
 		$registry->loadObject($strings);

 		if(!JFile::write($filename, $registry->toString('INI')))
    {
      return false;
    }

		return true;
	}

	/**
	 * Stock method to auto-populate the model state.
	 *
	 * @return  void
	 * @since   11.1
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();

		$client = $app->getUserStateFromRequest('com_languages.overrides.filter.client', 'filter_client', 0, 'int') ? 'administrator' : 'site';
		$this->setState('filter.client', $client);

		$language = $app->getUserStateFromRequest('com_languages.overrides.filter.language', 'filter_language', 'en-GB', 'cmd');
		$this->setState('filter.language', $language);

		// Load the parameters.
		$value = JComponentHelper::getParams($this->option);
		$this->setState('params', $value);
	}
}