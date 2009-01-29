<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('JPATH_BASE') or die('Restricted Access');

jimport('joomla.html.html');
require_once dirname(__FILE__).DS.'list.php';

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldEditors extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Editors';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions()
	{
		// @todo Why was this check here - this element should be agnostic
		//$user	= & JFactory::getUser();
		//if (!($user->get('gid') >= 19)) {
		//	return JText::_('No Access');
		//}

		// compile list of the editors
		$query	= 'SELECT element AS value, name AS text'
				. ' FROM #__extensions'
				. ' WHERE folder = "editors"'
				. ' AND type = "plugin"'
				. ' AND enabled = 1'
				. ' ORDER BY ordering, name';
		$db = & JFactory::getDBO();
		$db->setQuery($query);
		try {
			$options	= array_merge(
							parent::_getOptions(),
							$db->loadObjectList()
						);
		}
		catch (JException $e) {
			$options = array();
		}

		return $options;
	}
}