<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Content Component Query Helper
 *
 * @static
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since 1.5
 */
class ContentHelperQuery
{
	function orderbyPrimary($orderby)
	{
		switch ($orderby)
		{
			case 'alpha' :
				$orderby = 'c.title, ';
				break;

			case 'ralpha' :
				$orderby = 'c.title DESC, ';
				break;

			case 'order' :
				$orderby = 'c.ordering, ';
				break;

			default :
				$orderby = '';
				break;
		}

		return $orderby;
	}

	function orderbySecondary($orderby, $orderDate = 'created')
	{
		switch ($orderDate)
		{
			case 'modifed' :
				$queryDate = 'a.modified';
				break;
				
			case 'published' : 
				$queryDate = 'a.publish_up';
				
			case 'created' : 
			default : 
				$queryDate = 'a.created';
				break;
		}

		switch ($orderby)
		{
			case 'date' :
				$orderby = $queryDate;
				break;

			case 'rdate' :
				$orderby = $queryDate . ' DESC';
				break;

			case 'alpha' :
				$orderby = 'a.title';
				break;

			case 'ralpha' :
				$orderby = 'a.title DESC';
				break;

			case 'hits' :
				$orderby = 'a.hits DESC';
				break;

			case 'rhits' :
				$orderby = 'a.hits';
				break;

			case 'order' :
				$orderby = 'a.ordering';
				break;

			case 'author' :
				$orderby = 'a.created_by_alias, u.name';
				break;

			case 'rauthor' :
				$orderby = 'a.created_by_alias DESC, u.name DESC';
				break;

			case 'front' :
				$orderby = 'f.ordering';
				break;

			default :
				$orderby = 'a.ordering';
				break;
		}

		return $orderby;
	}

	function buildVotingQuery($params=null)
	{
		if (!$params) {
			$params = &JComponentHelper::getParams('com_content');
		}
		$voting = $params->get('show_vote');

		if ($voting) {
			// calculate voting count
			$select = ' , ROUND(v.rating_sum / v.rating_count) AS rating, v.rating_count';
			$join = ' LEFT JOIN #__content_rating AS v ON a.id = v.content_id';
		} else {
			$select = '';
			$join = '';
		}

		$results = array ('select' => $select, 'join' => $join);

		return $results;
	}
}
