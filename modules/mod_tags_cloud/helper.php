<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_cloud
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_tags_cloud
 *
 * @package     Joomla.Site
 * @subpackage  mod_tags_cloud
 * @since       3.1
 */
abstract class ModTagsCloudHelper
{
	public static function getList($params)
	{
		$db               = JFactory::getDbo();
		$user             = JFactory::getUser();
		$groups           = implode(',', $user->getAuthorisedViewLevels());
		$timeframe        = $params->get('timeframe', 'alltime');
		$maximum          = $params->get('maximum', 10);
		$minsize          = $params->get('minsize', 1);
		$maxsize          = $params->get('maxsize', 2);
		$order_value      = $params->get('order_value', 'rand()');
    $display_count    = $params->get('display_count', 0);
    
    if ($order_value == 'rand()') {
      $order_direction = '';
    }
    else {
      $order_value = $db->quoteName($order_value);
      if ($params->get('order_direction', 'asc') == 'desc') {
        $order_direction = 'DESC';
      }
      else {
        $order_direction = 'ASC';
      }
    }
    
		$query = $db->getQuery(true)
			->select(
				array(
					'MAX(' . $db->quoteName('tag_id') . ') AS tag_id',
					' COUNT(*) AS count', 'MAX(t.title) AS title',
					'MAX(' .$db->quoteName('t.access') . ') AS access',
					'MAX(' .$db->quoteName('t.alias') . ') AS alias'
				)
			)
			->group($db->quoteName(array('tag_id', 'title', 'access', 'alias')))
			->from($db->quoteName('#__contentitem_tag_map'))
			->where($db->quoteName('t.access') . ' IN (' . $groups . ')');

		// Only return published tags
		$query->where($db->quoteName('t.published') . ' = 1 ');

		// Optionally filter on language
		$language = JComponentHelper::getParams('com_tags')->get('tag_list_language_filter', 'all');

		if ($language != 'all')
		{
			if ($language == 'current_language')
			{
				$language = JHelperContent::getCurrentLanguage();
			}
			$query->where($db->quoteName('t.language') . ' IN (' . $db->quote($language) . ', ' . $db->quote('*') . ')');
		}

		if ($timeframe != 'alltime')
		{
			$now = new JDate;
			$query->where($db->quoteName('tag_date') . ' > ' . $query->dateAdd($now->toSql('date'), '-1', strtoupper($timeframe)));
		}

		$query->join('INNER', $db->quoteName('#__tags', 't') . ' ON ' . $db->quoteName('tag_id') . ' = t.id')
			->order($order_value . ' ' . $order_direction);
		$db->setQuery($query, 0, $maximum);
    
		$results = $db->loadObjectList();
    
    if ($minsize > $maxsize) {
      // swap $minsize and $maxsize if minimum > maximum
      $tempsize = $minsize;
      $minsize = $maxsize;
      $maxsize = $tempsize;
    }
    
    $num = count($results);
    
    // find maximum and minimum count
    $mincount = null;
    $maxcount = null;
    foreach ($results as $row) {
      if ($mincount === null || $mincount > $row->count) {
        $mincount = $row->count;
      }
      if ($maxcount === null || $maxcount < $row->count) {
        $maxcount = $row->count;
      }      
    }
    
    $countdiff = $maxcount - $mincount;
    
    // fontsizes for tag cloud
    for ($i = 0; $i < $num; $i++) {
      $count = $results[$i]->count;
      if ($countdiff == 0) {
        $fontsize = $minsize;
      }
      else {
        $fontsize = $minsize + (($maxsize - $minsize) / ($countdiff)) * ($count - $mincount);
      }
      $results[$i]->size = $fontsize;
      
      // pass param "display_count" to the view
      $results[$i]->display_count = $display_count;
    }
    
		return $results;
	}
}
