<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Banners Component Category Tree
 *
 * @package     Joomla.Site
 * @subpackage  com_banners
 * @since       1.6
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
