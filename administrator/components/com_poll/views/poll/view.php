<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Config
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package		Joomla
* @subpackage	Config
*/
class PollView
{
	function showPolls( &$rows, &$pageNav, $option, &$lists )
	{
		JHTML::_('behavior.tooltip');
		?>
		<form action="index.php?option=com_poll" method="post" name="adminForm">

		<table>
		<tr>
			<td align="left" width="100%">
				<?php echo JText::_( 'Filter' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
				<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
			</td>
			<td nowrap="nowrap">
				<?php
				echo $lists['state'];
				?>
			</td>
		</tr>
		</table>

		<div id="tablecell">
			<table class="adminlist">
			<thead>
				<tr>
					<th width="5">
						<?php echo JText::_( 'NUM' ); ?>
					</th>
					<th width="20">
						<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows ); ?>);" />
					</th>
					<th  class="title">
						<?php echo JHTML::_('grid.sort',   'Poll Title', 'm.title', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
					<th width="8%" align="center">
						<?php echo JHTML::_('grid.sort',   'Published', 'm.published', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
					<th width="8%" align="center">
						<?php echo JHTML::_('grid.sort',   'Votes', 'm.voters', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
					<th width="8%" align="center">
						<?php echo JHTML::_('grid.sort',   'Options', 'numoptions', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
					<th width="8%" align="center">
						<?php echo JHTML::_('grid.sort',   'Lag', 'm.lag', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
					<th width="1%" nowrap="nowrap">
						<?php echo JHTML::_('grid.sort',   'ID', 'm.id', @$lists['order_Dir'], @$lists['order'] ); ?>
					</th>
				</tr>
			</thead>
			<?php
			$k = 0;
			for ($i=0, $n=count( $rows ); $i < $n; $i++)
			{
				$row = &$rows[$i];

				$link 		= JRoute::_( 'index.php?option=com_poll&task=edit&cid[]='. $row->id );

				$checked 	= JHTML::_('grid.checkedout',   $row, $i );
				$published 	= JHTML::_('grid.published', $row, $i );
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo $pageNav->getRowOffset( $i ); ?>
					</td>
					<td>
						<?php echo $checked; ?>
					</td>
					<td>
						<a href="<?php echo $link; ?>" title="<?php echo JText::_( 'Edit Poll' ); ?>">
							<?php echo $row->title; ?></a>
					</td>
					<td align="center">
						<?php echo $published;?>
					</td>
					<td align="center">
						<?php echo $row->voters; ?>
					</td>
					<td align="center">
						<?php echo $row->numoptions; ?>
					</td>
					<td align="center">
						<?php echo $row->lag; ?>
					</td>
					<td align="center">
						<?php echo $row->id; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			<tfoot>
				<td colspan="8">
					<?php echo $pageNav->getListFooter(); ?>
				</td>
			</tfoot>
			</table>
		</div>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}


	function editPoll( &$row, &$options )
	{
		JRequest::setVar( 'hidemainmenu', 1 );

		jimport('joomla.filter.output');
		JOutputFilter::objectHTMLSafe( $row, ENT_QUOTES );
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton)
		{
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}
			// do field validation
			if (form.title.value == "") {
				alert( "<?php echo JText::_( 'Poll must have a title', true ); ?>" );
			} else if( isNaN( parseInt( form.lag.value ) ) ) {
				alert( "<?php echo JText::_( 'Poll must have a non-zero lag time', true ); ?>" );
			//} else if (form.menu.options.value == ""){
			//	alert( "Poll must have pages." );
			//} else if (form.adminForm.textfieldcheck.value == 0){
			//	alert( "Poll must have options." );
			} else {
				submitform( pressbutton );
			}
		}
		</script>
		<form action="index.php" method="post" name="adminForm">

		<div class="col50">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'Details' ); ?></legend>

				<table class="admintable">
					<tr>
						<td width="110" class="key">
							<label for="title">
								<?php echo JText::_( 'Title' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" name="title" id="title" size="60" value="<?php echo $row->title; ?>" />
						</td>
					</tr>
					<tr>
						<td width="110" class="key">
							<label for="alias">
								<?php echo JText::_( 'Alias' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" name="alias" id="alias" size="60" value="<?php echo $row->alias; ?>" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<label for="lag">
								<?php echo JText::_( 'Lag' ); ?>:
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" name="lag" id="lag" size="10" value="<?php echo $row->lag; ?>" />
							<?php echo JText::_( '(seconds between votes)' ); ?>
						</td>
					</tr>
					<tr>
						<td width="120" class="key">
							<?php echo JText::_( 'Published' ); ?>:
						</td>
						<td>
							<?php echo JHTML::_( 'select.booleanlist',  'published', 'class="inputbox"', $row->published ); ?>
						</td>
					</tr>
				</table>
			</fieldset>
		</div>

		<div class="col50">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'Options' ); ?></legend>

				<table class="admintable">
				<?php
				for ($i=0, $n=count( $options ); $i < $n; $i++ ) {
					?>
					<tr>
						<td class="key">
							<label for="polloption<?php echo $options[$i]->id; ?>">
								<?php echo JText::_( 'Option' ); ?> <?php echo ($i+1); ?>
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" name="polloption[<?php echo $options[$i]->id; ?>]" id="polloption<?php echo $options[$i]->id; ?>" value="<?php echo $options[$i]->text; ?>" size="60" />
						</td>
					</tr>
					<?php
				}
				for (; $i < 12; $i++) {
					?>
					<tr>
						<td class="key">
							<label for="polloption<?php echo $i + 1; ?>">
								<?php echo JText::_( 'Option' ); ?> <?php echo $i + 1; ?>
							</label>
						</td>
						<td>
							<input class="inputbox" type="text" name="polloption[]" id="polloption<?php echo $i + 1; ?>" value="" size="60" />
						</td>
					</tr>
					<?php
				}
				?>
				</table>
			</fieldset>
		</div>
		<div class="clr"></div>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="option" value="com_poll" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="textfieldcheck" value="<?php echo $n; ?>" />
		</form>
		<?php
	}

	function previewPoll($title, $options)
	{
		?>
		<form>
		<table align="center" width="90%" cellspacing="2" cellpadding="2" border="0" >
		<tr>
			<td class="moduleheading" colspan="2"><?php echo $title; ?></td>
		</tr>
		<?php foreach ($options as $text)
		{
			if ($text <> "")
			{?>
			<tr>
				<td valign="top" height="30"><input type="radio" name="poll" value="<?php echo $text; ?>"></td>
				<td class="poll" width="100%" valign="top"><?php echo $text; ?></td>
			</tr>
			<?php }
		} ?>
		<tr>
			<td valign="middle" height="50" colspan="2" align="center"><input type="button" name="submit" value="<?php echo JText::_( 'Vote' ); ?>">&nbsp;&nbsp;<input type="button" name="result" value="<?php echo JText::_( 'Results' ); ?>"></td>
		</tr>
		</table>
		</form>
		<?php
	}
}
?>
