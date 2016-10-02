<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_jtestreport
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! test report model
 *
 * @since  __DEPLOY_VERSION__
 */
class JtestreportModelDefault extends JModelAdmin
{
	/**
	 * @var null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $env = null;

	/**
	 * @var null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $extensions = null;

	/**
	 * @var null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $enabledExtensions = null;

	/**
	 * @var null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $unenabledExtensions = null;


	/**
	 * Get some other form data
	 *
	 * @return array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getMiscData()
	{
		$result = array();

		$formData = $this->getFormData();

		$fields = array ('name', 'email', 'github', 'livesite', 'prodenv', 'updatefrom', 'overallresult');

		foreach ($fields as $field)
		{
			$result[$field] = $formData[$field];
		}

		return $result;
	}

	/**
	 * Prepare data to be send
	 *
	 * @return mixed|string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function prepareData()
	{
		$data = array();

		$data['env']        = $this->getEnv();
		$data['user']       = $this->getMiscData();

		$data['extensions'] = $this->getExtensions();

		$enabledExtensions = $this->getEnabledExtensions();

		$formData = $this->getFormData();

		foreach ($enabledExtensions as $enabledExtension)
		{
			$id = $enabledExtension->extension_id;

			$data['extensions'][$id]->tested = false;

			if (isset($formData[$id]))
			{
				$data['extensions'][$id]->tested = $formData['eid' . $id] == "1";
			}
		}

		return $data;
	}

	/**
	 * Get the form data
	 *
	 * @return mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function getFormData()
	{
		return JFactory::getApplication()->input->post->get('jform', array(), 'array');
	}

	/**
	 * Get environment information
	 *
	 * @return array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getEnv()
	{
		$db = JFactory::getDbo();

		return array(
			'php_version' => PHP_VERSION,
			'db_type'     => $db->name,
			'db_version'  => $db->getVersion(),
			'cms_version' => JVERSION,
			'server_os'   => php_uname('s') . ' ' . php_uname('r')
		);
	}

	/**
	 * Get all installed extensions
	 *
	 * @return mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getExtensions()
	{
		if (!is_null($this->extensions))
		{
			return $this->extensions;
		}

		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__extensions'))
			->order('type');

		$db->setQuery($query);
		$this->extensions = $db->loadObjectList('extension_id');

		// Get the version of the extensions
		foreach ($this->extensions as &$extension)
		{
			$extension->version = JText::_('COM_JTESTREPORT_UNKNOWN_VERSION');

			unset($extension->params);

			if (isset($extension->manifest_cache))
			{
				$manifest = json_decode($extension->manifest_cache);

				unset($extension->manifest_cache);

				if (isset($manifest->version))
				{
					$extension->version = $manifest->version;
				}
			}
		}

		$this->enabledExtensions   = array();
		$this->unenabledExtensions = array();

		foreach ($this->extensions as $extension)
		{
			if ($extension->enabled == 1 && ! in_array($extension->type, array('file', 'library')))
			{
				$this->enabledExtensions[] = $extension;
			}
			else
			{
				$this->unenabledExtensions[] = $extension;
			}
		}
		return $this->extensions;
	}

	/**
	 * Getter for enabled extensions
	 *
	 * @return null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getEnabledExtensions()
	{
		if (is_null($this->enabledExtensions))
		{
			$this->getExtensions();
		}

		return $this->enabledExtensions;
	}

	/**
	 * Getter for unenabled extensions
	 *
	 * @return null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getUnenabledExtensions()
	{
		if (is_null($this->unenabledExtensions))
		{
			$this->getExtensions();
		}

		return $this->unenabledExtensions;
	}

	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm|boolean  A JForm object on success, false on failure
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_jtestreport.default', 'default', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}
}
