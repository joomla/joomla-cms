<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Installer Database raw Controller
 *
 * @since  __DEPLOY_VERSION__
 */
class InstallerControllerDatabase extends JControllerLegacy
{
	/**
	 * Tries to make a full database backup
	 *
	 * @return  void
	 *
	 * @throws JAccessExceptionNotallowed
	 */
	public function backup()
	{
		if (!JFactory::getUser()->authorise('core.admin', 'com_installer'))
		{
			throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$model = $this->getModel('database');

		$backup = $model->getBackup();

		JFactory::getApplication()->setHeader('Pragma', 'public')
			->setHeader('Content-Type', 'application/zip')
			->setHeader('Content-Disposition', 'attachment; filename=backup.zip')
			->setHeader('Content-Length', strlen($backup));

		echo $backup;
	}
}
