<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Languages Controller
 *
 * @package		Joomla.Site
 * @subpackage	com_languages
 * @since		1.6
 */
class LanguagesController extends JController
{
	function select()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));
		
		$tag = JRequest::getCmd('tag', 'en-GB');
		$redirect = JRequest::getVar('redirect');
		if ($tag)
		{
			$model = &$this->getModel('Language','LanguagesModel',array('ignore_request'=>true));
			$model->setState('language.tag',$tag);
			$model->select();
		}
		$this->setRedirect(base64_decode($redirect));
	}
}
