<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="btn" type="button" onclick="submitbutton('galleryselect');"><img style="vertical-align: middle" src="<?php echo HIKASHOP_IMAGES; ?>save.png"/><?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT; ?>&amp;ctrl=product" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<table width="100%" height="100%" class="adminlist" style="width:100%;height:100%;">
		<thead>
			<tr>
				<th></th>
				<th>
					<?php echo JText::_('FILTER');?>:
					<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" onchange="document.adminForm.submit();" />
					<button class="btn" onclick="document.adminForm.limitstart.value=0;this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
					<button class="btn" onclick="document.adminForm.limitstart.value=0;document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td></td>
				<td>
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<tr>
				<td width="130px" height="100%" style="width:130px;vertical-align:top;">
					<div style="width:130px;height:100%;overflow:auto;">
<?php
echo $this->treeContent;
?>
<script type="text/javascript">
hikashopGallery.callbackSelection = function(tree,id) {
	var d = document, node = tree.get(id);
	if( node.value && node.name ) {
		document.location = "<?php echo hikashop_completeLink('product&task=galleryimage&id='.JRequest::getInt('id').'&cid='.@$this->cid.'&product_id='.JRequest::getInt('product_id'), true, true) ;?>&folder=" + node.value;
	}
}
</script>
					</div>
				</td>
				<td>
					<ul class="hikaGallery">
<?php
if(!empty($this->dirContent)) {
	foreach($this->dirContent as $k => $content) {
		$chk_uid = 'hikaGalleryChk_' . $k . '_' . uniqid();
?>
	<li class="hikaGalleryItem">
		<a class="hikaGalleryPhoto" href="#" onclick="return window.hikagallery.select(this, '<?php echo $chk_uid; ?>');">
			<img src="<?php echo $content->thumbnail->url; ?>" alt="<?php echo $content->filename; ?>"/>
			<span style="display:none;" class="hikaGalleryChk"><input type="checkbox" id="<?php echo $chk_uid ;?>" name="files[]" value="<?php echo $content->path; ?>"/></span>
			<div class="hikaGalleryCommand">
				<span class="photo_name"><?php echo $content->filename; ?></span>
				<span><?php echo $content->width . 'x' . $content->height; ?></span>
				<span style="float:right"><?php echo $content->size; ?></span>
			</div>
		</a>
	</li>
<?php
	}
}
?>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>
<script type="text/javascript">
window.hikagallery = {
	selection: null
};
window.hikagallery.select = function(el, id) {
	var d = document, w = window, o = w.Oby, chk = d.getElementById(id);
	if(chk) {
		if(chk.checked) {
			o.removeClass(el.parentNode, 'selected');
			chk.checked = false;
			window.hikagallery.selection = null;
		} else {
			if(window.hikagallery.selection) {
				try {
					window.hikagallery.selection.chk.checked = false;
					o.removeClass(window.hikagallery.selection.obj, 'selected');
				} catch(e) {}
			}
			o.addClass(el.parentNode, 'selected');
			chk.checked = true;
			window.hikagallery.selection = {obj: el.parentNode, chk: chk};
		}
	}
	return false;
}
</script>
	<div class="clr"></div>
	<input type="hidden" name="data[file][file_type]" value="product" />
	<input type="hidden" name="data[file][file_ref_id]" value="<?php echo JRequest::getInt('product_id'); ?>" />
	<input type="hidden" name="id" value="<?php echo JRequest::getInt('id');?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="task" value="galleryimage" />
	<input type="hidden" name="ctrl" value="product" />
	<?php echo JHTML::_('form.token'); ?>
</form>
