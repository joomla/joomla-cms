<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	Templates
 */
class JHtmlTemplates
{
	/**
	 * Display the thumb for the template.
	 *
	 * @param	string	The name of the active view.
	 */
	public static function thumb($template, $clientId = 0)
	{
		$client		= JApplicationHelper::getClientInfo($clientId);
		$basePath	= $client->path.'/templates/'.$template;
		$baseUrl	= ($clientId == 0) ? JUri::root(true) : JUri::root(true).'/administrator';
		$thumb		= $basePath.'/template_thumbnail.png';
		$preview	= $basePath.'/template_preview.png';
		$html		= '';

		if (file_exists($thumb))
		{
			$thumb	= $baseUrl.'/templates/'.$template.'/template_thumbnail.png';
			$html	= '<img src="'.$thumb.'" alt="'.JText::_('Templates_Preview').'" />';
			if (file_exists($preview))
			{
				$preview	= $baseUrl.'/templates/'.$template.'/template_preview.png';
				$html		= '<a href="'.$preview.'" class="modal" title="'.JText::_('Templates_Click_to_enlarge').'">'.$html.'</a>';
			}
		}

		return $html;
	}
}