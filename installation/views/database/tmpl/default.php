<?php
/**
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div id="step">
	<div class="far-right">
<?php if ($this->document->direction == 'ltr') : ?>
		<div class="button1-right"><div class="prev"><a href="index.php?view=license" onclick="return Install.goToPage('license');" rel="prev" title="<?php echo JText::_('JPrevious'); ?>"><?php echo JText::_('JPrevious'); ?></a></div></div>
		<div class="button1-left"><div class="next"><a href="#" onclick="Install.submitform();" rel="next" title="<?php echo JText::_('JNext'); ?>"><?php echo JText::_('JNext'); ?></a></div></div>
<?php elseif ($this->document->direction == 'rtl') : ?>
		<div class="button1-right"><div class="prev"><a href="#" onclick="Install.submitform();" rel="next" title="<?php echo JText::_('JNext'); ?>"><?php echo JText::_('JNext'); ?></a></div></div>
		<div class="button1-left"><div class="next"><a href="index.php?view=license" onclick="return Install.goToPage('license');" rel="prev" title="<?php echo JText::_('JPrevious'); ?>"><?php echo JText::_('JPrevious'); ?></a></div></div>
<?php endif; ?>
	</div>
	<h2><?php echo JText::_('INSTL_DATABASE'); ?></h2>
</div>
<form action="index.php" method="post" id="adminForm" class="form-validate">
	<div id="installer">
		<div class="m">
			<h3><?php echo JText::_('INSTL_DATABASE_TITLE'); ?></h3>
			<div class="install-text">
					<?php echo JText::_('INSTL_DATABASE_DESC'); ?>
			</div>
			<div class="install-body">
				<div class="m">
					<h4 class="title-smenu" title="<?php echo JText::_('Basic'); ?>">
						<?php echo JText::_('INSTL_BASIC_SETTINGS'); ?>
					</h4>
					<div class="section-smenu">
						<table class="content2 db-table">
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
									<?php echo JText::_('INSTL_DATABASE_TYPE_DESC'); ?>
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
									<?php echo JText::_('INSTL_DATABASE_HOST_DESC'); ?>
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
									<?php echo JText::_('INSTL_DATABASE_USER_DESC'); ?>
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
									<?php echo JText::_('INSTL_DATABASE_PASSWORD_DESC'); ?>
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
									<?php echo JText::_('INSTL_DATABASE_NAME_DESC'); ?>
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
									<?php echo JText::_('INSTL_DATABASE_PREFIX_DESC'); ?>
									</em>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<?php echo $this->form->getLabel('db_old'); ?>
									<br />
									<?php echo $this->form->getInput('db_old'); ?>
								</td>
								<td>
									<em>
									<?php echo JText::_('INSTL_DATABASE_OLD_PROCESS_DESC'); ?>
									</em>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			<div class="clr"></div>
		</div>
	</div>
	<input type="hidden" name="task" value="setup.database" />
	<?php echo JHtml::_('form.token'); ?>
</form>
