<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Toolbar;

// Protect from unauthorized access
defined('_JEXEC') || die();

use FOF30\Toolbar\Toolbar as BaseToolbar;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar as JToolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class Toolbar extends BaseToolbar
{
	static $isJoomla3 = null;

	public function onAlices()
	{
		$this->setTitle('COM_AKEEBA_TITLE_ALICES');
		$this->backButton('JTOOLBAR_BACK', 'index.php?option=com_akeeba&view=ControlPanel');
	}

	public function onBackupsMain()
	{
		$this->setTitle('COM_AKEEBA_BACKUP');

		if (!$this->container->input->getBool('akeeba_hide_toolbar', false))
		{
			$this->backButton('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');

			ToolbarHelper::spacer();
			ToolbarHelper::help(null, false, 'https://www.akeeba.com/documentation/akeeba-backup-documentation/backup-now.html');
		}
	}

	public function onConfigurations()
	{
		$bar = JToolbar::getInstance('toolbar');

		$this->setTitle('COM_AKEEBA_CONFIG');

		ToolbarHelper::preferences('com_akeeba', '500', '660');
		ToolbarHelper::spacer();
		ToolbarHelper::apply();
		ToolbarHelper::save();
		ToolbarHelper::spacer();
		ToolbarHelper::custom('savenew', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		ToolbarHelper::cancel();
		ToolbarHelper::spacer();

		// Configuration wizard button. We apply styling to it.
		$bar->appendButton('Link', 'lightning', '<strong>' . Text::_('COM_AKEEBA_CONFWIZ') . '</strong>', 'index.php?option=com_akeeba&view=ConfigurationWizard');

		ToolbarHelper::spacer();

		if (AKEEBA_PRO)
		{
			$bar->appendButton('Link', 'calendar', Text::_('COM_AKEEBA_SCHEDULE'), 'index.php?option=com_akeeba&view=Schedule');
		}

		ToolbarHelper::spacer();
		ToolbarHelper::help(null, false, 'https://www.akeeba.com/documentation/akeeba-backup-documentation/configuration.html');

		$js = <<< JS
akeeba.Loader.add('akeeba.System', function(){
    akeeba.System.documentReady(function(){
	    var elButtons = document.querySelectorAll('#toolbar-lightning>button');
	    akeeba.System.iterateNodes(elButtons, function (elButton) {
			akeeba.System.addClass(elButton, 'btn-primary');        
	    });
    });
});

JS;
		$this->container->template->addJSInline($js);
	}

	public function onConfigurationWizardsMain()
	{
		$this->setTitle('COM_AKEEBA_CONFWIZ');
		$this->backButton('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		ToolbarHelper::spacer();
		ToolbarHelper::help(null, false, 'https://www.akeeba.com/documentation/akeeba-backup-documentation/configuration-wizard.html');
	}

	public function onControlPanelsMain()
	{
		$this->setTitle('COM_AKEEBA_CONTROLPANEL');
		ToolbarHelper::preferences('com_akeeba', '500', '660');
		ToolbarHelper::spacer();
		ToolbarHelper::help(null, false, 'https://www.akeeba.com/documentation/akeeba-backup-documentation/control-panel.html');
	}

	public function onDatabaseFiltersMain()
	{
		$this->setTitle('COM_AKEEBA_DBFILTER');
		$this->backButton('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		ToolbarHelper::spacer();
		ToolbarHelper::help(null, false, 'https://www.akeeba.com/documentation/akeeba-backup-documentation/database-tables-exclusion.html');
	}

	public function onDiscovers()
	{
		$this->setTitle('COM_AKEEBA_DISCOVER');

		$this->backButton('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		ToolbarHelper::spacer();
		ToolbarHelper::help(null, false, 'https://www.akeeba.com/documentation/akeeba-backup-documentation/discover-import-archives.html');
	}

	public function onFileFiltersMain()
	{
		$this->setTitle('COM_AKEEBA_FILEFILTERS');

		ToolbarHelper::title(Text::_('COM_AKEEBA') . ': <small>' . Text::_('COM_AKEEBA_FILEFILTERS') . '</small>', 'akeeba');
		$this->backButton('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		ToolbarHelper::spacer();
		ToolbarHelper::help(null, false, 'https://www.akeeba.com/documentation/akeeba-backup-documentation/exclude-data-from-backup.html#files-and-directories-exclusion');
	}

	public function onIncludeFoldersMain()
	{
		$this->setTitle('COM_AKEEBA_INCLUDEFOLDER');

		$this->backButton('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		ToolbarHelper::spacer();
		ToolbarHelper::help(null, false, 'https://www.akeeba.com/documentation/akeeba-backup-documentation/off-site-directories-inclusion.html');
	}

	public function onLogs()
	{
		$this->setTitle('COM_AKEEBA_LOG');

		$this->backButton('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		ToolbarHelper::spacer();
		ToolbarHelper::help(null, false, 'https://www.akeeba.com/documentation/akeeba-backup-documentation/view-log.html');
	}

	public function onManagesDefault()
	{
		$this->setTitle('COM_AKEEBA_BUADMIN');

		if (AKEEBA_PRO)
		{
			$bar  = JToolbar::getInstance('toolbar');
			$icon = $this->isJoomla3() ? 'folder-open' : 'search';
			$bar->appendButton('Link', $icon, Text::_('COM_AKEEBA_DISCOVER'), 'index.php?option=com_akeeba&view=Discover');
		}

		$user        = $this->container->platform->getUser();
		$permissions = [
			'configure' => $user->authorise('akeeba.configure', 'com_akeeba'),
			'backup'    => $user->authorise('akeeba.backup', 'com_akeeba'),
		];

		if ($permissions['configure'] && AKEEBA_PRO)
		{
			ToolbarHelper::publish('restore', Text::_('COM_AKEEBA_BUADMIN_LABEL_RESTORE'));
		}

		if ($permissions['backup'])
		{
			ToolbarHelper::editList('showcomment', Text::_('COM_AKEEBA_BUADMIN_LOG_EDITCOMMENT'));
		}

		if ($permissions['configure'] || $permissions['backup'])
		{
			ToolbarHelper::spacer();
		}

		if ($permissions['backup'])
		{
			ToolbarHelper::deleteList();
			ToolbarHelper::custom('deletefiles', 'delete.png', 'delete_f2.png', Text::_('COM_AKEEBA_BUADMIN_LABEL_DELETEFILES'), true);
			ToolbarHelper::spacer();
		}

		$this->backButton('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		ToolbarHelper::spacer();
		ToolbarHelper::help(null, false, 'https://www.akeeba.com/documentation/akeeba-backup-documentation/adminsiter-backup-files.html');
	}

	public function onManagesShowcomment()
	{
		$this->setTitle('COM_AKEEBA_BUADMIN');

		$this->backButton('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		ToolbarHelper::save();
		ToolbarHelper::cancel();
		ToolbarHelper::spacer();
		ToolbarHelper::help(null, false, 'https://www.akeeba.com/documentation/akeeba-backup-documentation/adminsiter-backup-files.html');
	}

	public function onMultipleDatabasesMain()
	{
		$this->setTitle('COM_AKEEBA_MULTIDB');

		$this->backButton('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		ToolbarHelper::spacer();
		ToolbarHelper::help(null, false, 'https://www.akeeba.com/documentation/akeeba-backup-documentation/include-data-to-archive.html#multiple-db-definitions');
	}

	public function onProfilesAdd()
	{
		parent::onAdd();

		$this->setTitle('COM_AKEEBA_PROFILES_PAGETITLE_NEW');

		ToolbarHelper::spacer();
		ToolbarHelper::help(null, false, 'https://www.akeeba.com/documentation/akeeba-backup-documentation/using-basic-operations.html#profiles-management');
	}

	public function onProfilesBrowse()
	{
		$this->setTitle('COM_AKEEBA_PROFILES');

		$this->backButton('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		ToolbarHelper::spacer();
		ToolbarHelper::addNew();
		ToolbarHelper::custom('copy', 'copy.png', 'copy_f2.png', 'COM_AKEEBA_LBL_BATCH_COPY', false);
		ToolbarHelper::spacer();
		ToolbarHelper::deleteList();
		ToolbarHelper::spacer();
		ToolbarHelper::spacer();
		ToolbarHelper::help(null, false, 'https://www.akeeba.com/documentation/akeeba-backup-documentation/using-basic-operations.html#profiles-management');
	}

	public function onProfilesEdit()
	{
		parent::onEdit();

		$this->setTitle('COM_AKEEBA_PROFILES_PAGETITLE_EDIT');

		ToolbarHelper::spacer();
		ToolbarHelper::help(null, false, 'https://www.akeeba.com/documentation/akeeba-backup-documentation/using-basic-operations.html#profiles-management');
	}

	public function onRegExDatabaseFiltersMain()
	{
		$this->setTitle('COM_AKEEBA_REGEXDBFILTERS');

		$this->backButton('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		ToolbarHelper::spacer();
		ToolbarHelper::help(null, false, 'https://www.akeeba.com/documentation/akeeba-backup-documentation/regex-database-tables-exclusion.html');
	}

	public function onRegExFileFiltersMain()
	{
		$this->setTitle('COM_AKEEBA_REGEXFSFILTERS');

		$this->backButton('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		ToolbarHelper::spacer();
		ToolbarHelper::help(null, false, 'https://www.akeeba.com/documentation/akeeba-backup-documentation/regex-files-directories-exclusion.html');
	}

	public function onRemoteFilesDownloadToServer()
	{
		$this->setTitle('COM_AKEEBA_REMOTEFILES');

		ToolbarHelper::spacer();
		ToolbarHelper::help(null, false, 'https://www.akeeba.com/documentation/akeeba-backup-documentation/ch03s03s05s02.html');
	}

	public function onRemoteFilesListActions()
	{
		$this->setTitle('COM_AKEEBA_REMOTEFILES');

		ToolbarHelper::spacer();
		ToolbarHelper::help(null, false, 'https://www.akeeba.com/documentation/akeeba-backup-documentation/manage-remotely-stored-files.html');
	}

	public function onRestores()
	{
		$this->setTitle('COM_AKEEBA_RESTORE');

		$this->backButton('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		ToolbarHelper::spacer();
		ToolbarHelper::help(null, false, 'https://www.akeeba.com/documentation/akeeba-backup-documentation/adminsiter-backup-files.html#integrated-restoration');
	}

	public function onS3ImportsMain()
	{
		$this->setTitle('COM_AKEEBA_S3IMPORT');

		$this->backButton('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		ToolbarHelper::spacer();
		ToolbarHelper::help(null, false, 'https://www.akeebabackup .com/documentation/akeeba-backup-documentation/import-s3.html');
	}

	public function onS3ImportsDltoserver()
	{
		$this->setTitle('COM_AKEEBA_S3IMPORT');

		$this->backButton('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		ToolbarHelper::spacer();
		ToolbarHelper::help(null, false, 'https://www.akeebabackup .com/documentation/akeeba-backup-documentation/import-s3.html');
	}

	public function onSchedules()
	{
		$this->setTitle('COM_AKEEBA_SCHEDULE');

		$this->backButton('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		ToolbarHelper::spacer();
		ToolbarHelper::help(null, false, 'https://www.akeeba.com/documentation/akeeba-backup-documentation/automating-your-backup.html');
	}

	public function onStatisticsMain()
	{
		$this->onManagesDefault();
	}

	public function onTransfers()
	{
		$this->setTitle('COM_AKEEBA_TRANSFER');

		$this->backButton('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');

		$bar  = JToolbar::getInstance('toolbar');
		$icon = $this->isJoomla3() ? 'loop' : 'refresh';
		$bar->appendButton('Link', $icon, 'COM_AKEEBA_TRANSFER_BTN_RESET', 'index.php?option=com_akeeba&view=Transfer&task=reset');
	}

	protected function isJoomla3()
	{
		if (is_null(self::$isJoomla3))
		{
			self::$isJoomla3 = version_compare(JVERSION, '3.999.999', 'lt');
		}

		return self::$isJoomla3;
	}

	protected function setTitle($viewTitle)
	{
		$title = Text::_('COM_AKEEBA');

		if ($this->isJoomla3())
		{
			$icon  = 'akeeba';
			$title .= ' <span style="display: none;"> - </span><small>';
		}
		else
		{
			$icon  = 'akeeba-j4';
			$title .= "<small> – ";
		}

		$title .= Text::_($viewTitle) . "</small>";

		ToolbarHelper::title($title, $icon);
	}

	protected function backButton($label, $link)
	{
		if ($this->isJoomla3())
		{
			ToolbarHelper::back($label, $link);

			return;
		}

		$bar = JToolbar::getInstance('toolbar');
		$bar->appendButton('Link', 'chevron-left', $label, $link);
	}
}
