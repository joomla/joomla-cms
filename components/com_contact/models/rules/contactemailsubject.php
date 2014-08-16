<?php
/**
 * @package		Joomla.Site
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class JFormRuleContactEmailSubject extends JFormRule
{
	public function test(& $element, $value, $group = null, & $input = null, & $form = null)
	{
		$params = JComponentHelper::getParams('com_contact');
		$banned = $params->get('banned_subject');

		foreach(explode(';', $banned) as $item){
			if ($item != '' && JString::stristr($value, $item) !== false)
					return false;
		}

		return true;
	}
}
