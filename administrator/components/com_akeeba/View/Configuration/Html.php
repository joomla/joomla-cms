<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\View\Configuration;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\Backup\Admin\View\ViewTraits\ProfileIdAndName;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use FOF30\View\DataView\Html as BaseView;
use Joomla\CMS\Language\Text as JText;

class Html extends BaseView
{
	use ProfileIdAndName;

	/**
	 * Status of the settings encryption: -1 disabled by user, 0 not available, 1 enabled and active
	 *
	 * @var  int
	 */
	public $secureSettings = 0;

	/**
	 * Should I show the Configuration Wizard popup prompt?
	 *
	 * @var  bool
	 */
	public $promptForConfigurationWizard = false;

	/**
	 * Executes when displaying the page
	 */
	public function onBeforeMain()
	{
		$this->container->template->addJS('media://com_akeeba/js/Configuration.min.js', true, false, $this->container->mediaVersion);

		$this->getProfileIdAndName();

		// Are the settings secured?
		$this->secureSettings = $this->getSecureSettingsOption();

		// Should I show the Configuration Wizard popup prompt?
		$this->promptForConfigurationWizard = Factory::getConfiguration()->get('akeeba.flag.confwiz', 0) != 1;

		// Push script options
		$urls = array(
			'browser'      => addslashes('index.php?option=com_akeeba&view=Browser&processfolder=1&tmpl=component&folder='),
			'ftpBrowser'   => addslashes('index.php?option=com_akeeba&view=FTPBrowser'),
			'sftpBrowser'  => addslashes('index.php?option=com_akeeba&view=SFTPBrowser'),
			'testFtp'      => addslashes('index.php?option=com_akeeba&view=Configuration&task=testftp'),
			'testSftp'     => addslashes('index.php?option=com_akeeba&view=Configuration&task=testsftp'),
			'dpeauthopen'  => addslashes('index.php?option=com_akeeba&view=Configuration&task=dpeoauthopen&format=raw'),
			'dpecustomapi' => addslashes('index.php?option=com_akeeba&view=Configuration&task=dpecustomapi&format=raw'),
		);

		// Push script options
		$platform = $this->container->platform;
		$platform->addScriptOptions('akeeba.Configuration.URLs', $urls);
		$platform->addScriptOptions('akeeba.Configuration.GUIData', json_decode(Factory::getEngineParamsProvider()->getJsonGuiDefinition(), true));


		// Push translations
		JText::script('COM_AKEEBA_CONFIG_UI_BROWSE');
		JText::script('COM_AKEEBA_CONFIG_UI_CONFIG');
		JText::script('COM_AKEEBA_CONFIG_UI_REFRESH');
		JText::script('COM_AKEEBA_FILEFILTERS_LABEL_UIROOT');
		JText::script('COM_AKEEBA_CONFIG_UI_FTPBROWSER_TITLE');
		JText::script('COM_AKEEBA_CONFIG_DIRECTFTP_TEST_OK');
		JText::script('COM_AKEEBA_CONFIG_DIRECTFTP_TEST_FAIL');
		JText::script('COM_AKEEBA_CONFIG_DIRECTSFTP_TEST_OK');
		JText::script('COM_AKEEBA_CONFIG_DIRECTSFTP_TEST_FAIL');
	}

	/**
	 * Returns the support status of settings encryption. The possible values are:
	 * -1 Disabled by the user
	 *  0 Enabled by inactive (not supported by the server)
	 *  1 Enabled and active
	 *
	 * @return  int
	 */
	private function getSecureSettingsOption()
	{
		// Encryption is disabled by the user
		if (Platform::getInstance()->get_platform_configuration_option('useencryption', -1) == 0)
		{
			return -1;
		}

		// Encryption is not supported by this server
		if (!Factory::getSecureSettings()->supportsEncryption())
		{
			return 0;
		}

		$filename = JPATH_COMPONENT_ADMINISTRATOR . '/BackupEngine/serverkey.php';

		// Encryption enabled, supported and a key file is present: encryption enabled
		if (is_file($filename))
		{
			return 1;
		}

		// Encryption enabled, supported but and a key file is NOT present: encryption not available
		return 0;
	}
}
