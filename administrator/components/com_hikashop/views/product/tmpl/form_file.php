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
$ajax = false;
if(!empty($this->upload_ajax))
	$ajax = true;
$product_type = (!empty($this->params->product_type) && $this->params->product_type == 'variant') ? 'variant' : 'product';
$upload = hikashop_acl('product/edit/files/upload');
$options = array(
	'classes' => array(
		'mainDiv' => 'hikashop_main_file_div',
		'contentClass' => 'hikashop_product_files',
		'btn_add' => 'hika_add_btn',
		'btn_upload' => 'hika_upload_btn'
	),
	'upload' => $upload,
	'toolbar' => array(
		$this->popup->display(
			'<span class="hika_add_btn"></span>',
			JText::_('ADD_FILE'),
			hikashop_completeLink('product&task=selectfile&pid='.@$this->product->product_id,true),
			'hikashop_file_add',
			750, 460, 'onclick="return window.productMgr.addFile(this,'.(int)@$this->product->product_id.',\''.$product_type.'\');"'.' data-toggle="hk-tooltip" data-title="'.JText::_('ADD_FILE').'"', '', 'link'
		)
	),
	'tooltip' => true,
	'text' => ($upload ? JText::_('HIKA_PRODUCT_FILES_EMPTY_UPLOAD') : JText::_('HIKA_PRODUCT_FILES_EMPTY')),
	'uploader' => array('product', 'product_file'),
	'vars' => array(
		'product_id' => @$this->product->product_id,
		'product_type' => $product_type,
		'file_type' => 'file'
	),
	'ajax' => $ajax
);

$content = array();
if(!empty($this->product->files)) {
	foreach($this->product->files as $k => $file) {
		$file->product_id = $this->product->product_id;
		$file->product_type = $product_type;
		$this->params = $file;
		$content[] = $this->loadTemplate('file_entry');
	}
}


if(empty($this->editing_variant))
	echo $this->uploaderType->displayFileMultiple('hikashop_product_file', $content, $options);
else
	echo $this->uploaderType->displayFileMultiple('hikashop_product_variant_file', $content, $options);

if(empty($this->editing_variant))
	echo $this->popup->display('','HIKASHOP_FILE','','hikashop_product_file_edit',750, 460,'', '', 'link');
else
	echo $this->popup->display('','HIKASHOP_FILE','','hikashop_product_variant_file_edit',750, 460,'', '', 'link');

?>
<script type="text/javascript">
window.productMgr.addFile = function(el, pid, type) {
	var t = window.hikashop;
	if(type === undefined || type == '') type = 'product';
	if(type == 'variant') type = 'product_variant';
	t.submitFct = function(data) {
		var o = window.Oby, d = document, c = d.getElementById('hikashop_'+type+'_file_content');
		if(data.cid) {
			var url = "<?php echo hikashop_completeLink('product&task=file_entry&pid=HIKAPID&cid=HIKACID', true, false, true); ?>";
			o.xRequest(
				url.replace('HIKAPID',pid).replace('HIKACID',data.cid),
				null,
				function(xhr,params){
					var myData = document.createElement('div');
					hkjQuery(myData).html(xhr.responseText);
					c.appendChild(myData);
					hkjQuery('#hikashop_'+type+'_file_empty').hide();
				}
			);
		}
	};
	t.openBox(el);
	return false;
};
window.productMgr.editFile = function(el, id, pid, type) {
	var t = window.hikashop, href = null, n = el;
	if(type === undefined || type == '') type = 'product';
	if(type == 'variant') type = 'product_variant';
	t.submitFct = function(data) {
		var o = window.Oby, c = el;
		while(c && !o.hasClass(c, 'hikashop_'+type+'_file'))
			c = c.parentNode;
		if(c && data.cid) {
			var url = "<?php echo hikashop_completeLink('product&task=file_entry&pid=HIKAPID&cid=HIKACID', true, false, true); ?>";
			o.xRequest(
				url.replace('HIKAPID', pid).replace('HIKACID',data.cid),
				null,
				function(xhr,params){
					var myData = document.createElement('div');
					hkjQuery(myData).html(xhr.responseText);
					c.parentNode.replaceChild(myData, c);
				}
			);
		}
	};
	if(el.getAttribute('rel') == null) {
		href = el.href;
		n = 'hikashop_'+type+'_file_edit';
	}
	t.openBox(n,href,(el.getAttribute('rel') == null));
	return false;
};
window.productMgr.delFile = function(el, type) {
	if(!confirm('<?php echo $this->escape(JText::_('PLEASE_CONFIRM_DELETION')); ?>')) return false;
	if(type === undefined || type == '') type = 'product';
	if(type == 'variant') type = 'product_variant';
	return window.hkUploaderList['hikashop_'+type+'_file'].delBlock(el);
};
window.hikashop.ready(function() {
	hkjQuery('#hikashop_product<?php if(!empty($this->editing_variant)) { echo '_variant'; } ?>_file_content').sortable({
		cursor: "move",
		placeholder: "ui-state-highlight",
		forcePlaceholderSize: true
	});
});
</script>
