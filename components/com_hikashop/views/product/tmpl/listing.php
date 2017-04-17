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
if(!$this->module && isset($this->element->category_canonical) && !empty($this->element->category_canonical)){
	$canonicalUrl = hikashop_cleanURL($this->element->category_canonical);

	$doc = JFactory::getDocument();
	$doc->addCustomTag( '<link rel="canonical" href="'.$canonicalUrl.'" />' );
}

if(hikashop_level(2) && JRequest::getVar('hikashop_front_end_main',0) && JRequest::getVar('task')=='listing' && $this->params->get('show_compare')) {?>
<script type="text/javascript">
<!--
var compare_list = {length: 0};
function setToCompareList(product_id,name,elem) {
	var compareBtn = document.getElementById('hikashop_compare_button');
	if( compare_list[product_id] ) {
		var old = compare_list[product_id];
		compare_list[product_id] = null;
		compare_list.length--;
		if( elem == null ) elem = old.elem;
		var nn = elem.nodeName.toLowerCase();
		if( nn == 'a' )
			elem.innerHTML = "<?php echo JText::_('ADD_TO_COMPARE_LIST');?>";
		else if( nn == 'input' )
		{
			if(elem.type.toLowerCase()=='submit')
				elem.value = "<?php echo JText::_('ADD_TO_COMPARE_LIST');?>";
			else
				elem.checked = false;
		}
	} else {
		if(compare_list.length < <?php echo $this->config->get('compare_limit',5); ?> ) {
			compare_list[product_id] = {name: name, elem: elem};
			compare_list.length++;
			var nn = elem.nodeName.toLowerCase();
			if( nn == 'a' )
				elem.innerHTML = "<?php echo JText::_('REMOVE_FROM_COMPARE_LIST');?>";
			else if( nn == 'input' )
			{
				if(elem.type.toLowerCase()=='submit')
					elem.value = "<?php echo JText::_('REMOVE_FROM_COMPARE_LIST');?>";
				else
					elem.checked = true;
			}
		} else {
			alert("<?php echo JText::_('COMPARE_LIMIT_REACHED');?>");
			elem.checked = false;
		}
	}
	if(compare_list.length == 0 ) {
		compareBtn.style.display = 'none';
	} else {
		compareBtn.style.display = '';
	}
	return false;
}
function compareProducts() {
	var url = '';
	for(var k in compare_list) {
		if( compare_list[k] != null && k != 'length' ) {
			if( url == '' )
				url = 'cid[]=' + k;
			else
				url += '&cid[]=' + k;
		}
	}
	window.location = "<?php
		$u = hikashop_completeLink('product&task=compare'.$this->itemid,false,true);
		if( strpos($u,'?')  === false ) {
			echo $u.'?';
		} else {
			echo $u.'&';
		}
	?>" + url;
	return false;
}
window.hikashop.ready( function() {
	$$('input.hikashop_compare_checkbox').each(function(el){
		el.checked = false;
	});
});
//-->
</script>
<?php }

ob_start();
if(version_compare(JVERSION,'1.6','<')){
	$title = 'show_page_title';
}else{
	$title = 'show_page_heading';
}
$titleType = 'h1';
if($this->module){
	$title = 'showtitle';
	$titleType = 'h2';
}

if($this->params->get($title) && JRequest::getVar('hikashop_front_end_main',0) && (!$this->module || $this->pageInfo->elements->total)){
	$name = $this->params->get('page_title');
	if($this->module){
		$name = $this->params->get('title');
	}elseif($this->params->get('page_heading')){
		$name = $this->params->get('page_heading');
	}
	?>
	<<?php echo $titleType; ?>>
	<?php echo $name; ?>
	</<?php echo $titleType; ?>>
	<?php
}

	if(($this->params->get('show_image') && !empty($this->element->file_path))|| ($this->params->get('show_description',!$this->module)&&!empty($this->element->category_description))){
		?>
		<div class="hikashop_category_description">
		<?php
		if($this->params->get('show_image') && !empty($this->element->file_path)){
			jimport('joomla.filesystem.file');
			if(JFile::exists($this->image->getPath($this->element->file_path,false))){
			?>
			<img src="<?php echo $this->image->getPath($this->element->file_path); ?>" class="hikashop_category_image"/>
			<?php
			}
		}
		if($this->params->get('show_description',!$this->module)&&!empty($this->element->category_description)){
			?>
			<div class="hikashop_category_description_content">
			<?php echo JHTML::_('content.prepare',$this->element->category_description); ?>
			</div>
			<?php
		}
	?></div><?php
	}
	if(!empty($this->fields)){ ?>

		<?php
		ob_start();
		$this->fieldsClass->prefix = '';
		foreach($this->fields as $fieldName => $oneExtraField) {
			if(!empty($this->element->$fieldName)){ ?>
			<tr class="hikashop_category_custom_<?php echo $oneExtraField->field_namekey;?>_line">
				<td class="key">
					<span id="hikashop_category_custom_name_<?php echo $oneExtraField->field_id;?>" class="hikashop_category_custom_name">
						<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
					</span>
				</td>
				<td>
					<span id="hikashop_category_custom_value_<?php echo $oneExtraField->field_id;?>" class="hikashop_category_custom_value">
						<?php echo $this->fieldsClass->show($oneExtraField,$this->element->$fieldName); ?>
					</span>
				</td>
			</tr>
		<?php }
		}
		$custom_fields_html = ob_get_clean();
		if(!empty($custom_fields_html)){ ?>
		<div id="hikashop_category_custom_info_main" class="hikashop_category_custom_info_main">
			<h4><?php echo JText::_('CATEGORY_ADDITIONAL_INFORMATION');?></h4>
			<table width="100%">
				<?php echo $custom_fields_html; ?>
			</table>
		</div>
		<?php }
	}
$mainInfo = ob_get_clean();
ob_start();
$display_filters = $this->params->get('display_filters', -1);
if($display_filters=='-1'){
	$config =& hikashop_config();
	$display_filters=$config->get('show_filters');
}
if(hikashop_level(2) && JRequest::getVar('hikashop_front_end_main',0) && (JRequest::getVar('task','listing')=='listing' || !empty($this->force_display_filter)) && $display_filters=='1'){
	$this->setLayout('filter');
	$htmlFilter = $this->loadTemplate();
}

if(!empty($htmlFilter)&&@$_GET['task']!='category') echo $htmlFilter;

$filter_type = (int)$this->params->get('filter_type');
$layout_type = $this->params->get('layout_type');
if(empty($layout_type)) $layout_type = 'div';

if($filter_type !== 3) {
	$this->setLayout('listing');
	$html = $this->loadTemplate($layout_type);
	if(!$this->module) echo $mainInfo;
	if(!empty($html)){
		if($this->module) echo $mainInfo;
		if(!empty($htmlFilter) && @$_GET['task']=='category')
			echo $htmlFilter;
?>
	<div class="hikashop_products_listing">
<?php
		if(JRequest::getVar('hikashop_front_end_main',0) && JRequest::getVar('task')=='listing' && $this->params->get('show_compare')) {
?>
			<div id="hikashop_compare_zone" class="hikashop_compare_zone">
<?php
		$empty='';
		$params = new HikaParameter($empty);
		echo $this->cart->displayButton(JText::_('COMPARE_PRODUCTS'),'compare_button',$params,'#','compareProducts();return false;','style="display:none;" id="hikashop_compare_button"',0,1,' hikashop_compare_button');
?>
			</div>
<?php
		}
		echo $html;
?>
	</div>
<?php
	}elseif(( !$this->module || JRequest::getVar('hikashop_front_end_main',0) ) && (@$_REQUEST['ctrl']=='product' || @$_REQUEST['view']=='product') && (@$_REQUEST['task']=='listing' || @$_REQUEST['layout']=='listing') && !empty($this->filters) && count($this->filters)){
		echo JText::_('HIKASHOP_NO_RESULT');
	}

	$html = ob_get_clean();
	if(!empty($html)) {
?>
	<div id="<?php echo $this->params->get('main_div_name');?>" class="hikashop_category_information hikashop_products_listing_main"><?php echo $html; ?></div>
<?php
	}
} else if(!empty($this->rows) && !empty($this->categories)) {

	if(!$this->module) echo $mainInfo;

	$allrows = $this->rows;

	$pagination = '';
	if((!$this->module || JRequest::getVar('hikashop_front_end_main',0)) && $this->pageInfo->elements->total) {
		$pagination = $this->config->get('pagination','bottom');
		$this->config->set('pagination', '');
	}

	if((!empty($allrows) || !$this->module || JRequest::getVar('hikashop_front_end_main',0)) && in_array($pagination, array('top','both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total) {
		$this->pagination->form = '_top';
?>
	<form action="<?php echo hikashop_currentURL(); ?>" method="post" name="adminForm_<?php echo $this->params->get('main_div_name').$this->category_selected;?>_top">
		<div class="hikashop_products_pagination hikashop_products_pagination_top">
		<?php echo $this->pagination->getListFooter($this->params->get('limit')); ?>
		<span class="hikashop_results_counter"><?php echo $this->pagination->getResultsCounter(); ?></span>
		</div>
		<input type="hidden" name="filter_order_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
		<input type="hidden" name="filter_order_Dir_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
<?php
	}

	$main_div_name = $this->params->get('main_div_name');
	foreach($this->categories as $category) {
		if(empty($category['products']))
			continue;

		$this->rows = array();
		foreach($allrows as $p) {
			if(in_array($p->product_id, $category['products']))
				$this->rows[] = $p;
		}

		$this->params->set('main_div_name', $main_div_name.'_'.$category['category']->category_id);

		$this->setLayout('listing');
		$html = $this->loadTemplate($layout_type);
		if(!empty($html)) {
			if(!empty($htmlFilter) && @$_GET['task']=='category')
				echo $htmlFilter;
?>
	<h2><?php echo $category['category']->category_name; ?></h2>
	<div class="hikashop_products_listing">
<?php
		if(JRequest::getVar('hikashop_front_end_main',0) && JRequest::getVar('task')=='listing' && $this->params->get('show_compare')) {
?>
			<div id="hikashop_compare_zone" class="hikashop_compare_zone">
<?php
			$empty='';
			$params = new HikaParameter($empty);
			echo $this->cart->displayButton(JText::_('COMPARE_PRODUCTS'),'compare_button',$params,'#','compareProducts();return false;','style="display:none;" id="hikashop_compare_button"',0,1,' hikashop_compare_button');
?>
			</div>
<?php
		}
		echo $html;
?>
	</div>
<?php
		}
	}
	$this->params->set('main_div_name', $main_div_name);
	$this->config->set('pagination', $pagination);

	if((!empty($allrows) || !$this->module || JRequest::getVar('hikashop_front_end_main',0)) && in_array($pagination,array('bottom','both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total) {
		$this->pagination->form = '_bottom';
?>
	<form action="<?php echo hikashop_currentURL(); ?>" method="post" name="adminForm_<?php echo $this->params->get('main_div_name').$this->category_selected;?>_bottom">
		<div class="hikashop_products_pagination hikashop_products_pagination_bottom">
		<?php echo $this->pagination->getListFooter($this->params->get('limit')); ?>
		<span class="hikashop_results_counter"><?php echo $this->pagination->getResultsCounter(); ?></span>
		</div>
		<input type="hidden" name="filter_order_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
		<input type="hidden" name="filter_order_Dir_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
<?php }

	$html = ob_get_clean();
	if(!empty($html)) {
?>
		<div id="<?php echo $this->params->get('main_div_name');?>" class="hikashop_category_information hikashop_products_listing_main hikashop_product_listing_<?php echo $this->element->category_id; ?>"><?php echo $html; ?></div>
<?php
	}
}

if(!$this->module){
?>
<div class="hikashop_submodules" style="clear:both">
<?php
	if(!empty($this->modules)){
		jimport('joomla.application.module.helper');
		foreach($this->modules as $module) {
			echo JModuleHelper::renderModule($module);
		}
	}
?>
</div>
<?php
}
