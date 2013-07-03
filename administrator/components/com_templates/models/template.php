<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 * @since       1.6
 */
class TemplatesModelTemplate extends JModelForm
{
	protected $template = null;

	/**
	 * Internal method to get file properties.
	 *
	 * @param   string The base path.
	 * @param   string The file name.
	 * @return  object
	 * @since   1.6
	 */
	protected function getFile($path, $name)
	{
		$temp = new stdClass;

		if ($template = $this->getTemplate())
		{
			$temp->name = str_replace('-', '_', $name);
			$temp->id = urlencode(base64_encode($path . $name));
			return $temp;
		}
	}

	/**
	 * Method to get a list of all the files to edit in a template.
	 *
	 * @return  array  A nested array of relevant files.
	 * @since   1.6
	 */
	public function getFiles()
	{
		$result	= array();

		if ($template = $this->getTemplate())
		{
			
			jimport('joomla.filesystem.folder');
            $app = JFactory::getApplication();
			$client 	= JApplicationHelper::getClientInfo($template->client_id);
			$path		= JPath::clean($client->path.'/templates/'.$template->element.'/');

			if (is_dir($path))
			{
				$result = $this->getDirectoryTree($path);
			} else {
                $app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_TEMPLATE_FOLDER_NOT_FOUND'),'warning');
				return false;
			}
		}

		return $result;
	}

    /**
     * Method to get the directory tree.
     *
     * @param $dir
     * @internal param \The $string base path.
     * @return  array
     * @since   3.1
     */
	
	public function getDirectoryTree($dir)
	{
		$result = array();
	
		$dirFiles = scandir($dir);
		foreach ($dirFiles as $key => $value) 
		{ 
			if (!in_array($value,array(".",".."))) 
			{ 
				if (is_dir($dir . $value . '/')) 
				{ 
					$result[$value] = $this->getDirectoryTree($dir . $value . '/');
				} 
				else 
				{
					$ext = pathinfo($dir . $value, PATHINFO_EXTENSION);
					if(in_array($ext, array('css','js','php','xml','ini','less')))
					{
						$pos = strpos($dir,$this->getFromName()) + strlen($this->getFromName());
						$relativePath = substr($dir, $pos);
						$info = $this->getFile($relativePath,$value);
						$result[] = $info;
					}
				} 
			} 
		} 
	   
		return $result;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   1.6
	 */
	protected function populateState()
	{
		jimport('joomla.filesystem.file');
		$app = JFactory::getApplication('administrator');

		// Load the User state.
		$pk = $app->input->getInt('id');
		$this->setState('extension.id', $pk);
		
		// Load the parameters.
		$params = JComponentHelper::getParams('com_templates');
		$this->setState('params', $params);
	}

	/**
	 * Method to get the template information.
	 *
	 * @return  mixed  Object if successful, false if not and internal error is set.
	 * @since   1.6
	 */
	public function &getTemplate()
	{
		if (empty($this->template))
		{
			$pk		= $this->getState('extension.id');
			$db		= $this->getDbo();
            $app = JFactory::getApplication();

			// Get the template information.
			$query = $db->getQuery(true)
				->select('extension_id, client_id, element')
				->from('#__extensions')
				->where($db->quoteName('extension_id') . ' = ' . (int) $pk)
				->where($db->quoteName('type') . ' = ' . $db->quote('template'));
			$db->setQuery($query);

			try
			{
				$result = $db->loadObject();
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage($e->getMessage(),'warning');
				$this->template = false;
				return false;
			}

			if (empty($result))
			{
                $app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_EXTENSION_RECORD_NOT_FOUND'),'warning');
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
	 * @return  boolean   true if name is not used, false otherwise
	 * @since	2.5
	 */
	public function checkNewName()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__extensions')
			->where('name = ' . $db->quote($this->getState('new_name')));
		$db->setQuery($query);
		return ($db->loadResult() == 0);
	}

	/**
	 * Method to check if new template name already exists
	 *
	 * @return  string     name of current template
	 * @since	2.5
	 */
	public function getFromName()
	{
		return $this->getTemplate()->element;
	}

	/**
	 * Method to check if new template name already exists
	 *
	 * @return  boolean   true if name is not used, false otherwise
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
	 * @return  boolean   true if delete successful, false otherwise
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
	 * @return  boolean   true if successful, false otherwise
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

		jimport('joomla.filesystem.file');
		foreach ($files as $file)
		{
			$newFile = str_replace($oldName, $newName, $file);
			$result = JFile::move($file, $newFile) && $result;
		}

		// Edit XML file
		$xmlFile = $this->getState('to_path') . '/templateDetails.xml';
		if (JFile::exists($xmlFile))
		{
			$contents = file_get_contents($xmlFile);
			$pattern[] = '#<name>\s*' . $oldName . '\s*</name>#i';
			$replace[] = '<name>'. $newName . '</name>';
			$pattern[] = '#<language(.*)' . $oldName . '(.*)</language>#';
			$replace[] = '<language${1}' . $newName . '${2}</language>';
			$contents = preg_replace($pattern, $replace, $contents);
			$result = JFile::write($xmlFile, $contents) && $result;
		}

		return $result;
	}
	
	/**
	 * Method to get the record form.
	 *
	 * @param   array      $data        Data for the form.
	 * @param   boolean    $loadData    True if the form is to load its own data (default case), false if not.
	 * @return  JForm    A JForm object on success, false on failure
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$app = JFactory::getApplication();
	
		// Codemirror or Editor None should be enabled
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
		->select('COUNT(*)')
		->from('#__extensions as a')
		->where('(a.name =' . $db->quote('plg_editors_codemirror') . ' AND a.enabled = 1) OR (a.name =' . $db->quote('plg_editors_none') . ' AND a.enabled = 1)');
		$db->setQuery($query);
		$state = $db->loadResult();
		if ((int) $state < 1)
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_EDITOR_DISABLED'), 'warning');
		}
	
		// Get the form.
		$form = $this->loadForm('com_templates.source', 'source', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
	
		return $form;
	}
	
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		$data = $this->getSource();
	
		$this->preprocessData('com_templates.source', $data);
	
		return $data;
	}
	
	/**
	 * Method to get a single record.
	 *
	 * @return  mixed  Object on success, false on failure.
	 * @since   1.6
	 */
	public function &getSource()
	{
        $app = JFactory::getApplication();
		$item = new stdClass;
		if (!$this->template)
		{
			$this->getTemplate();
		}
	
		if ($this->template)
		{
            $input = JFactory::getApplication()->input;
			$fileName = base64_decode($input->get('file'));
            if(empty($fileName))
            {
                $fileName = '/index.php';
            }
			$client = JApplicationHelper::getClientInfo($this->template->client_id);
			$filePath = JPath::clean($client->path . '/templates/' . $this->template->element . $fileName);
	
			if (file_exists($filePath))
			{
				$item->extension_id = $this->getState('extension.id');
				$item->filename = $fileName;
				$item->source = file_get_contents($filePath);
			}
			else
			{
                $app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_SOURCE_FILE_NOT_FOUND'),'warning');
			}
		}
	
		return $item;
	}
	
	/**
	 * Method to store the source file contents.
	 *
	 * @param   array  The souce data to save.
	 *
	 * @return  boolean  True on success, false otherwise and internal error set.
	 * @since   1.6
	 */
	public function save($data)
	{
		jimport('joomla.filesystem.file');
	
		// Get the template.
		$template = $this->getTemplate();
		if (empty($template))
		{
			return false;
		}

        $app = JFactory::getApplication();
        $fileName = base64_decode($app->input->get('file'));
		$client = JApplicationHelper::getClientInfo($template->client_id);
		$filePath = JPath::clean($client->path . '/templates/' . $template->element . '/' . $fileName);
	
		// Include the extension plugins for the save events.
		JPluginHelper::importPlugin('extension');
	
		// Try to make the template file writeable.
		if (JPath::isOwner($filePath) && !JPath::setPermissions($filePath, '0644'))
		{
            $app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_SOURCE_FILE_NOT_WRITABLE'),'warning');
			return false;
		}
	
		$return = JFile::write($filePath, $data['source']);
	
		// Try to make the template file unwritable.
		if (JPath::isOwner($filePath) && !JPath::setPermissions($filePath, '0444'))
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_SOURCE_FILE_NOT_UNWRITABLE'),'warning');
			return false;
		}
		elseif (!$return)
		{
            $app->enqueueMessage(JText::sprintf('COM_TEMPLATES_ERROR_FAILED_TO_SAVE_FILENAME', $fileName),'warning');
			return false;
		}
	
		return true;
	}

}
