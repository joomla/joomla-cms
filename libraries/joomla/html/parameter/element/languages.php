<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Renders a languages element
 *
 * @author 		Johan Janssens <johan.janssens@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage 	Parameter
 * @since		1.5
 */

class JElement_Languages extends JElement
{
   /**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Languages';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$user	= & JFactory::getUser();

		/*
		 * @TODO: change to acl_check method
		 */
		if(!($user->get('gid') >= 23) && $node->attributes('client') == 'administrator') {
			return JText::_('No Access');
		}


		$client = $node->attributes('client');

		$languages = JLanguageHelper::createLanguageList($value, constant('JPATH_'.strtoupper($client)));
		array_unshift($languages, JHTMLSelect::option('', '- '.JText::_('Select Language').' -'));

		return JHTMLSelect::genericList($languages, ''.$control_name.'['.$name.']', 'class="inputbox"', 'value', 'text', $value, $control_name.$name );
	}
}
?>