<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.packageInstaller
 * @copyright   Copyright (C) 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;


// Injection so that the Javascript the Key can be translate in Language
JText::script('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE');

/**
 * PackageInstaller Plugin.
 *
 * @since  1.6
 */
class PlgInstallerPackageInstaller  extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	private $hathor = null;

	/**
	 * Install the Plugin before displaying it.
	 *
	 * @param   boolean  &$showpackageInstaller  Parameter.
	 *
	 * @return void
	 *
	 * @since   1.6
	 */
	public function onInstallerBeforeDisplay(&$showpackageInstaller)
	{
		$showpackageInstaller = false;
	}

	/**
	 * onInstallerViewBeforeFirstTab.
	 *
	 * @return void
	 *
	 * @since   1.6
	 */
	public function onInstallerViewBeforeFirstTab()
	{
		$lang = JFactory::getLanguage();

		// Load all the require Language file needed
		$lang->load('plg_installer_packageInstaller', JPATH_ADMINISTRATOR);

		// Filter by Position of the Plugin
		if (!$this->params->get('tab_position', 0))
		{
			$this->getChanges();
		}
	}

	/**
	 * onInstallerViewAfterLastTab.
	 *
	 * @return void
	 *
	 * @since   1.6
	 */
	public function onInstallerViewAfterLastTab()
	{
		if ($this->params->get('tab_position', 0))
		{
			$this->getChanges();
		}

		/**
		 * Load the language file on instantiation.
		 *
		 * @var    boolean
		 * @since  3.1
		 */
		$document = JFactory::getDocument();

		// External files added Javascript and CSS
		$document->addScript(JURI::root() . 'plugins/installer/packageInstaller/js/packageInstaller.js');
		$document->addStyleSheet(JURI::root() . 'plugins/installer/packageInstaller/css/client.css');
	}

	/**
	 * test comment
	 *
	 * @return $this->hathor
	 */
	private function isHathor()
	{
		if (is_null($this->hathor))
		{
			$app = JFactory::getApplication();
			$templateName = strtolower($app->getTemplate());

			if ($templateName == 'hathor')
			{
				$this->_hathor = true;
			}
			else
			{
				$this->_hathor = false;
			}
		}

		return $this->hathor;
	}

	/**
	 * Textfield or Form of the Plugin.
	 *
	 * @return object
	 */
	private function getChanges()
	{
		$ishathor = $this->isHathor() ? 1 : 0;

		if ($ishathor || !$ishathor)
		{
			echo JHtml::_('bootstrap.addTab', 'myTab', 'upload', JText::_('PLG_INSTALLER_UPLOAD_PACKAGE_FILE', true));
?>
			<fieldset class="uploadform">
				<legend><?php echo JText::_('PLG_INSTALLER_UPLOAD_INSTALL_JOOMLA_EXTENSION'); ?></legend>
				<div class="control-group">
					<label for="install_package" class="control-label"><?php echo JText::_('PLG_INSTALLER_EXTENSION_PACKAGE_FILE'); ?></label>
					<div class="controls">
						<input class="input_box" id="install_package" name="install_package" type="file" size="57" />
					</div>
				</div>
				<div class="form-actions">
					<button class="btn btn-primary" type="button" onclick="Joomla.submitbutton_package()">
						<?php echo JText::_('PLG_INSTALLER_UPLOAD_AND_INSTALL'); ?></button>
				</div>
			</fieldset>

			<!-- get the Value from the form -->
			
			<?php
			echo JHtml::_('bootstrap.endTab');
		}
	}
}
