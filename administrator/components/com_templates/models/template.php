<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_templates
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_templates
 * @since		1.6
 */
class TemplatesModelTemplate extends JModel
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

			// Check if the template path exists.

			if (is_dir($path)) {
				$result['main'] = array();
				$result['css'] = array();
				$result['clo'] = array();
				$result['mlo'] = array();

				// Handle the main PHP files.
				$result['main']['index'] = $this->getFile($path, 'index.php');
				$result['main']['error'] = $this->getFile($path, 'error.php');
				$result['main']['print'] = $this->getFile($path, 'component.php');

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
}