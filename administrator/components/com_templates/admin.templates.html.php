<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Templates
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
* @package		Joomla
* @subpackage	Templates
*/
class TemplatesView
{
	/**
	* @param array An array of data objects
	* @param object A page navigation object
	* @param string The option
	*/
	function showTemplates(& $rows, & $lists, & $page, $option, & $client)
	{
		global $mainframe;

		$limitstart = JRequest :: getVar('limitstart', '0', '', 'int');

		$user = & JFactory :: getUser();

		if (isset ($row->authorUrl) && $row->authorUrl != '') {
			$row->authorUrl = str_replace('http://', '', $row->authorUrl);
		}

		JHTML::_('behavior.tooltip');
?>
		<form action="index.php" method="post" name="adminForm">

			<table class="adminlist">
			<thead>
				<tr>
					<th width="5" class="title">
						<?php echo JText::_( 'Num' ); ?>
					</th>
					<th class="title" colspan="2">
						<?php echo JText::_( 'Name' ); ?>
					</th>
					<?php

		if ($client->id == 1) {
?>
						<th width="5%">
							<?php echo JText::_( 'Default' ); ?>
						</th>
						<?php

		} else {
?>
						<th width="5%">
							<?php echo JText::_( 'Default' ); ?>
						</th>
						<th width="5%">
							<?php echo JText::_( 'Assigned' ); ?>
						</th>
						<?php

		}
?>
					<th width="10%" align="center">
						<?php echo JText::_( 'Version' ); ?>
					</th>
					<th width="15%" class="title">
						<?php echo JText::_( 'Date' ); ?>
					</th>
					<th width="25%"  class="title">
						<?php echo JText::_( 'Author' ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<td colspan="8">
					<?php echo $page->getListFooter(); ?>
				</td>
			</tfoot>
			<tbody>
			<?php

		$k = 0;
		for ($i = 0, $n = count($rows); $i < $n; $i++) {
			$row = & $rows[$i];

			$author_info = @ $row->authorEmail . '<br />' . @ $row->authorUrl;
?>
				<tr class="<?php echo 'row'. $k; ?>">
					<td>
						<?php echo $page->getRowOffset( $i ); ?>
					</td>
					<td width="5">
					<?php

			if ( JTable::isCheckedOut($user->get ('id'), $row->checked_out )) {
?>
							&nbsp;
							<?php

			} else {
?>
							<input type="radio" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->directory; ?>" onclick="isChecked(this.checked);" />
							<?php

			}
?>
					</td>
					<td><?php $img_path = ($client->id == 1 ? $mainframe->getSiteURL().'/administrator' : $mainframe->getSiteURL() ).'/templates/'.$row->directory.'/template_thumbnail.png'; ?>
						<span class="editlinktip hasTip" title="<?php echo $row->name;?>::
<img border=&quot;1&quot; src=&quot;<?php echo $img_path; ?>&quot; name=&quot;imagelib&quot; alt=&quot;<?php echo JText::_( 'No preview available' ); ?>&quot; width=&quot;206&quot; height=&quot;145&quot; />"><a href="index.php?option=com_templates&amp;task=edit&amp;cid[]=<?php echo $row->directory;?>&amp;client=<?php echo $client->id;?>">
							<?php echo $row->name;?></a></span>
					</td>
					<?php

			if ($client->id == 1) {
?>
						<td align="center">
							<?php

				if ($row->published == 1) {
?>
							<img src="templates/khepri/images/menu/icon-16-default.png" alt="<?php echo JText::_( 'Published' ); ?>" />
								<?php

				} else {
?>
								&nbsp;
								<?php

				}
?>
						</td>
						<?php

			} else {
?>
						<td align="center">
							<?php

				if ($row->published == 1) {
?>
								<img src="templates/khepri/images/menu/icon-16-default.png" alt="<?php echo JText::_( 'Default' ); ?>" />
								<?php

				} else {
?>
								&nbsp;
								<?php

				}
?>
						</td>
						<td align="center">
							<?php

				if ($row->assigned == 1) {
?>
								<img src="images/tick.png" alt="<?php echo JText::_( 'Assigned' ); ?>" />
								<?php

				} else {
?>
								&nbsp;
								<?php

				}
?>
						</td>
						<?php

			}
?>
					<td align="center">
						<?php echo $row->version; ?>
					</td>
					<td>
						<?php echo $row->creationdate; ?>
					</td>
					<td>
						<span class="editlinktip hasTip" title="<?php echo JText::_( 'Author Information' );?>::<?php echo $author_info; ?>">
							<?php echo @$row->author != '' ? $row->author : '&nbsp;'; ?>
						</span>
					</td>
				</tr>
				<?php

		}
?>
			</tbody>
			</table>

	<input type="hidden" name="option" value="<?php echo $option;?>" />
	<input type="hidden" name="client" value="<?php echo $client->id;?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	</form>
	<?php

	}

	function previewTemplate($template, $showPositions, $client, $option)
	{
		global $mainframe;

		$tp = intval($showPositions);
		$url = $client->id ? JURI::base() : $mainframe->getSiteURL();
?>
		<style type="text/css">
		.previewFrame {
			border: none;
			width: 95%;
			height: 600px;
			padding: 0px 5px 0px 10px;
		}
		</style>

		<table class="adminform">
			<tr>
				<th width="50%" class="title">
					<?php echo JText::_( 'Site Preview' ); ?>
				</th>
				<th width="50%" style="text-align:right">
					<?php echo JHTML::Link($url.'index.php?tp='.$tp.'&amp;template='.$template, JText::_( 'Open in new window' ), array('target' => '_blank')); ?>
				</th>
			</tr>
			<tr>
				<td width="100%" valign="top" colspan="2">
					<?php echo JHTML::Iframe($url.'index.php?tp='.$tp.'&amp;template='.$template,'previewFrame',  array('class' => 'previewFrame')) ?>
				</td>
			</tr>
		</table>
		<?php

	}

	/**
	* @param string Template name
	* @param string Source code
	* @param string The option
	*/
	function editTemplate($row, $lists, & $params, $option, & $client, & $ftp)
	{
		JRequest::setVar( 'hidemainmenu', 1 );
		
		JHTML::_('behavior.tooltip');
?>
		<form action="index.php" method="post" name="adminForm">

		<?php if($ftp): ?>
		<fieldset title="<?php echo JText::_('DESCFTPTITLE'); ?>" class="adminform">
			<legend><?php echo JText::_('DESCFTPTITLE'); ?></legend>

			<?php echo JText::_('DESCFTP'); ?>

			<?php if(JError::isError($ftp)): ?>
				<p><?php echo JText::_($ftp->message); ?></p>
			<?php endif; ?>

			<table class="adminform nospace">
			<tbody>
			<tr>
				<td width="120">
					<label for="username"><?php echo JText::_('Username'); ?>:</label>
				</td>
				<td>
					<input type="text" id="username" name="username" class="input_box" size="70" value="" />
				</td>
			</tr>
			<tr>
				<td width="120">
					<label for="password"><?php echo JText::_('Password'); ?>:</label>
				</td>
				<td>
					<input type="password" id="password" name="password" class="input_box" size="70" value="" />
				</td>
			</tr>
			</tbody>
			</table>
		</fieldset>
		<?php endif; ?>

		<div class="col50">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'Details' ); ?></legend>

				<table class="admintable">
				<tr>
					<td valign="top" class="key">
						<?php echo JText::_( 'Name' ); ?>:
					</td>
					<td>
						<strong>
							<?php echo JText::_($row->name); ?>
						</strong>
					</td>
				</tr>
				<tr>
					<td valign="top" class="key">
						<?php echo JText::_( 'Description' ); ?>:
					</td>
					<td>
						<?php echo JText::_($row->description); ?>
					</td>
				</tr>
				</table>
			</fieldset>

			<fieldset class="adminform">
				<legend><?php echo JText::_( 'Menu Assignment' ); ?></legend>
				<script type="text/javascript">
					function allselections() {
						var e = document.getElementById('selections');
							e.disabled = true;
						var i = 0;
						var n = e.options.length;
						for (i = 0; i < n; i++) {
							e.options[i].disabled = true;
							e.options[i].selected = true;
						}
					}
					function disableselections() {
						var e = document.getElementById('selections');
							e.disabled = true;
						var i = 0;
						var n = e.options.length;
						for (i = 0; i < n; i++) {
							e.options[i].disabled = true;
							e.options[i].selected = false;
						}
					}
					function enableselections() {
						var e = document.getElementById('selections');
							e.disabled = false;
						var i = 0;
						var n = e.options.length;
						for (i = 0; i < n; i++) {
							e.options[i].disabled = false;
						}
					}
				</script>
				<table class="admintable" cellspacing="1">
					<tr>
						<td valign="top" class="key">
							<?php echo JText::_( 'Menus' ); ?>:
						</td>
						<td>
							<?php if ($client->id == 1) {
									echo JText::_('Cannot assign administrator template');
								  } elseif ($row->pages == 'all') {
									echo JText::_('Cannot assign default template');
								  } elseif ($row->pages == 'none') { ?>
							<label for="menus-none"><input id="menus-none" type="radio" name="menus" value="none" onclick="disableselections();" checked="checked" /><?php echo JText::_( 'None' ); ?></label>
							<label for="menus-select"><input id="menus-select" type="radio" name="menus" value="select" onclick="enableselections();" /><?php echo JText::_( 'Select From List' ); ?></label>
							<?php } else { ?>
							<label for="menus-none"><input id="menus-none" type="radio" name="menus" value="none" onclick="disableselections();" /><?php echo JText::_( 'None' ); ?></label>
							<label for="menus-select"><input id="menus-select" type="radio" name="menus" value="select" onclick="enableselections();" checked="checked" /><?php echo JText::_( 'Select From List' ); ?></label>
							<?php } ?>
						</td>
					</tr>
					<?php if ($row->pages != 'all' && $client->id != 1) : ?>
					<tr>
						<td valign="top" class="key">
							<?php echo JText::_( 'Menu Selection' ); ?>:
						</td>
						<td>
							<?php echo $lists['selections']; ?>
							<?php if ($row->pages == 'none') { ?>
							<script type="text/javascript">disableselections();</script>
							<?php } ?>
						</td>
					</tr>
					<?php endif; ?>
				</table>
			</fieldset>
		</div>

		<div class="col50">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'Parameters' ); ?></legend>

				<table class="admintable">
				<tr>
					<td>
						<?php

		if (!is_null($params)) {
			echo $params->render();
		} else {
			echo '<i>' . JText :: _('No Parameters') . '</i>';
		}
?>
					</td>
				</tr>
				</table>
			</fieldset>
		</div>
		<div class="clr"></div>

		<input type="hidden" name="id" value="<?php echo $row->directory; ?>" />
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="client" value="<?php echo $client->id;?>" />
		</form>
		<?php
	}

	function editTemplateSource($template, & $content, $option, & $client, & $ftp)
	{
		JRequest::setVar( 'hidemainmenu', 1 );
		
		$template_path = $client->path .DS. 'templates' .DS. $template .DS. 'index.php';
?>
		<form action="index.php" method="post" name="adminForm">

		<?php if($ftp): ?>
		<fieldset title="<?php echo JText::_('DESCFTPTITLE'); ?>">
			<legend><?php echo JText::_('DESCFTPTITLE'); ?></legend>

			<?php echo JText::_('DESCFTP'); ?>

			<?php if(JError::isError($ftp)): ?>
				<p><?php echo JText::_($ftp->message); ?></p>
			<?php endif; ?>

			<table class="adminform nospace">
			<tbody>
			<tr>
				<td width="120">
					<label for="username"><?php echo JText::_('Username'); ?>:</label>
				</td>
				<td>
					<input type="text" id="username" name="username" class="input_box" size="70" value="" />
				</td>
			</tr>
			<tr>
				<td width="120">
					<label for="password"><?php echo JText::_('Password'); ?>:</label>
				</td>
				<td>
					<input type="password" id="password" name="password" class="input_box" size="70" value="" />
				</td>
			</tr>
			</tbody>
			</table>
		</fieldset>
		<?php endif; ?>

		<table class="adminform">
		<tr>
			<th>
				<?php echo $template_path; ?>
			</th>
		</tr>
		<tr>
			<td>
				<textarea style="width:100%;height:500px" cols="110" rows="25" name="filecontent" class="inputbox"><?php echo $content; ?></textarea>
			</td>
		</tr>
		</table>

		<div class="clr"></div>

		<input type="hidden" name="id" value="<?php echo $template; ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $template; ?>" />
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="client" value="<?php echo $client->id;?>" />
		</form>
		<?php
	}

	function chooseCSSFiles($template, $t_dir, $t_files, $option, & $client)
	{
		JRequest::setVar( 'hidemainmenu', 1 );
?>
		<form action="index.php" method="post" name="adminForm">

		<table cellpadding="1" cellspacing="1" border="0" width="100%">
		<tr>
			<td width="220">
				<span class="componentheading">&nbsp;</span>
			</td>
		</tr>
		</table>
		<table class="adminlist">
		<tr>
			<th width="5%" align="left">
				<?php echo JText::_( 'Num' ); ?>
			</th>
			<th width="85%" align="left">
				<?php echo $t_dir; ?>
			</th>
			<th width="10%">
				<?php echo JText::_( 'Writeable' ); ?>/<?php echo JText::_( 'Unwriteable' ); ?>
			</th>
		</tr>
		<?php

		$k = 0;
		for ($i = 0, $n = count($t_files); $i < $n; $i++) {
			$file = & $t_files[$i];
?>
			<tr class="<?php echo 'row'. $k; ?>">
				<td width="5%">
					<input type="radio" id="cb<?php echo $i;?>" name="filename" value="<?php echo '/templates/'. $template .'/css/'. $file; ?>" onClick="isChecked(this.checked);" />
				</td>
				<td width="85%">
					<?php echo $file; ?>
				</td>
				<td width="10%">
					<?php echo is_writable($t_dir.DS.$file) ? '<font color="green"> '. JText::_( 'Writeable' ) .'</font>' : '<font color="red"> '. JText::_( 'Unwriteable' ) .'</font>' ?>
				</td>
			</tr>
		<?php

			$k = 1 - $k;
		}
?>
		</table>
		<input type="hidden" name="id" value="<?php echo $template; ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $template; ?>" />
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="client" value="<?php echo $client->id;?>" />
		</form>
		<?php

	}

	/**
	* @param string Template name
	* @param string Source code
	* @param string The option
	*/
	function editCSSSource($template, $filename, & $content, $option, & $client, & $ftp)
	{
		JRequest::setVar( 'hidemainmenu', 1 );
		
		$css_path = $client->path . $filename;

?>
		<form action="index.php" method="post" name="adminForm">

		<?php if($ftp): ?>
		<fieldset title="<?php echo JText::_('DESCFTPTITLE'); ?>">
			<legend><?php echo JText::_('DESCFTPTITLE'); ?></legend>

			<?php echo JText::_('DESCFTP'); ?>

			<?php if(JError::isError($ftp)): ?>
				<p><?php echo JText::_($ftp->message); ?></p>
			<?php endif; ?>

			<table class="adminform nospace">
			<tbody>
			<tr>
				<td width="120">
					<label for="username"><?php echo JText::_('Username'); ?>:</label>
				</td>
				<td>
					<input type="text" id="username" name="username" class="input_box" size="70" value="" />
				</td>
			</tr>
			<tr>
				<td width="120">
					<label for="password"><?php echo JText::_('Password'); ?>:</label>
				</td>
				<td>
					<input type="password" id="password" name="password" class="input_box" size="70" value="" />
				</td>
			</tr>
			</tbody>
			</table>
		</fieldset>
		<?php endif; ?>

		<table class="adminform">
		<tr>
			<th>
				<?php echo $css_path; ?>
			</th>
		</tr>
		<tr>
			<td>
				<textarea style="width:100%;height:500px" cols="110" rows="25" name="filecontent" class="inputbox"><?php echo $content; ?></textarea>
			</td>
		</tr>
		</table>


		<input type="hidden" name="id" value="<?php echo $template; ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $template; ?>" />
		<input type="hidden" name="filename" value="<?php echo $filename; ?>" />
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="client" value="<?php echo $client->id;?>" />
		</form>
		<?php
	}
}
?>