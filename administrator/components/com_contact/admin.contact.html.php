<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	Contact
 */
class HTML_contact
{
	function showContacts(&$rows, &$pageNav, $option, &$lists)
	{
		$user = &JFactory::getUser();

		//Ordering allowed ?
		$ordering = ($lists['order'] == 'cd.ordering');

		JHtml::_('behavior.tooltip');
		?>
		<form action="index.php?option=com_contact" method="post" name="adminForm">

		<table>
		<tr>
			<td align="left" width="100%">
				<?php echo JText::_('Filter'); ?>:
				<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_('Go'); ?></button>
				<button onclick="document.getElementById('search').value='';this.form.getElementById('filter_catid').value='0';this.form.getElementById('filter_state').value='';this.form.submit();"><?php echo JText::_('Reset'); ?></button>
			</td>
			<td nowrap="nowrap">
				<?php
				echo $lists['catid'];
				echo $lists['state'];
				?>
			</td>
		</tr>
		</table>

			<table class="adminlist">
			<thead>
				<tr>
					<th width="10">
						<?php echo JText::_('Num'); ?>
					</th>
					<th width="10" class="title">
						<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($rows); ?>);" />
					</th>
					<th class="title">
						<?php echo JHtml::_('grid.sort',   'Name', 'cd.name', @$lists['order_Dir'], @$lists['order']); ?>
					</th>
					<th width="5%" class="title" nowrap="nowrap">
						<?php echo JHtml::_('grid.sort',   'Published', 'cd.published', @$lists['order_Dir'], @$lists['order']); ?>
					</th>
					<th nowrap="nowrap" width="8%">
						<?php echo JHtml::_('grid.sort',   'Order by', 'cd.ordering', @$lists['order_Dir'], @$lists['order']); ?>
						<?php if ($ordering) echo JHtml::_('grid.order',  $rows); ?>
					</th>
					<th width="8%" nowrap="nowrap">
						<?php echo JHtml::_('grid.sort',   'Access', 'cd.access', @$lists['order_Dir'], @$lists['order']); ?>
					</th>
					<th width="10%" class="title">
						<?php echo JHtml::_('grid.sort',   'Category', 'category', @$lists['order_Dir'], @$lists['order']); ?>
					</th>
					<th class="title" nowrap="nowrap" width="10%">
						<?php echo JHtml::_('grid.sort',   'Linked to User', 'user', @$lists['order_Dir'], @$lists['order']); ?>
					</th>
					<th width="1%" nowrap="nowrap">
						<?php echo JHtml::_('grid.sort',   'ID', 'cd.id', @$lists['order_Dir'], @$lists['order']); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="11">
						<?php echo $pageNav->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			for ($i=0, $n=count($rows); $i < $n; $i++) {
				$row = $rows[$i];

				$link 		= JRoute::_('index.php?option=com_contact&task=edit&cid[]='. $row->id);

				$checked 	= JHtml::_('grid.checkedout',   $row, $i);
				$published 	= JHtml::_('grid.published', $row, $i);

				$row->cat_link 	= JRoute::_('index.php?option=com_categories&extension=com_contact&task=edit&type=other&cid[]='. $row->catid);
				$row->user_link	= JRoute::_('index.php?option=com_users&task=editA&cid[]='. $row->user_id);
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo $pageNav->getRowOffset($i); ?>
					</td>
					<td>
						<?php echo $checked; ?>
					</td>
					<td>
					<?php
					if (JTable::isCheckedOut($user->get ('id'), $row->checked_out)) :
						echo $row->name;
					else :
						?>
						<span class="editlinktip hasTip" title="<?php echo JText::_('Edit Contact');?>::<?php echo $row->name; ?>">
						<a href="<?php echo $link; ?>">
							<?php echo $row->name; ?></a> </span>
						<?php
					endif;
					?>
					</td>
					<td align="center">
						<?php echo $published;?>
					</td>
					<td class="order">
						<span><?php echo $pageNav->orderUpIcon($i, ($row->catid == @$rows[$i-1]->catid), 'orderup', 'Move Up', $ordering); ?></span>
						<span><?php echo $pageNav->orderDownIcon($i, $n, ($row->catid == @$rows[$i+1]->catid), 'orderdown', 'Move Down', $ordering); ?></span>
						<?php $disabled = $ordering ?  '' : 'disabled="disabled"'; ?>
						<input type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
					</td>
					<td align="center">
						<?php echo $row->groupname;?>
					</td>
					<td>
						<a href="<?php echo $row->cat_link; ?>" title="<?php echo JText::_('Edit Category'); ?>">
							<?php echo $row->category; ?></a>
					</td>
					<td>
						<a href="<?php echo $row->user_link; ?>" title="<?php echo JText::_('Edit User'); ?>">
							<?php echo $row->user; ?></a>
					</td>
					<td align="center">
						<?php echo $row->id; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
			</table>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $lists['order_Dir']; ?>" />
		<?php echo JHtml::_('form.token'); ?>
		</form>
		<?php
	}

	function editContact(&$row, &$lists, $option, &$params) {

		JRequest::setVar('hidemainmenu', 1);

		if ($row->image == '') {
			$row->image = 'blank.png';
		}

		JHtml::_('behavior.tooltip');
		jimport('joomla.html.pane');
        // TODO: allowAllClose should default true in J!1.6, so remove the array when it does.
		$pane = &JPane::getInstance('sliders', array('allowAllClose' => true));

		JFilterOutput::objectHTMLSafe($row, ENT_QUOTES, 'misc');
		$cparams = JComponentHelper::getParams ('com_media');
		?>
		<script language="javascript" type="text/javascript">
		<!--
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform(pressbutton);
				return;
			}

			// do field validation
			if (form.name.value == "") {
				alert("<?php echo JText::_('You must provide a name.', true); ?>");
			} else if (form.catid.value == 0) {
				alert("<?php echo JText::_('Please select a Category.', true); ?>");
			} else {
				submitform(pressbutton);
			}
		}
		//-->
		</script>

		<form action="index.php" method="post" name="adminForm">

		<div class="col width-60">
			<fieldset class="adminform">
				<legend><?php echo JText::_('Details'); ?></legend>

				<table class="admintable">
				<tr>
					<td class="key">
						<label for="name">
							<?php echo JText::_('Name'); ?>:
						</label>
					</td>
					<td >
						<input class="inputbox" type="text" name="name" id="name" size="60" maxlength="255" value="<?php echo $row->name; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="name">
							<?php echo JText::_('Alias'); ?>:
						</label>
					</td>
					<td >
						<input class="inputbox" type="text" name="alias" id="alias" size="60" maxlength="255" value="<?php echo $row->alias; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('Published'); ?>:
					</td>
					<td>
						<?php echo $lists['published']; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="catid">
							<?php echo JText::_('Category'); ?>:
						</label>
					</td>
					<td>
						<?php echo $lists['catid'];?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="user_id">
							<?php echo JText::_('Linked to User'); ?>:
						</label>
					</td>
					<td >
						<?php echo $lists['user_id'];?>
					</td>
				</tr>
				<tr>
					<td valign="top" class="key">
						<label for="ordering">
							<?php echo JText::_('Ordering'); ?>:
						</label>
					</td>
					<td>
						<?php echo $lists['ordering']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" class="key">
						<label for="access">
							<?php echo JText::_('Access'); ?>:
						</label>
					</td>
					<td>
						<?php echo $lists['access']; ?>
					</td>
				</tr>
				<?php
				if ($row->id) {
					?>
					<tr>
						<td class="key">
							<label>
								<?php echo JText::_('ID'); ?>:
							</label>
						</td>
						<td>
							<strong><?php echo $row->id;?></strong>
						</td>
					</tr>
					<?php
				}
				?>
				</table>
			</fieldset>

			<fieldset class="adminform">
				<legend><?php echo JText::_('Information'); ?></legend>

				<table class="admintable">
				<tr>
					<td class="key">
					<label for="con_position">
						<?php echo JText::_('Contact\'s Position'); ?>:
						</label>
					</td>
					<td>
						<input class="inputbox" type="text" name="con_position" id="con_position" size="60" maxlength="255" value="<?php echo $row->con_position; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="email_to">
							<?php echo JText::_('E-mail'); ?>:
						</label>
					</td>
					<td>
						<input class="inputbox" type="text" name="email_to" id="email_to" size="60" maxlength="255" value="<?php echo $row->email_to; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key" valign="top">
						<label for="address">
							<?php echo JText::_('Street Address'); ?>:
							</label>
						</td>
						<td>
 							<textarea name="address" id="address" rows="3" cols="45" class="inputbox"><?php echo $row->address; ?></textarea>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="suburb">
							<?php echo JText::_('Town/Suburb'); ?>:
						</label>
					</td>
					<td>
						<input class="inputbox" type="text" name="suburb" id="suburb" size="60" maxlength="100" value="<?php echo $row->suburb;?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="state">
							<?php echo JText::_('State/County'); ?>:
						</label>
					</td>
					<td>
						<input class="inputbox" type="text" name="state" id="state" size="60" maxlength="100" value="<?php echo $row->state;?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="postcode">
							<?php echo JText::_('Postal Code/ZIP'); ?>:
						</label>
					</td>
					<td>
						<input class="inputbox" type="text" name="postcode" id="postcode" size="60" maxlength="100" value="<?php echo $row->postcode; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="country">
							<?php echo JText::_('Country'); ?>:
						</label>
					</td>
					<td>
						<input class="inputbox" type="text" name="country" id="country" size="60" maxlength="100" value="<?php echo $row->country;?>" />
					</td>
				</tr>
				<tr>
					<td class="key" valign="top">
					<label for="telephone">
					<?php echo JText::_('Telephone'); ?>:
						</label>
					</td>
					<td>
						<input class="inputbox" type="text" name="telephone" id="telephone" size="60" maxlength="255" value="<?php echo $row->telephone; ?>" />
  					</td>
				</tr>
				<tr>
					<td class="key" valign="top">
						<label for="mobile">
							<?php echo JText::_('Mobile'); ?>:
						</label>
					</td>
					<td>
 						<input class="inputbox" type="text" name="mobile" id="mobile" size="60" maxlength="255" value="<?php echo $row->mobile; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key" valign="top">
						<label for="fax">
							<?php echo JText::_('Fax'); ?>:
						</label>
					</td>
					<td>
						<input class="inputbox" type="text" name="fax" id="fax" size="60" maxlength="255" value="<?php echo $row->fax; ?>" />
 					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="webpage">
							<?php echo JText::_('Webpage'); ?>:
						</label>
					</td>
					<td>
						<input class="inputbox" type="text" name="webpage" id="webpage" size="60" maxlength="255" value="<?php echo $row->webpage; ?>" />
					</td>
				</tr>
				<tr>
					<td  class="key" valign="top">
						<label for="misc">
							<?php echo JText::_('Miscellaneous Info'); ?>:
						</label>
					</td>
					<td>
						<textarea name="misc" id="misc" rows="5" cols="45" class="inputbox"><?php echo $row->misc; ?></textarea>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="image">
							<?php echo JText::_('Image'); ?>:
						</label>
					</td>
					<td >
						<?php echo $lists['image']; ?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<script language="javascript" type="text/javascript">
						if (document.forms.adminForm.image.options.value!=''){
							jsimg='../<?php echo $cparams->get('image_path'); ?>/' + getSelectedValue('adminForm', 'image');
						} else {
							jsimg='../images/system/blank.png';
						}
						document.write('<img src=' + jsimg + ' name="imagelib" width="100" height="100" border="2" alt="<?php echo JText::_('Preview'); ?>" />');
						</script>
					</td>
				</tr>
				</table>
			</fieldset>
		</div>

		<div class="col width-40">
			<fieldset class="adminform">
				<legend><?php echo JText::_('Parameters'); ?></legend>

				<?php
					echo $pane->startPane("menu-pane");
					echo $pane->startPanel(JText :: _('Contact Parameters'), "param-page");
					echo $params->render();
					echo $pane->endPanel();
					echo $pane->startPanel(JText :: _('Advanced Parameters'), "param-page");
					echo $params->render('params', 'advanced');
					echo $pane->endPanel();
					echo $pane->startPanel(JText :: _('E-mail Parameters'), "param-page");
					echo $params->render('params', 'email');
					echo $pane->endPanel();
					echo $pane->endPane();
				?></fieldset>
		</div>
		<div class="clr"></div>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
		</form>
		<?php
	}
}