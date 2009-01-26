<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access'); ?>

<script language="JavaScript" type="text/javascript">
<!--

	Window.addEvent('domready', function(){
	document.formvalidator.handlers['isftp'] = { enabled : true,
									exec : function (value) {
										if (document.getElementById('ftpenable').checked == true) {
											if (value == '') {
												return false;
											} else {
												return true;
											}
										} else {
											return true;
										}
									}
									}
	});

	function validateForm(frm, task) {
		var valid = document.formvalidator.isValid(frm);
		if (valid == false) {
			return false;
		}

		var ftpEnable = document.getElementById("ftpenable");
		var ftpRoot = document.getElementById("ftproot");

		if (ftpEnable.checked == false) {
			submitForm(frm, task);
		} else if (ftpRoot.value == '') {
			alert('<?php echo JText::_('warnFtpRoot', true) ?>');
			return;
		} else {
			submitForm(frm, task);
		}
	}

	function doFTPVerify() {
		xajax_FTPVerify(xajax.getFormValues('adminForm'));
	}

	function JProcess() {

		if (document.getElementById("ftphost").value == '') {
			alert('<?php echo JText::_('validFtpHost', true) ?>');
			return;
		} else if (document.getElementById("ftpuser").value == '') {
			alert('<?php echo JText::_('validFtpUser', true) ?>');
			return;
		} else if (document.getElementById("ftppass").value == '') {
			alert('<?php echo JText::_('validFtpPass', true) ?>');
			return;
		} else {
			xajax_getFtpRoot(xajax.getFormValues('adminForm'));
		}
	}
//-->
</script>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="form-validate" autocomplete="off">

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
						<div class="button1-right"><div class="prev"><a onclick="submitForm(adminForm, 'dbconfig');" title="<?php echo JText::_('Previous', true) ?>"><?php echo JText::_('Previous') ?></a></div></div>
						<div class="button1-left"><div class="next"><a onclick="validateForm(adminForm, 'mainconfig');" title="<?php echo JText::_('Next', true) ?>"><?php echo JText::_('Next') ?></a></div></div>
					<?php else : ?>
						<div class="button1-right"><div class="prev"><a onclick="validateForm(adminForm, 'mainconfig');" title="<?php echo JText::_('Next', true) ?>"><?php echo JText::_('Next') ?></a></div></div>
						<div class="button1-left"><div class="next"><a onclick="submitForm(adminForm, 'dbconfig');" title="<?php echo JText::_('Previous', true) ?>"><?php echo JText::_('Previous') ?></a></div></div>
					<?php endif; ?>
				</div>
				<span class="step"><?php echo JText::_('FTP Configuration') ?></span>
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

				<h2><?php echo JText::_('FTP Configuration') ?>:</h2>
				<div class="install-text">
					<?php echo JText::_('tipFtpConfSteps') ?>
				</div>
				<div class="install-body">
					<div class="t">
						<div class="t">
							<div class="t"></div>
						</div>
					</div>
					<div class="m">

						<h3 class="title-smenu" title="<?php echo JText::_('Basic') ?>"><?php echo JText::_('Basic Settings') ?></h3>
						<div class="section-smenu">
							<table class="content2">
								<tr>
									<td width="100">
										<input id="ftpenable" type="radio" name="vars[ftpEnable]" value="1" />
										<label for="vars_ftpenable">
											<?php echo JText::_('Yes') ?>
										</label>
										<br />
										<input id="ftpdisable" type="radio" name="vars[ftpEnable]" value="0" checked="checked" />
										<label for="vars_ftpdisable">
											<?php echo JText::_('No') ?>
										</label>
									</td>
									<td align="justify">
										<?php echo JText::_('ENABLEFTPDESC') ?>
									</td>
								</tr>
								<tr>
									<td width="100">
										<label for="ftpuser">
											<span id="ftpusermsg"><?php echo JText::_('FTP user') ?></span>
										</label>
									</td>
									<td align="center">
										<input class="inputbox validate notrequired isftp ftpusermsg" type="text" id="ftpuser" name="vars[ftpUser]" value="<?php echo $this->getSessionVar('ftpUser') ?>" size="30"/>
									</td>
								</tr>
								<tr>
									<td>
										<label for="ftppass">
											<span id="ftppassmsg"><?php echo JText::_('FTP password') ?></span>
										</label>
									</td>
									<td align="center">
										<input class="inputbox validate notrequired isftp ftppassmsg" type="password" id="ftppass" name="vars[ftpPassword]" value="<?php echo $this->getSessionVar('ftpPassword') ?>" size="30"/>
									</td>
								</tr>
								<tr id="rootPath">
									<td>
										<label for="ftproot">
											<span id="ftprootmsg"><?php echo JText::_('FTP Root Path') ?></span>
										</label>
									</td>
									<td align="center">
										<input class="inputbox validate notrequired isftp ftprootmsg" id="ftproot" type="text" name="vars[ftpRoot]" value="<?php echo $this->getSessionVar('ftpRoot') ?>" size="30"/>
									</td>
								</tr>
							</table>
							<input type="button" id="findbutton" class="button" value="<?php echo JText::_('Autofind FTP Path') ?>" onclick="JProcess();" />
							<input type="button" id="verifybutton" class="button" value="<?php echo JText::_('Verify FTP Settings') ?>" onclick="doFTPVerify();" />
							<br /><br />
						</div>

						<h3 class="title-smenu moofx-toggler" title="<?php echo JText::_('Advanced') ?>"><?php echo JText::_('Advanced settings') ?></h3>
						<div class="section-smenu moofx-slider">
							<table class="content2">
								<tr id="host">
									<td width="100">
										<label for="ftphost">
											<?php echo JText::_('FTP host') ?>
										</label>
									</td>
									<td align="center">
										<input class="inputbox" type="text" id="ftphost" name="vars[ftpHost]" value="<?php echo $this->getSessionVar('ftpHost', 'localhost') ?>" size="30"/>
									</td>
								</tr>
								<tr id="port">
									<td width="100">
										<label for="ftpport">
											<?php echo JText::_('FTP port') ?>
										</label>
									</td>
									<td align="center">
										<input class="inputbox" type="text" id="ftpport" name="vars[ftpPort]" value="<?php echo $this->getSessionVar('ftpPort', 21) ?>" size="30"/>
									</td>
								</tr>
								<tr>
									<td width="100">
										<label for="ftpsavepass">
											<?php echo JText::_('Save FTP Password') ?>
										</label>
									</td>
									<td align="justify">
										<input id="ftpsavepass" type="radio" name="vars[ftpSavePass]" value="1" />
										<label for="ftpsavepass">
											<?php echo JText::_('Yes') ?>
										</label>
										<br />
										<input id="ftpnosavepass" type="radio" name="vars[ftpSavePass]" value="0" checked="checked" />
										<label for="ftpnosavepass">
											<?php echo JText::_('No') ?>
										</label>
									</td>
								</tr>
							</table>
						</div>
						<div class="clr"></div>
					</div>
					<div class="b">
						<div class="b">
							<div class="b"></div>
						</div>
					</div>
					<div class="clr"></div>
				</div>
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

<input type="hidden" name="task" value="" />
</form>

<script type="text/javascript">
	//Element.cleanWhitespace('content');
	//init();
</script>
