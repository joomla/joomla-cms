<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_services
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Model for the global configuration
 *
 * @package     Joomla.Site
 * @subpackage  com_services
 * @since       3.2
 */
class ServicesModelConfig extends JModelForm
{
	/**
	 * Method to get a form object.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed	A JForm object on success, false on failure
	 *
	 * @since	3.2
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_services.config', 'config', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the configuration data.
	 *
	 * This method will load the global configuration data straight from
	 * JConfig. If configuration data has been saved in the session, that
	 * data will be merged into the original data, overwriting it.
	 *
	 * @return	array		An array containg all global config data.
	 *
	 * @since	3.2
	 */
	public function getData()
	{

		// Create a HTTP client
		$options = isset($options) ? $options : new JRegistry;
		$client = isset($client) ? $client : new JHttp($options);

		// Backend com_config URL
		$path = JURI::base() . 'administrator/index.php?option=com_config&format=json';

		// Create Authentication header
		$username = JFactory::getUser()->username;
		$password = JFactory::getUser()->password;

		$header = array('Authorization' => 'Basic ' . base64_encode($username . ':' . $password));

		// Send GET request to back-end
		$response = $client->get($path, $header);

		// Decode JSON string returned
		$data = json_decode($response->body, true);

		// Check for data in the session.
		$temp = JFactory::getApplication()->getUserState('com_services.config.global.data');

		// Merge in the session data.
		if (!empty($temp))
		{
			$data = array_merge($data, $temp);
		}

		return $data;
	}

	/**
	 * Method to save the configuration data.
	 *
	 * @param   array  $data  An array containing all global config data.
	 *
	 * @return	bool	True on success, false on failure.
	 *
	 * @since	3.2
	 */
	public function save($data)
	{
		// Clear cache of com_config component.
		$this->cleanCache('_system');

		// Create HTTP Client
		$options = isset($options) ? $options : new JRegistry;
		$client = isset($client) ? $client : new JHttp($options);

		// Backend com_config URL
		$path = JURI::base() . 'administrator/index.php?option=com_config&controller=application.apply';

		// Create Authentication header
		$username = JFactory::getUser()->username;
		$password = JFactory::getUser()->password;

		$header = array('Authorization' => 'Basic ' . base64_encode($username . ':' . $password));

		// Add $data as [jform]=>array()
		$content = array ('jform' => $data);

		// Send POST request to back-end
		$response = $client->post($path, $content, $header);

		// Check for response code
		if ($response->code == 303 || $response->code == 200)
		{
			return true;
		}
		else
		{
			return false;
		}

	}

}
