<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Config Module model.
 *
 * @package     Joomla.Site
 * @subpackage  com_config
 * @since       3.2
 */
class ConfigModelModules extends ConfigModelForm
{

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('administrator');

		// Load the User state.
		$pk = $app->input->getInt('id');

		$state = $this->loadState();

		$state->set('module.id', $pk);

		$this->setState($state);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 *
	 * @since   3.2
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_config.modules', 'modules', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		$form->setFieldAttribute('position', 'client',  'site');

		return $form;
	}

	/**
	 * Method to preprocess the form
	 *
	 * @param   JForm   $form   A form object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @throws  Exception if there is an error loading the form.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		jimport('joomla.filesystem.path');

		$lang     = JFactory::getLanguage();

		$module = $this->getState()->get('module.name');
		$basePath = JPATH_BASE;

		$formFile = JPath::clean($basePath . '/modules/' . $module . '/' . $module . '.xml');

		// Load the core and/or local language file(s).
		$lang->load($module, $basePath, null, false, true)
			||	 $lang->load($module, $basePath . '/modules/' . $module, null, false, true);

		if (file_exists($formFile))
		{
			// Get the module form.
			if (!$form->loadFile($formFile, false, '//config'))
			{
				throw new Exception(JText::_('JERROR_LOADFILE_FAILED'));
			}

			// Attempt to load the xml file.
			if (!$xml = simplexml_load_file($formFile))
			{
				throw new Exception(JText::_('JERROR_LOADFILE_FAILED'));
			}
		}

		// Load the default advanced params
		JForm::addFormPath(JPATH_BASE . '/components/com_config/model/form');
		$form->loadFile('modules_advanced', false);

		// Trigger the default form events.
		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to get list of module positions in current template
	 *
	 * @return array
	 *
	 * @since 3.2
	 */
	public function getPositions()
	{
		$lang            = JFactory::getLanguage();
		$templateName = JFactory::getApplication()->getTemplate();

		// Load templateDetails.xml file
		$path = JPath::clean(JPATH_BASE . '/templates/' . $templateName . '/templateDetails.xml');
		$currentPositions = array();

		if (file_exists($path))
		{
			$xml = simplexml_load_file($path);

			if (isset($xml->positions[0]))
			{
				foreach ($xml->positions[0] as $position)
				{
					// Load language files
					$lang->load('tpl_' . $templateName . '.sys', JPATH_BASE, null, false, true)
					||	$lang->load('tpl_' . $templateName . '.sys', JPATH_BASE . '/templates/' . $templateName, null, false, true);

					$key = (string) $position;
					$value = preg_replace('/[^a-zA-Z0-9_\-]/', '_', 'TPL_' . strtoupper($templateName) . '_POSITION_' . strtoupper($key));

					// Construct list of positions
					$currentPositions[$key] = JText::_($value) . ' [' . $key . ']';
				}
			}
		}

		return $currentPositions;
	}
}
