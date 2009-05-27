<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the JavaScript behaviors.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>

<script language="JavaScript" type="text/javascript">
<!--
	function validateForm(frm, task) {
		Joomla.submitform(task);
	}
// -->
</script>

<div id="stepbar">
	<div class="t">
		<div class="t">
			<div class="t"></div>
		</div>
	</div>
	<div class="m">
		<h1><?php echo JText::_('Steps'); ?></h1>
		<div class="step-off">
			1 : <?php echo JText::_('Language'); ?>
		</div>
		<div class="step-off">
			2 : <?php echo JText::_('Pre-Installation check'); ?>
		</div>
		<div class="step-off">
			3 : <?php echo JText::_('License'); ?>
		</div>
		<div class="step-off">
			4 : <?php echo JText::_('Database'); ?>
		</div>
		<div class="step-on">
			5 : <?php echo JText::_('FTP Configuration'); ?>
		</div>
		<div class="step-off">
			6 : <?php echo JText::_('Configuration'); ?>
		</div>
		<div class="step-off">
			7 : <?php echo JText::_('Finish'); ?>
		</div>
		<div class="box"></div>
  	</div>
	<div class="b">
		<div class="b">
			<div class="b"></div>
		</div>
	</div>
</div>

<form action="index.php" method="post" name="adminForm">
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
<?php if ($this->document->direction == 'ltr') : ?>
					<div class="button1-right"><div class="prev"><a href="index.php?view=database" title="<?php echo JText::_('Previous'); ?>"><?php echo JText::_('Previous'); ?></a></div></div>
					<div class="button1-left"><div class="next"><a onclick="validateForm(adminForm, 'setup.filesystem');" title="<?php echo JText::_('Next'); ?>"><?php echo JText::_('Next'); ?></a></div></div>
<?php elseif ($this->document->direction == 'rtl') : ?>
					<div class="button1-right"><div class="prev"><a onclick="validateForm(adminForm, 'setup.filesystem');" title="<?php echo JText::_('Next'); ?>"><?php echo JText::_('Next'); ?></a></div></div>
					<div class="button1-left"><div class="next"><a href="index.php?view=database" title="<?php echo JText::_('Previous'); ?>"><?php echo JText::_('Previous'); ?></a></div></div>
<?php endif; ?>
				</div>
				<span class="step"><?php echo JText::_('FTP Configuration'); ?></span>
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
				<h2>
					<?php echo JText::_('FTP Configuration'); ?>:
				</h2>
				<div class="install-text">
					<?php echo JText::_('
						<p>Due to filesystem permission restrictions and PHP Safe Mode restrictions.
						For all users to utilize the Joomla! installers an FTP layer exists to handle
						filesystem manipulation.
						<br />
						<br />
						Enter an FTP username and password with access to the Joomla! root directory,
						this will be the FTP account that handles all filesystem operations when Joomla!
						requires FTP access to complete a task.
						<br />
						<br />
						For security reasons, it is best if a separate FTP user account is created with
						access only to the Joomla! installation.</p>
					'); ?>
				</div>
				<div class="install-body">
					<div class="t">
						<div class="t">
							<div class="t"></div>
						</div>
					</div>
					<div class="m">
						<h3 class="title-smenu" title="<?php echo JText::_('Basic'); ?>">
							<?php echo JText::_('Basic Settings'); ?>
						</h3>
						<div class="section-smenu">
							<table class="content2">
								<tr>
									<td width="100">
										<input id="ftpenable" type="radio" name="vars[ftpEnable]" value="1" />
										<label for="ftpenable"><?php echo JText::_('Yes'); ?></label><br />

										<input id="ftpdisable" type="radio" name="vars[ftpEnable]" value="0" checked="checked" />
										<label for="ftpdisable"> <?php echo JText::_('No'); ?> </label>
									</td>
									<td align="justify">
										<?php echo JText::_('Enable FTP filesystem layer'); ?>
									</td>
								</tr>
								<tr>
									<td width="100">
										<label for="ftpuser"><span id="ftpusermsg"><?php echo JText::_('FTP user'); ?></span></label>
									</td>
									<td align="center">
										<input class="inputbox validate notrequired isftp ftpusermsg" type="text" id="ftpuser" name="vars[ftpUser]" value="<?php echo !empty($this->options['ftpUser']) ? $this->options['ftpUser'] : ''; ?>" size="30" />
									</td>
								</tr>
								<tr>
									<td>
										<label for="ftppass"> <span id="ftppassmsg"><?php echo JText::_('FTP password'); ?></span></label>
									</td>
									<td align="center">
										<input class="inputbox validate notrequired isftp ftppassmsg" type="password" id="ftppass" name="vars[ftpPassword]" value="<?php echo !empty($this->options['ftpPassword']) ? $this->options['ftpPassword'] : ''; ?>" size="30" />
									</td>
								</tr>
								<tr id="rootPath">
									<td>
										<label for="ftproot"> <span id="ftprootmsg"><?php echo JText::_('FTP Root Path'); ?></span></label>
									</td>
									<td align="center">
										<input class="inputbox validate notrequired isftp ftprootmsg" id="ftproot" type="text" name="vars[ftpRoot]" value="<?php echo !empty($this->options['ftpRoot']) ? $this->options['ftpRoot'] : ''; ?>" size="30" />
									</td>
								</tr>
							</table>

							<input type="button" id="findbutton" class="button" value="<?php echo JText::_('Autofind FTP Path'); ?>" onclick="Install.detectFtpRoot(this);" />
							<input type="button" id="verifybutton" class="button" value="<?php echo JText::_('Verify FTP Settings'); ?>" onclick="Install.verifyFtpSettings(this);" />
							<br /><br />
						</div>

						<h3 class="title-smenu moofx-toggler" title="<?php echo JText::_('Advanced'); ?>">
							<?php echo JText::_('Advanced settings'); ?>
						</h3>
						<div class="section-smenu moofx-slider">
							<table class="content2">
								<tr id="host">
									<td width="100">
										<label for="ftphost"> <?php echo JText::_('FTP host'); ?></label>
									</td>
									<td align="center">
										<input class="inputbox" type="text" id="ftphost" name="vars[ftpHost]" value="<?php echo !empty($this->options['ftpHost']) ? $this->options['ftpHost'] : ''; ?>" size="30" />
									</td>
								</tr>
								<tr id="port">
									<td width="100">
										<label for="ftpport"><?php echo JText::_('FTP port'); ?></label>
									</td>
									<td align="center">
										<input class="inputbox" type="text" id="ftpport" name="vars[ftpPort]" value="<?php echo !empty($this->options['ftpPort']) ? $this->options['ftpPort'] : ''; ?>" size="30" />
									</td>
								</tr>
								<tr>
									<td width="100">
										<label for="ftpsavepass"> <?php echo JText::_('Save FTP Password'); ?></label>
									</td>
									<td align="justify">
										<input id="ftpsavepass" type="radio" name="vars[ftpSavePass]" value="1" />
										<label for="ftpsavepass"><?php echo JText::_('Yes'); ?></label><br />

										<input id="ftpnosavepass" type="radio" name="vars[ftpSavePass]" value="0" checked="checked" />
										<label for="ftpnosavepass"><?php echo JText::_('No'); ?></label>
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
	<?php echo JHtml::_('form.token'); ?>
</form>
