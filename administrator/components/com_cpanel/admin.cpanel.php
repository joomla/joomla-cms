<?php
/**
 * @version		$Id: admin.cpanel.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @subpackage	Admin
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;
require_once(JApplicationHelper::getPath('admin_html'));

switch (JRequest::getCmd('task'))
{
	default:
	{
		//set the component specific template file in the request
		JRequest::setVar('tmpl', 'cpanel');
		HTML_cpanel::display();
	}	break;
}