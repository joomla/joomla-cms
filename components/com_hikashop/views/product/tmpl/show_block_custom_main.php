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
$this->fieldsClass->prefix = '';
$displayTitle = false;
ob_start();
foreach ($this->fields as $fieldName => $oneExtraField) {
	$value = '';
	if(empty($this->element->$fieldName) && !empty($this->element->main->$fieldName))
		$this->element->$fieldName = $this->element->main->$fieldName;
	if(isset($this->element->$fieldName))
		$value = trim($this->element->$fieldName);

	if(!empty($value) || $value === '0') {
		$displayTitle = true;
	?>
		<tr class="hikashop_product_custom_<?php echo $oneExtraField->field_namekey;?>_line">
			<td class="key">
				<span id="hikashop_product_custom_name_<?php echo $oneExtraField->field_id;?>" class="hikashop_product_custom_name">
					<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
				</span>
			</td>
			<td>
				<span id="hikashop_product_custom_value_<?php echo $oneExtraField->field_id;?>" class="hikashop_product_custom_value">
					<?php echo $this->fieldsClass->show($oneExtraField,$value); ?>
				</span>
			</td>
		</tr>
	<?php
	}
}
$specifFields = ob_get_clean();
if($displayTitle){
?>

<div id="hikashop_product_custom_info_main" class="hikashop_product_custom_info_main">
	<h4><?php echo JText::_('SPECIFICATIONS');?></h4>
	<table width="100%">
		<?php echo $specifFields; ?>
	</table>
</div>
<?php }
