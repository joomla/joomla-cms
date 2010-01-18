<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
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
		<?php echo JHtml::_('installation.stepbar', 4); ?>
		<div class="box"></div>
  	</div>
	<div class="b">
		<div class="b">
			<div class="b"></div>
		</div>
	</div>
</div>

<form action="index.php" method="post" name="adminForm" class="form-validate">
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
					<div class="button1-right"><div class="prev"><a href="index.php?view=license" title="<?php echo JText::_('JPrevious'); ?>"><?php echo JText::_('JPrevious'); ?></a></div></div>
					<div class="button1-left"><div class="next"><a onclick="validateForm(adminForm, 'setup.database');" title="<?php echo JText::_('JNext'); ?>"><?php echo JText::_('JNext'); ?></a></div></div>
<?php elseif ($this->document->direction == 'rtl') : ?>
					<div class="button1-right"><div class="prev"><a onclick="validateForm(adminForm, 'setup.database');" title="<?php echo JText::_('JNext'); ?>"><?php echo JText::_('JNext'); ?></a></div></div>
					<div class="button1-left"><div class="next"><a href="index.php?view=license" title="<?php echo JText::_('JPrevious'); ?>"><?php echo JText::_('JPrevious'); ?></a></div></div>
<?php endif; ?>
				</div>
				<span class="step"><?php echo JText::_('DATABASE_CONFIGURATION'); ?></span>
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
				<h2><?php echo JText::_('CONNECTION_SETTINGS'); ?>:</h2>
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
							<?php echo JText::_('BASIC_SETTINGS'); ?>
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
										<?php echo $this->form->getLabel('db_type'); ?>
										<br />
										<?php echo $this->form->getInput('db_type'); ?>
									</td>
									<td>
										<em>
										<?php echo JText::_('This is probably "mysql"'); ?>
										</em>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<?php echo $this->form->getLabel('db_host'); ?>
										<br />
										<?php echo $this->form->getInput('db_host'); ?>
									</td>
									<td>
										<em>
										<?php echo JText::_('This is usually "localhost"'); ?>
										</em>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<?php echo $this->form->getLabel('db_user'); ?>
										<br />
										<?php echo $this->form->getInput('db_user'); ?>
									</td>
									<td>
										<em>
										<?php echo JText::_('Either something as "root" or a username given by the hoster'); ?>
										</em>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<?php echo $this->form->getLabel('db_pass'); ?>
										<br />
										<?php echo $this->form->getInput('db_pass'); ?>
									</td>
									<td>
										<em>
										<?php echo JText::_('For site security using a password for the mysql account is mandatory'); ?>
										</em>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<?php echo $this->form->getLabel('db_name'); ?>
										<br />
										<?php echo $this->form->getInput('db_name'); ?>
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
							<?php echo JText::_('ADVANCED_SETTINGS'); ?>
						</h3>
						<div class="section-smenu moofx-slider">
							<table class="content2">
								<tr>
									<td></td>
									<td></td>
									<td></td>
								</tr>
								<tr>
									<td colspan="2">
										<?php echo $this->form->getLabel('db_old'); ?>
										<br />
										<?php echo $this->form->getInput('db_old'); ?>
									</td>
									<td>
										<em>
										<?php echo JText::_('Any existing backup tables from former Joomla! installations will be replaced'); ?>
										</em>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<?php echo $this->form->getLabel('db_prefix'); ?>
										<br />
										<?php echo $this->form->getInput('db_prefix'); ?>
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
