<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 ***************************************************************************************
 * Warning: Some modifications and improved were made by the Community Juuntos for
 * the latinamerican Project Jokte! CMS
 ***************************************************************************************
 */

// No direct access.
defined('_JEXEC') or die;

// Import library dependencies

jimport('joomla.installer.installer');
jimport('joomla.installer.helper');
jimport('joomla.updater.updater');
jimport('joomla.updater.update');

/**
 * Extension Manager Install Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @since		1.5
 */
class InstallerModelInstall extends JModelLegacy
{
	/**
	 * @var object JTable object
	 */
	protected $_table = null;

	/**
	 * @var object JTable object
	 */
	protected $_url = null;

	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'com_installer.install';

    /**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'name',
				'client_id',
				'type',
				'folder',
				'extension_id',
				'update_id',
				'update_site_id',
                'update_site',
			);
		}

		parent::__construct($config);
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
		// Initialise variables.
		$app = JFactory::getApplication();
		$filters = JRequest::getVar('filters');
		if (empty($filters)) {
			$data = $app->getUserState($this->_context.'.data');
			$filters = $data['filters'];
		}
		else {
			$app->setUserState($this->_context.'.data', array('filters'=>$filters));
		}

		$this->setState('message', $app->getUserState('com_installer.message'));
		$this->setState('extension_message', $app->getUserState('com_installer.extension_message'));
		$app->setUserState('com_installer.message', '');
		$app->setUserState('com_installer.extension_message', '');

		// Recall the 'Install from Directory' path.
		$path = $app->getUserStateFromRequest($this->_context.'.install_directory', 'install_directory', $app->getCfg('tmp_path'));
		$this->setState('install.directory', $path);

        $this->setState('filter.type', isset($filters['type']) ? $filters['type'] : '');
        if (!isset($filters['update_site_id'])) JRequest::setVar('update_site_id', '1');
        $this->setState('filter.update_site_id', isset($filters['update_site_id']) ? $filters['update_site_id'] : '1');
        $this->setState('filter.folder', isset($filters['folder']) ? $filters['folder'] : '');
	$this->setState('filter.search', isset($filters['search']) ? $filters['search'] : '');

		parent::populateState('a.name', 'asc');
	}

        /**
	 * Method to get the database query
	 *
	 * @return	JDatabaseQuery	The database query
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		// grab updates ignoring new installs
		$query->select('a.*, u.name AS update_site')->from('#__updates AS a')->where('extension_id = 0')->where('u.enabled = 1');
		$query->order($this->getState('list.ordering').' '.$this->getState('list.direction'));

        if ($this->getState('filter.type') != '') $query->where('a.type = '.$db->quote($this->getState('filter.type')));
        if ($this->getState('filter.update_site_id') != '') $query->where('a.update_site_id = '.$db->quote($this->getState('filter.update_site_id')));
        if ($this->getState('filter.folder') != '') $query->where('a.folder = '.$db->quote($this->getState('filter.folder')));
	if ($this->getState('filter.search') != '') $query->where('CONCAT(a.name, a.element, a.folder) LIKE '.$db->quote('%'.$this->getState('filter.search').'%'));

        // Join update_sites
        $query->join('left', $db->nameQuote('#__update_sites').' AS u ON u.update_site_id = a.update_site_id');

		return $query;
	}

    /**
	 * Removes all of the updates from the table.
	 *
	 * @return	boolean result of operation
	 * @since	1.6
	 */
	public function purge()
	{
		$db = JFactory::getDBO();
		// Note: TRUNCATE is a DDL operation
		// This may or may not mean depending on your database
		$db->setQuery('TRUNCATE TABLE #__updates');
		if ($db->Query()) {
			// Reset the last update check timestamp
			$query = $db->getQuery(true);
			$query->update($db->nq('#__update_sites'));
			$query->set($db->nq('last_check_timestamp').' = '.$db->q(0));
			$db->setQuery($query);
			$db->query();

			$this->_message = JText::_('COM_INSTALLER_PURGED_UPDATES');
			return true;
		} else {
			$this->_message = JText::_('COM_INSTALLER_FAILED_TO_PURGE_UPDATES');
			return false;
		}
	}

    /**
	 * Finds updates for an extension.
	 *
	 * @param	int		Extension identifier to look for
	 * @return	boolean Result
	 * @since	1.6
	 */
	public function findUpdates($eid=0, $cache_timeout = 0)
	{
		$updater = JUpdater::getInstance();
		$results = $updater->findUpdates($eid, $cache_timeout);
		return true;
	}

    /**
     * Get current extensions
     *
     * @since   2.5
     * @return  object
     */
    public function getUpdates()
    {
        $db = JFactory::getDBO();
        $db->setQuery('SELECT * FROM #__updates');
        if ($updates = $db->loadObjectList())
        {
            return $updates;
        }
        return false;
    }

    /**
     * Get current extensions
     *
     * @since   2.5
     * @return  object
     */
    public function getExtensions()
    {
        $db = JFactory::getDBO();
        $db->setQuery('SELECT * FROM #__extensions');
        if ($extensions = $db->loadObjectList())
        {
            return $extensions;
        }
        return false;
    }

	/**
	 * Install an extension from either folder, url or upload.
	 *
	 * @return	boolean result of install
	 * @since	1.5
	 */
	function install()
	{
		$this->setState('action', 'install');

		// Set FTP credentials, if given.
		JClientHelper::setCredentialsFromRequest('ftp');
		$app = JFactory::getApplication();

		switch(JRequest::getWord('installtype')) {
			case 'folder':
				// Remember the 'Install from Directory' path.
				$app->getUserStateFromRequest($this->_context.'.install_directory', 'install_directory');
				$package = $this->_getPackageFromFolder();
				break;

			case 'upload':
				$package = $this->_getPackageFromUpload();
				break;

			case 'url':
				$package = $this->_getPackageFromUrl();
				break;

			default:
				$app->setUserState('com_installer.message', JText::_('COM_INSTALLER_NO_INSTALL_TYPE_FOUND'));
				return false;
				break;
		}

		// Was the package unpacked?
		if (!$package) {
			$app->setUserState('com_installer.message', JText::_('COM_INSTALLER_UNABLE_TO_FIND_INSTALL_PACKAGE'));
			return false;
		}

        // Check if its a distribution

        if ($package['type'] == 'distribution')
        {
            // Set some model state values
            $app	= JFactory::getApplication();
            $app->setUserState('com_installer.redirect_url', 'index.php?option=com_installer&view=install&layout=distribution');
            $this->setState('install.directory', $package['dir']);
        }
        else
        {
            // Get an installer instance
            $installer = JInstaller::getInstance();

            // Install the package
            if (!$installer->install($package['dir'])) {
                // There was an error installing the package
                $msg = JText::sprintf('COM_INSTALLER_INSTALL_ERROR', JText::_('COM_INSTALLER_TYPE_TYPE_'.strtoupper($package['type'])));
                $result = false;
            } else {
                // Package installed sucessfully
                $msg = JText::sprintf('COM_INSTALLER_INSTALL_SUCCESS', JText::_('COM_INSTALLER_TYPE_TYPE_'.strtoupper($package['type'])));
                $result = true;
            }

            // Set some model state values
            $app	= JFactory::getApplication();
            $app->enqueueMessage($msg);
            $this->setState('name', $installer->get('name'));
            $this->setState('result', $result);
            $app->setUserState('com_installer.message', $installer->message);
            $app->setUserState('com_installer.extension_message', $installer->get('extension_message'));
            $app->setUserState('com_installer.redirect_url', $installer->get('redirect_url'));

            // Cleanup the install files
            if (!is_file($package['packagefile'])) {
                $config = JFactory::getConfig();
                $package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
            }

            JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

        }

        return $result;
	}

    /**
	 * Install function.
	 *
	 * Sets the "result" state with the result of the operation.
	 *
	 * @param	Array[int] List of updates to apply
	 * @since	1.7
	 */
	public function install_remote($cids)
	{
		$result = true;
		foreach($cids as $cid) {
			$update = new JUpdate();
			$instance = JTable::getInstance('update');
			$instance->load($cid);
			$update->loadFromXML($instance->detailsurl);

			// install sets state and enqueues messages
			$res = $this->install_install($update->get('downloadurl')->_data);

            // Disabling the purging of the update list, instead deleting specific row
			if ($res) {
				$instance->delete($ed);
			}

			$result = $res & $result;
		}

		// Set the final state
		$this->setState('result', $result);
	}

    private function install_install($url)
	{
		jimport('joomla.client.helper');
		$this->setState('action', 'install');

		// Set FTP credentials, if given.
		JClientHelper::setCredentialsFromRequest('ftp');
		$app = JFactory::getApplication();

        $package = $this->_getPackageFromUrl($url);
	
		// Was the package unpacked?
		if (!$package) {
			$app->setUserState('com_installer.message', JText::_('COM_INSTALLER_UNABLE_TO_FIND_INSTALL_PACKAGE'));
			return false;
		}

		// Get an installer instance
		$installer = JInstaller::getInstance();

		// Install the package
		if (!$installer->install($package['dir'])) {
			// There was an error installing the package
			$msg = JText::sprintf('COM_INSTALLER_INSTALL_ERROR', JText::_('COM_INSTALLER_TYPE_TYPE_'.strtoupper($package['type'])));
			$result = false;
		} else {
			// Package installed sucessfully
			$msg = JText::sprintf('COM_INSTALLER_INSTALL_SUCCESS', JText::_('COM_INSTALLER_TYPE_TYPE_'.strtoupper($package['type'])));
			$result = true;
		}

		// Set some model state values
		$app	= JFactory::getApplication();
		$app->enqueueMessage($msg);
		$this->setState('name', $installer->get('name'));
		$this->setState('result', $result);
		$app->setUserState('com_installer.message', $installer->message);
		$app->setUserState('com_installer.extension_message', $installer->get('extension_message'));
		$app->setUserState('com_installer.redirect_url', $installer->get('redirect_url'));

		// Cleanup the install files
		if (!is_file($package['packagefile'])) {
			$config = JFactory::getConfig();
			$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
		}

		JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);


		return $result;
	}

	/**
	 * Works out an installation package from a HTTP upload
	 *
	 * @return package definition or false on failure
	 */
	protected function _getPackageFromUpload()
	{
		// Get the uploaded file information
		$userfile = JRequest::getVar('install_package', null, 'files', 'array');

		// Make sure that file uploads are enabled in php
		if (!(bool) ini_get('file_uploads')) {
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLFILE'));
			return false;
		}

		// Make sure that zlib is loaded so that the package can be unpacked
		if (!extension_loaded('zlib')) {
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLZLIB'));
			return false;
		}

		// If there is no uploaded file, we have a problem...
		if (!is_array($userfile)) {
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_NO_FILE_SELECTED'));
			return false;
		}

		// Check if there was a problem uploading the file.
		if ($userfile['error'] || $userfile['size'] < 1) {
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR'));
			return false;
		}

		// Build the appropriate paths
		$config		= JFactory::getConfig();
		$tmp_dest	= $config->get('tmp_path') . '/' . $userfile['name'];
		$tmp_src	= $userfile['tmp_name'];

		// Move uploaded file
		jimport('joomla.filesystem.file');
		$uploaded = JFile::upload($tmp_src, $tmp_dest);

		// Unpack the downloaded package file
		$package = JInstallerHelper::unpack($tmp_dest);

		return $package;
	}

	/**
	 * Install an extension from a directory
	 *
	 * @return	Package details or false on failure
	 * @since	1.5
	 */
	protected function _getPackageFromFolder()
	{
		// Get the path to the package to install
		$p_dir = JRequest::getString('install_directory');
		$p_dir = JPath::clean($p_dir);

		// Did you give us a valid directory?
		if (!is_dir($p_dir)) {
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_PLEASE_ENTER_A_PACKAGE_DIRECTORY'));
			return false;
		}

		// Detect the package type
		$type = JInstallerHelper::detectType($p_dir);

		// Did you give us a valid package?
		if (!$type) {
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_PATH_DOES_NOT_HAVE_A_VALID_PACKAGE'));
			return false;
		}

		$package['packagefile'] = null;
		$package['extractdir'] = null;
		$package['dir'] = $p_dir;
		$package['type'] = $type;

		return $package;
	}

	/**
	 * Install an extension from a URL
	 *
	 * @return	Package details or false on failure
	 * @since	1.5
	 */
	protected function _getPackageFromUrl($url = false)
	{
		// Get a database connector
		$db = JFactory::getDbo();

		// Get the URL of the package to install
		if (!$url) $url = JRequest::getString('install_url');

		// Did you give us a URL?
		if (!$url) {
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL'));
			return false;
		}

		// Download the package at the URL given
		$p_file = JInstallerHelper::downloadPackage($url);

		// Was the package downloaded?
		if (!$p_file) {
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL'));
			return false;
		}

		$config		= JFactory::getConfig();
		$tmp_dest	= $config->get('tmp_path');

		// Unpack the downloaded package file
		$package = JInstallerHelper::unpack($tmp_dest . '/' . $p_file);

		return $package;
	}


    /**
     * Square One Additions
     */
	
    public function distro_download()
    {
        $update = new JUpdate();
        $detailsurl = base64_decode(JRequest::getString('detailsurl', '', 'post'));
		
		if (substr($detailsurl, 0, 4) == 'http')
		{
			$result->result = false;
			
			if (substr($detailsurl, 0, -3) == 'xml')
			{
				// Url to an update manifest
				$update->loadFromXML($detailsurl);

				$file = JInstallerHelper::downloadPackage($update->get('downloadurl')->_data);
			}
			else
			{
				$file = JInstallerHelper::downloadPackage($detailsurl);
			}
			
			if (!$file) {
				$result->message = JText::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL');
			}
			else
			{
				$result->result = true;
				$result->task = 'distro_download';
				$result->file = $file;
				$result->message = JText::_('COM_INSTALLER_PACKAGE_DOWNLOADED');
			}
		}
		else
		{
			// We have a file
			$config	= JFactory::getConfig();
			$source = base64_decode(JRequest::getString('source'));
			JFile::move($source.'/'.$detailsurl, $config->get('tmp_path').'/'.$detailsurl);
			$result->result = true;
			$result->task = 'distro_download';
			$result->file = $detailsurl;
			$result->message = JText::_('COM_INSTALLER_PACKAGE_FOUND');
		}

        return $result;
    }


    public function distro_extract()
    {
        $file = JRequest::getString('file', '', 'post');

        $config		= JFactory::getConfig();
		$tmp_dest	= $config->get('tmp_path');

        $result->result = false;

        $package = JInstallerHelper::unpack($tmp_dest . '/' . $file);
        if (!$package) {
            $result->message = JText::_('COM_INSTALLER_EXTRACT_FAILED');
		}
        else
        {
            $result->result = true;
            $result->task = 'distro_extract';
            $result->dir = $package['dir'];
            $result->extractdir = $package['extractdir'];
            $result->packagefile = $package['packagefile'];
            $result->type = $package['type'];
            $result->message = JText::_('COM_INSTALLER_EXTRACTED');
        }

        return $result;
    }

    function distro_install()
	{
        $dir = JRequest::getString('dir', '', 'post');
        $extractdir = JRequest::getString('extractdir', '', 'post');
        $packagefile = JRequest::getString('packagefile', '', 'post');
        $type = JRequest::getString('type', '', 'post');

		// Get an installer instance
		$installer = JInstaller::getInstance();

		// Install the package
		if (!$installer->install($dir)) {
			// There was an error installing the package
			$msg = JText::sprintf('COM_INSTALLER_INSTALL_ERROR', JText::_('COM_INSTALLER_TYPE_TYPE_'.strtoupper($type)));
			$result->result = false;
		} else {
			// Package installed sucessfully
			$msg = JText::sprintf('COM_INSTALLER_INSTALL_SUCCESS', JText::_('COM_INSTALLER_TYPE_TYPE_'.strtoupper($type)));
			$result->result = true;
		}

		// Set some model state values
		$app	= JFactory::getApplication();
		$app->enqueueMessage($msg);
		$this->setState('name', $installer->get('name'));
		$this->setState('result', $result);
		$app->setUserState('com_installer.message', $installer->message);
		$app->setUserState('com_installer.extension_message', $installer->get('extension_message'));
		$app->setUserState('com_installer.redirect_url', $installer->get('redirect_url'));

        if ($result->result) {
            $result->result = true;
            $result->extractdir = $extractdir;
            $result->packagefile = $packagefile;
            $result->task = 'distro_install';
            $result->message = $msg;
        }

		return $result;
	}

    public function distro_sql()
    {
        // Distro sql files
        $sqlfile = JRequest::getString('path', '', 'post');
        $db = JFactory::getDBO();
        $result->result = false;

        $buffer = file_get_contents(base64_decode($sqlfile));

        if ($buffer === false) {
            $result->message = JText::_('JLIB_INSTALLER_ERROR_SQL_READBUFFER');
            return $result;
        }

        $queries = JInstallerHelper::splitSql($buffer);

        if (count($queries) == 0)
        {
            // No queries to process
            $result->message = JText::_('COM_INSTALLER_SQL_FAILED');
            return $result;
        }

        // Process each query in the $queries array (split out of sql file).
        foreach ($queries as $query)
        {
            $query = trim($query);
            if ($query != '' && $query{0} != '#')
            {
                $db->setQuery($query);
                if (!$db->query())
                {
                    $result->message = JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true));
                    return $result;
                }
            }
        }

        $result->result = true;
        $result->message = JText::_('COM_INSTALLER_SQL_RAN');

        return $result;
    }

    public function distro_script_preflight()
    {
        // Distro script files
        $class = JRequest::getString('class', '', 'post');
        $path = JRequest::getString('path', '', 'post');
        $result->result = false;
        $result->message = JText::_('COM_INSTALLER_SCRIPT_FAILED');

        include(base64_decode($path));
        $script = new $class();

        $root = realpath($path);
        $root = substr($root, 0, strrpos($root, '/'));

        $script->preflight($root);

        $result->result = true;
        $result->message = JText::_('COM_INSTALLER_SCRIPT_RAN');

        return $result;
    }

    public function distro_script_postflight()
    {
        // Distro script files
        $class = JRequest::getString('class', '', 'post');
        $path = JRequest::getString('path', '', 'post');
        $result->result = false;
        $result->message = JText::_('Script unable to run');

        include(base64_decode($path));
        $script = new $class();

        $root = realpath($path);
        $root = substr($root, 0, strrpos($root, '/'));

        $script->postflight($root);

        $result->result = true;
        $result->message = JText::_('Script ran');

        return $result;
    }

    public function distro_cleanup()
    {
        $result->result = false;
        $config = JFactory::getConfig();

        // Cleanup the install files
        $folders = JFolder::folders($config->get('tmp_path'), '.', false, true);
        foreach ($folders as $folder)
        {
            JFolder::delete($folder);
        }

        $files = JFolder::files($config->get('tmp_path'), '.', false, true, array('index.html'));
        foreach ($files as $file)
        {
            JFile::delete($file);
        }

        $result->result = true;
        $result->message = JText::_('COM_INSTALLER_CLEANED_UP');

        return $result;
    }

    /**
	 * Method to get the row form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$app = JFactory::getApplication();
		JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
		$form = JForm::getInstance('com_installer.install', 'install', array('load_data' => $loadData));

		// Check for an error.
		if ($form == false) {
			$this->setError($form->getMessage());
			return false;
		}
		// Check the session for previously entered form data.
		$data = $this->loadFormData();

		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
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
		$data = JFactory::getApplication()->getUserState('com_installer.install.data', array());

		return $data;
	}

}