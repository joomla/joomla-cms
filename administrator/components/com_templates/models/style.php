<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * Template style model.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_templates
 * @since		1.6
 */
class TemplatesModelStyle extends JModelForm
{
	/**
	 * Item cache.
	 */
	private $_cache = array();

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('administrator');

		// Load the User state.
		if (!($pk = (int) $app->getUserState('com_templates.edit.style.id'))) {
			$pk = (int) JRequest::getInt('id');
		}
		$this->setState('style.id', $pk);

		// Load the parameters.
		$params	= JComponentHelper::getParams('com_templates');
		$this->setState('params', $params);
	}

	/**
	 * Method to delete rows.
	 *
	 * @param	array	An array of item ids.
	 *
	 * @return	boolean	Returns true on success, false on failure.
	 */
	public function delete(&$pks)
	{
		// Initialise variables.
		$pks	= (array) $pks;
		$user	= JFactory::getUser();
		$table	= $this->getTable();

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk) {
			if ($table->load($pk)) {
				// Access checks.
				if (!$user->authorise('core.delete', 'com_templates')) {
					throw new Exception(JText::_('JERROR_CORE_DELETE_NOT_PERMITTED'));
				}

				if (!$table->delete($pk)) {
					$this->setError($table->getError());
					return false;
				}
			} else {
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to duplicate styles.
	 *
	 * @param	array	An array of primary key IDs.
	 *
	 * @return	boolean	True if successful.
	 * @throws	Exception
	 */
	public function duplicate(&$pks)
	{
		// Initialise variables.
		$user	= JFactory::getUser();
		$db		= $this->getDbo();

		// Access checks.
		if (!$user->authorise('core.create', 'com_templates')) {
			throw new Exception(JText::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
		}

		$table = $this->getTable();

		foreach ($pks as $pk) {
			if ($table->load($pk, true)) {
				// Reset the id to create a new record.
				$table->id = 0;

				// Reset the home (don't want dupes of that field).
				$table->home = 0;

				// Alter the title.
				$m = null;
				if (preg_match('#\((\d+)\)$#', $table->title, $m)) {
					$table->title = preg_replace('#\(\d+\)$#', '('.($m[1] + 1).')', $table->title);
				} else {
					$table->title .= ' (2)';
				}

				if (!$table->check() || !$table->store()) {
					throw new Exception($table->getError());
				}
			} else {
				throw new Exception($table->getError());
			}
		}


		return true;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// The folder and element vars are passed when saving the form.
		if (empty($data)) {
			$item		= $this->getItem();
			$clientId	= $item->client_id;
			$template	= $item->template;
		} else {
			$clientId	= JArrayHelper::getValue($data, 'client_id');
			$template	= JArrayHelper::getValue($data, 'template');
		}

		// These variables are used to add data from the plugin XML files.
		$this->setState('item.client_id',	$clientId);
		$this->setState('item.template',	$template);

		// Get the form.
		$form = $this->loadForm('com_templates.style', 'style', array('control' => 'jform', 'load_data' => $loadData));
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
		$data = JFactory::getApplication()->getUserState('com_templates.edit.style.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 */
	public function &getItem($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('style.id');

		if (!isset($this->_cache[$pk])) {
			$false	= false;

			// Get a row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			$return = $table->load($pk);

			// Check for a table object error.
			if ($return === false && $table->getError()) {
				$this->setError($table->getError());
				return $false;
			}

			// Convert to the JObject before adding other data.
			$this->_cache[$pk] = JArrayHelper::toObject($table->getProperties(1), 'JObject');

			// Convert the params field to an array.
			$registry = new JRegistry;
			$registry->loadJSON($table->params);
			$this->_cache[$pk]->params = $registry->toArray();
		}

		return $this->_cache[$pk];
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	*/
	public function getTable($type = 'Style', $prefix = 'TemplatesTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 */
	protected function prepareTable(&$table)
	{
	}

	/**
	 * @param	object	A form object.
	 * @param	mixed	The data expected for the form.
	 * @throws	Exception if there is an error in the form event.
	 * @since	1.6
	 */
	protected function preprocessForm($form, $data)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		// Initialise variables.
		$clientId	= $this->getState('item.client_id');
		$template	= $this->getState('item.template');
		$lang		= JFactory::getLanguage();
		$client		= JApplicationHelper::getClientInfo($clientId);
		$formFile	= JPath::clean($client->path.'/templates/'.$template.'/templateDetails.xml');

		// Load the core and/or local language file(s).
			$lang->load('tpl_'.$template, $client->path, null, false, false)
		||	$lang->load('tpl_'.$template, $client->path.'/templates/'.$template, null, false, false)
		||	$lang->load('tpl_'.$template, $client->path, $lang->getDefault(), false, false)
		||	$lang->load('tpl_'.$template, $client->path.'/templates/'.$template, $lang->getDefault(), false, false);

		if (file_exists($formFile)) {
			// Get the template form.
			if (!$form->loadFile($formFile, false, '//config')) {
				throw new Exception(JText::_('JERROR_LOADFILE_FAILED'));
			}
		}

		// Disable home field if it is default style
		if(is_array($data) && $data['home'] || is_object($data) && $data->home) {
			$form->setFieldAttribute('home','readonly','true');
		}

		// Trigger the default form events.
		parent::preprocessForm($form, $data);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 */
	public function save($data)
	{
		// Initialise variables;
		$dispatcher = JDispatcher::getInstance();
		$table		= $this->getTable();
		$pk			= (!empty($data['id'])) ? $data['id'] : (int)$this->getState('style.id');
		$isNew		= true;

		// Include the extension plugins for the save events.
		JPluginHelper::importPlugin('extension');

		// Load the row if saving an existing record.
		if ($pk > 0) {
			$table->load($pk);
			$isNew = false;
		}

		// Bind the data.
		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		// Prepare the row for saving
		$this->prepareTable($table);

		// Check the data.
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Trigger the onExtensionBeforeSave event.
		$result = $dispatcher->trigger('onExtensionBeforeSave', array('com_templates.style', &$table, $isNew));
		if (in_array(false, $result, true)) {
			$this->setError($table->getError());
			return false;
		}

		// Store the data.
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		$user = JFactory::getUser();
		if ($user->authorise('core.edit','com_menus') && $table->client_id==0) {
			$n=0;

			$db = JFactory::getDbo();
			$user = JFactory::getUser();
			$app = JFactory::getApplication();
			$query=$db->getQuery(true);
			$query->update('#__menu');
			$query->set('template_style_id='.(int)$table->id);
			$query->where('id IN ('.implode(',',$data['assigned']).')');
			$query->where('template_style_id!='.(int)$table->id);
			$query->where('checked_out in (0,'.(int)$user->id.')');
			$db->setQuery($query);
			$db->query();
			$n = $n + $db->getAffectedRows();

			$query=$db->getQuery(true);
			$query->update('#__menu');
			$query->set('template_style_id=0');
			$query->where('id NOT IN ('.implode(',',$data['assigned']).')');
			$query->where('template_style_id='.(int)$table->id);
			$query->where('checked_out in (0,'.(int)$user->id.')');
			$db->setQuery($query);
			$db->query();
			$n = $n + $db->getAffectedRows();
			if ($n>0) {
				$app->enQueueMessage(JText::plural('COM_TEMPLATES_MENU_CHANGED',$n));
			}
		}

		// Clean the cache.
		$cache = JFactory::getCache('com_templates');
		$cache->clean();

		// Trigger the onExtensionAfterSave event.
		$dispatcher->trigger('onExtensionAfterSave', array('com_templates.style', &$table, $isNew));

		$this->setState('style.id', $table->id);

		return true;
	}

	/**
	 * Method to set a template style as home.
	 *
	 * @param	int		The primary key ID for the style.
	 *
	 * @return	boolean	True if successful.
	 * @throws	Exception
	 */
	public function setHome($id = 0)
	{
		// Initialise variables.
		$user	= JFactory::getUser();
		$db		= $this->getDbo();

		// Access checks.
		if (!$user->authorise('core.edit.state', 'com_templates')) {
			throw new Exception(JText::_('JERROR_CORE_EDIT_STATE_NOT_PERMITTED'));
		}

		// Lookup the client_id.
		$db->setQuery(
			'SELECT client_id' .
			' FROM #__template_styles' .
			' WHERE id = '.(int) $id
		);
		$clientId = $db->loadResult();

		if ($error = $db->getErrorMsg()) {
			throw new Exception($error);
		} else if (!is_numeric($clientId)) {
			throw new Exception(JText::_('COM_TEMPLATES_ERROR_STYLE_NOT_FOUND'));
		}

		// Reset the home fields for the client_id.
		$db->setQuery(
			'UPDATE #__template_styles' .
			' SET home = 0' .
			' WHERE client_id = '.(int) $clientId
		);

		if (!$db->query()) {
			throw new Exception($db->getErrorMsg());
		}

		// Set the new home style.
		$db->setQuery(
			'UPDATE #__template_styles' .
			' SET home = 1' .
			' WHERE id = '.(int) $id
		);

		if (!$db->query()) {
			throw new Exception($db->getErrorMsg());
		}

		return true;
	}
}
