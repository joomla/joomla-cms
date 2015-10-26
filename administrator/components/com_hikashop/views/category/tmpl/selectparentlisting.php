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
<?php if($this->config->get('category_explorer')){?>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
<div id="page-product">
	<table style="width:100%">
		<tr>
			<td style="vertical-align:top;border:1px solid #CCC;background-color: #F3F3F3" width="150px">
				<?php echo hikashop_setExplorer('category&task=selectparentlisting&control='.$this->control.'&id='.$this->id,$this->pageInfo->filter->filter_id,true,$this->type); ?>
			</td>
			<td style="vertical-align:top;">
<?php } else { ?>
<div id="page-product" class="row-fluid">
	<div class="span4">
		<?php echo hikashop_setExplorer('category&task=selectparentlisting&control='.$this->control.'&id='.$this->id,$this->pageInfo->filter->filter_id,true,$this->type); ?>
	</div>
	<div class="span8">
<?php } ?>
<?php } ?>
			<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=category" method="post"  name="adminForm" id="adminForm">
				<table>
					<tr>
						<td width="100%">
							<a href="<?php echo hikashop_completeLink('category&task=selectparentlisting&filter_id=0&control='.$this->control.'&id='.$this->id,true); ?>"><?php echo JText::_( 'ROOT' ); ?>/</a>
							<?php echo $this->breadCrumb.'<br/>'.JText::_( 'FILTER' ); ?>:
							<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" onchange="document.adminForm.submit();" />
							<button class="btn" onclick="this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
							<button class="btn" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
						</td>
						<td nowrap="nowrap">
							<?php echo $this->childDisplay; ?>
						</td>
					</tr>
				</table>
				<table class="adminlist table table-striped table-hover" cellpadding="1">
					<thead>
						<tr>
							<th class="title titlenum">
								<?php echo JText::_( 'HIKA_NUM' );?>
							</th>
							<th class="title titlebox">
								<?php echo JText::_('USE'); ?>
							</th>
							<th class="title">
								<?php echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'a.category_name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value,'selectparentlisting' ); ?>
							</th>
							<th class="title">
								<?php echo JText::_('SHOW_SUB_CATEGORIES'); ?>
							</th>
							<th class="title">
								<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'a.category_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value,'selectparentlisting' ); ?>
							</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="5">
								<?php echo $this->pagination->getListFooter(); ?>
								<?php echo $this->pagination->getResultsCounter(); ?>
							</td>
						</tr>
					</tfoot>
					<tbody>
						<?php
							$k = 0;
							for($i = 0,$a = count($this->rows);$i<$a;$i++){
								$row =& $this->rows[$i];
						?>
							<tr class="<?php echo "row$k"; ?>">
								<td align="center">
								<?php echo $this->pagination->getRowOffset($i); ?>
								</td>
								<td width="1%" align="center">
									<a href="#" onclick="changeParent(<?php echo $row->category_id;?>,'<?php echo str_replace("'","\'",$this->escape($row->category_name));?>');window.top.hikashop.closeBox();">
										<img src="<?php echo HIKASHOP_IMAGES; ?>add.png" alt="edit"/>
									</a>
								</td>
								<td>
									<a href="#" onclick="changeParent(<?php echo $row->category_id;?>,'<?php echo str_replace("'","\'",$this->escape($row->category_name));?>');window.top.hikashop.closeBox();">
										<?php echo $row->category_name; ?>
									</a>
								</td>
								<td>
									<a href="<?php echo hikashop_completeLink('category&task=selectparentlisting&filter_id='.$row->category_id.'&control='.$this->control.'&id='.$this->id,true); ?>">
										<img src="<?php echo HIKASHOP_IMAGES; ?>go.png" alt="edit"/>
									</a>
								</td>
								<td width="1%" align="center">
									<?php echo $row->category_id; ?>
								</td>
							</tr>
						<?php
								$k = 1-$k;
							}
						?>
					</tbody>
				</table>
				<input type="hidden" name="control" value="<?php echo $this->control; ?>" />
				<input type="hidden" name="id" value="<?php echo $this->id; ?>" />
				<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
				<input type="hidden" name="task" value="<?php echo JRequest::getCmd('task'); ?>" />
				<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="tmpl" value="component" />
				<input type="hidden" id="filter_id" name="filter_id" value="<?php echo $this->pageInfo->filter->filter_id; ?>" />
				<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
				<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
				<?php echo JHTML::_( 'form.token' ); ?>
			</form>
<?php if($this->config->get('category_explorer')){?>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
		</tr>
	</table>
</div>
<?php } else { ?>
	</div>
</div>
<?php } ?>
<?php } ?>
