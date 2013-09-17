<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('ordering');

/**
 * Supports an HTML select list of plugins
 *
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 * @since       1.6
 */
class JFormFieldWeblinkordering extends JFormFieldOrdering
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since   1.6
	 */
	protected $type = 'Weblinkordering';

	/**
	 * Builds the query for the ordering list.
	 *
	 * @return  JDatabaseQuery  The query for the ordering form field
	 */
	protected function getQuery()
	{
		$categoryId	= (int) $this->form->getValue('catid');

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array($db->quoteName('ordering', 'value'), $db->quoteName('title', 'text')))
			->from($db->quoteName('#__weblinks'))
			->where($db->quoteName('catid') . ' = ' . (int) $categoryId)
			->order('ordering');

		return $query;
	}
}
