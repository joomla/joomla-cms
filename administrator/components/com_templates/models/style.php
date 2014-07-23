<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Template style model.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 * @since       1.6
 */
class TemplatesModelStyle extends JModelAdmin
{
	/**
	 * @var	    string	The help screen key for the module.
	 * @since   1.6
	 */
	protected $helpKey = 'JHELP_EXTENSIONS_TEMPLATE_MANAGER_STYLES_EDIT';

	/**
	 * @var	    string	The help screen base URL for the module.
	 * @since   1.6
	 */
	protected $helpURL;

	/**
	 * Item cache.
	 */
	private $_cache = array();

	/**
	 * Method to auto-populate the model state.
	 *
	 * @note    Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('administrator');

		// Load the User state.
		$pk = $app->input->getInt('id');
		$this->setState('style.id', $pk);

		// Load the parameters.
		$params	= JComponentHelper::getParams('com_templates');
		$this->setState('params', $params);
	}

	/**
	 * Method to delete rows.
	 *
	 * @param   array  &$pks  An array of item ids.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 */
	public function delete(&$pks)
	{
		$pks	= (array) $pks;
		$user	= JFactory::getUser();
		$table	= $this->getTable();

		// Iterate the items to delete each one.
		foreach ($pks as $pk)
		{
			if ($table->load($pk))
			{
				// Access checks.
				if (!$user->authorise('core.delete', 'com_templates'))
				{
					throw new Exception(JText::_('JERROR_CORE_DELETE_NOT_PERMITTED'));
				}

				// You should not delete a default style
				if ($table->home != '0')
				{
					JError::raiseWarning(SOME_ERROR_NUMBER, JText::_('COM_TEMPLATES_STYLE_CANNOT_DELETE_DEFAULT_STYLE'));

					return false;
				}

				if (!$table->delete($pk))
				{
					$this->setError($table->getError());

					return false;
				}
			}
			else
			{
				$this->setError($table->getError());

				return false;
			}
		}

		// Clean cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to duplicate styles.
	 *
	 * @param   array  &$pks  An array of primary key IDs.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @throws  Exception
	 */
	public function duplicate(&$pks)
	{
		$user = JFactory::getUser();

		// Access checks.
		if (!$user->authorise('core.create', 'com_templates'))
		{
			throw new Exception(JText::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
		}

		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			if ($table->load($pk, true))
			{
				// Reset the id to create a new record.
				$table->id = 0;

				// Reset the home (don't want dupes of that field).
				$table->home = 0;

				// Alter the title.
				$m = null;
				$table->title = $this->generateNewTitle(null, null, $table->title);

				if (!$table->check() || !$table->store())
				{
					throw new Exception($table->getError());
				}
			}
			else
			{
				throw new Exception($table->getError());
			}
		}

		// Clean cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to change the title.
	 *
	 * @param   integer  $category_id  The id of the category.
	 * @param   string   $alias        The alias.
	 * @param   string   $title        The title.
	 *
	 * @return  string  New title.
	 *
	 * @since   1.7.1
	 */
	protected function generateNewTitle($category_id, $alias, $title)
	{
		// Alter the title
		$table = $this->getTable();

		while ($table->load(array('title' => $title)))
		{
			$title = JString::increment($title);
		}

		return $title;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// The folder and element vars are passed when saving the form.
		if (empty($data))
		{
			$item	   = $this->getItem();
			$clientId  = $item->client_id;
			$template  = $item->template;
		}
		else
		{
			$clientId  = JArrayHelper::getValue($data, 'client_id');
			$template  = JArrayHelper::getValue($data, 'template');
		}

		// These variables are used to add data from the plugin XML files.
		$this->setState('item.client_id', $clientId);
		$this->setState('item.template', $template);

		// Get the form.
		$form = $this->loadForm('com_templates.style', 'style', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		// Modify the form based on access controls.
		if (!$this->canEditState((object) $data))
		{
			// Disable fields for display.
			$form->setFieldAttribute('home', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('home', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_templates.edit.style.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->preprocessData('com_templates.style', $data);

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('style.id');

		if (!isset($this->_cache[$pk]))
		{
			$false	= false;

			// Get a row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			$return = $table->load($pk);

			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());

				return $false;
			}

			// Convert to the JObject before adding other data.
			$properties = $table->getProperties(1);
			$this->_cache[$pk] = JArrayHelper::toObject($properties, 'JObject');

			// Convert the params field to an array.
			$registry = new JRegistry;
			$registry->loadString($table->params);
			$this->_cache[$pk]->params = $registry->toArray();

			// Get the template XML.
			$client	= JApplicationHelper::getClientInfo($table->client_id);
			$path	= JPath::clean($client->path.'/templates/'.$table->template.'/templateDetails.xml');

			if (file_exists($path))
			{
				$this->_cache[$pk]->xml = simplexml_load_file($path);
			}
			else
			{
				$this->_cache[$pk]->xml = null;
			}
		}

		return $this->_cache[$pk];
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A database object
	*/
	public function getTable($type = 'Style', $prefix = 'TemplatesTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * TODO
	 *
	 * @param   object  $form   A form object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  TODO
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @throws  Exception if there is an error in the form event.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		$clientId = $this->getState('item.client_id');
		$template = $this->getState('item.template');
		$lang     = JFactory::getLanguage();
		$client   = JApplicationHelper::getClientInfo($clientId);

		if (!$form->loadFile('style_' . $client->name, true))
		{
			throw new Exception(JText::_('JERROR_LOADFILE_FAILED'));
		}

		jimport('joomla.filesystem.path');

		$formFile = JPath::clean($client->path . '/templates/' . $template . '/templateDetails.xml');

		// Load the core and/or local language file(s).
			$lang->load('tpl_' . $template, $client->path, null, false, true)
		||	$lang->load('tpl_' . $template, $client->path . '/templates/' . $template, null, false, true);

		if (file_exists($formFile))
		{
			// Get the template form.
			if (!$form->loadFile($formFile, false, '//config'))
			{
				throw new Exception(JText::_('JERROR_LOADFILE_FAILED'));
			}
		}

		// Disable home field if it is default style

		if ((is_array($data) && array_key_exists('home', $data) && $data['home'] == '1')
			|| ((is_object($data) && isset($data->home) && $data->home == '1')))
		{
			$form->setFieldAttribute('home', 'readonly', 'true');
		}

		// Attempt to load the xml file.
		if (!$xml = simplexml_load_file($formFile))
		{
			throw new Exception(JText::_('JERROR_LOADFILE_FAILED'));
		}

		// Get the help data from the XML file if present.
		$help = $xml->xpath('/extension/help');

		if (!empty($help))
		{
			$helpKey = trim((string) $help[0]['key']);
			$helpURL = trim((string) $help[0]['url']);

			$this->helpKey = $helpKey ? $helpKey : $this->helpKey;
			$this->helpURL = $helpURL ? $helpURL : $this->helpURL;
		}

		// Trigger the default form events.
		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 */
	public function save($data)
	{
		// Detect disabled extension
		$extension = JTable::getInstance('Extension');

		if ($extension->load(array('enabled' => 0, 'type' => 'template', 'element' => $data['template'], 'client_id' => $data['client_id'])))
		{
			$this->setError(JText::_('COM_TEMPLATES_ERROR_SAVE_DISABLED_TEMPLATE'));

			return false;
		}

		$app        = JFactory::getApplication();
		$dispatcher = JEventDispatcher::getInstance();
		$table      = $this->getTable();
		$pk         = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('style.id');
		$isNew      = true;

		// Include the extension plugins for the save events.
		JPluginHelper::importPlugin('extension');

		// Load the row if saving an existing record.
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		if ($app->input->get('task') == 'save2copy')
		{
			$data['title']    = $this->generateNewTitle(null, null, $data['title']);
			$data['home']     = 0;
			$data['assigned'] = '';
		}

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		// Prepare the row for saving
		$this->prepareTable($table);

		// Check the data.
		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		// Trigger the onExtensionBeforeSave event.
		$result = $dispatcher->trigger('onExtensionBeforeSave', array('com_templates.style', &$table, $isNew));

		if (in_array(false, $result, true))
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

		$user = JFactory::getUser();

		if ($user->authorise('core.edit', 'com_menus') && $table->client_id == 0)
		{
			$n    = 0;
			$db   = JFactory::getDbo();
			$user = JFactory::getUser();

			if (!empty($data['assigned']) && is_array($data['assigned']))
			{
				JArrayHelper::toInteger($data['assigned']);

				// Update the mapping for menu items that this style IS assigned to.
				$query = $db->getQuery(true)
					->update('#__menu')
					->set('template_style_id = ' . (int) $table->id)
					->where('id IN (' . implode(',', $data['assigned']) . ')')
					->where('template_style_id != ' . (int) $table->id)
					->where('checked_out IN (0,' . (int) $user->id . ')');
				$db->setQuery($query);
				$db->execute();
				$n += $db->getAffectedRows();
			}

			// Remove style mappings for menu items this style is NOT assigned to.
			// If unassigned then all existing maps will be removed.
			$query = $db->getQuery(true)
				->update('#__menu')
				->set('template_style_id = 0');

			if (!empty($data['assigned']))
			{
				$query->where('id NOT IN (' . implode(',', $data['assigned']) . ')');
			}

			$query->where('template_style_id = ' . (int) $table->id)
				->where('checked_out IN (0,' . (int) $user->id . ')');
			$db->setQuery($query);
			$db->execute();

			$n += $db->getAffectedRows();

			if ($n > 0)
			{
				$app->enqueueMessage(JText::plural('COM_TEMPLATES_MENU_CHANGED', $n));
			}
		}

		// Clean the cache.
		$this->cleanCache();

		// Trigger the onExtensionAfterSave event.
		$dispatcher->trigger('onExtensionAfterSave', array('com_templates.style', &$table, $isNew));

		$this->setState('style.id', $table->id);

		return true;
	}

	/**
	 * Method to set a template style as home.
	 *
	 * @param   integer  $id  The primary key ID for the style.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @throws	Exception
	 */
	public function setHome($id = 0)
	{
		$user = JFactory::getUser();
		$db   = $this->getDbo();

		// Access checks.
		if (!$user->authorise('core.edit.state', 'com_templates'))
		{
			throw new Exception(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
		}

		$style = JTable::getInstance('Style', 'TemplatesTable');

		if (!$style->load((int) $id))
		{
			throw new Exception(JText::_('COM_TEMPLATES_ERROR_STYLE_NOT_FOUND'));
		}

		// Detect disabled extension
		$extension = JTable::getInstance('Extension');

		if ($extension->load(array('enabled' => 0, 'type' => 'template', 'element' => $style->template, 'client_id' => $style->client_id)))
		{
			throw new Exception(JText::_('COM_TEMPLATES_ERROR_SAVE_DISABLED_TEMPLATE'));
		}

		// Reset the home fields for the client_id.
		$db->setQuery(
			'UPDATE #__template_styles' .
			' SET home = \'0\'' .
			' WHERE client_id = ' . (int) $style->client_id .
			' AND home = \'1\''
		);
		$db->execute();

		// Set the new home style.
		$db->setQuery(
			'UPDATE #__template_styles' .
			' SET home = \'1\'' .
			' WHERE id = ' . (int) $id
		);
		$db->execute();

		// Clean the cache.
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to unset a template style as default for a language.
	 *
	 * @param   integer  $id  The primary key ID for the style.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @throws	Exception
	 */
	public function unsetHome($id = 0)
	{
		$user = JFactory::getUser();
		$db   = $this->getDbo();

		// Access checks.
		if (!$user->authorise('core.edit.state', 'com_templates'))
		{
			throw new Exception(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
		}

		// Lookup the client_id.
		$db->setQuery(
			'SELECT client_id, home' .
			' FROM #__template_styles' .
			' WHERE id = ' . (int) $id
		);
		$style = $db->loadObject();

		if (!is_numeric($style->client_id))
		{
			throw new Exception(JText::_('COM_TEMPLATES_ERROR_STYLE_NOT_FOUND'));
		}
		elseif ($style->home == '1')
		{
			throw new Exception(JText::_('COM_TEMPLATES_ERROR_CANNOT_UNSET_DEFAULT_STYLE'));
		}

		// Set the new home style.
		$db->setQuery(
			'UPDATE #__template_styles' .
			' SET home = \'0\'' .
			' WHERE id = ' . (int) $id
		);
		$db->execute();

		// Clean the cache.
		$this->cleanCache();

		return true;
	}

	/**
	 * Get the necessary data to load an item help screen.
	 *
	 * @return  object  An object with key, url, and local properties for loading the item help screen.
	 *
	 * @since   1.6
	 */
	public function getHelp()
	{
		return (object) array('key' => $this->helpKey, 'url' => $this->helpURL);
	}

	/**
	 * Custom clean cache method
	 *
	 * @param   string   $group      The cache group
	 * @param   integer  $client_id  The ID of the client
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_templates');
		parent::cleanCache('_system');
	}
}
