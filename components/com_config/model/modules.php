<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Config Module model.
 *
 * @since  3.2
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
	 * @return  array
	 *
	 * @since   3.2
	 */
	public function getPositions()
	{
		$lang         = JFactory::getLanguage();
		$templateName = JFactory::getApplication()->getTemplate();

		// Load templateDetails.xml file
		$path = JPath::clean(JPATH_BASE . '/templates/' . $templateName . '/templateDetails.xml');
		$currentTemplatePositions = array();

		if (file_exists($path))
		{
			$xml = simplexml_load_file($path);

			if (isset($xml->positions[0]))
			{
				// Load language files
				$lang->load('tpl_' . $templateName . '.sys', JPATH_BASE, null, false, true)
				||	$lang->load('tpl_' . $templateName . '.sys', JPATH_BASE . '/templates/' . $templateName, null, false, true);

				foreach ($xml->positions[0] as $position)
				{
					$value = (string) $position;
					$text = preg_replace('/[^a-zA-Z0-9_\-]/', '_', 'TPL_' . strtoupper($templateName) . '_POSITION_' . strtoupper($value));

					// Construct list of positions
					$currentTemplatePositions[] = self::createOption($value, JText::_($text) . ' [' . $value . ']');
				}
			}
		}

		$templateGroups = array();

		// Add an empty value to be able to deselect a module position
		$option = self::createOption();
		$templateGroups[''] = self::createOptionGroup('', array($option));

		$templateGroups[$templateName] = self::createOptionGroup($templateName, $currentTemplatePositions);

		// Add custom position to options
		$customGroupText = JText::_('COM_MODULES_CUSTOM_POSITION');

		$editPositions   = true;
		$customPositions = self::getActivePositions(0, $editPositions);
		$templateGroups[$customGroupText] = self::createOptionGroup($customGroupText, $customPositions);

		return $templateGroups;
	}

	/**
	 * Get a list of modules positions
	 *
	 * @param   integer  $clientId       Client ID
	 * @param   boolean  $editPositions  Allow to edit the positions
	 *
	 * @return  array  A list of positions
	 *
	 * @since   3.6.3
	 */
	public static function getActivePositions($clientId, $editPositions = false)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT position')
			->from($db->quoteName('#__modules'))
			->where($db->quoteName('client_id') . ' = ' . (int) $clientId)
			->order($db->quoteName('position'));

		$db->setQuery($query);

		try
		{
			$positions = $db->loadColumn();
			$positions = is_array($positions) ? $positions : array();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());

			return;
		}

		// Build the list
		$options = array();

		foreach ($positions as $position)
		{
			if (!$position && !$editPositions)
			{
				$options[] = JHtml::_('select.option', 'none', ':: ' . JText::_('JNONE') . ' ::');
			}
			else
			{
				$options[] = JHtml::_('select.option', $position, $position);
			}
		}

		return $options;
	}

	/**
	 * Create and return a new Option
	 *
	 * @param   string  $value  The option value [optional]
	 * @param   string  $text   The option text [optional]
	 *
	 * @return  object  The option as an object (stdClass instance)
	 *
	 * @since   3.6.3
	 */
	private static function createOption($value = '', $text = '')
	{
		if (empty($text))
		{
			$text = $value;
		}

		$option = new stdClass;
		$option->value = $value;
		$option->text  = $text;

		return $option;
	}

	/**
	 * Create and return a new Option Group
	 *
	 * @param   string  $label    Value and label for group [optional]
	 * @param   array   $options  Array of options to insert into group [optional]
	 *
	 * @return  array  Return the new group as an array
	 *
	 * @since   3.6.3
	 */
	private static function createOptionGroup($label = '', $options = array())
	{
		$group = array();
		$group['value'] = $label;
		$group['text']  = $label;
		$group['items'] = $options;

		return $group;
	}
}
