<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Controller;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\Backup\Admin\Controller\Mixin\CustomACL;
use Akeeba\Backup\Admin\Controller\Mixin\PredefinedTaskList;
use Akeeba\Backup\Admin\Helper\Utils;
use Akeeba\Backup\Admin\Model\Backup as BackupModel;
use Akeeba\Backup\Admin\Model\ConfigurationWizard;
use Akeeba\Backup\Admin\Model\Updates;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use Exception;
use FOF30\Container\Container;
use FOF30\Controller\Controller;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use RuntimeException;

/**
 * The Control Panel controller class
 */
class ControlPanel extends Controller
{
	use CustomACL, PredefinedTaskList;

	public function __construct(Container $container, array $config = [])
	{
		parent::__construct($container, $config);

		$this->setPredefinedTaskList([
			'main', 'SwitchProfile', 'UpdateInfo', 'applydlid', 'resetSecretWord', 'reloadUpdateInformation',
			'forceUpdateDb', 'dismissUpsell', 'fixOutputDirectory', 'checkOutputDirectory', 'addRandomToFilename',
		]);
	}

	public function SwitchProfile()
	{
		// CSRF prevention
		$this->csrfProtection();

		$newProfile = $this->input->get('profileid', -10, 'int');

		if (!is_numeric($newProfile) || ($newProfile <= 0))
		{
			$this->setRedirect(\Joomla\CMS\Uri\Uri::base() . 'index.php?option=com_akeeba', \Joomla\CMS\Language\Text::_('COM_AKEEBA_CPANEL_PROFILE_SWITCH_ERROR'), 'error');

			return;
		}

		$this->container->platform->setSessionVar('profile', $newProfile, 'akeeba');
		$returnurl = $this->input->get('returnurl', '', 'base64');
		$url       = Utils::safeDecodeReturnUrl($returnurl);

		if (empty($url))
		{
			$url = \Joomla\CMS\Uri\Uri::base() . 'index.php?option=com_akeeba';
		}

		$this->setRedirect($url, \Joomla\CMS\Language\Text::_('COM_AKEEBA_CPANEL_PROFILE_SWITCH_OK'));
	}

	public function UpdateInfo()
	{
		/** @var Updates $updateModel */
		$updateModel = $this->container->factory->model('Updates')->tmpInstance();
		$updateInfo   = $updateModel->getUpdates();

		$result = '';

		if ($updateInfo['hasUpdate'])
		{
			$result = $this->getView()->loadAnyTemplate('admin:com_akeeba/ControlPanel/updateinfo', [
				'updateInfo'     => $updateInfo,
				'softwareName'   => 'Akeeba Backup',
				'currentVersion' => AKEEBA_VERSION,
				'currentDate'    => AKEEBA_DATE,
				'compatibilitySlug'    => '#akeeba-backup-compatibility',
			]);
		}
		echo '###' . $result . '###';

		// Cut the execution short
		$this->container->platform->closeApplication();
	}

	/**
	 * Applies the Download ID when the user is prompted about it in the Control Panel
	 */
	public function applydlid()
	{
		// CSRF prevention
		$this->csrfProtection();

		$msg     = \Joomla\CMS\Language\Text::_('COM_AKEEBA_CPANEL_ERR_INVALIDDOWNLOADID');
		$msgType = 'error';
		$dlid    = $this->input->getString('dlid', '');

		/** @var Updates $updateModel */
		$updateModel = $this->container->factory->model('Updates')->tmpInstance();
		$dlid        = $updateModel->sanitizeLicenseKey($dlid);
		$isValidDLID = $updateModel->isValidLicenseKey($dlid);

		// If the Download ID seems legit let's apply it
		if ($isValidDLID)
		{
			$msg     = null;
			$msgType = null;

			$updateModel->setLicenseKey($dlid);
		}

		// Redirect back to the control panel
		$returnurl = $this->input->get('returnurl', '', 'base64');
		$url       = Utils::safeDecodeReturnUrl($returnurl);

		if (empty($url))
		{
			$url = \Joomla\CMS\Uri\Uri::base() . 'index.php?option=com_akeeba';
		}

		$this->setRedirect($url, $msg, $msgType);
	}

	/**
	 * Reset the Secret Word for front-end and remote backup
	 *
	 * @return  void
	 */
	public function resetSecretWord()
	{
		// CSRF prevention
		$this->csrfProtection();

		$newSecret = $this->container->platform->getSessionVar('newSecretWord', null, 'akeeba.cpanel');

		if (empty($newSecret))
		{
			$random    = new \Akeeba\Engine\Util\RandomValue();
			$newSecret = $random->generateString(32);
			$this->container->platform->setSessionVar('newSecretWord', $newSecret, 'akeeba.cpanel');
		}

		$this->container->params->set('frontend_secret_word', $newSecret);
		$this->container->params->save();

		$msg = \Joomla\CMS\Language\Text::sprintf('COM_AKEEBA_CPANEL_MSG_FESECRETWORD_RESET', $newSecret);

		$url = 'index.php?option=com_akeeba';
		$this->setRedirect($url, $msg);
	}

	public function reloadUpdateInformation()
	{
		$msg = null;

		/** @var Updates $model */
		$model = $this->container->factory->model('Updates')->tmpInstance();
		$model->getUpdates(true);

		$msg = \Joomla\CMS\Language\Text::_('COM_AKEEBA_COMMON_UPDATE_INFORMATION_RELOADED');
		$url = 'index.php?option=com_akeeba';

		$this->setRedirect($url, $msg);
	}

	/**
	 * Resets the "updatedb" flag and forces the database updates
	 */
	public function forceUpdateDb()
	{
		// Reset the flag so the updates could take place
		$this->container->params->set('updatedb', null);
		$this->container->params->save();

		/** @var \Akeeba\Backup\Admin\Model\ControlPanel $model */
		$model = $this->getModel();

		try
		{
			$model->checkAndFixDatabase();
		}
		catch (\RuntimeException $e)
		{
			// This should never happen, since we reset the flag before execute the update, but you never know
		}

		$this->setRedirect('index.php?option=com_akeeba');
	}

	/**
	 * Dismisses the Core to Pro upsell for 15 days
	 *
	 * @return  void
	 */
	public function dismissUpsell()
	{
		// Reset the flag so the updates could take place
		$this->container->params->set('lastUpsellDismiss', time());
		$this->container->params->save();

		$this->setRedirect('index.php?option=com_akeeba');
	}

	/**
	 * Check the security of the backup output directory and return the results for consumption through AJAX
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   7.0.3
	 */
	public function checkOutputDirectory()
	{
		/** @var \Akeeba\Backup\Admin\Model\ControlPanel $model */
		$model  = $this->getModel();
		$outDir = $model->getOutputDirectory();

		try
		{
			$result = $model->getOutputDirectoryWebAccessibleState($outDir);
		}
		catch (RuntimeException $e)
		{
			$result = [
				'readFile'   => false,
				'listFolder' => false,
				'isSystem'   => $model->isOutputDirectoryInSystemFolder(),
				'hasRandom'  => $model->backupFilenameHasRandom(),
			];
		}

		@ob_end_clean();

		echo '###' . json_encode($result) . '###';

		$this->container->platform->closeApplication();
	}

	/**
	 * Add security files to the output directory of the currently configured backup profile
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   7.0.3
	 */
	public function fixOutputDirectory()
	{
		// CSRF prevention
		$this->csrfProtection();

		/** @var \Akeeba\Backup\Admin\Model\ControlPanel $model */
		$model  = $this->getModel();
		$outDir = $model->getOutputDirectory();

		$fsUtils = Factory::getFilesystemTools();
		$fsUtils->ensureNoAccess($outDir, true);

		$this->setRedirect('index.php?option=com_akeeba');
	}

	/**
	 * Adds the [RANDOM] variable to the backup output filename, save the configuration and reload the Control Panel.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   7.0.3
	 */
	public function addRandomToFilename()
	{
		// CSRF prevention
		$this->csrfProtection();
		$registry     = Factory::getConfiguration();
		$templateName = $registry->get('akeeba.basic.archive_name');

		if (strpos($templateName, '[RANDOM]') === false)
		{
			$templateName .= '-[RANDOM]';
			$registry->set('akeeba.basic.archive_name', $templateName);
			Platform::getInstance()->save_configuration();
		}

		$this->setRedirect('index.php?option=com_akeeba');
	}

	/**
	 * Run everything necessary to display the Control Panel page
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   5.0.0
	 */
	protected function onBeforeMain()
	{
		/** @var \Akeeba\Backup\Admin\Model\ControlPanel $model */
		$model = $this->getModel();

		$engineConfig = Factory::getConfiguration();

		// Invalidate stale backups
		$params = $this->container->params;

		try
		{
			Factory::resetState([
				'global' => true,
				'log'    => false,
				'maxrun' => $params->get('failure_timeout', 180),
			]);
		}
		catch (Exception $e)
		{
			// This will die if the output directory is invalid. Let it die, then.
		}

		// Just in case the reset() loaded a stale configuration...
		Platform::getInstance()->load_configuration();
		Platform::getInstance()->apply_quirk_definitions();

		// Let's make sure the temporary and output directories are set correctly and writable...
		/** @var ConfigurationWizard $wizmodel */
		$wizmodel = $this->container->factory->model('ConfigurationWizard')->tmpInstance();
		$wizmodel->autofixDirectories();

		// Check if we need to toggle the settings encryption feature
		$model->checkSettingsEncryption();

		// Convert existing log files to the new .log.php format
		/** @var BackupModel $backupModel */
		$backupModel = $this->container->factory->model('Backup')->tmpInstance();
		$backupModel->convertLogFiles();

		// Run the automatic update site refresh
		/** @var Updates $updateModel */
		$updateModel = $this->container->factory->model('Updates')->tmpInstance();
		$updateModel->refreshUpdateSite();
	}

}
