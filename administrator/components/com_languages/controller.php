<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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
class LanguagesController extends JController
{
	/**
	 * @var		string	The default view.
	 * @since	1.6
	 */
	protected $default_view = 'installed';

	/**
	 * task to display the view
	 */
	function display()
	{
		require_once JPATH_COMPONENT.'/helpers/languages.php';

		parent::display();

		// Load the submenu.
		LanguagesHelper::addSubmenu(JRequest::getWord('view', 'installed'));
	}

	/**
	 * task to set the default language
	 */
	function publish()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));
		$model = & $this->getModel('languages');
		if ($model->publish()) {
			$msg = JText::_('COM_LANGUAGES_MSG_DEFAULT_LANGUAGE_SAVED');
			$type = 'message';
		} else {
			$msg = & $this->getError();
			$type = 'error';
		}
		$client = & $model->getClient();
		$this->setredirect('index.php?option=com_languages&client='.$client->id,$msg,$type);
	}
}
