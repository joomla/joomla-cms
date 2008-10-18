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

$languages	=& $this->languages;

?>

<script language="JavaScript" type="text/javascript">
	function validateForm( frm, task ) {
		submitForm( frm, task );
	}
</script>

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
						<div class="button1-left">
							<div class="next"><a onclick="validateForm( adminForm, 'preinstall' );" alt="<?php echo JText::_('Next' ,true ) ?>"><?php echo JText::_('Next') ?></a></div>
						</div>
						<?php else: ?>
						<div class="button1-right">
							<div class="prev"><a onclick="validateForm( adminForm, 'preinstall' );" alt="<?php echo JText::_('Next' ,true ) ?>"><?php echo JText::_('Next') ?></a></div>
						</div>
						<?php endif; ?>
					</div>
					<span class="step"><?php echo JText::_('Choose Language') ?></span>

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

					<h2><?php echo JText::_('Select Language') ?></h2>
					<div class="install-text">
						<?php echo JText::_('PICKYOURCHOICEOFLANGS') ?>
					</div>
					<div class="install-body">
						<div class="t">
							<div class="t">
								<div class="t"></div>
							</div>
						</div>
						<div class="m">
							<fieldset>
								<select name="vars[lang]" class="inputbox" size="20">
									<?php foreach ( $languages as $language ) : ?>
									<option value="<?php echo $language['value'] ?>" <?php echo @ $language['selected'] ?>><?php echo $language['value'] ?> - <?php echo $language['text'] ?></option>
									<?php endforeach; ?>
								</select>
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
