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
$app = JFactory::getApplication();
if((!empty($this->rows) || !$this->module || JRequest::getVar('hikashop_front_end_main',0)) && $this->pageInfo->elements->total) {
	$pagination = $this->config->get('pagination','bottom');
	if(in_array($pagination,array('top','both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total) {
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
?>
	<div class="hikashop_products">
<?php
	if(!empty($this->rows)){
		if ($this->config->get('show_quantity_field') >= 2) {
?>
		<form action="<?php echo hikashop_completeLink('product&task=updatecart'); ?>" method="post" name="hikashop_product_form_<?php echo $this->params->get('main_div_name'); ?>" enctype="multipart/form-data">
<?php
		}
		$columns = $this->params->get('columns');
		if(empty($columns)|| $columns == 0)
			$columns = 1;
		$width = (int)(100 / $columns) - 2;
		$current_column = 1;

		if(empty($width))
			$width='style="float:left;"';
		else
			$width='style="float:left;width:'.$width.'%;"';

?>
			<ul class="hikashop_product_list<?php echo $this->params->get('ul_class_name'); ?>" data-consistencyheight="true">
<?php
		foreach($this->rows as $row) {
			$this->row =& $row;
			$link = hikashop_contentLink('product&task=show&cid='.$row->product_id.'&name='.$row->alias.$this->itemid.$this->category_pathway,$row);
			if($row->product_parent_id != 0 && isset($row->main_product_quantity_layout)){
				$row->product_quantity_layout = $row->main_product_quantity_layout;
			}
			if(!empty($row->product_quantity_layout) &&  $row->product_quantity_layout != 'inherit'){
				$qLayout = $row->product_quantity_layout;
			}else{
				$categoryQuantityLayout = '';
				if(!empty($row->categories) ) {
					foreach($row->categories as $category) {
						if(!empty($category->category_quantity_layout) && $this->quantityDisplayType->check($category->category_quantity_layout, $app->getTemplate())) {
							$categoryQuantityLayout = $category->category_quantity_layout;
							break;
						}
					}
				}
				if(!empty($categoryQuantityLayout) && $categoryQuantityLayout != 'inherit'){
					$qLayout = $categoryQuantityLayout;
				}else{
					$qLayout = $this->config->get('product_quantity_display','show_default');
				}
			}
			JRequest::setVar('quantitylayout',$qLayout);
?>
				<li class="hikashop_product_list_item" <?php echo $width; ?>>
<?php
			if($this->params->get('link_to_product_page',0)){ ?>
					<a href="<?php echo $link; ?>" class="hikashop_product_name_in_list">
<?php
			}
			echo $row->product_name;
?>
					<span class='hikashop_product_code_list'><?php
						if ($this->config->get('show_code')) {
							echo $this->row->product_code;
						}
					?></span>
<?php
			if($this->params->get('show_price')) {
				$this->setLayout('listing_price');
				echo '&nbsp;'.$this->loadTemplate();
			}

			if($this->params->get('link_to_product_page',1)){ ?>
					</a>
<?php
			}

			if($this->params->get('show_vote_product')){
				$this->setLayout('listing_vote');
				echo $this->loadTemplate();
			}

			if($this->params->get('add_to_cart') || $this->params->get('add_to_wishlist')){
				$this->setLayout('add_to_cart_listing');
				echo $this->loadTemplate();
			}

			if(JRequest::getVar('hikashop_front_end_main',0) && JRequest::getVar('task')=='listing' && $this->params->get('show_compare')) {
				if( $this->params->get('show_compare') == 1 ) {
					$js = 'setToCompareList('.$this->row->product_id.',\''.$this->escape($this->row->product_name).'\',this); return false;';
					echo $this->cart->displayButton(JText::_('ADD_TO_COMPARE_LIST'),'compare',$this->params,$link,$js,'',0,1,'hikashop_compare_button');
				} else {
?>
					<input type="checkbox" class="hikashop_compare_checkbox" id="hikashop_listing_chk_<?php echo $this->row->product_id;?>" onchange="setToCompareList(<?php echo $this->row->product_id;?>,'<?php echo $this->escape($this->row->product_name); ?>',this);"><label for="hikashop_listing_chk_<?php echo $this->row->product_id;?>"><?php echo JText::_('ADD_TO_COMPARE_LIST'); ?></label>
<?php
				}
			}
?>
				</li>
<?php
			if($current_column >= $columns) {
				$current_column=0;
			}
			$current_column++;
		}
?>
			</ul>
<?php
		if($this->config->get('show_quantity_field') >= 2) {
			$this->ajax = 'if(hikashopCheckChangeForm(\'item\',\'hikashop_product_form_'.$this->params->get('main_div_name').'\')){ return hikashopModifyQuantity(\'\',field,1,\'hikashop_product_form_'.$this->params->get('main_div_name').'\'); } return false;';
			$this->row = new stdClass();
			$this->row->product_quantity = -1;
			$this->row->product_min_per_order = 0;
			$this->row->product_max_per_order = -1;
			$this->row->product_sale_start = 0;
			$this->row->product_sale_end = 0;
			$this->row->prices = array('filler');
			$this->setLayout('quantity');
			echo $this->loadTemplate();

			if(!empty($this->ajax) && $this->config->get('redirect_url_after_add_cart','stay_if_cart')=='ask_user') {
?>
			<input type="hidden" name="popup" value="1"/>
<?php } ?>
			<input type="hidden" name="hikashop_cart_type_0" id="hikashop_cart_type_0" value="cart"/>
			<input type="hidden" name="add" value="1"/>
			<input type="hidden" name="ctrl" value="product"/>
			<input type="hidden" name="task" value="updatecart"/>
			<input type="hidden" name="return_url" value="<?php echo urlencode(base64_encode(urldecode($this->redirect_url)));?>"/>
		</form>
<?php
		}
	}
		?>
	</div>
<?php
	if(in_array($pagination,array('bottom','both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total) {
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
}
