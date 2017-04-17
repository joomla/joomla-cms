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
if(!isset($this->element['layout_type']))
	$this->element['layout_type'] = 'inherit';
?>
<div id="hikashop_main_content" class="hk-container-fluid item-menu-interface">
	<!-- Menu edition -->
	<div id="hikashop_menu_backend_page_edition">
		<!-- Top part (Layout selection) -->
		<?php
		if($this->type == 'category'){
			$class = 'hkc-xl-4';
		}else{
			$class = 'hkc-xl-3';
		}
		$this->layoutType->load();
		?>
		<div class="hkc-xl-12 hikashop_edit_display_type">
		<?php
		foreach($this->layoutType->values as $value){
			$src = strtolower($value->value);
			if($this->type == 'category' && $value->value == 'table') continue;
			if($value->value == 'inherit' && $this->type == 'product')
				$value->text = $value->text.' ('.$this->default_params['layout_type'].')';
			if($value->value == 'inherit' && $this->type == 'category')
				$value->text = $value->text.' (div)';
		?>
			<div class="<?php echo $class; ?> hikashop_menu_block_content_type hikashop_menu_edit_display_type_<?php echo $value->value; ?>" onclick="window.optionMgr.tabChange(this);" data-type="<?php echo $this->type; ?>_layout_choice" data-layout="<?php echo $this->type.'_'.$value->value; ?>">
				<img class="hikashop_menu_block_img_unselected" src="<?php echo HIKASHOP_IMAGES; ?>icons/icon-24-<?php echo $src; ?>.png">
				<img class="hikashop_menu_block_img_selected" src="<?php echo HIKASHOP_IMAGES; ?>icons/icon-24-<?php echo $src; ?>-selected.png">
				<?php echo $value->text; ?>
			</div>
		<?php
		}
		?>
			<input type="hidden" id="data_menu__<?php echo $this->type; ?>_layout_type" name="<?php echo $this->name; ?>[layout_type]" value="<?php echo $this->element['layout_type']; ?>">
		</div>

		<!-- Middle part (Display options) -->
		<div class="hkc-xl-12 hikashop_menu_block hikashop_menu_edit_display">
<!--			<div class="hkc-xl-4 hikashop_menu_block hikashop_menu_edit_display_preview">
			<?php
				$this->setLayout('options_display_preview');
				echo $this->loadTemplate();
			?>
			</div>
-->
			<div class="hkc-xl-12 hikashop_menu_edit_display_settings">
			<?php
			foreach($this->layoutType->values as $value){
				if($this->type == 'category' && $value->value == 'table') continue;
				if($value->value == 'inherit') continue;
				$this->setLayout('options_display_'.$value->value);
				echo $this->loadTemplate();
			}
			?>
			</div>
		</div>

		<!-- Bottom part (Generic options) -->
		<div class="hkc-xl-12 hikashop_menu_block hikashop_menu_edit_general">
		<?php
		$this->setLayout('options_main');
		echo $this->loadTemplate();
		if($this->type == 'category'){
			$this->setLayout('options_category');
			echo $this->loadTemplate();
		}elseif($this->type == 'product'){
			$this->setLayout('options_product');
			echo $this->loadTemplate();
		}
		?>
		</div>

		<!-- Extra part (Carousel options & ...) -->
		<div class="hkc-xl-12 hikashop_menu_block hikashop_menu_edit_extra" data-type="<?php echo $this->type; ?>" data-display-tab="div">
		<?php
			$this->setLayout('options_product_extra');
			echo $this->loadTemplate();
		?>
		</div>
<?php
if(!empty($this->extra_blocks['layouts'])) {
	foreach($this->extra_blocks['layouts'] as $key => $r) {
		if(is_string($r))
			echo $r;
		if(is_array($r)) {
			if(!isset($r['name']) && isset($r[0]))
				$r['name'] = $r[0];
			if(!isset($r['value']) && isset($r[1]))
				$r['value'] = $r[1];
?>
		<div class="hkc-xl-12 hikashop_menu_block hikashop_menu_edit_<?php echo $key; ?>">
<div class="hkc-xl-4 hikashop_menu_subblock hikashop_menu_edit_product">
	<div class="hikashop_menu_subblock_content">
		<div class="hikashop_menu_subblock_title hikashop_menu_edit_<?php echo $key; ?>_title"><?php echo JText::_(@$r['name']); ?></div>
<?php
			if(is_array($r['value'])) {
?>
		<dl class="hika_options">
<?php
				foreach($r['value'] as $k => $v) {
?>
			<dt class="hikashop_option_name"><?php echo JText::_($k); ?></dt>
			<dd class="hikashop_option_value"><?php echo $v; ?></dd>
<?php
				}
?>
		</dl>
<?php
			} else {
				echo $r['value'];
			}
?>
	</div>
</div>
		</div>
<?php
		}
	}
}
?>
	</div>
</div>
<?php
$js = "
window.hikashop.ready(function(){
	window.hikashop.dlTitle('hikashop_main_content');
";
$js .= "
	if(hkjQuery('#attrib-products')){
		hkjQuery('#attrib-products .control-group').hide();
		hkjQuery('#attrib-basic .control-group').hide();
	}
";
$js .= "
	hkjQuery('div[data-type=\'".$this->type."_layout\']').hide();
	window.optionMgr.tabChange('div[data-layout=\'".$this->type."_".$this->element['layout_type']."\']');
";
$js .= "
	hkjQuery('#hikashop_menu_backend_page_edition .hikashop_menu_edit_display .hikashop_option_value').find('input').change(function(){
		var name = hkjQuery(this).attr('name');
		var val = hkjQuery(this).val();
		hkjQuery('[name=\''+name+'\']').val(val);
	});
	hkjQuery('#hikashop_menu_backend_page_edition .hikashop_menu_edit_display .hikashop_option_value').find('select').change(function(){
		var name = hkjQuery(this).attr('name');
		var val = hkjQuery(this).val();
		hkjQuery('[name=\''+name+'\']').val(val);
	});
";
$js .= "
	window.optionMgr.showCarouselOptions('carousel','".@$this->element['enable_carousel']."','".$this->name."');
	hkjQuery('.hikashop_menu_edit_extra .hikashop_option_value').find('input').change(function(){
		window.optionMgr.showCarouselOptions(''+hkjQuery(this).parent().parent().attr('data-control')+'',''+hkjQuery(this).val()+'',''+hkjQuery(this).attr('name')+'');
	});
	hkjQuery('.hikashop_menu_edit_extra .hikashop_option_value').find('select').change(function(){
		window.optionMgr.showCarouselOptions(''+hkjQuery(this).parent().attr('data-control')+'',''+hkjQuery(this).val()+'',''+hkjQuery(this).attr('name')+'');
	});
";
$js .= "
	window.optionMgr.hideDisplayOptions();
	hkjQuery('#hikashop_main_content .hikashop_option_value').find('input').change(function(){
		window.optionMgr.hideDisplayOptions(''+hkjQuery(this).attr('name')+'',''+hkjQuery(this).val()+'');
	});
	hkjQuery('#hikashop_main_content .hikashop_option_value').find('select').change(function(){
		window.optionMgr.hideDisplayOptions(''+hkjQuery(this).attr('name')+'',''+hkjQuery(this).val()+'');
	});
";
$js .="
	hkjQuery('.listing_item_quantity_fields input').change(function(){
		var name = hkjQuery(this).attr('name').replace('[columns]','').replace('[rows]','');
		var listType = hkjQuery(this).parent().attr('data-list-type');
		var cCol = 1;
		if(listType != 'table')
			cCol = hkjQuery('input[name=\''+name+'[columns]\']').val();
		var cRow = hkjQuery('input[name=\''+name+'[rows]\']').val();
		hkjQuery('input[name=\''+name+'[limit]\']').val(parseInt(cRow) * parseInt(cCol));
		window.optionMgr.fillSelector(cCol,cRow,name);
	});
	hkjQuery('.listing_item_quantity_selector div').mouseover(function(){
		var classes = hkjQuery(this).attr('class').split(' ');
		window.optionMgr.fillSelector(parseInt(classes[0].replace('col',''))+1,parseInt(classes[1].replace('row',''))+1,hkjQuery(this).parent().attr('data-name'));
	});
	hkjQuery('.listing_item_quantity_selector div').click(function(){
		var name = hkjQuery(this).parent().attr('data-name');
		var classes = hkjQuery(this).attr('class').split(' ');
		hkjQuery('input[name=\''+name+'[columns]\']').val(parseInt(classes[0].replace('col',''))+1);
		hkjQuery('input[name=\''+name+'[rows]\']').val(parseInt(classes[1].replace('row',''))+1);
		hkjQuery('input[name=\''+name+'[limit]\']').val((parseInt(classes[0].replace('col',''))+1) * (parseInt(classes[1].replace('row',''))+1));
	});
	hkjQuery('.listing_item_quantity_selector').mouseleave(function(){
		var name = hkjQuery(this).attr('data-name');
		var cCol = hkjQuery('input[name=\''+name+'[columns]\']').val();
		var limit = hkjQuery('input[name=\''+name+'[limit]\']').val();
		var cRow = 0;
		if(limit != 0)
			cRow = limit / cCol;
		Math.ceil(cRow);
		window.optionMgr.fillSelector(cCol,cRow,name);
	});
});
";
$js .= "var defaultParams = [];";
foreach($this->default_params as $k => $v){
	$js .= "defaultParams['".$k."'] = '".$v."';";
}
$js .= "
window.optionMgr = {
	cpt:{
	},
	fillSelector : function(cCol,cRow,name) {
		hkjQuery('.listing_item_quantity_selector[data-name=\''+name+'\'] div').each(function(){
			var classes = hkjQuery(this).attr('class').split(' ');
			var col = parseInt(classes[0].replace('col',''));
			var row = parseInt(classes[1].replace('row',''));
			hkjQuery(this).removeClass('selected');
			if(col < cCol && row < cRow)
				hkjQuery(this).addClass('selected');
		});
	},
	tabChange : function(el) {
		var val = hkjQuery(el).attr('data-layout');
		if(val == 'category_table')
			val = 'category_inherit';
		var info = val.split('_');
		if(info[1] == 'inherit'){
			if(info[0] == 'category' && '".$this->default_params['layout_type']."' == 'table'){
				val = info[0]+'_div';
			}else{
				val = info[0]+'_".$this->default_params['layout_type']."';
			}
		}
		hkjQuery('div[data-type=\''+info[0]+'_layout\']').css('display','none');
		hkjQuery('div[data-layout=\''+val+'\']').css('display','inherit');
		hkjQuery('#data_menu__'+info[0]+'_layout_type').val(info[1]);
		hkjQuery('div[data-type=\''+info[0]+'_layout_choice\']').removeClass('selected');
		hkjQuery(el).addClass('selected');
		if(info[1] == 'div')
			hkjQuery('div[data-display-tab=\'div\']').show();
		else
			hkjQuery('div[data-display-tab=\'div\']').hide();
	},
	hideDisplayOptions : function(optionName,newValue) {
		var dynamicHide = {
			'child_display_type': {
				'hideValues': ['nochild','inherit'],
				'hideOptions': ['child_limit']
			},
			'div_item_layout_type': {
				'hideValues': ['title','inherit'],
				'hideOptions': ['image_width','image_height']
			},
			'show_price': {
				'hideValues': ['0','-1'],
				'hideOptions': ['price_display_type','price_with_tax','show_original_price','show_discount']
			}
		};
		if(optionName === undefined || newValue === undefined){
			var optPrefix = ['".$this->name."','".$this->name."'], done = false;
			optPrefix[0] = optPrefix[0].replace('hk_category','hk_product').replace('hk_product','hk_category');
			optPrefix[1] = optPrefix[1].replace('hk_product','hk_category').replace('hk_category','hk_product');
			for(var x = 0; x <= 1; x++){
				hkjQuery.each(dynamicHide, function(mainOption,values){
					var currentValue = hkjQuery('[name=\''+optPrefix[x]+'['+mainOption+']\'][checked=\'checked\']').val();
					if(currentValue === undefined)
						currentValue = hkjQuery('[name=\''+optPrefix[x]+'['+mainOption+']\']').val();
					if((currentValue == 'inherit' || currentValue == '-1'))
						currentValue = defaultParams[mainOption];
					if(hkjQuery.inArray(currentValue,dynamicHide[mainOption]['hideValues']) != '-1'){
						hkjQuery.each(values['hideOptions'],function(index, optionList){
							option = optionList.split(',');
							for(var i = option.length - 1; i >= 0; i--){
								hkjQuery('[name=\''+optPrefix[x]+'['+option+']\']').parent().parent('.hika_options').hide();
								hkjQuery('[name=\''+optPrefix[x]+'['+option+']\']').parent().parent().parent('.hika_options').hide();
							}
						});
					}
				});
			}
		}else{
			if(optionName.match(/hk_category/g)){
				optionName = optionName.replace('jform[params][hk_category][','').replace(']','');
				var optPrefix = 'jform[params][hk_category]';
			}else{
				optionName = optionName.replace('jform[params][hk_product][','').replace(']','');
				var optPrefix = 'jform[params][hk_product]';
			}
			if(dynamicHide[optionName] === undefined)
				return;
			if((newValue == 'inherit' || newValue == '-1'))
				newValue = defaultParams[optionName];
			if(hkjQuery.inArray(newValue,dynamicHide[optionName]['hideValues']) != '-1'){
				hkjQuery.each(dynamicHide[optionName]['hideOptions'],function(j, option){
					hkjQuery('[name=\''+optPrefix+'['+option+']\']').parent().parent('.hika_options').hide();
					hkjQuery('[name=\''+optPrefix+'['+option+']\']').parent().parent().parent('.hika_options').hide();
				});
			}else{
				hkjQuery.each(dynamicHide[optionName]['hideOptions'],function(j, option){
					hkjQuery('[name=\''+optPrefix+'['+option+']\']').parent().parent('.hika_options').show();
					hkjQuery('[name=\''+optPrefix+'['+option+']\']').parent().parent().parent('.hika_options').show();
				});
			}
		}
	},
	showCarouselOptions : function(dataControl,value,name){
		var type = 'product';
		var optPrefix = 'jform[params][hk_product]';
		if(name !== undefined && name.match(/category/g)){
			type = 'category';
			optPrefix = 'jform[params][hk_category]';
		}
		var mainValue = value;
		var dataParts = [dataControl];
		if(dataControl == 'carousel'){
			if(value == 1){
				hkjQuery('[data-type=\''+type+'\']  .hikashop_menu_edit_product_extra_part2').show();
				dataParts = ['effect','paginationthumbnail','pagination','autoslide','carousel'];
			}else{
				hkjQuery('[data-type=\''+type+'\']  .hikashop_menu_edit_product_extra_part2').hide();
				dataParts = ['carousel','effect','autoslide','pagination','paginationthumbnail'];
			}
		}else if(dataControl == 'pagination'){
			dataParts = ['paginationthumbnail','pagination'];
		}
		for(var i = dataParts.length - 1; i >= 0; i--){
			if(dataParts[i] != dataControl && (dataControl != 'carousel' || mainValue != 0)){
				if(dataParts[i] == 'carousel')
					value = hkjQuery('[name=\''+optPrefix+'[enable_carousel]\'][checked=\'checked\']').val();
				if(dataParts[i] == 'effect')
					value = hkjQuery('[name=\''+optPrefix+'[carousel_effect]\']').val();
				if(dataParts[i] == 'pagination')
					value = hkjQuery('[name=\''+optPrefix+'[pagination_type]\']').val();
				if(dataParts[i] == 'autoslide')
					value = hkjQuery('[name=\''+optPrefix+'[auto_slide]\'][checked=\'checked\']').val();
			}else{
				value = mainValue;
			}
			if(value == '' || value == '0' || value == 'no' || value == 'fade' || value == 'no_pagination' || (dataParts[i] == 'paginationthumbnail' && value != 'thumbnails')){
				hkjQuery('[data-type=\''+type+'\'] dl[data-part=\''+dataParts[i]+'\']').hide();
			}else{
				hkjQuery('[data-type=\''+type+'\'] dl[data-part=\''+dataParts[i]+'\']').show();
			}
		}
	}
};
";
global $hkMenusJs;
if(!isset($hkMenusJs))
	$hkMenusJs = $js;
else
	$hkMenusJs = "
window.hikashop.ready(function(){
	hkjQuery('div[data-type=\'".$this->type."_layout\']').hide();
	window.optionMgr.tabChange('div[data-layout=\'".$this->type."_".$this->element['layout_type']."\']');
});
	";
$doc = JFactory::getDocument();
$doc->addScriptDeclaration($hkMenusJs);
