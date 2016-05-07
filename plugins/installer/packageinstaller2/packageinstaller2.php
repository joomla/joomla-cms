<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.packageInstaller
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

// Injection so that the Javascript the Key can be translate in Language
JText::script('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE');

/**
 * PackageInstaller Plugin.
 *
 * @since  3.5
 */
class PlgInstallerPackageInstaller  extends JPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.5
	 */
	protected $autoloadLanguage = true;

	/**
	 * Is the backend Template hathor (true) or not (false)
	 *
	 * @var    boolean
	 * @since  3.5
	 */
	private $hathor = null;

	/**
	 * The onInstallerViewBeforeFirstTab event
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function onInstallerViewBeforeFirstTab()
	{
		// Filter by Position of the Plugin
		if (!$this->params->get('tab_position', 0))
		{
			$this->getChanges();
		}
	}

	/**
	 * The onInstallerViewAfterLastTab event
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function onInstallerViewAfterLastTab()
	{
		if ($this->params->get('tab_position', 0))
		{
			$this->getChanges();
		}

		$document = JFactory::getDocument();

		// External files added Javascript and CSS
		$document->addScript(JUri::root() . 'plugins/installer/packageinstaller/js/packageinstaller.js');
		$document->addStyleSheet(JUri::root() . 'plugins/installer/packageinstaller/css/client.css');
	}

	/**
	 * Returns true if it is hathor else false
	 *
	 * @return  boolean
	 *
	 * @since   3.5
	 */
	private function isHathor()
	{
		if (is_null($this->hathor))
		{
			$app          = JFactory::getApplication();
			$templateName = strtolower($app->getTemplate());

			if ($templateName == 'hathor')
			{
				$this->hathor = true;
			}
			else
			{
				$this->hathor = false;
			}
		}

		return $this->hathor;
	}

	/**
	 * Textfield or Form of the Plugin.
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	private function getChanges()
	{
		$ishathor = $this->isHathor() ? 1 : 0;

		if ($ishathor || !$ishathor)
		{
			echo JHtml::_('bootstrap.addTab', 'myTab', 'package', JText::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_PACKAGE_FILE', true));
			?>
			<fieldset class="uploadform">
				<legend><?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_INSTALL_JOOMLA_EXTENSION'); ?></legend>
				<div class="control-group">
					<label for="install_package" class="control-label"><?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_EXTENSION_PACKAGE_FILE'); ?></label>
					<div class="controls">
						<input class="input_box" id="install_package" name="install_package" type="file" size="57" />
					</div>
				</div>
				<div class="form-actions">
					<button class="btn btn-primary" type="button" onclick="Joomla.submitbutton_package()">
						<?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER__UPLOAD_AND_INSTALL'); ?></button>
				</div>
			</fieldset>

			<!-- Get the Value from the form -->

			<?php
			echo JHtml::_('bootstrap.endTab');
		}
	}
}
