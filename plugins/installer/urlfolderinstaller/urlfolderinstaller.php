<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.urlFolderInstaller
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

// Injection so that the Javascript the Key can be translate in Language
JText::script('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE');

/**
 * UrlFolderInstaller Plugin.
 *
 * @since  3.5
 */
class PlgInstallerUrlFolderInstaller  extends JPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.5
	 */
	protected $autoloadLanguage = true;

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
		$document->addScript(JUri::root() . 'plugins/installer/urlfolderinstaller/js/urlfolderinstaller.js');
		$document->addStyleSheet(JUri::root() . 'plugins/installer/urlfolderinstaller/css/client.css');
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
		echo JHtml::_('bootstrap.addTab', 'myTab', 'urlfolder', JText::_('PLG_INSTALLER_URLFOLDERINSTALLER_INSTALLALL_TEXT', true));
		?>
		<div class="clr"></div>
		<fieldset class="uploadform">
			<legend><?php echo JText::_('PLG_INSTALLER_URLFOLDERINSTALLER_INSTALLALL_TEXT'); ?></legend>
			<div class="control-group">
				<label for="install_all" class="control-label"><?php echo JText::_('PLG_INSTALLER_URLFOLDERINSTALLER_INSTALLALL_TEXT'); ?></label>
				<div class="controls">
					<input type="text" id="install_all" name="install_all" class="span5 input_box" size="70" value="" />
				</div>

			</div>
			<div class="form-actions">
				<input type="button" class="btn btn-primary"
					value="<?php echo JText::_('PLG_INSTALLER_URLFOLDERINSTALLER_INSTALLALL_BUTTON'); ?>" onclick="Joomla.submitbuttonall()"
				/>
			</div>

			<input type="hidden" name="install_url" value="" />
			<input type="hidden" name="install_directory" value="" />
		</fieldset>

		<?php
		echo JHtml::_('bootstrap.endTab');
	}
}
