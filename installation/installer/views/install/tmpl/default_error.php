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
						<div class="t">
						</div>
					</div>
				</div>
				<div class="m">
					<div class="far-right">
						<div class="button1-right"><div class="prev"><a onclick="submitForm( adminForm, '<?php echo $this->back ?>' );" alt="<?php echo JText::_('Previous', true) ?>"><?php echo JText::_('Previous') ?></a></div></div>
					</div>
					<span class="step"><?php echo JText::_('Error') ?></span>
				</div>
				<div class="b">
					<div class="b">
						<div class="b">
						</div>
					</div>
				</div>
			</div>

			<div id="installer">
				<div class="t">
					<div class="t">
						<div class="t">
						</div>
					</div>
				</div>
				<div class="m">
					<h2><?php echo JText::_('An error has occurred') ?>:</h2>
					<div class="install-text">
						<p>
							<?php echo $this->message ?>
						</p>
					</div>
					<?php if ( $this->errors ) : ?>
						<div class="install-form">
							<fieldset class="form-block">
								<textarea rows="10" cols="50"><?php echo $this->errors ?></textarea>
							</fieldset>
						</div>
					<?php endif; ?>
					<div class="clr">
					</div>
				</div>
				<div class="b">
					<div class="b">
						<div class="b">
						</div>
					</div>
				</div>
				<div class="clr">
				</div>
			</div>
			<div class="clr">
			</div>
		</div>
		<div class="b">
			<div class="b">
				<div class="b">
				</div>
			</div>
  		</div>
	</div>
	<div class="clr">
	</div>
	<input type="hidden" name="task" value="" />
</form>
