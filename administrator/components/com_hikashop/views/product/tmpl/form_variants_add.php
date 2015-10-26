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
if($this->subtask != 'duplicate') {
	$populateMode = 'add';
?>
<dl>
<?php foreach($this->characteristics as $characteristic) { ?>
	<dt><?php echo $characteristic->characteristic_value; ?></dt>
	<dd><?php
		echo $this->nameboxVariantType->display(
			'data[variant_add][' . $characteristic->characteristic_id . '][]',
			null,
			hikashopNameboxType::NAMEBOX_MULTIPLE,
			'characteristic_value',
			array(
				'add' => true,
				'url_params' => array('ID' => $characteristic->characteristic_id)
			)
		);
	?></dd>
<?php } ?>
</dl>
<?php
} else {
	$populateMode = 'duplicate';
?>
<div>
	<select style="width:30%" name="data[variant_duplicate][characteristic]" onchange="window.productMgr.duplicateChangeCharacteristic(this);">
<?php foreach($this->characteristics as $characteristic) { ?>
		<option value="<?php echo $characteristic->characteristic_id; ?>"><?php echo $characteristic->characteristic_value; ?></option>
<?php } ?>
	</select>
	<div style="display:inline-block;width:68%;">
<?php
	if(empty($this->productClass))
		$this->productClass = hikashop_get('class.product');
	$c = reset($this->characteristics);
	echo $this->nameboxVariantType->display(
		'data[variant_duplicate][variants][]',
		null,
		hikashopNameboxType::NAMEBOX_MULTIPLE,
		'characteristic_value',
		array(
			'add' => true,
			'url_params' => array('ID' => $c->characteristic_id)
		)
	);
?>
	</div>
</div>
<script type="text/javascript">
window.productMgr.duplicateChangeCharacteristic = function(el) {
	var w = window, d = document,
		u = '<?php echo hikashop_completeLink('characteristic&task=findList&characteristic_type=value&characteristic_parent_id={ID}', true, false, true); ?>',
		a = '<?php echo hikashop_completeLink('characteristic&task=add&characteristic_type=value&characteristic_parent_id={ID}&tmpl=json', true, false, true); ?>';
	var n = w.oNameboxes['data_variant_duplicate_variants'];
	if(!n) return true;
	n.changeUrl(u.replace('{ID}', el.value), {add: a.replace('{ID}', el.value)});
	return true;
};
</script>
<?php } ?>
<div style="clear:both"></div>
<div style="float:right">
	<button onclick="return window.productMgr.populateVariants('<?php echo $populateMode; ?>');" class="btn btn-success"><img src="<?php echo HIKASHOP_IMAGES; ?>save.png" alt="" style="vertical-align:middle;"/> <?php echo JText::_('HIKA_SAVE'); ;?></button>
</div>
<button onclick="return window.productMgr.cancelPopulateVariants();" class="btn btn-danger"><img src="<?php echo HIKASHOP_IMAGES; ?>cancel.png" alt="" style="vertical-align:middle;"/> <?php echo JText::_('HIKA_CANCEL'); ;?></button>
<div style="clear:both"></div>
