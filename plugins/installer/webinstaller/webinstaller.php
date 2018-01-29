<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.webinstaller
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Rule\UrlRule;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Updater\Update;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;

/**
 * Support for the "Install from Web" tab
 *
 * @since  3.2
 */
class PlgInstallerWebinstaller extends CMSPlugin
{
	const REMOTE_URL = 'https://appscdn.joomla.org/webapps/';

	/**
	 * The application object.
	 *
	 * @var    CMSApplication
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * The URL to install from
	 *
	 * @var    string|null
	 * @since  __DEPLOY_VERSION__
	 */
	private $installfrom = null;

	/**
	 * Flag if the document is in a RTL direction
	 *
	 * @var    integer|null
	 * @since  __DEPLOY_VERSION__
	 */
	private $rtl = null;

	/**
	 * Event listener for the `onInstallerAddInstallationTab` event.
	 *
	 * @return  array  Returns an array with the tab information
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onInstallerAddInstallationTab()
	{
		$installfrom = $this->getInstallFrom();

		HTMLHelper::_('script', 'plg_installer_webinstaller/client.min.js', ['version' => 'auto', 'relative' => true]);
		HTMLHelper::_('stylesheet', 'plg_installer_webinstaller/client.min.css', ['version' => 'auto', 'relative' => true]);

		$devLevel = Version::PATCH_VERSION;

		if (!empty(Version::EXTRA_VERSION))
		{
			$devLevel .= '-' . Version::EXTRA_VERSION;
		}

		$doc  = Factory::getDocument();
		$lang = Factory::getLanguage();

		$doc->addScriptOptions(
			'plg_installer_webinstaller',
			[
				'base_url'        => addslashes(self::REMOTE_URL),
				'installat_url'   => base64_encode(Uri::current() . '?option=com_installer&view=install'),
				'installfrom_url' => addslashes($installfrom),
				'product'         => base64_encode(Version::PRODUCT),
				'release'         => base64_encode(Version::MAJOR_VERSION . '.' . Version::MINOR_VERSION),
				'dev_level'       => base64_encode($devLevel),
				'installfromon'   => $installfrom ? 1 : 0,
				'language'        => base64_encode($lang->getTag()),
			]
		);

		$javascript = <<<END
jQuery(document).ready(function ($) {
	var options = Joomla.getOptions('plg_installer_webinstaller', {});

	if (options.installfromon) {
		$('#myTabTabs a[href="#web"]').click();
	}

	var link = $('#myTabTabs a[href="#web"]');

	if (link.hasClass('active')) {
		if (!Joomla.apps.loaded) {
			Joomla.apps.initialize();
		}
	}

	link.closest('li').click(function () {
		if (!Joomla.apps.loaded) {
			Joomla.apps.initialize();
		}
	});
	
	if (options.installfrom_url != '') {
		link.closest('li').click();
	}

	$('#myTabTabs a[href="#web"]').on('shown.bs.tab', function () {
		if (!Joomla.apps.loaded) {
			Joomla.apps.initialize();
		}
	});
});
		
END;
		$doc->addScriptDeclaration($javascript);

		$tab = [
			'name'  => 'web',
			'label' => Text::_('COM_INSTALLER_INSTALL_FROM_WEB'),
		];

		// Render the input
		ob_start();
		include PluginHelper::getLayoutPath('installer', 'webinstaller');
		$tab['content'] = ob_get_clean();

		return $tab;
	}

	/**
	 * Internal check to determine if the output is in a RTL direction
	 *
	 * @return  integer
	 *
	 * @since   3.2
	 */
	private function isRTL()
	{
		if ($this->rtl === null)
		{
			$this->rtl = strtolower(Factory::getDocument()->getDirection()) === 'rtl' ? 1 : 0;
		}

		return $this->rtl;
	}

	/**
	 * Get the install from URL
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	private function getInstallFrom()
	{
		if ($this->installfrom === null)
		{
			$installfrom = base64_decode($this->app->input->getBase64('installfrom', ''));

			$field = new SimpleXMLElement('<field></field>');

			if ((new UrlRule)->test($field, $installfrom) && preg_match('/\.xml\s*$/', $installfrom))
			{
				$update = new Update;
				$update->loadFromXML($installfrom);
				$package_url = trim($update->get('downloadurl', false)->_data);

				if ($package_url)
				{
					$installfrom = $package_url;
				}
			}

			$this->installfrom = $installfrom;
		}

		return $this->installfrom;
	}
}
