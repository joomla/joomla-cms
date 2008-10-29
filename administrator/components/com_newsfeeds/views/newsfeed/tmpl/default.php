<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php JHtml::_('behavior.tooltip'); ?>
<?php JRequest::setVar( 'hidemainmenu', 1 ); ?>

<script language="javascript" type="text/javascript">
function submitbutton(pressbutton) {
	var form = document.adminForm;
	if (pressbutton == 'cancel') {
		submitform( pressbutton );
		return;
	}

	// do field validation
	if (form.name.value == '') {
		alert( "<?php echo JText::_( 'Please fill in the newsfeed name.', true ); ?>" );
	} else if (form.catid.value == 0) {
		alert( "<?php echo JText::_( 'Please select a Category.', true ); ?>" );
	} else if (form.link.value == '') {
		alert( "<?php echo JText::_( 'Please fill in the newsfeed link.', true ); ?>" );
	} else if (getSelectedValue('adminForm','catid') < 0) {
		alert( "<?php echo JText::_( 'Please select a category.', true ); ?>" );
	} else if (form.numarticles.value == "" || form.numarticles.value == 0) {
		alert( "<?php echo JText::_( 'VALIDARTICLESDISPLAY', true ); ?>" );
	} else if (form.cache_time.value == "" || form.cache_time.value == 0) {
		alert( "<?php echo JText::_( 'Please fill in the cache refresh time.', true ); ?>" );
	} else {
		submitform( pressbutton );
	}
}
</script>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">

<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Details' ); ?></legend>

		<table class="admintable">
		<tr>
			<td width="170" class="key">
				<label for="name">
					<?php echo JText::_( 'Name' ); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" size="40" name="name" id="name" value="<?php echo $this->newsfeed->name; ?>" />
			</td>
		</tr>
		<tr>
			<td width="170" class="key">
				<label for="name">
					<?php echo JText::_( 'Alias' ); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" size="40" name="alias" id="alias" value="<?php echo $this->newsfeed->alias; ?>" />
			</td>
		</tr>
		<tr>
			<td valign="top" align="right" class="key">
				<?php echo JText::_( 'Published' ); ?>:
			</td>
			<td>
				<?php echo JHtml::_('select.booleanlist',  'published', 'class="inputbox"', $this->newsfeed->published ); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="catid">
					<?php echo JText::_( 'Category' ); ?>
				</label>
			</td>
			<td>
				<?php echo JHtml::_('list.category',  'catid', $option, intval( $this->newsfeed->catid ) ); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="link">
					<?php echo JText::_( 'Link' ); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" size="60" name="link" id="link" value="<?php echo $this->newsfeed->link; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="numarticles">
					<?php echo JText::_( 'Number of Articles' ); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" size="2" name="numarticles" id="numarticles" value="<?php echo $this->newsfeed->numarticles; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'TIPCACHETIME' ); ?>">
			<?php echo JText::_( 'Cache time' ); ?>
		</span>
			</td>
			<td>
				<input class="inputbox" type="text" size="4" name="cache_time" id="cache_time" value="<?php echo $this->newsfeed->cache_time; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="ordering">
					<?php echo JText::_( 'Ordering' ); ?>
				</label>
			</td>
			<td>
				<?php echo JHtml::_('list.specificordering',  $this->newsfeed, $this->newsfeed->id, $this->order_query, 1 ); ?>
			</td>
		</tr>
		<?php
			$isRtl = '';
			if ($this->newsfeed->rtl == 1) {
				$isRtl = 'checked="checked"';
			}
		?>
		<tr>
			<td class="key">
				<label for="rtl">
					<?php echo JText::_( 'RTL feed' ); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="checkbox" name="rtl" id="rtl" value="1" <?php echo $isRtl; ?>  />
			</td>
		</tr>
		<tr>
			<td colspan="2" align="center">
			</td>
		</tr>
		</table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
<input type="hidden" name="id" value="<?php echo $this->newsfeed->id; ?>" />
<input type="hidden" name="cid[]" value="<?php echo $this->newsfeed->id; ?>" />
<input type="hidden" name="option" value="<?php echo $option; ?>" />
<input type="hidden" name="task" value="" />
</form>