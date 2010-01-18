<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Templates
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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
	/**
	 * Cache for the template information.
	 *
	 * @var		object
	 */
	private $_template = null;

	/**
	 * Method to auto-populate the model state.
	 */
	protected function _populateState()
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
	 * Internal method to get file properties.
	 *
	 * @param	string $path	The base path.
	 * @param	string $name	The file name.
	 *
	 * @return	object
	 */
	private function _getFile($path, $name)
	{
		$temp = new stdClass;

		if ($this->_template)
		{
			$temp->name = $name;
			$temp->exists = file_exists($path.$name);
			$temp->id = urlencode(base64_encode($this->_template->extension_id.':'.$name));
			return $temp;
		}
	}

	/**
	 * Method to get the template information.
	 *
	 * @return	mixed	Object if successful, false if not and internal error is set.
	 */
	public function &getTemplate()
	{
		if (empty($this->_template))
		{
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
			if (empty($result))
			{
				if ($error = $db->getErrorMsg()) {
					$this->setError($error);
				}
				else {
					$this->setError(JText::_('Templates_Error_Extension_record_not_found'));
				}
				$this->_template = false;
			}
			else {
				$this->_template = $result;
			}
		}

		return $this->_template;
	}

	/**
	 * Method to get a list of all the files to edit in a template.
	 *
	 * @return	array	A nested array of relevant files.
	 */
	public function getFiles()
	{
		// Initialise variables.
		$result	= array();

		if ($this->_template)
		{
			jimport('joomla.filesystem.folder');

			$client = JApplicationHelper::getClientInfo($this->_template->client_id);
			$path	= JPath::clean($client->path.'/templates/'.$this->_template->element.'/');

			// Check if the template path exists.
			if (is_dir($path))
			{
				$result['main'] = array();
				$result['css'] = array();
				$result['clo'] = array();
				$result['mlo'] = array();

				// Handle the main PHP files.
				$result['main']['index'] = $this->_getFile($path, 'index.php');
				$result['main']['error'] = $this->_getFile($path, 'error.php');
				$result['main']['print'] = $this->_getFile($path, 'component.php');

				// Handle the CSS files.
				$files = JFolder::files($path.'/css', '\.css$', false, false);

				foreach ($files as $file)
				{
					$result['css'][] = $this->_getFile($path.'/css/', 'css/'.$file);
				}
			}
			else
			{
				$this->setError(JText::_('Templates_Error_Template_folder_not_found'));
			}
		}

		return $result;
	}
}
