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
					<?php if ( $this->direction == 'ltr' ) : ?>
						<div class="button1-left"><div class="refresh"><a onclick="submitForm( adminForm, 'preinstall' );" alt="<?php echo JText::_('Check Again' ,true ) ?>"><?php echo JText::_('Check Again') ?></a></div></div>
						<div class="button1-right"><div class="prev"><a onclick="submitForm( adminForm, 'lang' );" alt="<?php echo JText::_('Previous' ,true ) ?>"><?php echo JText::_('Previous') ?></a></div></div>
						<div class="button1-left"><div class="next"><a onclick="submitForm( adminForm, 'license' );" alt="<?php echo JText::_('Next' ,true ) ?>"><?php echo JText::_('Next') ?></a></div></div>
					<?php else: ?>
						<div class="button1-right"><div class="prev"><a onclick="submitForm( adminForm, 'license' );" alt="<?php echo JText::_('Next' ,true ) ?>"><?php echo JText::_('Next') ?></a></div></div>
						<div class="button1-left"><div class="next"><a onclick="submitForm( adminForm, 'lang' );" alt="<?php echo JText::_('Previous' ,true ) ?>"><?php echo JText::_('Previous') ?></a></div></div>
						<div class="button1-left"><div class="refresh"><a onclick="submitForm( adminForm, 'preinstall' );" alt="<?php echo JText::_('Check Again' ,true ) ?>"><?php echo JText::_('Check Again') ?></a></div></div>
					<?php endif; ?>
				</div>
				<span class="step"><?php echo JText::_('Pre-Installation check') ?></span>
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

				<h2><?php echo JText::_('Pre-installation check for') ?> <?php $this->version ?>:</h2>
				<div class="install-text">
					<?php echo JText::_('If any of these items is not supported (marked as <strong><font color="#ff00">No</font></strong>)
					then please take actions to correct them. Failure to do so
					could lead to your Joomla! installation not functioning
					correctly.') ?>
				</div>
				<div class="install-body">
				<div class="t">
			<div class="t">
				<div class="t"></div>
			</div>
			</div>
			<div class="m">
						<fieldset>

							<table class="content">
							<?php foreach ( $this->options as $option ) : ?>
							<tr>
								<td class="item" valign="top">
									<?php echo $option['label'] ?>
								</td>
								<td valign="top">
									<span class="<?php echo @ $option['state'] ?>">
									<?php echo JText::_(isset($option['statetext']) ? $option['statetext'] : $option['state']) ?>
									</span>
									<span class="small">
									<?php echo @ $option['notice'] ?>&nbsp;
									</span>
								</td>
							</tr>
							<?php endforeach; ?>
							<tr>
								<td valign="top" class="item">
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
				<h2><?php echo JText::_('Recommended settings') ?>:</h2>
				<div class="install-text">
					<?php echo JText::_('These settings are recommended for PHP in order to ensure full
					compatibility with Joomla.
					<br />
					However, Joomla! will still operate if your settings do not quite match the recommended.') ?>
				</div>
				<div class="install-body">
				<div class="t">
			<div class="t">
				<div class="t"></div>
			</div>
			</div>
			<div class="m">
						<fieldset>
							<table class="content">
							<tr>
								<td class="toggle">
									<?php echo JText::_('Directive') ?>
								</td>
								<td class="toggle">
									<?php echo JText::_('Recommended') ?>
								</td>
								<td class="toggle">
									<?php echo JText::_('Actual') ?>
								</td>
							</tr>
							<?php foreach ( $this->settings as $setting ) : ?>
							<tr>
								<td class="item">
									<?php echo $setting['label'] ?>:
								</td>
								<td class="toggle">
									<?php echo JText::_($setting['setting']) ?>:
								</td>
								<td>
									<span class="<?php echo @ $setting['state'] ?>">
									<?php echo JText::_($setting['actual']) ?>
									</span>
								<td>
							</tr>
							<?php endforeach; ?>
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

<input type="hidden" name="task" value="" />
</form>
