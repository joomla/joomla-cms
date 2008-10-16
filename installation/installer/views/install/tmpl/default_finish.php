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

<form action="index.php" method="post" name="adminForm" autocomplete="off">

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
						<div class="button1-left"><div class="site"><a onclick="window.location.href='<?php echo $this->getSessionVar('siteUrl') ?>';" alt="<?php echo JText::_('Site', true ) ?>"><?php echo JText::_('Site') ?></a></div></div>
						<div class="button1-left"><div class="admin"><a onclick="window.location.href='<?php echo $this->getSessionVar('adminUrl') ?>';" alt="<?php echo JText::_('Admin', true ) ?>"><?php echo JText::_('Admin') ?></a></div></div>
					<?php else: ?>
						<div class="button1-left"><div class="admin"><a onclick="window.location.href='<?php echo $this->getSessionVar('adminUrl') ?>';" alt="<?php echo JText::_('Admin', true ) ?>"><?php echo JText::_('Admin') ?></a></div></div>
						<div class="button1-left"><div class="site"><a onclick="window.location.href='<?php echo $this->getSessionVar('siteUrl') ?>';" alt="<?php echo JText::_('Site', true ) ?>"><?php echo JText::_('Site') ?></a></div></div>
					<?php endif; ?>
				</div>
				<span class="step"><?php echo JText::_('Finish') ?></span>
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

				<h2><?php echo JText::_('congratulations') ?></h2>
				<div class="install-text">
					<?php echo JText::_('finishButtons') ?>
					<?php echo JText::_('languageinfo') ?>
				</div>
		<div class="install-body">
				<div class="t">
			<div class="t">
				<div class="t"></div>
			</div>
			</div>
			<div class="m">
						<fieldset>
							<table class="final-table">
								<tr>
								<td class="error" align="center">
									<?php echo JText::_('removeInstallation') ?>
								</td>
								</tr>
								<tr>
								<td align="center">
									<h3>
									<?php echo JText::_('Administration Login Details') ?>
									</h3>
								</td>
								</tr>
								<tr>
								<td align="center" class="notice">
									<?php echo JText::_('Username') ?>: <?php echo $this->getSessionVar('adminLogin') ?>
								</td>
								</tr>
								<tr>
								<td>&nbsp;</td>
								</tr>
								<tr>
								<td align="center" class="notice">
								<div id="cpanel">
										<div>
											<div class="icon">
												<a href="http://help.joomla.org/content/view/1651/243/" target="_blank">
												<br />
												<b><?php echo JText::_('languagebuttonlineone') ?></b>
												<br />
												<?php echo JText::_('languagebuttonlinetwo') ?>
												<br /><br />
												</a></td>
											</div>
										</div>
								</div>
								</td>
								</tr>
								<tr>
								<td>&nbsp;</td>
								</tr>
								<?php if ( $this->buffer ) : ?>
								<tr>
								<td class="small">
									<?php echo JText::_('confProblem') ?>
								</td>
								</tr>
								<tr>

								<td align="center">
									<textarea rows="5" cols="60" name="configcode" onclick="this.form.configcode.focus();this.form.configcode.select();" ><?php echo $this->buffer ?></textarea>
								</td>
								</tr>
								<?php endif; ?>
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

</form>
