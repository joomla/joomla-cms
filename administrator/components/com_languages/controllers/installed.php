<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Languages Controller.
 *
 * @since  1.5
 */
class LanguagesControllerInstalled extends JControllerLegacy
{
	/**
	 * Task to set the default language.
	 *
	 * @return  void
	 */
	public function setDefault()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$cid = $this->input->get('cid', '');
		$model = $this->getModel('installed');

		if ($model->publish($cid))
		{
			// Switching to the new administrator language for the message
			if ($model->getState('client_id') == 1)
			{
				$language = JFactory::getLanguage();
				$newLang = JLanguage::getInstance($cid);
				JFactory::$language = $newLang;
				JFactory::getApplication()->loadLanguage($language = $newLang);
				$newLang->load('com_languages', JPATH_ADMINISTRATOR);
			}

			$msg = JText::_('COM_LANGUAGES_MSG_DEFAULT_LANGUAGE_SAVED');
			$type = 'message';
		}
		else
		{
			$msg = $this->getError();
			$type = 'error';
		}

		$clientId = $model->getState('client_id');
		$this->setredirect('index.php?option=com_languages&view=installed&client=' . $clientId, $msg, $type);
	}

	/**
	 * Task to switch the administrator language.
	 *
	 * @return  void
	 */
	public function switchAdminLanguage()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$cid   = $this->input->get('cid', '');
		$model = $this->getModel('installed');

		// Fetching the language name from the xx-XX.xml
		$file = JPATH_ADMINISTRATOR . '/language/' . $cid . '/' . $cid . '.xml';
		$info = JInstaller::parseXMLInstallFile($file);
		$languageName = $info['name'];

		if ($model->switchAdminLanguage($cid))
		{
			// Switching to the new language for the message
			$language = JFactory::getLanguage();
			$newLang = JLanguage::getInstance($cid);
			JFactory::$language = $newLang;
			JFactory::getApplication()->loadLanguage($language = $newLang);
			$newLang->load('com_languages', JPATH_ADMINISTRATOR);

			$msg = JText::sprintf('COM_LANGUAGES_MSG_SWITCH_ADMIN_LANGUAGE_SUCCESS', $languageName);
			$type = 'message';
		}
		else
		{
			$msg = $this->getError();
			$type = 'error';
		}

		$this->setredirect('index.php?option=com_languages&view=installed', $msg, $type);
	}
}
