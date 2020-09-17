<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\View\ConfigurationWizard;

// Protect from unauthorized access
defined('_JEXEC') || die();

use FOF30\View\DataView\Html as BaseView;
use Joomla\CMS\Language\Text;

class Html extends BaseView
{
	protected function onBeforeMain()
	{
		// Push translations
		// -- Wizard
		Text::script('COM_AKEEBA_CONFWIZ_UI_MINEXECTRY');
		Text::script('COM_AKEEBA_CONFWIZ_UI_CANTSAVEMINEXEC');
		Text::script('COM_AKEEBA_CONFWIZ_UI_SAVEMINEXEC');
		Text::script('COM_AKEEBA_CONFWIZ_UI_CANTDETERMINEMINEXEC');
		Text::script('COM_AKEEBA_CONFWIZ_UI_CANTFIXDIRECTORIES');
		Text::script('COM_AKEEBA_CONFWIZ_UI_CANTDBOPT');
		Text::script('COM_AKEEBA_CONFWIZ_UI_EXECTOOLOW');
		Text::script('COM_AKEEBA_CONFWIZ_UI_SAVINGMAXEXEC');
		Text::script('COM_AKEEBA_CONFWIZ_UI_CANTSAVEMAXEXEC');
		Text::script('COM_AKEEBA_CONFWIZ_UI_CANTDETERMINEPARTSIZE');
		Text::script('COM_AKEEBA_CONFWIZ_UI_PARTSIZE');

		// -- Backup
		Text::script('COM_AKEEBA_BACKUP_TEXT_LASTRESPONSE', true);

		// Load the Configuration Wizard Javascript file
		$this->container->template->addJS('media://com_akeeba/js/Backup.min.js', true, false, $this->container->mediaVersion);
		$this->container->template->addJS('media://com_akeeba/js/ConfigurationWizard.min.js', true, false, $this->container->mediaVersion);

		$platform = $this->container->platform;
		$platform->addScriptOptions('akeeba.System.params.AjaxURL', 'index.php?option=com_akeeba&view=ConfigurationWizard&task=ajax');

		// Set the layour
		$this->setLayout('wizard');
	}
}
