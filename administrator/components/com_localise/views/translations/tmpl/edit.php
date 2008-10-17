<?php
/**
* @version 1.5
* @package com_localise
* @author Ifan Evans
* @copyright Copyright (C) 2007 Ifan Evans. All rights reserved.
* @license GNU/GPL
* @bugs - please report to post@ffenest.co.uk
*/

defined('_JEXEC') or die('Restricted access');

// CONFIGURATION
$metaTokens = array (
	'version' 	=> 10,
	'author'	=> 80,
	'copyright'	=> 80,
	'license' 	=> 80,
);

// TOOLBAR
JRequest::setVar( 'hidemainmenu', 1 );
$newprocess = JRequest::getVar('newprocess',0,'','integer' );
$action = (($this->options['task']=='add')||($newprocess)) ? 'New' : 'Edit';
JToolbarHelper::title(JText::_($action.' INI'),'langmanager.png');
JToolbarHelper::save();
JToolbarHelper::apply();
JToolbarHelper::cancel('cancel');

?>
<link href="components/com_localise/media/css/localise.css" rel="stylesheet" type="text/css" />
<script language="javascript" type="text/javascript" src="components/com_localise/media/js/localise.js"></script>
<script language="javascript" type="text/javascript">
function submitbutton(pressbutton) {
	if (pressbutton == "cancel") {
		submitform(pressbutton);
		return;
	}
	var form = document.adminForm;
	submitform(pressbutton);
}
// set a timeout to refresh the page
window.setTimeout ('if( window.confirm("<?php echo JText::_( 'Apply Reminder', 1 ); ?>" ) ) submitform("apply");', 300000);
// initialise ffAutoCorrect array
<?php foreach ($this->options['autoCorrect'] as $k=>$v) echo "ffacList['$k'] = '$v';\n"; ?>
// initialise ffCheckDisable message
ffchkmessage = '<?php echo ( $this->options['isReference'] ? JText::_('Warning Default Language',1) . '\n' : '' ) . JText::_('Confirm Delete String',1); ?>';
</script>

<div id="localise">
<form action="index.php" method="post" name="adminForm">
	<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
	<input type="hidden" name="option" value="com_localise" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="client_lang" value="<?php echo $this->options['client_lang']; ?>" />
	<input type="hidden" name="newprocess" value="<?php echo $this->options['newprocess']; ?>" />
	<input type="hidden" name="limitstart" value="<?php echo $this->options['limitstart']; ?>" />

	<div class="col100">
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'Details' ) ; ?></legend>
			<table class="admintable" width="100%">
				<tr>
					<td class="ffMetaToken"><?php echo $this->getTooltip('Language'); ?></td>
					<td class="ffMeta" nowrap><b><?php echo $this->options['langName'];?></b> <?php if ($this->options['isReference']) echo ' <i>['.JText::_('Warning Default Language').']</i></b>'; ?></td>
					<td class="ffKeys" rowspan="3" nowrap>
						<?php echo $this->getTooltip('Key'); ?> &nbsp;
						<input class="ffChanged" type="text" readonly value="<?php echo JText::_('String Changed'); ?>"><br>
						<input class="ffUnchanged" type="text" readonly value="<?php echo JText::_('String Unchanged'); ?>"><br>
						<input class="ffExtra" type="text" readonly value="<?php echo JText::_('String Extra'); ?>">
					</td>
				</tr>
				<tr>
					<td class="ffMetaToken">
						<?php echo $this->getTooltip('Filename'); ?>
					</td>
					<td>
						<b><?php
						// 1: NEW FILE = text input (with error CSS)
						// 2: EXISTING = hidden input
						if ($this->options['newprocess']) {
							echo '<input ' . ( ( ($this->options['task']!='add') && (isset($this->options['field_error_list']['filename'])) ) ? 'class="ffError"' : '' ) . ' type="text" class="inputbox" size="30" name="newfilename" value="' . htmlspecialchars($this->options['newfilename']) . '" />';
						} else {
							echo '<input type="hidden" name="cid[]" value="'. htmlspecialchars($this->options['filename']) .'" />' . $this->options['filename'];
						}
						?></b>
					</td>
				</tr>
			<?php
			// show Meta
			foreach ( $metaTokens as $k=>$v ) {
				$img = '<img src="components/com_localise/images/default.png" alt="*" onclick="document.adminForm.'. $k .'.value=\''. htmlspecialchars($this->options['XMLmeta'][$k]) .'\'" />';
				echo '
				<tr>
					<td class="ffMetaToken">
						<label for="' . $k . '">' . $this->getTooltip( ucfirst($k) ) . '</label>
					</td>
					<td colspan="2">
						<input ' . ( ( ($this->options['task']!='add') && (isset($this->options['field_error_list'][$k])) ) ? 'class="ffError"' : '' ) . ' type="text" size="'.$v.'" name="'.$k.'" id="'.$k.'" value="'.$this->meta[$k].'" onkeyup="ffAutoCorrect(this)" />' . $this->getTooltip( $img, $this->options['XMLmeta'][$k], sprintf( JText::_('Use The Default'), JText::_($k) ), false ) . '
					</td>
				</tr>';
			}
			// show Status/Complete
			if (!$this->options['isReference']) {
				$status = sprintf( JText::_('of translated'), $this->meta['changed'], $this->meta['refstrings'], $this->meta['extra'] );
				if ($this->meta['extra']) $status .= ', '. sprintf( JText::_('extra strings'), $this->meta['extra'] );
				echo '
				<tr>
					<td class="ffMetaToken">
						<label for="complete">' . $this->getTooltip( 'Status' ) . '</label>
					</td>
					<td colspan="2">
						<b>'. $this->meta['status'] .'%</b> &nbsp; ['. $status .'] &nbsp; <input class="ffCheckbox" type="checkbox" name="complete" value="COMPLETE" />'. $this->getTooltip( 'Mark as Complete' ) . '
					</td>
				</tr>
				';
			}
			?>
			</table>
		</fieldset>
	</div>
	<div class="clr"></div>

			<?php
			// Configure the search highlighting
			$search = array();
			if ($this->options['searchStyle']) {
				$replace = '<span style="'.$this->options['searchStyle'].'">$0</span>';
				foreach(explode(' ',$this->options['filter_search']) as $v) {
					if ($v) {
						$search[] = '/'.$v.'/i';
					}
				}
			}
			// process the file data into sections and HTML strings
			$i = 0;
			$heading = 0;
			$output = array();
			foreach($this->data as $k=>$v) {

				// 1: strings are comments or lines from the INI file (change the section name if we have a comment)
				// 2: arrays are key=value lines from the INI file
				if ( is_string($v) ) {
					if (!empty($v)) {
						$heading = trim($v,';# ');
					}
				} else {
					// initialise the row object
					$row 		= new stdClass();
					$row->cb 	= '';
					$row->css 	= 'class="ffChanged"';
					$row->edit	= $v['edit'];
					$row->i 	= ++$i;
					$row->key 	= htmlspecialchars($k,ENT_QUOTES);
					$row->match = 0;
					$row->ref 	= (!isset($v['ref'])) ? null : $v['ref'];
					$row->refshow = htmlspecialchars($row->ref);

					// prepare form elements and styles
					// 1: there is no reference language entry for this string
					// 2: this is the reference language file
					// 3: the reference language entry has not been changed
					// 4: the reference language entry has been changed
					if (is_null($row->ref)) {
						$row->refshow 	= '<span class="ffToken">['.$row->key.']</span>';
						$row->cb 		= '<input class="ffCheckbox" type="checkbox" onclick="javascript:ffCheckDisable(this,'.$i.');" />';
						$row->css 		= 'class="ffExtra"';
					} else if ($this->options['isReference']) {
						$row->cb 		= '<input class="ffCheckbox" type="checkbox" onclick="javascript:ffCheckDisable(this,'.$i.');" />';
						$row->css 		= 'class="ffChanged"';
					} else if ($row->ref == $row->edit) {
						$row->css 		= 'class="ffUnchanged"';
					}

					// highlight search terms
					if (count($search)) {
						$chk = preg_replace( $search, $replace, $row->refshow );
						if ( $row->refshow != $chk ) {
							$row->match++;
							$row->refshow = $chk;
						} else {
							$chk = preg_replace( $search, $replace, $row->edit );
							if ( $row->edit != $chk ) {
								$row->match++;
							} else {
								$chk = preg_replace( $search, $replace, $row->key );
								if ( $row->key != $chk ) {
									$row->match++;
								}
							}
						}
					}

					// store the input
					if ( (strlen($row->ref)>80) || (strlen($row->edit)>80) ) {
						$row->input = '<textarea '. $row->css .' name="ffValues[]" id="value'.$i.'" cols="80" rows="4" onkeyup="ffAutoCorrect(this)">'. htmlspecialchars( $row->edit, ENT_QUOTES ) .'</textarea>';
					} else {
						$row->input = '<input '. $row->css .' name="ffValues[]" id="value'.$i.'" type="text" size="80" value="'. htmlspecialchars( $row->edit, ENT_QUOTES ) .'" onkeyup="ffAutoCorrect(this)" />';
					}

					// store to the $extra or the $sections array
					if ( (!$row->ref) && (!$this->options['isReference']) ) {
						$extra[$k] = $row;
					} else {
						$sections[$heading][$k] = $row;
					}
				}
			}
			// add on any extra phrases at the end
			if (isset($extra)) {
				$sections['extra'] = $extra;
			}

			if (isset($sections)) {

				// process the output data by section and then by row
				foreach($sections as $k=>$v){
					// section legend
					$legend = (empty($k)) ? '' : '<legend>' . JText::_($k) . '</legend>';
					// section help
					$help = '';
					if ($k) {
						$help_key = $k . ' DESC';
						$help = JText::_($help_key);
						$help = ($help==$help_key) ? '' : '<tr valign="top"><td colspan="4">' . $help . '</td></tr>';
					}
					// section delete column (if there are any 'delete' checkboxes in the section)
					foreach ( $v as $v2 ) {
						if ($v2->cb) {
							$help .= '<tr valign="bottom"><td colspan="2"></td><td nowrap align="right"><b>' . $this->getTooltip( 'Delete', null, 'Delete Phrase' ) . '</b></td></tr>';
							break;
						}
					}
					?>
	<div class="col100">
		<fieldset class="adminform">
			<?php echo $legend; ?>
			<table class="admintable" width="100%">
				<?php
				echo $help;
				$i=1;
				foreach($v as $row){
					?>
				<tr valign="top" id="row<?php echo $row->i; ?>">
					<td class="ffCounter">
						<?php echo ($row->match) ? '<span style="' . $this->options['searchStyle'] . ';width:100%">' . $i++ . '</span>' : $i++; ?>
					</td>
					<td class="ffToken">
						<?php
						if (!is_null($row->ref)) {
							echo '<a class="ffCopy" href="javascript:ffCopyRef2Val(' . $row->i . ')">' . $this->getTooltip( '<img src="../images/M_images/arrow.png" alt="&gt;" />', null, 'COPY STRING', 'TC') . '</a>';
						}
						?>
						<input type="hidden" name="ffKeys[]" value="<?php echo $row->key . '" id="key' . $row->i;?>" />
						<?php echo $this->getTooltip( '<span id="ref' . $row->i .'">' . $row->refshow . '</span>', $row->key, JText::_('Key'), false); ?>
					</td>
					<td class="ffValue" nowrap>
						<?php
						echo $row->input;
						// echo '<a class="ffReset" href="javascript:ffResetVal(\'value' . $row->i . '\')">' . $this->getTooltip( '<img src="components/com_localise/images/reset9.png" alt="&lt;" />', null, 'RESET STRING', 'TC') . '</a>';
						echo $row->cb;
						?>
					</td>
				</tr>
					<?php
					}
					?>
			</table>
		</fieldset>
	</div>
	<div class="clr"></div>
					<?php
				}
			}
				?>

	<div class="col100">
		<fieldset class="adminform">
			<legend><?php echo JText::_('New Phrases'); ?></legend>
			<table class="admintable" width="100%" id="extraTable">
			<tr valign="top">
				<td colspan="4"><?php echo JText::_('New Phrases DESC'); ?></td>
			</tr>
			<tr>
				<td><div id="ffExtra"></div></td>
			</tr>
			</table>
			<a href="javascript:ffAppendRow('extraTable','extraRow');"><b>[+]</b> <?php echo JText::_('Add phrases'); ?></a>
		</fieldset>
	</div>
	<div class="clr"></div>

	<div id="ffAddField" style="display:none">
		<table class="admintable" width="100%">
			<tr valign="top" id="extraRow">
				<td class="ffToken">
					[new key] <input class="ffUnchanged" name="ffKeys[]" type="text" size="80"  value="" style="width:50%" onchange="this.value=this.value.replace(/[=]/,'').toUpperCase()" />
				</td>
				<td class="ffValue">
					<input class="ffChanged" name="ffValues[]" type="text" size="80"  value="" />
				</td>
			</tr>
		</table>
	</div>

</form>
</div>