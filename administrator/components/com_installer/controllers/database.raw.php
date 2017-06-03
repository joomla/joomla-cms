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
	 * Tries to make a full database dump
	 *
	 * @return  void
	 *
	 * @throws JAccessExceptionNotallowed
	 */
	public function dump()
	{
		if (!JFactory::getUser()->authorise('core.admin', 'com_installer'))
		{
			throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$model = $this->getModel('database');

		$host = JUri::getInstance()->getHost();

		$dump = $model->getDump();

		JFactory::getApplication()->setHeader('Pragma', 'public')
			->setHeader('Content-Type', 'application/zip')
			->setHeader('Content-Disposition', 'attachment; filename=' . JApplicationHelper::stringURLSafe($host) . '-dump.zip')
			->setHeader('Content-Length', strlen($dump));

		echo $dump;
	}
}
