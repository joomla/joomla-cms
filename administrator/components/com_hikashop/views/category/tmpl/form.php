<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=category" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
<div id="page-category">
	<table style="width:100%">
		<tr>
			<td valign="top" width="70%">
<?php } else { ?>
<div id="page-category" class="row-fluid">
	<div class="span6">
<?php } ?>
				<fieldset class="adminform" id="htmlfieldset_info">
					<legend><?php echo JText::_( 'MAIN_INFORMATION' ); ?></legend>
					<?php
						$this->category_name_input = "data[category][category_name]";
						$this->category_meta_description_input = "data[category][category_meta_description]";
						$this->category_keywords_input = "data[category][category_keywords]";
						$this->category_page_title_input = "data[category][category_page_title]";
						$this->category_alias_input = "data[category][category_alias]";
						$this->category_canonical_input = "data[category][category_canonical]";
						if($this->translation){
							$this->setLayout('translation');
						}else{
							$this->setLayout('normal');
						}
						echo $this->loadTemplate();
					?>
				</fieldset>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
			<td valign="top">
<?php } else { ?>
	</div>
	<div class="span6">
<?php } ?>
				<fieldset class="adminform" id="htmlfieldset_additional">
					<legend><?php echo JText::_( 'CATEGORY_ADDITIONAL_INFORMATION' ); ?></legend>
					<table class="admintable table" style="">
						<tr>
							<td class="key">
									<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', "data[category][category_published]" , '',@$this->element->category_published	); ?>
							</td>
						</tr>
<?php
	if(empty($this->element->category_type) || $this->element->category_type=='product') {
?>
						<tr>
							<td class="key">
									<?php echo JText::_( 'LAYOUT_ON_PRODUCT_PAGE' ); ?>
							</td>
							<td>
								<?php echo $this->productDisplayType->display('data[category][category_layout]' , @$this->element->category_layout); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'QUANTITY_LAYOUT_ON_PRODUCT_PAGE' ); ?>
							</td>
							<td>
								<?php echo $this->quantityDisplayType->display('data[category][category_quantity_layout]' , @$this->element->category_quantity_layout); ?>
							</td>
						</tr>
<?php
	}
	if(file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'helpers'.DS.'utils.php')){
		include_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'helpers'.DS.'utils.php');
		if ( class_exists( 'MultisitesHelperUtils') && method_exists( 'MultisitesHelperUtils', 'getComboSiteIDs')) {
			$comboSiteIDs = MultisitesHelperUtils::getComboSiteIDs( @$this->element->category_site_id, 'data[category][category_site_id]', JText::_( 'SELECT_A_SITE'));
			if( !empty( $comboSiteIDs) && (empty($this->element->category_parent_id) || $this->element->category_parent_id!=1) && (empty($this->element->category_type) || in_array($this->element->category_type,array('product','brand','manufacturer')))){
?>
								<tr>
									<td class="key">
											<?php echo JText::_( 'SITE_ID' ); ?>
									</td>
									<td>
										<?php echo $comboSiteIDs; ?>
									</td>
								</tr>
<?php
			}
		}
	}

	if(!empty($this->fields)) {
		foreach($this->fields as $fieldName => $oneExtraField) {
			if(!$oneExtraField->field_backend) {
?>
								<tr><td><input type="hidden" name="data[category][<?php echo $fieldName; ?>]" value="<?php echo $this->element->$fieldName; ?>" /></td></tr>
								<?php }else{ ?>
								<tr id='hikashop_category_<?php echo $fieldName; ?>'>
									<td class="key">
										<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
									</td>
									<td>
										<?php $onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick'; ?>
										<?php echo $this->fieldsClass->display($oneExtraField,$this->element->$fieldName,'data[category]['.$fieldName.']',false,' '.$onWhat.'="hikashopToggleFields(this.value,\''.$fieldName.'\',\'category\',0);"'); ?>
									</td>
								</tr>
<?php
			}
		}
	}

	if(!empty($this->extra_blocks['category'])) {
		foreach($this->extra_blocks['category'] as $r) {
			if(is_string($r))
				echo $r;
			if(is_object($r)) $r = (array)$r;
			if(is_array($r)) {
				if(!isset($r['name']) && isset($r[0]))
					$r['name'] = $r[0];
				if(!isset($r['value']) && isset($r[1]))
					$r['value'] = $r[1];
?>
								<tr>
									<td class="key"><?php echo JText::_(@$r['name']); ?></td>
									<td><?php echo @$r['value']; ?></td>
								</tr>
<?php
			}
		}
	}
?>
					</table>
<?php
	if((isset($this->element->category_type) && $this->element->category_type == 'status') || (isset($this->element->category_namekey) && in_array($this->element->category_namekey, array('root','product','tax','status','created','confirmed','cancelled','refunded','shipped','manufacturer')))) {
?>
						<input type="hidden" name="data[category][category_parent_id]" value="<?php echo @$this->element->category_parent_id; ?>" />
<?php
	} else {
		switch(@$this->element->category_type){
			case 'tax':
				$type = 'tax_category';
				break;
			case 'manufacturer':
				$type = 'category';
				break;
			case 'status':
				$type = 'order_status';
				break;
			default:
				$type = 'category';
				break;
		}
?>
						<table class="admintable table" id="category_parent">
							<tr>
								<td class="key">
									<?php echo JText::_( 'CATEGORY_PARENT' ); ?>
								</td>
								<td><?php
		echo $this->nameboxType->display(
			'data[category][category_parent_id]',
			@$this->element->category_parent_id,
			hikashopNameboxType::NAMEBOX_SINGLE,
			$type,
			array(
				'delete' => true,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
			)
		);
							?>
								</td>
							</tr>
						</table>
<?php
	}
?>
				</fieldset>
<?php if($this->category_image){ ?>
				<fieldset class="adminform" id="htmlfieldset">
					<legend><?php echo JText::_( 'HIKA_IMAGE' ); ?></legend>
					<span id="category_image-<?php echo @$this->element->file_id;?>">
						<?php echo $this->image->display(@$this->element->file_path,true,"",'','', 100, 100); ?>
					<span class="spanloading"><?php if(!empty($this->element->file_path)) echo $this->toggle->delete("category_image-".$this->element->file_id,'category-'.$this->element->category_id,'file',true); ?></span><br/></span>
					<input type="file" name="files[]" size="30" />
					<?php echo JText::sprintf('MAX_UPLOAD',(hikashop_bytes(ini_get('upload_max_filesize')) > hikashop_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize')); ?>
				</fieldset>
<?php } ?>
				<fieldset class="adminform">
				<legend><?php echo JText::_('ACCESS_LEVEL'); ?></legend>
<?php
	if(hikashop_level(2)) {
		$acltype = hikashop_get('type.acl');
		echo $acltype->display('category_access',@$this->element->category_access,'category');
	} else {
		echo '<small style="color:red">'.JText::_('ONLY_FROM_HIKASHOP_BUSINESS').'</small>';
	}
?>
				</fieldset>

<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
		</tr>
	</table>
</div>
<?php } else { ?>
	</div>
</div>
<?php } ?>
	<div style="clear:both" class="clr"></div>
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->category_id; ?>" />
	<input type="hidden" name="data[category][category_id]" value="<?php echo @$this->element->category_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="category" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
