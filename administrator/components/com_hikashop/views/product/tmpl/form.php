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
<script type="text/javascript">
	window.productMgr = { cpt:{} };
	window.hikashop.ready(function(){window.hikashop.dlTitle('adminForm');});
</script>
<form action="<?php echo hikashop_completeLink('product');?>" method="post" onsubmit="window.productMgr.prepare();" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<!-- Product edition header -->
	<div id="hikashop_product_edition_header" style="<?php if(empty($this->product->characteristics)) echo 'display:none;'; ?>">
<?php
	if(!empty($this->product)) {
		$image = $this->imageHelper->getThumbnail(@$this->product->images[0]->file_path, array(50,50), array('default' => true));
		if($image->success)
			$image_url = $image->url;
		else
			$image_url = $image->path;
		unset($image);
?>
		<h3><img src="<?php echo $image_url; ?>" alt="" style="vertical-align:middle;margin-right:5px;"/><?php echo $this->product->product_name; ?></h3>
		<ul class="hika_tabs" rel="tabs:hikashop_product_edition_tab_">
			<li class="active"><a href="#product" rel="tab:1" onclick="return window.hikashop.switchTab(this);"><?php echo JText::_('PRODUCT'); ?></a></li>
			<li><a href="#variants" rel="tab:2" onclick="return window.hikashop.switchTab(this);"><?php echo JText::_('VARIANTS'); ?><span id="hikashop_product_variant_label"></span></a></li>
		</ul>
		<div style="clear:both"></div>
<?php
	}
?>
	</div>
<div id="hikashop_product_backend_page_edition">

	<!-- Product edition : main tab -->
	<div id="hikashop_product_edition_tab_1"><div class="hk-container-fluid">

	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_general"><div>
		<div class="hikashop_product_part_title hikashop_product_edit_general_title"><?php
			echo JText::_('MAIN_OPTIONS');
		?></div>

		<dl class="hika_options">
<?php if(hikashop_acl('product/edit/name')) { ?>
			<dt class="hikashop_product_name"><label for="data_product__product_name"><?php echo JText::_('HIKA_NAME'); ?></label></dt>
			<dd class="hikashop_product_name"><input type="text" id="data_product__product_name" name="data[product][product_name]" value="<?php echo $this->escape(@$this->product->product_name); ?>"/></dd>
<?php } else { ?>
			<dt class="hikashop_product_name"><label><?php echo JText::_('HIKA_NAME'); ?></label></dt>
			<dd class="hikashop_product_name"><?php echo @$this->product->product_name; ?></dd>
<?php }

	if(hikashop_acl('product/edit/code')) { ?>
			<dt class="hikashop_product_code"><label for="data_product__product_code"><?php echo hikashop_tooltip(JText::_('PRODUCT_CODE_SKU'), '', '', JText::_('HIKA_PRODUCT_CODE'), '', 0); ?></label></dt>
			<dd class="hikashop_product_code"><input type="text" id="data_product__product_code" name="data[product][product_code]" value="<?php echo $this->escape(@$this->product->product_code); ?>"/></dd>
<?php }

	if(hikashop_acl('product/edit/quantity')) { ?>
			<dt class="hikashop_product_quantity"><label for="data_product__product_quantity"><?php echo JText::_('PRODUCT_QUANTITY'); ?></label></dt>
			<dd class="hikashop_product_quantity"><?php
				echo $this->quantityType->displayInput('data[product][product_quantity]', @$this->product->product_quantity);
			?></dd>
<?php }

	if(@$this->product->product_type != 'variant') { ?>
			<dt class="hikashop_product_category"><label for="data_product_categories_text"><?php echo JText::_('HIKA_CATEGORIES'); ?></label></dt>
			<dd class="hikashop_product_category"><?php
		$categories = null;
		if(!empty($this->product->categories))
			$categories = array_keys($this->product->categories);
		echo $this->nameboxType->display(
			'data[product][categories]',
			$categories,
			hikashopNameboxType::NAMEBOX_MULTIPLE,
			'category',
			array(
				'delete' => true,
				'sort' => true,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
				'tooltip' => true,
			)
		);
			?></dd>
<?php }

	if(@$this->product->product_type != 'variant' && hikashop_acl('product/edit/manufacturer')) { ?>
			<dt class="hikashop_product_manufacturer"><label for="data_product_product_manufacturer_id_text"><?php echo JText::_('MANUFACTURER'); ?></label></dt>
			<dd class="hikashop_product_manufacturer"><?php
		echo $this->nameboxType->display(
			'data[product][product_manufacturer_id]',
			(int)@$this->product->product_manufacturer_id,
			hikashopNameboxType::NAMEBOX_SINGLE,
			'brand',
			array(
				'delete' => true,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
			)
		);
			?></dd>
<?php }

	$tagsHelper = hikashop_get('helper.tags');
	if(!empty($tagsHelper) && $tagsHelper->isCompatible()) {
?>
			<dt class="hikashop_product_tags"><label for="data_tags_"><?php echo JText::_('JTAG'); ?></label></dt>
			<dd class="hikashop_product_tags"><?php
				$tags = $tagsHelper->loadTags('product', $this->product);
				echo $tagsHelper->renderInput($tags, array('name' => 'data[tags]', 'class' => 'inputbox'));
			?></dd>
<?php
	}

	if(hikashop_acl('product/edit/published')) { ?>
			<dt class="hikashop_product_published"><label><?php echo JText::_('HIKA_PUBLISHED'); ?></label></dt>
			<dd class="hikashop_product_published"><?php echo JHTML::_('hikaselect.booleanlist', "data[product][product_published]" , '', @$this->product->product_published); ?></dd>
<?php }

	if(hikashop_acl('product/edit/translations') && !empty($this->product->translations) && !empty($this->product->product_id)) {
?>
			<dt class="hikashop_product_translations"><label><?php echo JText::_('HIKA_TRANSLATIONS'); ?></label></dt>
			<dd class="hikashop_product_translations"><?php
		foreach($this->product->translations as $language_id => $translation){
			$lngName = $this->translationHelper->getFlag($language_id);
			echo '<div class="hikashop_multilang_button">' .
				$this->popup->display(
					$lngName, $lngName,
					hikashop_completeLink('product&task=edit_translation&product_id=' . @$this->product->product_id.'&language_id='.$language_id, true),
					'hikashop_product_translation_'.$language_id,
					760, 480, '', '', 'link'
				).
				'</div>';
		}
			?></dd>
<?php
	}
?>
		</dl>
	</div></div>


	<?php
	if(hikashop_acl('product/edit/images') || hikashop_acl('product/edit/files')) {
?>
	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_images"><div>
		<div class="hikashop_product_part_title hikashop_product_upload_title"><?php
			echo JText::_('IMAGES_AND_FILES');
		?></div>
<?php
		if(hikashop_acl('product/edit/images'))
			echo $this->loadTemplate('image');

		if(hikashop_acl('product/edit/files'))
			echo $this->loadTemplate('file');
?>
	</div></div>
<?php
	}
?>
	<div class="hkc-lg-clear"></div>

	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_price"><div>
		<div class="hikashop_product_part_title hikashop_product_edit_price_title">
			<?php echo JText::_('PRICES_AND_TAXES'); ?>
		</div>
		<dl class="hika_options">
<?php
		if((!isset($this->product->product_type) || in_array($this->product->product_type, array('main', 'template'))) && hikashop_acl('product/edit/tax')) {
?>
			<dt class="hikashop_product_tax"><label for="dataproductproduct_tax_id"><?php echo JText::_('PRODUCT_TAXATION_CATEGORY'); ?></label></dt>
			<dd class="hikashop_product_tax"><?php
				echo $this->categoryType->display('data[product][product_tax_id]', @$this->product->product_tax_id, 'tax');
			?></dd>
<?php
		}

		if(@$this->product->product_type != 'variant') {
			$curr = '';
			$mainCurr = $this->currencyClass->getCurrencies($this->main_currency_id, $curr);
?>
			<dt class="hikashop_product_msrp"><label for="data_product__product_msrp"><?php echo JText::_('PRODUCT_MSRP'); ?></label></dt>
			<dd class="hikashop_product_msrp">
				<input type="text" id="data_product__product_msrp" name="data[product][product_msrp]" value="<?php echo $this->escape(@$this->product->product_msrp); ?>"/> <?php echo $mainCurr[$this->main_currency_id]->currency_symbol.' '.$mainCurr[$this->main_currency_id]->currency_code;?>
			</dd>
<?php
		}
?>
		</dl>

<?php
	if(hikashop_acl('product/edit/price')) {
?>
		<div class="hikashop_product_price"><?php
			echo $this->loadTemplate('price');
		?></div>
<?php
	}
?>
	</div></div>
	<div class="hkc-xl-clear"></div>

<?php if(hikashop_acl('product/edit/description')) { ?>
	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_description"><div>
		<div class="hikashop_product_part_title hikashop_product_edit_description_title"><?php
			echo JText::_('DESCRIPTION');
		?></div>
		<?php echo $this->editor->display(); ?>
<script type="text/javascript">
window.productMgr.saveProductEditor = function() { <?php echo $this->editor->jsCode(); ?> };
</script>
		<div style="clear:both"></div>
	</div></div>
<?php } ?>

<?php
	if(!isset($this->product->product_type) || $this->product->product_type == 'main') {
?>
	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_meta"><div>
		<div class="hikashop_product_part_title hikashop_product_edit_meta_title"><?php
			echo JText::_('SEO');
		?></div>
		<dl class="hika_options">
<?php
		if(hikashop_acl('product/edit/pagetitle')) { ?>
			<dt class="hikashop_product_pagetitle"><label for="data_product__product_page_title"><?php echo JText::_('PAGE_TITLE'); ?></label></dt>
			<dd class="hikashop_product_pagetitle"><input id="data_product__product_page_title" type="text" style="width:100%" size="45" name="data[product][product_page_title]" value="<?php echo $this->escape(@$this->product->product_page_title); ?>" /></dd>
<?php
		}

		if(hikashop_acl('product/edit/url')) { ?>
			<dt class="hikashop_product_url"><label for="data_product__product_url"><?php echo JText::_('BRAND_URL'); ?></label></dt>
			<dd class="hikashop_product_url"><input id="data_product__product_url" type="text" style="width:100%" size="45" name="data[product][product_url]" value="<?php echo $this->escape(@$this->product->product_url); ?>" /></dd>
<?php
		}

		if(hikashop_acl('product/edit/metadescription')) { ?>
			<dt class="hikashop_product_metadescription"><label for="product_meta_description"><?php echo JText::_('PRODUCT_META_DESCRIPTION'); ?></label></dt>
			<dd class="hikashop_product_metadescription"><textarea id="product_meta_description" style="width:100%" cols="35" rows="2" name="data[product][product_meta_description]"><?php echo $this->escape(@$this->product->product_meta_description); ?></textarea></dd>
<?php
		}

		if(hikashop_acl('product/edit/keywords')) { ?>
			<dt class="hikashop_product_keywords"><label for="product_keywords"><?php echo JText::_('PRODUCT_KEYWORDS'); ?></label></dt>
			<dd class="hikashop_product_keywords"><textarea id="product_keywords" style="width:100%" cols="35" rows="2" name="data[product][product_keywords]"><?php echo $this->escape(@$this->product->product_keywords); ?></textarea></dd>
<?php
		}

		if(hikashop_acl('product/edit/alias')) { ?>
			<dt class="hikashop_product_alias"><label for="data_product__product_alias"><?php echo JText::_('HIKA_ALIAS'); ?></label></dt>
			<dd class="hikashop_product_alias"><input id="data_product__product_alias" type="text" style="width:100%" size="45" name="data[product][product_alias]" value="<?php echo $this->escape(@$this->product->product_alias); ?>" /></dd>
<?php
		}

		if(hikashop_acl('product/edit/canonical')) { ?>
			<dt class="hikashop_product_canonical"><label for="data_product__product_canonical"><?php echo JText::_('PRODUCT_CANONICAL'); ?></label></dt>
			<dd class="hikashop_product_canonical"><input id="data_product__product_canonical" type="text" style="width:100%" size="45" name="data[product][product_canonical]" value="<?php echo $this->escape(@$this->product->product_canonical); ?>"/></dd>
<?php
		}
?>
		</dl>
	</div></div>
<?php
	}
?>

	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_restrictions"><div>
		<div class="hikashop_product_part_title hikashop_product_edit_restrictions_title"><?php
			echo JText::_('RESTRICTIONS_AND_DIMENSIONS');
		?></div>
		<dl class="hika_options">
<?php
	if(hikashop_acl('product/edit/qtyperorder')) { ?>
			<dt class="hikashop_product_qtyperorder">
				<label for="data_product__product_min_per_order"><?php echo JText::_('QUANTITY_PER_ORDER'); ?></label>
<?php
		if(HIKASHOP_BACK_RESPONSIVE)
			echo '<div class="hikashop_product_qtyperorder_dt">To</div>';
?>
			</dt>
			<dd class="hikashop_product_qtyperorder">
				<input type="text" id="data_product__product_min_per_order" name="data[product][product_min_per_order]" value="<?php echo (int)@$this->product->product_min_per_order; ?>" /><?php
					echo ' <label for="data_product__product_max_per_order" style="font-weight:bold">' . JText::_('HIKA_QTY_RANGE_TO') . '</label> ';
					echo $this->quantityType->displayInput('data[product][product_max_per_order]', @$this->product->product_max_per_order);
			?></dd>
<?php
	}

	if(hikashop_acl('product/edit/salestart')) { ?>
			<dt class="hikashop_product_salestart">
				<label for="product_sale_start_img"><?php echo JText::_('PRODUCT_SALE_DATES'); ?></label>
<?php
		if(HIKASHOP_BACK_RESPONSIVE)
			echo '<div class="hikashop_product_salestart_dt">To</div>';
?>
			</dt>
			<dd class="hikashop_product_salestart"><?php
				if(!HIKASHOP_J30)
					echo '<div class="calendarj25" style="display: inline; margin-left: 2px">';

				echo JHTML::_('calendar', hikashop_getDate((@$this->product->product_sale_start?@$this->product->product_sale_start:''),'%Y-%m-%d %H:%M'), 'data[product][product_sale_start]','product_sale_start','%Y-%m-%d %H:%M',array('size' => '20'));
				if(!HIKASHOP_J30)
					echo '</div>';

				echo ' <label for="product_sale_end_img" class="calendar-separator" style="font-weight:bold">' . JText::_('HIKA_RANGE_TO') . '</label> ';

				if(!HIKASHOP_J30)
					echo '<div class="calendarj25" style="display: inline; margin-left: 2px">';
				echo JHTML::_('calendar', hikashop_getDate((@$this->product->product_sale_end?@$this->product->product_sale_end:''),'%Y-%m-%d %H:%M'), 'data[product][product_sale_end]','product_sale_end','%Y-%m-%d %H:%M',array('size' => '20'));
				if(!HIKASHOP_J30)
					echo '</div';
			?></dd>
<?php
	}

	if(hikashop_acl('product/edit/acl') && hikashop_level(2)) { ?>
			<dt class="hikashop_product_acl"><label><?php echo JText::_('ACCESS_LEVEL'); ?></label></dt>
			<dd class="hikashop_product_acl"><?php
				$product_access = 'all';
				if(isset($this->product->product_access))
					$product_access = $this->product->product_access;
				echo $this->joomlaAcl->display('data[product][product_access]', $product_access, true, true);
			?></dd>
<?php }

	if(hikashop_acl('product/edit/warehouse')) { ?>
			<dt class="hikashop_product_warehouse"><label for="data_product_product_warehouse_id_text"><?php echo JText::_('WAREHOUSE'); ?></label></dt>
			<dd class="hikashop_product_warehouse"><?php
				echo $this->nameboxType->display(
					'data[product][product_warehouse_id]',
					(int)@$this->product->product_warehouse_id,
					hikashopNameboxType::NAMEBOX_SINGLE,
					'warehouse',
					array(
						'delete' => true,
						'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
					)
				);
			?></dd>
<?php
	}

	if(hikashop_acl('product/edit/weight')) { ?>
			<dt class="hikashop_product_weight"><label for="data_product__product_weight"><?php echo JText::_('PRODUCT_WEIGHT'); ?></label></dt>
			<dd class="hikashop_product_weight">
				<input type="text" id="data_product__product_weight" name="data[product][product_weight]" id="data_product__product_weight" value="<?php echo $this->escape(@$this->product->product_weight); ?>"/>
				<?php echo $this->weight->display('data[product][product_weight_unit]', @$this->product->product_weight_unit, '', 'style="width:93px;"'); ?>
			</dd>
<?php
	}

	if(hikashop_acl('product/edit/volume')) { ?>
			<dt class="hikashop_product_volume"><label for="data_product__product_length"><?php echo JText::_('PRODUCT_VOLUME'); ?></label></dt>
			<dd class="hikashop_product_volume">
				<div class="input-prepend">
					<span class="add-on"><?php
						echo str_replace('#MYTEXT#', '<i class="hk-icon-14 icon-14-length"></i>', hikashop_tooltip(JText::_('PRODUCT_LENGTH'), '', '', '#MYTEXT#', '', 0));
					?></span>
					<input size="10" style="width:50px" type="text" id="data_product__product_length" name="data[product][product_length]" value="<?php echo $this->escape(@$this->product->product_length); ?>"/>
				</div>
				<div class="input-prepend">
					<span class="add-on"><?php
						echo str_replace('#MYTEXT#', '<i class="hk-icon-14 icon-14-width"></i>', hikashop_tooltip(JText::_('PRODUCT_WIDTH'), '', '', '#MYTEXT#', '', 0));
					?></span>
					<input size="10" style="width:50px" type="text" id="data_product__product_width" name="data[product][product_width]" value="<?php echo $this->escape(@$this->product->product_width); ?>"/>
				</div>
				<div class="input-prepend">
					<span class="add-on"><?php
						echo str_replace('#MYTEXT#', '<i class="hk-icon-14 icon-14-height"></i>', hikashop_tooltip(JText::_('PRODUCT_HEIGHT'), '', '', '#MYTEXT#', '', 0));
					?></span>
					<input size="10" style="width:50px" type="text" id="data_product__product_height" name="data[product][product_height]" value="<?php echo $this->escape(@$this->product->product_height); ?>"/>
				</div>
				<?php echo $this->volume->display('data[product][product_dimension_unit]', @$this->product->product_dimension_unit, 'dimension', '', 'class="no-chzn" style="width:93px;"'); ?>
			</dd>
<?php
	}
?>
		</dl>
	</div></div>
	<div class="hkc-xl-clear"></div>

	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_specifications"><div>
		<div class="hikashop_product_part_title hikashop_product_edit_specifications_title"><?php
			echo JText::_('SPECIFICATIONS');
		?></div>
		<dl class="hika_options">
<?php
	if(hikashop_acl('product/edit/characteristics')) { ?>
			<dt class="hikashop_product_characteristics"><label><?php echo JText::_('CHARACTERISTICS'); ?></label></dt>
			<dd id="hikashop_product_characteristics" class="hikashop_product_characteristics"><?php
				echo $this->loadTemplate('characteristic');
			?></dd>
<?php
	}

	if(hikashop_acl('product/edit/related')) { ?>
			<dt class="hikashop_product_related"><label for="data_product_related_text"><?php echo JText::_('RELATED_PRODUCTS'); ?></label></dt>
			<dd class="hikashop_product_related"><?php
				echo $this->nameboxType->display(
					'data[product][related]',
					@$this->product->related,
					hikashopNameboxType::NAMEBOX_MULTIPLE,
					'product',
					array(
						'delete' => true,
						'sort' => true,
						'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
					)
				);
			?></dd>
<?php
	}

	if(hikashop_acl('product/edit/options')) { ?>
			<dt class="hikashop_product_options"><label for="data_product_options_text"><?php echo JText::_('OPTIONS'); ?></label></dt>
			<dd class="hikashop_product_options"><?php
				if(hikashop_level(1)) {
					echo $this->nameboxType->display(
						'data[product][options]',
						@$this->product->options,
						hikashopNameboxType::NAMEBOX_MULTIPLE,
						'product',
						array(
							'delete' => true,
							'sort' => true,
							'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
						)
					);
				} else
					echo hikashop_getUpgradeLink('essential');
			?></dd>
<?php
	}
?>
		</dl>
	</div></div>

	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_display"><div>
		<div class="hikashop_product_part_title hikashop_product_edit_display_title"><?php
			echo JText::_('DISPLAY');
		?></div>
		<dl class="hika_options">
<?php
	if(hikashop_level(1) && $this->config->get('product_contact', 0) == 1 && hikashop_acl('product/edit/contactbtn')) {
?>
			<dt class="hikashop_product_contact_btn"><label><?php echo hikashop_tooltip(JText::_('DISPLAY_CONTACT_BUTTON'), '', '', JText::_('CONTACT_BUTTON'), '', 0); ?></label></dt>
			<dd class="hikashop_product_contact_btn"><?php echo JHTML::_('hikaselect.booleanlist', "data[product][product_contact]" , '',@$this->product->product_contact ); ?></dd>
<?php
	}

	if(hikashop_level(1) && $this->config->get('product_waitlist', 0) == 1 && hikashop_acl('product/edit/waitlistbtn')) { ?>
			<dt class="hikashop_product_waitlist_btn"><label><?php echo JText::_('DISPLAY_WAITLIST_BUTTON'); ?></label></dt>
			<dd class="hikashop_product_waitlist_btn"><?php echo JHTML::_('hikaselect.booleanlist', "data[product][product_waitlist]" , '',@$this->product->product_waitlist ); ?></dd>
<?php
	}

	if(hikashop_acl('product/edit/productlayout')) { ?>
			<dt class="hikashop_product_productlayout"><label><?php echo JText::_('PAGE_LAYOUT'); ?></label></dt>
			<dd class="hikashop_product_productlayout"><?php echo $this->productDisplayType->display('data[product][product_layout]' , @$this->product->product_layout); ?></dd>
<?php
	}

	if(hikashop_acl('product/edit/quantitylayout')) { ?>
			<dt class="hikashop_product_quantitylayout"><label><?php echo hikashop_tooltip(JText::_('QUANTITY_LAYOUT_ON_PRODUCT_PAGE'), '', '', JText::_('QUANTITY_LAYOUT'), '', 0); ?></label></dt>
			<dd class="hikashop_product_quantitylayout"><?php echo $this->quantityDisplayType->display('data[product][product_quantity_layout]' , @$this->product->product_quantity_layout); ?></dd>
<?php
	}
?>
		</dl>
	</div></div>

<?php
	JPluginHelper::importPlugin('hikashop');
	$dispatcher = JDispatcher::getInstance();
	$html = array();
	$dispatcher->trigger('onProductFormDisplay', array( &$this->product, &$html ));

	if((!empty($this->fields) && hikashop_acl('product/edit/customfields')) || !empty($html)) {
?>
	<div class="hkc-xl-4 hkc-lg-6 hikashop_product_block hikashop_product_edit_fields"><div>
		<div class="hikashop_product_part_title hikashop_product_edit_fields_title"><?php
			echo JText::_('FIELDS');
		?></div>
<?php
		if(!empty($this->fields) && hikashop_acl('product/edit/customfields')) {
			foreach($this->fields as $fieldName => $oneExtraField) {
?>
		<dl id="hikashop_product_<?php echo $fieldName; ?>" class="hika_options">
			<dt class="hikashop_product_<?php echo $fieldName; ?>"><label><?php echo $this->fieldsClass->getFieldName($oneExtraField); ?></label></dt>
			<dd class="hikashop_product_<?php echo $fieldName; ?>"><?php
				$onWhat = 'onchange';
				if($oneExtraField->field_type == 'radio')
					$onWhat = 'onclick';
				echo $this->fieldsClass->display($oneExtraField, $this->product->$fieldName, 'data[product]['.$fieldName.']', false, ' '.$onWhat.'="hikashopToggleFields(this.value,\''.$fieldName.'\',\'product\',0);"');
			?></dd>
		</dl>
<?php
			}
		}

		if(!empty($html)) {
			foreach($html as $k => $h) {
				if(is_string($h) && strtolower(substr(trim($h), 0, 4)) == '<tr>')
					continue;

				if(is_string($h)) {
					echo $h;
				} else {
					$fieldname = strtolower($h['name']);
					if(empty($h['label']))
						$h['label'] = $h['name'];
					$fieldname = preg_replace('([^-a-z0-9])', '_', $fieldname);
?>
		<dl id="hikashop_product_<?php echo $fieldname; ?>" class="hika_options">
			<dt class="hikashop_product_<?php echo $fieldname; ?>"><label><?php echo JText::_($h['label']); ?></label></dt>
			<dd class="hikashop_product_<?php echo $fieldname; ?>"><?php echo $h['content']; ?></dd>
		</dl>
<?php
				}

				unset($html[$k]);
			}
		}

		if(!empty($html)) {
?>
		<table class="admintable table" width="100%">
<?php
			foreach($html as $h) {
				echo $h;
			}
?>
		</table>
<?php
		}
?>
	</div></div>
<?php
	}

	$html = array();
	$dispatcher->trigger('onProductDisplay', array( &$this->product, &$html ) );
	if(!empty($html)){
		echo '<div style="clear:both"></div>';
		foreach($html as $h){
			echo $h;
		}
	}

	if(hikashop_acl('product/edit/plugin')) {
		$html = array();
		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onProductBlocksDisplay', array(&$this->product, &$html));
		if(!empty($html)) {
			echo '<div style="clear:both"></div>';
			foreach($html as $h) {
				echo $h;
			}
		}
	}
?>
	</div></div>

	<div id="hikashop_product_edition_tab_2" style="display:none;">
		<div id="hikashop_product_variant_list"><?php
			echo $this->loadTemplate('variants');
		?></div>
		<div id="hikashop_product_variant_edition">
		</div>
	</div>
</div>
<?php if(!empty($this->product->product_type) && $this->product->product_type == 'variant' && !empty($this->product->product_parent_id)) { ?>
	<input type="hidden" name="data[product][product_type]" value="<?php echo $this->product->product_type; ?>"/>
	<input type="hidden" name="data[product][product_parent_id]" value="<?php echo (int)$this->product->product_parent_id; ?>"/>
<?php } ?>
	<input type="hidden" name="cancel_action" value="<?php echo @$this->cancel_action; ?>"/>
	<input type="hidden" name="cancel_url" value="<?php echo @$this->cancel_url; ?>"/>
	<input type="hidden" name="product_id" value="<?php echo @$this->product->product_id; ?>"/>
	<input type="hidden" name="cid[]" value="<?php echo @$this->product->product_id; ?>"/>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="ctrl" value="product"/>
	<?php echo JHTML::_('form.token'); ?>
</form>
<script type="text/javascript">
window.productMgr.prepare = function() {
	var w = window, o = w.Oby;
	if(w.productMgr.saveProductEditor) {
		try { w.productMgr.saveProductEditor(); } catch(err){}
	}
	if(window.productMgr.saveVariantEditor) {
		try { window.productMgr.saveVariantEditor(); } catch(err){}
	}
	o.fireAjax("syncWysiwygEditors", null);
};
</script>
