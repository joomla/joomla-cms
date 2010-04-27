<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Plugin model.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_plugins
 * @since		1.6
 */
class PluginsModelPlugin extends JModelAdmin
{
	protected $_cache;

	/**
	 * Method to get the record form.
	 *
	 * @param	array		An optional array of source data.
	 *
	 * @return	mixed		JForm object on success, false on failure.
	 */
	public function getForm($data = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// The folder and element vars are passed when saving the form.
		if (empty($data)) {
			$item		= $this->getItem();
			$folder		= $item->folder;
			$element	= $item->element;
		} else {
			$folder		= JArrayHelper::getValue($data, 'folder', '', 'word');
			$element	= JArrayHelper::getValue($data, 'element', '', 'word');
		}

		// These variables are used to add data from the plugin XML files.
		$this->setState('item.folder',	$folder);
		$this->setState('item.element',	$element);

		// Get the form.
		$form = parent::getForm('com_plugins.plugin', 'plugin', array('control' => 'jform'));
		if (empty($form)) {
			return false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_plugins.edit.plugin.data', array());

		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		} else {
			$form->bind($this->getItem());
		}

		return $form;
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
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	*/
	public function getTable($type = 'Extension', $prefix = 'JTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * @param	object	A form object.
	 *
	 * @return	mixed	True if successful.
	 * @throws	Exception if there is an error in the form event.
	 * @since	1.6
	 */
	protected function preprocessForm($form)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		// Initialise variables.
		$folder		= $this->getState('item.folder');
		$element	= $this->getState('item.element');
		$lang		= JFactory::getLanguage();
		$client		= JApplicationHelper::getClientInfo(0);

		// Try 1.6 format: /plugins/folder/element/element.xml
		$formFile = JPath::clean($client->path.'/plugins/'.$folder.'/'.$element.'/'.$element.'.xml');
		if (!file_exists($formFile)) {
			// Try 1.5 format: /plugins/folder/element/element.xml
			$formFile = JPath::clean($client->path.'/plugins/'.$folder.'/'.$element.'.xml');
			if (!file_exists($formFile)) {
				throw new Exception(JText::sprintf('JError_File_not_found', $element.'.xml'));
				return false;
			}
		}

		// Load the core and/or local language file(s).
			$lang->load('plg_'.$folder.'_'.$element, JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load('plg_'.$folder.'_'.$element, $client->path.'/plugins/'.$folder.'/'.$element, null, false, false)
		||	$lang->load('plg_'.$folder.'_'.$element, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load('plg_'.$folder.'_'.$element, $client->path.'/plugins/'.$folder.'/'.$element, $lang->getDefault(), false, false);

		if (file_exists($formFile)) {
			// Get the plugin form.
			if (!$form->loadFile($formFile, false, '//config')) {
				throw new Exception(JText::_('JModelForm_Error_loadFile_failed'));
			}
		}

		// Trigger the default form events.
		parent::preprocessForm($form);
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param	object	A record object.
	 * @return	array	An array of conditions to add to add to ordering queries.
	 * @since	1.6
	 */
	protected function getReorderConditions($table = null)
	{
		$condition = array();
		$condition[] = 'type = '. $this->_db->Quote($table->type);
		$condition[] = 'folder = '. $this->_db->Quote($table->folder);
		return $condition;
	}
}