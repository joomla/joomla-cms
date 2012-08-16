<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
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
			$clientPath = ($clientId == 0) ? '' : 'administrator/';
			$thumb	= $clientPath.'templates/'.$template.'/template_thumbnail.png';
			$html	= JHtml::_('image', $thumb, JText::_('COM_TEMPLATES_PREVIEW'));
			if (file_exists($preview))
			{
				$preview	= $baseUrl.'/templates/'.$template.'/template_preview.png';
				$html		= '<a href="'.$preview.'" class="thumbnail modal" title="'.JText::_('COM_TEMPLATES_CLICK_TO_ENLARGE').'">'.$html.'</a>';
			}
		}

		return $html;
	}
}
