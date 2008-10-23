<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access'); ?>

<script language="JavaScript" type="text/javascript">
<!--
	function validateForm(frm, task) {
		var valid = document.formvalidator.isValid(frm);
		submitForm(frm, task);
	}

	function selectMode() {
		var frm = this.document.filename;
		if(frm.what_to_load[0].checked) {
			frm.instDefault.disabled = false;
			frm.sqlFile.disabled = true;
			frm.sqlLoad.disabled = true;
			frm.oldPrefix.disabled = true;
			frm.srcEncoding.disabled = true;
			frm.migrationFile.disabled = true;
			frm.migrationLoad.disabled = true;
		} else if(frm.what_to_load[1].checked) {
			frm.instDefault.disabled = true;
			frm.sqlFile.disabled = false;
			frm.sqlLoad.disabled = false;
			frm.oldPrefix.disabled = true;
			frm.srcEncoding.disabled = true;
			frm.migrationFile.disabled = true;
			frm.migrationLoad.disabled = true;
		} else if(frm.what_to_load[2].checked) {
			frm.instDefault.disabled = true;
			frm.sqlFile.disabled = true;
			frm.sqlLoad.disabled = true;
			frm.oldPrefix.disabled = false;
			frm.srcEncoding.disabled = false;
			frm.migrationFile.disabled = false;
			frm.migrationLoad.disabled = false;
		}
	}

	function JDefault() {
		this.document.filename.dataLoaded.value = '1';
		xajax_instDefault(xajax.getFormValues('filename'));
	}

	function externalSql(frm, task) {

		if (frm.sqlFile.value == '') {
			alert('<?php echo JText::_('No file selected', true) ?>');
			return;
		} else {
			frm.sqlupload.value = '1';
			frm.dataLoaded.value = '1';
			submitForm(frm, task);
		}
	}

	function migrationSql(frm, task) {

		if (frm.migrationFile.value == '') {
			alert('<?php echo JText::_('No file selected', true) ?>');
			return;
		} else {
			frm.migrationupload.value = '1';
			frm.dataLoaded.value = '1';
			submitForm(frm, task);
		}
	}

	function clearPasswordFields(frm) {
		var adminPassword 			= getElementByName(frm, 'vars[adminPassword]');
		var confirmAdminPassword 	= getElementByName(frm, 'vars[confirmAdminPassword]');

		if(adminPassword.defaultValue == adminPassword.value || confirmAdminPassword.defaultValue == confirmAdminPassword.value) {
			adminPassword.value 		= '';
			confirmAdminPassword.value 	= '';
		}
		return;
	}
//-->
</script>

<div id="right">
	<div id="rightpad">
		<div id="step">
			<div class="t">
		<div class="t">
			<div class="t"></div>
		</div>
		</div>
		<div class="m">
				<div class="far-right">
					<?php if ($this->direction == 'ltr') : ?>
							<div class="button1-right"><div class="prev"><a onclick="submitForm(adminForm, 'mainconfig');" alt="<?php echo JText::_('Previous', true) ?>"><?php echo JText::_('Previous') ?></a></div></div>
							<div class="button1-left"><div class="next"><a onclick="validateForm(adminForm, 'mainconfig');" alt="<?php echo JText::_('Next', true) ?>"><?php echo JText::_('Next') ?></a></div></div>
					<?php else: ?>
							<div class="button1-right"><div class="prev"><a onclick="validateForm(adminForm, 'mainconfig');" alt="<?php echo JText::_('Next', true) ?>"><?php echo JText::_('Next') ?></a></div></div>
							<div class="button1-left"><div class="next"><a onclick="submitForm(adminForm, 'mainconfig');" alt="<?php echo JText::_('Previous', true) ?>"><?php echo JText::_('Previous') ?></a></div></div>
					<?php endif; ?>
				</div>
				<span class="step"><?php echo JText::_('Site Migration') ?></span>
			</div>
		<div class="b">
		<div class="b">
			<div class="b"></div>
		</div>
		</div>
	</div>

	<div id="installer">
			<div class="t">
		<div class="t">
			<div class="t"></div>
		</div>
		</div>
		<div class="m">

				<h2><?php echo JText::_('Migration Output') ?>:</h2>

				<iframe src="migration.html" name="migrationtarget" class="license" frameborder="0" marginwidth="25" scrolling="none"></iframe>

				<div class="clr"></div>
		</div>
		<div class="b">
		<div class="b">
			<div class="b"></div>
		</div>
		</div>
		</div>
	</div>
</div>

<div class="clr"></div>

<!-- Variables -->
<form action="index.php" method="post" name="adminForm" id="adminForm" class="form-validate">
	<input type="hidden" name="task" value="dumpLoad" />
	<input type="hidden" name="sqlupload" value="0" />
  	<input type="hidden" name="migrationupload" value="0" />
  	<input type="hidden" name="vars[migresponse]" value="<?php echo JText::_('Migration completed') ?>" />
  	<input type="hidden" name="vars[migstatus]" value="1" />
  	<input type="hidden" name="vars[dataLoaded]" value="1" />
  	<input type="hidden" name="vars[loadchecked]" value="1" />
</form>
<form action="index.php" method="post" name="migrateForm" id="migrateForm" class="form-validate" target="migrationtarget">
	<input type="hidden" name="task" value="dumpLoad" />
  	<input type="hidden" name="migration" value="<?php echo JRequest::getBool('migration', 0, 'post') ?>" />
  	<input type="hidden" name="start" value="1" />
	<input type="hidden" name="foffset" value="0" />
	<input type="hidden" name="totalqueries" value="0" />
</form>
<script language="JavaScript" type="text/javascript">window.setTimeout('submitForm(this.document.migrateForm,"dumpLoad")',500);</script>
