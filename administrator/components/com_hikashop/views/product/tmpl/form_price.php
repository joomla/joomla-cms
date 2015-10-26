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
$acls = array(
	'value' => hikashop_acl('product/edit/price/value'),
	'tax' => hikashop_acl('product/edit/price/tax') && !$this->config->get('floating_tax_prices', 0),
	'currency' => hikashop_acl('product/edit/price/currency') && (count($this->currencies) > 1),
	'quantity' => hikashop_acl('product/edit/price/quantity'),
	'acl' => hikashop_level(2) && hikashop_acl('product/edit/price/acl')
);
$show_minimal = (!$acls['currency'] && !$acls['quantity'] && !$acls['acl']);

$jms_integration = false;
if(file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'helpers'.DS.'utils.php')){
	include_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'helpers'.DS.'utils.php');
	if(class_exists('MultisitesHelperUtils') && method_exists('MultisitesHelperUtils','getComboSiteIDs')){
		$comboSiteIDs = str_replace('class="inputbox"','class="inputbox chzn-done"',MultisitesHelperUtils::getComboSiteIDs('','price[price_site_id][##]',JText::_('SELECT_A_SITE')));
		$jms_integration = !empty($comboSiteIDs);
	}
}

$form_key = 'price';
if(!empty($this->editing_variant))
	$form_key = 'variantprice';

if(!$show_minimal) {
?>
<table style="width:100%">
	<thead>
		<tr>
			<th class="title"><?php
				echo JText::_('PRICE');
			?></th>
<?php if($acls['tax']){ ?>
			<th class="title"><?php
				echo JText::_('PRICE_WITH_TAX');
			?></th>
<?php }
	if($acls['currency']){ ?>
			<th class="title"><?php
				echo JText::_('CURRENCY');
			?></th>
<?php }
	if($acls['quantity']){ ?>
			<th class="title"><?php
				echo hikashop_tooltip(JText::_('MINIMUM_QUANTITY'), '', '', JText::_('MIN_QTY'), '', 0);
			?></th>
<?php }
	if(hikashop_level(2) && $acls['acl']){ ?>
			<th class="title"><?php
				echo hikashop_tooltip(JText::_('ACCESS_LEVEL'), '', '', JText::_('ACL'), '', 0);
			?></th>
<?php }

	if($jms_integration) { ?>
			<th class="title"><?php
				echo JText::_('SITE_ID');
			?></th>
<?php }
?>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
$k = 0;
if(!empty($this->product->prices)) {
	foreach($this->product->prices as $i => $price) {
		if(empty($price->price_min_quantity))
			$price->price_min_quantity = 1;

		$pre_price = '';
		$post_price = '';
		if(!$acls['currency']) {
			$currency = (!empty($price->price_currency_id) && isset($this->currencies[$price->price_currency_id])) ? $this->currencies[$price->price_currency_id] : $this->default_currency;
			if(is_string($currency->currency_locale))
				$currency->currency_locale = unserialize($currency->currency_locale);
			if($currency->currency_locale['p_cs_precedes']) {
				$pre_price .= $currency->currency_symbol;
				if($currency->currency_locale['p_sep_by_space'])
					$pre_price .= ' ';
			} else {
				if($currency->currency_locale['p_sep_by_space'])
					$post_price .= ' ';
				$post_price .= $currency->currency_symbol;
			}
		}

?>		<tr class="row<?php echo $k;?>" id="hikashop_<?php echo $form_key; ?>_<?php echo $i;?>">
			<td class="hika_price">
				<input type="hidden" name="<?php echo $form_key; ?>[<?php echo $i;?>][price_id]" value="<?php echo @$price->price_id;?>" />
				<?php echo $pre_price; ?><input size="10" type="text" id="hikashop_<?php echo $form_key; ?>_<?php echo $i;?>_price" name="<?php echo $form_key; ?>[<?php echo $i;?>][price_value]" value="<?php if($this->config->get('floating_tax_prices',0)){ echo @$price->price_value_with_tax; }else{ echo @$price->price_value; } ?>" onchange="window.productMgr.updatePrice(<?php echo $i; ?>, false, '<?php echo $form_key; ?>')" /><?php echo $post_price; ?>
			</td>
<?php
	if($acls['tax']){ ?>
			<td class="hika_price">
				<?php echo $pre_price; ?><input size="10" type="text" id="hikashop_<?php echo $form_key; ?>_<?php echo $i;?>_with_tax" name="<?php echo $form_key; ?>_with_tax_<?php echo $i;?>" value="<?php echo @$price->price_value_with_tax; ?>" onchange="window.productMgr.updatePrice(<?php echo $i; ?>, true, '<?php echo $form_key; ?>')"/><?php echo $post_price; ?>
			</td>
<?php }
	if($acls['currency']){ ?>
			<td class="hika_currency"><?php
				echo $this->currencyType->display($form_key.'['.$i.'][price_currency_id]', @$price->price_currency_id, '');
			?></td>
<?php }
	if($acls['quantity']){ ?>
			<td class="hika_qty">
				<input size="3" type="text" name="<?php echo $form_key; ?>[<?php echo $i;?>][price_min_quantity]" value="<?php echo @$price->price_min_quantity; ?>" />
			</td>
<?php }
	if(hikashop_level(2) && $acls['acl']){ ?>
			<td class="hika_acl"><?php echo $this->joomlaAcl->displayButton($form_key.'['.$i.'][price_access]', @$price->price_access); ?></td>
<?php }
	if($jms_integration) { ?>
			<td class="hika_jms"><?php
				echo str_replace('class="inputbox"','class="inputbox no-chzn" style="width:90px;"', MultisitesHelperUtils::getComboSiteIDs( @$price->price_site_id, $form_key.'['.$i.'][price_site_id]', JText::_( 'SELECT_A_SITE')))
			?></td>
<?php } ?>
			<td>
				<a href="#" onclick="window.hikashop.deleteRow(this); return false;"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="<?php echo JText::_('HIKA_DELETE'); ?>"></a>
			</td>
		</tr>
<?php
		$k = 1 - $k;
	}
}
?>		<tr class="row<?php echo $k;?>" id="hikashop_<?php echo $form_key; ?>_tpl" style="display:none;">
			<td class="hika_price">
				<input type="hidden" name="<?php echo $form_key; ?>[{id}][price_id]" value="" />
				<?php echo $pre_price; ?><input size="10" type="text" id="hikashop_<?php echo $form_key; ?>_{id}_price" name="<?php echo $form_key; ?>[{id}][price_value]" value="" onchange="window.productMgr.updatePrice({id}, false, '<?php echo $form_key; ?>')" /><?php echo $post_price; ?>
			</td>
<?php
	if($acls['tax']){ ?>
			<td class="hika_price">
				<?php echo $pre_price; ?><input size="10" type="text" id="hikashop_<?php echo $form_key; ?>_{id}_with_tax" value="" onchange="window.productMgr.updatePrice({id}, true, '<?php echo $form_key; ?>')"/><?php echo $post_price; ?>
			</td>
<?php }
	if($acls['currency']){ ?>
			<td class="hika_currency"><?php echo $this->currencyType->display($form_key.'[{id}][price_currency_id]', $this->main_currency_id, 'class="no-chzn"'); ?></td>
<?php }
	if($acls['quantity']){ ?>
			<td class="hika_qty"><input size="3" type="text" name="<?php echo $form_key; ?>[{id}][price_min_quantity]" value="" /></td>
<?php }
	if(hikashop_level(2) && $acls['acl']){ ?>
			<td  class="hika_acl"><?php echo $this->joomlaAcl->displayButton($form_key.'[{id}][price_access]', 'all'); ?></td>
<?php }
	if($jms_integration) { ?>
			<td class="hika_jms"><?php
				echo str_replace('class="inputbox"','class="inputbox no-chzn" style="width:90px;"', MultisitesHelperUtils::getComboSiteIDs(0 , $form_key.'[{id}][price_site_id]', JText::_( 'SELECT_A_SITE')))
			?></td>
<?php } ?>
			<td>
				<a href="#" onclick="window.hikashop.deleteRow(this); return false;"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="<?php echo JText::_('HIKA_DELETE'); ?>"></a>
			</td>
		</tr>
	</tbody>
</table>

<a class="addprice btn" href="#" onclick="return window.productMgr.newPrice('<?php echo $form_key; ?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>plus.png" alt="<?php echo JText::_('ADD'); ?>" style="vertical-align:top" /><span><?php echo JText::_('HIKA_ADD_PRICE'); ?></span></a>

<script type="text/javascript">
if(!window.productMgr)
	window.productMgr = {};
window.hikashop.ready(function(){ setTimeout(function(){ window.hikashop.noChzn(); }, 50); });
window.productMgr.newPrice = function(key) {
	var t = window.hikashop,
		cpt = window.productMgr.cpt[key],
		htmlBlocks = {id: cpt};
	t.dupRow('hikashop_'+key+'_tpl', htmlBlocks, 'hikashop_'+key+'_'+cpt);
	window.productMgr.cpt[key]++;
	return false;
};
window.productMgr.updatePrice = function(id, taxed, key) {
<?php if($acls['tax']){ ?>
	var d = document, o = window.Oby, conversion = '', elName = 'hikashop_'+key+'_'+id, destName = elName;
	if(taxed) {
		elName += '_with_tax'; destName += '_price'; conversion = 1;
	} else {
		elName += '_price'; destName += '_with_tax'; conversion = 0;
	}

	var price = d.getElementById(elName).value,
		dest = d.getElementById(destName),
		taxElem = d.getElementById('dataproductproduct_tax_id'),
		tax_id = -1;
	if(taxElem)
		tax_id = taxElem.value;
<?php if(!empty($this->product->product_tax_id)) { ?>
	else
		tax_id = <?php echo $this->product->product_tax_id; ?>;
<?php } ?>
	var url = '<?php echo str_replace('\'', '\\\'', hikashop_completeLink('product&task=getprice&price={PRICE}&product_id='.$this->product->product_id.'&tax_id={TAXID}&conversion={CONVERSION}', true, false, true)); ?>';
	url = url.replace('{PRICE}', price).replace('{TAXID}', tax_id).replace('{CONVERSION}', conversion);
	o.xRequest(url, null, function(xhr, params) {
		dest.value = xhr.responseText;
	});
<?php } ?>
};
if(!window.productMgr.cpt)
	window.productMgr.cpt = {};
window.productMgr.cpt['<?php echo $form_key; ?>'] = <?php echo count(@$this->product->prices); ?>;
</script>
<?php
} else {

?>
	<dl>
		<dt><label for="hikashop_<?php echo $form_key; ?>_0_price"><?php echo JText::_('PRICE'); ?></label></dt>
		<dd>
<?php

	$price = reset($this->product->prices);

	$pre_price = '';
	$post_price = '';

	$currency = empty($price->price_currency_id) ? $this->default_currency : $this->currencies[$price->price_currency_id];
	if(is_string($currency->currency_locale))
		$currency->currency_locale = unserialize($currency->currency_locale);
	if($currency->currency_locale['p_cs_precedes']) {
		$pre_price .= $currency->currency_symbol;
		if($currency->currency_locale['p_sep_by_space'])
			$pre_price .= ' ';
	} else {
		if($currency->currency_locale['p_sep_by_space'])
			$post_price .= ' ';
		$post_price .= $currency->currency_symbol;
	}

	if($acls['tax'] && empty($this->product->product_tax_id)) {
		echo $pre_price;
		?><input size="10" type="text" id="hikashop_<?php echo $form_key; ?>_0_price" name="<?php echo $form_key; ?>[0][price_value]" value="<?php echo @$price->price_value; ?>"/><?php
		echo $post_price;
	} else {
		if($acls['value']) {
			echo $pre_price;
			?><input size="10" type="text" id="hikashop_<?php echo $form_key; ?>_0_price" name="<?php echo $form_key; ?>[0][price_value]" value="<?php echo @$price->price_value; ?>" onchange="window.productMgr.updatePriceMini(false, '<?php echo $form_key; ?>'))" /><?php
			echo $post_price;
			echo '<br/>';
			if($acls['tax']) {
				echo $pre_price;
				?><input size="10" type="text" id="hikashop_<?php echo $form_key; ?>_0_with_tax" name="<?php echo $form_key; ?>_with_tax_0" value="<?php echo @$price->price_value_with_tax; ?>" onchange="window.productMgr.updatePriceMini(true, '<?php echo $form_key; ?>'))" /><?php
				echo $post_price;
			} else {
				echo $pre_price;
				?><span id="hikashop_<?php echo $form_key; ?>_0_with_tax_span"><?php echo @$price->price_value_with_tax;?></span><?php
				echo $post_price;
			}
		} else {
			echo $pre_price;
?>
		<input size="10" type="text" id="hikashop_<?php echo $form_key; ?>_0_with_tax" name="<?php echo $form_key; ?>_with_tax_0" value="<?php echo @$price->price_value_with_tax; ?>" onchange="window.productMgr.updatePriceMini(true, '<?php echo $form_key; ?>')" />
		<input type="hidden" id="hikashop_<?php echo $form_key; ?>_0_price" name="<?php echo $form_key; ?>[0][price_value]" value="<?php echo @$price->price_value; ?>" />
<?php
			echo $post_price;
		}
	}
?>
		</dd>
	</dl>
	<input type="hidden" name="<?php echo $form_key; ?>[0][price_id]" value="<?php echo @$price->price_id;?>" />
<script type="text/javascript">
if(!window.productMgr)
	window.productMgr = {};
window.productMgr.updatePriceMini = function(taxed, key) {
	var d = document, o = window.Oby, conversion = '', elName = 'hikashop_'+key+'_0', destName = elName;
	if(taxed) {
		elName += '_with_tax'; destName += '_price'; conversion = 1;
	} else {
		elName += '_price'; destName += '_with_tax'; conversion = 0;
	}

	var price = d.getElementById(elName).value,
		dest = d.getElementById(destName),
		taxElem = d.getElementById('dataproductproduct_tax_id'),
		tax_id = -1,
		valueMode = true;
	if(!dest) {
		dest = d.getElementById(destName + '_span');
		valueMode = false;
	}
	if(taxElem)
		tax_id = taxElem.value;
<?php if(!empty($this->product->product_tax_id)) { ?>
	else
		tax_id = <?php echo $this->product->product_tax_id; ?>;
<?php } ?>
	var url = '<?php echo str_replace('\'', '\\\'', hikashop_completeLink('product&task=getprice&price={PRICE}&product_id='.$this->product->product_id.'&tax_id={TAXID}&conversion={CONVERSION}', true, false, true)); ?>';
	url = url.replace('{PRICE}', price).replace('{TAXID}', tax_id).replace('{CONVERSION}', conversion);
	o.xRequest(url, null, function(xhr, params) {
		if(valueMode)
			dest.value = xhr.responseText;
		else
			dest.innerHTML = xhr.responseText;
	});
}
</script>
<?php
}
