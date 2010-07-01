<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Editor Image buton
 *
 * @package Editors-xtd
 * @since 1.5
 */
class plgButtonImage extends JPlugin
{
	/**
	 * Display the button
	 *
	 * @return array A two element array of (imageName, textToInsert)
	 */
	function onDisplay($name)
	{
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_media');
		$ranks = array('publisher', 'editor', 'author', 'registered');
		$acl = JFactory::getACL();

		// TODO: Fix this ACL call
		//for($i = 0; $i < $params->get('allowed_media_usergroup', 3); $i++)
		//{
		//	$acl->addACL('com_media', 'popup', 'users', $ranks[$i]);
		//}


		// TODO: Fix this ACL call
		//Make sure the user is authorized to view this page
		$user = JFactory::getUser();
		if (!$user->authorise('com_media.popup')) {
			//return;
		}
		$doc		= JFactory::getDocument();
		$template	= $app->getTemplate();

		$link = 'index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;e_name='.$name;

		JHtml::_('behavior.modal');

		$button = new JObject;
		$button->set('modal', true);
		$button->set('link', $link);
		$button->set('text', JText::_('PLG_IMAGE_BUTTON_IMAGE'));
		$button->set('name', 'image');
		$button->set('options', "{handler: 'iframe', size: {x: 800, y: 500}}");

		return $button;
	}
}
