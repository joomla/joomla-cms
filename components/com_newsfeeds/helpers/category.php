<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Component Helper
jimport('joomla.application.component.helper');
jimport('joomla.application.categories');

/**
 * Content Component Category Tree
 *
 * @static
 * @package		Joomla
 * @subpackage	Com_newsfeeds
 * @since 1.6
 */
class NewsfeedsCategories extends JCategories
{
	public function __construct($options = array())
	{
		$options['table'] = '#__newsfeeds';
		$options['extension'] = 'com_newsfeeds';
		$options['statefield'] = 'published';
		parent::__construct($options);
	}
}