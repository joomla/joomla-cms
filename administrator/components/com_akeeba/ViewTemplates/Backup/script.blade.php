<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * This file passes parameters to the Backup.js script using Joomla's script options API
 *
 * @var  $this  \Akeeba\Backup\Admin\View\Backup\Html
 */

$escapedBaseURL = addslashes(Uri::base());
$platform       = $this->container->platform;

// Initialization
$platform->addScriptOptions('akeeba.Backup.defaultDescription', addslashes($this->defaultDescription));
$platform->addScriptOptions('akeeba.Backup.currentDescription', addslashes(empty($this->description) ? $this->defaultDescription : $this->description));
$platform->addScriptOptions('akeeba.Backup.currentComment', addslashes($this->comment));
$platform->addScriptOptions('akeeba.Backup.config_angiekey', addslashes($this->ANGIEPassword));
$platform->addScriptOptions('akeeba.Backup.jpsKey', $this->showJPSPassword ? addslashes($this->jpsPassword) : '');

// Auto-resume setup
$platform->addScriptOptions('akeeba.Backup.resume.enabled', (bool) $this->autoResume);
$platform->addScriptOptions('akeeba.Backup.resume.timeout', (int) $this->autoResumeTimeout);
$platform->addScriptOptions('akeeba.Backup.resume.maxRetries', (int) $this->autoResumeRetries);

// The return URL
$platform->addScriptOptions('akeeba.Backup.returnUrl', addcslashes($this->returnURL, "'\\"));

// Used as parameters to start_timeout_bar()
$platform->addScriptOptions('akeeba.Backup.maxExecutionTime', (int) $this->maxExecutionTime);
$platform->addScriptOptions('akeeba.Backup.runtimeBias', (int) $this->runtimeBias);

// Notifications
$platform->addScriptOptions('akeeba.System.notification.iconURL', sprintf("%s../media/com_akeeba/icons/logo-48.png", $escapedBaseURL));
$platform->addScriptOptions('akeeba.System.notification.hasDesktopNotification', (bool) $this->desktopNotifications);

// Domain keys
$platform->addScriptOptions('akeeba.Backup.domains', $this->domains);

// AJAX proxy, View Log and ALICE URLs
$platform->addScriptOptions('akeeba.System.params.AjaxURL', 'index.php?option=com_akeeba&view=Backup&task=ajax');
$platform->addScriptOptions('akeeba.Backup.URLs.LogURL', sprintf("%sindex.php?option=com_akeeba&view=Log", $escapedBaseURL));
$platform->addScriptOptions('akeeba.Backup.URLs.AliceURL', sprintf("%sindex.php?option=com_akeeba&view=Alice", $escapedBaseURL));

// Behavior triggers
$platform->addScriptOptions('akeeba.Backup.autostart', (!$this->unwriteableOutput && $this->autoStart) ? 1 : 0);

// Push language strings to Javascript
Text::script('COM_AKEEBA_BACKUP_TEXT_LASTRESPONSE');
Text::script('COM_AKEEBA_BACKUP_TEXT_BACKUPSTARTED');
Text::script('COM_AKEEBA_BACKUP_TEXT_BACKUPFINISHED');
Text::script('COM_AKEEBA_BACKUP_TEXT_BACKUPHALT');
Text::script('COM_AKEEBA_BACKUP_TEXT_BACKUPRESUME');
Text::script('COM_AKEEBA_BACKUP_TEXT_BACKUPHALT_DESC');
Text::script('COM_AKEEBA_BACKUP_TEXT_BACKUPFAILED');
Text::script('COM_AKEEBA_BACKUP_TEXT_BACKUPWARNING');
Text::script('COM_AKEEBA_BACKUP_TEXT_AVGWARNING');
