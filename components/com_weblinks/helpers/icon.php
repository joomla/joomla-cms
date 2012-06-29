<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.helper');

/**
 * Weblink Component HTML Helper
 *
 * @static
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @since 1.5
 */
class JHtmlIcon
{
	static function create($weblink, $params)
	{
		$uri = JFactory::getURI();

		$url = JRoute::_(WeblinksHelperRoute::getFormRoute(0, base64_encode($uri)));
		$text = JHtml::_('image', 'system/new.png', JText::_('JNEW'), null, true);
		$button = JHtml::_('link', $url, $text);
		$output = '<span class="hasTip" title="'.JText::_('COM_WEBLINKS_FORM_CREATE_WEBLINK').'">'.$button.'</span>';
		return $output;
	}

	static function edit($weblink, $params, $attribs = array())
	{
		$user = JFactory::getUser();
		$uri = JFactory::getURI();

		if ($params && $params->get('popup')) {
			return;
		}

		if ($weblink->state < 0) {
			return;
		}

		JHtml::_('behavior.tooltip');
		$url	= WeblinksHelperRoute::getFormRoute($weblink->id, base64_encode($uri));
		$icon	= $weblink->state ? 'edit.png' : 'edit_unpublished.png';
		$text	= JHtml::_('image', 'system/'.$icon, JText::_('JGLOBAL_EDIT'), null, true);

		if ($weblink->state == 0) {
			$overlib = JText::_('JUNPUBLISHED');
		}
		else {
			$overlib = JText::_('JPUBLISHED');
		}

		$date = JHtml::_('date', $weblink->created);
		$author = $weblink->created_by_alias ? $weblink->created_by_alias : $weblink->author;

		$overlib .= '&lt;br /&gt;';
		$overlib .= $date;
		$overlib .= '&lt;br /&gt;';
		$overlib .= htmlspecialchars($author, ENT_COMPAT, 'UTF-8');

		$button = JHtml::_('link', JRoute::_($url), $text);

		$output = '<span class="hasTip" title="'.JText::_('COM_WEBLINKS_EDIT').' :: '.$overlib.'">'.$button.'</span>';

		return $output;
	}
}
