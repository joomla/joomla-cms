<?php
/**
 * @version		$Id: controller.php 12685 2009-09-10 14:14:04Z pentacle $
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * Plugin model.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_plugins
 * @since		1.6
 */
class PluginsModelPlugin extends JModelForm
{
	/**
	 * Item cache.
	 */
	private $_cache = array();

	/**
	 * Method to auto-populate the model state.
	 */
	protected function _populateState()
	{
		$app = JFactory::getApplication('administrator');

		// Load the User state.
		if (!($pk = (int) $app->getUserState('com_plugins.edit.plugin.id'))) {
			$pk = (int) JRequest::getInt('id');
		}
		$this->setState('plugin.id', $pk);

		// Load the parameters.
		$params	= JComponentHelper::getParams('com_plugins');
		$this->setState('params', $params);
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 */
	protected function _prepareTable(&$table)
	{
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type 	$type 	 The table type to instantiate
	 * @param	string 	$prefix	 A prefix for the table class name. Optional.
	 * @param	array	$options Configuration array for model. Optional.
	 * @return	JTable	A database object
	*/
	public function getTable($type = 'Extension', $prefix = 'JTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to override check-out a row for editing.
	 *
	 * @param	int		The ID of the primary key.
	 * @return	boolean
	 */
	public function checkout($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('plugin.id');

		return parent::checkout($pk);
	}

	/**
	 * Method to checkin a row.
	 *
	 * @param	integer	The ID of the primary key.
	 *
	 * @return	boolean
	 */
	public function checkin($pk = null)
	{
		// Initialise variables.
		$pk	= (!empty($pk)) ? $pk : (int) $this->getState('plugin.id');

		return parent::checkin($pk);
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
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('plugin.id');

		if (!isset($this->_cache[$pk]))
		{
			$false	= false;

			// Get a row instance.
			$table = &$this->getTable();

			// Attempt to load the row.
			$return = $table->load($pk);

			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());
				return $false;
			}

			// Convert to the JObject before adding other data.
			$this->_cache[$pk] = JArrayHelper::toObject($table->getProperties(1), 'JObject');

			// Convert the params field to an array.
			$registry = new JRegistry;
			$registry->loadJSON($table->params);
			$this->_cache[$pk]->params = $registry->toArray();

			// Get the plugin XML.
			$client	= JApplicationHelper::getClientInfo($table->client_id);
			$path	= JPath::clean($client->path.'/plugins/'.$table->folder.'/'.$table->element.'/'.$table->element.'.xml');

			if (file_exists($path)) {
				$this->_cache[$pk]->xml = &JFactory::getXML($path);
			} else {
				$this->_cache[$pk]->xml = null;
			}
		}

		return $this->_cache[$pk];
	}

	/**
	 * Method to get the record form.
	 *
	 * @return	mixed	JForm object on success, false on failure.
	 */
	public function getForm()
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = parent::getForm('plugin', 'com_plugins.plugin', array('array' => 'jform', 'event' => 'onPrepareForm'));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_plugins.edit.plugin.data', array());

		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		}

		return $form;
	}

	/**
	 * Method to get a form object for the template params.
	 *
	 * @param	string		An optional plugin folder.
	 * @param	string		An options plugin element.
	 *
	 * @return	mixed		A JForm object on success, false on failure.
	 */
	public function getParamsForm($folder = null, $element = null)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		// Initialise variables.
		$lang			= JFactory::getLanguage();
		$form			= null;
		$formName		= 'com_plugins.plugin.params';
		$formOptions	= array('array' => 'jformparams', 'event' => 'onPrepareForm');

		if (empty($folder) && empty($element))
		{
			$item		= $this->getItem();
			$folder		= $item->folder;
			$element	= $item->element;
		}
		$client			= JApplicationHelper::getClientInfo(0);

		// Try 1.6 format: /plugins/folder/element/element.xml
		$formFile = JPath::clean($client->path.'/plugins/'.$folder.'/'.$element.'/'.$element.'.xml');
		if (!file_exists($formFile))
		{
			// Try 1.5 format: /plugins/folder/element/element.xml
			$formFile = JPath::clean($client->path.'/plugins/'.$folder.'/'.$element.'.xml');
			if (!file_exists($formFile))
			{
				$this->setError(JText::sprintf('JError_File_not_found', $element.'.xml'));
				return false;
			}
		}

		// Load the core and/or local language file(s).
		$lang->load('plg_'.$folder.'_'.$element, $client->path.'/plugins/'.$folder.'/'.$element);
		$lang->load('plg_'.$folder.'_'.$element, JPATH_SITE);
		$lang->load('plg_'.$folder.'_'.$element, JPATH_ADMINISTRATOR);

		// If an XML file was found in the component, load it first.
		// We need to qualify the full path to avoid collisions with component file names.
		$form = parent::getForm($formFile, $formName, $formOptions, true);

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return false;
		}

		return $form;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 */
	public function save($data)
	{
		// Initialise variables.
		$table		= $this->getTable();
		$pk			= (!empty($data['id'])) ? $data['id'] : (int) $this->getState('plugin.id');
		$isNew		= true;

		// Include the content plugins for the onSave events.
		JPluginHelper::importPlugin('content');

		// Load the row if saving an existing record.
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError(JText::sprintf('JTable_Error_Bind_failed', $table->getError()));
			return false;
		}

		// Prepare the row for saving
		$this->_prepareTable($table);

		// Check the data.
		if (!$table->check())
		{
			$this->setError($table->getError());
			return false;
		}

		// Store the data.
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		$this->setState('plugin.id', $table->extension_id);

		return true;
	}

	/**
	 * Method to publish records.
	 *
	 * @param	array	The ids of the items to publish.
	 * @param	int		The value of the published state
	 *
	 * @return	boolean	True on success.
	 */
	function publish(&$pks, $value = 1)
	{
		// Initialise variables.
		$user	= JFactory::getUser();
		$table	= $this->getTable();
		$pks	= (array) $pks;

		if (!$user->authorise('core.edit.state', 'com_plugins'))
		{
			$pks = array();
			$this->setError(JText::_('JError_Core_Edit_State_not_permitted'));
			return false;
		}

		// Attempt to change the state of the records.
		if (!$table->publish($pks, $value, $user->get('id'))) {
			$this->setError($table->getError());
			return false;
		}

		return true;
	}

	/**
	 * Method to adjust the ordering of a row.
	 *
	 * @param	int		The ID of the primary key to move.
	 * @param	integer	Increment, usually +1 or -1
	 * @return	boolean	False on failure or error, true otherwise.
	 */
	public function reorder($pks, $delta = 0)
	{
		// Initialise variables.
		$user	= JFactory::getUser();
		$table	= $this->getTable();
		$pks	= (array) $pks;
		$result	= true;

		// Access checks.
		$allow = $user->authorise('core.edit.state', 'com_plugins');
		if (!$allow)
		{
			$this->setError(JText::_('JError_Core_Edit_State_not_permitted'));
			return false;
		}

		foreach ($pks as $i => $pk)
		{
			$table->reset();
			if ($table->load($pk) && $this->checkout($pk))
			{
				$table->ordering += $delta;
				if (!$table->store())
				{
					$this->setError($table->getError());
					unset($pks[$i]);
					$result = false;
				}
			}
			else
			{
				$this->setError($table->getError());
				unset($pks[$i]);
				$result = false;
			}
		}

		return $result;
	}

	/**
	 * Saves the manually set order of records.
	 *
	 * @param	array	An array of primary key ids.
	 * @param	int		+/-1
	 */
	function saveorder($pks, $order)
	{
		// Initialise variables.
		$user		= JFactory::getUser();
		$table		= $this->getTable();
		$conditions	= array();

		if (empty($pks)) {
			return JError::raiseWarning(500, JText::_('JError_No_items_selected'));
		}

		if (!$user->authorise('core.edit.state', 'com_plugins'))
		{
			$pks = array();
			$this->setError(JText::_('JError_Core_Edit_State_not_permitted'));
			return false;
		}

		// update ordering values
		foreach ($pks as $i => $pk)
		{
			$table->load((int) $pk);

			if ($table->ordering != $order[$i])
			{
				$table->ordering = $order[$i];
				if (!$table->store())
				{
					$this->setError($table->getError());
					return false;
				}
			}
		}

		return true;
	}
}
