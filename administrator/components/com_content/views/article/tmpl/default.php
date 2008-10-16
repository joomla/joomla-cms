<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php
	JRequest::setVar( 'hidemainmenu', 1 );

	jimport('joomla.html.pane');
	JFilterOutput::objectHTMLSafe( $row );

	$db		=& JFactory::getDBO();
	$editor =& JFactory::getEditor();
	$pane	=& JPane::getInstance('sliders');

	JHTML::_('behavior.tooltip');

	$create_date 	= null;
	$nullDate 		= $db->getNullDate();

	// used to hide "Reset Hits" when hits = 0
	if ( !$this->row->hits ) {
		$visibility = 'style="display: none; visibility: hidden;"';
	} else {
		$visibility = '';
	}
?>

<?php
	$cid = JRequest::getVar( 'cid', array(0), '', 'array' );
	$cid = intval($cid[0]);
	$edit	= JRequest::getVar('edit',true);

	$text = ( $edit ? JText::_( 'Edit' ) : JText::_( 'New' ) );

	JToolBarHelper::title( JText::_( 'Article' ).': <small><small>[ '. $text.' ]</small></small>', 'addedit.png' );
	JToolBarHelper::preview( 'index.php?option=com_content&id='.$cid.'&tmpl=component', true );
	JToolBarHelper::save();
	JToolBarHelper::apply();
	if ( $edit ) {
		// for existing articles the button is renamed `close`
		JToolBarHelper::cancel( 'cancel', 'Close' );
	} else {
		JToolBarHelper::cancel();
	}
	JToolBarHelper::help( 'screen.content.edit' );
?>
<script language="javascript" type="text/javascript">
<!--
var sectioncategories = new Array;
<?php
$i = 0;
foreach ($this->sectioncategories as $k=>$items) {
	foreach ($items as $v) {
		echo "sectioncategories[".$i++."] = new Array( '$k','".addslashes( $v->id )."','".addslashes( $v->title )."' );\n\t\t";
	}
}
?>

function submitbutton(pressbutton)
{
	var form = document.adminForm;

	if ( pressbutton == 'menulink' ) {
		if ( form.menuselect.value == "" ) {
			alert( "<?php echo JText::_( 'Please select a Menu', true ); ?>" );
			return;
		} else if ( form.link_name.value == "" ) {
			alert( "<?php echo JText::_( 'Please enter a Name for this menu item', true ); ?>" );
			return;
		}
	}

	if (pressbutton == 'cancel') {
		submitform( pressbutton );
		return;
	}

	// do field validation
	var text = <?php echo $editor->getContent( 'text' ); ?>
	if (form.title.value == ""){
		alert( "<?php echo JText::_( 'Article must have a title', true ); ?>" );
	} else if (form.sectionid.value == "-1"){
		alert( "<?php echo JText::_( 'You must select a Section', true ); ?>" );
	} else if (form.catid.value == "-1"){
		alert( "<?php echo JText::_( 'You must select a Category', true ); ?>" );
	} else if (form.catid.value == ""){
		alert( "<?php echo JText::_( 'You must select a Category', true ); ?>" );
	} else if (text == ""){
		alert( "<?php echo JText::_( 'Article must have some text', true ); ?>" );
	} else {
		<?php
		echo $editor->save( 'text' );
		?>
		submitform( pressbutton );
	}
}
//-->
</script>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">

<div class="col width-70">
	<fieldset class="adminform">
		<table  class="admintable">
		<tr>
			<td class="key">
				<label for="title">
					<?php echo JText::_( 'Title' ); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="title" id="title" size="40" maxlength="255" value="<?php echo $this->row->title; ?>" />
			</td>
			<td class="key">
				<label>
					<?php echo JText::_( 'Published' ); ?>
				</label>
			</td>
			<td>
				<?php echo $this->lists['state']; ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="alias">
					<?php echo JText::_( 'Alias' ); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="alias" id="alias" size="40" maxlength="255" value="<?php echo $this->row->alias; ?>" />
			</td>
			<td class="key">
				<label>
				<?php echo JText::_( 'Frontpage' ); ?>
				</label>
			</td>
			<td>
				<?php echo $this->lists['frontpage']; ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="sectionid">
					<?php echo JText::_( 'Section' ); ?>
				</label>
			</td>
			<td>
				<?php echo $this->lists['sectionid']; ?>
			</td>
			<td class="key">
				<label for="catid">
					<?php echo JText::_( 'Category' ); ?>
				</label>
			</td>
			<td>
				<?php echo $this->lists['catid']; ?>
			</td>
		</tr>
		</table>
		<table class="admintable">
		<tr>
			<td>
				<?php
				// parameters : areaname, content, width, height, cols, rows
				echo $editor->display( 'text',  $this->row->text , '100%', '550', '75', '20' ) ;
				?>
			</td>
		</tr>
		</table>
	</fieldset>
</div>

<div class="col width-30">
	<fieldset class="adminform" style="border: 1px dashed silver;">
		<table class="admintable" style="padding: 5px; margin-bottom: 10px;">
		<?php
		if ( $this->row->id ) {
		?>
		<tr>
			<td>
				<strong><?php echo JText::_( 'Article ID' ); ?>:</strong>
			</td>
			<td>
				<?php echo $this->row->id; ?>
			</td>
		</tr>
		<?php
		}
		?>
		<tr>
			<td>
				<strong><?php echo JText::_( 'State' ); ?></strong>
			</td>
			<td>
				<?php echo $this->row->state > 0 ? JText::_( 'Published' ) : ($this->row->state < 0 ? JText::_( 'Archived' ) : JText::_( 'Draft Unpublished' ) );?>
			</td>
		</tr>
		<tr>
			<td>
				<strong><?php echo JText::_( 'Hits' ); ?></strong>
			</td>
			<td>
				<?php echo $this->row->hits;?>
				<span <?php echo $visibility; ?>>
					<input name="reset_hits" type="button" class="button" value="<?php echo JText::_( 'Reset' ); ?>" onclick="javascript: submitbutton('resethits');" />
				</span>
			</td>
		</tr>
		<tr>
			<td>
				<strong><?php echo JText::_( 'Revised' ); ?></strong>
			</td>
			<td>
				<?php echo $this->row->version;?> <?php echo JText::_( 'times' ); ?>
			</td>
		</tr>
		<tr>
			<td>
				<strong><?php echo JText::_( 'Created' ); ?></strong>
			</td>
			<td>
				<?php
				if ( $this->row->created == $nullDate ) {
					echo JText::_( 'New document' );
				} else {
					echo JHTML::_('date',  $this->row->created,  JText::_('DATE_FORMAT_LC2') );
				}
				?>
			</td>
		</tr>
		<tr>
			<td>
				<strong><?php echo JText::_( 'Modified' ); ?></strong>
			</td>
			<td>
				<?php
					if ( $this->row->modified == $nullDate ) {
						echo JText::_( 'Not modified' );
					} else {
						echo JHTML::_('date',  $this->row->modified, JText::_('DATE_FORMAT_LC2'));
					}
				?>
			</td>
		</tr>
		</table>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Parameters' ); ?></legend>

		<?php
			jimport('joomla.html.pane');
			$pane =& JPane::getInstance('sliders');
			echo $pane->startPane("menu-pane");
			$groups = $this->params->getGroups();
			if(count($groups)) {
				foreach($groups as $groupname => $group) {
					if($groupname == '_default') {
						$title = 'Article';
					} else {
						$title = ucfirst($groupname);
					}
					if($this->params->getNumParams($groupname)) {
						echo $pane->startPanel(JText :: _('Parameters - '.$title), $groupname.'-page');
						echo $this->params->render('params', $groupname);
						echo $pane->endPanel();
					}

				}
			}
			echo $pane->endPane();		?>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
<input type="hidden" name="cid[]" value="<?php echo $this->row->id; ?>" />
<input type="hidden" name="version" value="<?php echo $this->row->version; ?>" />
<input type="hidden" name="mask" value="0" />
<input type="hidden" name="option" value="<?php echo $option;?>" />
<input type="hidden" name="task" value="" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>