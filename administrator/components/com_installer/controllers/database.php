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
 * Installer Database Controller
 *
 * @since  2.5
 */
class InstallerControllerDatabase extends JControllerLegacy
{
	/**
	 * Tries to fix missing database updates
	 *
	 * @return  void
	 *
	 * @since   2.5
	 * @todo    Purge updates has to be replaced with an events system
	 */
	public function fix()
	{
		$model = $this->getModel('database');
		$model->fix();

		// Purge updates
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_joomlaupdate/models', 'JoomlaupdateModel');
		$updateModel = JModelLegacy::getInstance('default', 'JoomlaupdateModel');
		$updateModel->purge();

		// Refresh versionable assets cache
		JFactory::getApplication()->flushAssets();

		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=database', false));
	}

	/**
	 * Finalise core update
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function finaliseUpdate()
	{
		// Finalise incomplete core update
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_joomlaupdate/models', 'JoomlaupdateModel');
		$updateModel = JModelLegacy::getInstance('default', 'JoomlaupdateModel');

		// Load up the logger
		JLog::addLogger(
			array(
				'format'    => '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}',
				'text_file' => 'joomla_update.php',
			),
			JLog::INFO,
			array('Update', 'databasequery', 'jerror')
		);

		// Load the Joomla library and update component language files
		$lang = JFactory::getLanguage();
		$lang->load('lib_joomla');
		$lang->load('com_joomlaupdate');

		JLog::add(JText::_('COM_JOOMLAUPDATE_UPDATE_LOG_FINALISE'), JLog::INFO, 'Update');

		// Finalize the update
		if ($updateModel->finaliseUpgrade() === false)
		{
			$app->enqueueMessage('Postupdate failed', 'error');

			return false;
		}

		JLog::add(JText::_('COM_JOOMLAUPDATE_UPDATE_LOG_CLEANUP'), JLog::INFO, 'Update');

		// Cleanup after the update
		$updateModel->cleanUp();

		JLog::add(JText::sprintf('COM_JOOMLAUPDATE_UPDATE_LOG_COMPLETE', JVERSION), JLog::INFO, 'Update');

		// Purge updates
		$updateModel->purge();

		// Refresh versionable assets cache
		JFactory::getApplication()->flushAssets();

		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=database', false));
	}
}
