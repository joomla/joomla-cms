<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

// TOOLBAR
$newprocess = JRequest::getVar('newprocess',0,'','integer' );
$action = (($this->options['task']=='addxml')||($newprocess)) ? 'New' : 'Edit';
JToolbarHelper::title(JText::_($action.' XML'),'langmanager.png');
JToolbarHelper::save('savexml');
JToolbarHelper::apply('applyxml');
JToolbarHelper::cancel('cancelxml');

?>
<link href="components/com_localise/media/css/localise.css" rel="stylesheet" type="text/css" />
<script language="javascript" type="text/javascript" src="components/com_localise/media/js/localise.js"></script>
<script language="javascript" type="text/javascript">
function submitbutton(pressbutton) {
	if (pressbutton == "cancelxml") {
		submitform(pressbutton);
		return;
	}
	// validation
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
	<input type="hidden" name="task" value="editxml" />
	<input type="hidden" name="client_lang" value="<?php echo $this->options['client_lang']; ?>" />
	<input type="hidden" name="newprocess" value="<?php echo $this->options['newprocess']; ?>" />

	<div class="col100">
		<fieldset class="adminform">
			<legend><?php echo $this->getTooltip( 'Language Details' ) ; ?></legend>
			<table class="admintable">
				<tr>
					<td class="ffMetaToken">
						<?php echo $this->getTooltip( 'Client' ); ?>
					</td>
					<td>
						<b><?php
							if ($this->options['newprocess']) {
								echo '<input type="radio" name="newclient" value="A"'.( ($this->options['client']=='A')?' checked':'' ).'/> '.JText::_('Administrator').' ';
								echo '<input type="radio" name="newclient" value="I" '.( ($this->options['client']=='I')?'checked ':'' ).'/> '.JText::_('Installation').' ';
								echo '<input type="radio" name="newclient" value="S" '.( ($this->options['client']=='S')?'checked ':'' ).'/> '.JText::_('Site').' ';
							} else {
								echo $this->options['clientName'];
							}
						?></b>
					</td>
				</tr>
				<tr>
					<td class="ffMetaToken">
						<?php echo $this->getTooltip( 'TAG' ); ?>
					</td>
					<td>
						<b><?php
							if ($this->options['newprocess']) {
								echo '<input type="text" name="tag" class="inputbox"'.( (($this->options['task']=='addxml')||(isset($this->options['field_error_list']['tag'])))?'style=";border-left:solid red 2px;padding-left:3px" ':'' ) .'size="5" maxlength="6" value="'. htmlspecialchars($this->data['tag']) .'" />';
							} else {
								echo $this->options['lang'];
							}
						?></b>
					<td>
				</tr>
				<?php
				// set up the yes-no array
				$yn[] = JHtml::_( 'select.option',  0, JText::_( 'No' ) );
				$yn[] = JHtml::_( 'select.option',  1, JText::_( 'Yes' ) );
				// set up the process array
				$arr = array();
				$arr['name'] = 40;
				$arr['description'] = 'textarea';
				$arr['backwardLang'] = 40;
				$arr['locale'] = 'textarea';
				$arr['winCodePage'] = 40;
				$arr['pdfFontName'] =  80;
				$arr['rtl'] = JHtml::_(
					'select.genericlist',
					$yn,
					'rtl',
					array('list.select' => $this->data['metadata']['rtl'])
				);
				$arr[] = 'Author Details';
				$arr['author'] =  80;
				$arr['authorEmail'] =  80;
				$arr['authorUrl'] =  80;
				$arr['version'] = 40;
				$arr['creationDate'] = 40;
				$arr['copyright'] = 80;
				$arr['license'] = 80;

				// process the array
				foreach ($arr as $k=>$v) {

					// new fieldset
					if (is_int($k)) {
						echo '
			</table>
		</fieldset>
	</div>
	<div class="clr"></div>

	<div class="col100">
		<fieldset class="adminform">
			<legend>'. JText::_($v) .'</legend>
			<table class="admintable">';
					}

					// display a table row
					else {
						// get the value
						if (isset($this->data[$k])) {
							$value = $this->data[$k];
						} else if (isset($this->data['metadata'][$k])) {
							$value = $this->data['metadata'][$k];
						} else {
							$value = '';
						}
						// check error class
						$style = ( ($this->options['task']!='addxml') && (isset($this->options['field_error_list'][$k])) ) ? 'class="ffError" ' : '';
						// check the input type
						if (is_int($v)) {
							$input = '<input '. $style .' type="text" size="'. $v .'" name="'. $k .'" id="'. $k .'" value="'. $value .'" onkeyup="ffAutoCorrect(this)" />';
						} else if ($v=='textarea') {
							$input = '<textarea '. $style .' rows="2" cols="80" name="'. $k .'" id="'. $k .'" onkeyup="ffAutoCorrect(this)">'. htmlspecialchars($value,ENT_COMPAT) .'</textarea>';
						} else {
							$input = $v;
						}
						// output the row
						echo '
						<tr>
							<td class="ffMetaToken"><label for="' . $k . '">' . $this->getTooltip($k ). '</label></td>
							<td>' . $input . '</td>
						</tr>';
					}

				}
				?>

			</table>
		</fieldset>
	</div>
	<div class="clr"></div>

</form>
</div>