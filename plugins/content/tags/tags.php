<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.contact
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Tag Plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Content.contact
 * @since       1.5
 */
class PlgContentTags extends JPlugin
{
	/**
	 * Plugin that retrieves contact for content
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   mixed    &$row     An object with a "text" property
	 * @param   mixed    &$params  Additional parameters. See {@see PlgContentTags()}.
	 * @param   integer  $page     Optional page number. Unused. Defaults to zero.
	 *
	 * @return  boolean	True on success.
	 */
	public function onContentPrepare($context, &$row, $params, $page = 0)
	{
        if ($context == 'com_content.category') {
            $context = 'com_content.article';
        }

        if (isset($row->id)) {
        } else {
            return $row;
        }

		if (is_object($row))
		{
            $row->contact = new JHelperTags;
            $row->contact->getItemTags($context, $row->id);
		}

		return $row;
	}
}
