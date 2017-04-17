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
echo $this->leftmenu(
	'display',
	array(
		'#display_general' => JText::_('GENERAL_DISPLAY_OPTIONS'),
		'#display_css' => JText::_('CSS'),
		'#display_modules' => JText::_('MODULES_MAIN_DEFAULT_OPTIONS'),
		'#display_products' => JText::_('DEFAULT_PARAMS_FOR_PRODUCTS'),
		'#display_categories' => JText::_('DEFAULT_PARAMS_FOR_CATEGORIES'),
		'#display_divs' => JText::_('DEFAULT_PARAMS_FOR_DIV')
	)
);
JRequest::setVar('from_display',true);
?>
<div id="page-display" class="rightconfig-container <?php if(HIKASHOP_BACK_RESPONSIVE) echo 'rightconfig-container-j30';?>">
	<table style="width:100%;">
		<tr>
			<td valign="top" width="50%">
	<!-- GENERAL -->
		<div id="display_general"></div>
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'GENERAL_DISPLAY_OPTIONS' ); ?></legend>
			<table class="admintable table" cellspacing="1">
				<tr>
					<td class="key"><?php echo JText::_('BUTTON_STYLE'); ?></td>
					<td><?php
						echo $this->button->display('config[button_style]',$this->config->get('button_style'));
					?></td>
				</tr>
<?php if(!HIKASHOP_J30) { ?>
				<tr>
					<td class="key"><?php echo JText::_('MENU_STYLE'); ?></td>
					<td><?php
						echo $this->menu_style->display('config[menu_style]',$this->config->get('menu_style'));
					?></td>
				</tr>
<?php } ?>
				<tr>
					<td class="key"><?php echo JText::_('USE_BOOTSTRAP_ON_FRONT'); ?></td>
					<td><?php
						echo JHtml::_('hikaselect.booleanlist', 'config[bootstrap_design]', '', $this->config->get('bootstrap_design', HIKASHOP_J30));
					?></td>
				</tr>
<?php if(HIKASHOP_J30) { ?>
				<tr>
					<td class="key"><?php echo JText::_('POPUP_MODE'); ?></td>
					<td><?php
						$options = array(
							JHTML::_('hikaselect.option', 'inherit', JText::_('HIKA_INHERIT')),
							JHTML::_('hikaselect.option', 'mootools', JText::_('mootools')),
							JHTML::_('hikaselect.option', 'bootstrap', JText::_('bootstrap'))
						);
						if(!empty($this->popup_plugins['content'])) {
							foreach($this->popup_plugins['content'] as $k => $v) {
								$options[] = JHTML::_('hikaselect.option', $k, JText::_($v));
							}
						}
						echo JHTML::_('select.genericlist', $options, 'config[popup_mode]', '', 'value', 'text', $this->config->get('popup_mode', 'inherit'));
					?></td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('USE_CHOSEN'); ?></td>
					<td><?php
						echo JHTML::_('hikaselect.booleanlist',  'config[bootstrap_forcechosen]','',$this->config->get('bootstrap_forcechosen', 0));
					?></td>
				</tr>
<?php } ?>
				<tr>
					<td class="key"><?php echo JText::_('IMAGE_POPUP_MODE'); ?></td>
					<td><?php
						$options = array(
							JHTML::_('hikaselect.option', 'mootools', JText::_('mootools')),
							JHTML::_('hikaselect.option', 'shadowbox', JText::_('shadowbox (external)')),
							JHTML::_('hikaselect.option', 'shadowbox-embbeded', JText::_('shadowbox (embedded)'))
						);
						if(!empty($this->popup_plugins['image'])) {
							foreach($this->popup_plugins['image'] as $k => $v) {
								$options[] = JHTML::_('hikaselect.option', $k, JText::_($v));
							}
						}
						echo JHTML::_('select.genericlist', $options, 'config[image_popup_mode]', 'onchange="return window.localPage.imagepopupmode(this);"', 'value', 'text', $this->config->get('image_popup_mode', 'mootools'));
					?>
<script type="text/javascript">
if(!window.localPage)
	window.localPage = {};
window.localPage.imagepopupmode = function(el) {
	if(el.value == 'shadowbox-embbeded') {
		var ret = confirm('Be careful: You want to use ShadowBox in embbeded mode, the library should be installed in your website to work correctly');
		if(!ret) {
			el.value = 'shadowbox';
		}
	}
}
</script>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('HIKA_PAGINATION'); ?></td>
					<td><?php
						echo $this->paginationType->display('config[pagination]',$this->config->get('pagination','bottom'));
					?></td>
				</tr>
<?php
	$values = array();
	if(file_exists(HIKASHOP_ROOT.'components'.DS.'com_jcomments'.DS.'jcomments.php')){
		$values[] = JHTML::_('select.option', 'jcomments','jComments');
	}
	if(file_exists(HIKASHOP_ROOT.'plugins'.DS.'content'.DS.'jom_comment_bot.php')){
		$values[] = JHTML::_('select.option', 'jomcomment','jomComment');
	}
	if(count($values)){
		$values[] = JHTML::_('select.option', 0,JText::_('HIKASHOP_NO'));
?>
				<tr>
					<td class="key" ><?php echo JText::_('COMMENTS_ENABLED_ON_PRODUCTS'); ?></td>
					<td>
						<?php echo JHTML::_('hikaselect.radiolist',  $values, 'config[comments_feature]', '', 'value', 'text', $this->config->get('comments_feature') ); ?>
					</td>
				</tr>
<?php } ?>
				<tr>
					<td class="key"><?php echo JText::_('PRINT_INVOICE_FRONTEND'); ?></td>
					<td>
						<?php if(hikashop_level(1)){
							echo JHTML::_('hikaselect.booleanlist', 'config[print_invoice_frontend]','',$this->config->get('print_invoice_frontend'));
						}else{
							echo hikashop_getUpgradeLink('essential');
						} ?>
					</td>
				</tr>
				<?php if(!$this->config->get('category_explorer')){ ?>
				<tr>
					<td class="key"><?php echo JText::_('CATEGORY_EXPLORER'); ?></td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', 'config[category_explorer]','',$this->config->get('category_explorer'));?>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<td class="key" >
					<?php echo JText::_('SHOW_FOOTER'); ?>
					</td>
					<td>
						<?php echo $this->elements->show_footer; ?>
					</td>
				</tr>
			</table>
		</fieldset>
	<!-- CSS -->
		<div id="display_css"></div>
		<fieldset class="adminform">
			<legend><?php echo 'CSS' ?></legend>
			<table class="admintable table" cellspacing="1">
				<tr>
					<td class="key"><?php echo JText::_('CSS_FRONTEND'); ?></td>
					<td>
						<?php echo $this->elements->css_frontend;?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('STYLES_FOR_FRONTEND'); ?></td>
					<td>
						<?php echo $this->elements->css_style;?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('CSS_BACKEND'); ?></td>
					<td>
						<?php echo $this->elements->css_backend;?>
					</td>
				</tr>
			</table>
		</fieldset>
	<!-- MODULES -->
		<div id="display_modules"></div>
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'MODULES_MAIN_DEFAULT_OPTIONS' ); ?></legend>
			<table class="admintable table" cellspacing="1">
				<tr>
					<td class="key"><?php echo JText::_('TYPE_OF_CONTENT');?></td>
					<td>
						<?php echo $this->contentType->display('config[default_params][content_type]',$this->default_params['content_type'],$this->js,false); ?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('TYPE_OF_LAYOUT');?></td>
					<td>
						<?php echo $this->layoutType->display('config[default_params][layout_type]',$this->default_params['layout_type'],$this->js,false);?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('NUMBER_OF_COLUMNS');?></td>
					<td>
						<input name="config[default_params][columns]" type="text" value="<?php echo $this->default_params['columns'];?>" />
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('NUMBER_OF_ITEMS');?></td>
					<td>
						<input name="config[default_params][limit]" type="text" value="<?php echo $this->default_params['limit'];?>" />
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('RANDOM_ITEMS');?></td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', 'config[default_params][random]' , '',$this->default_params['random']); ?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('ORDERING_DIRECTION');?></td>
					<td>
						<?php echo $this->orderdirType->display('config[default_params][order_dir]',$this->default_params['order_dir']);?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('SUB_ELEMENTS_FILTER');?></td>
					<td>
						<?php echo $this->childdisplayType->display('config[default_params][filter_type]',@$this->default_params['filter_type']);?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('ASSOCIATED_CATEGORY');?></td>
					<td>
						<?php
							echo $this->nameboxType->display(
								'config[default_params][selectparentlisting]',
								@$this->default_params['selectparentlisting'],
								hikashopNameboxType::NAMEBOX_SINGLE,
								'category',
								array(
									'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
									'displayFormat' => '{category_id} - {category_name}'
								)
							);
						?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('MODULE_CLASS_SUFFIX');?></td>
					<td>
						<input name="config[default_params][moduleclass_sfx]" type="text" value="<?php echo @$this->default_params['moduleclass_sfx'];?>" />
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('MODULES_TO_DISPLAY_UNDER_MAIN_ZONE');?></td>
					<td>
						<input type="hidden" name="config[default_params][modules]" id="modules_display"  value="<?php echo @$this->default_params['modules']; ?>" />
						<?php
							echo $this->popup->display(
								JText::_('SELECT'),
								'SELECT_MODULES',
								'\''.hikashop_completeLink('modules&task=selectmodules',true).'\'+\'&modules=\'+document.getElementById(\'modules_display\').value',
								'linkmodules_display',
								750, 375, '', '', 'button',true
							);
						?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('SYNCHRO_WITH_ITEM');?></td>
					<td><?php
						echo JHTML::_('hikaselect.booleanlist', 'config[default_params][content_synchronize]' , '',$this->default_params['content_synchronize']);
					?></td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('USE_NAME_INSTEAD_TITLE');?></td>
					<td><?php
						echo JHTML::_('hikaselect.booleanlist', 'config[default_params][use_module_name]' , '',@$this->default_params['use_module_name']);
					?></td>
				</tr>
			</table>
		</fieldset>
	<!-- PRODUCTS -->
		<div id="display_products"></div>
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'DEFAULT_PARAMS_FOR_PRODUCTS' ); ?></legend>
			<table class="admintable table" cellspacing="1">
				<tr>
					<td class="key"><?php echo JText::_('ORDERING_FIELD');?></td>
					<td>
						<?php echo $this->orderType->display('config[default_params][product_order]',$this->default_params['product_order'],'product');?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('RECENTLY_VIEWED');?></td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', 'config[default_params][recently_viewed]' , '',@$this->default_params['recently_viewed']); ?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('ADD_TO_CART_BUTTON');?></td>
					<td><?php
						if(!isset($this->default_params['add_to_cart']))
							$this->default_params['add_to_cart']=1;
						echo JHTML::_('hikaselect.booleanlist', 'config[default_params][add_to_cart]' , '',$this->default_params['add_to_cart']);
					?></td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('ADD_TO_CART_QUANTITY');?></td>
					<td><?php
						if(!isset($this->default_params['show_quantity_field']))
							$this->default_params['show_quantity_field']=1;
						echo JHTML::_('hikaselect.booleanlist', 'config[default_params][show_quantity_field]' , '',@$this->default_params['show_quantity_field']);
					?></td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('ADD_TO_WISHLIST_BUTTON');?></td>
					<td>
						<?php if(hikashop_level(1)){
							echo JHTML::_('hikaselect.booleanlist', 'config[default_params][add_to_wishlist]' , '',@$this->default_params['add_to_wishlist']);
						}else{
							echo hikashop_getUpgradeLink('essential');
						} ?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('LINK_TO_PRODUCT_PAGE');?></td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', 'config[default_params][link_to_product_page]' , '',@$this->default_params['link_to_product_page']); ?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('CONTENT_ON_PRODUCT_PAGE');?></td>
					<td>
						<?php echo $this->productSyncType->display('config[default_params][product_synchronize]' , $this->default_params['product_synchronize']); ?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('DISPLAY_PRICE');?></td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', 'config[default_params][show_price]' , '',$this->default_params['show_price']); ?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('DISPLAY_CODE');?></td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', "config[show_code]" , '', $this->config->get('show_code',0));?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('LAYOUT_ON_PRODUCT_PAGE');?></td>
					<td>
						<?php echo $this->productDisplayType->display('config[product_display]' , $this->config->get('product_display')); ?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('QUANTITY_LAYOUT_ON_PRODUCT_PAGE');?></td>
					<td>
						<?php echo $this->quantityDisplayType->display('config[product_quantity_display]' , @$this->config->get('product_quantity_display')); ?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('DISPLAY_MANUFACTURER');?></td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', 'config[manufacturer_display]' , '',@$this->config->get('manufacturer_display')); ?>
					</td>
				</tr>
				<tr id="show_original_price_line">
					<td class="key"><?php echo JText::_('ORIGINAL_CURRENCY_PRICE');?></td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', 'config[default_params][show_original_price]' , '',@$this->default_params['show_original_price']); ?>
					</td>
				</tr>
				<tr id="show_discount_line">
					<td class="key"><?php echo JText::_('SHOW_DISCOUNTED_PRICE');?></td>
					<td>
						<?php echo $this->discountDisplayType->display('config[default_params][show_discount]' ,@$this->default_params['show_discount']); ?>
					</td>
				</tr>
				<tr id="price_display_type_line">
					<td class="key"><?php echo JText::_('PRICE_DISPLAY_METHOD');?></td>
					<td>
						<?php echo $this->priceDisplayType->display( 'config[default_params][price_display_type]',@$this->default_params['price_display_type']); ?>
					</td>
				</tr>
<?php if(hikashop_level(2)){ ?>
				<tr>
					<td class="key"><?php echo JText::_('DISPLAY_CUSTOM_ITEM_FIELDS');?></td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', 'config[default_params][display_custom_item_fields]' , '',@$this->default_params['display_custom_item_fields']); ?>
					</td>
				</tr>
<?php } ?>
				<tr id="show_price_weight_line">
					<td class="key"><?php echo JText::_('WEIGHT_UNIT_PRICE');?></td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', 'config[show_price_weight]' , '',@$this->config->get('show_price_weight')); ?>
					</td>
				</tr>
				<tr id="price_stock_display_line">
					<td class="key"><?php echo JText::_('DISPLAY_OUT_OF_STOCK_PRODUCTS');?></td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', 'config[show_out_of_stock]', '',$this->config->get('show_out_of_stock',1)); ?>
					</td>
				</tr>
				<tr id="prev_next_display_line">
					<td class="key"><?php echo JText::_('DISPLAY_OTHER_PRODUCT_SHORTCUT');?></td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', 'config[show_other_product_shortcut]' , '',$this->config->get('show_other_product_shortcut',0)); ?>
					</td>
				</tr>
<?php if(hikashop_level(2)){ ?>
				<tr id="prev_next_display_line">
					<td class="key"><?php echo JText::_('DISPLAY_FILTERS_ON_PRODUCT_LISTING');?></td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', 'config[show_filters]' , '',$this->config->get('show_filters',1)); ?>
					</td>
				</tr>
<?php } ?>
				<tr>
					<td class="key"><?php echo JText::_('DISPLAY_BADGE');?></td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', 'config[default_params][display_badges]' , '',@$this->default_params['display_badges']); ?>
					</td>
				</tr>
			</table>
		</fieldset>
	<!-- CATEGORIES -->
		<div id="display_categories"></div>
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'DEFAULT_PARAMS_FOR_CATEGORIES' ); ?></legend>
			<table class="admintable table" cellspacing="1">
				<tr>
					<td class="key"><?php echo JText::_('ORDERING_FIELD');?></td>
					<td>
						<?php echo $this->orderType->display('config[default_params][category_order]',$this->default_params['category_order'],'category');?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('SHOW_SUB_CATEGORIES');?></td>
					<td>
						<?php echo $this->listType->display('config[default_params][child_display_type]',$this->default_params['child_display_type']);?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('NUMBER_OF_SUB_CATEGORIES');?></td>
					<td>
						<input name="config[default_params][child_limit]" type="text" value="<?php echo @$this->default_params['child_limit'];?>" />
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('DISPLAY_VOTE_IN_CATEGORIES');?></td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', 'config[default_params][show_vote]' , '',@$this->default_params['show_vote']); ?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('SHOW_NUMBER_OF_PRODUCTS');?></td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', 'config[default_params][number_of_products]' , '',@$this->default_params['number_of_products']); ?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('LINK_ON_MAIN_CATEGORIES');?></td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', 'config[default_params][links_on_main_categories]' , '',@$this->default_params['links_on_main_categories']); ?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('ONLY_DISPLAY_CATEGORIES_WITH_PRODUCTS');?></td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', 'config[default_params][only_if_products]' , '',@$this->default_params['only_if_products']); ?>
					</td>
				</tr>
			</table>
		</fieldset>
	<!-- DIVS -->
		<div id="display_divs"></div>
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'DEFAULT_PARAMS_FOR_DIV' ); ?></legend>
			<table class="admintable table" cellspacing="1">
				<tr>
					<td class="key"><?php echo JText::_('TYPE_OF_ITEM_LAYOUT');?></td>
					<td>
						<?php echo $this->itemType->display('config[default_params][div_item_layout_type]',$this->default_params['div_item_layout_type'],$this->js);?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('ITEM_BOX_COLOR');?></td>
					<td>
						<?php echo $this->colorType->displayAll('','config[default_params][background_color]',@$this->default_params['background_color']); ?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('ITEM_BOX_MARGIN');?></td>
					<td>
						<input name="config[default_params][margin]" type="text" value="<?php echo @$this->default_params['margin'];?>" />px
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('ITEM_BOX_BORDER');?></td>
					<td><?php
						$values = array(
							JHTML::_('select.option', '0', JText::_('HIKASHOP_NO')),
							JHTML::_('select.option', '1', JText::_('HIKASHOP_YES')),
							JHTML::_('select.option', '2', JText::_('THUMBNAIL'))
						);
						echo JHTML::_('hikaselect.radiolist', $values, 'config[default_params][border_visible]' , '', 'value', 'text', @$this->default_params['border_visible']);
					?></td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('ITEM_BOX_ROUND_CORNER');?></td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', 'config[default_params][rounded_corners]' , '',@$this->default_params['rounded_corners']); ?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('TEXT_CENTERED');?></td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', 'config[default_params][text_center]' , '',@$this->default_params['text_center']); ?>
					</td>
				</tr>
			</table>
		</fieldset>
				</div>
			</td>
		</tr>
	</table>
</div>
