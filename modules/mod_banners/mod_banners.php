<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.controller' );

class JModBannerController extends JController
{
	var $params;

	function display()
	{
		$model		= &$this->getModel( 'banner', 'JModel' );
		$params		= &$this->params;
		$database	= &JFactory::getDBO();

		// Model Variables
		$vars['cid']		= (int) $params->get( 'cid' );
		$vars['catid']		= (int) $params->get( 'catid' );
		$vars['limit']		= (int) $params->get( 'count', 1 );
		$vars['randomise']	= (int) $params->get( 'randomise' );

		$banners = $model->getList( $vars );
		$model->impress( $banners );

		// View Variables
		$cssSuffix	= $params->get( 'moduleclass_sfx' );
		$headerText	= trim( $params->get( 'header_text' ) );
		$footerText	= trim( $params->get( 'footer_text' ) );

		echo '<div class="bannergroup' . $cssSuffix . '">';
		if ($footerText)
		{
			echo '<div class="bannerheader">' . $headerText . '</div>';
		}
		
		$n = count( $banners );
		for ($i = 0; $i < $n; $i++) {
			$item = &$banners[$i];
			$link = sefRelToAbs( 'index.php?option=com_banners&amp;task=click&amp;bid='. $item->bid );
		
			echo '<div class="banneritem' . $cssSuffix . '">';

			if (trim($item->custombannercode))
			{
				// template replacements
				$html = str_replace( '{CLICKURL}', $link, $item->custombannercode );
				$html = str_replace( '{NAME}', $item->name, $html );
				echo $html;
			}
			else if ($model->isImage( $item->imageurl ))
			{
				$imageurl 	= 'images/banners/'.$item->imageurl;
				echo '<a href="'.$link.'" target="_blank"><img src="'.$imageurl.'" border="0" alt="'.JText::_('Banner').'" /></a>';
			}
			else if ($model->isFlash( $item->imageurl ))
			{
				$imageurl = "images/banners/".$item->imageurl;
				echo "	<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0\" border=\"5\">
							<param name=\"movie\" value=\"$imageurl\"><embed src=\"$imageurl\" loop=\"false\" pluginspage=\"http://www.macromedia.com/go/get/flashplayer\" type=\"application/x-shockwave-flash\"></embed>
						</object>";
			}
		
			echo '	<div class="clr"></div>';
			echo '</div>';
		}
		if ($footerText)
		{
			echo '<div class="bannerfooter' . $cssSuffix . '">' . $footerText . '</div>';
		}
		echo '</div>';
	}
}

$controller = new JModBannerController( $mainframe );
$controller->setModelPath( JPATH_SITE . '/components/com_banners/models' );
$controller->params = &$params;
$controller->execute( 'display' );


?>