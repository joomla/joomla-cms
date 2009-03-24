<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php
	jimport('joomla.utilities.date');

	// Initialize variables
	$db		=& JFactory::getDBO();
	$user	=& JFactory::getUser();
	$config	=& JFactory::getConfig();
	$now	=& JFactory::getDate();
	$nullDate 	= $db->getNullDate();

	//Ordering allowed ?
	$ordering = ($this->filter->order == 'name' || $this->filter->order == 'cc.name');
	JHtml::_('behavior.tooltip');
?>

<form action="<?php echo JRoute::_('index.php?option=com_content'); ?>" method="post" name="adminForm">
	<table>
		<tr>
			<td width="100%">
				<?php echo JText::_('Filter'); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->filter->search;?>" class="text_area" onchange="document.adminForm.submit();" title="<?php echo JText::_('Filter by title or enter article ID');?>"/>
				<button onclick="this.form.submit();"><?php echo JText::_('Go'); ?></button>
				<button onclick="document.getElementById('search').value='';this.form.getElementById('filter_sectionid').value='-1';this.form.getElementById('filter_catid').value='0';this.form.getElementById('filter_authorid').value='0';this.form.getElementById('filter_state').value='';this.form.submit();"><?php echo JText::_('Reset'); ?></button>
			</td>
			<td nowrap="nowrap">
				<?php
				echo JHtml::_('list.category', 'filter_catid', 'com_content', NULL, (int) $this->filter->catid, ' onchange="submitform();"', 1, 1, 1);
				echo JHtml::_('contentgrid.author', 'filter_authorid', $this->filter->authorid);
				echo JHtml::_('grid.state', $this->filter->state, 'Published', 'Unpublished', 'Archived');
				?>
			</td>
		</tr>
	</table>

	<table class="adminlist" cellspacing="1">
	<thead>
		<tr>
			<th width="5">
				<?php echo JText::_('Num'); ?>
			</th>
			<th width="5">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->rows); ?>);" />
			</th>
			<th class="title">
				<?php echo JHtml::_('grid.sort',   'Title', 'c.title', @$this->filter->order_Dir, @$this->filter->order); ?>
			</th>
			<th width="1%" nowrap="nowrap">
				<?php echo JHtml::_('grid.sort',   'Published', 'c.state', @$this->filter->order_Dir, @$this->filter->order); ?>
			</th>
			<th nowrap="nowrap" width="1%">
				<?php echo JHtml::_('grid.sort',   'Front Page', 'frontpage', @$this->filter->order_Dir, @$this->filter->order); ?>
			</th>
			<th width="12%">
				<?php echo JHtml::_('grid.sort',   'Order', 'section_name', @$this->filter->order_Dir, @$this->filter->order); ?>
				<?php echo JHtml::_('grid.order',  $this->rows); ?>
			</th>
			<th width="7%">
				<?php echo JHtml::_('grid.sort',   'Access', 'groupname', @$this->filter->order_Dir, @$this->filter->order); ?>
			</th>
			<th  class="title" width="8%" nowrap="nowrap">
				<?php echo JHtml::_('grid.sort',   'Category', 'cc.name', @$this->filter->order_Dir, @$this->filter->order); ?>
			</th>
			<th  class="title" width="8%" nowrap="nowrap">
				<?php echo JHtml::_('grid.sort',   'Author', 'author', @$this->filter->order_Dir, @$this->filter->order); ?>
			</th>
			<th align="center" width="10">
				<?php echo JHtml::_('grid.sort',   'Date', 'c.created', @$this->filter->order_Dir, @$this->filter->order); ?>
			</th>
			<th align="center" width="10">
				<?php echo JHtml::_('grid.sort',   'Hits', 'c.hits', @$this->filter->order_Dir, @$this->filter->order); ?>
			</th>
			<th width="1%" class="title">
				<?php echo JHtml::_('grid.sort',   'ID', 'c.id', @$this->filter->order_Dir, @$this->filter->order); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
	<tr>
		<td colspan="15">
			<?php echo $this->pagination->getListFooter(); ?>
		</td>
	</tr>
	</tfoot>
	<tbody>
	<?php
	$k = 0;
	for ($i=0, $n=count($this->rows); $i < $n; $i++)
	{
		$row = &$this->rows[$i];

		$link 	= 'index.php?option=com_content&sectionid='. $this->redirect .'&task=edit&cid[]='. $row->id;

		$row->sect_link = JRoute::_('index.php?option=com_sections&task=edit&cid[]='. $row->sectionid);
		$row->cat_link 	= JRoute::_('index.php?option=com_categories&task=edit&cid[]='. $row->catid);

		$publish_up = new JDate($row->publish_up);
		$publish_down = new JDate($row->publish_down);
		$publish_up->setOffset($config->getValue('config.offset'));
		$publish_down->setOffset($config->getValue('config.offset'));
		if ($now->toUnix() <= $publish_up->toUnix() && $row->state == 1) {
			$img = 'publish_y.png';
			$alt = JText::_('Published');
		} else if (($now->toUnix() <= $publish_down->toUnix() || $row->publish_down == $nullDate) && $row->state == 1) {
			$img = 'publish_g.png';
			$alt = JText::_('Published');
		} else if ($now->toUnix() > $publish_down->toUnix() && $row->state == 1) {
			$img = 'publish_r.png';
			$alt = JText::_('Expired');
		} else if ($row->state == 0) {
			$img = 'publish_x.png';
			$alt = JText::_('Unpublished');
		} else if ($row->state == -1) {
			$img = 'disabled.png';
			$alt = JText::_('Archived');
		}
		$times = '';
		if (isset($row->publish_up)) {
			if ($row->publish_up == $nullDate) {
				$times .= JText::_('Start: Always');
			} else {
				$times .= JText::_('Start') .": ". $publish_up->toFormat();
			}
		}
		if (isset($row->publish_down)) {
			if ($row->publish_down == $nullDate) {
				$times .= "<br />". JText::_('Finish: No Expiry');
			} else {
				$times .= "<br />". JText::_('Finish') .": ". $publish_down->toFormat();
			}
		}

		if ($user->authorize('com_users', 'manage')) {
			if ($row->created_by_alias) {
				$author = $row->created_by_alias;
			} else {
				$linkA 	= 'index.php?option=com_users&task=edit&cid[]='. $row->created_by;
				$author = '<a href="'. JRoute::_($linkA) .'" title="'. JText::_('Edit User') .'">'. $row->author .'</a>';
			}
		} else {
			if ($row->created_by_alias) {
				$author = $row->created_by_alias;
			} else {
				$author = $row->author;
			}
		}

		$checked 	= JHtml::_('grid.checkedout',   $row, $i);
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td>
				<?php echo $this->pagination->getRowOffset($i); ?>
			</td>
			<td align="center">
				<?php echo $checked; ?>
			</td>
			<td>
			<?php
				if ( JTable::isCheckedOut($user->get ('id'), $row->checked_out)) {
					echo $row->title;
				} else if ($row->state == -1) {
					echo htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8');
					echo ' [ '. JText::_('Archived') .' ]';
				} else {
					?>
					<a href="<?php echo JRoute::_($link); ?>">
						<?php echo htmlspecialchars($row->title, ENT_QUOTES); ?></a>
					<?php
				}
				?>
				<div style="float:right"><?php echo JHtml::_('content.warnings', $row);?></div>
			</td>
			<?php
			if ($times) {
				?>
				<td align="center">
					<span class="editlinktip hasTip" title="<?php echo JText::_('Publish Information');?>::<?php echo $times; ?>"><a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i;?>','<?php echo $row->state ? 'unpublish' : 'publish' ?>')">
						<img src="images/<?php echo $img;?>" width="16" height="16" border="0" alt="<?php echo $alt; ?>" /></a></span>
				</td>
				<?php
			}
			?>
			<td align="center">
				<a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i;?>','toggle_frontpage')" title="<?php echo ($row->frontpage) ? JText::_('Yes') : JText::_('No');?>">
					<img src="images/<?php echo ($row->frontpage) ? 'tick.png' : ($row->state != -1 ? 'publish_x.png' : 'disabled.png');?>" width="16" height="16" border="0" alt="<?php echo ($row->frontpage) ? JText::_('Yes') : JText::_('No');?>" /></a>
			</td>
			<td class="order">
				<span><?php echo $this->pagination->orderUpIcon($i, ($row->catid == @$this->rows[$i-1]->catid), 'orderup', 'Move Up', $ordering); ?></span>
				<span><?php echo $this->pagination->orderDownIcon($i, $n, ($row->catid == @$this->rows[$i+1]->catid), 'orderdown', 'Move Down', $ordering); ?></span>
				<?php $disabled = $ordering ?  '' : 'disabled="disabled"'; ?>
				<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo $disabled; ?> class="text_area" style="text-align: center" />
			</td>
			<td align="center">
				<?php echo $row->groupname;?>
			</td>
			<td>
				<a href="<?php echo $row->cat_link; ?>" title="<?php echo JText::_('Edit Category'); ?>">
					<?php echo $row->name; ?></a>
			</td>
			<td>
				<?php echo $author; ?>
			</td>
			<td nowrap="nowrap">
				<?php echo JHtml::_('date',  $row->created, JText::_('DATE_FORMAT_LC4')); ?>
			</td>
			<td nowrap="nowrap" align="center">
				<?php echo $row->hits ?>
			</td>
			<td>
				<?php echo $row->id; ?>
			</td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	</tbody>
	</table>
	<?php JHtml::_('content.legend'); ?>

<input type="hidden" name="option" value="com_content" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="redirect" value="<?php echo $this->redirect;?>" />
<input type="hidden" name="filter_order" value="<?php echo $this->filter->order; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->filter->order_Dir; ?>" />
<?php echo JHtml::_('form.token'); ?>
</form>
