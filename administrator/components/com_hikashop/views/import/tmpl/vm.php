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
echo JText::sprintf('X_IMPORT_DESC','Virtuemart','Virtuemart').' :<br/>';
$functions = array('TAXATIONS','HIKA_CATEGORIES','PRODUCTS','PRICES','USERS','ORDERS','DOWNLOADS','FILES','HIKA_IMAGES','DISCOUNTS','COUPONS');
foreach($functions as $k => $v){
	echo '<br/>  - '.JText::_($v);
}
if ($this->vmversion==2)
{
?>
<p><br/><?php echo JText::_('VM_LANGUAGES'); ?></p>
<table class="admintable table" cellspacing="1">
<tr>
	<td class="key" >
		<?php echo JText::_('LANGUAGES'); ?>
	</td>
	<td>
		<?php
			$translate = hikashop_get('helper.translation');
			$languages = $translate->loadLanguages();
			$arraylang = array();
			foreach ($languages as $lang)
				array_push( $arraylang , JHTML::_('select.option', JText::_($lang->code), JText::_($lang->code)) );
			echo JHTML::_('select.genericlist', $arraylang, 'language', null, 'value', 'text', 'en-GB');
		?>
	</td>
</tr>
<?php
}
else
{
	echo '<table class="admintable table" cellspacing="1">';
}
?>
<tr>
	<td class="key" >
		<?php echo JText::_('VM_TABLE_PREFIX'); ?>
	</td>
	<td>
		<input type="text" name="vmPrefix" class="inputbox" />
	</td>
</tr>
</table>
