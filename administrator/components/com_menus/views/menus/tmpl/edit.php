<?php defined('_JEXEC') or die('Restricted access'); ?>

<script language="javascript" type="text/javascript">
<!--
	function submitbutton(pressbutton) {
		var form = document.adminForm;

		if (pressbutton == 'savemenu') {
			if ( form.menutype.value == '' ) {
				alert( '<?php echo JText::_( 'Please enter a menu name', true ); ?>' );
				form.menutype.focus();
				return;
			}
			var r = new RegExp("[\']", "i");
			if ( r.exec(form.menutype.value) ) {
				alert( '<?php echo JText::_( 'The menu name cannot contain a \'', true ); ?>' );
				form.menutype.focus();
				return;
			}
			<?php if ($this->isnew) : ?>
			if ( form.title.value == '' ) {
				alert( '<?php echo JText::_( 'Please enter a module name for your menu', true ); ?>' );
				form.title.focus();
				return;
			}
			<?php endif; ?>
			submitform( 'savemenu' );
		} else {
			submitform( pressbutton );
		}
	}
//-->
</script>
<form action="index.php" method="post" name="adminForm">

<table class="adminform">
	<tr>
		<td width="100" >
			<label for="menutype">
				<strong><?php echo JText::_( 'Menu Type' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="menutype" id="menutype" size="30" maxlength="25" value="<?php echo $this->row->menutype; ?>" />			<?php echo JHTML::_('tooltip', JText::_( 'TIPNAMEUSEDTOIDENTIFYMENU' )); ?>
		</td>
	</tr>
	<tr>
		<td width="100" >
			<label for="title">
				<strong><?php echo JText::_( 'Title' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="title" id="title" size="30" maxlength="255" value="<?php echo $this->row->title; ?>" />
			<?php echo JHTML::_('tooltip',  JText::_( 'A proper title for the Menu' ) ); ?>
		</td>
	</tr>
	<tr>
		<td width="100" >
			<label for="description">
				<strong><?php echo JText::_( 'Description' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="description" id="description" size="30" maxlength="255" value="<?php echo $this->row->description; ?>" />
			<?php echo JHTML::_('tooltip',  JText::_( 'A description for the Menu' ) ); ?>
		</td>
	</tr>
	<?php if ($this->isnew) : ?>
	<tr>
		<td width="100"  valign="top">
			<label for="module_title">
				<strong><?php echo JText::_( 'Module Title' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="module_title" id="module_title" size="30" value="" />
			<?php echo JHTML::_('tooltip',  JText::_( 'TIPTITLEMAINMENUMODULEREQUIRED' ) ); ?>
		</td>
	</tr>
	<?php endif; ?>
</table>

	<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
	<input type="hidden" name="option" value="com_menus" />
	<input type="hidden" name="task" value="savemenu" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>