<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Parameter
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Renders a languages element
 *
 * @package		Joomla.Framework
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
		$client = $node->attributes('client');

		jimport('joomla.language.helper');
		$languages = JLanguageHelper::createLanguageList($value, constant('JPATH_'.strtoupper($client)), true);
		array_unshift($languages, JHtml::_('select.option', '', JText::_('JOPTION_SELECT_LANGUAGE')));

		return JHtml::_('select.genericlist', $languages, $control_name .'['. $name .']',
			array(
				'id' => $control_name.$name,
				'list.attr' => 'class="inputbox"',
				'list.select' => $value
			)
		);
	}
}
