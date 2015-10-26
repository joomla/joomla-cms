<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
		if(!headers_sent()){
			header('Content-Type: text/css; charset=utf-8', true, 200);
		}
		$price = JRequest::getVar( 'price', 0 );
		$currency = hikashop_get('class.currency');
		echo '<span class="hikashop_option_price_title">'.JText::_('PRICE_WITH_OPTIONS').':</span> <span class="hikashop_option_price_value">'.$currency->format($price, hikashop_getCurrency()).'</span>';
		exit;
