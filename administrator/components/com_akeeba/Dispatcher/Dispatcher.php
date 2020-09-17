<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Dispatcher;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\Backup\Admin\Helper\SecretWord;
use Akeeba\Backup\Admin\Model\ControlPanel;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use AkeebaFEFHelper;
use FOF30\Container\Container;
use FOF30\Dispatcher\Dispatcher as BaseDispatcher;
use FOF30\Dispatcher\Mixin\ViewAliases;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text;

class Dispatcher extends BaseDispatcher
{
	/** @var   string  The name of the default view, in case none is specified */
	public $defaultView = 'ControlPanel';

	use ViewAliases
	{
		onBeforeDispatch as onBeforeDispatchViewAliases;
	}

	/** @var  \Akeeba\Backup\Admin\Container  The container we belong to */
	protected $container = null;

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->viewNameAliases = [
			'buadmin'        => 'Manage',
			'buadmins'       => 'Manage',
			'config'         => 'Configuration',
			'configs'        => 'Configuration',
			'confwiz'        => 'ConfigurationWizard',
			'confwizs'       => 'ConfigurationWizard',
			'confwizes'      => 'ConfigurationWizard',
			'cpanel'         => 'ControlPanel',
			'cpanels'        => 'ControlPanel',
			'dbef'           => 'DatabaseFilters',
			'dbefs'          => 'DatabaseFilters',
			'eff'            => 'IncludeFolders',
			'effs'           => 'IncludeFolders',
			'fsfilter'       => 'FileFilters',
			'fsfilters'      => 'FileFilters',
			'ftpbrowser'     => 'FTPBrowser',
			'ftpbrowsers'    => 'FTPBrowser',
			'sftpbrowser'    => 'SFTPBrowser',
			'sftpbrowsers'   => 'SFTPBrowser',
			'multidb'        => 'MultipleDatabases',
			'multidbs'       => 'MultipleDatabases',
			'regexdbfilter'  => 'RegExDatabaseFilters',
			'regexdbfilters' => 'RegExDatabaseFilters',
			'regexfsfilter'  => 'RegExFileFilters',
			'regexfsfilters' => 'RegExFileFilters',
			'remotefile'     => 'RemoteFiles',
			'remotefiles'    => 'RemoteFiles',
			's3import'       => 'S3Import',
			's3imports'      => 'S3Import',
		];

	}

	/**
	 * Executes before dispatching the request to the appropriate controller
	 */
	public function onBeforeDispatch()
	{
		$this->container->platform->importPlugin('akeebabackup');
		$this->container->platform->runPlugins('onComAkeebaDispatcherBeforeDispatch', []);

		$this->onBeforeDispatchViewAliases();

		// Load the FOF language
		$lang = $this->container->platform->getLanguage();
		$lang->load('lib_fof30', JPATH_ADMINISTRATOR, 'en-GB', true, true);
		$lang->load('lib_fof30', JPATH_ADMINISTRATOR, null, true, false);

		// Necessary for routing the Alice view
		$this->container->inflector->addWord('Alice', 'Alices');

		// Does the user have adequate permissions to access our component?
		if (!$this->container->platform->authorise('core.manage', 'com_akeeba'))
		{
			throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 404);
		}

		// FEF Renderer options. Used to load the common CSS file.
		$darkMode  = $this->container->params->get('dark_mode', -1);
		$customCss = 'media://com_akeeba/css/akeebaui.min.css';

		if ($darkMode != 0)
		{
			$customCss .= ', media://com_akeeba/css/dark.min.css';
		}

		$helperFile = JPATH_SITE . '/media/fef/fef.php';

		if (!class_exists('AkeebaFEFHelper') && is_file($helperFile))
		{
			include_once $helperFile;
		}

		AkeebaFEFHelper::load();

		$this->container->renderer->setOptions([
			'custom_css' => $customCss,
			'fef_dark'   => $darkMode,
		]);

		// Load Akeeba Engine
		$this->loadAkeebaEngine();

		// Load the Akeeba Engine configuration
		try
		{
			$this->loadAkeebaEngineConfiguration();
		}
		catch (\Exception $e)
		{
			// Maybe the tables are not installed?
			/** @var ControlPanel $cPanelModel */
			$cPanelModel = $this->container->factory->model('ControlPanel')->tmpInstance();

			try
			{
				$cPanelModel->checkAndFixDatabase();
			}
			catch (\RuntimeException $e)
			{
				// The update is stuck. We will display a warning in the Control Panel
			}

			$msg = Text::_('COM_AKEEBA_CONTROLPANEL_MSG_REBUILTTABLES');
			$this->container->platform->redirect('index.php', 307, $msg, 'warning');
		}

		// Prevents the "SQLSTATE[HY000]: General error: 2014" due to resource sharing with Akeeba Engine
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		// !!!!! WARNING: ALWAYS GO THROUGH JFactory; DO NOT GO THROUGH $this->container->db !!!!!
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		$jDbo = JFactory::getDbo();

		if ($jDbo->name == 'pdomysql')
		{
			@JFactory::getDbo()->disconnect();
		}

		// Load the utils helper library
		Platform::getInstance()->load_version_defines();
		Platform::getInstance()->apply_quirk_definitions();

		// Make sure the front-end backup Secret Word is stored encrypted
		$params = $this->container->params;
		SecretWord::enforceEncryption($params, 'frontend_secret_word');

		// Make sure we have a version loaded
		@include_once($this->container->backEndPath . '/version.php');

		if (!defined('AKEEBA_VERSION'))
		{
			define('AKEEBA_VERSION', 'dev');
			define('AKEEBA_DATE', date('Y-m-d'));
		}

		// Create a media file versioning tag
		$this->container->mediaVersion = md5(AKEEBA_VERSION . AKEEBA_DATE);

		// Perform certain functionality only in HTML tasks
		$format = $this->input->getCmd('format', 'html');

		if ($format == 'html')
		{
			// Load common Javascript files. NOTE: CSS and anything style-related is loaded by the FEF Renderer class.
			$this->loadCommonJavascript();

			// Perform common maintenance tasks
			$this->autoMaintenance();
		}

		// Set the linkbar style to Classic (Bootstrap tabs). The sidebar takes too much space and requires adding
		// manual HTML to render it...
		$this->container->renderer->setOption('linkbar_style', 'classic');
	}

	public function loadAkeebaEngine()
	{
		// Necessary defines for Akeeba Engine
		if (!defined('AKEEBAENGINE'))
		{
			define('AKEEBAENGINE', 1);
			define('AKEEBAROOT', $this->container->backEndPath . '/BackupEngine');
		}

		// Make sure we have a profile set throughout the component's lifetime
		$profile_id = $this->container->platform->getSessionVar('profile', null, 'akeeba');

		if (is_null($profile_id))
		{
			$this->container->platform->setSessionVar('profile', 1, 'akeeba');
		}

		// Load Akeeba Engine
		$basePath = $this->container->backEndPath;
		require_once $basePath . '/BackupEngine/Factory.php';
	}

	public function loadAkeebaEngineConfiguration()
	{
		Platform::addPlatform('joomla3x', $this->container->backEndPath . '/BackupPlatform/Joomla3x');
		$akeebaEngineConfig = Factory::getConfiguration();
		Platform::getInstance()->load_configuration();
		unset($akeebaEngineConfig);
	}

	/**
	 * Loads the Javascript files which are common across many views of the component.
	 *
	 * @return  void
	 */
	private function loadCommonJavascript()
	{
		// Do not move: everything depends on UserInterfaceCommon
		$this->container->template->addJS('media://com_akeeba/js/UserInterfaceCommon.min.js', true, false, $this->container->mediaVersion);
		// Do not move: System depends on Modal
		$this->container->template->addJS('media://com_akeeba/js/Modal.min.js', true, false, $this->container->mediaVersion);
		// Do not move: System depends on Ajax
		$this->container->template->addJS('media://com_akeeba/js/Ajax.min.js', true, false, $this->container->mediaVersion);
		// Do not move: System depends on Ajax
		$this->container->template->addJS('media://com_akeeba/js/System.min.js', true, false, $this->container->mediaVersion);
		// Do not move: Tooltip depends on System
		$this->container->template->addJS('media://com_akeeba/js/Tooltip.min.js', true, false, $this->container->mediaVersion);
		// Always add last (it's the least important)
		$this->container->template->addJS('media://com_akeeba/js/piecon.min.js', true, false, $this->container->mediaVersion);
	}

	/**
	 * Perform common maintenance tasks
	 *
	 * @return  void
	 */
	private function autoMaintenance()
	{
		/** @var \Akeeba\Backup\Admin\Model\ControlPanel $model */
		$model = $this->container->factory->model('ControlPanel')->tmpInstance();

		// Update the db structure if necessary (once per session at most)
		$lastVersion = $this->container->platform->getSessionVar('magicParamsUpdateVersion', null, 'com_akeeba');

		if ($lastVersion != AKEEBA_VERSION)
		{
			try
			{
				$model->checkAndFixDatabase();
				$this->container->platform->setSessionVar('magicParamsUpdateVersion', AKEEBA_VERSION, 'com_akeeba');
			}
			catch (\RuntimeException $e)
			{
				// The update is stuck. We will display a warning in the Control Panel
			}
		}

		// Update magic parameters if necessary
		$model->updateMagicParameters();
	}
}
