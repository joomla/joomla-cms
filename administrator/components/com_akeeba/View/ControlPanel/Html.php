<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\View\ControlPanel;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\Backup\Admin\Helper\Status;
use Akeeba\Backup\Admin\Model\ControlPanel;
use Akeeba\Backup\Admin\Model\UsageStatistics;
use Akeeba\Backup\Admin\View\ViewTraits\ProfileIdAndName;
use Akeeba\Backup\Admin\View\ViewTraits\ProfileList;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use FOF30\View\DataView\Html as BaseView;

class Html extends BaseView
{
	use ProfileList, ProfileIdAndName;

	/**
	 * List of profiles to display as Quick Icons in the control panel page
	 *
	 * @var   array  Array of stdClass objects
	 */
	public $quickIconProfiles = [];

	/**
	 * The HTML for the backup status cell
	 *
	 * @var   string
	 */
	public $statusCell = '';

	/**
	 * HTML for the warnings (status details)
	 *
	 * @var   string
	 */
	public $detailsCell = '';

	/**
	 * Details of the latest backup as HTML
	 *
	 * @var   string
	 */
	public $latestBackupCell = '';

	/**
	 * Do I have to ask the user to fix the permissions?
	 *
	 * @var   bool
	 */
	public $areMediaPermissionsFixed = false;

	/**
	 * Do I have to ask the user to provide a Download ID?
	 *
	 * @var   bool
	 */
	public $needsDownloadID = false;

	/**
	 * Did a Core edition user provide a Download ID instead of installing Akeeba Backup Professional?
	 *
	 * @var   bool
	 */
	public $coreWarningForDownloadID = false;

	/**
	 * Our extension ID
	 *
	 * @var   int
	 */
	public $extension_id = 0;

	/**
	 * Should I have the browser ask for desktop notification permissions?
	 *
	 * @var   bool
	 */
	public $desktopNotifications = false;

	/**
	 * If anonymous statistics collection is enabled and we have to collect statistics this will include the HTML for
	 * the IFRAME that performs the anonymous stats collection.
	 *
	 * @var   string
	 */
	public $statsIframe = '';

	/**
	 * If front-end backup is enabled and the secret word has an issue (too insecure) we populate this variable
	 *
	 * @var  string
	 */
	public $frontEndSecretWordIssue = '';

	/**
	 * In case the existing Secret Word is insecure we generate a new one. This variable contains the new Secret Word.
	 *
	 * @var  string
	 */
	public $newSecretWord = '';

	/**
	 * Is the mbstring extension installed and enabled? This is required by Joomla and Akeeba Backup to correctly work
	 *
	 * @var  bool
	 */
	public $checkMbstring = true;

	/**
	 * The fancy formatted changelog of the component
	 *
	 * @var  string
	 */
	public $formattedChangelog = '';

	/**
	 * Should I pormpt the user ot run the configuration wizard?
	 *
	 * @var  bool
	 */
	public $promptForConfigurationWizard = false;

	/**
	 * How many warnings do I have to display?
	 *
	 * @var  int
	 */
	public $countWarnings = 0;

	/**
	 * Do I have stuck updates pending?
	 *
	 * @var  bool
	 */
	public $stuckUpdates = false;

	/**
	 * Cache the user permissions
	 *
	 * @var   array
	 *
	 * @since 5.3.0
	 */
	public $permissions = [];

	/**
	 * Timestamp when the Core user last dismissed the upsell to Pro
	 *
	 * @var   int
	 * @since 7.0.0
	 */
	public $lastUpsellDismiss = 0;

	/**
	 * Is the output directory under the site's root?
	 *
	 * @var   bool
	 * @since 7.0.3
	 */
	public $isOutputDirectoryUnderSiteRoot = false;

	/**
	 * Does the output directory have the expected security files?
	 *
	 * @var   bool
	 * @since 7.0.3
	 */
	public $hasOutputDirectorySecurityFiles = false;

	/**
	 * Executes before displaying the control panel page
	 */
	public function onBeforeMain()
	{
		/** @var ControlPanel $model */
		$model = $this->getModel();

		$statusHelper      = Status::getInstance();
		$this->statsIframe = '';

		try
		{
			/** @var UsageStatistics $usageStatsModel */
			$usageStatsModel = $this->container->factory->model('UsageStatistics')->tmpInstance();

			if (
				is_object($usageStatsModel)
				&& class_exists('Akeeba\\Backup\\Admin\\Model\\UsageStatistics')
				&& ($usageStatsModel instanceof UsageStatistics)
				&& method_exists($usageStatsModel, 'collectStatistics')
			)
			{
				$this->statsIframe = $usageStatsModel->collectStatistics(true);
			}
		}
		catch (\Exception $e)
		{
			// Don't give a crap if usage stats ain't loaded
		}

		$this->getProfileList();
		$this->getProfileIdAndName();

		$this->quickIconProfiles               = $model->getQuickIconProfiles();
		$this->statusCell                      = $statusHelper->getStatusCell();
		$this->detailsCell                     = $statusHelper->getQuirksCell();
		$this->latestBackupCell                = $statusHelper->getLatestBackupDetails();
		$this->areMediaPermissionsFixed        = $model->fixMediaPermissions();
		$this->checkMbstring                   = $model->checkMbstring();
		$this->needsDownloadID                 = $model->needsDownloadID() ? 1 : 0;
		$this->coreWarningForDownloadID        = $model->mustWarnAboutDownloadIDInCore();
		$this->extension_id                    = $model->getState('extension_id', 0, 'int');
		$this->frontEndSecretWordIssue         = $model->getFrontendSecretWordError();
		$this->newSecretWord                   = $this->container->platform->getSessionVar('newSecretWord', null, 'akeeba.cpanel');
		$this->desktopNotifications            = $this->container->params->get('desktop_notifications', '0') ? 1 : 0;
		$this->formattedChangelog              = $this->formatChangelog();
		$this->promptForConfigurationWizard    = Factory::getConfiguration()->get('akeeba.flag.confwiz', 0) == 0;
		$this->countWarnings                   = count(Factory::getConfigurationChecks()->getDetailedStatus());
		$this->stuckUpdates                    = ($this->container->params->get('updatedb', 0) == 1);
		$user                                  = $this->container->platform->getUser();
		$this->permissions                     = [
			'configure' => $user->authorise('akeeba.configure', 'com_akeeba'),
			'backup'    => $user->authorise('akeeba.backup', 'com_akeeba'),
			'download'  => $user->authorise('akeeba.download', 'com_akeeba'),
		];
		$this->isOutputDirectoryUnderSiteRoot  = $model->isOutputDirectoryUnderSiteRoot();
		$this->hasOutputDirectorySecurityFiles = $model->hasOutputDirectorySecurityFiles();

		$this->lastUpsellDismiss = $this->container->params->get('lastUpsellDismiss', 0);

		// Load the version constants
		Platform::getInstance()->load_version_defines();

		// Add the Javascript to the document
		$this->container->template->addJS('media://com_akeeba/js/ControlPanel.min.js', true, false, $this->container->mediaVersion);
		$this->addJSScriptOptions();
	}

	/**
	 * Adds inline Javascript to the document
	 */
	protected function addJSScriptOptions()
	{
		$platform = $this->container->platform;
		$platform->addScriptOptions('akeeba.System.notification.hasDesktopNotification', (bool)$this->desktopNotifications);
		$platform->addScriptOptions('akeeba.ControlPanel.needsDownloadID', (bool) $this->needsDownloadID);
		$platform->addScriptOptions('akeeba.ControlPanel.outputDirUnderSiteRoot', (bool) $this->isOutputDirectoryUnderSiteRoot);
		$platform->addScriptOptions('akeeba.ControlPanel.hasSecurityFiles', (bool) $this->hasOutputDirectorySecurityFiles);
	}

	protected function formatChangelog($onlyLast = false)
	{
		$ret   = '';
		$file  = $this->container->backEndPath . '/CHANGELOG.php';
		$lines = @file($file);

		if (empty($lines))
		{
			return $ret;
		}

		array_shift($lines);

		foreach ($lines as $line)
		{
			$line = trim($line);

			if (empty($line))
			{
				continue;
			}

			$type = substr($line, 0, 1);

			switch ($type)
			{
				case '=':
					continue 2;
					break;

				case '+':
					$ret .= "\t" . '<li class="akeeba-changelog-added"><span></span>' . htmlentities(trim(substr($line, 2))) . "</li>\n";
					break;

				case '-':
					$ret .= "\t" . '<li class="akeeba-changelog-removed"><span></span>' . htmlentities(trim(substr($line, 2))) . "</li>\n";
					break;

				case '~':
					$ret .= "\t" . '<li class="akeeba-changelog-changed"><span></span>' . htmlentities(trim(substr($line, 2))) . "</li>\n";
					break;

				case '!':
					$ret .= "\t" . '<li class="akeeba-changelog-important"><span></span>' . htmlentities(trim(substr($line, 2))) . "</li>\n";
					break;

				case '#':
					$ret .= "\t" . '<li class="akeeba-changelog-fixed"><span></span>' . htmlentities(trim(substr($line, 2))) . "</li>\n";
					break;

				default:
					if (!empty($ret))
					{
						$ret .= "</ul>";
						if ($onlyLast)
						{
							return $ret;
						}
					}

					if (!$onlyLast)
					{
						$ret .= "<h3 class=\"akeeba-changelog\">$line</h3>\n";
					}
					$ret .= "<ul class=\"akeeba-changelog\">\n";

					break;
			}
		}

		return $ret;
	}
}
