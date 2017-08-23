<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;

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
	 *
	 * @since  __DEPLOY_VERSION__
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

	/**
	 * Delete a (broken) dump
	 *
	 * @return  void
	 *
	 * @throws JAccessExceptionNotallowed
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function delete()
	{
		if (!JFactory::getUser()->authorise('core.admin', 'com_installer'))
		{
			throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.path');

		$app = JFactory::getApplication();

		$hash = $this->input->get('hash');

		if (StringHelper::strlen($hash) != 20)
		{
			return;
		}

		$path = $app->get('tmp_path', JPATH_ROOT . '/tmp');
		$dump = JPath::check($path . '/' . $hash . '.php');

		$file = JPath::check($dump);

		if (JFile::exists($path))
		{
			JFile::delete($file);
		}
	}

	/**
	 * Download a finished dump
	 *
	 * @return  void
	 *
	 * @throws JAccessExceptionNotallowed
	 *
	 * @since  __DEPLOY_VERSION__
	 */
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
		$dump = JPath::check($path . '/' . $hash . '.php');

		if (StringHelper::strlen($hash) != 20 || !JFile::exists($dump))
		{
			throw new RuntimeException(JText::_('COM_INSTALLER_MSG_WARNINGS_JOOMLATMPNOTWRITEABLE'), 500);
		}

		$handle = fopen($dump, 'rb');

		JFactory::getApplication()->setHeader('Pragma', 'public')
			->setHeader('Content-Type', 'application/sql')
			->setHeader('Content-Disposition', 'attachment; filename=' . JApplicationHelper::stringURLSafe($host) . '.sql')
			->setHeader('Content-Length', filesize($dump))
			->sendHeaders();

		if (ob_get_length())
		{
			ob_end_clean();
		}

		$sizelimit = 1024 * 1024;

		set_time_limit(0);

		while (!feof($handle))
		{
			echo fread($handle, $sizelimit);

			flush();
		}

		fclose($handle);

		JFile::delete($dump);

	}
}
