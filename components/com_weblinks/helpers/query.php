<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Content Component Query Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class WeblinksHelperQuery
{
	function orderbyPrimary($orderby)
	{
		switch ($orderby)
		{
			case 'alpha' :
				$orderby = 'cc.title, ';
				break;

			case 'ralpha' :
				$orderby = 'cc.title DESC, ';
				break;

			case 'order' :
				$orderby = 'cc.ordering, ';
				break;

			default :
				$orderby = '';
				break;
		}

		return $orderby;
	}

	function orderbySecondary($orderby)
	{
		switch ($orderby)
		{
			case 'date' :
				$orderby = 'w.date';
				break;

			case 'rdate' :
				$orderby = 'w.date DESC';
				break;

			case 'alpha' :
				$orderby = 'w.title';
				break;

			case 'ralpha' :
				$orderby = 'w.title DESC';
				break;

			case 'hits' :
				$orderby = 'w.hits DESC';
				break;

			case 'rhits' :
				$orderby = 'w.hits';
				break;

			case 'order' :
				$orderby = 'w.ordering';
				break;

			default :
				$orderby = 'w.ordering';
				break;
		}

		return $orderby;
	}
}
