<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access'); ?>

<script language="JavaScript" type="text/javascript">
<!--
	function validateForm(frm, task) {

		var valid_site = document.formvalidator.isValid(frm, 'vars[siteName]');
		var valid_email = document.formvalidator.isValid(frm, 'vars[adminEmail]');
		var valid_password = document.formvalidator.isValid(frm, 'vars[adminPassword]');
		var confirm_password = document.formvalidator.isValid(frm, 'vars[confirmAdminPassword]');

		var siteName 				= getElementByName(frm, 'vars[siteName]');
		var adminEmail 				= getElementByName(frm, 'vars[adminEmail]');
		var adminPassword 			= getElementByName(frm, 'vars[adminPassword]');
		var confirmAdminPassword 	= getElementByName(frm, 'vars[confirmAdminPassword]');

		if (siteName.value == '' || !valid_site) {
			alert('<?php echo JText::_('warnSiteName', true) ?>');
		} else if (this.document.filename.migstatus.value == '1' && this.document.filename.dataLoaded.value == '1') {
			submitForm(frm, task); // Migration doesn't need email or admin passord
		} else if (adminEmail.value == '' || !valid_email) {
			alert('<?php echo JText::_('warnEmailAddress', true) ?>');
		} else if (adminPassword.value == '' || !valid_password) {
			alert('<?php echo JText::_('warnAdminPassword', true) ?>');
		} else if (adminPassword.value != confirmAdminPassword.value || !confirm_password) {
			alert('<?php echo JText::_('warnAdminPasswordDoesntMatch', true) ?>');
		} else {
			if (this.document.filename.dataLoaded.value == '1' || confirm('<?php echo JText::_('warnNoData', true) ?>')) {
				submitForm(frm, task);
			} else {
				return;
			}
		}
	}

	function selectMode() {
		var frm = this.document.filename;
		if (frm.what_to_load[0].checked) {
			frm.instDefault.disabled = false;
			frm.sqlFile.disabled = true;
			frm.oldPrefix.disabled = true;
			frm.srcEncoding.disabled = true;
			frm.migrationLoad.disabled = true;
			frm.migration.disabled = true;
			frm.sqlUploaded.disabled = true;
		} else if (frm.what_to_load[1].checked) {
			frm.instDefault.disabled = true;
			frm.sqlFile.disabled = false;
			frm.oldPrefix.disabled = false;
			frm.srcEncoding.disabled = false;
			frm.migrationLoad.disabled = false;
			frm.migration.disabled = false;
			frm.sqlUploaded.disabled = false;
		}
	}

	function JDefault() {
		this.document.filename.dataLoaded.value = '1';
		xajax_instDefault(xajax.getFormValues('filename'));
	}

	function JMigration() {

		var frm = this.document.filename;

		if (frm.sqlFile.value == '' && !frm.sqlUploaded.checked) {
			alert('<?php echo JText::_('No file selected', true) ?>');
			return;
		}

		frm.migrationupload.value = '1';
		frm.dataLoaded.value = '1';

		this.document.filename.dataLoaded.value = '1';
		xajax_instDefault(xajax.getFormValues('filename'));
	}

	function clearPasswordFields(frm) {
		var adminPassword 			= getElementByName(frm, 'vars[adminPassword]');
		var confirmAdminPassword 	= getElementByName(frm, 'vars[confirmAdminPassword]');

		if (adminPassword.defaultValue == adminPassword.value || confirmAdminPassword.defaultValue == confirmAdminPassword.value) {
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
							<div class="button1-right"><div class="prev"><a onclick="submitForm(adminForm, 'ftpconfig');" title="<?php echo JText::_('Previous', true) ?>"><?php echo JText::_('Previous') ?></a></div></div>
							<div class="button1-left"><div class="next"><a onclick="validateForm(adminForm, 'saveconfig');" title=<?php echo JText::_('Next', true) ?>"><?php echo JText::_('Next') ?></a></div></div>
					<?php else: ?>
							<div class="button1-right"><div class="prev"><a onclick="validateForm(adminForm, 'saveconfig');" title="<?php echo JText::_('Next', true) ?>"><?php echo JText::_('Next') ?></a></div></div>
							<div class="button1-left"><div class="next"><a onclick="submitForm(adminForm, 'ftpconfig');" title="<?php echo JText::_('Previous', true) ?>"><?php echo JText::_('Previous') ?></a></div></div>
					<?php endif; ?>
				</div>
				<span class="step"><?php echo JText::_('Main Configuration') ?></span>
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

			<form action="index.php" method="post" name="adminForm" id="adminForm" class="form-validate">
			<div id="installerpad">


		<div class="newsection"></div>
				<h2><?php echo JText::_('Site Name') ?>:</h2>
				<div class="install-text">
					<?php echo JText::_('enterSiteName') ?>
				</div>
				<div class="install-body">
				<div class="t">
			<div class="t">
				<div class="t"></div>
			</div>
			</div>
			<div class="m">
						<fieldset>
							<table class="content2">
								<tr>
									<td class="item">
									<label for="siteName">
										<span id="sitenamemsg"><?php echo JText::_('Site name') ?></span>
									</label>
									</td>
									<td align="center">
									<input class="inputbox validate required sitename sitenamemsg" type="text" id="siteName" name="vars[siteName]" size="30" value="<?php echo $this->getSessionVar('siteName') ?>" />
									</td>
								</tr>
							</table>
						</fieldset>
			</div>
			<div class="b">
			<div class="b">
				<div class="b"></div>
			</div>
			</div>
					<div class="clr"></div>
				</div>

		<div class="newsection"></div>
				<h2><?php echo JText::_('confTitle') ?></h2>
				<div class="install-text">
					<?php echo JText::_('tipConfSteps') ?>
				</div>
				<div class="install-body">
				<div class="t">
			<div class="t">
				<div class="t"></div>
			</div>
			</div>
			<div class="m">
					<fieldset>
						<table class="content2">
						<tr>
							<td class="item">
							<label for="adminEmail">
								<span id="emailmsg"><?php echo JText::_('Your E-mail') ?></span>
							</label>
							</td>
							<td align="center">
							<input class="inputbox validate required email emailmsg" type="text" id="adminEmail" name="vars[adminEmail]" value="" size="30" />
							</td>
						</tr>
						<tr>
							<td class="item">
							<label for="adminPassword">
								<span id="passwordmsg"><?php echo JText::_('Admin password') ?></span>
							</label>
							</td>
							<td align="center">
							<input onfocus="clearPasswordFields(adminForm);" class="inputbox validate required password passwordmsg" type="password" id="adminPassword" name="vars[adminPassword]" value="" size="30"/>
							</td>
						</tr>
						<tr>
							<td class="item">
							<label for="confirmAdminPassword">
								<span id="confirmpasswordmsg"><?php echo JText::_('Confirm admin password') ?></span>
							</label>
							</td>
							<td align="center">
							<input class="inputbox validate required confirmpassword confirmpasswordmsg" type="password" id="confirmAdminPassword" name="vars[confirmAdminPassword]" value="" size="30"/>
							</td>
						</tr>
						</table>
					</fieldset>
			</div>
			<div class="b">
			<div class="b">
				<div class="b"></div>
			</div>
			</div>
					<div class="clr"></div>
				</div>

			</div>
			<input type="hidden" name="task" value="" />
			</form>

			<div class="clr"></div>

			<form enctype="multipart/form-data" action="index.php" method="post" name="filename" id="filename">

				<h2><?php echo JText::_('loadSampleOrMigrate') ?></h2>
				<div class="install-text">
					<p><?php echo JText::_('LOADSQLINSTRUCTIONS1') ?></p>
					<p><?php echo JText::_('LOADSQLINSTRUCTIONS2') ?></p>
					<p><?php echo JText::_('LOADSQLINSTRUCTIONS3') ?></p>
					<p><?php echo JText::_('LOADSQLINSTRUCTIONS4') ?></p>
					<p><?php echo JText::_('LOADSQLINSTRUCTIONS5') ?></p>
					<p><?php echo JText::_('LOADSQLINSTRUCTIONS6') ?></p>
					<p><?php echo JText::_('LOADSQLINSTRUCTIONS7') ?></p>
				</div>
				<div class="install-body">
				<div class="t">
			<div class="t">
				<div class="t"></div>
			</div>
			</div>
			<div class="m">
						<fieldset>
							<table class="content2">
							<tr>
								<td width="5%"></td>
								<td width="25%"></td>
								<td width="70%"></td>
							</tr>
							<tr>
								<td>
									<input id="default_sample" type="radio" name="what_to_load" onclick="selectMode();"/>
								</td>
								<td>
									<label for="default_sample">
										<?php echo JText::_('Install default sample data') ?>
									</label>
								</td>
								<td>
									<em>
									<?php echo JText::_('tipInstallDefault') ?>
									</em>
								</td>
							</tr>
							<tr>
								<td></td>
								<td colspan="2">
									<span id="theDefault"><input class="button" type="button" name="instDefault" value="<?php echo JText::_('clickToInstallDefault') ?>" onclick="JDefault();"/></span>
								</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td></td>
								<td></td>
							</tr>

							<tr>
								<td valign="top">
									<input id="migrate_sql" type="radio" name="what_to_load" onclick="selectMode();"/>
								</td>
								<td valign="top">
									<label for="migrate_sql">
										<?php echo JText::_('migrateTitle') ?>
									</label>
								</td>
								<td>
									<em>
									<?php echo JText::_('tipLoadMigration') ?>
									</em>
									<br />
									<em>
									<?php echo JText::_('tipLoadSql') ?>
									</em>

								</td>
							</tr>
							<tr>
								<td></td>
								<td>
									<?php echo JText::_('Maximum Upload Size') ?>
								</td>
								<td>
									<p><?php echo $this->maxupload ?></p>
								</td>
							</tr>
							<tr>
								<td></td>
								<td>
									<label for="oldPrefix">
										<?php echo JText::_('Old table prefix') ?>
									</label>
								</td>
								<td>
									<input class="inputbox" type="text" id="oldPrefix" name="vars[oldPrefix]" value="" size="24" />
								</td>
							</tr>
							<tr>
								<td></td>
								<td>
									<label for="srcEncoding">
										<?php echo JText::_('Old site encoding') ?>
									</label>
								</td>
								<td>
									<!--<input class="inputbox" type="text" id="srcEncoding" name="vars[srcEncoding]" value="" size="24" />-->
									<select id="srcEncoding" name="vars[srcEncoding]" class="inputbox" >
									<?php foreach ($this->encodings as $encoding) : ?>
										<option value="<?php echo $encoding ?>" ><?php echo $encoding ?></option>
									<?php endforeach; ?>
									</select>
								</td>
							</tr>
							<tr>
								<td></td>
								<td valign="top">
									<label for="migration_script">
										<?php echo JText::_('Migration Script') ?>
									</label>
								</td>
								<td>
									<input class="input_box" id="migration_script" name="sqlFile" type="file" size="20"  />
									<br />
									<input class="input_box" id="sqlUploaded" name="sqlUploaded" type="checkbox" /><?php echo JText::_('tipUploaded') ?>
									<br />
									<input class="input_box" id="migration" name="migration" type="checkbox" /><?php echo JText::_('tipMigration') ?>
									<br />
									<input class="button" type="button" name="migrationLoad" value="<?php echo JText::_('Upload and execute') ?>" onclick="JMigration();" />
									<br /><br />
									<span id="theMigrationResponse"><?php echo $this->getSessionVar('dircheck') ?> <?php echo $this->getSessionVar('migresponse') ?></span>
								</td>
							</tr>

							</table>

						</fieldset>
					</div>
				<div class="b">
					<div class="b">
						<div class="b"></div>
					</div>
				</div>
			</div>
  			<input type="hidden" name="task" value="mainconfig" />
  			<input type="hidden" name="sqlupload" value="0" />
  			<input type="hidden" name="migrationupload" value="0" />
			<input type="hidden" name="loadchecked" value="<?php echo $this->getSessionVar('loadchecked') ?>" />
  			<input type="hidden" name="dataLoaded" value="<?php echo $this->getSessionVar('dataloaded') ?>" />
  			<input type="hidden" name="migstatus" value="<?php echo $this->getSessionVar('migstatus') ?>" />
  		</form>


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


<script language="JavaScript" type="text/javascript">
	document.filename.what_to_load[document.filename.loadchecked.value].checked = true;
	selectMode();
	if (this.document.filename.migstatus.value == '1') {
		this.document.filename.what_to_load.disabled = 1;
		this.document.filename.instDefault.disabled = 1;
		this.document.filename.default_sample.disabled = 1;
		this.document.filename.migrate_sql.disabled = 1;
	}
</script>
