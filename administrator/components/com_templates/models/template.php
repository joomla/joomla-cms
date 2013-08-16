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
	protected $element = null;

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
            $this->element = $path;

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
                    $relativePath = str_replace($this->element,'',$dir . $value);
					$result['/' . $relativePath] = $this->getDirectoryTree($dir . $value . '/');
				} 
				else 
				{
					$ext = pathinfo($dir . $value, PATHINFO_EXTENSION);
					if(in_array($ext, array('css','js','php','xml','ini','less','jpg','jpeg','png','gif')))
					{
                        $relativePath = str_replace($this->element,'',$dir);
						$info = $this->getFile('/' . $relativePath,$value);
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
        $app = JFactory::getApplication();
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
                    $app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_COULD_NOT_WRITE'),'error');
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
            $app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_INVALID_FROM_NAME'),'error');
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
			$client = JApplicationHelper::getClientInfo($this->template->client_id);
			$filePath = JPath::clean($client->path . '/templates/' . $this->template->element . '/' . $fileName);
	
			if (file_exists($filePath))
			{
				$item->extension_id = $this->getState('extension.id');
				$item->filename = $fileName;
				$item->source = file_get_contents($filePath);
			}
			else
			{
                $app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_SOURCE_FILE_NOT_FOUND'),'error');
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

        $user = get_current_user();
        chown($filePath,$user);
        JPath::setPermissions($filePath,'0644');

		// Try to make the template file writable.
		if (!is_writable($filePath))
		{
            $app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_SOURCE_FILE_NOT_WRITABLE'),'warning');
            $app->enqueueMessage(JText::_('The File Permissions are ' . JPath::getPermissions($filePath)),'warning');
            if(!JPath::isOwner($filePath))
            {
                $app->enqueueMessage(JText::_('Check file ownership'),'warning');
            }
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

    public function getOverridesFolder($name,$path)
    {
        $folder = new stdClass();
        $folder->name = $name;
        $folder->path = base64_encode($path . $name);

        return $folder;
    }

    public function getOverridesList()
    {
        if ($template = $this->getTemplate())
        {
            $client 	= JApplicationHelper::getClientInfo($template->client_id);
            $componentPath		= JPath::clean($client->path.'/components/');
            $modulePath		= JPath::clean($client->path.'/modules/');
            $layoutPath		= JPath::clean(JPATH_ROOT . '/layouts/joomla/');
            $components = JFolder::folders($componentPath);
            foreach($components as $component)
            {
                $result['components'][] = $this->getOverridesFolder($component,$componentPath);
            }
            $modules = JFolder::folders($modulePath);
            foreach($modules as $module)
            {
                $result['modules'][] = $this->getOverridesFolder($module,$modulePath);
            }
            $layouts = JFolder::folders($layoutPath);
            foreach($layouts as $layout)
            {
                $result['layouts'][] = $this->getOverridesFolder($layout,$layoutPath);
            }

        }
        if (!empty($result))
        {
            return $result;
        }
    }

    public function createOverride($override)
    {
        jimport('joomla.filesystem.folder');
        if ($template = $this->getTemplate())
        {
            $app        = JFactory::getApplication();
            $explodeArray = explode('/',$override);
            $name       = end($explodeArray);
            $client 	= JApplicationHelper::getClientInfo($template->client_id);
            if(stristr($name,'mod_') != FALSE || stristr($name,'com_') != FALSE)
            {
                $htmlPath   = JPath::clean($client->path . '/templates/' . $template->element . '/html/' . $name);
            }
            else
            {
                $htmlPath   = JPath::clean($client->path . '/templates/' . $template->element . '/html/layouts/joomla/' .  $name);
            }

            if(JFolder::exists($htmlPath))
            {
                $app->enqueueMessage('Override already exists.','error');
                return false;
            }

            if(!JFolder::create($htmlPath))
            {
                $app->enqueueMessage('Not able to create folder.','error');
                return false;
            }

            if(stristr($name,'mod_') != FALSE)
            {
                $return = JFolder::copy($override . '/tmpl',$htmlPath,'',true);
            }
            elseif(stristr($name,'com_') != FALSE)
            {
                $folders = JFolder::folders($override . '/views');
                foreach($folders as $folder)
                {
                    if(!JFolder::create($htmlPath . '/' . $folder))
                    {
                        $app->enqueueMessage('Not able to create folder.','error');
                        return false;
                    }
                    $return = JFolder::copy($override . '/views/' . $folder . '/tmpl',$htmlPath . '/' . $folder,'',true);
                }
            }
            else
            {
                $return = JFolder::copy($override,$htmlPath,'',true);
            }

            if($return)
            {
                $app->enqueueMessage('Override created');
                return true;
            }
            else
            {
                $app->enqueueMessage('Failed to create override','error');
                return false;
            }

        }
    }

    public function compileLess($input)
    {
        if ($template = $this->getTemplate())
        {
            JLoader::registerPrefix('J', JPATH_ROOT . '/build/libraries');
            $app        = JFactory::getApplication();
            $client 	= JApplicationHelper::getClientInfo($template->client_id);
            $path       = JPath::clean($client->path . '/templates/' . $template->element . '/');
            $inFile     = urldecode(base64_decode($input));
            $explodeArray = explode('/',$inFile);
            $fileName   = end($explodeArray);
            $outFile    = reset(explode('.',$fileName));
            $less       = new JLess();
            try
            {
                $less->compileFile($path . $inFile, $path . 'css/' . $outFile . '.css');
                return true;
            }
            catch(Exception $e)
            {
                $app->enqueueMessage($e->getMessage(),'error');
            }
        }
    }

    public function deleteFile($file)
    {
        if ($template = $this->getTemplate())
        {
            $app        = JFactory::getApplication();
            $client 	= JApplicationHelper::getClientInfo($template->client_id);
            $path       = JPath::clean($client->path . '/templates/' . $template->element . '/');
            $filePath   = $path . urldecode(base64_decode($file));

            $return = JFile::delete($filePath);

            if(!$return)
            {
                $app->enqueueMessage('Not able to delete the file.','error');
                return false;
            }

            return true;
        }
    }

    public function createFile($name,$type,$location)
    {
        if ($template = $this->getTemplate())
        {
            $app        = JFactory::getApplication();
            $client 	= JApplicationHelper::getClientInfo($template->client_id);
            $path       = JPath::clean($client->path . '/templates/' . $template->element . '/');

            if(file_exists(JPath::clean($path . '/' . $location . '/' . $name . '.' . $type)))
            {
                $app->enqueueMessage('File with the same name already exists.','error');
                return false;
            }

            if(!fopen(JPath::clean($path . '/' . $location . '/' . $name . '.' . $type), 'x'))
            {
                $app->enqueueMessage('An error occurred creating the file.','error');
                return false;
            }

            return true;
        }
    }

    public function uploadFile($file, $location)
    {
        jimport('joomla.filesystem.folder');
        if ($template = $this->getTemplate())
        {
            $app        = JFactory::getApplication();
            $client 	= JApplicationHelper::getClientInfo($template->client_id);
            $path       = JPath::clean($client->path . '/templates/' . $template->element . '/');

            if(file_exists(JPath::clean($path . '/' . $location . '/' . $file['name'])))
            {
                $app->enqueueMessage('File with the same name already exists.','error');
                return false;
            }

            if(!JFile::upload($file['tmp_name'],JPath::clean($path . '/' . $location . '/' . $file['name'])))
            {
                $app->enqueueMessage('There was some error uploading the file.','error');
                return false;
            }

            return true;
        }
    }

    public function createFolder($name, $location)
    {
        jimport('joomla.filesystem.folder');
        if ($template = $this->getTemplate())
        {
            $app        = JFactory::getApplication();
            $client 	= JApplicationHelper::getClientInfo($template->client_id);
            $path       = JPath::clean($client->path . '/templates/' . $template->element . '/');

            if(file_exists(JPath::clean($path . '/' . $location . '/' . $name . '/')))
            {
                $app->enqueueMessage('Folder with the same name already exists.','error');
                return false;
            }

            if(!JFolder::create(JPath::clean($path . '/' . $location . '/' . $name)))
            {
                $app->enqueueMessage('There was some error creating the folder.','error');
                return false;
            }

            return true;
        }
    }

    public function deleteFolder($location)
    {
        jimport('joomla.filesystem.folder');
        if ($template = $this->getTemplate())
        {
            $app        = JFactory::getApplication();
            $client 	= JApplicationHelper::getClientInfo($template->client_id);
            $path       = JPath::clean($client->path . '/templates/' . $template->element . '/' . $location);

            if(!file_exists($path))
            {
                $app->enqueueMessage('The folder does not exist.','error');
                return false;
            }

            $return = JFolder::delete($path);

            if(!$return)
            {
                $app->enqueueMessage('Not able to delete the file.','error');
                return false;
            }

            return true;
        }
    }

    public function renameFile($file, $name)
    {
        if ($template = $this->getTemplate())
        {
            $app        = JFactory::getApplication();
            $client 	= JApplicationHelper::getClientInfo($template->client_id);
            $path       = JPath::clean($client->path . '/templates/' . $template->element . '/');
            $fileName   = base64_decode($file);
            $explodeArray = explode('.', $fileName);
            $type       = end($explodeArray);
            $explodeArray = explode('/', $fileName);
            $newName    = str_replace(end($explodeArray), $name . '.' . $type, $fileName);

            if(!rename($path . $fileName, $path . $newName))
            {
                $app->enqueueMessage('An error occurred creating the file.','error');
                return false;
            }

            return base64_encode($newName);
        }
    }

    public function getImage()
    {
        if ($template = $this->getTemplate())
        {
            $app        = JFactory::getApplication();
            $client 	= JApplicationHelper::getClientInfo($template->client_id);
            $fileName   = base64_decode($app->input->get('file'));
            $path       = JPath::clean($client->path . '/templates/' . $template->element . '/');

            if(file_exists(JPath::clean($path . '/' . $fileName)))
            {
                $JImage = new JImage(JPath::clean($path . '/' . $fileName));
                $explodeArray = explode('/',JPATH_ROOT);
                $base = end($explodeArray);
                $image['address'] = str_replace(JPATH_ROOT, '/' . $base, $JImage->getPath() . '?' . time());

                $image['height'] = $JImage->getHeight();
                $image['width']  = $JImage->getWidth();
            }

            else
            {
                $app->enqueueMessage('Source file not found.','error');
                return false;
            }

            return $image;
        }
    }

    public function cropImage($file, $w, $h, $x, $y)
    {
        if ($template = $this->getTemplate())
        {
            $app        = JFactory::getApplication();
            $client 	= JApplicationHelper::getClientInfo($template->client_id);
            $relPath    = base64_decode($file);
            $path       = JPath::clean($client->path . '/templates/' . $template->element . '/' . $relPath);

            $JImage = new JImage($path);

            try
            {
                $image = $JImage->crop($w, $h, $x, $y, true);
                $image->toFile($path);
                return true;
            }
            catch(Exception $e)
            {
                $app->enqueueMessage($e->getMessage(),'error');
            }
        }
    }

    public function resizeImage($file, $width, $height)
    {
        if ($template = $this->getTemplate())
        {
            $app        = JFactory::getApplication();
            $client 	= JApplicationHelper::getClientInfo($template->client_id);
            $relPath    = base64_decode($file);
            $path       = JPath::clean($client->path . '/templates/' . $template->element . '/' . $relPath);

            $JImage = new JImage($path);

            try
            {
                $image = $JImage->resize($width, $height, true, 1);
                $image->toFile($path);
                return true;
            }
            catch(Exception $e)
            {
                $app->enqueueMessage($e->getMessage(),'error');
            }
        }
    }

}
