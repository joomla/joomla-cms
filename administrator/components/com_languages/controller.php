<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

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
	 * task to display the view
	 */
	function display()
	{
		// Load the submenu.
		$model = & $this->getModel('languages');
		$client = & $model->getClient();
		require_once JPATH_COMPONENT.DS.'helpers'.DS.'languages.php';
		LanguagesHelper::addSubmenu($client->id);
		parent::display();
	}

	/**
	 * task to set the default language
	 */
	function publish()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$model = & $this->getModel('languages');
		if ($model->publish())
		{
			$msg = JText::_('Languages_Default_Language_Saved');
			$type = 'message';
		}
		else
		{
			$msg = & $this->getError();
			$type = 'error';
		}
		$client = & $model->getClient();
		$this->setredirect('index.php?option=com_languages&client='.$client->id,$msg,$type);
	}
}
