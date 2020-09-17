<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Controller\Mixin;

// Protect from unauthorized access
defined('_JEXEC') || die();

use RuntimeException;
use Joomla\CMS\Language\Text;

trait CustomACL
{
	protected function onBeforeExecute(&$task)
	{
		$this->akeebaBackupACLCheck($this->view, $this->task);
	}

	/**
	 * Checks if the currently logged in user has the required ACL privileges to access the current view. If not, a
	 * RuntimeException is thrown.
	 *
	 * @return  void
	 */
	protected function akeebaBackupACLCheck($view, $task)
	{
		// Akeeba Backup-specific ACL checks. All views not listed here are limited by the akeeba.configure privilege.
		$viewACLMap = [
			'ControlPanel'       => 'core.manage',
			'Backup'             => 'akeeba.backup',
			'Manage'             => 'core.manage',
			'Manage.download'    => 'akeeba.download',
			'Manage.remove'      => 'akeeba.download',
			'Manage.deletefiles' => 'akeeba.download',
			'Manage.showcomment' => 'akeeba.backup',
			'Manage.save'        => 'akeeba.download',
			'Manage.restore'     => 'akeeba.configure',
			'Manage.cancel'      => 'akeeba.backup',
			'Upload'             => 'akeeba.backup',
			'RemoteFiles'        => 'akeeba.download',
			'Transfer'           => 'akeeba.download',
		];

		// Default
		$privilege = 'akeeba.configure';

		// Just the view was found
		if (array_key_exists($view, $viewACLMap))
		{
			$privilege = $viewACLMap[$view];
		}

		// The view AND task was found
		if (array_key_exists($view . '.' . $task, $viewACLMap))
		{
			$privilege = $viewACLMap[$view . '.' . $task];
		}

		// If an empty privilege is defined do not perform any ACL checks
		if (empty($privilege))
		{
			return;
		}

		if (!$this->container->platform->authorise($privilege, 'com_akeeba'))
		{
			throw new RuntimeException(\Joomla\CMS\Language\Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}
	}
}
