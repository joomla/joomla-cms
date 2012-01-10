<?php
/**
 * @version		$Id: category.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Site
 * @subpackage	com_banners
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Component Helper
jimport('joomla.application.component.helper');
jimport('joomla.application.categories');

/**
 * Banners Component Category Tree
 *
 * @static
 * @package		Joomla.Site
 * @subpackage	com_banners
 * @since 1.6
 */
class BannersCategories extends JCategories
{
	public function __construct($options = array())
	{
		$options['table'] = '#__banners';
		$options['extension'] = 'com_banners';
		parent::__construct($options);
	}
}
