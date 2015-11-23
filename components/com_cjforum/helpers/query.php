<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumHelperQuery
{
	public static function orderbyPrimary ($orderby)
	{
		switch ($orderby)
		{
			case 'alpha':
				$orderby = 'c.path, ';
				break;
			
			case 'ralpha':
				$orderby = 'c.path DESC, ';
				break;
			
			case 'order':
				$orderby = 'c.lft, ';
				break;
			
			default:
				$orderby = '';
				break;
		}
		
		return $orderby;
	}

	public static function orderbySecondary ($orderby, $orderDate = 'created')
	{
		$queryDate = self::getQueryDate($orderDate);
		
		switch ($orderby)
		{
			case 'date':
				$orderby = $queryDate;
				break;
			
			case 'rdate':
				$orderby = $queryDate . ' DESC ';
				break;
			
			case 'alpha':
				$orderby = 'a.title';
				break;
			
			case 'ralpha':
				$orderby = 'a.title DESC';
				break;
			
			case 'hits':
				$orderby = 'a.hits DESC';
				break;
			
			case 'rhits':
				$orderby = 'a.hits';
				break;
			
			case 'order':
				$orderby = 'a.ordering';
				break;
			
			case 'author':
				$orderby = 'author';
				break;
			
			case 'rauthor':
				$orderby = 'author DESC';
				break;
			
			case 'front':
				$orderby = 'a.featured DESC, fp.ordering';
				break;
			
			default:
				$orderby = 'a.ordering';
				break;
		}
		
		return $orderby;
	}

	public static function getQueryDate ($orderDate)
	{
		$db = JFactory::getDbo();
		
		switch ($orderDate)
		{
			case 'modified':
				$queryDate = ' CASE WHEN a.modified = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.modified END';
				break;
			
			// use created if publish_up is not set
			case 'published':
				$queryDate = ' CASE WHEN a.publish_up = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.publish_up END ';
				break;
			
			case 'created':
			default:
				$queryDate = ' a.created ';
				break;
		}
		
		return $queryDate;
	}

	public static function buildVotingQuery ($params = null)
	{
		if (! $params)
		{
			$params = JComponentHelper::getParams('com_cjforum');
		}
		
		$voting = $params->get('show_vote');
		
		if ($voting)
		{
			// calculate voting count
			$select = ' , ROUND(v.rating_sum / v.rating_count) AS rating, v.rating_count';
			$join = ' LEFT JOIN #__cjforum_rating AS v ON a.id = v.topic_id';
		}
		else
		{
			$select = '';
			$join = '';
		}
		
		$results = array(
				'select' => $select,
				'join' => $join
		);
		
		return $results;
	}

	public static function orderDownColumns (&$topics, $numColumns = 1)
	{
		$count = count($topics);
		
		// just return the same array if there is nothing to change
		if ($numColumns == 1 || ! is_array($topics) || $count <= $numColumns)
		{
			$return = $topics;
		}
		// we need to re-order the intro topics array
		else
		{
			// we need to preserve the original array keys
			$keys = array_keys($topics);
			
			$maxRows = ceil($count / $numColumns);
			$numCells = $maxRows * $numColumns;
			$numEmpty = $numCells - $count;
			$index = array();
			
			// calculate number of empty cells in the array
			
			// fill in all cells of the array
			// put -1 in empty cells so we can skip later
			
			for ($row = 1, $i = 1; $row <= $maxRows; $row ++)
			{
				for ($col = 1; $col <= $numColumns; $col ++)
				{
					if ($numEmpty > ($numCells - $i))
					{
						// put -1 in empty cells
						$index[$row][$col] = - 1;
					}
					else
					{
						// put in zero as placeholder
						$index[$row][$col] = 0;
					}
					$i ++;
				}
			}
			
			// layout the topics in column order, skipping empty cells
			$i = 0;
			for ($col = 1; ($col <= $numColumns) && ($i < $count); $col ++)
			{
				for ($row = 1; ($row <= $maxRows) && ($i < $count); $row ++)
				{
					if ($index[$row][$col] != - 1)
					{
						$index[$row][$col] = $keys[$i];
						$i ++;
					}
				}
			}
			
			// now read the $index back row by row to get topics in right
			// row/col
			// so that they will actually be ordered down the columns (when read
			// by row in the layout)
			$return = array();
			$i = 0;
			for ($row = 1; ($row <= $maxRows) && ($i < $count); $row ++)
			{
				for ($col = 1; ($col <= $numColumns) && ($i < $count); $col ++)
				{
					$return[$keys[$i]] = $topics[$index[$row][$col]];
					$i ++;
				}
			}
		}
		return $return;
	}
}
