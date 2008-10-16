<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
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
