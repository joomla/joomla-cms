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
if(!empty($this->rows)){
	$app = JFactory::getApplication();
	$pagination = $this->config->get('pagination','bottom');
	if(in_array($pagination,array('top','both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total){ $this->pagination->form = '_top'; ?>
	<form action="<?php echo hikashop_currentURL(); ?>" method="post" name="adminForm_<?php echo $this->params->get('main_div_name').$this->category_selected;?>_top">
		<div class="hikashop_products_pagination hikashop_products_pagination_top">
		<?php echo $this->pagination->getListFooter($this->params->get('limit')); ?>
		<span class="hikashop_results_counter"><?php echo $this->pagination->getResultsCounter(); ?></span>
		</div>
		<input type="hidden" name="filter_order_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
		<input type="hidden" name="filter_order_Dir_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
	<?php } ?>
	<div class="hikashop_products">
	<?php
		if ($this->config->get('show_quantity_field')>=2) { ?>
			<form action="<?php echo hikashop_completeLink('product&task=updatecart'); ?>" method="post" name="hikashop_product_form_<?php echo $this->params->get('main_div_name'); ?>" enctype="multipart/form-data">
		<?php }
		$columns = 1; ?>
		<table class="hikashop_products_table adminlist table table-striped table-hover" cellpadding="1">
			<thead>
				<tr>
					<?php if($this->config->get('thumbnail')){ $columns++; ?>
						<th class="hikashop_product_image title" align="center">
							<?php echo JText::_( 'HIKA_IMAGE' );?>
						</th>
					<?php } ?>
					<th class="hikashop_product_name title" align="center">
						<?php echo JText::_( 'PRODUCT' );?>
					</th>

					<?php if ($this->config->get('show_code')) { $columns++;?>
						<th class="hikashop_product_code title" align="center">
							<?php echo JText::_( 'PRODUCT_CODE' ); ?>
						</th>
					<?php } ?>
					<?php if($this->params->get('show_vote_product')){ ?>
						<th class="hikashop_product_vote title" align="center">
							<?php echo JText::_('VOTE'); ?>
						</th>
					<?php } ?>
					<?php
						if($this->params->get('show_price','-1')=='-1'){
							$config =& hikashop_config();
							$this->params->set('show_price',$config->get('show_price'));
						}
						if($this->params->get('show_price')){ $columns++; ?>
						<th class="hikashop_product_price title" align="center">
							<?php echo JText::_('PRICE'); ?>
						</th>
					<?php } ?>
					<?php if($this->params->get('add_to_cart') || $this->params->get('add_to_wishlist')){ $columns++; ?>
						<th class="hikashop_product_add_to_cart title" align="center">
						</th>
					<?php } ?>
					<?php if(JRequest::getVar('hikashop_front_end_main',0) && JRequest::getVar('task')=='listing' && $this->params->get('show_compare')) { $columns++; ?>
						<th class="hikashop_product_compare title" align="center">
						</th>
					<?php } ?>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="<?php echo $columns; ?>">

					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php foreach($this->rows as $row){
				$this->row =& $row;
				$height = $this->params->get('image_height');
				$width = $this->params->get('image_width');
				if(empty($height)) $height=$this->config->get('thumbnail_y');
				if(empty($width)) $width=$this->config->get('thumbnail_x');
				$divWidth=$width;
				$divHeight=$height;
				$this->image->checkSize($divWidth,$divHeight,$row);
				$link = hikashop_contentLink('product&task=show&cid='.$this->row->product_id.'&name='.$this->row->alias.$this->itemid.$this->category_pathway,$this->row);
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
				<tr>
					<?php if($this->config->get('thumbnail')){ ?>
						<td class="hikashop_product_image_row">
							<div style="height:<?php echo $divHeight;?>px;text-align:center;clear:both;" class="hikashop_product_image">
								<div style="position:relative;text-align:center;clear:both;width:<?php echo $divWidth;?>px;margin: auto;" class="hikashop_product_image_subdiv">
								<?php if($this->params->get('link_to_product_page',1)){ ?>
									<a href="<?php echo $link;?>" title="<?php echo $this->escape($this->row->product_name); ?>">
								<?php }
									$image_options = array('default' => true,'forcesize'=>$this->config->get('image_force_size',true),'scale'=>$this->config->get('image_scale_mode','inside'));
									$img = $this->image->getThumbnail(@$this->row->file_path, array('width' => $this->image->main_thumbnail_x, 'height' => $this->image->main_thumbnail_y), $image_options);
									if($img->success) {
										echo '<img class="hikashop_product_listing_image" title="'.$this->escape(@$this->row->file_description).'" alt="'.$this->escape(@$this->row->file_name).'" src="'.$img->url.'"/>';
									}
									$main_thumb_x = $this->image->main_thumbnail_x;
									$main_thumb_y = $this->image->main_thumbnail_y;
									if($this->params->get('display_badges',1)){
										$this->classbadge->placeBadges($this->image, $this->row->badges, -10, 0);
									}
									$this->image->main_thumbnail_x = $main_thumb_x;
									$this->image->main_thumbnail_y = $main_thumb_y;

								if($this->params->get('link_to_product_page',1)){ ?>
									</a>
								<?php } ?>
								</div>
							</div>
						</td>
					<?php } ?>
					<td class="hikashop_product_name_row">
						<span class="hikashop_product_name">
							<?php if($this->params->get('link_to_product_page',1)){ ?>
								<a href="<?php echo $link;?>">
							<?php }
								echo $this->row->product_name;
							if($this->params->get('link_to_product_page',1)){ ?>
								</a>
							<?php } ?>
						</span>
						<?php if(!empty($this->row->extraData->afterProductName)) { echo implode("\r\n",$this->row->extraData->afterProductName); } ?>
					</td>

					<?php if ($this->config->get('show_code')){ ?>
						<td class="hikashop_product_code_row">
							<?php if($this->params->get('link_to_product_page',1)){ ?>
								<a href="<?php echo $link;?>">
							<?php }
							echo $this->row->product_code;
							if($this->params->get('link_to_product_page',1)){ ?>
								</a>
							<?php } ?>
						</td>
					<?php } ?>
					<?php if($this->params->get('show_vote_product')){ ?>
						<td class="hikashop_product_vote_row">
							<?php
							$this->row =& $row;
							$this->setLayout('listing_vote');
							echo $this->loadTemplate();
							?>
						</td>
					<?php } ?>
					<?php
						if($this->params->get('show_price','-1')=='-1'){
							$config =& hikashop_config();
							$this->params->set('show_price',$config->get('show_price'));
						}
						if($this->params->get('show_price')){ ?>
						<td class="hikashop_product_price_row">
						<?php
							$this->setLayout('listing_price');
							echo $this->loadTemplate();
						?>
						</td>
					<?php } ?>

					<?php if($this->params->get('add_to_cart')){ ?>
						<td class="hikashop_product_add_to_cart_row">
							<?php
								$this->setLayout('add_to_cart_listing');
								echo $this->loadTemplate();
							?>
						</td>
					<?php } ?>

					<?php
					if(JRequest::getVar('hikashop_front_end_main',0) && JRequest::getVar('task')=='listing' && $this->params->get('show_compare')) {
						if( $this->params->get('show_compare') == 1 ) {
					?>
						<td class="hikashop_product_compare_row">
							<?php
							$js = 'setToCompareList('.$this->row->product_id.',\''.$this->escape($this->row->product_name).'\',this); return false;';
							echo $this->cart->displayButton(JText::_('ADD_TO_COMPARE_LIST'),'compare',$this->params,$link,$js,'',0,1,'hikashop_compare_button');
							?>
						</td>
					<?php } else { ?>
						<td class="hikashop_product_compare_row">
							<input type="checkbox" class="hikashop_compare_checkbox" id="hikashop_listing_chk_<?php echo $this->row->product_id;?>" onchange="setToCompareList(<?php echo $this->row->product_id;?>,'<?php echo $this->escape($this->row->product_name); ?>',this);"><label for="hikashop_listing_chk_<?php echo $this->row->product_id;?>"><?php echo JText::_('ADD_TO_COMPARE_LIST'); ?></label>
						</td>
					<?php }
					} ?>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<?php if ($this->config->get('show_quantity_field')>=2) {
				$this->ajax = 'if(hikashopCheckChangeForm(\'item\',\'hikashop_product_form_'.$this->params->get('main_div_name').'\')){ return hikashopModifyQuantity(\'\',field,1,\'hikashop_product_form_'.$this->params->get('main_div_name').'\'); } return false;';
				$this->row = null;
				$this->row->product_quantity = -1;
				$this->row->product_min_per_order = 0;
				$this->row->product_max_per_order = -1;
				$this->row->product_sale_start = 0;
				$this->row->product_sale_end = 0;
				$this->row->prices = array('filler');
				$this->setLayout('quantity');
				echo $this->loadTemplate();
				if(!empty($this->ajax) && $this->config->get('redirect_url_after_add_cart','stay_if_cart')=='ask_user'){ ?>
					<input type="hidden" name="popup" value="1"/>
				<?php } ?>
				<input type="hidden" name="hikashop_cart_type_0" id="hikashop_cart_type_0" value="cart"/>
				<input type="hidden" name="add" value="1"/>
				<input type="hidden" name="ctrl" value="product"/>
				<input type="hidden" name="task" value="updatecart"/>
				<input type="hidden" name="return_url" value="<?php echo urlencode(base64_encode(urldecode($this->redirect_url)));?>"/>
				</form>
		<?php }
		if(in_array($pagination,array('bottom','both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total){ $this->pagination->form = '_bottom'; ?>
			<form action="<?php echo hikashop_currentURL(); ?>" method="post" name="adminForm_<?php echo $this->params->get('main_div_name').$this->category_selected;?>_bottom">
				<div class="hikashop_products_pagination hikashop_products_pagination_bottom">
				<?php echo $this->pagination->getListFooter($this->params->get('limit')); ?>
				<span class="hikashop_results_counter"><?php echo $this->pagination->getResultsCounter(); ?></span>
				</div>
				<input type="hidden" name="filter_order_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
				<input type="hidden" name="filter_order_Dir_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
				<?php echo JHTML::_( 'form.token' ); ?>
			</form>
		<?php } ?>
	</div>
<?php } ?>
