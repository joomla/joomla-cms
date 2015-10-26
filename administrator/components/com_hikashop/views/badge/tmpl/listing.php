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
<form action="<?php echo hikashop_completeLink('badge'); ?>" method="post"  name="adminForm" id="adminForm">
<?php if(HIKASHOP_BACK_RESPONSIVE) { ?>
	<div class="row-fluid">
		<div class="span8">
			<div class="input-prepend input-append">
				<span class="add-on"><i class="icon-filter"></i></span>
				<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" />
				<button class="btn" onclick="this.form.limitstart.value=0;this.form.submit();"><i class="icon-search"></i></button>
				<button class="btn" onclick="this.form.limitstart.value=0;document.getElementById('search').value='';this.form.submit();"><i class="icon-remove"></i></button>
			</div>
		</div>
		<div class="span4">
<?php } else { ?>
	<table>
		<tr>
			<td width="100%">
				<?php echo JText::_('FILTER'); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" />
				<button class="btn" onclick="this.form.limitstart.value=0;this.form.submit();"><?php echo JText::_('GO'); ?></button>
				<button class="btn" onclick="this.form.limitstart.value=0;document.getElementById('search').value='';this.form.submit();"><?php echo JText::_('RESET'); ?></button>
			</td>
			<td nowrap="nowrap">
<?php }

if(HIKASHOP_BACK_RESPONSIVE) { ?>
		</div>
	</div>
<?php } else { ?>
			</td>
		</tr>
	</table>
<?php } ?>
	<table id="hikashop_badge_listing" class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<th class="title titlebox">
					<?php echo JText::_('HIKA_IMAGE'); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'a.badge_name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('POSITION'), 'a.badge_position', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title titleorder">
				<?php echo JHTML::_('grid.sort', JText::_( 'HIKA_ORDER' ), 'a.badge_ordering',$this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
					<?php if ($this->order->ordering) echo JHTML::_('grid.order',  $this->rows ); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JHTML::_('grid.sort',   JText::_('HIKA_PUBLISHED'), 'a.badge_published', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JText::_('RESTRICTIONS'); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'a.badge_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="9">
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
				$k = 0;
				$a = count($this->rows);
				if($a){
					for($i = 0;$i<$a;$i++){
						$row =& $this->rows[$i];
						$publishedid = 'badge_published-'.$row->badge_id;
				?>
					<tr class="<?php echo "row$k"; ?>">
						<td align="center">
						<?php echo $this->pagination->getRowOffset($i); ?>
						</td>
						<td align="center">
							<?php echo JHTML::_('grid.id', $i, $row->badge_id ); ?>
						</td>
						<td>
							<?php echo $this->image->display(@$row->badge_image,true,"",'','', 100, 100); ?>
						</td>
						<td>
							<?php if($this->manage){ ?>
								<a href="<?php echo hikashop_completeLink('badge&task=edit&cid[]='.$row->badge_id); ?>">
							<?php } ?>
									<?php echo $row->badge_name; ?>
							<?php if($this->manage){ ?>
								</a>
							<?php } ?>
						</td>
						<td>
									<?php echo $row->badge_position; ?>
						</td>
						<td class="order">
							<?php if($this->manage){ ?>
								<span><?php echo $this->pagination->orderUpIcon( $i, $this->order->reverse XOR ( $row->badge_ordering >= @$this->rows[$i-1]->badge_ordering ), $this->order->orderUp, 'Move Up',$this->order->ordering ); ?></span>
								<span><?php echo $this->pagination->orderDownIcon( $i, $a, $this->order->reverse XOR ( $row->badge_ordering <= @$this->rows[$i+1]->badge_ordering ), $this->order->orderDown, 'Move Down' ,$this->order->ordering); ?></span>
								<input type="text" name="order[]" size="5" <?php if(!$this->order->ordering) echo 'disabled="disabled"'?> value="<?php echo $row->badge_ordering; ?>" class="text_area" style="text-align: center" />
							<?php }else{ echo $row->badge_ordering; } ?>
						</td>
						<td align="center">
							<?php if($this->manage){ ?>
								<span id="<?php echo $publishedid ?>" class="spanloading"><?php echo $this->toggleClass->toggle($publishedid,(int) $row->badge_published,'badge') ?></span>
							<?php }else{ echo $this->toggleClass->display('activate',$row->badge_published); } ?>
						</td>
						<td>

							<?php
								$restrictions=array();
								if(!empty($row->badge_discount_id)){
									$restrictions[]='<b>'.JText::_('DISCOUNT').'</b>:'.$row->badge_discount_id;
								}
								if(!empty($row->badge_product_id)){
									$restrictions[]='<b>'.JText::_('PRODUCT').'</b>:'.$row->badge_product_id;
								}
								if(!empty($row->badge_category_id)){
									$restriction='<b>'.JText::_('CATEGORY').'</b>:'.$row->badge_category_id;
									if($row->badge_category_childs){
										$restriction.='</br>'.JText::_('INCLUDING_SUB_CATEGORIES');
									}
									$restrictions[]=$restriction;
								}
								echo implode('<br/>',$restrictions);
							?>
						</td>
						<td width="1%" align="center">
							<?php echo $row->badge_id; ?>
						</td>
					</tr>
				<?php
						$k = 1-$k;
					}
				}
			?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
