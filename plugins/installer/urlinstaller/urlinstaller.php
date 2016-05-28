<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.Urlinstaller
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

/**
 * UrlFolderInstaller Plugin.
 *
 * @since  3.6.0
 */
class PlgInstallerUrlInstaller extends JPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Get the install type and the related form for this Plugin.
	 *
	 * @param   array  &$types    An array that will be appended with this plugin's install-type object
	 *                            Properties of that object:
	 *                            name: install type name,
	 *                            title: Title of the install type,
	 *                            description: Short description for the install type,
	 *                            button: Submit Button label
	 *                            form: The JForm instance for the install form OR the html
	 * @param   bool   $withForm  Whether to pass the install type information only or include relevant form/html also
	 *
	 * @return  bool  Always returns true
	 *
	 * @since   3.6.0
	 */
	public function onInstallerFetchInstallTypes(&$types, $withForm = false)
	{
		$type = new stdClass;

		$type->name        = 'url';
		$type->title       = JText::_('PLG_INSTALLER_URLINSTALLER_TEXT');
		$type->description = JText::_('PLG_INSTALLER_URLINSTALLER_TEXT');
		$type->button      = JText::_('PLG_INSTALLER_URLINSTALLER_BUTTON');

		if ($withForm)
		{
			$form = JForm::getInstance('com_installer.install.url', __DIR__ . '/form.xml');

			if (!$form instanceof JForm)
			{
				// We cannot render the form so skip this.
				return true;
			}

			$type->form = $form;

			$this->addScript();
		}

		$types[] = $type;

		return true;
	}

	/**
	 * Add the install submit handler script to the page
	 *
	 * @return  void
	 */
	private function addScript()
	{
		JText::script('PLG_INSTALLER_URLINSTALLER_NO_URL', true);

		$script = '
			jQuery(document).ready(function($) {
				Joomla.installer.addHandler("url", function(form) {
					form = form || document.getElementById("adminForm");
					// Do field validation 
					if (form.install_url.value == "" || form.install_url.value == "http://" || form.install_url.value == "https://") {
						alert(Joomla.JText._("PLG_INSTALLER_URLINSTALLER_NO_URL"));
						return false;
					}
					form.installtype.value = "url";
					return true;
				});
			});
		';

		JFactory::getDocument()->addScriptDeclaration($script);
	}
}
