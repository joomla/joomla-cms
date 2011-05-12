<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

/**
 * Languages Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @since		1.5
 */
class LanguagesControllerInstalled extends JController
{
	/**
	 * task to set the default language
	 */
	function setDefault()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));
		$cid = JRequest::getCmd('cid', '');
		$model = $this->getModel('installed');
		if ($model->publish($cid))
		{
			$msg = JText::_('COM_LANGUAGES_MSG_DEFAULT_LANGUAGE_SAVED');
			$type = 'message';
		}
		else
		{
			$msg = $this->getError();
			$type = 'error';
		}
		$client = $model->getClient();
		$clientId = $model->getState('filter.client_id');
		$this->setredirect('index.php?option=com_languages&view=installed&client='.$clientId,$msg,$type);
	}
}
