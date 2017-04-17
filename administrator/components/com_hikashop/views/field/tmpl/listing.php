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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=field" method="post"  name="adminForm" id="adminForm">
	<?php if(hikashop_level(1)){ ?>
		<table>
			<tr>
				<td width="100%">
				</td>
				<td nowrap="nowrap">
					<?php echo $this->tabletype->display('filter_table',$this->selectedType); ?>
				</td>
			</tr>
		</table>
	<?php } ?>
	<table id="hikashop_field_listing" class="adminlist table table-striped" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<?php if(hikashop_level(1)){ ?>
					<th id="hikashop_field_table_title" class="title">
						<?php echo JText::_('FIELD_TABLE'); ?>
					</th>
				<?php } ?>
				<th class="title">
					<?php echo JText::_('FIELD_COLUMN'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('FIELD_LABEL'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('FIELD_TYPE'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('REQUIRED'); ?>
				</th>
				<th class="title titleorder">
					<?php echo JText::_('HIKA_ORDER'); echo JHTML::_('grid.order',  $this->rows );?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('DISPLAY_FRONTCOMP'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('DISPLAY_BACKEND_FORM'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('DISPLAY_BACKEND_LISTING'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('HIKA_PUBLISHED'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('CORE'); ?>
				</th>
				<th class="title titleid">
					<?php echo JText::_( 'ID' ); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$k = 0;

				for($i = 0,$a = count($this->rows);$i<$a;$i++){
					$row =& $this->rows[$i];

					$publishedid = 'field_published-'.$row->field_id;
					$requiredid = 'field_required-'.$row->field_id;
					$backendid = 'field_backend-'.$row->field_id;
					$backendlistingid = 'field_backend_listing-'.$row->field_id;
					$frontcompid = 'field_frontcomp-'.$row->field_id;
			?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="center">
					<?php echo $i+1; ?>
					</td>
					<td align="center">
						<?php echo JHTML::_('grid.id', $i, $row->field_id ); ?>
					</td>
					<?php if(hikashop_level(1)){ ?>
						<td class="hikashop_field_table_value">
							<?php echo $row->field_table; ?>
						</td>
					<?php } ?>
					<td>
						<?php if($this->manage){ ?>
							<a href="<?php echo hikashop_completeLink('field&task=edit&cid[]='.$row->field_id); ?>">
						<?php } ?>
								<?php echo $row->field_namekey; ?>
						<?php if($this->manage){ ?>
							</a>
						<?php } ?>
					</td>
					<td>
						<?php echo $this->fieldsClass->trans($row->field_realname); ?>
					</td>
					<td>
						<?php
							if(isset($this->fieldtype->allValues[$row->field_type])) echo $this->fieldtype->allValues[$row->field_type]['name'];
							else echo $row->field_type; ?>
					</td>
					<td align="center">
						<?php if($this->manage){ ?>
							<span id="<?php echo $requiredid ?>" class="loading"><?php echo $this->toggleClass->toggle($requiredid,(int) $row->field_required,'field') ?></span>
						<?php }else{ echo $this->toggleClass->display('activate',$row->field_required); } ?>
					</td>
					<td class="order">
						<?php if($this->manage){ ?>
							<span><?php echo $this->pagination->orderUpIcon( $i, $row->field_ordering >= @$this->rows[$i-1]->field_ordering ,'orderup', 'Move Up',true ); ?></span>
							<span><?php echo $this->pagination->orderDownIcon( $i, $a, $row->field_ordering <= @$this->rows[$i+1]->field_ordering , 'orderdown', 'Move Down' ,true); ?></span>
							<input type="text" name="order[]" size="5" value="<?php echo $row->field_ordering; ?>" class="text_area" style="text-align: center" />
						<?php }else{ $row->field_ordering; } ?>
					</td>
					<td align="center">
						<?php if($this->manage){ ?>
							<span id="<?php echo $frontcompid ?>" class="loading"><?php echo $this->toggleClass->toggle($frontcompid,(int) $row->field_frontcomp,'field') ?></span>
						<?php }else{ echo $this->toggleClass->display('activate',$row->field_frontcomp); } ?>
					</td>
					<td align="center">
						<?php if($this->manage){ ?>
							<span id="<?php echo $backendid ?>" class="loading"><?php echo $this->toggleClass->toggle($backendid,(int) $row->field_backend,'field') ?></span>
						<?php }else{ echo $this->toggleClass->display('activate',$row->field_backend); } ?>
					</td>
					<td align="center">
						<?php if($row->field_table=='address'){
							echo '--';
						}else{?>
							<?php if($this->manage){ ?>
								<span id="<?php echo $backendlistingid ?>" class="loading"><?php echo $this->toggleClass->toggle($backendlistingid,(int) $row->field_backend_listing,'field') ?></span>
							<?php }else{ echo $this->toggleClass->display('activate',$row->field_backend_listing); } ?>
						<?php } ?>
					</td>
					<td align="center">
						<?php if($this->manage){ ?>
							<span id="<?php echo $publishedid ?>" class="loading"><?php echo $this->toggleClass->toggle($publishedid,(int) $row->field_published,'field') ?></span>
						<?php }else{ echo $this->toggleClass->display('activate',$row->field_published); } ?>
					</td>
					<td align="center">
						<?php echo $this->toggleClass->display('activate',$row->field_core); ?>
					</td>
					<td width="1%" align="center">
						<?php echo $row->field_id; ?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			?>
		</tbody>
	</table>

	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="field" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
