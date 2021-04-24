<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.formvalidator');

/** @var \Joomla\CMS\Installation\View\Configuration\HtmlView $this */
?>
<div id="installer-view" data-page-name="configuration">
	<fieldset id="installCongrat" class="j-install-step active">
		<legend class="j-install-step-header">
			<span class="icon-cog" aria-hidden="true"></span> <?php echo Text::_('INSTL_SAVE_CONFIGURATION'); ?>
		</legend>
		<div class="j-install-step-form">
			<div class="alert alert-error">
				<h3 class="alert-heading"><?php echo Text::_('JNOTICE'); ?></h3>
				<p><?php echo Text::_('INSTL_CONFPROBLEM'); ?></p>
				<textarea rows="10" cols="80" style="width: 100%;" name="configcode" onclick="this.form.configcode.focus();this.form.configcode.select();"><?php echo $this->buffer; ?></textarea>
			</div>
			<div class="form-group">
				<button id="checkConfigurationButton" class="btn btn-primary btn-block"><span class="icon-check icon-white"></span> <?php echo Text::_('INSTL_TO_FINISH'); ?></button>
			</div>
		</div>
	</fieldset>
</div>
