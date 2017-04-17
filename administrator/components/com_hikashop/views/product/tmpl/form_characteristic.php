<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><table id="hikashop_product_characteristics_table" class="adminlist table table-striped" style="width:100%">
	<thead>
		<tr>
			<th></th>
			<th class="title"><?php
				echo JText::_('HIKA_NAME');
			?></th>
			<th style="width:40px;text-align:center">
				<a href="#" onclick="return window.productMgr.newCharacteristic();"><img src="<?php echo HIKASHOP_IMAGES; ?>plus.png" alt="<?php echo JText::_('ADD'); ?>"></a>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr id="hikashop_characteristic_add_zone" style="display:none;">
			<td colspan="2">
				<dl>
					<dt><?php echo JText::_('HIKA_CHARACTERISTIC'); ?></dt>
					<dd><?php
	echo $this->nameboxVariantType->display(
		null,
		null,
		hikashopNameboxType::NAMEBOX_SINGLE,
		'characteristic',
		array(
			'id' => 'hikashop_characteristic_nb_add',
			'add' => true,
			'vendor' => @$this->vendor->vendor_id,
			'default_text' => 'PLEASE_SELECT'
		)
	);
					?></dd>
					<dt><?php echo JText::_('DEFAULT_VALUE'); ?></dt>
					<dd><?php
	echo $this->nameboxVariantType->display(
		null,
		null,
		hikashopNameboxType::NAMEBOX_SINGLE,
		'characteristic_value',
		array(
			'id' => 'hikashop_characteristic_nb_def',
			'add' => true,
			'displayFormat' => '{characteristic_value} - {characteristic_alias}',
			'vendor' => @$this->vendor->vendor_id,
			'url_params' => array('ID' => -1),
			'default_text' => 'PLEASE_SELECT'
		)
	);
					?></dd>
				</dl>
				<div style="float:right">
					<button onclick="return window.productMgr.addCharacteristic();" class="btn btn-success"><img src="<?php echo HIKASHOP_IMAGES; ?>save.png" alt="" style="vertical-align:middle;"/> <?php echo JText::_('HIKA_SAVE'); ;?></button>
				</div>
				<button onclick="return window.productMgr.cancelNewCharacteristic();" class="btn btn-danger"><img src="<?php echo HIKASHOP_IMAGES; ?>cancel.png" alt="" style="vertical-align:middle;"/> <?php echo JText::_('HIKA_CANCEL'); ;?></button>
				<div style="clear:both"></div>
			</td>
		</tr>
	</tfoot>
	<tbody>
<?php
$k = 0;
$current_characteristics = array();
if(empty($this->product->characteristics))
	$this->product->characteristics = array();
foreach($this->product->characteristics as $characteristic) {
	if((int)$characteristic->characteristic_parent_id > 0)
		continue;

	$current_characteristics[] = (int)$characteristic->characteristic_id;
?>
		<tr class="row<?php echo $k ?>">
			<td class="column_move"><img src="<?php echo HIKASHOP_IMAGES; ?>move.png"/></td>
			<td><?php
				echo $characteristic->characteristic_value;
				if(!empty($characteristic->characteristic_alias))
					echo ' - ' . $characteristic->characteristic_alias;
			?></td>
			<td style="text-align:center">
				<a href="#delete" onclick="return window.productMgr.deleteCharacteristic(this, <?php echo (int)$characteristic->characteristic_id; ?>); return false;"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="<?php echo JText::_('HIKA_DELETE'); ?>"></a>
				<input type="hidden" name="data[characteristics][]" value="<?php echo (int)$characteristic->characteristic_id; ?>"/>
			</td>
		</tr>
<?php
	$k = 1 - $k;
}
?>
		<tr id="hikashop_characteristic_row_template" class="row<?php echo $k ?>" style="display:none;">
			<td class="column_move"><img src="<?php echo HIKASHOP_IMAGES; ?>move.png"/></td>
			<td>{NAME}</td>
			<td style="text-align:center">
				<a href="#delete" onclick="return window.productMgr.deleteCharacteristic(this, {ID}); return false;"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="<?php echo JText::_('HIKA_DELETE'); ?>"></a>
				<input type="hidden" name="{INPUT_NAME}" value="{ID}"/>
				<input type="hidden" name="{INPUT_NAME_2}" value="{ID_2}"/>
			</td>
		</tr>
	</tbody>
</table>
<script type="text/javascript">
hkjQuery("#hikashop_product_characteristics_table tbody").sortable({
	axis: "y", cursor: "move", opacity: 0.8,
	helper: function(e, ui) {
		ui.children().each(function() {
			hkjQuery(this).width(hkjQuery(this).width());
		});
		return ui;
	},
	stop: function(event, ui) {
		window.hikashop.cleanTableRows('hikashop_product_characteristics_table');
	}
});

window.productMgr.current_characteristics = [<?php echo implode(',', $current_characteristics); ?>];
window.productMgr.addCharacteristic = function() {
	var w = window, d = document, o = w.Oby, c = null, cv = null, ct = null,
		el = d.getElementById('hikashop_characteristic_nb_add_valuehidden');

	if(el) {
		c = parseInt(el.value);
		el = d.getElementById('hikashop_characteristic_nb_add_valuetext');
		if(el)
			ct = el.innerHTML;
	}
	if(isNaN(c) || c === 0) c = null;

	el = d.getElementById('hikashop_characteristic_nb_def_valuehidden')
	if(el) cv = parseInt(el.value);
	if(isNaN(cv) || cv === 0) cv = null;

	if(c <= 0  || c === null || cv <= 0 || cv === null)
		return false;

<?php
	if((int)$this->product->product_id > 0) {
?>
	var url = '<?php echo hikashop_completeLink('product&task=characteristic&subtask=add&product_id='.(int)$this->product->product_id,true,false,true); ?>',
		formData = encodeURI('characteristic_id') + '=' + encodeURIComponent(c) + '&' + encodeURI('characteristic_value_id') + '=' + encodeURIComponent(cv) + '&' + encodeURI('<?php echo hikashop_getFormToken(); ?>') + '=1';
	o.xRequest(url, {mode:'POST',data:formData}, function(x,p) {
		var el = d.getElementById('hikashop_product_edition_header');
		if(el && el.style.display == 'none')
			el.style.display = '';
		if(window.productMgr.refreshVariantList)
			window.productMgr.refreshVariantList();
	});
<?php } ?>

	var htmlblocks = {
		NAME: ct, ID: c, INPUT_NAME: 'data[characteristics][]',
<?php if((int)$this->product->product_id > 0) { ?>
		INPUT_NAME_2: '', ID_2: ''
<?php } else { ?>
		INPUT_NAME_2: 'data[characteristics][]', ID_2: cv
<?php } ?>
	};
	w.hikashop.dupRow('hikashop_characteristic_row_template', htmlblocks);
	w.productMgr.cancelNewCharacteristic();

	w.productMgr.current_characteristics[w.productMgr.current_characteristics.length] = c;

	return false;
};
window.productMgr.deleteCharacteristic = function(el, id) {
	var w = window, d = document, o = w.Oby;
	if(!confirm('<?php echo str_replace('\'', '\\\'', JText::_('PLEASE_CONFIRM_DELETION')); ?>'))
		return false;

<?php
	if((int)$this->product->product_id > 0) {
?>
	var url = '<?php echo hikashop_completeLink('product&task=characteristic&subtask=remove&product_id='.(int)$this->product->product_id,true,false,true); ?>',
		formData = encodeURI('characteristic_id') + '=' + encodeURIComponent(id) + '&' + encodeURI('<?php echo hikashop_getFormToken(); ?>') + '=1';
	o.xRequest(url, {mode:'POST',data:formData}, function(x,p) {
		if(x.responseText == '0') {
			var el = d.getElementById('hikashop_product_edition_header');
			if(el && el.style.display == '')
				el.style.display = 'none';
		}
		if(w.productMgr.refreshVariantList)
			w.productMgr.refreshVariantList();
	});
<?php } ?>

	if(w.oNameboxes['hikashop_characteristic_nb_add'] && w.oNameboxes['hikashop_characteristic_nb_add'].content)
		w.oNameboxes['hikashop_characteristic_nb_add'].content.unblock(id);
	for(var i = w.productMgr.current_characteristics.length - 1; i >= 0; i--) {
		if(w.productMgr.current_characteristics[i] && w.productMgr.current_characteristics[i] == id) {
			delete w.productMgr.current_characteristics[i];
			break;
		}
	}

	w.hikashop.deleteRow(el);
	return false;
};
window.productMgr.newCharacteristic = function() {
	var w = window, d = document;
	w.oNameboxes['hikashop_characteristic_nb_add'].clear();
	w.oNameboxes['hikashop_characteristic_nb_add'].content.config.hideBlocked = true;
	w.oNameboxes['hikashop_characteristic_nb_add'].content.block(w.productMgr.current_characteristics);
	var el = d.getElementById('hikashop_characteristic_add_zone');
	if(el) el.style.display = '';
	return false;
};
window.productMgr.cancelNewCharacteristic = function() {
	var w = window, d = document, o = w.Oby;
	var el = d.getElementById('hikashop_characteristic_add_zone');
	if(el) el.style.display = 'none';
	el = d.getElementById('hikashop_characteristic_add_list');
	if(el) setTimeout(function() { el.innerHTML = ''; }, 10);
	return false;
};
window.Oby.ready(function() {
	var w = window, ona = 'hikashop_characteristic_nb_add', onv = 'hikashop_characteristic_nb_def',
		u = '<?php echo hikashop_completeLink('characteristic&task=findList&characteristic_type=value&characteristic_parent_id={ID}', true, false, true); ?>',
		a = '<?php echo hikashop_completeLink('characteristic&task=add&characteristic_type=value&characteristic_parent_id={ID}&tmpl=json', true, false, true); ?>';
	if(!w.oNameboxes[ona] || !w.oNameboxes[onv])
		return;
	w.oNameboxes[ona].register('set', function(e) {
		if(e.value) {
			w.oNameboxes[onv].changeUrl(u.replace('{ID}', e.value), {add: a.replace('{ID}', e.value)});
		} else {
			w.oNameboxes[onv].loadData(null);
			w.oNameboxes[onv].clear();
		}
	});
});
</script>
