<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.categories');

/**
 * Content Component Category Tree
 *
 * @static
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since 1.6
 */
class ContentCategories extends JCategories
{
	public function __construct($options = array())
	{
		$options['table'] = '#__content';
		$options['extension'] = 'com_content';
		parent::__construct($options);
	}
}
