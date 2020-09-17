<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Akeeba\Engine\Platform;
use FOF30\Container\Container;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;

defined('_JEXEC') || die();

// PHP version check
if (!version_compare(PHP_VERSION, '7.1.0', '>='))
{
	return;
}

class plgActionlogAkeebabackup extends CMSPlugin
{
	/** @var Container */
	private $container;

	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An array that holds the plugin configuration
	 *
	 * @since       6.4.0
	 */
	public function __construct(&$subject, $config)
	{
		// Make sure Akeeba Backup is installed
		if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_akeeba'))
		{
			return;
		}

		// Make sure Akeeba Backup is enabled
		if (!ComponentHelper::isEnabled('com_akeeba'))
		{
			return;
		}

		// Load FOF
		if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
		{
			return;
		}

		$this->container = Container::getInstance('com_akeeba');

		// No point in logging guest actions
		if ($this->container->platform->getUser()->guest)
		{
			return;
		}

		// If any of the above statement returned, our plugin is not attached to the subject, so it's basically disabled
		parent::__construct($subject, $config);
	}

	/**
	 * Logs the creation of a new backup profile
	 *
	 * @param   \Akeeba\Backup\Admin\Controller\Profiles  $controller
	 * @param   array                                     $data
	 * @param   int                                       $id
	 */
	public function onComAkeebaControllerProfilesAfterApplySave($controller, $data, $id)
	{
		// If I have an ID in the request and it's the same of the model, I'm just editing a record
		if (isset($data['id']) && $data['id'] == $id)
		{
			return;
		}

		$profile_title = $data['description'];

		$this->container->platform->logUserAction($profile_title, 'COM_AKEEBA_LOGS_PROFILE_ADD', 'com_akeeba');
	}

	/**
	 * Logs deletion of a backup profile
	 *
	 * @param   \Akeeba\Backup\Admin\Controller\Profiles  $controller
	 */
	public function onComAkeebaControllerProfilesAfterRemove($controller)
	{
		$ids           = $controller->input->get('cid', [], 'array', 2);
		$profile_title = '# ' . implode(', ', $ids);

		$this->container->platform->logUserAction($profile_title, 'COM_AKEEBA_LOGS_PROFILE_DELETE', 'com_akeeba');
	}

	/**
	 * Log configuration edit (apply)
	 *
	 * @param   \Akeeba\Backup\Admin\Controller\Configuration  $controller
	 */
	public function onComAkeebaControllerConfigurationAfterApply($controller)
	{
		$this->logConfigurationChange();
	}

	/**
	 * Log configuration edit (save and close)
	 *
	 * @param   \Akeeba\Backup\Admin\Controller\Configuration  $controller
	 */
	public function onComAkeebaControllerConfigurationAfterSave($controller)
	{
		$this->logConfigurationChange();
	}

	/**
	 * Log configuration edit (save and new)
	 *
	 * @param   \Akeeba\Backup\Admin\Controller\Configuration  $controller
	 */
	public function onComAkeebaControllerConfigurationAfterSavenew($controller)
	{
		$this->logConfigurationChange();
	}

	/**
	 * Log starting a new backup
	 *
	 * @param   \Akeeba\Backup\Admin\Controller\Backup  $controller
	 */
	public function onComAkeebaControllerBackupBeforeAjax($controller)
	{
		$ajaxTask = $this->container->input->get('ajax', '', 'cmd');

		// Log only starting the backup
		if ($ajaxTask != 'start')
		{
			return;
		}

		$profile_id = $this->container->platform->getSessionVar('profile', -10, 'akeeba');

		if ($profile_id < 1)
		{
			return;
		}

		$profile_id = '#' . $profile_id;

		$this->container->platform->logUserAction($profile_id, 'COM_AKEEBA_LOGS_BACKUP_RUN', 'com_akeeba');
	}

	/**
	 * Log downloading a backup using Joomla interface
	 *
	 * @param   \Akeeba\Backup\Admin\Controller\Manage  $controller
	 */
	public function onComAkeebaControllerManageBeforeDownload($controller)
	{
		$id   = $this->container->input->getInt('id');
		$part = $this->container->input->getInt('part', -1);

		// This should never happens, but better be safe
		if (!$id)
		{
			return;
		}

		$stat         = Platform::getInstance()->get_statistics($id);
		$profile_name = Platform::getInstance()->get_profile_name($stat['profile_id']);

		$title = 'Profile: "' . $profile_name . '" ID: ' . $id;

		if ($part > -1)
		{
			$title .= ' part: ' . $part;
		}

		$this->container->platform->logUserAction($title, 'COM_AKEEBA_LOGS_MANAGE_DOWNLOAD', 'com_akeeba');
	}

	/**
	 * Logs deleting backup files
	 *
	 * @param   \Akeeba\Backup\Admin\Controller\Manage  $controller
	 */
	public function onComAkeebaControllerManageBeforeDeletefiles($controller)
	{
		$ids = $this->getIDsFromRequest();

		foreach ($ids as $id)
		{
			$this->container->platform->logUserAction('ID: ' . $id, 'COM_AKEEBA_LOGS_MANAGE_DELETEFILES', 'com_akeeba');
		}
	}

	/**
	 * Logs deleting backup stat entry
	 *
	 * @param   \Akeeba\Backup\Admin\Controller\Manage  $controller
	 */
	public function onComAkeebaControllerManageBeforeRemove($controller)
	{
		$ids = $this->getIDsFromRequest();

		foreach ($ids as $id)
		{
			$this->container->platform->logUserAction($id, 'COM_AKEEBA_LOGS_MANAGE_DELETE', 'com_akeeba');
		}
	}

	/**
	 * Logs downloading remote archives to browser
	 *
	 * @param   \Akeeba\Backup\Admin\Controller\RemoteFiles  $controller
	 */
	public function onComAkeebaControllerRemoteFilesBeforeDlfromremote($controller)
	{
		$id   = $this->container->input->getInt('id');
		$part = $this->container->input->getInt('part', -1);

		// This should never happens, but better be safe
		if (!$id)
		{
			return;
		}

		$stat         = Platform::getInstance()->get_statistics($id);
		$profile_name = Platform::getInstance()->get_profile_name($stat['profile_id']);

		$title = 'Profile: "' . $profile_name . '" ID: ' . $id;

		if ($part > -1)
		{
			$title .= ' part: ' . $part;
		}

		$this->container->platform->logUserAction($title, 'COM_AKEEBA_LOGS_REMOTEFILE_DOWNLOAD', 'com_akeeba');
	}

	/**
	 * Logs downloading remote archives back to the server
	 *
	 * @param   \Akeeba\Backup\Admin\Controller\RemoteFiles  $controller
	 */
	public function onComAkeebaControllerRemoteFilesBeforeDltoserver($controller)
	{
		$id   = $this->container->input->getInt('id');
		$part = $this->container->input->getInt('part', -1);
		$frag = $this->container->input->getInt('frag', -1);

		// Log only the first step
		if ($frag > -1 || $part > -1)
		{
			return;
		}

		// This should never happens, but better be safe
		if (!$id)
		{
			return;
		}

		$stat         = Platform::getInstance()->get_statistics($id);
		$profile_name = Platform::getInstance()->get_profile_name($stat['profile_id']);

		$title = 'Profile: "' . $profile_name . '" ID: ' . $id;

		$this->container->platform->logUserAction($title, 'COM_AKEEBA_LOGS_REMOTEFILE_FETCH', 'com_akeeba');
	}

	/**
	 * Logs downloading remote archives to browser
	 *
	 * @param   \Akeeba\Backup\Admin\Controller\RemoteFiles  $controller
	 */
	public function onComAkeebaControllerRemoteFilesBeforeDelete($controller)
	{
		$id   = $this->container->input->getInt('id');
		$part = $this->container->input->getInt('part', -1);

		// This should never happens, but better be safe
		if (!$id)
		{
			return;
		}

		$stat         = Platform::getInstance()->get_statistics($id);
		$profile_name = Platform::getInstance()->get_profile_name($stat['profile_id']);

		$title = 'Profile: "' . $profile_name . '" ID: ' . $id;

		if ($part > -1)
		{
			$title .= ' part: ' . $part;
		}

		$this->container->platform->logUserAction($title, 'COM_AKEEBA_LOGS_REMOTEFILE_DELETE', 'com_akeeba');
	}

	/**
	 * Logs downloading remote archives to browser
	 *
	 * @param   \Akeeba\Backup\Admin\Controller\Upload  $controller
	 */
	public function onComAkeebaControllerUploadBeforeStart($controller)
	{
		$id = $this->container->input->getInt('id');

		// This should never happens, but better be safe
		if (!$id)
		{
			return;
		}

		$stat         = Platform::getInstance()->get_statistics($id);
		$profile_name = Platform::getInstance()->get_profile_name($stat['profile_id']);

		$title = 'Profile: "' . $profile_name . '" ID: ' . $id;

		$this->container->platform->logUserAction($title, 'COM_AKEEBA_LOGS_UPLOADS_ADD', 'com_akeeba');
	}

	/**
	 * Log starting a site transfer wizard (connections valid, just before starting to actually transfer files)
	 *
	 * @param   \Akeeba\Backup\Admin\Controller\Transfer  $controller
	 */
	public function onComAkeebaControllerTransferBeforeUpload($controller)
	{
		$start = $this->container->input->getBool('start', false);

		if (!$start)
		{
			return;
		}

		$title = $this->container->platform->getSessionVar('transfer.url', '', 'akeeba');

		if (!$title)
		{
			return;
		}

		$this->container->platform->logUserAction($title, 'COM_AKEEBA_LOGS_TRANSFER_RUN', 'com_akeeba');
	}

	/**
	 * Logs downloading a backup log
	 *
	 * @param   \Akeeba\Backup\Admin\Controller\Log  $controller
	 */
	public function onComAkeebaControllerLogBeforeDownload($controller)
	{
		$tag = $this->container->input->get('tag', null, 'cmd');;

		$this->container->platform->logUserAction($tag, 'COM_AKEEBA_LOGS_LOG_DOWNLOAD', 'com_akeeba');
	}

	/**
	 * Log importing a backup archive
	 *
	 * @param   \Akeeba\Backup\Admin\Controller\Discover  $controller
	 */
	public function onComAkeebaControllerDiscoverBeforeImport($controller)
	{
		$files = $this->container->input->get('files', [], 'array');

		foreach ($files as $file)
		{
			$this->container->platform->logUserAction($file, 'COM_AKEEBA_LOGS_DISCOVER_IMPORT', 'com_akeeba');
		}
	}

	/**
	 * Log importing a backup archive from S3
	 *
	 * @param   \Akeeba\Backup\Admin\Controller\Discover  $controller
	 */
	public function onComAkeebaControllerS3ImportBeforeDltoserver($controller)
	{
		$file = $this->container->input->get('file', '', 'string');

		// Log only the initial download step
		$part = $this->container->input->getInt('part', -1);
		$frag = $this->container->input->getInt('frag', -1);
		$step = $this->container->input->getInt('step', -1);

		if ($part > -1 || $frag > -1 || $step > -1)
		{
			return;
		}

		$this->container->platform->logUserAction($file, 'COM_AKEEBA_LOGS_S3IMPORT_IMPORT', 'com_akeeba');
	}

	private function logConfigurationChange()
	{
		$profileName = $this->container->input->getString('profilename', null);

		$this->container->platform->logUserAction('"' . $profileName . '"', 'COM_AKEEBA_LOGS_CONFIGURATION_EDIT', 'com_akeeba');
	}

	/**
	 * Gets the list of IDs from the request data
	 *
	 * @return array
	 */
	private function getIDsFromRequest()
	{
		// Get the ID or list of IDs from the request or the configuration
		$cid = $this->container->input->get('cid', [], 'array');
		$id  = $this->container->input->getInt('id', 0);

		$ids = [];

		if (is_array($cid) && !empty($cid))
		{
			$ids = $cid;
		}
		elseif (!empty($id))
		{
			$ids = [$id];
		}

		return $ids;
	}
}
