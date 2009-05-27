<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Parameter
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Renders a languages element
 *
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class JElementLanguages extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	protected $_name = 'Languages';

	public function fetchElement($name, $value, &$node, $control_name)
	{
		$user	= & JFactory::getUser();

		/*
		 * @TODO: change to acl_check method
		 */
		if (!($user->get('gid') >= 23) && $node->attributes('client') == 'administrator') {
			return JText::_('No Access');
		}


		$client = $node->attributes('client');

		jimport('joomla.language.helper');
		$languages = JLanguageHelper::createLanguageList($value, constant('JPATH_'.strtoupper($client)), true);
		array_unshift($languages, JHtml::_('select.option', '', '- '.JText::_('Select Language').' -'));

		return JHtml::_('select.genericlist', $languages, $control_name .'['. $name .']',
			array(
				'id' => $control_name.$name,
				'list.attr' => 'class="inputbox"',
				'list.select' => $value
			)
		);
	}
}
