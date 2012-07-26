<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Weblinks Component Category Tree
 *
 * @static
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 * @since       1.6
 */
class WeblinksCategories extends JCategories
{
	public function __construct($options = array())
	{
		$options['table'] = '#__weblinks';
		$options['extension'] = 'com_weblinks';
		parent::__construct($options);
	}
}
