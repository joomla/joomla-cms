<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><legend><?php echo JText::_('PRODUCT_LIST'); ?></legend>
<?php
	$url = hikashop_completeLink('product&task=selection&single=1&confirm=0&after=order|product_create&afterParams=order_id|'.$this->order->order_id, true);
?>
<div class="hika_edit"><?php
	echo $this->popup->display(
		'<img src="'. HIKASHOP_IMAGES .'plus.png" alt=""/><span>'. JText::_('HIKA_EDIT') .'</span>',
		'HIKA_ADD_ORDER_PRODUCT',
		hikashop_completeLink('order&task=product_create&order_id='.$this->order->order_id, true),
		'hikashop_addproduct_popup',
		750, 460, 'onclick="return window.orderMgr.addProduct(this);"', '', 'link'
	);
	echo ' ';
	echo $this->popup->display(
		'<img src="'. HIKASHOP_IMAGES .'product.png" alt=""/><span>'. JText::_('HIKA_EDIT') .'</span>',
		'HIKA_ADD_ORDER_PRODUCT',
		hikashop_completeLink('product&task=selection&single=1&confirm=0&after=order|product_create&afterParams=order_id|'.$this->order->order_id, true),
		'hikashop_selectproduct_popup',
		750, 460, 'onclick="return window.orderMgr.selectProduct(this);"', '', 'link'
	);
?></div>
<script type="text/javascript">
<!--
window.orderMgr.addProduct = function(el) {
	window.hikashop.submitFct = function(data) {
		var d = document, o = window.Oby;
		o.xRequest('<?php echo hikashop_completeLink('order&task=show&subtask=products&cid='.$this->order->order_id, true, false, true); ?>', {update: 'hikashop_order_products'});
		window.orderMgr.updateAdditional();
		o.fireAjax('hikashop.order_update', {el: 'product', type: 'add', obj: data});
		window.hikashop.closeBox();
	};
	window.hikashop.openBox(el);
	return false;
}
window.orderMgr.selectProduct = function(el) {
	window.hikashop.submitFct = function(data) {
		var d = document, o = window.Oby;
		o.xRequest('<?php echo hikashop_completeLink('order&task=show&subtask=products&cid='.$this->order->order_id, true, false, true); ?>', {update: 'hikashop_order_products'});
		window.orderMgr.updateAdditional();
		o.fireAjax('hikashop.order_update', {el: 'product', type: 'select', obj: data});
		window.hikashop.closeBox();
	};
	window.hikashop.openBox(el);
	return false;
}
//-->
</script>
<table class="hika_listing adminlist <?php echo (HIKASHOP_RESPONSIVE)?'table table-striped table-hover':'hika_table'; ?>" id="hikashop_order_product_listing" style="width:100%">
	<thead>
		<tr>
			<th class="hikashop_order_item_name_title title"><?php echo JText::_('PRODUCT'); ?></th>
			<th class="hikashop_order_item_price_title title"><?php echo JText::_('UNIT_PRICE'); ?></th>
			<th class="hikashop_order_item_files_title title"><?php echo JText::_('HIKA_FILES'); ?></th>
			<th class="hikashop_order_item_quantity_title title"><?php echo JText::_('PRODUCT_QUANTITY'); ?></th>
			<th class="hikashop_order_item_total_price_title title"><?php echo JText::_('PRICE'); ?></th>
<?php
	if(!empty($this->extra_data['products'])) {
		foreach($this->extra_data['products'] as $key => $content) {
?>			<th class="hikashop_order_item_<?php echo $key; ?>_title title"><?php echo JText::_($content); ?></th>
<?php
		}
	}
?>
			<th colspan="2" class="hikashop_order_item_remove_title title"><?php echo JText::_('ACTIONS'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$manage = hikashop_isAllowed($this->config->get('acl_product_manage','all'));
foreach($this->order->products as $k => $product) {
	$td_class = '';
	if(!empty($product->order_product_option_parent_id))
		$td_class = ' hikamarket_order_item_option';
?>
		<tr>
			<td class="hikashop_order_item_name_value<?php echo $td_class; ?>">
<?php
	if(!empty($product->product_id)) {
?>
				<a onclick="return window.orderMgr.showProduct(this);" href="<?php echo hikashop_frontendLink('index.php?option=com_hikashop&ctrl=product&task=show&cid='.$product->product_id,true); ?>"><?php
					echo $product->order_product_name;
				?></a>
<?php
		if($manage) {
?>
			<a target="_blank" href="<?php echo hikashop_completeLink('product&task=edit&cid[]='. $product->product_id); ?>">
				<img style="vertical-align:middle;" src="<?php echo HIKASHOP_IMAGES; ?>go.png" alt="<?php echo JText::_('HIKA_EDIT'); ?>" />
			</a>
<?php
		}
	} else {
		echo $product->order_product_name;
	}
?>
				<br/><?php echo $product->order_product_code; ?>
				<p class="hikashop_order_product_custom_item_fields"><?php
				if(hikashop_level(2) && !empty($this->fields['item'])){
					foreach($this->fields['item'] as $field){
						$namekey = $field->field_namekey;
						if(empty($product->$namekey) || !strlen($product->$namekey)){
							continue;
						}
						echo '<p class="hikashop_order_item_'.$namekey.'">'.$this->fieldsClass->getFieldName($field).': '.$this->fieldsClass->show($field,$product->$namekey).'</p>';
					}
				}?></p>
			</td>
			<td class="hikashop_order_item_price_value"><?php
				echo $this->currencyHelper->format($product->order_product_price, $this->order->order_currency_id);
				if(bccomp($product->order_product_tax,0,5)) {
					echo '<br/>'.JText::sprintf('PLUS_X_OF_VAT', $this->currencyHelper->format($product->order_product_tax, $this->order->order_currency_id));
				}
			?></td>
			<td class="hikashop_order_item_files_value"><?php
	if(!empty($product->files)){
		$html = array();
		foreach($product->files as $file){
			if(empty($file->file_name)){
				$file->file_name = $file->file_path;
			}
			$fileHtml = '';
			if(!empty($this->order_status_for_download) && !in_array($this->order->order_status,explode(',',$this->order_status_for_download))){
				$fileHtml .= ' / <b>'.JText::_('BECAUSE_STATUS_NO_DOWNLOAD').'</b>';
			}
			if(!empty($this->download_time_limit)){
					if(($this->download_time_limit+(!empty($this->order->order_invoice_created)?$this->order->order_invoice_created:$this->order->order_created))<time()){
						$fileHtml .= ' / <b>'.JText::_('TOO_LATE_NO_DOWNLOAD').'</b>';
					}else{
						$fileHtml .= ' / '.JText::sprintf('UNTIL_THE_DATE',hikashop_getDate((!empty($this->order->order_invoice_created)?$this->order->order_invoice_created:$this->order->order_created)+$this->download_time_limit));
					}
			}
			if(!empty($file->file_limit) && (int)$file->file_limit != 0) {
				$download_number_limit = $file->file_limit;
				if($download_number_limit < 0)
					$download_number_limit = 0;
			} else {
				$download_number_limit = $this->download_number_limit;
			}
			if(!empty($download_number_limit)){
				if($download_number_limit<=$file->download_number){
					$fileHtml .= ' / <b>'.JText::_('MAX_REACHED_NO_DOWNLOAD').'</b>';
				}else{
					$fileHtml .= ' / '.JText::sprintf('X_DOWNLOADS_LEFT',$download_number_limit-$file->download_number);
				}
				if($file->download_number){
					$fileHtml .= '<a href="'.hikashop_completeLink('file&task=resetdownload&file_id='.$file->file_id.'&order_id='.$this->order->order_id.'&'.hikashop_getFormToken().'=1&return='.urlencode(base64_encode(hikashop_completeLink('order&task=edit&cid='.$this->order->order_id,false,true)))).'"><img src="'.HIKASHOP_IMAGES.'delete.png" alt="'.JText::_('HIKA_DELETE').'" /></a>';
				}
			}
			$file_pos = '';
			if($file->file_pos > 0) {
				$file_pos = '&file_pos='.$file->file_pos;
			}
			$fileLink = '<a href="'.hikashop_completeLink('order&task=download&file_id='.$file->file_id.'&order_id='.$this->order->order_id.$file_pos).'">'.$file->file_name.'</a>';
			$html[]=$fileLink.' '.$fileHtml;
		}
		echo implode('<br/>',$html);
	}
			?></td>
			<td class="hikashop_order_item_quantity_value"><?php echo $product->order_product_quantity;?></td>
			<td class="hikashop_order_item_total_price_value"><?php echo $this->currencyHelper->format($product->order_product_total_price, $this->order->order_currency_id);?></td>
<?php
	if(!empty($this->extra_data['products'])) {
		foreach($this->extra_data['products'] as $key => $content) {
?>			<td class="hikashop_order_item_<?php echo $key; ?>_value"><?php
				if(isset($product->extra_data[$key]))
					echo $product->extra_data[$key];
			?></td>
<?php
		}
	}
?>
			<td class="hikashop_order_item_edit_value" style="text-align:center">
				<a onclick="return window.orderMgr.setProduct(this);" href="<?php
					echo hikashop_completeLink('order&task=edit&subtask=products&order_id='.$this->order->order_id.'&order_product_id='.$product->order_product_id, true);
				?>"><img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="<?php echo JText::_('HIKA_EDIT'); ?>"/></a>
			</td>
			<td class="hikashop_order_item_remove_value" style="text-align:center">
				<a onclick="return window.orderMgr.delProduct(this, <?php echo $product->order_product_id; ?>);" href="<?php echo hikashop_completeLink('order&task=product_delete&order_id='.$this->order->order_id.'&order_product_id='.$product->order_product_id); ?>"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="<?php echo JText::_('HIKA_DELETE'); ?>"/></a>
			</td>
		</tr>
<?php
}
?>
	</tbody>
</table>
<?php
echo $this->popup->display(
	'',
	JText::_('COM_HIKASHOP_PRODUCT_FORM_VIEW_DEFAULT_TITLE'),
	hikashop_frontendLink('index.php?option=com_hikashop&ctrl=product&task=show&cid=0', true),
	'hikashop_showproduct_popup',
	750, 460, 'style="display:none;"', '', 'link'
);
?>
<script type="text/javascript">
<!--
window.orderMgr.showProduct = function(el) {
	window.hikashop.submitFct = function(data) { window.hikashop.closeBox(); };
	window.hikashop.openBox('hikashop_showproduct_popup', el.getAttribute('href'));
	return false;
}
//-->
</script>
<?php
	echo $this->popup->display(
		'',
		JText::_('HIKA_MODIFY_ORDER_PRODUCT'),
		hikashop_completeLink('order&task=edit&subtask=products&order_id='.$this->order->order_id.'&order_product_id=0', true),
		'hikashop_editproduct_popup',
		550, 350, 'style="display:none;"', '', 'link'
	);
?>
<script type="text/javascript">
<!--
window.orderMgr.setProduct = function(el) {
	window.hikashop.submitFct = function(data) {
		var w = window, o = w.Oby;
		w.hikashop.closeBox();
		o.xRequest('<?php echo hikashop_completeLink('order&task=show&subtask=products&cid='.$this->order->order_id, true, false, true); ?>', {mode:'POST', data:'<?php echo hikashop_getFormToken(); ?>=1', update: 'hikashop_order_products'}, function() {
			window.orderMgr.updateAdditional();
			window.orderMgr.updateHistory();
			o.fireAjax('hikashop.order_update', {el: 'product', type: 'set', obj: data});
		});
	};
	window.hikashop.openBox('hikashop_editproduct_popup', el.getAttribute('href'));
	return false;
}
window.orderMgr.delProduct = function(el, id) {
	if(confirm("<?php echo JText::_('HIKA_CONFIRM_DELETE_ORDER_PRODUCT'); ?>")) {
		var w = window, o = w.Oby;
		el.parentNode.innerHTML = '<img src="<?php echo HIKASHOP_IMAGES; ?>loading.gif" alt="loading..."/>';
		o.xRequest('<?php echo hikashop_completeLink('order&task=product_remove&order_id='.$this->order->order_id.'&order_product_id=HKPRODID', true, false, true); ?>'.replace('HKPRODID',id), {mode:'POST', data:'<?php echo hikashop_getFormToken(); ?>=1', update: 'hikashop_order_products'}, function() {
			window.orderMgr.updateAdditional();
			window.orderMgr.updateHistory();
			o.fireAjax('hikashop.order_update', {el: 'product', type: 'del', obj: id});
		});
	}
	return false;
}
//-->
</script>
