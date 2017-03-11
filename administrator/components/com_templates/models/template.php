<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Template model class.
 *
 * @since  1.6
 */
class TemplatesModelTemplate extends JModelForm
{
	/**
	 * The information in a template
	 *
	 * @var    stdClass
	 * @since  1.6
	 */
	protected $template = null;

	/**
	 * The path to the template
	 *
	 * @var    stdClass
	 * @since  3.2
	 */
	protected $element = null;

	/**
	 * Internal method to get file properties.
	 *
	 * @param   string  $path  The base path.
	 * @param   string  $name  The file name.
	 *
	 * @return  object
	 *
	 * @since   1.6
	 */
	protected function getFile($path, $name)
	{
		$temp = new stdClass;

		if ($template = $this->getTemplate())
		{
			$temp->name = $name;
			$temp->id = urlencode(base64_encode($path . $name));

			return $temp;
		}
	}

	/**
	 * Method to get a list of all the files to edit in a template.
	 *
	 * @return  array  A nested array of relevant files.
	 *
	 * @since   1.6
	 */
	public function getFiles()
	{
		$result = array();

		if ($template = $this->getTemplate())
		{
			jimport('joomla.filesystem.folder');
			$app    = JFactory::getApplication();
			$client = JApplicationHelper::getClientInfo($template->client_id);
			$path   = JPath::clean($client->path . '/templates/' . $template->element . '/');
			$lang   = JFactory::getLanguage();

			// Load the core and/or local language file(s).
			$lang->load('tpl_' . $template->element, $client->path, null, false, true) ||
			$lang->load('tpl_' . $template->element, $client->path . '/templates/' . $template->element, null, false, true);
			$this->element = $path;

			if (!is_writable($path))
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_DIRECTORY_NOT_WRITABLE'), 'error');
			}

			if (is_dir($path))
			{
				$result = $this->getDirectoryTree($path);
			}
			else
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_TEMPLATE_FOLDER_NOT_FOUND'), 'error');

				return false;
			}
		}

		return $result;
	}

	/**
	 * Get the directory tree.
	 *
	 * @param   string  $dir  The path of the directory to scan
	 *
	 * @return  array
	 *
	 * @since   3.2
	 */
	public function getDirectoryTree($dir)
	{
		$result = array();

		$dirFiles = scandir($dir);

		foreach ($dirFiles as $key => $value)
		{
			if (!in_array($value, array('.', '..')))
			{
				if (is_dir($dir . $value))
				{
					$relativePath = str_replace($this->element, '', $dir . $value);
					$result['/' . $relativePath] = $this->getDirectoryTree($dir . $value . '/');
				}
				else
				{
					$ext           = pathinfo($dir . $value, PATHINFO_EXTENSION);
					$allowedFormat = $this->checkFormat($ext);

					if ($allowedFormat == true)
					{
						$relativePath = str_replace($this->element, '', $dir);
						$info = $this->getFile('/' . $relativePath, $value);
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
	 * @return  void
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
	 *
	 * @since   1.6
	 */
	public function &getTemplate()
	{
		if (empty($this->template))
		{
			$pk  = $this->getState('extension.id');
			$db  = $this->getDbo();
			$app = JFactory::getApplication();

			// Get the template information.
			$query = $db->getQuery(true)
				->select('extension_id, client_id, element, name, manifest_cache')
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
				$app->enqueueMessage($e->getMessage(), 'warning');
				$this->template = false;

				return false;
			}

			if (empty($result))
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_EXTENSION_RECORD_NOT_FOUND'), 'error');
				$this->template = false;
			}
			else
			{
				$this->template = $result;
			}
		}

		return $this->template;
	}

	/**
	 * Method to check if new template name already exists
	 *
	 * @return  boolean   true if name is not used, false otherwise
	 *
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
	 *
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
	 *
	 * @since	2.5
	 */
	public function copy()
	{
		$app = JFactory::getApplication();

		if ($template = $this->getTemplate())
		{
			jimport('joomla.filesystem.folder');
			$client = JApplicationHelper::getClientInfo($template->client_id);
			$fromPath = JPath::clean($client->path . '/templates/' . $template->element . '/');

			// Delete new folder if it exists
			$toPath = $this->getState('to_path');

			if (JFolder::exists($toPath))
			{
				if (!JFolder::delete($toPath))
				{
					$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_COULD_NOT_WRITE'), 'error');

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
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_INVALID_FROM_NAME'), 'error');

			return false;
		}
	}

	/**
	 * Method to delete tmp folder
	 *
	 * @return  boolean   true if delete successful, false otherwise
	 *
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
	 * @return  boolean  true if successful, false otherwise
	 *
	 * @since	2.5
	 */
	protected function fixTemplateName()
	{
		// Rename Language files
		// Get list of language files
		$result   = true;
		$files    = JFolder::files($this->getState('to_path'), '.ini', true, true);
		$newName  = strtolower($this->getState('new_name'));
		$template = $this->getTemplate();
		$oldName  = $template->element;
		$manifest = json_decode($template->manifest_cache);

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
			$pattern[] = '#<name>\s*' . $manifest->name . '\s*</name>#i';
			$replace[] = '<name>' . $newName . '</name>';
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
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm    A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$app = JFactory::getApplication();

		// Codemirror or Editor None should be enabled
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__extensions as a')
			->where(
				'(a.name =' . $db->quote('plg_editors_codemirror') .
				' AND a.enabled = 1) OR (a.name =' .
				$db->quote('plg_editors_none') .
				' AND a.enabled = 1)'
			);
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
	 *
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
	 *
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
			$input    = JFactory::getApplication()->input;
			$fileName = base64_decode($input->get('file'));
			$client   = JApplicationHelper::getClientInfo($this->template->client_id);

			try
			{
				$filePath = JPath::check($client->path . '/templates/' . $this->template->element . '/' . $fileName);
			}
			catch (Exception $e)
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_SOURCE_FILE_NOT_FOUND'), 'error');
				return;
			}

			if (file_exists($filePath))
			{
				$item->extension_id = $this->getState('extension.id');
				$item->filename = $fileName;
				$item->source = file_get_contents($filePath);
			}
			else
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_SOURCE_FILE_NOT_FOUND'), 'error');
			}
		}

		return $item;
	}

	/**
	 * Method to store the source file contents.
	 *
	 * @param   array  $data  The source data to save.
	 *
	 * @return  boolean  True on success, false otherwise and internal error set.
	 *
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
		chown($filePath, $user);
		JPath::setPermissions($filePath, '0644');

		// Try to make the template file writable.
		if (!is_writable($filePath))
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_SOURCE_FILE_NOT_WRITABLE'), 'warning');
			$app->enqueueMessage(JText::sprintf('COM_TEMPLATES_FILE_PERMISSIONS', JPath::getPermissions($filePath)), 'warning');

			if (!JPath::isOwner($filePath))
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_CHECK_FILE_OWNERSHIP'), 'warning');
			}

			return false;
		}

		// Make sure EOL is Unix
		$data['source'] = str_replace(array("\r\n", "\r"), "\n", $data['source']);

		$return = JFile::write($filePath, $data['source']);

		if (!$return)
		{
			$app->enqueueMessage(JText::sprintf('COM_TEMPLATES_ERROR_FAILED_TO_SAVE_FILENAME', $fileName), 'error');

			return false;
		}

		// Get the extension of the changed file.
		$explodeArray = explode('.', $fileName);
		$ext = end($explodeArray);

		if ($ext == 'less')
		{
			$app->enqueueMessage(JText::sprintf('COM_TEMPLATES_COMPILE_LESS', $fileName));
		}

		return true;
	}

	/**
	 * Get overrides folder.
	 *
	 * @param   string   $name    The name of override.
	 * @param   string   $path    Location of override.
	 * @param   boolean  $plugin  Are we in plugin mode?
	 *
	 * @return  object  containing override name and path.
	 *
	 * @since   3.2
	 */
	public function getOverridesFolder($name, $path, $plugin = false)
	{
		if ($plugin === false)
		{
			$folder = new stdClass;
			$folder->name = $name;
			$folder->path = base64_encode($path . $name);

			return $folder;
		}

		$pluginFolderName = explode('_', $name);
		$pluginFolderName = end($pluginFolderName);

		$folder = new stdClass;
		$folder->name = $name;
		$folder->path = base64_encode($path . $pluginFolderName);

		return $folder;

	}

	/**
	 * Get a list of overrides.
	 *
	 * @return  array containing overrides.
	 *
	 * @since   3.2
	 */
	public function getOverridesList()
	{
		if ($template = $this->getTemplate())
		{
			$client        = JApplicationHelper::getClientInfo($template->client_id);
			$componentPath = JPath::clean($client->path . '/components/');
			$modulePath    = JPath::clean($client->path . '/modules/');
			$layoutPath    = JPath::clean(JPATH_ROOT . '/layouts/joomla/');
			$pluginPath    = JPath::clean(JPATH_ROOT . '/plugins/');
			$components    = JFolder::folders($componentPath);

			foreach ($components as $component)
			{
				if (file_exists($componentPath . '/' . $component . '/views/'))
				{
					$viewPath = JPath::clean($componentPath . '/' . $component . '/views/');
				}
				elseif (file_exists($componentPath . '/' . $component . '/view/'))
				{
					$viewPath = JPath::clean($componentPath . '/' . $component . '/view/');
				}
				else
				{
					$viewPath = '';
				}

				if ($viewPath)
				{
					$views = JFolder::folders($viewPath);

					foreach ($views as $view)
					{
						// Only show the view has layout inside it
						if (file_exists($viewPath . $view . '/tmpl'))
						{
							$result['components'][$component][] = $this->getOverridesFolder($view, $viewPath);
						}
					}
				}
			}

			$modules = JFolder::folders($modulePath);

			foreach ($modules as $module)
			{
				$result['modules'][] = $this->getOverridesFolder($module, $modulePath);
			}

			$layouts = JFolder::folders($layoutPath);

			foreach ($layouts as $layout)
			{
				$result['layouts'][] = $this->getOverridesFolder($layout, $layoutPath);
			}

			$pluginTypes = JFolder::folders($pluginPath);

			foreach ($pluginTypes as $pluginType)
			{
				$pluginTypesPath = JPath::clean($pluginPath . $pluginType . '/');
				$plugins         = JFolder::folders($pluginTypesPath);

				foreach ($plugins as $plugin)
				{
					// Only if the plugin supports views
					if (file_exists($pluginTypesPath . $plugin . '/tmpl'))
					{
						$pluginName = 'plg_' . $pluginType . '_' . $plugin;
						$result['plugins'][]= $this->getOverridesFolder($pluginName, $pluginTypesPath, true);
					}
				}				
			}
		}

		if (!empty($result))
		{
			return $result;
		}
	}

	/**
	 * Create overrides.
	 *
	 * @param   string  $override  The override location.
	 *
	 * @return   boolean  true if override creation is successful, false otherwise
	 *
	 * @since   3.2
	 */
	public function createOverride($override)
	{
		jimport('joomla.filesystem.folder');

		if ($template = $this->getTemplate())
		{
			$app            = JFactory::getApplication();
			$explodeArray   = explode(DIRECTORY_SEPARATOR, $override);
			$name           = end($explodeArray);
			$client 	    = JApplicationHelper::getClientInfo($template->client_id);

			// Something special is needed for the plugins
			if (strpos($override, 'plugins') != false)
			{
				$i        = count($explodeArray) - 2;
				$name     = 'plg_' . $explodeArray[$i] . '_' . $name;
				$htmlPath = JPath::clean($client->path . '/templates/' . $template->element . '/html/' . $name);
			}
			elseif (stristr($name, 'mod_') != false)
			{
				$htmlPath = JPath::clean($client->path . '/templates/' . $template->element . '/html/' . $name);
			}
			elseif (stristr($override, 'com_') != false)
			{
				$folderExplode = explode(DIRECTORY_SEPARATOR, $override);
				$size = count($folderExplode);

				$url = JPath::clean($folderExplode[$size - 3] . '/' . $folderExplode[$size - 1]);

				$htmlPath = JPath::clean($client->path . '/templates/' . $template->element . '/html/' . $url);
			}
			else
			{
				$htmlPath   = JPath::clean($client->path . '/templates/' . $template->element . '/html/layouts/joomla/' . $name);
			}

			// Check Html folder, create if not exist
			if (!JFolder::exists($htmlPath))
			{
				if (!JFolder::create($htmlPath))
				{
					$app->enqueueMessage(JText::_('COM_TEMPLATES_FOLDER_ERROR'), 'error');

					return false;
				}
			}

			if (stristr($name, 'plg_') != false)
			{
				$return = $this->createTemplateOverride(JPath::clean($override . '/tmpl'), $htmlPath);
			}
			elseif (stristr($name, 'mod_') != false)
			{
				$return = $this->createTemplateOverride(JPath::clean($override . '/tmpl'), $htmlPath);
			}
			elseif (stristr($override, 'com_') != false)
			{
				$return = $this->createTemplateOverride(JPath::clean($override . '/tmpl'), $htmlPath);
			}
			else
			{
				$return = $this->createTemplateOverride($override, $htmlPath);
			}

			if ($return)
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_OVERRIDE_CREATED') . str_replace(JPATH_ROOT, '', $htmlPath));

				return true;
			}
			else
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_OVERRIDE_FAILED'), 'error');

				return false;
			}
		}
	}

	/**
	 * Create override folder & file
	 *
	 * @param   string  $overridePath  The override location
	 * @param   string  $htmlPath      The html location
	 *
	 * @return  boolean                True on success. False otherwise.
	 */
	public function createTemplateOverride($overridePath, $htmlPath)
	{
		$return = false;

		if (empty($overridePath) || empty($htmlPath))
		{
			return $return;
		}

		// Get list of template folders
		$folders = JFolder::folders($overridePath, null, true, true);

		if (!empty($folders))
		{
			foreach ($folders as $folder)
			{
				$htmlFolder = $htmlPath . str_replace($overridePath, '', $folder);

				if (!JFolder::exists($htmlFolder))
				{
					JFolder::create($htmlFolder);
				}
			}
		}

		// Get list of template files (Only get *.php file for template file)
		$files = JFolder::files($overridePath, '.php', true, true);

		if (empty($files))
		{
			return true;
		}

		foreach ($files as $file)
		{
			$overrideFilePath = str_replace($overridePath, '', $file);
			$htmlFilePath = $htmlPath . $overrideFilePath;

			if (JFile::exists($htmlFilePath))
			{
				// Generate new unique file name base on current time
				$today = JFactory::getDate();
				$htmlFilePath = JFile::stripExt($htmlFilePath) . '-' . $today->format('Ymd-His') . '.' . JFile::getExt($htmlFilePath);
			}

			$return = JFile::copy($file, $htmlFilePath, '', true);
		}

		return $return;
	}

	/**
	 * Compile less using the less compiler under /build.
	 *
	 * @param   string  $input  The relative location of the less file.
	 *
	 * @return  boolean  true if compilation is successful, false otherwise
	 *
	 * @since   3.2
	 */
	public function compileLess($input)
	{
		if ($template = $this->getTemplate())
		{
			$app          = JFactory::getApplication();
			$client       = JApplicationHelper::getClientInfo($template->client_id);
			$path         = JPath::clean($client->path . '/templates/' . $template->element . '/');
			$inFile       = urldecode(base64_decode($input));
			$explodeArray = explode('/', $inFile);
			$fileName     = end($explodeArray);
			$outFile      = reset(explode('.', $fileName));

			$less = new JLess;
			$less->setFormatter(new JLessFormatterJoomla);

			try
			{
				$less->compileFile($path . $inFile, $path . 'css/' . $outFile . '.css');

				return true;
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');
			}
		}
	}

	/**
	 * Delete a particular file.
	 *
	 * @param   string  $file  The relative location of the file.
	 *
	 * @return   boolean  True if file deletion is successful, false otherwise
	 *
	 * @since   3.2
	 */
	public function deleteFile($file)
	{
		if ($template = $this->getTemplate())
		{
			$app      = JFactory::getApplication();
			$client   = JApplicationHelper::getClientInfo($template->client_id);
			$path     = JPath::clean($client->path . '/templates/' . $template->element . '/');
			$filePath = $path . urldecode(base64_decode($file));

			$return = JFile::delete($filePath);

			if (!$return)
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_DELETE_FAIL'), 'error');

				return false;
			}

			return true;
		}
	}

	/**
	 * Create new file.
	 *
	 * @param   string  $name      The name of file.
	 * @param   string  $type      The extension of the file.
	 * @param   string  $location  Location for the new file.
	 *
	 * @return  boolean  true if file created successfully, false otherwise
	 *
	 * @since   3.2
	 */
	public function createFile($name, $type, $location)
	{
		if ($template = $this->getTemplate())
		{
			$app    = JFactory::getApplication();
			$client = JApplicationHelper::getClientInfo($template->client_id);
			$path   = JPath::clean($client->path . '/templates/' . $template->element . '/');

			if (file_exists(JPath::clean($path . '/' . $location . '/' . $name . '.' . $type)))
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_EXISTS'), 'error');

				return false;
			}

			if (!fopen(JPath::clean($path . '/' . $location . '/' . $name . '.' . $type), 'x'))
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_CREATE_ERROR'), 'error');

				return false;
			}
			// Check if the format is allowed and will be showed in the backend
			$check = $this->checkFormat($type);

			// Add a message if we are not allowed to show this file in the backend.
			if (!$check)
			{
				$app->enqueueMessage(JText::sprintf('COM_TEMPLATES_WARNING_FORMAT_WILL_NOT_BE_VISIBLE', $type), 'warning');
			}

			return true;
		}
	}

	/**
	 * Upload new file.
	 *
	 * @param   string  $file      The name of the file.
	 * @param   string  $location  Location for the new file.
	 *
	 * @return   boolean  True if file uploaded successfully, false otherwise
	 *
	 * @since   3.2
	 */
	public function uploadFile($file, $location)
	{
		jimport('joomla.filesystem.folder');

		if ($template = $this->getTemplate())
		{
			$app      = JFactory::getApplication();
			$client   = JApplicationHelper::getClientInfo($template->client_id);
			$path     = JPath::clean($client->path . '/templates/' . $template->element . '/');
			$fileName = JFile::makeSafe($file['name']);

			$err = null;
			JLoader::register('TemplateHelper', JPATH_ADMINISTRATOR . '/components/com_templates/helpers/template.php');

			if (!TemplateHelper::canUpload($file, $err))
			{
				// Can't upload the file
				return false;
			}

			if (file_exists(JPath::clean($path . '/' . $location . '/' . $file['name'])))
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_EXISTS'), 'error');

				return false;
			}

			if (!JFile::upload($file['tmp_name'], JPath::clean($path . '/' . $location . '/' . $fileName)))
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_UPLOAD_ERROR'), 'error');

				return false;
			}

			$url = JPath::clean($location . '/' . $fileName);

			return $url;
		}
	}

	/**
	 * Create new folder.
	 *
	 * @param   string  $name      The name of the new folder.
	 * @param   string  $location  Location for the new folder.
	 *
	 * @return   boolean  True if override folder is created successfully, false otherwise
	 *
	 * @since   3.2
	 */
	public function createFolder($name, $location)
	{
		jimport('joomla.filesystem.folder');

		if ($template = $this->getTemplate())
		{
			$app    = JFactory::getApplication();
			$client = JApplicationHelper::getClientInfo($template->client_id);
			$path   = JPath::clean($client->path . '/templates/' . $template->element . '/');

			if (file_exists(JPath::clean($path . '/' . $location . '/' . $name . '/')))
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_FOLDER_EXISTS'), 'error');

				return false;
			}

			if (!JFolder::create(JPath::clean($path . '/' . $location . '/' . $name)))
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_FOLDER_CREATE_ERROR'), 'error');

				return false;
			}

			return true;
		}
	}

	/**
	 * Delete a folder.
	 *
	 * @param   string  $location  The name and location of the folder.
	 *
	 * @return  boolean  True if override folder is deleted successfully, false otherwise
	 *
	 * @since   3.2
	 */
	public function deleteFolder($location)
	{
		jimport('joomla.filesystem.folder');

		if ($template = $this->getTemplate())
		{
			$app    = JFactory::getApplication();
			$client = JApplicationHelper::getClientInfo($template->client_id);
			$path   = JPath::clean($client->path . '/templates/' . $template->element . '/' . $location);

			if (!file_exists($path))
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_FOLDER_NOT_EXISTS'), 'error');

				return false;
			}

			$return = JFolder::delete($path);

			if (!$return)
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_DELETE_ERROR'), 'error');

				return false;
			}

			return true;
		}
	}

	/**
	 * Rename a file.
	 *
	 * @param   string  $file  The name and location of the old file
	 * @param   string  $name  The new name of the file.
	 *
	 * @return  string  Encoded string containing the new file location.
	 *
	 * @since   3.2
	 */
	public function renameFile($file, $name)
	{
		if ($template = $this->getTemplate())
		{
			$app          = JFactory::getApplication();
			$client       = JApplicationHelper::getClientInfo($template->client_id);
			$path         = JPath::clean($client->path . '/templates/' . $template->element . '/');
			$fileName     = base64_decode($file);
			$explodeArray = explode('.', $fileName);
			$type         = end($explodeArray);
			$explodeArray = explode('/', $fileName);
			$newName      = str_replace(end($explodeArray), $name . '.' . $type, $fileName);

			if (file_exists($path . $newName))
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_EXISTS'), 'error');

				return false;
			}

			if (!rename($path . $fileName, $path . $newName))
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_RENAME_ERROR'), 'error');

				return false;
			}

			return base64_encode($newName);
		}
	}

	/**
	 * Get an image address, height and width.
	 *
	 * @return  array an associative array containing image address, height and width.
	 *
	 * @since   3.2
	 */
	public function getImage()
	{
		if ($template = $this->getTemplate())
		{
			$app      = JFactory::getApplication();
			$client   = JApplicationHelper::getClientInfo($template->client_id);
			$fileName = base64_decode($app->input->get('file'));
			$path     = JPath::clean($client->path . '/templates/' . $template->element . '/');

			if (stristr($client->path, 'administrator') == false)
			{
				$folder = '/templates/';
			}
			else
			{
				$folder = '/administrator/templates/';
			}

			$uri = JUri::root(true) . $folder . $template->element;

			if (file_exists(JPath::clean($path . $fileName)))
			{
				$JImage = new JImage(JPath::clean($path . $fileName));
				$image['address'] = $uri . $fileName;
				$image['path']    = $fileName;
				$image['height']  = $JImage->getHeight();
				$image['width']   = $JImage->getWidth();
			}

			else
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_IMAGE_FILE_NOT_FOUND'), 'error');

				return false;
			}

			return $image;
		}
	}

	/**
	 * Crop an image.
	 *
	 * @param   string  $file  The name and location of the file
	 * @param   string  $w     width.
	 * @param   string  $h     height.
	 * @param   string  $x     x-coordinate.
	 * @param   string  $y     y-coordinate.
	 *
	 * @return  boolean     true if image cropped successfully, false otherwise.
	 *
	 * @since   3.2
	 */
	public function cropImage($file, $w, $h, $x, $y)
	{
		if ($template = $this->getTemplate())
		{
			$app      = JFactory::getApplication();
			$client   = JApplicationHelper::getClientInfo($template->client_id);
			$relPath  = base64_decode($file);
			$path     = JPath::clean($client->path . '/templates/' . $template->element . '/' . $relPath);
			$JImage   = new JImage($path);

			try
			{
				$image = $JImage->crop($w, $h, $x, $y, true);
				$image->toFile($path);

				return true;
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');
			}
		}
	}

	/**
	 * Resize an image.
	 *
	 * @param   string  $file    The name and location of the file
	 * @param   string  $width   The new width of the image.
	 * @param   string  $height  The new height of the image.
	 *
	 * @return   boolean  true if image resize successful, false otherwise.
	 *
	 * @since   3.2
	 */
	public function resizeImage($file, $width, $height)
	{
		if ($template = $this->getTemplate())
		{
			$app     = JFactory::getApplication();
			$client  = JApplicationHelper::getClientInfo($template->client_id);
			$relPath = base64_decode($file);
			$path    = JPath::clean($client->path . '/templates/' . $template->element . '/' . $relPath);

			$JImage = new JImage($path);

			try
			{
				$image = $JImage->resize($width, $height, true, 1);
				$image->toFile($path);

				return true;
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');
			}
		}
	}

	/**
	 * Template preview.
	 *
	 * @return  object  object containing the id of the template.
	 *
	 * @since   3.2
	 */
	public function getPreview()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('id, client_id');
		$query->from('#__template_styles');
		$query->where($db->quoteName('template') . ' = ' . $db->quote($this->template->element));

		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'warning');
		}

		if (empty($result))
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_EXTENSION_RECORD_NOT_FOUND'), 'warning');
		}
		else
		{
			return $result;
		}
	}

	/**
	 * Rename a file.
	 *
	 * @return  mixed  array on success, false on failure
	 *
	 * @since   3.2
	 */
	public function getFont()
	{
		if ($template = $this->getTemplate())
		{
			$app          = JFactory::getApplication();
			$client       = JApplicationHelper::getClientInfo($template->client_id);
			$relPath      = base64_decode($app->input->get('file'));
			$explodeArray = explode('/', $relPath);
			$fileName     = end($explodeArray);
			$path         = JPath::clean($client->path . '/templates/' . $template->element . '/' . $relPath);

			if (stristr($client->path, 'administrator') == false)
			{
				$folder = '/templates/';
			}
			else
			{
				$folder = '/administrator/templates/';
			}

			$uri = JUri::root(true) . $folder . $template->element;

			if (file_exists(JPath::clean($path)))
			{
				$font['address'] = $uri . $relPath;

				$font['rel_path'] = $relPath;

				$font['name'] = $fileName;
			}

			else
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_FONT_FILE_NOT_FOUND'), 'error');

				return false;
			}

			return $font;
		}
	}

	/**
	 * Copy a file.
	 *
	 * @param   string  $newName   The name of the copied file
	 * @param   string  $location  The final location where the file is to be copied
	 * @param   string  $file      The name and location of the file
	 *
	 * @return   boolean  true if image resize successful, false otherwise.
	 *
	 * @since   3.2
	 */
	public function copyFile($newName, $location, $file)
	{
		if ($template = $this->getTemplate())
		{
			$app          = JFactory::getApplication();
			$client       = JApplicationHelper::getClientInfo($template->client_id);
			$relPath      = base64_decode($file);
			$explodeArray = explode('.', $relPath);
			$ext          = end($explodeArray);
			$path         = JPath::clean($client->path . '/templates/' . $template->element . '/');
			$newPath      = JPath::clean($path . '/' . $location . '/' . $newName . '.' . $ext);

			if (file_exists($newPath))
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_EXISTS'), 'error');

				return false;
			}

			if (JFile::copy($path . $relPath, $newPath))
			{
				$app->enqueueMessage(JText::sprintf('COM_TEMPLATES_FILE_COPY_SUCCESS', $newName . '.' . $ext));

				return true;
			}
			else
			{
				return false;
			}
		}
	}

	/**
	 * Get the compressed files.
	 *
	 * @return   array if file exists, false otherwise
	 *
	 * @since   3.2
	 */
	public function getArchive()
	{
		if ($template = $this->getTemplate())
		{
			$app     = JFactory::getApplication();
			$client  = JApplicationHelper::getClientInfo($template->client_id);
			$relPath = base64_decode($app->input->get('file'));
			$path    = JPath::clean($client->path . '/templates/' . $template->element . '/' . $relPath);

			if (file_exists(JPath::clean($path)))
			{
				$files = array();
				$zip = new ZipArchive;

				if ($zip->open($path) === true)
				{
					for ($i = 0; $i < $zip->numFiles; $i++)
					{
						$entry = $zip->getNameIndex($i);
						$files[] = $entry;
					}
				}
				else
				{
					$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_ARCHIVE_OPEN_FAIL'), 'error');

					return false;
				}
			}
			else
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_FONT_FILE_NOT_FOUND'), 'error');

				return false;
			}

			return $files;
		}
	}

	/**
	 * Extract contents of an archive file.
	 *
	 * @param   string  $file  The name and location of the file
	 *
	 * @return  boolean  true if image extraction is successful, false otherwise.
	 *
	 * @since   3.2
	 */
	public function extractArchive($file)
	{
		if ($template = $this->getTemplate())
		{
			$app          = JFactory::getApplication();
			$client       = JApplicationHelper::getClientInfo($template->client_id);
			$relPath      = base64_decode($file);
			$explodeArray = explode('/', $relPath);
			$fileName     = end($explodeArray);
			$folderPath   = stristr($relPath, $fileName, true);
			$path         = JPath::clean($client->path . '/templates/' . $template->element . '/' . $folderPath . '/');

			if (file_exists(JPath::clean($path . '/' . $fileName)))
			{
				$zip = new ZipArchive;

				if ($zip->open(JPath::clean($path . '/' . $fileName)) === true)
				{
					for ($i = 0; $i < $zip->numFiles; $i++)
					{
						$entry = $zip->getNameIndex($i);

						if (file_exists(JPath::clean($path . '/' . $entry)))
						{
							$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_ARCHIVE_EXISTS'), 'error');

							return false;
						}
					}

					$zip->extractTo($path);

					return true;
				}
				else
				{
					$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_ARCHIVE_OPEN_FAIL'), 'error');

					return false;
				}
			}
			else
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_ARCHIVE_NOT_FOUND'), 'error');

				return false;
			}
		}
	}

	/**
 	* Check if the extension is allowed and will be shown in the template manager
 	*
	* @param   string  $ext  The extension to check if it is allowed
 	*
 	* @return  boolean  true if the extension is allowed false otherwise
 	*
 	* @since   3.6.0
	*/
	protected function checkFormat($ext)
	{
		if (!isset($this->allowedFormats))
		{
			$params       = JComponentHelper::getParams('com_templates');
			$imageTypes   = explode(',', $params->get('image_formats'));
			$sourceTypes  = explode(',', $params->get('source_formats'));
			$fontTypes    = explode(',', $params->get('font_formats'));
			$archiveTypes = explode(',', $params->get('compressed_formats'));

			$this->allowedFormats = array_merge($imageTypes, $sourceTypes, $fontTypes, $archiveTypes);
		}

		return in_array($ext, $this->allowedFormats);
	}
}
