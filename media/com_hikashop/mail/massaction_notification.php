<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div style="background-color: #ffffff; font-family: Verdana, Arial, Helvetica, sans-serif;font-size:12px; color: #000000; width: 100%;">
	{TXT:EMAIL_BODY}
<!--{IF:product_listing}-->
	<table class="w550" border="0" cellspacing="0" cellpadding="0" width="550" style="margin-top:10px;margin-bottom:10px;font-family: Arial, Helvetica, sans-serif;font-size:12px;line-height:18px;">
		<tr>
			<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:left;color:#1c8faf !important;font-size:12px;font-weight:bold;">{TXT:PRODUCT_NAME}</td>
			<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:right;color:#1c8faf !important;font-size:12px;font-weight:bold;">{TXT:PRODUCT_PRICE}</td>
			<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:right;color:#1c8faf !important;font-size:12px;font-weight:bold;">{TXT:PRODUCT_QUANTITY}</td>
		</tr>
	<!--{START:PRODUCT_LINE}-->
		<tr>
			<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:right">{LINEVAR:PRODUCT_NAME}</td>
			<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:right">{LINEVAR:PRODUCT_PRICE}</td>
			<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:right">{LINEVAR:PRODUCT_QUANTITY}</td>
		</tr>
	<!--{END:PRODUCT_LINE}-->
	</table>
<!--{ENDIF:product_listing}-->
</div>
