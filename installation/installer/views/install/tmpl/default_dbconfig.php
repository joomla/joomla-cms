<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Installation
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

?>

<script language="JavaScript" type="text/javascript">
<!--
	function validateForm( frm, task ) {
		var valid = document.formvalidator.isValid(frm);
		if (valid == false) {
			return false;
		}
		var DBtype = getElementByName( frm, 'vars[DBtype]' );
		var DBhostname = getElementByName( frm, 'vars[DBhostname]' );
		var DBname = getElementByName( frm, 'vars[DBname]' );
		var DBPrefix = getElementByName( frm, 'vars[DBPrefix]' );

		var regex=/^[a-zA-Z]+[a-zA-Z0-9_]*$/;

		if ( DBtype.selectedIndex == 0 ) {
			alert( '<?php echo JText::_('Please select the database type', true ) ?>' );
			return;
		} else if (DBhostname.value == '') {
			alert( '<?php echo JText::_('Please enter the host name', true ) ?>' );
			return;
		} else if (DBname.value == '') {
			alert( '<?php echo JText::_('Please enter a database name', true ) ?>' );
			return;
		} else if (DBPrefix.value == '') {
			alert('<?php echo JText::_('You must enter a MySQL Table Prefix for Joomla to operate correctly', true ) ?>');
			return;
		} else if (DBname.value.length > 64) {
			alert('<?php echo JText::_('The MySQL Database Name must be a maximum of 64 characters', true ) ?>');
			return;
		} else if (DBPrefix.value.length > 15) {
			alert('<?php echo JText::_('The MySQL Table Prefix must be a maximum of 15 characters', true ) ?>');
			return;
		} else if (!regex.test(DBname.value)) {
			alert('<?php echo JText::_('The MySQL Database Name must start with a letter, and be followed by only letters, numbers or underscores', true ) ?>');
			return;
		} else if (!regex.test(DBPrefix.value)) {
			alert('<?php echo JText::_('The MySQL Table Prefix must start with a letter, and be followed by only letters, numbers or underscores', true ) ?>');
			return;
		} else {
			submitForm( frm, task );
		}
	}

	function JProcess( action ) {

		if ( document.getElementById("vars_dbtype").selectedIndex == 0 ) {
			alert( '<?php echo JText::_('Please select the database type', true ) ?>' );
			return;
		} else if (document.getElementById("vars_dbhostname").value == '') {
			alert( '<?php echo JText::_('Please enter the host name', true ) ?>' );
			return;
		} else if (document.getElementById("vars_dbusername").value == '') {
			alert( '<?php echo JText::_('Please enter a database username', true ) ?>' );
			return;
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
					<?php if ( $this->direction == 'ltr' ) : ?>
						<div class="button1-right"><div class="prev"><a onclick="submitForm( adminForm, 'license' );" alt="<?php echo JText::_('Previous', true ) ?>"><?php echo JText::_('Previous' ) ?></a></div></div>
						<div class="button1-left"><div class="next"><a onclick="validateForm( adminForm, 'makedb' );" alt="<?php echo JText::_('Next', true ) ?>"><?php echo JText::_('Next' ) ?></a></div></div>
					<?php else: ?>
						<div class="button1-right"><div class="prev"><a onclick="validateForm( adminForm, 'makedb' );" alt="<?php echo JText::_('Next', true ) ?>"><?php echo JText::_('Next' ) ?></a></div></div>
						<div class="button1-left"><div class="next"><a onclick="submitForm( adminForm, 'license' );" alt="<?php echo JText::_('Previous', true ) ?>"><?php echo JText::_('Previous' ) ?></a></div></div>
					<?php endif; ?>
				</div>
				<span class="step"><?php echo JText::_('Database Configuration' ) ?></span>
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

				<h2><?php echo JText::_('Connection Settings' ) ?>:</h2>
				<div class="install-text">
					<?php echo JText::_('
					<p>Setting up Joomla! to run on your server involves 4 simple steps...</p>
					<p>Please enter the hostname of the server Joomla! is to be installed on.</p>
					<p>Enter the MySQL username, password and database name you wish to use with Joomla.</p>
					<p>Enter a prefix to be used by tables for this Joomla! installation. Select how to handle exisitng tables from a previous installation.</p>
					<p>Install the samples unless you are experienced want to start with a virtually empty site.</p>
					' ) ?>
				</div>
				<div class="install-body">
				<div class="t">
			<div class="t">
				<div class="t"></div>
			</div>
			</div>
			<div class="m">
							<h3 class="title-smenu" title="<?php echo JText::_('Basic' ) ?>"><?php echo JText::_('Basic Settings' ) ?></h3>
							<div class="section-smenu">
								<table class="content2">
								<tr>
									<td></td>
									<td></td>
									<td></td>
								</tr>
								<tr>
									<td colspan="2">
										<label for="vars_dbtype">
											<?php echo JText::_('Database Type' ) ?>
										</label>
										<br />
										<select id="vars_dbtype" name="vars[DBtype]" class="inputbox" size="1">
										<option value=""><?php echo JText::_('Select Type' ) ?></option>
										<?php foreach ( $this->options as $option) : ?>
											<option value="<?php echo $option['text'] ?>" <?php echo @ $option['selected'] ?> ><?php echo $option['text'] ?></option>
										<?php endforeach; ?>
										</select>
									</td>
									<td>
										<em>
										<?php echo JText::_('This is probably \'mysql\'' ) ?>
										</em>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<label for="vars_dbhostname">
											<span id="dbhostnamemsg"><?php echo JText::_('Host Name' ) ?></span>
										</label>
										<br />
										<input id="vars_dbhostname" class="inputbox validate required none dbhostnamemsg" type="text" name="vars[DBhostname]" value="<?php echo $this->getSessionVar('DBhostname') ?>" />
									</td>
									<td>
										<em>
										<?php echo JText::_('This is usually \'localhost\'') ?>
										</em>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<label for="vars_dbusername">
											<span id="dbusernamemsg"><?php echo JText::_('User Name') ?></span>
										</label>
										<br />
										<input id="vars_dbusername" class="inputbox validate required none dbusernamemsg" type="text" name="vars[DBuserName]" value="<?php echo $this->getSessionVar('DBuserName') ?>" />
									</td>
									<td>
										<em>
										<?php echo JText::_('Either something as \'root\' or a username given by the hoster' ) ?>
										</em>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<label for="vars_dbpassword">
											<?php echo JText::_('Password' ) ?>
										</label>
										<br />
										<input id="vars_dbpassword" class="inputbox" type="password" name="vars[DBpassword]" value="<?php echo $this->getSessionVar('DBpassword') ?>" />
									</td>
									<td>
										<em>
										<?php echo JText::_('For site security using a password for the mysql account is mandatory' ) ?>
										</em>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<label for="vars_dbname">
											<span id="dbnamemsg"><?php echo JText::_('Database Name') ?></span>
										</label>
										<br />
										<input id="vars_dbname" class="inputbox validate required none dbnamemsg" type="text" name="vars[DBname]" value="<?php echo $this->getSessionVar('DBname') ?>" />
									</td>
									<td>
										<em>
										<?php echo JText::_('Some hosts allow only a certain DB name per site. Use table prefix in this case for distinct Joomla! sites.' ) ?>
										</em>
									</td>
								</tr>
								</table>
								<br /><br />
							</div>
							<h3 class="title-smenu moofx-toggler" title="<?php echo JText::_('Advanced', true ) ?>"><?php echo JText::_('Advanced settings' ) ?></h3>
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
											<?php echo JText::_('Drop Existing Tables' ) ?>
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
											<?php echo JText::_('Backup Old Tables' ) ?>
										</label>
									</td>

									<td>
										<em>
										<?php echo JText::_('Any existing backup tables from former Joomla! installations will be replaced' ) ?>
										</em>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<label for="vars_dbprefix">
											<?php echo JText::_('Table Prefix' ) ?>
										</label>
										<br />
										<input id="vars_dbprefix" class="inputbox" type="text" name="vars[DBPrefix]" value="<?php echo $this->getSessionVar('DBPrefix') ?>" />
									</td>
									<td>
										<em>
										<?php echo JText::_('Dont use \'old_\' since this is used for backup tables' ) ?>
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

</form>


