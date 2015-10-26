<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_checkout_<?php echo $this->type; ?>_address_selection"<?php if(empty($this->addresses)) { echo ' style="display:none;"'; } ?>>
<?php
$config = hikashop_config();
$address_selector = (int)$config->get('checkout_address_selector', 0);
$other_type = ($this->type == 'billing') ? 'shipping' : 'billing';

$t = $this->type . '_address';
$current = $this->$t;

if($address_selector == 1) {
	if(!empty($this->addresses)) {
		foreach($this->addresses as $address) {
			$checked = '';
			if($address->address_id == $current)
				$checked = ' checked="checked"';
			if($config->get('auto_submit_methods', 1) && empty($checked))
				$checked = ' onclick="this.form.submit();return false;"';
?>
<div id="hikashop_address_<?php echo $this->type; ?>_selection_<?php echo $address->address_id; ?>" class="address_selection<?php echo ($address->address_id == $current) ? ' address_selected':''; ?>">
	<input id="hikashop_checkout_<?php echo $this->type;?>_address_radio_<?php echo $address->address_id;?>" class="checkout_<?php echo $this->type;?>_address_radio" type="radio" name="hikashop_address_<?php echo $this->type;?>" value="<?php echo $address->address_id;?>"<?php echo $checked; ?>/>
<?php
			$js = '';
			$params = new stdClass();
			$params->type = $this->type;
			$params->address_id = (int)$address->address_id;
			$params->fieldset_id = 'hikashop_checkout_'.$this->type.'_address_zone';
			echo hikashop_getLayout('address', 'show', $params, $js);
?>
</div>
<?php
		}
	}
?>
	<div id="hikashop_checkout_<?php echo $this->type; ?>_address_template" class="address_selection" style="display:none;">
		<input id="hikashop_checkout_<?php echo $this->type;?>_address_radio_{VALUE}" class="checkout_<?php echo $this->type;?>_address_radio" type="radio" name="hikashop_address_<?php echo $this->type;?>" value="{VALUE}"/>
		{CONTENT}
	</div>
	<div class="" style="margin-top:6px;">
		<a class="btn btn-success" href="#newAddress" onclick="return window.localPage.switchAddr(0, '<?php echo $this->type; ?>');"><?php echo JText::_('HIKA_NEW'); ?></a>
	</div>
<?php
}

if($address_selector == 2) {
	$values = array();
	if(!empty($this->addresses)) {
		$addressClass = hikashop_get('class.address');
		foreach($this->addresses as $k => $address) {
			$addr = $addressClass->miniFormat($address);
			$values[] = JHTML::_('select.option', $k, $addr);
		}
	}
	$values[] = JHTML::_('select.option', 0, JText::_('HIKASHOP_NEW_ADDRESS_ITEM'));
	echo JHTML::_('select.genericlist', $values, 'hikashop_address_'.$this->type, 'class="hikashop_field_dropdown" onchange="window.localPage.switchAddr(this, \''.$this->type.'\');"', 'value', 'text', $current, 'hikashop_checkout_address_'.$this->type.'_selector');
?><div id="hikashop_checkout_selected_<?php echo $this->type; ?>_address">
<?php
	if(isset($this->addresses[$current]))
		$address = $this->addresses[$current];
	else
		$address = reset($this->addresses);

	$js = '';
	$params = new stdClass();
	$params->type = $this->type;
	$params->address_id = (int)$address->address_id;
	$params->fieldset_id = 'hikashop_checkout_'.$this->type.'_address_zone';
	$params->cancel_url = '';
	echo hikashop_getLayout('address', 'show', $params, $js);
?>
</div>
<?php
}
?>
</div>
<div id="hikashop_checkout_<?php echo $this->type; ?>_address_zone">
<?php
if(empty($this->addresses)) {
	$js = '';
	$params = new stdClass();
	$params->type = $this->type;
	$params->address_id = 0; //(int)$address->address_id;
	$params->edit = true;
	$params->fieldset_id = 'hikashop_checkout_'.$this->type.'_address_zone';
	echo hikashop_getLayout('address', 'show', $params, $js);
}
?>
</div>

<?php
static $hikashop_address_select_once = false;
if(!$hikashop_address_select_once) {
	$hikashop_address_select_once = true;
?>
<script type="text/javascript">
if(!window.localPage) window.localPage = {};
window.localPage.switchAddr = function(el, type) {
	var d = document, w = window, o = w.Oby, target = d.getElementById('hikashop_checkout_selected_' + type + '_address');
	if(el === 0 || el.value == '0') {
		if(target)
			target.innerHTML = '';
		var dest = d.getElementById('hikashop_checkout_' + type + '_address_zone'),
			url = '<?php echo hikashop_completeLink('address&task=edit&cid=0&address_type={TYPE}&fid=hikashop_checkout_{TYPE}_address_zone', true, true); ?>'.replace(/\{TYPE\}/g, type);
		o.xRequest(url, {update:dest});
		if(el === 0)
			return false;
		return;
	}
<?php if($config->get('auto_submit_methods', 1)) { ?>
	el.form.submit();
<?php } else { ?>
	if(!target)
		return;
	var url = '<?php echo hikashop_completeLink('address&task=show&cid={CID}&address_type={TYPE}&fid=hikashop_checkout_{TYPE}_address_zone', true, true); ?>'.replace(/\{CID\}/g, el.value).replace(/\{TYPE\}/g, type);
	o.xRequest(url, {update:target});
<?php } ?>
}
window.Oby.registerAjax('hikashop_address_changed', function(params) {
	if(!params || !params.type)
		return;

	var d = document,
		other_type = (params.type == 'billing') ? 'shipping' : 'billing',
		el_show = d.getElementById('hikashop_checkout_'+params.type+'_address_selection'),
		el_edit = d.getElementById('hikashop_checkout_'+params.type+'_address_zone');

	var radios = document.getElementsByName("checkout_'+params.type+'_address_radio");
	var selected = false;
	if(radios){
		for (var i = 0, len = radios.length; i < len; i++) {
			if (radios[i].checked) {
				selected = true;
			}
		}
	}

	if(params.edit) {
		el_show.style.display = 'none';
		el_edit.style.display = '';
		return;
	}else if(params.type=='shipping' && (!selected || d.getElementById('hikashop_shipping_methods') || d.getElementById('hikashop_payment_methods') || d.getElementById('hikashop_checkout_cart') )){
		d.forms['hikashop_checkout_form'].submit();
	}else if(params.type=='billing' && (!d.getElementById('hikashop_checkout_shipping_address') || (d.getElementById('same_address') && d.getElementById('same_address').checked) || !selected) && (d.getElementById('hikashop_shipping_methods') || d.getElementById('hikashop_payment_methods') || d.getElementById('hikashop_checkout_cart') )){
		window.location.reload();
	}
	if(el_edit.children.length == 0)
		return;

	var el_cur = d.getElementById('hikashop_checkout_selected_'+params.type+'_address'),
		el_sel = d.getElementById('hikashop_checkout_address_'+params.type+'_selector');
		content = el_edit.firstChild.innerHTML,
		reg = new RegExp(params.type, 'g');

	el_edit.style.display = 'none';
	el_edit.innerHTML = '';

	if(el_sel && el_cur && params.cid > 0) {
		for(var k in el_sel.options) {
			if(!el_sel.options.hasOwnProperty(k))
				continue;
			if(params.previous_cid && el_sel.options[k].value == params.previous_cid && params.previous_cid != 0 && params.previous_cid != params.cid)
				el_sel.options[k].value = params.cid;
			if(el_sel.options[k].value == params.cid)
				el_sel.options[k].text = params.miniFormat;
		}
		if(params.previous_cid !== undefined && params.previous_cid === 0) {
			var o = d.createElement('option');
			o.text = params.miniFormat;
			o.value = params.cid;
			el_sel.add(o, el_sel.options[el_sel.selectedIndex]);
			el_sel.selectedIndex--;
		}
		if(el_sel.options[el_sel.selectedIndex].value == params.cid)
			el_cur.innerHTML = content;

		var ot_cur = d.getElementById('hikashop_checkout_selected_'+other_type+'_address'),
			ot_sel = d.getElementById('hikashop_checkout_address_'+other_type+'_selector');
		if(ot_sel) {
			for(var k in ot_sel.options) {
				if(!ot_sel.options.hasOwnProperty(k))
					continue;
				if(params.previous_cid && ot_sel.options[k].value == params.previous_cid && params.previous_cid != 0 && params.previous_cid != params.cid)
					ot_sel.options[k].value = params.cid;
				if(ot_sel.options[k].value == params.cid)
					ot_sel.options[k].text = params.miniFormat;
			}
			if(params.previous_cid !== undefined && params.previous_cid === 0) {
				var o = d.createElement('option');
				o.text = params.miniFormat;
				o.value = params.cid;
				ot_sel.add(o, el_sel.options[el_sel.options.length]);
			}
			if(ot_sel.options[ot_sel.selectedIndex].value == params.cid)
				ot_cur.innerHTML = content.replace(reg, other_type);
		}
	} else if(!el_sel || !el_cur) {
		var target_id = params.previous_cid || params.cid,
			target = d.getElementById('hikashop_address_'+params.type+'_' + target_id),
			other_target = d.getElementById('hikashop_address_'+other_type+'_' + target_id);

		if(target) {
			target.innerHTML = content;
		} else if(params.cid > 0) {
			window.hikashop.dup('hikashop_checkout_'+params.type+'_address_template', {'VALUE':params.cid, 'CONTENT':content}, null);
			window.hikashop.dup('hikashop_checkout_'+other_type+'_address_template', {'VALUE':params.cid, 'CONTENT': content.replace(reg, other_type) }, null);
		}

		if(other_target)
			other_target.innerHTML = content.replace(reg, other_type);
	}
	el_show.style.display = '';
});

window.Oby.registerAjax('hikashop_address_deleted', function(params) {
	if(!params || !params.type)
		return;

	var d = document, w = window, o = w.Oby,
		other_type = (params.type == 'billing') ? 'shipping' : 'billing',
		el_show = d.getElementById('hikashop_checkout_'+params.type+'_address_selection'),
		el_edit = d.getElementById('hikashop_checkout_'+params.type+'_address_zone'),
		el_cur = d.getElementById('hikashop_checkout_selected_'+params.type+'_address'),
		el_sel = d.getElementById('hikashop_checkout_address_'+params.type+'_selector');
		reg = new RegExp(params.type, 'g');

	if(params.cid <= 0)
		return;

	if(el_sel && el_cur) {
		for(var k in el_sel.options) {
			if(!el_sel.options.hasOwnProperty(k))
				continue;
			if(el_sel.options[k].value == params.cid) {
				el_sel.remove(k);
				break;
			}
		}
		o.fireEvent(el_sel,'change');

		var ot_cur = d.getElementById('hikashop_checkout_selected_'+other_type+'_address'),
			ot_sel = d.getElementById('hikashop_checkout_address_'+other_type+'_selector');
		if(ot_sel) {
			for(var k in el_sel.options) {
				if(!el_sel.options.hasOwnProperty(k))
					continue;
				if(ot_sel.options[k].value == params.cid) {
					ot_sel.remove(k);
					break;
				}
			}
			o.fireEvent(ot_sel,'change');
		}
	} else {
		var targets = ['hikashop_address_' + params.type + '_selection_' + params.cid, 'hikashop_address_' + other_type + '_selection_' + params.cid];
		for(var i = 0; i < targets.length; i++) {
			var s = d.getElementById(targets[i]);
			if(s)
				s.parentNode.removeChild(s);
		}
	}
});

window.Oby.registerAjax('hikashop_address_click', function(params) {
	if(!params.type || !params.cid)
		return;
	var d = document, w = window, o = w.Oby,
		r = d.getElementById('hikashop_checkout_' + params.type + '_address_radio_' + params.cid);
	if(r)
		r.checked = 'checked';
	r = d.getElementById('hikashop_checkout_' + params.type + '_address_selection');
	for(var i = r.childNodes.length - 1; i >= 0; i--) {
		var e = r.childNodes[i];
		if(!e) continue;
		if(!e.tagName || e.tagName.toLowerCase() != 'div') continue;
		o.removeClass(e, 'address_selected');
	}
	r = d.getElementById('hikashop_address_' + params.type + '_selection_' + params.cid);
	o.addClass(r, 'address_selected');
});
</script>
<?php
}
