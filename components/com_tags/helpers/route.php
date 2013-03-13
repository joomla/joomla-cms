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
	 * A method to get the route for a specific item
	 *
	 * @param  integer  $id         Value of the primary key for the item in its content table
	 * @param  string   $typealias  The type_alias for the item being routed. Of the form extension.view.
	 * @param  string   $link       The link to be routed
	 * @param  string   $language   The language of the content for multilingual sites
	 * @param  integer  $catid      Optional category id
	 *
	 * @return  string  The route of the item
	 *
	 * @since 3.1
	 */
	public function getRoute($id, $typealias = 'com_tags.tag', $link = '', $language = null, $category = null)
	{
		//Create the link
		$link = 'index.php?option=com_tags&view=tag&id='. $id;

		return parent::getRoute($id, 'com_tags.tag', $link, $language = null, $category = null);
	}
}
