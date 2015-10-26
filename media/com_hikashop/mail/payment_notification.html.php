<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><style type="text/css">
body.hikashop_mail { background-color:#ffffff; color:#575757; }
.ReadMsgBody{width:100%;}
.ExternalClass{width:100%;}
div, p, a, li, td {-webkit-text-size-adjust:none;}
@media (min-width:600px){
	#hikashop_mail {width:600px !important;margin:auto !important;}
	.pict img {max-width:500px !important;height:auto !important;}
}
@media (max-width:330px){
	#hikashop_mail{width:300px !important; margin:auto !important;}
	table[class=w600], td[class=w600], table[class=w598], td[class=w598], table[class=w500], td[class=w500], img[class="w600"]{width:100% !important;}
	td[class="w49"] { width: 10px !important;}
	.pict img {max-width:278px; height:auto !important;}
}
@media (min-width:331px) and (max-width:480px){
	#hikashop_mail{width:450px !important; margin:auto !important;}
	table[class=w600], td[class=w600], table[class=w598], td[class=w598], table[class=w500], td[class=w500], img[class="w600"]{width:100% !important;}
	td[class="w49"] { width: 20px !important;}
	.pict img {max-width:408px;  height:auto !important;}
}
h1{color:#1c8faf;font-size:16px;font-weight:bold;border-bottom:1px solid #ddd; padding-bottom:10px;}
h2{color:#89a9c1;font-size:14px;font-weight:bold;margin-top:20px;margin-bottom:5px;border-bottom:1px solid #d6d6d6;padding-bottom:4px;}
a:visited{cursor:pointer;color:#2d9cbb;text-decoration:none;border:none;}
</style>

<div id="hikashop_mail" style="font-family:Arial, Helvetica,sans-serif;font-size:12px;line-height:18px;width:100%;background-color:#ffffff;padding-bottom:20px;color:#5b5b5b;">
	<!--{IF:USER_ACCOUNT}-->
	<div class="hikashop_online" style="font-family:Arial, Helvetica,sans-serif;font-size:11px;line-height:18px;color:#6a5c6b;text-decoration:none;margin:10px;text-align:center;">
		<a style="cursor:pointer;color:#2d9cbb;text-decoration:none;border:none;" href="{VAR:URL}">
			<span class="hikashop_online" style="color:#6a5c6b;text-decoration:none;font-size:11px;margin-top:10px;margin-bottom:10px;text-align:center;">
				{TXT:MAIL_HEADER}
			</span>
		</a>
	</div>
	<!--{ENDIF:USER_ACCOUNT}-->
	<table class="w600" style="font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px;margin:auto;background-color:#ebebeb;" border="0" cellspacing="0" cellpadding="0" width="600" align="center">
		<tr style="line-height: 0px;">
			<td class="w600" style="line-height:0px" width="600" valign="bottom">
				<img class="w600" src="{VAR:LIVE_SITE}/media/com_hikashop/images/mail/header_black.png" border="0" alt="" />
			</td>
		</tr>
		<tr>
			<td class="w600" style="" width="600" align="center">
				<table class="w600" border="0" cellspacing="0" cellpadding="0" width="600" style="margin:0px;font-family: Arial, Helvetica, sans-serif;font-size:12px;line-height:18px;">
					<tr>
						<td class="w20" width="20"></td>
						<td class="w560 pict" style="text-align:left; color:#575757" width="560">
							<div id="title" style="font-family: Arial, Helvetica, sans-serif;font-size:12px;line-height:18px;">

<img src="{VAR:LIVE_SITE}/media/com_hikashop/images/icons/icon-48-order.png" border="0" alt="" style="float:left;margin-right:4px;"/>
<h1 style="color:#1c8faf !important;font-size:16px;font-weight:bold; border-bottom:1px solid #ddd; padding-bottom:10px">
	{TXT:ORDER_TITLE}
</h1>

<h2 style="color:#1c8faf !important;font-size:12px;font-weight:bold; padding-bottom:10px">
	{TXT:ORDER_CHANGED}
</h2>
							</div>
						</td>
						<td class="w20" width="20"></td>
					</tr>
					<tr>
						<td class="w20" width="20"></td>
						<td style="border:1px solid #adadad;background-color:#ffffff;">
							<div class="w550" width="550" id="content" style="font-family: Arial, Helvetica, sans-serif;font-size:12px;line-height:18px;margin-left:5px;margin-right:5px;">
<p>
	<h3 style="color:#393939 !important; font-size:14px; font-weight:normal; font-weight:bold;margin-bottom:0px;padding:0px;">{TXT:HI_CUSTOMER}</h3>
	{TXT:ORDER_BEGIN_MESSAGE}
</p>

<table class="w550" border="0" cellspacing="0" cellpadding="0" width="550" style="margin-top:10px;font-family: Arial, Helvetica, sans-serif;font-size:12px;line-height:18px;">
	<tr>
		<!--{IF:BILLING_ADDRESS}--><td style="color:#1c8faf !important;font-size:12px;font-weight:bold;">{TXT:BILLING_ADDRESS}</td><!--{ENDIF:BILLING_ADDRESS}-->
		<!--{IF:SHIPPING}--><!--{IF:SHIPPING_ADDRESS}--><td style="color:#1c8faf !important;font-size:12px;font-weight:bold;">{TXT:SHIPPING_ADDRESS}</td><!--{ENDIF:SHIPPING_ADDRESS}--><!--{ENDIF:SHIPPING}-->
	</tr>
	<tr>
		<!--{IF:BILLING_ADDRESS}--><td>{VAR:BILLING_ADDRESS}</td><!--{ENDIF:BILLING_ADDRESS}-->
		<!--{IF:SHIPPING}--><!--{IF:SHIPPING_ADDRESS}--><td>{VAR:SHIPPING_ADDRESS}</td><!--{ENDIF:SHIPPING_ADDRESS}--><!--{ENDIF:SHIPPING}-->
	</tr>
</table>

<h1 style="color:#1c8faf !important;font-size:16px;font-weight:bold;border-bottom:1px solid #ddd;padding-top:10px;padding-bottom:10px;">
	{TXT:SUMMARY_OF_YOUR_ORDER}
</h1>

<table class="w550" border="0" cellspacing="0" cellpadding="0" width="550" style="margin-top:10px;margin-bottom:10px;font-family: Arial, Helvetica, sans-serif;font-size:12px;line-height:18px;">
	<tr>
		<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:left;color:#1c8faf !important;font-size:12px;font-weight:bold;">{TXT:PRODUCT_NAME}</td>
		<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:right;color:#1c8faf !important;font-size:12px;font-weight:bold;">{TXT:PRODUCT_PRICE}</td>
		<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:right;color:#1c8faf !important;font-size:12px;font-weight:bold;">{TXT:PRODUCT_QUANTITY}</td>
		<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:right;color:#1c8faf !important;font-size:12px;font-weight:bold;">{TXT:PRODUCT_TOTAL}</td>
	</tr>
<!--{START:PRODUCT_LINE}-->
	<tr>
		<td style="border-bottom:1px solid #ddd;padding-bottom:3px;">
			{LINEVAR:PRODUCT_IMG}
			{LINEVAR:PRODUCT_NAME}
			{LINEVAR:PRODUCT_DOWNLOAD}
		</td>
		<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:right">{LINEVAR:PRODUCT_PRICE}</td>
		<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:right">{LINEVAR:PRODUCT_QUANTITY}</td>
		<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:right">{LINEVAR:PRODUCT_TOTAL}</td>
	</tr>
<!--{END:PRODUCT_LINE}-->
<!--{START:ORDER_FOOTER}-->
	<tr>
		<td colspan="3" style="text-align:right;color:#1c8faf !important;font-size:12px;font-weight:bold;">{LINEVAR:NAME}</td>
		<td style="text-align:right">{LINEVAR:VALUE}</td>
	</tr>
<!--{END:ORDER_FOOTER}-->
</table>
<!--{IF:PAYMENT}-->
<p>
	{TXT:PAYMENT_METHOD}: {VAR:PAYMENT}
</p>
<!--{ENDIF:PAYMENT}-->
<!--{IF:SHIPPING}-->
<p>
	{TXT:HIKASHOP_SHIPPING_METHOD}: {VAR:SHIPPING}
</p>
<!--{ENDIF:SHIPPING}-->
<!--{IF:ORDER_SUMMARY}-->
<h1 style="color:#1c8faf !important;font-size:16px;font-weight:bold;border-bottom:1px solid #ddd;padding-top:10px;padding-bottom:10px;">
	{TXT:ADDITIONAL_INFORMATION}
</h1>
<p style="border-bottom:1px solid #ddd;padding-bottom:10px;">
	{VAR:ORDER_SUMMARY}
</p>
<!--{ENDIF:ORDER_SUMMARY}-->
<p>
	{TXT:ORDER_END_MESSAGE}
</p>
							</div>
						</td>
						<td class="w20" width="20"></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr style="line-height: 0px;">
			<td class="w600" style="line-height:0px" width="600" valign="top">
				<img class="w600" src="{VAR:LIVE_SITE}/media/com_hikashop/images/mail/footer_black.png" border="0" alt="--" />
			</td>
		</tr>
	</table>
</div>
