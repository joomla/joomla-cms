<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Tags Component Route Helper
 *
 * @static
 * @package     Joomla.Site
 * @subpackage  com_tags
 * @since       3.1
 */
class TagsHelperRoute extends JHelperRoute
{

	/**
	 * @paramn  integer   The route of the tag
	 *
	 * @since  3.1
	 */
	public function getRoute($id, $typealias = 'com_tags.tag', $link = '', $language = null)
	{
		//Create the link
		$link = 'index.php?option=com_tags&view=tag&id='. $id;

		return parent::getRoute($id, $typealias, $link, $language = null);
	}

}
