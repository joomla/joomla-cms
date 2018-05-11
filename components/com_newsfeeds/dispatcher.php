<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

\JLoader::register('NewsfeedsHelperRoute', JPATH_COMPONENT . '/helpers/route.php');

use Joomla\CMS\Dispatcher\Dispatcher;

/**
 * Dispatcher class for com_newsfeeds
 *
 * @since  4.0.0
 */
class NewsfeedsDispatcher extends Dispatcher
{
	/**
	 * The extension namespace
	 *
	 * @var    string
	 *
	 * @since  4.0.0
	 */
	protected $namespace = 'Joomla\\Component\\Newsfeeds';
}
