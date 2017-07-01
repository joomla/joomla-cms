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
	 * Generate a full database dump
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

		$hash = $this->input->get('hash');

		$model = $this->getModel('database');

		$result = $model->dump($hash);

		echo (new JResponseJson($result));
	}

	public function zip()
	{
		if (!JFactory::getUser()->authorise('core.admin', 'com_installer'))
		{
			throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$hash = $this->input->get('hash');

		$model = $this->getModel('database');

		$result = $model->zip($hash);

		echo (new JResponseJson($result));
	}

	public function download()
	{
		if (!JFactory::getUser()->authorise('core.admin', 'com_installer'))
		{
			throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.path');

		$app = JFactory::getApplication();
		$host = JUri::getInstance()->getHost();

		$hash = $this->input->get('hash');

		$path = $app->get('tmp_path', JPATH_ROOT . '/tmp');
		$zipfile = JPath::check($path . '/' . $hash . '.zip');

		if (!JFile::exists($zipfile))
		{
			throw new RuntimeException(JText::_('COM_INSTALLER_MSG_WARNINGS_JOOMLATMPNOTWRITEABLE'), 500);
		}

		$handle = fopen($zipfile, 'rb');

		JFactory::getApplication()->setHeader('Pragma', 'public')
			->setHeader('Content-Type', 'application/zip')
			->setHeader('Content-Disposition', 'attachment; filename=' . JApplicationHelper::stringURLSafe($host) . '.zip')
			->setHeader('Content-Length', filesize($zipfile));

		$sizelimit = 1024 * 1024;

		while (!feof($handle))
		{
			echo fread($handle, $sizelimit);
		}

		fclose($handle);

		JFile::delete($zipfile);

	}
}
