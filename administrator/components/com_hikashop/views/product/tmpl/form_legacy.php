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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=product" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">
	<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
	<div id="hikashop_zone_form">
		<table style="width:100%" class="table">
			<tr>
				<td width="70%" valign="top">
	<?php } else { ?>
	<div id="hikashop_product_form" class="row-fluid">
		<div class="span6 hikaspanleft">
	<?php } ?>
				<?php
					$this->product_name_input = "data[product][product_name]";
					$this->product_url_input = "data[product][product_url]";
					$this->product_meta_description_input = "data[product][product_meta_description]";
					$this->product_keywords_input = "data[product][product_keywords]";
					$this->product_page_title_input = "data[product][product_page_title]";
					$this->product_alias_input = "data[product][product_alias]";
					$this->product_canonical_input = "data[product][product_canonical]";
					if($this->translation){
						$this->setLayout('translation');
						echo $this->loadTemplate();
					}else{
						?>
						<fieldset class="adminform hikashop_product_maininfo" id="htmlfieldset">
							<legend><?php echo JText::_( 'MAIN_INFORMATION' ); ?></legend>
							<?php
								$this->setLayout('normal');
								echo $this->loadTemplate();
							?>
						</fieldset>
						<?php
					}

					if($this->element->product_type=='main' || $this->element->product_type=='template'){
						$this->setLayout('info');
						echo $this->loadTemplate();
					}else{
						$this->setLayout('infovariant');
						echo $this->loadTemplate();
					}

					if(!empty($this->fields)){?>
						<table class="admintable table" width="100%">
						<?php foreach($this->fields as $fieldName => $oneExtraField){
							if(!$oneExtraField->field_backend){
								if($oneExtraField->field_type != "customtext"){?>
							<tr><td><input type="hidden" name="data[product][<?php echo $fieldName; ?>]" value="<?php echo $this->element->$fieldName; ?>" /></td></tr>
							<?php }
							}else{ ?>
							<tr id="hikashop_product_<?php echo $fieldName; ?>">
								<td class="key">
									<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
								</td>
								<td>
									<?php
										if(!isset($this->element->$fieldName))
											$this->element->$fieldName = $oneExtraField->field_default;
									?>
									<?php $onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick'; ?>
									<?php echo $this->fieldsClass->display($oneExtraField,$this->element->$fieldName,'data[product]['.$fieldName.']',false,' '.$onWhat.'="hikashopToggleFields(this.value,\''.$fieldName.'\',\'product\',0);"'); ?>
								</td>
							</tr>
							<?php }
						} ?>
						</table>
					<?php }

					if(!empty($this->extra_blocks['product'])) {
						foreach($this->extra_blocks['product'] as $r) {
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
	<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
				</td>
				<td valign="top">
	<?php } else { ?>
		</div>
		<div class="span6">
	<?php } ?>
				<?php if($this->element->product_type=='main' || $this->element->product_type=='template'){?>
					<fieldset class="adminform hikashop_product_categories" id="htmlfieldset">
						<legend><?php echo JText::_( 'HIKA_CATEGORIES' ); ?></legend>
					<?php
							$this->setLayout('category');
							echo $this->loadTemplate();
					?>
					</fieldset>
					<fieldset class="adminform hikashop_product_related" id="htmlfieldset">
						<legend><?php echo JText::_( 'RELATED_PRODUCTS' ); ?></legend>
					<?php
							$this->type='related';
							$this->setLayout('related');
							echo $this->loadTemplate();
					?>
					</fieldset>
					<fieldset class="adminform hikashop_product_options" id="htmlfieldset">
						<legend><?php echo JText::_( 'OPTIONS' ); ?></legend>
					<?php
					if(hikashop_level(1)){
						$this->type='options';
						$this->setLayout('related');
						echo $this->loadTemplate();
					}else{
						echo hikashop_getUpgradeLink('essential');
					}
					?>
					</fieldset>
					<fieldset class="adminform hikashop_product_characteristics" id="htmlfieldset">
						<legend><?php echo JText::_('CHARACTERISTICS');?></legend>
						<div id="hikashop_product_characteristics_message" style="display: none;" class="alert"></div>
						<?php
							$this->setLayout('characteristic');
							echo $this->loadTemplate();
						?>
					</fieldset>
				<?php }?>
				<fieldset class="adminform hikashop_product_prices" id="htmlfieldset">
					<legend><?php echo JText::_('PRICES');?></legend>
					<?php
						$this->setLayout('price');
						echo $this->loadTemplate();
					?>
				</fieldset>
				<fieldset class="adminform hikashop_product_images" id="htmlfieldset">
					<legend><?php echo JText::_('HIKA_IMAGES');?></legend>
					<?php
						$this->setLayout('image');
						echo $this->loadTemplate();
					?>
				</fieldset>
				<fieldset class="adminform hikashop_product_files" id="htmlfieldset">
					<legend><?php echo JText::_('HIKA_FILES');?></legend>
					<?php
						$this->setLayout('file');
						echo $this->loadTemplate();
					?>
				</fieldset>
<?php
JPluginHelper::importPlugin('hikashop');
$dispatcher = JDispatcher::getInstance();
$html = array();
$dispatcher->trigger('onProductBlocksDisplay', array(&$this->element, &$html));
if(!empty($html)){
	foreach($html as $h){
		echo $h;
	}
}
?>
	<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
				</td>
			</tr>
		</table>
	</div>
	<?php } else { ?>
		</div>
	</div>
	<?php } ?>
	<div class="clr"></div>
	<input type="hidden" name="data[product][product_type]" value="<?php echo @$this->element->product_type; ?>" />
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->product_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="product" />
	<input type="hidden" name="legacy" value="1" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
