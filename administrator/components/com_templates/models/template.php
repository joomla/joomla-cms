<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_templates
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	com_templates
 * @since		1.6
 */
class TemplatesModelTemplate extends JModelLegacy
{
	protected $template = null;

	/**
	 * Internal method to get file properties.
	 *
	 * @param	string The base path.
	 * @param	string The file name.
	 * @return	object
	 * @since	1.6
	 */
	protected function getFile($path, $name)
	{
		$temp = new stdClass;

		if ($template = $this->getTemplate()) {
			$temp->name = $name;
			$temp->exists = file_exists($path.$name);
			$temp->id = urlencode(base64_encode($template->extension_id.':'.$name));
			return $temp;
		}
	}

	/**
	 * Method to get a list of all the files to edit in a template.
	 *
	 * @return	array	A nested array of relevant files.
	 * @since	1.6
	 */
	public function getFiles()
	{
		// Initialise variables.
		$result	= array();

		if ($template = $this->getTemplate()) {
			jimport('joomla.filesystem.folder');

			$client = JApplicationHelper::getClientInfo($template->client_id);
			$path	= JPath::clean($client->path.'/templates/'.$template->element.'/');
			$lang	= JFactory::getLanguage();

			// Load the core and/or local language file(s).
			$lang->load('tpl_'.$template->element, $client->path, null, false, false)
				||	$lang->load('tpl_'.$template->element, $client->path.'/templates/'.$template->element, null, false, false)
				||	$lang->load('tpl_'.$template->element, $client->path, $lang->getDefault(), false, false)
				||	$lang->load('tpl_'.$template->element, $client->path.'/templates/'.$template->element, $lang->getDefault(), false, false);

			// Check if the template path exists.

			if (is_dir($path)) {
				$result['main'] = array();
				$result['css'] = array();
				$result['clo'] = array();
				$result['mlo'] = array();
				$result['html'] = array();

				// Handle the main PHP files.
				$result['main']['index'] = $this->getFile($path, 'index.php');
				$result['main']['error'] = $this->getFile($path, 'error.php');
				$result['main']['print'] = $this->getFile($path, 'component.php');
				$result['main']['offline'] = $this->getFile($path, 'offline.php');

				// Handle the CSS files.
				$files = JFolder::files($path.'/css', '\.css$', false, false);

				foreach ($files as $file) {
					$result['css'][] = $this->getFile($path.'/css/', 'css/'.$file);
				}
			} else {
				$this->setError(JText::_('COM_TEMPLATES_ERROR_TEMPLATE_FOLDER_NOT_FOUND'));
				return false;
			}
		}

		return $result;
	}

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
		$pk = (int) JRequest::getInt('id');
		$this->setState('extension.id', $pk);

		// Load the parameters.
		$params	= JComponentHelper::getParams('com_templates');
		$this->setState('params', $params);
	}

	/**
	 * Method to get the template information.
	 *
	 * @return	mixed	Object if successful, false if not and internal error is set.
	 * @since	1.6
	 */
	public function &getTemplate()
	{
		if (empty($this->template)) {
			// Initialise variables.
			$pk		= $this->getState('extension.id');
			$db		= $this->getDbo();
			$result	= false;

			// Get the template information.
			$db->setQuery(
				'SELECT extension_id, client_id, element' .
				' FROM #__extensions' .
				' WHERE extension_id = '.(int) $pk.
				'  AND type = '.$db->quote('template')
			);

			$result = $db->loadObject();
			if (empty($result)) {
				if ($error = $db->getErrorMsg()) {
					$this->setError($error);
				} else {
					$this->setError(JText::_('COM_TEMPLATES_ERROR_EXTENSION_RECORD_NOT_FOUND'));
				}
				$this->template = false;
			} else {
				$this->template = $result;
			}
		}

		return $this->template;
	}

	/**
	 * Method to check if new template name already exists
	 *
	 * @return	boolean   true if name is not used, false otherwise
	 * @since	2.5
	 */
	public function checkNewName()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__extensions');
		$query->where('name = ' . $db->quote($this->getState('new_name')));
		$db->setQuery($query);
		return ($db->loadResult() == 0);
	}

	/**
	 * Method to check if new template name already exists
	 *
	 * @return	string     name of current template
	 * @since	2.5
	 */
	public function getFromName()
	{
		return $this->getTemplate()->element;
	}

	/**
	 * Method to check if new template name already exists
	 *
	 * @return	boolean   true if name is not used, false otherwise
	 * @since	2.5
	 */
	public function copy()
	{
		if ($template = $this->getTemplate())
		{
			jimport('joomla.filesystem.folder');
			$client = JApplicationHelper::getClientInfo($template->client_id);
			$fromPath = JPath::clean($client->path.'/templates/'.$template->element.'/');

			// Delete new folder if it exists
			$toPath = $this->getState('to_path');
			if (JFolder::exists($toPath))
			{
				if (!JFolder::delete($toPath))
				{
					JError::raiseWarning(403, JText::_('COM_TEMPLATES_ERROR_COULD_NOT_WRITE'));
					return false;
				}
			}

			// Copy all files from $fromName template to $newName folder
			if (!JFolder::copy($fromPath, $toPath) || !$this->fixTemplateName())
			{
				return false;
			}

		return true;
		}
		else
		{
			JError::raiseWarning(403, JText::_('COM_TEMPLATES_ERROR_INVALID_FROM_NAME'));
			return false;
		}
	}

	/**
	 * Method to delete tmp folder
	 *
	 * @return	boolean   true if delete successful, false otherwise
	 * @since	2.5
	 */
	public function cleanup()
	{
		// Clear installation messages
		$app = JFactory::getApplication();
		$app->setUserState('com_installer.message', '');
		$app->setUserState('com_installer.extension_message', '');

		// Delete temporary directory
		return JFolder::delete($this->getState('to_path'));

	}

	/**
	 * Method to rename the template in the XML files and rename the language files
	 *
	 * @return	boolean   true if successful, false otherwise
	 * @since	2.5
	 */
	protected function fixTemplateName()
	{
		// Rename Language files
		// Get list of language files
		$result = true;
		$files = JFolder::files($this->getState('to_path'), '.ini', true, true);
		$newName = strtolower($this->getState('new_name'));
		$oldName = $this->getTemplate()->element;

		foreach ($files as $file)
		{
			$newFile = str_replace($oldName, $newName, $file);
			$result = JFile::move($file, $newFile) && $result;
		}

		// Edit XML file
		$xmlFile = $this->getState('to_path') . '/templateDetails.xml';
		if (JFile::exists($xmlFile))
		{
			$contents = JFile::read($xmlFile);
			$pattern[] = '#<name>\s*' . $oldName . '\s*</name>#';
			$replace[] = '<name>'. ucfirst($newName) . '</name>';
			$pattern[] = '#<language(.*)' . $oldName . '(.*)</language>#';
			$replace[] = '<language${1}' . $newName . '${2}</language>';
			$contents = preg_replace($pattern, $replace, $contents);
			$result = JFile::write($xmlFile, $contents) && $result;
		}

		return $result;
	}

}
