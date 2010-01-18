<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once JPATH_ROOT . '/libraries/joomla/form/formfield.php';
require_once JPATH_ROOT . '/libraries/joomla/form/fields/list.php';

/**
 * Bannerclient Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	com_banners
 * @since		1.6
 */
class JFormFieldBannerClient extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'BannerClient';

	protected function _getOptions()
	{
		$db = &JFactory::getDbo();
		$options	= array();

		// This might get a conflict with the dynamic translation - TODO: search for better solution
		$query = 'SELECT id, name' .
				' FROM #__banner_clients' .
				' ORDER BY name';
		$db->setQuery($query);
		foreach ($db->loadObjectList() as $option) {
			$options[] = JHtml::_('select.option', $option->id, $option->name);
		}

		array_unshift($options, JHtml::_('select.option', '0', JText::_('Banners_No_Client')));

		return $options;
	}
	public function getOptions()
	{
		return self::_getOptions();
	}
}
