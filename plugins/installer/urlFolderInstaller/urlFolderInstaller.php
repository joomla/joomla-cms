<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.urlFolderInstaller
 * @copyright   Copyright (C) 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
// Injection so that the Javascript the Key can be translate in Language
JText::script('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE');

/**
 * UrlFolderInstaller Plugin.
 *
 * @since  1.6
 */
class PlgInstallerUrlFolderInstaller  extends JPlugin
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
		$lang->load('plg_installer_urlFolderInstaller', JPATH_ADMINISTRATOR);

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
		$document->addScript(JURI::root() . 'plugins/installer/urlFolderInstaller/js/urlFolderInstaller.js');
		$document->addStyleSheet(JURI::root() . 'plugins/installer/urlFolderInstaller/css/client.css');
	}

	/**
	 * This is for the Layout of the Plugin.
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
			echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'all'));
			echo JHtml::_('bootstrap.addTab', 'myTab', 'all', JText::_('PLG_INSTALLALL_TEXT', true));
			?>
			<div class="clr"></div>
			<fieldset class="uploadform">
				<legend><?php echo JText::_('PLG_INSTALLALL_TEXT'); ?></legend>
				<div class="control-group">
					<label for="install_all" class="control-label"><?php echo JText::_('PLG_INSTALLALL_TEXT'); ?></label>
					<div class="controls">
						<input type="text" id="install_all" name="install_all" class="span5 input_box" size="70" value="" />
					</div>

				</div>
				<div class="form-actions">
					<input type="button" class="btn btn-primary" value="<?php echo JText::_('PLG_INSTALLALL_BUTTON'); ?>" onclick="Joomla.submitbuttonall()" />
				</div>

				<input type="hidden" name="install_url" value="" />
				<input type="hidden" name="install_directory" value="" />
			</fieldset>

			<?php
			echo JHtml::_('bootstrap.endTab');
		}

	}
}