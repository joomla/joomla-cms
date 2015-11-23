<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

JFormHelper::loadFieldClass('list');

class JFormFieldRanks extends JFormFieldList
{
	protected $type = 'Ranks';
	
	protected function getOptions()
	{
		$options = array();
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true)
			->select('a.id AS value, a.title AS text')
			->from('#__cjforum_ranks AS a')
			->order('a.ordering ASC');
		
		$db->setQuery($query);
		
		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}
		
		$options = array_merge(parent::getOptions(), $options);
		
		return $options;
	}
}