<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_address_selection"<?php if(empty($this->addresses)) { echo ' style="display:none;"'; } ?>>
<?php
$config = hikashop_config();
$address_selector = (int)$config->get('user_address_selector', 1);
$this->type = 'user';
$this->fieldset_id = 'hikashop_user_address_zone';

if($address_selector == 1) {
	if(!empty($this->addresses)) {
		foreach($this->addresses as $address) {
			$checked = '';
			if($address->address_default == 1)
				$checked = ' checked="checked"';
			else
				$checked = ' onclick="this.form.submit();return false;"';
?>
<div id="hikashop_address_selection_<?php echo $address->address_id; ?>" class="address_selection<?php echo ($address->address_default == 1) ? ' address_selected':''; ?>">
	<input id="hikashop_checkout_address_radio_<?php echo $address->address_id;?>" class="checkout_address_radio" type="radio" name="hikashop_address" value="<?php echo $address->address_id;?>"<?php echo $checked; ?>/>
<?php
			$this->address_id = (int)$address->address_id;
			$this->address = $address;
			$this->setLayout('show');
			echo $this->loadTemplate();
?>
</div>
<?php
		}
	}
?>
	<div id="hikashop_checkout_address_template" class="address_selection" style="display:none;">
		<input id="hikashop_checkout_address_radio_{VALUE}" class="checkout_address_radio" type="radio" name="hikashop_address" value="{VALUE}"/>
		{CONTENT}
	</div>
	<div class="" style="margin-top:6px;">
		<a class="btn btn-success" href="#newAddress" onclick="return window.localPage.switchAddr(0);"><?php echo JText::_('HIKA_NEW'); ?></a>
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
	echo JHTML::_('select.genericlist', $values, 'hikashop_address', 'class="hikashop_field_dropdown" onchange="window.localPage.switchAddr(this);"', 'value', 'text', $current, 'hikashop_checkout_address_selector');
?><div id="hikashop_checkout_selected_address">
<?php
	if(isset($this->addresses[$current]))
		$address = $this->addresses[$current];
	else
		$address = reset($this->addresses);

	$js = '';
	$params = new stdClass();
	$params->type = $this->type;
	$params->address_id = (int)$address->address_id;
	$params->fieldset_id = 'hikashop_user_address_zone';
	$params->cancel_url = '';
	echo hikashop_getLayout('address', 'show', $params, $js);
?>
</div>
<?php
}
?>
</div>
<div id="hikashop_user_address_zone">
<?php
if(empty($this->addresses)) {
	$this->address_id = 0;
	$this->edit = true;
	$this->address = null;
	$this->setLayout('show');
	echo $this->loadTemplate();
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
window.localPage.switchAddr = function(el) {
	var d = document, w = window, o = w.Oby, target = d.getElementById('hikashop_user_address_zone');
	if(el === 0 || el.value == '0') {
		if(target)
			target.innerHTML = '';
		var dest = d.getElementById('hikashop_user_address_zone'),
			url = '<?php echo hikashop_completeLink('address&task=edit&cid=0&address_type=user&fid=hikashop_user_address_zone', true, true); ?>';
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
	var url = '<?php echo hikashop_completeLink('address&task=show&cid={CID}&address_type=user&fid=hikashop_user_address_zone', true, true); ?>'.replace(/\{CID\}/g, el.value);
	o.xRequest(url, {update:target});
<?php } ?>
}
window.Oby.registerAjax('hikashop_address_changed', function(params) {
	if(!params || !params.type)
		return;

	var d = document,
		el_show = d.getElementById('hikashop_checkout_address_selection'),
		el_edit = d.getElementById('hikashop_checkout_address_zone');

	if(params.edit) {
		el_show.style.display = 'none';
		el_edit.style.display = '';
		return;
	}else {
		if(d.forms['hikashop_user_address'])
			d.forms['hikashop_user_address'].submit();
	}
	if(el_edit.children.length == 0)
		return;

	var el_cur = d.getElementById('hikashop_checkout_selected_address'),
		el_sel = d.getElementById('hikashop_checkout_address_selector');
		content = el_edit.firstChild.innerHTML;

	el_edit.style.display = 'none';
	el_edit.innerHTML = '';

	if(el_sel && el_cur && params.cid > 0) {
		for(var k in el_sel.options) {
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
	} else if(!el_sel || !el_cur) {
		var target_id = params.previous_cid || params.cid,
			target = d.getElementById('hikashop_address_' + target_id),
			other_target = d.getElementById('hikashop_address_' + target_id);

		if(target) {
			target.innerHTML = content;
		} else if(params.cid > 0) {
			window.hikashop.dup('hikashop_checkout_address_template', {'VALUE':params.cid, 'CONTENT':content}, null);
		}
	}
	el_show.style.display = '';
});

window.Oby.registerAjax('hikashop_address_deleted', function(params) {
	if(!params || !params.type)
		return;

	var d = document, w = window, o = w.Oby,
		el_show = d.getElementById('hikashop_checkout_address_selection'),
		el_edit = d.getElementById('hikashop_checkout_address_zone'),
		el_cur = d.getElementById('hikashop_checkout_selected_address'),
		el_sel = d.getElementById('hikashop_checkout_address_selector');

	if(params.cid <= 0)
		return;

	if(el_sel && el_cur) {
		for(var k in el_sel.options) {
			if(el_sel.options[k].value == params.cid) {
				el_sel.remove(k);
				break;
			}
		}
		o.fireEvent(el_sel,'change');
	} else {
		var s = d.getElementById('hikashop_address_selection_' + params.cid);
		if(s)
			s.parentNode.removeChild(s);
	}
});

window.Oby.registerAjax('hikashop_address_click', function(params) {
	if(!params.type || !params.cid)
		return;
	var d = document, w = window, o = w.Oby,
		r = d.getElementById('hikashop_checkout_address_radio_' + params.cid);
	if(r)
		r.checked = 'checked';
	r = d.getElementById('hikashop_checkout_address_selection');
	for(var i = r.childNodes.length - 1; i >= 0; i--) {
		var e = r.childNodes[i];
		if(!e) continue;
		if(!e.tagName || e.tagName.toLowerCase() != 'div') continue;
		o.removeClass(e, 'address_selected');
	}
	r = d.getElementById('hikashop_address_selection_' + params.cid);
	o.addClass(r, 'address_selected');
});
</script>
<?php
}
