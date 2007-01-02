<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage MailTo
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport('joomla.application.component.view');

class MailtoViewMailto extends JView
{
	function display($tpl = null)
	{
		$data = $this->getData();
		if ($data === false) {
			return false;
		}

		// Menu Parameters
		$params = &JSiteHelper::getMenuParams();

		$this->set('params', $params);
		$this->set('data'  , $data);

		parent::display($tpl);
	}

	function &getData()
	{
		$user =& JFactory::getUser();
		$data = new stdClass();

		$data->link = urldecode( JRequest::getVar( 'link' ) );
		if ($data->link == '') {
			JError::raiseError( 403, 'Link is missing' );
			$false = false;
			return $false;
		}

		if ($user->get('id') > 0) {
			$data->sender	= $user->get('name');
			$data->from		= $user->get('email');
		}
		else
		{
			$data->sender	= '';
			$data->from		= '';
		}

		return $data;
	}
}
?>