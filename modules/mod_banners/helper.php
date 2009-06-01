<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_banners
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_banners'.DS.'helpers'.DS.'banner.php');

class modBannersHelper
{
	function getList(&$params)
	{
		$model		= modBannersHelper::getModel();

		// Model Variables
		$vars['cid']		= (int) $params->get('cid');
		$vars['catid']		= (int) $params->get('catid');
		$vars['limit']		= (int) $params->get('count', 1);
		$vars['ordering']	= $params->get('ordering');

		if ($params->get('tag_search'))
		{
			$document		= &JFactory::getDocument();
			$keywords		=  $document->getMetaData('keywords');

			$vars['tag_search'] = BannerHelper::getKeywords($keywords);
		}

		$banners = $model->getList($vars);
		$model->impress($banners);

		return $banners;
	}

	function getModel()
	{
		if (!class_exists('BannersModelBanner'))
		{
			// Build the path to the model based upon a supplied base path
			$path = JPATH_SITE.DS.'components'.DS.'com_banners'.DS.'models'.DS.'banner.php';
			$false = false;

			// If the model file exists include it and try to instantiate the object
			if (file_exists($path)) {
				require_once($path);
				if (!class_exists('BannersModelBanner')) {
					JError::raiseWarning(0, 'Model class BannersModelBanner not found in file.');
					return $false;
				}
			} else {
				JError::raiseWarning(0, 'Model BannersModelBanner not supported. File not found.');
				return $false;
			}
		}

		$model = new BannersModelBanner();
		return $model;
	}

	function renderBanner($params, &$item)
	{
		$link = JRoute::_('index.php?option=com_banners&task=click&bid='. $item->bid);
		$baseurl = JURI::base();

		$html = '';
		if (trim($item->custombannercode))
		{
			// template replacements
			$html = str_replace('{CLICKURL}', $link, $item->custombannercode);
			$html = str_replace('{NAME}', $item->name, $html);
		}
		else if (BannerHelper::isImage($item->imageurl))
		{
			$image 	= '<img src="'.$baseurl.'images/banners/'.$item->imageurl.'" alt="'.JText::_('Banner').'" />';
			if ($item->clickurl)
			{
				switch ($params->get('target', 1))
				{
					// cases are slightly different
					case 1:
						// open in a new window
						$a = '<a href="'. $link .'" target="_blank">';
						break;

					case 2:
						// open in a popup window
						$a = "<a href=\"javascript:void window.open('". $link ."', '', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550'); return false\">";
						break;

					default:	// formerly case 2
						// open in parent window
						$a = '<a href="'. $link .'">';
						break;
					}

				$html = $a . $image . '</a>';
			}
			else
			{
				$html = $image;
			}
		}
		else if (BannerHelper::isFlash($item->imageurl))
		{
			//echo $item->params;
			$banner_params = new JParameter($item->params);
			$width = $banner_params->get('width');
			$height = $banner_params->get('height');

			$imageurl = $baseurl."images/banners/".$item->imageurl;
			$html =	"<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0\" border=\"0\" width=\"$width\" height=\"$height\">
						<param name=\"movie\" value=\"$imageurl\"><embed src=\"$imageurl\" loop=\"false\" pluginspage=\"http://www.macromedia.com/go/get/flashplayer\" type=\"application/x-shockwave-flash\" width=\"$width\" height=\"$height\"></embed>
					</object>";
		}

		return $html;
	}
}
