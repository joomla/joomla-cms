<?php
/**
 * @version		$Id: default.php 329 2009-05-27 22:13:23Z andrew.eddie $
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
		<div class="step-on">
			4 : <?php echo JText::_('Database'); ?>
		</div>
		<div class="step-off">
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
					<div class="button1-right"><div class="prev"><a href="index.php?view=license" title="<?php echo JText::_('Previous'); ?>"><?php echo JText::_('Previous'); ?></a></div></div>
					<div class="button1-left"><div class="next"><a onclick="validateForm(adminForm, 'setup.database');" title="<?php echo JText::_('Next'); ?>"><?php echo JText::_('Next'); ?></a></div></div>
<?php elseif ($this->document->direction == 'rtl') : ?>
					<div class="button1-right"><div class="prev"><a onclick="validateForm(adminForm, 'setup.database');" title="<?php echo JText::_('Next'); ?>"><?php echo JText::_('Next'); ?></a></div></div>
					<div class="button1-left"><div class="next"><a href="index.php?view=license" title="<?php echo JText::_('Previous'); ?>"><?php echo JText::_('Previous'); ?></a></div></div>
<?php endif; ?>
				</div>
				<span class="step"><?php echo JText::_('Database Configuration'); ?></span>
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
				<h2><?php echo JText::_('Connection Settings'); ?>:</h2>
				<div class="install-text">
						<?php echo JText::_('
							<p>Setting up Joomla! to run on your server involves 4 simple steps...</p>
							<p>Please enter the hostname of the server Joomla! is to be installed on.</p>
							<p>Enter the MySQL username, password and database name you wish to use with Joomla.</p>
							<p>Enter a prefix to be used by tables for this Joomla! installation. Select how to handle exisitng tables from a previous installation.</p>
							<p>Install the samples unless you are experienced want to start with a virtually empty site.</p>
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
									<td></td>
									<td></td>
									<td></td>
								</tr>
								<tr>
									<td colspan="2">
										<label for="vars_dbtype"><?php echo JText::_('Database Type'); ?></label>
										<br />
										<select id="vars_dbtype" name="vars[DBtype]" class="inputbox" size="1">
										<option value=""><?php echo JText::_('Select Type'); ?></option>
<?php foreach ($this->dbOptions as $option) : ?>
											<option value="<?php echo $option->value; ?>"<?php echo !empty($option->selected) ? $option->selected : ''; ?>><?php echo $option->text; ?></option>
<?php endforeach; ?>
										</select>
									</td>
									<td>
										<em>
										<?php echo JText::_('This is probably "mysql"'); ?>
										</em>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<label for="vars_dbhostname">
											<span id="dbhostnamemsg"><?php echo JText::_('Host Name'); ?></span>
										</label>
										<br />
										<input id="vars_dbhostname" class="inputbox validate required none dbhostnamemsg" type="text" name="vars[DBhostname]" value="<?php echo !empty($this->options['DBhostname']) ? $this->options['DBhostname'] : 'localhost'; ?>" />
									</td>
									<td>
										<em>
										<?php echo JText::_('This is usually "localhost"'); ?>
										</em>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<label for="vars_dbusername">
											<span id="dbusernamemsg"><?php echo JText::_('User Name'); ?></span>
										</label>
										<br />
										<input id="vars_dbusername" class="inputbox validate required none dbusernamemsg" type="text" name="vars[DBuserName]" value="<?php echo !empty($this->options['DBuserName']) ? $this->options['DBuserName'] : ''; ?>" />
									</td>
									<td>
										<em>
										<?php echo JText::_('Either something as "root" or a username given by the hoster'); ?>
										</em>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<label for="vars_dbpassword">
											<?php echo JText::_('Password'); ?>
										</label>
										<br />
										<input id="vars_dbpassword" class="inputbox" type="password" name="vars[DBpassword]" value="<?php echo !empty($this->options['DBpassword']) ? $this->options['DBpassword'] : ''; ?>" />
									</td>
									<td>
										<em>
										<?php echo JText::_('For site security using a password for the mysql account is mandatory'); ?>
										</em>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<label for="vars_dbname">
											<span id="dbnamemsg"><?php echo JText::_('Database Name'); ?></span>
										</label>
										<br />
										<input id="vars_dbname" class="inputbox validate required none dbnamemsg" type="text" name="vars[DBname]" value="<?php echo !empty($this->options['DBname']) ? $this->options['DBname'] : ''; ?>" />
									</td>
									<td>
										<em>
										<?php echo JText::_('Some hosts allow only a certain DB name per site. Use table prefix in this case for distinct Joomla! sites.'); ?>
										</em>
									</td>
								</tr>
							</table>
							<br /><br />
						</div>

						<h3 class="title-smenu moofx-toggler" title="<?php echo JText::_('Advanced'); ?>">
							<?php echo JText::_('Advanced settings'); ?>
						</h3>
						<div class="section-smenu moofx-slider">
							<table class="content2">
								<tr>
									<td></td>
									<td></td>
									<td></td>
								</tr>
								<tr>
									<td>
										<input id="vars_dbolddel" type="radio" name="vars[DBOld]" value="rm" />
									</td>
									<td>
										<label for="vars_dbolddel">
											<?php echo JText::_('Drop Existing Tables'); ?>
										</label>
									</td>
									<td></td>
								</tr>
								<tr>
									<td>
										<input id="vars_dboldbackup" type="radio" name="vars[DBOld]" value="bu"  checked="checked"/>
									</td>

									<td>
										<label for="vars_dboldbackup">
											<?php echo JText::_('Backup Old Tables'); ?>
										</label>
									</td>

									<td>
										<em>
										<?php echo JText::_('Any existing backup tables from former Joomla! installations will be replaced'); ?>
										</em>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<label for="vars_dbprefix">
											<?php echo JText::_('Table Prefix'); ?>
										</label>
										<br />
										<input id="vars_dbprefix" class="inputbox" type="text" name="vars[DBPrefix]" value="<?php echo !empty($this->options['DBPrefix']) ? $this->options['DBPrefix'] : 'jos_'; ?>" />
									</td>
									<td>
										<em>
										<?php echo JText::_('Dont use "old_" since this is used for backup tables'); ?>
										</em>
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
