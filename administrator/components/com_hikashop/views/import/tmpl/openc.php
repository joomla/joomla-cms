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

echo JText::sprintf('X_IMPORT_DESC','Opencart','Opencart').' :<br/>';
$functions = array('TAXATIONS','HIKA_CATEGORIES','PRODUCTS','PRICES','USERS','ORDERS','DOWNLOADS','FILES','HIKA_IMAGES','DISCOUNTS','COUPONS');
foreach($functions as $k => $v){
	echo '<br/>  - '.JText::_($v);
	if ($v == 'HIKA_IMAGES')
		echo ' ( '.JText::_('OPENC_WARNING_IMAGES').' )';
}
echo '<p style="color:red"><br/>'.JText::_('OPENC_WARNING').'</p>';
?>
<table class="admintable table" cellspacing="1">
<tr>
	<td class="key" >
		<?php echo JText::_('OPENC_DATABASE_NAME'); ?>
	</td>
	<td>
		<input type="text" name="opencDbName" class="inputbox" />
	</td>
</tr>
<tr>
	<td class="key" >
		<?php echo JText::_('OPENC_TABLE_PREFIX'); ?>
	</td>
	<td>
		<input type="text" name="opencPrefix" class="inputbox" value="oc_" />
	</td>
</tr>
<tr>
	<td class="key" >
		<?php echo JText::_('OPENC_WEBSITE_PATH'); ?>
	</td>
	<td>
		<input type="text" name="opencRootPath" class="inputbox" />
	</td>
</tr>
<tr>
	<td class="key" >
		<?php echo JText::_('OPENC_IMPORT_CURRENCIES'); ?>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', 'import_currencies','',JRequest::getInt('import_currencies','0')); ?>
	</td>
</tr>
</table>

<?php
